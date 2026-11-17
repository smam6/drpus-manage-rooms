<?php
/**
 * Dr Plus Child Theme — Room Capacity Management
 *
 * Implements: spec 1.1-1.15, 4.1-4.12, 5.6.51, 5.6.80-5.6.90
 * Category: 1 — Capacity counting, Category 4 — Race condition
 *
 * All $wpdb queries of the module live in this class (spec 5.6.51).
 *
 * @package DrPlusChild\Capacity
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Repository: every database access for capacity counting and locking.
 *
 * Single responsibility: SQL only, no business decisions (spec 5.6.51).
 */
class DrPlus_Child_Capacity_Repository {

	/**
	 * Booking table name without prefix (spec 5.6.66).
	 */
	const BOOKING_TABLE = 'drplus_booking';

	/**
	 * Statuses excluded from every count (spec 1.6, 5.6.65).
	 *
	 * A cancelled booking MUST NOT hold a room or a specialist's hour. Getting this list wrong
	 * is how a customer is told «این متخصص در این ساعت قبلاً رزرو دارد» for an hour whose only
	 * booking was cancelled — the row is still there, and if its status is not in this list it
	 * counts as a live booking forever.
	 *
	 * The list carries every spelling that can reach this column, because the value is written
	 * by hand in the parent theme (`$book->order_status = 'cancelled'`), by a cron job, and
	 * potentially by any integration syncing WooCommerce order status into the row:
	 *
	 *   cancelled / canceled / wc-cancelled / wc-canceled
	 *       The parent writes `cancelled` (utils-booking.php:789, CheckBookingStatuses.php:20).
	 *       `canceled` is the US spelling, and the `wc-` prefix is what WooCommerce itself uses
	 *       (`$order->get_status()` returns `cancelled`; `wc_get_order_statuses()` returns
	 *       `wc-cancelled`). A sync that stores the WooCommerce form verbatim would otherwise
	 *       leave the row counting as active forever.
	 *
	 *   refunded / wc-refunded / failed / wc-failed / trash
	 *       The parent already treats these as non-blocking in two places — its own slot
	 *       generator (utils-booking.php:445, `['cancelled','refunded','failed']`) and its
	 *       cancel guard (utils-booking.php:634) — so the child must agree or the two layers
	 *       disagree about which hours are free.
	 *
	 *   checkout-draft
	 *       WooCommerce's abandoned-checkout status; no appointment was ever confirmed.
	 *
	 * Deliberately NOT excluded: `on-hold` and `pending`. Those still hold the slot while the
	 * customer pays, and the parent's own cron cancels them after 30 minutes. Excluding them
	 * would let two people hold the same hour simultaneously.
	 */
	const EXCLUDED_STATUSES = array(
		'cancelled',
		'canceled',
		'wc-cancelled',
		'wc-canceled',
		'refunded',
		'wc-refunded',
		'failed',
		'wc-failed',
		'trash',
		'checkout-draft',
	);

	/**
	 * Error code when the row lock cannot be acquired (spec 3.15, 5.6.67).
	 */
	const ERR_LOCK_FAILED = 'lock_failed';

	/**
	 * Returned by the counters when the date cannot be proven Gregorian.
	 *
	 * Set above the configurable ceiling (`DrPlus_Child_Capacity_Admin::MAX_CAPACITY = 99`) so
	 * that every comparison of the form `$count >= $capacity` is TRUE, and the booking is
	 * rejected. Failing open here would recreate the overbooking bug; failing closed on a
	 * malformed date is the only safe direction. Never a real count.
	 */
	const UNRESOLVED_DATE_COUNT = 999;

	/**
	 * Which input made the last specialist check unverifiable: '', 'specialist', 'date' or 'time'.
	 *
	 * A refusal must name its own cause. Reporting "specialist already booked" when the real
	 * problem is a missing specialist id sends the customer looking for a booking that does not
	 * exist, and sends the operator looking in the database instead of at the form.
	 *
	 * @var string
	 */
	protected static $last_unverifiable = '';

	/**
	 * Read-and-clear the reason recorded by the last specialist check.
	 *
	 * @return string '', 'specialist', 'date' or 'time'.
	 */
	public static function last_unverifiable_reason() {
		$reason                  = self::$last_unverifiable;
		self::$last_unverifiable = '';
		return $reason;
	}

	/**
	 * Fully-qualified booking table name via dynamic prefix (spec 5.5.81).
	 *
	 * Implements: spec 1.1, 5.5.81
	 *
	 * @return string
	 */
	public static function booking_table() {
		global $wpdb;
		return $wpdb->prefix . self::BOOKING_TABLE;
	}

	/**
	 * SQL predicate that keeps only rows holding a live claim on the slot.
	 *
	 * WHY NOT A BARE `order_status NOT IN (...)`
	 *
	 * `NOT IN` is exact-match and collation-dependent. Two failure modes have already bitten
	 * this module and neither is hypothetical:
	 *
	 *   1. Case. A row could hold `Cancelled` (written by an admin UI, an import, or a future
	 *      integration). Under a case-sensitive collation that row is NOT in the list and keeps
	 *      blocking the hour.
	 *   2. Whitespace. `'cancelled '` — a trailing space from a CSV import or a form field —
	 *      is a different string to MySQL and also keeps blocking.
	 *
	 * Both produce exactly the reported symptom: the customer is told the specialist is already
	 * booked for an hour whose only booking was cancelled. Lowercasing and trimming inside SQL
	 * makes the comparison match the intent ("is this booking cancelled?") rather than the
	 * storage detail ("is this string byte-identical to one of ours?").
	 *
	 * `LOWER(TRIM(...))` is not index-friendly, but this predicate is applied to rows already
	 * narrowed by an indexed (date, start_time, office_id/specialist_id) range, so the column
	 * is never scanned on its own. That trade is deliberate: a missed cancellation locks a
	 * patient out of a free appointment.
	 *
	 * @param string $placeholders Comma-separated %s placeholders, one per excluded status.
	 * @return string SQL fragment, safe to append after "AND".
	 */
	protected static function active_status_predicate( $placeholders ) {
		return "LOWER(TRIM(order_status)) NOT IN ({$placeholders})";
	}

	/**
	 * Does this status string mean "this booking no longer holds the slot"?
	 *
	 * PHP-side mirror of active_status_predicate(), used where the status arrives as a value
	 * rather than as a SQL column. Normalizes exactly the same way so the two layers cannot
	 * disagree: trimming, lowercasing, and accepting the WooCommerce `wc-` prefix (WooCommerce
	 * reports `wc-cancelled` via wc_get_order_statuses() but `cancelled` from
	 * WC_Order::get_status(), and both spellings reach this codebase).
	 *
	 * @param string $status Raw status value.
	 * @return bool True when the booking still occupies the room / the specialist's hour.
	 */
	public static function status_holds_slot( $status ) {
		$status = strtolower( trim( (string) $status ) );
		if ( '' === $status ) {
			// No status recorded. Treat as holding the slot: failing open would release a
			// room that may well be occupied, which is the overbooking bug this module exists
			// to prevent.
			return true;
		}
		if ( in_array( $status, self::EXCLUDED_STATUSES, true ) ) {
			return false;
		}
		// Tolerate the WooCommerce prefix on any spelling we already exclude.
		if ( 0 === strpos( $status, 'wc-' ) ) {
			return ! in_array( substr( $status, 3 ), self::EXCLUDED_STATUSES, true );
		}
		return true;
	}

	/**
	 * Normalize a date to the Y-m-d value the parent theme stores in `{prefix}drplus_booking`.`date`.
	 *
	 * The booking table is the source of truth for capacity counting, so this MUST apply the
	 * same convention the parent uses when it writes the row (`date( 'Y-m-d', $timestamp )` in UTC).
	 * `wp_date()` would shift the calendar day across the Asia/Tehran midnight boundary and make
	 * the COUNT query miss real bookings (the capacity bypass this module had to fix).
	 *
	 * Jalali/Gregorian calendar must never leak in here: a Jalali string such as `1405-08-28`
	 * is a syntactically valid `Y-m-d` that MySQL happily compares against real Gregorian dates,
	 * returning 0 rows. Anything that still looks like a calendar triple in a non-Gregorian year
	 * is rejected outright, and free text is never handed to strtotime() (a localized Jalali
	 * string can silently resolve to a relative, wrong Gregorian date).
	 *
	 * Implements: spec 1.3, 3.3, Matrix C
	 *
	 * @param string|int $date            Y-m-d, ISO-8601 timestamp, or unix timestamp.
	 * @param bool       $treat_as_number When true, a numeric value is always a unix timestamp.
	 * @return string Y-m-d or empty string when invalid.
	 */
	public static function normalize_date( $date, $treat_as_number = false ) {
		$date = trim( (string) $date );
		if ( '' === $date ) {
			return '';
		}

		// Unix timestamp in seconds or milliseconds (raw_date).
		if ( $treat_as_number || is_numeric( $date ) ) {
			if ( ! is_numeric( $date ) ) {
				return ''; // Caller declared a timestamp but did not pass one.
			}
			$ts = (int) $date;
			if ( $ts > 100000000000 ) {
				$ts = (int) floor( $ts / 1000 );
			}
			return $ts > 0 ? gmdate( 'Y-m-d', $ts ) : '';
		}

		// Gregorian Y-m-d: reject Jalali years (1405-08-28) and impossible dates.
		$parsed = self::parse_ymd( $date );
		if ( null !== $parsed ) {
			list( $year, $month, $day ) = $parsed;
			if ( ! self::is_gregorian_date( $year, $month, $day ) ) {
				return '';
			}
			return sprintf( '%04d-%02d-%02d', $year, $month, $day );
		}

		// Any other year/month/day triple (1405/08/28, 1405.08.28) is a non-Gregorian calendar: never query it.
		if ( preg_match( '/^\d{3,4}[\/.]\d{1,2}[\/.]\d{1,2}$/', $date ) ) {
			return '';
		}

		// ISO-8601 timestamps (the datepicker `altFormat: "X"` value can arrive as an ISO string).
		$iso = self::parse_iso8601( $date );
		if ( null !== $iso ) {
			return gmdate( 'Y-m-d', $iso );
		}

		// Anything left is free text of unknown calendar origin (e.g. localized "28 آبان 1405").
		// Never guess: callers fall back to raw_date.
		return '';
	}

	/**
	 * Is this a real calendar date in the Gregorian era this module can safely query?
	 *
	 * The lower bound rejects Jalali (14xx) and any other non-Gregorian year, which is what made
	 * the capacity COUNT query miss every row.
	 *
	 * @param int $year  Four-digit year.
	 * @param int $month Month 1-12.
	 * @param int $day   Day 1-31.
	 * @return bool
	 */
	public static function is_gregorian_date( $year, $month, $day ) {
		return $year >= 1900 && checkdate( (int) $month, (int) $day, (int) $year );
	}

	/**
	 * Parse a strict `Y-m-d` (optionally with a time part) into [year, month, day], without
	 * letting PHP guess. Returns null when the string does not match the shape.
	 *
	 * @param string $date Candidate date string.
	 * @return array{0:int,1:int,2:int}|null
	 */
	protected static function parse_ymd( $date ) {
		if ( ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})(?:[ T]\d{1,2}:\d{2}(?::\d{2})?)?$/', $date, $m ) ) {
			return null;
		}
		return array( (int) $m[1], (int) $m[2], (int) $m[3] );
	}

	/**
	 * Parse an ISO-8601 timestamp (the datepicker `altFormat: "X"` alt value as an ISO string).
	 *
	 * @param string $date Candidate string.
	 * @return int|null Unix timestamp or null.
	 */
	protected static function parse_iso8601( $date ) {
		if ( ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})T\d{2}:\d{2}:\d{2}/', $date, $m ) ) {
			return null;
		}
		// Same Gregorian guard as the plain Y-m-d path.
		if ( ! self::is_gregorian_date( (int) $m[1], (int) $m[2], (int) $m[3] ) ) {
			return null;
		}
		$ts = strtotime( $date );
		return false === $ts ? null : (int) $ts;
	}

	/**
	 * Normalize a time to the H:i:s value stored in `{prefix}drplus_booking`.`start_time`.
	 *
	 * Accepts H:i:s, H:i (normalized to H:i:s), or milliseconds since midnight (`raw_time`).
	 * Deliberately does NOT fall back to strtotime(): a bare "1405" would become "14:05:00"
	 * and silently query the wrong hour.
	 *
	 * Implements: spec 1.4, 2.8, 3.3, Matrix B
	 *
	 * @param string|int $time Time string or ms-since-midnight (raw_time).
	 * @return string H:i:s or empty string when invalid.
	 */
	public static function normalize_time( $time ) {
		$time = trim( (string) $time );
		if ( '' === $time ) {
			return '';
		}
		if ( is_numeric( $time ) ) {
			return self::time_from_milliseconds( (int) $time );
		}
		// Anchor an explicit HH[:MM[:SS]] value. This is what makes `15:00` match the stored `15:00:00`.
		if ( preg_match( '/^(\d{1,2}):(\d{2})(?::(\d{2}))?(?:\.\d+)?$/', $time, $m ) ) {
			$hour   = (int) $m[1];
			$minute = (int) $m[2];
			$second = isset( $m[3] ) ? (int) $m[3] : 0;
			if ( $hour > 23 || $minute > 59 || $second > 59 ) {
				return '';
			}
			return sprintf( '%02d:%02d:%02d', $hour, $minute, $second );
		}
		// A bare number is NOT a time: strtotime() turns "1405" into 14:05:00, and a Jalali year
		// or a leftover counter must never be mistaken for a slot.
		return '';
	}

	/**
	 * Convert a milliseconds-since-midnight value (`raw_time`) to H:i:s.
	 *
	 * The parent stores `raw_time` as `strtotime( '1970-01-01 ' . $start_time ) * 1000`, i.e. a
	 * ms count in [0, 86400000). An earlier `>= 86400000` guard therefore rejected EVERY real
	 * value (all 13 distinct values in the live dump are < 24h) and the slot could never match.
	 *
	 * A value is accepted when it is a whole number of seconds within one day. Anything else
	 * (e.g. a Jalali year "1405", a stray counter) is rejected instead of being mistaken for a slot.
	 *
	 * @param int $ms Milliseconds since midnight.
	 * @return string H:i:s or empty string.
	 */
	protected static function time_from_milliseconds( $ms ) {
		$ms = (int) $ms;
		if ( $ms <= 0 ) {
			return '';
		}
		// A real slot is at least 00:01:00 (60000 ms) and strictly before the next midnight.
		if ( $ms < 60000 || $ms >= 86400000 ) {
			return '';
		}
		// raw_time is always a whole second converted to ms; reject anything else as noise.
		if ( 0 !== $ms % 1000 ) {
			return '';
		}
		return gmdate( 'H:i:s', ( $ms / 1000 ) % 86400 );
	}

	/**
	 * Count distinct specialists with active in-person bookings for a slot.
	 * No transaction, no locks — the caller is responsible for locking (spec 4.17).
	 *
	 * Implements: spec 1.16, 4.17
	 *
	 * @param string $office_id Clinic id.
	 * @param string $date      Y-m-d.
	 * @param string $start     Start time H:i:s.
	 * @return int Distinct active specialist count.
	 */
	public static function count_distinct_specialists( string $office_id, string $date, string $start ): int {
		$raw_date = $date;
		$date     = self::assert_queryable_date( $date );
		if ( '' === $date ) {
			DrPlus_Child_Capacity_Logger::diag( 'assert_queryable_date REJECTED date: ' . wp_json_encode( $raw_date ) . ' -> returning ' . self::UNRESOLVED_DATE_COUNT . ' (no SQL issued)' );
			return self::UNRESOLVED_DATE_COUNT;
		}

		// Bug D: `14:00` must become `14:00:00` before it is compared to the TIME column, or the
		// comparison matches nothing, the hour reads as empty, and the booking is allowed.
		$raw_start = $start;
		$start     = self::normalize_time( $start );
		if ( '' === $start ) {
			DrPlus_Child_Capacity_Logger::diag( 'count_distinct_specialists REJECTED unusable time ' . wp_json_encode( $raw_start ) . ' -> returning ' . self::UNRESOLVED_DATE_COUNT );
			return self::UNRESOLVED_DATE_COUNT;
		}

		global $wpdb;
		$statuses = self::EXCLUDED_STATUSES;

		// The parent stores the SAME physical clinic under several identifiers: a numeric
		// hospital post id (614) and one or more per-specialist UUID "custom office" ids that
		// merely repeat that clinic's name and phone. Counting `office_id = '614'` alone misses
		// every booking filed under a UUID, so two rooms read as empty and a third specialist is
		// accepted. Count every identifier that resolves to the same clinic (Bug B).
		$clinic_ids = self::resolve_clinic_identifiers( $office_id );
		DrPlus_Child_Capacity_Logger::diag( 'clinic "' . $office_id . '" resolves to ' . count( $clinic_ids ) . ' identifier(s): ' . implode( ', ', $clinic_ids ) );

		$in           = implode( ',', array_fill( 0, count( $clinic_ids ), '%s' ) );
		$placeholders = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );
		$count        = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT specialist_id) FROM " . self::booking_table() .
				" WHERE office_id IN ({$in}) AND `date` = %s AND start_time = %s AND deleted = 0 AND " . self::active_status_predicate( $placeholders ),
				array_merge( $clinic_ids, array( $date, $start ), $statuses )
			)
		);
		return (int) $count;
	}

	/**
	 * Expand one office identifier into every identifier that denotes the same physical clinic.
	 *
	 * WHY THIS IS NEEDED (Bug B — the "3 bookings" bug)
	 *
	 * `drplus_booking.office_id` holds two different kinds of value: a numeric hospital post id
	 * (`614`) or a per-specialist "custom office" UUID
	 * (`1b9d72ba-f2b2-4dfd-8a3c-5b0f13c5c650`). In the live data four UUIDs and post 614 all
	 * denote the same clinic («کلینیک آغازگر», phone 09932115061). Counting the single id that
	 * happens to be in the booking therefore lets a 2-room clinic accept a 3rd booking.
	 *
	 * WHY THERE IS NO AUTOMATIC JOIN (verified against the live dump — do not "fix" this by
	 * guessing)
	 *
	 *  - `{prefix}drplus_specialists`.`offices` stores those UUIDs as `"type":"custom"` with no
	 *    hospital / parent / clinic field of any kind.
	 *  - The hospital's own entry in that same blob is `{"type":"hospital","id":"614","main":true}`
	 *    — it carries NO name and NO phone, so it cannot be matched to a UUID by identity.
	 *  - `{prefix}drplus_specialist_hospitals_rel` (user_id -> hospital_id) has NO row for the
	 *    four specialists who own the UUID offices.
	 *  - `{prefix}drplus_times.office` lists (user_id, office) pairs, but the same specialist's
	 *    UUID is filed under a DIFFERENT hospital, so it is not a trustworthy mapping either.
	 *  - Post 614 has no postmeta at all; its phone appears only in free-text post content,
	 *    which is shared verbatim with ~60 other posts — useless as a key.
	 *
	 * So the mapping is genuinely absent from the data. Rather than guess (a wrong guess merges
	 * two real clinics and blocks legitimate bookings), aliases come from an explicit option:
	 *
	 *     update_option( 'drplus_capacity_clinic_aliases', array(
	 *         '1b9d72ba-f2b2-4dfd-8a3c-5b0f13c5c650' => '614',
	 *         'bfafd267-63c7-4a5b-95d8-02e2690cd7aa' => '614',
	 *     ) );
	 *
	 * Each id in the group is also accepted as a key so resolution works from either side. The
	 * option is read once per request. With no option configured the behaviour is exactly the
	 * previous one (the single id), so this can never make an existing site over-block.
	 *
	 * @param string $office_id Office identifier from the booking.
	 * @return string[] Non-empty list of identifiers, always including $office_id.
	 */
	public static function resolve_clinic_identifiers( $office_id ) {
		$office_id = trim( (string) $office_id );
		if ( '' === $office_id ) {
			return array( '' );
		}

		// normalize_to_clinic: every identifier for one physical clinic collapses to a single
		// count set, so a clinic is never counted two ways depending on which entry point the
		// customer happened to book through.
		$map = self::load_clinic_aliases();

		$key   = strtolower( $office_id );
		$group = isset( $map[ $key ] ) ? $map[ $key ] : array();

		$ids = array();
		foreach ( array_merge( array( $office_id ), $group ) as $id ) {
			$id = trim( (string) $id );
			if ( '' !== $id && ! in_array( $id, $ids, true ) ) {
				$ids[] = $id;
			}
		}

		if ( count( $ids ) > 1 ) {
			DrPlus_Child_Capacity_Logger::diag( 'clinic "' . $office_id . '" expands to: ' . implode( ', ', $ids ) );
		}

		return $ids;
	}

	/**
	 * Build the lowercased alias => [ids] lookup from the configured option.
	 *
	 * Accepts either a flat map (`uuid => clinic`) or a list of groups
	 * (`array( array( '614', 'uuid-a' ) )`), because both are natural ways to write it by hand.
	 *
	 * @return array<string,string[]>
	 */
	protected static function load_clinic_aliases() {
		$raw = get_option( 'drplus_capacity_clinic_aliases', array() );
		if ( ! is_array( $raw ) || empty( $raw ) ) {
			return array();
		}

		$groups = array();

		// Shape 1: list of groups.
		if ( isset( $raw[0] ) && is_array( $raw[0] ) ) {
			foreach ( $raw as $group ) {
				$groups[] = array_values( array_filter( array_map( 'strval', (array) $group ), 'strlen' ) );
			}
		} else {
			// Shape 2: id => clinic (or clinic => id); collect into groups by target.
			$by_target = array();
			foreach ( $raw as $from => $to ) {
				$from = (string) $from;
				$to   = is_array( $to ) ? array_map( 'strval', $to ) : array( (string) $to );
				foreach ( $to as $target ) {
					$by_target[ $target ][] = $from;
				}
			}
			foreach ( $by_target as $target => $members ) {
				$members[] = (string) $target;
				$groups[]  = array_values( array_unique( $members ) );
			}
		}

		$map = array();
		foreach ( $groups as $group ) {
			if ( count( $group ) < 2 ) {
				continue;
			}
			foreach ( $group as $id ) {
				$map[ strtolower( $id ) ] = $group;
			}
		}
		return $map;
	}

	/**
	 * Does this specialist already hold a booking at this exact date + hour?
	 *
	 * WHY THIS IS NOT COVERED BY THE ROOM COUNT (Bug C)
	 *
	 * Capacity is counted as COUNT(DISTINCT specialist_id). When specialist 11 books 14:00 they
	 * occupy room 1, but the distinct count for that hour is 1. If the SAME specialist 11 books
	 * 14:00 again the count is STILL 1, so a 2-room clinic never rejects it. The customer gets a
	 * confirmation, the specialist cannot physically be in both, and the slot is silently wasted.
	 *
	 * Deliberately office-agnostic: a specialist cannot be in two rooms at once regardless of
	 * which room is requested, so the check ignores office_id. Online consultations never reach
	 * here (the validator returns early for them).
	 *
	 * FAILS CLOSED: an unresolvable specialist/date/time returns TRUE (conflict) so the booking
	 * is refused rather than allowed through on an unverifiable slot.
	 *
	 * @param int    $specialist_id Specialist (post) id from the booking payload.
	 * @param string $date          Gregorian Y-m-d.
	 * @param string $start         Start time, normalized to H:i:s.
	 * @return bool True when a conflicting booking exists.
	 */
	public static function specialist_already_booked( $specialist_id, $date, $start ) {
		return self::specialist_conflict_in_scope( $specialist_id, $date, $start, '' );
	}

	/**
	 * Is this specialist busy at this hour anywhere — or, when scoped, at this clinic?
	 *
	 * TWO DIFFERENT QUESTIONS, AND CONFLATING THEM IS THE BUG
	 *
	 * The parent's own slot generator (utils-booking.php:445-457) asks "is this specialist busy
	 * at THIS office at this hour?" — its WHERE is specialist_id AND office_id AND date. Our
	 * filter_full_slots() runs after that and speaks for the same screen, so it must answer the
	 * same question. When it applies a BROADER conflict rule (specialist busy anywhere), it hides
	 * slots the parent just offered, and — worse — the checkout then rejects the same hour with
	 * «این متخصص در این ساعت قبلاً رزرو دارد» for a booking the customer can see nowhere.
	 *
	 * That is exactly the reported symptom. A specialist who is booked at one clinic is not
	 * necessarily unavailable at another (different rooms, different addresses); the parent
	 * treats clinics as independent, and so must the display layer.
	 *
	 * The checkout validator keeps the broad rule on purpose (see validate_capacity): a
	 * specialist physically cannot be in two clinics at once, so a cross-clinic clash must
	 * still be refused. It just must not be *silent* — which is why the slot list hides it
	 * instead of offering it. This split keeps both guarantees:
	 *
	 *   display  → scoped to the clinic being booked, so the grid matches the parent's grid
	 *   checkout → clinic-agnostic, so a double-booking is impossible
	 *
	 * @param int    $specialist_id Specialist id.
	 * @param string $date          Y-m-d.
	 * @param string $start         H:i or H:i:s.
	 * @param string $office_id     Clinic to scope to. '' = anywhere (checkout behaviour).
	 * @return bool True when a conflicting booking exists.
	 */
	public static function specialist_conflict_in_scope( $specialist_id, $date, $start, $office_id = '' ) {
		$specialist_id = (int) $specialist_id;
		$date          = self::assert_queryable_date( $date );
		$start         = self::normalize_time( $start );
		$office_id     = trim( (string) $office_id );

		if ( $specialist_id <= 0 || '' === $date || '' === $start ) {
			// Cannot prove the slot, so it must not be booked. But "I cannot verify this" is
			// NOT the same as "this specialist already has a booking", and saying the latter
			// when the table is empty is a lie the customer cannot act on.
			//
			// This is exactly the reported bug: a booking arrived with specialist_id = 0, the
			// old code returned TRUE here, and the customer was told «این متخصص در این ساعت
			// قبلاً رزرو دارد» for an hour with zero bookings. Every slot on the page failed
			// the same way, so the whole day looked booked.
			//
			// Still refuse — returning FALSE would let an unverifiable slot through, which is
			// the overbooking bug this module exists to prevent. The difference is that the
			// caller must now describe the real problem. last_unverifiable_reason() carries
			// which input was missing so the validator can pick an honest message.
			self::$last_unverifiable = ( $specialist_id <= 0 )
				? 'specialist'
				: ( '' === $date ? 'date' : 'time' );

			DrPlus_Child_Capacity_Logger::diag( 'specialist_already_booked UNVERIFIABLE (specialist=' . $specialist_id . ' date=' . $date . ' time=' . $start . ') reason=' . self::$last_unverifiable . ' -> refusing, but NOT reporting a conflict' );
			return true;
		}

		self::$last_unverifiable = '';

		global $wpdb;
		$statuses     = self::EXCLUDED_STATUSES;
		$placeholders = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );
		$sql          = "SELECT COUNT(*) FROM " . self::booking_table() .
			" WHERE specialist_id = %d AND `date` = %s AND start_time = %s AND deleted = 0 AND " . self::active_status_predicate( $placeholders );
		$bind         = array_merge( array( $specialist_id, $date, $start ), $statuses );

		if ( '' !== $office_id ) {
			// Same clinic-alias expansion as the capacity counters: a clinic can be booked under
			// its numeric post id and under per-specialist UUIDs, and a conflict filed under one
			// of those must be found when the customer picked the other (Bug B).
			$clinic_ids = self::resolve_clinic_identifiers( $office_id );
			$in         = implode( ',', array_fill( 0, count( $clinic_ids ), '%s' ) );
			$sql       .= " AND office_id IN ({$in})";
			$bind       = array_merge( $bind, $clinic_ids );
		}

		$conflicts = $wpdb->get_var( $wpdb->prepare( $sql, $bind ) );
		return ( (int) $conflicts ) > 0;
	}

	/**
	 * Last line of defense: guarantee the value bound to `date = %s` is a real Gregorian date.
	 *
	 * Every caller normalizes before it gets here, so this normally passes the value through
	 * untouched. It exists because that is an invariant held by convention, not by the type
	 * system, and count_distinct_specialists() is public.
	 *
	 * Deliberately NOT a converter: this layer cannot know whether `1405-08-28` is Jalali, a
	 * typo, or a truncated timestamp, and guessing is how the original bug was introduced.
	 *
	 * @param string $date Candidate date.
	 * @return string Valid Gregorian Y-m-d, or '' when the value must not be queried.
	 */
	protected static function assert_queryable_date( $date ) {
		$date = trim( (string) $date );
		if ( '' === $date ) {
			return '';
		}

		// A 3- or 4-digit leading year outside the Gregorian era this site can have data for.
		// Catches 1405-08-28, 1405-8-28, 1405/08/28 and 1405.08.28 (note: no fixed-length
		// assumption — a `strlen() === 10` check misses the single-digit-month variants).
		if ( preg_match( '/^(\d{3,4})[-\/.]\d{1,2}[-\/.]\d{1,2}/', $date, $m ) ) {
			$year = (int) $m[1];
			if ( $year < 1900 || $year > 2200 ) {
				return '';
			}
		}

		// Re-normalize so only a canonical, calendar-valid Gregorian Y-m-d reaches SQL.
		$parsed = self::parse_ymd( $date );
		if ( null === $parsed ) {
			return '';
		}
		list( $year, $month, $day ) = $parsed;
		if ( ! self::is_gregorian_date( $year, $month, $day ) ) {
			return '';
		}
		return sprintf( '%04d-%02d-%02d', $year, $month, $day );
	}

	/**
	 * Batch-count all hours of one day in ONE query — no N+1 (spec 1.13, 2.7, 8.6, 5.6.89).
	 *
	 * Implements: spec 1.13, 2.7
	 *
	 * @param string $office_id Clinic id.
	 * @param string $date      Y-m-d.
	 * @return array<string,int> Map of 'H:i:s' => distinct specialist count.
	 */
	public static function count_day_slots( $office_id, $date ) {
		$date = self::assert_queryable_date( $date );
		if ( '' === $date ) {
			// Cannot prove the date is Gregorian, so no count can be trusted. Return an empty
			// map; the caller decides how to present the day. (Enforcement does not rely on
			// this: count_distinct_specialists() returns UNRESOLVED_DATE_COUNT and the booking
			// is refused.)
			return array();
		}

		global $wpdb;
		$statuses = self::EXCLUDED_STATUSES;

		// Same clinic-alias expansion as count_distinct_specialists(): if the dropdown counted
		// only the exact office_id it was handed, it would offer hours the validator then
		// rejects (or hide hours that are in fact free) whenever a clinic is split across a
		// numeric id and custom-office UUIDs (Bug B).
		$clinic_ids = self::resolve_clinic_identifiers( $office_id );

		$in           = implode( ',', array_fill( 0, count( $clinic_ids ), '%s' ) );
		$placeholders = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );
		$sql = $wpdb->prepare(
			"SELECT start_time, COUNT(DISTINCT specialist_id) AS cnt FROM " . self::booking_table() .
			" WHERE office_id IN ({$in}) AND `date` = %s AND deleted = 0 AND " . self::active_status_predicate( $placeholders ) . " GROUP BY start_time",
			array_merge( $clinic_ids, array( $date ), $statuses )
		);
		$rows  = $wpdb->get_results( $sql, ARRAY_A );
		$map   = array();
		foreach ( (array) $rows as $row ) {
			$map[ $row['start_time'] ] = (int) $row['cnt'];
		}
		return $map;
	}

	/**
	 * Every hour of one day at which this specialist already holds a live booking — ONE query.
	 *
	 * Serves the "Select Time" grid: a slot the specialist cannot take must never be shown,
	 * because the customer only discovers the clash after filling in the whole form.
	 *
	 * Scoped to the clinic being booked, matching the parent's own booked-range query
	 * (utils-booking.php:445-457: specialist_id AND office_id AND date). Scope matters — see
	 * specialist_conflict_in_scope() for why a cross-clinic booking must not shape this grid.
	 *
	 * WHY BATCHED RATHER THAN PER-SLOT
	 *
	 * `specialist_already_booked()` per slot would issue one query per rendered hour: a 12-hour
	 * clinic day becomes 12 round trips on every date change, on a page the customer refreshes
	 * while picking a time. One grouped query returns the same answer (spec 1.13, 2.7: no N+1).
	 *
	 * Unlike count_day_slots(), a date that cannot be proven Gregorian does NOT return an empty
	 * map. Empty would mean "specialist is free all day" and repaint a full schedule as
	 * bookable. The caller gets an empty map plus its own failure to act on — see
	 * filter_full_slots(), which returns no slots at all in that case.
	 *
	 * @param int    $specialist_id Specialist id.
	 * @param string $date          Y-m-d.
	 * @param string $office_id     Clinic being booked (scope), '' = any clinic.
	 * @return array<string,bool> Map of 'H:i:s' => true for hours the specialist already holds.
	 */
	public static function specialist_booked_slots_for_day( $specialist_id, $date, $office_id = '' ) {
		$specialist_id = (int) $specialist_id;
		$date          = self::assert_queryable_date( $date );
		$office_id     = trim( (string) $office_id );

		if ( $specialist_id <= 0 || '' === $date ) {
			return array();
		}

		global $wpdb;
		$statuses     = self::EXCLUDED_STATUSES;
		$placeholders = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );

		$sql  = "SELECT start_time FROM " . self::booking_table() .
			" WHERE specialist_id = %d AND `date` = %s AND deleted = 0 AND " . self::active_status_predicate( $placeholders );
		$bind = array_merge( array( $specialist_id, $date ), $statuses );

		if ( '' !== $office_id ) {
			// Same clinic-alias expansion as every other counter here: the booking may be filed
			// under the clinic's numeric id or under a per-specialist UUID (Bug B).
			$clinic_ids = self::resolve_clinic_identifiers( $office_id );
			$in         = implode( ',', array_fill( 0, count( $clinic_ids ), '%s' ) );
			$sql       .= " AND office_id IN ({$in})";
			$bind       = array_merge( $bind, $clinic_ids );
		}

		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $bind ), ARRAY_A );
		$busy = array();
		foreach ( (array) $rows as $row ) {
			$key = self::normalize_time( isset( $row['start_time'] ) ? $row['start_time'] : '' );
			if ( '' !== $key ) {
				$busy[ $key ] = true;
			}
		}
		return $busy;
	}


}
