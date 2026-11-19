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
	 * Memoised office-identity index (see office_identity_index()).
	 *
	 * @var array|null
	 */
	protected static $office_identity_index = null;

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
		// Rows, not COUNT(DISTINCT ...): the copied `order_status` column is a cache of the
		// WooCommerce order, and a stale cache row would either hide a room that is free or
		// free a room that is taken. A single slot holds only a handful of rows, so loading
		// them costs no more than the aggregate did, and it lets row_holds_slot() ask the
		// order before trusting the column.
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT specialist_id, order_status, order_id FROM " . self::booking_table() .
				" WHERE office_id IN ({$in}) AND `date` = %s AND start_time = %s AND deleted = 0 AND " . self::active_status_predicate( $placeholders ),
				array_merge( $clinic_ids, array( $date, $start ), $statuses )
			),
			ARRAY_A
		);

		$holds = array();
		foreach ( (array) $rows as $row ) {
			if ( self::row_holds_slot( $row ) ) {
				$holds[] = (int) $row['specialist_id'];
			} elseif ( isset( $row['order_id'] ) && (int) $row['order_id'] > 0 ) {
				DrPlus_Child_Capacity_Logger::diag( sprintf(
					'slot %s %s: specialist %d freed — order #%d no longer holds it (stale column "%s")',
					$date,
					$start,
					(int) $row['specialist_id'],
					(int) $row['order_id'],
					isset( $row['order_status'] ) ? $row['order_status'] : ''
				) );
			}
		}

		// Deliberately deduplicated in PHP: COUNT(DISTINCT specialist_id) counted a specialist
		// once even if they held two rows, and the capacity arithmetic depends on that.
		return count( array_unique( $holds ) );
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
		//
		// TWO SOURCES, IN THIS ORDER (the automatic one comes first on purpose)
		//
		//  1. The DATA. `{prefix}drplus_specialists`.`offices` records, for every custom
		//     office UUID, the clinic's own `name` and `phone`. Two UUIDs carrying the same
		//     (name, phone) pair are the same physical clinic — that is what the operator
		//     typed when they created each one. This needs no configuration and covers
		//     every UUID, including ones created after the manual map was written.
		//     (The column is double-encoded: a JSON string inside the value, so it has to
		//     be decoded twice. Failing to do that is exactly why an earlier version
		//     concluded "the mapping is absent from the data".)
		//
		//  2. The configured option, which can still join things the data cannot — a
		//     custom office and the numeric hospital post for the same clinic.
		//
		// Both are additive: the returned list always contains $office_id, so this can
		// never make an existing site block more than it did.
		$map = self::load_clinic_aliases();

		$key   = strtolower( $office_id );
		$group = isset( $map[ $key ] ) ? $map[ $key ] : array();

		// Union of the configured group and every identifier the data says is the same clinic.
		$group = array_values( array_unique( array_merge(
			(array) $group,
			self::identifiers_from_office_identity( $office_id )
		) ) );

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
	 * Every office identifier that the site's own data says belongs to the same clinic as
	 * $office_id, derived from the (name, phone) pair recorded on each custom office.
	 *
	 * WHY THIS EXISTS
	 *
	 * When an operator adds a "custom office" for a specialist, the parent theme stores a
	 * JSON blob in `{prefix}drplus_specialists`.`offices` keyed by a fresh UUID, and copies
	 * the clinic name / phone / address they typed. Booking a second specialist into the
	 * same clinic creates a SECOND UUID with an identical name and phone. Those UUIDs are
	 * one clinic with one waiting room, but `drplus_booking.office_id` only ever holds the
	 * one UUID that was used, so each UUID was counted as its own clinic.
	 *
	 * The consequence was the reported ticket: specialist 12 held a booking under UUID
	 * `32c836b9…`, the checkout correctly refused a second one, and the grid — which only
	 * looked at the UUID the customer was on — offered the hour anyway. The customer saw a
	 * bookable slot that could never be booked.
	 *
	 * IDENTITY RULE
	 *
	 * Same normalized name AND same digits-only phone. Both are required: a name alone is
	 * ambiguous (two clinics can share a name in different cities) and a phone alone is not
	 * enough either (a shared switchboard). Requiring both keeps the join conservative,
	 * which matters because a wrong merge blocks real bookings.
	 *
	 * A numeric hospital post id is included when its own `_phones` postmeta carries the
	 * same number, which is how a UUID joins its hospital post without any configuration.
	 *
	 * Memoised per request: the table is small and this runs on every slot filter.
	 *
	 * @param string $office_id Office identifier.
	 * @return string[] Additional identifiers (may be empty).
	 */
	public static function identifiers_from_office_identity( $office_id ) {
		$office_id = trim( (string) $office_id );
		if ( '' === $office_id ) {
			return array();
		}

		$by_key = self::office_identity_index();
		if ( empty( $by_key ) ) {
			return array();
		}

		$key = strtolower( $office_id );
		if ( ! isset( $by_key['id'][ $key ] ) ) {
			// The identifier itself may be the numeric clinic post: resolve it through its phone.
			$as_post = self::identity_key_for_hospital_post( $office_id );
			if ( '' === $as_post || ! isset( $by_key[ $as_post ] ) ) {
				return array();
			}
			return $by_key[ $as_post ];
		}

		$identity = $by_key['id'][ $key ];
		return isset( $by_key[ $identity ] ) ? $by_key[ $identity ] : array();
	}

	/**
	 * Build (once per request) the two indexes used by identifiers_from_office_identity().
	 *
	 *  - ['id'][lowercased identifier] => identity key
	 *  - [identity key]                => [identifiers...]
	 *
	 * @return array
	 */
	protected static function office_identity_index() {
		if ( null !== self::$office_identity_index ) {
			return self::$office_identity_index;
		}

		global $wpdb;
		self::$office_identity_index = array( 'id' => array() );

		$rows = $wpdb->get_results(
			"SELECT user_id, offices FROM " . $wpdb->prefix . "drplus_specialists",
			ARRAY_A
		);
		if ( ! is_array( $rows ) ) {
			return self::$office_identity_index;
		}

		foreach ( $rows as $row ) {
			$offices = maybe_unserialize( isset( $row['offices'] ) ? $row['offices'] : '' );
			// The parent stores this column DOUBLE-ENCODED (a JSON string inside the value),
			// so a single unserialize leaves a string behind.
			if ( is_string( $offices ) ) {
				$decoded = json_decode( $offices, true );
				if ( is_string( $decoded ) ) {
					$decoded = json_decode( $decoded, true );
				}
				$offices = $decoded;
			}
			if ( ! is_array( $offices ) ) {
				continue;
			}
			foreach ( $offices as $identifier => $office ) {
				if ( ! is_array( $office ) ) {
					continue;
				}
				$type = isset( $office['type'] ) ? (string) $office['type'] : '';
				if ( 'custom' !== $type ) {
					continue;
				}
				$key = self::identity_key( isset( $office['name'] ) ? $office['name'] : '', isset( $office['phone'] ) ? $office['phone'] : '' );
				if ( '' === $key ) {
					continue;
				}
				self::index_identifier( (string) $identifier, $key );
			}
		}

		// Join numeric hospital posts to the custom offices that carry the same phone number.
		// A hospital post stores its numbers in `_phones` meta and carries no single "name"
		// field comparable to a custom office, so the phone is the shared key here: for every
		// custom-office identity whose phone matches, add the post id to that same group.
		$hospitals = $wpdb->get_results(
			"SELECT p.ID, pm.meta_value
			 FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = '_phones'
			 WHERE p.post_type = 'hospital'",
			ARRAY_A
		);
		foreach ( (array) $hospitals as $hospital ) {
			$post_id = isset( $hospital['ID'] ) ? (string) $hospital['ID'] : '';
			if ( '' === $post_id ) {
				continue;
			}
			$phones = maybe_unserialize( $hospital['meta_value'] );
			if ( is_string( $phones ) ) {
				$phones = maybe_unserialize( $phones );
			}
			$wanted = array();
			foreach ( (array) $phones as $entry ) {
				$number = is_array( $entry ) && isset( $entry['phone'] ) ? $entry['phone'] : '';
				$digits = self::identity_phone( $number );
				if ( '' !== $digits ) {
					$wanted[ $digits ] = true;
				}
			}
			if ( empty( $wanted ) ) {
				continue;
			}
			// Any custom-office group whose phone matches this hospital joins it.
			foreach ( self::$office_identity_index as $identity => $members ) {
				if ( 'id' === $identity || ! is_array( $members ) ) {
					continue;
				}
				$pipe = strrpos( $identity, '|' );
				if ( false === $pipe ) {
					continue;
				}
				$phone = substr( $identity, $pipe + 1 );
				if ( isset( $wanted[ $phone ] ) ) {
					self::index_identifier( $post_id, $identity );
				}
			}
		}

		return self::$office_identity_index;
	}

	/**
	 * Record one identifier under one identity key.
	 *
	 * @param string $identifier Office identifier.
	 * @param string $key        Identity key.
	 * @return void
	 */
	protected static function index_identifier( $identifier, $key ) {
		$identifier = trim( (string) $identifier );
		if ( '' === $identifier ) {
			return;
		}
		$lower = strtolower( $identifier );
		self::$office_identity_index['id'][ $lower ] = $key;
		if ( ! isset( self::$office_identity_index[ $key ] ) ) {
			self::$office_identity_index[ $key ] = array();
		}
		if ( ! in_array( $identifier, self::$office_identity_index[ $key ], true ) ) {
			self::$office_identity_index[ $key ][] = $identifier;
		}
	}

	/**
	 * Identity key for a custom office: normalized name + digits-only phone.
	 *
	 * Returns '' when either half is missing, because half an identity is not an identity.
	 *
	 * @param string $name  Clinic name as typed by the operator.
	 * @param string $phone Clinic phone as typed by the operator.
	 * @return string
	 */
	protected static function identity_key( $name, $phone ) {
		$name   = self::normalize_identity_text( $name );
		$digits = self::identity_phone( $phone );
		if ( '' === $name || '' === $digits ) {
			return '';
		}
		return $name . '|' . $digits;
	}

	/**
	 * Digits-only phone, so `025-32427870` and `02532427870` are the same number.
	 *
	 * @param string $phone Raw phone.
	 * @return string
	 */
	protected static function identity_phone( $phone ) {
		return preg_replace( '/\D+/', '', (string) $phone );
	}

	/**
	 * Normalize a clinic name: trim, collapse whitespace, drop ZWNJ, lowercase.
	 *
	 * Persian names are frequently retyped with a different half-space (U+200C) or with
	 * different spacing, which would otherwise defeat an exact comparison.
	 *
	 * @param string $text Raw name.
	 * @return string
	 */
	protected static function normalize_identity_text( $text ) {
		$text = (string) $text;
		$text = str_replace( array( "\xE2\x80\x8C", "\u{200C}" ), '', $text );
		$text = preg_replace( '/\s+/u', ' ', $text );
		return function_exists( 'mb_strtolower' ) ? mb_strtolower( trim( $text ), 'UTF-8' ) : strtolower( trim( $text ) );
	}

	/**
	 * Identity key for a numeric hospital post, from its `_phones` meta.
	 *
	 * @param string $post_id Hospital post id.
	 * @return string Identity key (digits only) or ''.
	 */
	protected static function identity_key_for_hospital_post( $post_id ) {
		if ( ! ctype_digit( (string) $post_id ) ) {
			return '';
		}
		$index = self::office_identity_index();
		$lower = strtolower( (string) $post_id );
		return isset( $index['id'][ $lower ] ) ? $index['id'][ $lower ] : '';
	}

	/**
	 * Forget the memoised office-identity index. Used by tests and after a specialist's
	 * offices are edited in wp-admin.
	 *
	 * @return void
	 */
	public static function flush_office_identity_cache() {
		self::$office_identity_index = null;
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
		// EXEMPT ONLINE VISITS MUST NOT BLOCK A ROOM.
		//
		// A phone/chat/video consultation never occupies a physical room, so it must not make
		// the specialist read as "already booked" for an in-person hour — and the reverse: an
		// in-person booking must not block the specialist from taking a call at the same time.
		//
		// Without this clause, `specialist_already_booked()` (which passes office_id = '' to stay
		// room-agnostic) happily matched a `chat_consultation` row and refused a perfectly free
		// room. That contradicts the documented rule that online visits are capacity-exempt, and
		// it silently costs the clinic every hour a specialist happens to be taking calls.
		$online = DrPlus_Child_Capacity_Service::virtual_office_ids();
		$sql          = "SELECT book_id, order_status, order_id FROM " . self::booking_table() .
			" WHERE specialist_id = %d AND `date` = %s AND start_time = %s AND deleted = 0 AND " . self::active_status_predicate( $placeholders );
		$bind         = array_merge( array( $specialist_id, $date, $start ), $statuses );

		// In 'any' mode a virtual visit IS a conflict for a room (and the other way round),
		// so the exclusion below must not run. In the default 'room' mode it must.
		if ( DrPlus_Child_Capacity_Service::virtual_visit_blocks_room() ) {
			DrPlus_Child_Capacity_Logger::diag( 'conflict_mode=any: a virtual visit counts as a conflict for this hour' );
		} elseif ( empty( $office_id ) || ! in_array( $office_id, $online, true ) ) {
			// Unscoped: every physical office is in play, so exclude the virtual ones outright.
			// Scoped to a real room: a virtual-visit row is not a conflict for it. (When the
			// requested office IS virtual, the validator has already returned early.)
			$online_ph = implode( ',', array_fill( 0, count( $online ), '%s' ) );
			$sql      .= " AND office_id NOT IN ({$online_ph})";
			$bind      = array_merge( $bind, $online );
		}

		if ( '' !== $office_id && DrPlus_Child_Capacity_Service::virtual_visit_blocks_room() ) {
			// 'any' mode: the scope is the SPECIALIST, not the clinic, and that is true in BOTH
			// directions. A specialist is one person at one hour.
			//
			// Scoping by clinic here breaks the feature in the direction that is easiest to
			// miss. A virtual visit belongs to no clinic, so its office_id is 'phone_consultation'
			// while resolve_clinic_identifiers( <real clinic> ) returns just that clinic's ids.
			// The IN (...) clause then filtered the phone row OUT, and a specialist who was
			// already on a call at 10:00 was reported as free to take a room at 10:00 — the
			// exact double-booking this mode exists to prevent. Measured on the real dump:
			// conflict_in_scope( 6, '596' ) was false while specialist_already_booked( 6 ) was
			// true, i.e. the unscoped check saw the clash and the clinic-scoped one did not.
			DrPlus_Child_Capacity_Logger::diag( 'conflict_mode=any: office "' . $office_id . '" checked against every booking of this specialist (no clinic scope)' );
		} elseif ( '' !== $office_id ) {
			// Same clinic-alias expansion as the capacity counters: a clinic can be booked under
			// its numeric post id and under per-specialist UUIDs, and a conflict filed under one
			// of those must be found when the customer picked the other (Bug B).
			$clinic_ids = self::resolve_clinic_identifiers( $office_id );
			$in         = implode( ',', array_fill( 0, count( $clinic_ids ), '%s' ) );
			$sql       .= " AND office_id IN ({$in})";
			$bind       = array_merge( $bind, $clinic_ids );
		}

		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $bind ), ARRAY_A );
		if ( empty( $rows ) ) {
			return false;
		}

		// The SQL filtered on the copied column, but that column can lag behind the order.
		// Re-check each surviving row against its order: an order cancelled in WooCommerce
		// while the row still says "processing" must not keep this specialist locked out.
		foreach ( (array) $rows as $row ) {
			if ( self::row_holds_slot( $row ) ) {
				return true;
			}
			DrPlus_Child_Capacity_Logger::diag( 'specialist conflict avoided: book #' . ( isset( $row['book_id'] ) ? $row['book_id'] : '?' ) . ' no longer holds the slot (order status overrides stale column "' . ( isset( $row['order_status'] ) ? $row['order_status'] : '' ) . '")' );
		}

		return false;
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

		$sql  = "SELECT start_time, order_status, order_id FROM " . self::booking_table() .
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
			if ( '' === $key ) {
				continue;
			}
			// The copied column passed the SQL filter, but that column can be stale. Ask the
			// order before hiding the hour: a cancelled order whose row still says "processing"
			// would otherwise block this specialist forever, with no visible error.
			if ( ! self::row_holds_slot( $row ) ) {
				DrPlus_Child_Capacity_Logger::diag( sprintf(
					'book slot %s freed: the order no longer holds it (row said "%s")',
					$key,
					isset( $row['order_status'] ) ? $row['order_status'] : ''
				) );
				continue;
			}
			$busy[ $key ] = true;
		}
		return $busy;
	}

	/**
	 * Does this booking row still deserve to block, judged by its WOOCOMMERCE ORDER?
	 *
	 * WHY THIS EXISTS — the gap this module could not see
	 *
	 * Every count in this class reads `{prefix}drplus_booking`.`order_status`. That column is a
	 * COPY. The authoritative status lives on the WooCommerce order, and the copy is refreshed
	 * by the parent theme's order-status listener — which is ionCube-encoded
	 * (`inc/SaveBookingProcess.php`) and therefore cannot be read, audited, or trusted from
	 * here.
	 *
	 * A copy can go stale. When it does, the consequences are asymmetric and one direction is
	 * far worse than the other:
	 *
	 *   - order cancelled, copy still `processing`  →  the hour is blocked forever. Nobody can
	 *     book it, the specialist loses income, and no error is ever shown. This is exactly the
	 *     «قبلاً رزرو دارد» symptom with no live booking behind it.
	 *   - order alive, copy says cancelled  →  an overbooking hole.
	 *
	 * This method answers the second question from the first source: given a row, what does the
	 * ORDER say? A row whose order is cancelled/refunded/failed/trashed does not hold a slot no
	 * matter what the copied column says.
	 *
	 * Fails CLOSED in every uncertain case: no order id, an order that cannot be loaded, or a
	 * booking with no order at all (an admin-created appointment) all keep holding the slot.
	 * Releasing a room on a lookup failure would create the overbooking bug this module exists
	 * to prevent.
	 *
	 * @param int $order_id WooCommerce order id from the booking row (0 when unattached).
	 * @return bool True when the order is gone for good and the row must stop blocking.
	 */
	public static function order_releases_slot( $order_id ) {
		$order_id = (int) $order_id;
		if ( $order_id <= 0 ) {
			// No order: an appointment created by an admin, or a row from before orders existed.
			// It holds the slot — there is no second source to overrule it.
			return false;
		}

		if ( ! function_exists( 'wc_get_order' ) ) {
			return false;
		}

		// THE ORDER RECORD NO LONGER EXISTS.
		//
		// Two ways this happens, and they must be treated identically:
		//
		//   1. `wp_trash_post()` — the order moves to `trash` but its internal status column
		//      stays at `wc-processing`. WC_Order::get_status() therefore still reports
		//      "processing" even though the merchant has thrown the order away (verified on a
		//      real order). Trusting get_status() alone blocked that hour forever after a
		//      routine delete-from-the-orders-list.
		//   2. The row points at an order id that does not exist at all — a booking imported
		//      from an old system, or an order that was deleted with its posts purged.
		//
		// In BOTH cases nobody is coming to the appointment and no order can ever be paid or
		// completed for it, so the hour must come back. Deciding this from the post record
		// (which both paths share) keeps the two answers from diverging.
		$post_status = get_post_status( $order_id );
		if ( false === $post_status ) {
			DrPlus_Child_Capacity_Logger::diag( 'order #' . $order_id . ' does not exist — releasing the slot (no order can ever fill it)' );
			return true;
		}
		if ( 'trash' === $post_status ) {
			DrPlus_Child_Capacity_Logger::diag( 'order #' . $order_id . ' is trashed — releasing the slot' );
			return true;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			// The post exists but WooCommerce cannot build an order from it (wrong post type,
			// corrupt meta). That is genuinely unknown, so keep holding: failing open here
			// would release a room that may well be occupied.
			return false;
		}

		// A refund/fail/cancel on the order itself.
		// WC_Order::get_status() returns the bare status (`cancelled`), never `wc-cancelled`.
		return ! self::status_holds_slot( $order->get_status() );
	}

	/**
	 * Row-level predicate for "this row holds a slot", considering BOTH the copied column and
	 * the live order.
	 *
	 * The SQL counts cannot call into WooCommerce per row, so they use the column alone; this
	 * method is the reconcile path used where rows are already loaded (the day-slot map, the
	 * specialist conflict check, and the audit). Keeping the decision in one place means the
	 * two paths cannot drift apart in the direction that matters — releasing a room that is
	 * genuinely taken.
	 *
	 * @param array $row Booking row with `order_status` and `order_id`.
	 * @return bool True when the row still occupies its slot.
	 */
	public static function row_holds_slot( $row ) {
		$status = isset( $row['order_status'] ) ? $row['order_status'] : '';
		if ( ! self::status_holds_slot( $status ) ) {
			return false;
		}
		// The copied column says "active". Ask the order before believing it.
		$order_id = isset( $row['order_id'] ) ? $row['order_id'] : 0;
		return ! self::order_releases_slot( $order_id );
	}

	/**
	 * Count how many rows in this site's booking table are stale — the copied `order_status`
	 * disagrees with the WooCommerce order — and optionally repair them.
	 *
	 * WHY A REPAIR IS SAFE
	 *
	 * The order is authoritative; the column is a cache of it. Writing the order's own status
	 * back into the column therefore cannot invent a state: it copies the truth. This does not
	 * cancel, complete, or refund anything — it only teaches the cache what the order already
	 * says. No customer is notified and no money moves.
	 *
	 * `deleted = 1` rows are skipped: they are already excluded from every count.
	 *
	 * @param bool $repair When true, write the order status into the stale rows.
	 * @param int  $limit  Maximum rows to inspect (0 = no limit). Bounds a large site's runtime.
	 * @return array {
	 *     @type int   $inspected Rows examined.
	 *     @type int   $stale     Rows whose column disagrees with the order.
	 *     @type int   $repaired  Rows actually corrected (0 when $repair is false).
	 *     @type int   $unreadable Rows whose order could not be loaded.
	 *     @type array $examples  Up to 10 human-readable descriptions of stale rows.
	 * }
	 */
	public static function reconcile_order_statuses( $repair = false, $limit = 0 ) {
		global $wpdb;

		$summary = array(
			'inspected'  => 0,
			'stale'      => 0,
			'repaired'   => 0,
			'unreadable' => 0,
			'examples'   => array(),
		);

		if ( ! function_exists( 'wc_get_order' ) ) {
			return $summary;
		}

		$sql = 'SELECT book_id, order_id, order_status, `date`, start_time FROM ' . self::booking_table()
			. ' WHERE deleted = 0 AND order_id > 0 ORDER BY book_id';
		if ( (int) $limit > 0 ) {
			$sql .= ' LIMIT ' . (int) $limit;
		}

		$rows = $wpdb->get_results( $sql, ARRAY_A );
		foreach ( (array) $rows as $row ) {
			$summary['inspected']++;

			$order = wc_get_order( (int) $row['order_id'] );
			if ( ! $order ) {
				$summary['unreadable']++;
				continue;
			}

			$order_status = strtolower( trim( (string) $order->get_status() ) );
			$stored       = strtolower( trim( (string) $row['order_status'] ) );
			// Compare on the bare spelling: wc-cancelled and cancelled are the same status.
			$stored_bare  = ( 0 === strpos( $stored, 'wc-' ) ) ? substr( $stored, 3 ) : $stored;

			if ( $stored_bare === $order_status ) {
				continue;
			}

			$summary['stale']++;
			if ( count( $summary['examples'] ) < 10 ) {
				$summary['examples'][] = sprintf(
					'book #%d (order #%d): table says "%s", the order says "%s"',
					$row['book_id'],
					$row['order_id'],
					$stored,
					$order_status
				);
			}

			if ( ! $repair ) {
				continue;
			}

			// Write the order's status verbatim, including its wc- prefix when WooCommerce uses
			// one for storage in this install. active_status_predicate() tolerates both.
			$wpdb->update(
				self::booking_table(),
				array( 'order_status' => $order_status ),
				array( 'book_id' => (int) $row['book_id'] ),
				array( '%s' ),
				array( '%d' )
			);
			$summary['repaired']++;

			DrPlus_Child_Capacity_Logger::diag( sprintf(
				'reconciled book #%d: "%s" -> "%s" (from order #%d)',
				$row['book_id'],
				$stored,
				$order_status,
				$row['order_id']
			) );
		}

		return $summary;
	}


}
