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
	 * Per-clinic capacity limit, default 2 (spec 1.15, 2.9, 5.5.70, 5.5.71).
	 *
	 * Implements: spec 1.15, 2.9, 5.6.63
	 *
	 * @param string $office_id Clinic id.
	 * @return int Capacity >= 1.
	 */
	public static function capacity_for( $office_id ) {
		$default = (int) apply_filters( 'drplus_child_capacity/default_capacity', DRPLUS_ROOM_CAPACITY_DEFAULT );
		$value   = (int) get_option( 'drplus_capacity_clinic_' . $office_id, $default );
		$value   = (int) apply_filters( 'drplus_child_capacity/per_clinic', $value, $office_id );
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
			return $slots;
		}
		$capacity = self::capacity_for( $office_id );
		// One GROUP BY query for the whole day — no N+1 (spec 1.13, 2.7).
		$counts = DrPlus_Child_Capacity_Repository::count_day_slots( $office_id, $date );
		$filtered = array();
		foreach ( $slots as $slot ) {
			if ( empty( $slot['available'] ) ) {
				// Trust the parent: off-hours, breaks and the parent soft-delete behavior stay as-is (spec 2.4).
				$filtered[] = $slot;
				continue;
			}
			$start = DrPlus_Child_Capacity_Repository::normalize_time( isset( $slot['from'] ) ? $slot['from'] : '' );
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
				);
			}
		}
		return $hours;
	}
}
