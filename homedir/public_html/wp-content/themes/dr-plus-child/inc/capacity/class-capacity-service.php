<?php
/**
 * Dr Plus Child Theme — Room Capacity Management
 *
 * Implements: spec 2.1-2.15, 5.6.52
 * Category: 2 — Slot filtering (business logic; no $wpdb here, spec 5.6.52)
 *
 * @package DrPlusChild\Capacity
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Service: capacity business logic — slot filtering, online-visit detection, capacity lookup.
 *
 * Single responsibility: decisions only; all SQL is delegated to the repository (spec 5.6.52).
 */
class DrPlus_Child_Capacity_Service {

	/**
	 * Online visit types exempt from capacity (spec 5.6.64, decision #5).
	 *
	 * instant_chat_consultation is intentionally NOT in this list (decision #6).
	 * 'consultation' is the parent theme's documented legacy alias of
	 * phone_consultation (class-specialists-reserve.php:114,
	 * template-booking-step-receipt.php:79), so it must be exempt too — otherwise
	 * a legacy-tagged phone consultation would be counted against room capacity.
	 */
	const ONLINE_TYPES = array( 'phone_consultation', 'chat_consultation', 'video_consultation' );

	/**
	 * Legacy office ids that mean an exempt online visit. Kept separate so the
	 * three real visit types above stay auditable against the business rule.
	 */
	const ONLINE_ALIASES = array( 'consultation' => 'phone_consultation' );

	/**
	 * Whether the feature is enabled (spec 2.12, 5.5.4, 5.6.95).
	 *
	 * Implements: spec 2.12, 5.6.95
	 *
	 * @return bool
	 */
	public static function enabled() {
		return (bool) apply_filters( 'drplus_child_capacity/enabled', true );
	}

	/**
	 * Normalize a legacy office id to its modern identifier.
	 *
	 * @param string $office_id Raw office/visit identifier.
	 * @return string
	 */
	public static function normalize_office_id( $office_id ) {
		$office_id = (string) $office_id;
		return isset( self::ONLINE_ALIASES[ $office_id ] ) ? self::ONLINE_ALIASES[ $office_id ] : $office_id;
	}

	/**
	 * Is this office id an online (exempt) visit type? (spec 2.6, 3.4, Finding 4)
	 *
	 * Implements: spec 2.6, 3.4, 5.5.64
	 *
	 * @param string $office_id Office/visit identifier from book_data.
	 * @return bool
	 */
	public static function is_online_visit( $office_id ) {
		$types = apply_filters( 'drplus_child_capacity/online_types', self::ONLINE_TYPES );
		return in_array(
			self::normalize_office_id( $office_id ),
			array_map( 'strval', (array) $types ),
			true
		);
	}

	/**
	 * Every office_id that denotes a virtual visit, including the legacy aliases.
	 *
	 * is_online_visit() maps `consultation` to `phone_consultation` before comparing, but SQL
	 * compares raw column values and cannot do that mapping. This method returns the literal
	 * strings that may appear in `drplus_booking.office_id`, so a query used to decide "does
	 * this specialist already hold a room?" can exclude all of them.
	 *
	 * Honours the same filter as is_online_visit() so the two cannot disagree when a site
	 * extends the list.
	 *
	 * @return string[] Raw office_id values that never occupy a physical room.
	 */
	public static function virtual_office_ids() {
		$types = apply_filters( 'drplus_child_capacity/online_types', self::ONLINE_TYPES );
		$ids   = array_map( 'strval', (array) $types );

		// The legacy spellings: is_online_visit() resolves these to a modern type, so a row
		// filed under one of them is just as exempt as a row filed under the modern id.
		foreach ( self::ONLINE_ALIASES as $legacy => $modern ) {
			if ( in_array( $modern, $ids, true ) && ! in_array( $legacy, $ids, true ) ) {
				$ids[] = (string) $legacy;
			}
		}

		return array_values( array_unique( $ids ) );
	}

	/**
	 * Per-clinic capacity limit, default 2 (spec 1.15, 2.9, 5.5.70, 5.5.71).
	 *
	 * Implements: spec 1.15, 2.9, 5.6.63
	 *
	 * @param string $office_id Clinic id.
	 * @return int Capacity >= 1.
	 */
	public static function capacity_for( $office_id ) {
		$default = (int) apply_filters( 'drplus_child_capacity/default_capacity', DRPLUS_ROOM_CAPACITY_DEFAULT );

		// The limit is saved against the clinic's post id (the settings page lists hospital
		// posts). A booking that arrives through one of that clinic's custom-office UUIDs must
		// get the SAME limit, otherwise the two entry points enforce different capacities:
		// a clinic configured for 3 silently enforces 2 via a UUID, and a clinic configured for
		// 1 would allow 2. Resolve aliases first and use the first configured value found.
		// Take the STRICTEST value configured for any identifier of this clinic, not the first
		// one found.
		//
		// resolve_clinic_identifiers() returns the ids in a data-dependent order (the numeric
		// hospital post id and several per-specialist UUIDs all denote one clinic). If an
		// operator ever set two of them to different numbers — 614 = 1 from the settings page
		// and a UUID = 3 written by an import or an older release — then "first match wins"
		// makes the SAME clinic enforce 1 through one entry point and 3 through another. The
		// room can then be overbooked simply because the customer happened to be routed to the
		// UUID. Minimum is the only safe merge: it can never allow more bookings than the
		// operator's most conservative configured limit.
		$identifiers = DrPlus_Child_Capacity_Repository::resolve_clinic_identifiers( $office_id );
		$value       = null;
		$sources     = array();
		foreach ( $identifiers as $identifier ) {
			$stored = get_option( 'drplus_capacity_clinic_' . $identifier, null );
			if ( null !== $stored && '' !== $stored && false !== $stored ) {
				$candidate = (int) $stored;
				if ( $candidate > 0 ) {
					$sources[] = array( 'id' => $identifier, 'value' => $candidate );
					$value     = ( null === $value ) ? $candidate : min( $value, $candidate );
				}
			}
		}
		if ( null === $value ) {
			$value = $default;
		} elseif ( count( $sources ) > 1 ) {
			// Two identifiers of one clinic disagree. Honour the strictest, but say so: a silent
			// choice here is exactly how the same clinic ends up with two different capacities.
			$distinct = array_unique( wp_list_pluck( $sources, 'value' ) );
			if ( count( $distinct ) > 1 ) {
				$summary = array();
				foreach ( $sources as $source ) {
					$summary[] = $source['id'] . '=' . $source['value'];
				}
				DrPlus_Child_Capacity_Logger::diag( 'capacity_for("' . $office_id . '"): identifiers disagree (' . implode( ', ', $summary ) . ') — enforcing the strictest value ' . $value );
			}
		}

		$value = (int) apply_filters( 'drplus_child_capacity/per_clinic', $value, $office_id );
		if ( $value < 1 ) {
			$value = max( 1, $default );
		}
		return $value;
	}

	/**
	 * Filter callback for drplus/booking/available_time_slots (spec 2.1-2.5).
	 *
	 * Removes slots whose hour reached capacity. Slots the parent marked
	 * available=false are trusted and left untouched (locked coexistence policy).
	 *
	 * Implements: spec 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8, 2.12
	 *
	 * @param array $slots Available slots, each {from,to,available,label} (utils-booking.php:500-506).
	 * @param array $args  Context with date, specialist_id, office_id (utils-booking.php:511-516).
	 * @return array Filtered slot list, same shape (spec 2.5).
	 */
	public static function filter_full_slots( $slots, $args ) {
		if ( ! self::enabled() || ! is_array( $slots ) ) {
			return $slots;
		}
		$office_id = isset( $args['office_id'] ) ? (string) $args['office_id'] : '';
		if ( '' === $office_id || self::is_online_visit( $office_id ) ) {
			return $slots; // Online visits are exempt (spec 2.6).
		}
		$date = DrPlus_Child_Capacity_Repository::normalize_date( isset( $args['date'] ) ? $args['date'] : '' );
		if ( '' === $date ) {
			// The requested day cannot be resolved to a Gregorian date. Returning the slots
			// unchanged would paint a full schedule as bookable and then let the customer pick
			// an hour the validator can never verify (the server-side check refuses it, but only
			// after the whole form is filled in). Fail closed on display too: an unresolvable
			// day offers no hours, exactly like a day with every slot full.
			return array();
		}
		$capacity = self::capacity_for( $office_id );
		// One GROUP BY query for the whole day — no N+1 (spec 1.13, 2.7).
		$counts = DrPlus_Child_Capacity_Repository::count_day_slots( $office_id, $date );

		// A specialist who already holds this hour cannot take it again, whatever the room
		// count says. Distinct-specialist capacity cannot see this: one specialist booking
		// twice leaves the count at 1, so a 2-room clinic offers the hour and then rejects it
		// at checkout. Removing it from the grid is the fix — the customer should never be
		// shown an hour that is guaranteed to fail (Task 2).
		//
		// Scoped to THIS clinic, exactly like the parent's booked-range query, so this filter
		// never hides an hour the parent deliberately offered. A cross-clinic clash is still
		// refused at checkout by the validator's broader rule — but that path also knows the
		// reason and says so, instead of this filter silently disagreeing with the grid above.
		$specialist_id = isset( $args['specialist_id'] ) ? (int) $args['specialist_id'] : 0;
		$busy          = $specialist_id > 0
			? DrPlus_Child_Capacity_Repository::specialist_booked_slots_for_day( $specialist_id, $date, $office_id )
			: array();
		if ( $specialist_id <= 0 ) {
			// Without a specialist there is no double-booking to detect. The parent always
			// passes specialist_id (utils-booking.php:511-516), so this is the "unknown
			// caller" path: keep the room-capacity filtering below and say why in the log
			// rather than silently degrading.
			DrPlus_Child_Capacity_Logger::diag( 'filter_full_slots: no specialist_id in filter args — specialist-conflict hiding skipped, room capacity still enforced' );
		}

		$filtered = array();
		foreach ( $slots as $slot ) {
			if ( empty( $slot['available'] ) ) {
				// Trust the parent: off-hours, breaks and the parent soft-delete behavior stay as-is (spec 2.4).
				$filtered[] = $slot;
				continue;
			}
			$start = DrPlus_Child_Capacity_Repository::normalize_time( isset( $slot['from'] ) ? $slot['from'] : '' );
			if ( isset( $busy[ $start ] ) ) {
				continue; // Specialist already booked this hour — remove it from the grid.
			}
			$count = isset( $counts[ $start ] ) ? (int) $counts[ $start ] : 0;
			if ( $count >= $capacity ) {
				continue; // Full slot removed entirely, not disabled (spec 2.3, decision #2).
			}
			$filtered[] = $slot;
		}
		return array_values( $filtered ); // Re-index after unset-equivalent removal (spec 2.3).
	}

	/**
	 * All still-available hours of a day for rejection messages (decision #4, spec 3.9).
	 *
	 * Implements: spec 3.9, 2.7
	 *
	 * @param string $office_id Clinic id.
	 * @param string $date      Y-m-d.
	 * @param array  $slots     Parent slot list for the day (from the same filter args).
	 * @return array<int,array{startTime:string,endTime:string}>
	 */
	public static function get_available_hours( $office_id, $date, $slots ) {
		$capacity = self::capacity_for( $office_id );
		$counts   = DrPlus_Child_Capacity_Repository::count_day_slots( $office_id, $date );
		$hours    = array();
		foreach ( (array) $slots as $slot ) {
			if ( empty( $slot['available'] ) ) {
				continue;
			}
			$start = DrPlus_Child_Capacity_Repository::normalize_time( isset( $slot['from'] ) ? $slot['from'] : '' );
			$count = isset( $counts[ $start ] ) ? (int) $counts[ $start ] : 0;
			if ( $count < $capacity ) {
				$hours[] = array(
					'startTime' => isset( $slot['from'] ) ? $slot['from'] : '',
					'endTime'   => isset( $slot['to'] ) ? $slot['to'] : '',
					'order'     => $start,
				);
			}
		}

		// Rule 5 says "suggest the first available alternative", so the list must be ordered by
		// real clock time. The parent's slot array is not guaranteed to arrive sorted, and a
		// naive string sort would put 9:00 after 14:00; sort on the normalized H:i:s instead.
		usort(
			$hours,
			function ( $a, $b ) {
				return strcmp( $a['order'], $b['order'] );
			}
		);
		foreach ( $hours as &$hour ) {
			unset( $hour['order'] );
		}
		unset( $hour );

		return $hours;
	}
}
