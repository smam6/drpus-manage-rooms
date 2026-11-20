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
	 * Per-request cache of "does this order release its slot?" results.
	 *
	 * Building one day of the grid loads every booking row for that day and asks
	 * order_releases_slot() about each one. Without this cache that is one
	 * wc_get_order() + one get_post_status() per ROW, which on a busy clinic (320 rows
	 * measured) was 121 queries and ~420 ms for a single render — and the booking grid
	 * is on the critical path of every visit to the booking page.
	 *
	 * The answer for a given order cannot change within one request, so caching it is
	 * free of correctness risk. It is keyed by order id and reset with
	 * DrPlus_Child_Capacity_Repository::flush_order_cache() after any status write this
	 * module makes, so a hook that cancels an order mid-request is still observed.
	 *
	 * @var array<int,bool>
	 */
	protected static $order_release_cache = array();

	/**
	 * Per-request memo of resolve_clinic_identifiers(), keyed by lower-cased identifier.
	 *
	 * @var array<string,array>
	 */
	protected static $clinic_identifier_memo = array();

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

		// A COMPACT YYYYMMDD DATE IS A DATE, NOT AN EPOCH.
		//
		// This branch used to run for any numeric string, so `20271201` became the unix
		// timestamp 20,271,201 = 1970-08-23. That date is in the past, so the capacity
		// check found nothing there and allowed every booking — the silent-pass bug.
		//
		// Only the caller can say a value is genuinely a timestamp (raw_date), so that
		// stays explicit via $treat_as_number. An eight-digit value that forms a real
		// calendar date is read as one; anything else numeric keeps the old timestamp
		// behaviour, which raw_date relies on.
		if ( ! $treat_as_number && 8 === strlen( $date ) && ctype_digit( $date ) ) {
			$compact_year  = (int) substr( $date, 0, 4 );
			$compact_month = (int) substr( $date, 4, 2 );
			$compact_day   = (int) substr( $date, 6, 2 );
			if ( self::is_gregorian_date( $compact_year, $compact_month, $compact_day ) ) {
				return sprintf( '%04d-%02d-%02d', $compact_year, $compact_month, $compact_day );
			}
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
				// created_at is selected so the shared row_holds_slot() age guard can
				// release an abandoned checkout here too. Without it the room count keeps
				// a dead row in the total while checkout releases it — the two layers
				// would disagree about the same hour.
				"SELECT book_id, specialist_id, order_status, order_id, created_at FROM " . self::booking_table() .
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
	/**
	 * One stable identifier for a physical clinic, used as the key of the booking lock.
	 *
	 * The set returned by resolve_clinic_identifiers() describes every spelling that maps
	 * to the same clinic, but a lock needs a single deterministic name: whichever spelling
	 * arrives, the name must be byte-identical. Sorting the resolved set and joining it
	 * gives exactly that, while staying stable when the alias map later gains an entry
	 * (the set can only grow, so a lock taken before the change and one taken after would
	 * still differ — acceptable, because a booking in flight is short-lived and the worst
	 * case is one extra serialisation point, never a missed one).
	 *
	 * When nothing resolves, the raw identifier is returned so an unknown clinic keeps its
	 * own lock rather than colliding with every other unknown clinic.
	 *
	 * @param string $office_id Numeric clinic id or custom-office UUID.
	 * @return string
	 */
	public static function canonical_clinic_id( $office_id ) {
		$office_id = trim( (string) $office_id );
		if ( '' === $office_id ) {
			return '';
		}

		$identifiers = (array) self::resolve_clinic_identifiers( $office_id );
		$identifiers = array_filter( array_map( 'strval', $identifiers ), 'strlen' );
		if ( empty( $identifiers ) ) {
			return $office_id;
		}

		sort( $identifiers );

		return implode( ',', $identifiers );
	}

	public static function resolve_clinic_identifiers( $office_id ) {
		$office_id = trim( (string) $office_id );
		if ( '' === $office_id ) {
			return array( '' );
		}

		// MEMOISED. The alias map and the office-identity index do not change within one
		// request, but this method is called once per booking row while building the grid.
		// Measured on a rebuilt database: 9 identical `drplus_specialists` reads and 9
		// identical `posts`/`postmeta` reads for 30 rows, purely from re-resolving the same
		// clinic. The computation is pure, so caching it cannot change an answer.
		$memo_key = strtolower( $office_id );
		if ( isset( self::$clinic_identifier_memo[ $memo_key ] ) ) {
			return self::$clinic_identifier_memo[ $memo_key ];
		}

		$resolved = self::compute_clinic_identifiers( $office_id );

		self::$clinic_identifier_memo[ $memo_key ] = $resolved;

		return $resolved;
	}

	/**
	 * The uncached body of resolve_clinic_identifiers(). See that method for the reasoning.
	 *
	 * @param string $office_id Trimmed, non-empty office identifier.
	 * @return array
	 */
	protected static function compute_clinic_identifiers( $office_id ) {

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
		// created_at is REQUIRED, not decorative: row_holds_slot() ages an abandoned
		// `pending`/`on-hold` row from it. Omitting the column made the guard silently
		// skip (no timestamp -> "keep holding"), which is precisely how a dead checkout
		// row pins an hour forever.
		$sql          = "SELECT book_id, order_status, order_id, created_at FROM " . self::booking_table() .
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

		// ROWS, NOT COUNT(DISTINCT ...). This is the grid/checkout parity bug.
		//
		// This method feeds the booking grid; count_distinct_specialists() feeds the
		// checkout gate. They must never disagree, and the SQL aggregate made them disagree
		// in three proven ways:
		//
		//   * an abandoned (older-than-TTL) pending row counted here but not there,
		//   * a stale copied `order_status` counted here but not there,
		//   * a row whose order_id does not exist counted here but not there,
		//
		// because a COUNT(DISTINCT specialist_id) cannot call row_holds_slot(): it never
		// sees `created_at` or `order_id`, which is exactly what that predicate needs.
		//
		// The symptom the operator reports is a grid that hides free hours (the slot is
		// counted as taken here, then the checkout happily accepts the very same hour — or
		// worse, refuses a slot the grid offered). Either way the two surfaces contradict
		// each other, and the contradiction is the bug. Selecting the rows and applying the
		// one shared predicate makes parity structural rather than something to remember.
		$sql = $wpdb->prepare(
			"SELECT book_id, start_time, specialist_id, order_status, order_id, created_at FROM " . self::booking_table() .
			" WHERE office_id IN ({$in}) AND `date` = %s AND deleted = 0 AND " . self::active_status_predicate( $placeholders ) .
			" ORDER BY start_time",
			array_merge( $clinic_ids, array( $date ), $statuses )
		);
		$rows = $wpdb->get_results( $sql, ARRAY_A );

		$map    = array();
		$seen   = array();
		foreach ( (array) $rows as $row ) {
			$slot = $row['start_time'];

			// ONE specialist can only occupy a slot once: dedupe exactly like
			// COUNT(DISTINCT specialist_id) did, but only after the row has passed the
			// same test the checkout gate applies.
			if ( ! self::row_holds_slot( $row ) ) {
				continue;
			}

			$specialist = (int) $row['specialist_id'];
			if ( isset( $seen[ $slot ][ $specialist ] ) ) {
				continue;
			}
			$seen[ $slot ][ $specialist ] = true;

			$map[ $slot ] = isset( $map[ $slot ] ) ? $map[ $slot ] + 1 : 1;
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

		// created_at travels with start_time for the same reason as above: the grid
		// must hide exactly the hours checkout refuses, and the age guard lives in
		// row_holds_slot(), which both paths share.
		$sql  = "SELECT book_id, start_time, order_status, order_id, created_at FROM " . self::booking_table() .
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

		if ( array_key_exists( $order_id, self::$order_release_cache ) ) {
			return self::$order_release_cache[ $order_id ];
		}

		$result = self::compute_order_releases_slot( $order_id );

		self::$order_release_cache[ $order_id ] = $result;

		return $result;
	}

	/**
	 * Drop the per-request order cache.
	 *
	 * Called after this module writes a status it later reads through the cache, so the
	 * write is observed immediately rather than one request late.
	 *
	 * @return void
	 */
	public static function flush_order_cache() {
		self::$order_release_cache = array();
	}

	/**
	 * The uncached body of order_releases_slot(). See that method for the reasoning.
	 *
	 * @param int $order_id Positive order id.
	 * @return bool
	 */
	protected static function compute_order_releases_slot( $order_id ) {
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
		if ( self::order_releases_slot( $order_id ) ) {
			return false;
		}

		// AN ABANDONED, UNPAID CHECKOUT MUST NOT LOCK AN HOUR FOREVER.
		//
		// This is the second reported "no specialist has any booking, yet every hour is
		// refused" bug, and it needs no phantom contradiction to explain: the row is real,
		// the order is real, and nobody is coming.
		//
		// HOW IT HAPPENS. The customer reaches checkout, the parent INSERTs the booking row,
		// the payment fails / the tab is closed / the gateway times out. The row is left at
		// `pending` (or `on-hold`). The parent has a cron that is *supposed* to cancel those
		// after 30 minutes — but this module cannot assume that cron runs. Sites disable
		// wp-cron, have no traffic to trigger it, or a plugin purges the schedule. When it
		// does not run, that unpaid row holds its hour indefinitely.
		//
		// The damage compounds: every abandoned attempt adds another pinned hour. The slots
		// also disappear from the grid (specialist_booked_slots_for_day uses this same
		// predicate), so the operator sees a specialist with an empty calendar in the
		// appointments list and a fully-blocked booking page — which is exactly the report.
		//
		// WHY AN AGE LIMIT IS THE RIGHT FIX AND NOT A WEAKENING
		//
		// A slot is only genuinely at risk while a payment is in flight. No gateway settles
		// an appointment days later, and the parent's own window is 30 minutes. Holding for
		// two hours is already 4x more conservative than the parent; past that the only thing
		// being protected is a dead row. Crucially this is measured from the order's own
		// creation time, NOT from "now" — a row with no readable timestamp keeps holding, so
		// a site with broken clocks fails closed rather than releasing live bookings.
		if ( self::is_stale_unpaid( $row, $order_id ) ) {
			// A RELEASED ROW MUST ALSO STOP LOOKING LIKE AN APPOINTMENT.
			//
			// Releasing the hour in this module's own predicates is only half the job. The
			// parent theme's appointments list, dashboard counters and per-specialist counts
			// all filter the copied `order_status` column directly, so an abandoned checkout
			// that this module correctly stopped counting still appeared there as a live
			// appointment — and every new attempt added another one.
			//
			// Measured on a rebuilt database: five abandoned attempts at one hour produced
			// FIVE phantom appointments in the operator's list, and a sixth that really was
			// paid made six. The operator cannot tell which one is real.
			//
			// Marking the row `cancelled` when the hold expires makes the release visible
			// everywhere the parent looks, and it is idempotent: the write happens once, on
			// the first read after the TTL, and later reads see `cancelled` and skip the age
			// check entirely. The parent's own cron does the same thing for the same reason.
			self::retire_abandoned_row( $row, $order_id );

			return false;
		}

		return true;
	}

	/**
	 * How long an unpaid booking may hold its hour before it is treated as abandoned.
	 *
	 * Filterable so a clinic with slow offline payments can widen it without patching the
	 * module. Two hours is deliberately generous: the parent cancels `pending`/`on-hold`
	 * after 30 minutes, and no payment gateway settles an appointment hours after the
	 * checkout request. Narrowing this would start releasing genuinely in-flight payments;
	 * widening it re-creates the permanent lock this exists to prevent.
	 *
	 * @return int Seconds.
	 */
	public static function abandoned_checkout_ttl() {
		$ttl = (int) apply_filters( 'drplus_child_capacity/abandoned_checkout_ttl', 2 * HOUR_IN_SECONDS );
		// Never let a filter disable the release entirely (0 or negative would mean "hold
		// forever", which is the bug) and never let it shrink below the parent's 30 minutes.
		return max( 30 * MINUTE_IN_SECONDS, $ttl );
	}

	/**
	 * Is this row an abandoned, unpaid checkout that has outlived its hold?
	 *
	 * Only `pending` / `on-hold` qualify. A `processing`/`completed` row is a real
	 * appointment and is never released by age — the visit may well be in the future.
	 *
	 * @param array $row      Booking row.
	 * @param int   $order_id Order id from the row.
	 * @return bool True when the hold has expired and the hour must come back.
	 */
	protected static function is_stale_unpaid( $row, $order_id ) {
		$status = strtolower( trim( (string) ( isset( $row['order_status'] ) ? $row['order_status'] : '' ) ) );
		if ( 0 === strpos( $status, 'wc-' ) ) {
			$status = substr( $status, 3 );
		}
		if ( ! in_array( $status, array( 'pending', 'on-hold' ), true ) ) {
			return false;
		}

		// THE ROW'S "pending" IS NOT PROOF THAT NOBODY PAID.
		//
		// This is the third incarnation of the "a booked hour became bookable again" bug
		// and it was introduced by the very fix that was meant to end it.
		//
		// The copied `order_status` column is written ONCE, when the parent INSERTs the
		// booking row — which happens BEFORE the gateway settles. Nothing in the parent
		// ever updates it on payment: the only writer is the parent's five-minute cron,
		// and that cron only touches rows already sitting at pending/on-hold. So the
		// ordinary, happy-path sequence is:
		//
		//   1. customer checks out      -> row.order_status = 'pending', order = 'pending'
		//   2. gateway settles          -> order = 'processing'      (row untouched)
		//   3. two hours pass           -> this function sees 'pending', calls it abandoned,
		//                                  and hands a PAID appointment back to the pool.
		//
		// Verified on the operator's own data: book #61 / order #2947 is 'processing' with
		// the row still at 'pending'. On that row the old code freed the slot at the TTL.
		//
		// The order is the authority (see order_releases_slot()), so ask it before calling
		// anything abandoned. A row only counts as an unfinished checkout when the ORDER
		// is also still unpaid. If the order cannot be read we fall through to the age
		// check, which keeps the previous fail-closed behaviour.
		if ( self::order_is_paid_or_beyond( $order_id ) ) {
			return false;
		}

		// A ROW WITH NO ORDER IS NOT AN ABANDONED CHECKOUT.
		//
		// The TTL exists for one situation only: the customer reached checkout, the parent
		// INSERTed the row, and the payment never arrived. Those rows always have an order —
		// the parent creates it in the same request.
		//
		// A row with order_id = 0 was never part of a checkout. It is an appointment the
		// operator entered by hand, or an import from an older system. Nobody is "waiting to
		// pay" for it, so there is nothing to time out: releasing it on age silently erases
		// a real appointment from the capacity picture after two hours, which is a booking
		// the clinic is still expecting to fill. The site's own data contains one.
		//
		// Note this is the OPPOSITE of the paid-order case above and must not be merged with
		// it: there, an order proves the payment went through; here, no order exists to ask,
		// and the row is the only record that the hour is taken.
		if ( (int) $order_id <= 0 ) {
			return false;
		}

		$created = self::row_created_timestamp( $row, $order_id );
		if ( $created <= 0 ) {
			// No trustworthy timestamp. Failing closed here would re-create the permanent
			// lock, but releasing would drop a hold we cannot age. Prefer holding: an
			// unknown-age row is rarer than a broken clock, and the operator can see it in
			// the appointments list. Logged so this is diagnosable rather than mysterious.
			DrPlus_Child_Capacity_Logger::diag( 'stale-unpaid check on book #' . ( isset( $row['book_id'] ) ? $row['book_id'] : '?' ) . ': no readable created_at — keeping the hold (fail closed)' );
			return false;
		}

		$age = time() - $created;
		if ( $age < self::abandoned_checkout_ttl() ) {
			return false; // Still inside the payment window: genuinely holds the slot.
		}

		DrPlus_Child_Capacity_Logger::diag( sprintf(
			'releasing an abandoned checkout: book #%s is %s with order #%d, %d minutes old (ttl %d) — nobody is coming to this appointment',
			isset( $row['book_id'] ) ? $row['book_id'] : '?',
			$status,
			(int) $order_id,
			(int) round( $age / MINUTE_IN_SECONDS ),
			(int) round( self::abandoned_checkout_ttl() / MINUTE_IN_SECONDS )
		) );

		return true;
	}

	/**
	 * When was this booking created, in unix time?
	 *
	 * Tries the row's own `created_at`, then the order's creation date. Both are read as
	 * site-local time with an explicit UTC conversion, because `strtotime()` on a bare
	 * 'Y-m-d H:i:s' uses the PHP default timezone, which on many hosts is UTC while the site
	 * is not — and a mismatch of hours is exactly the kind of error that turns a 2-hour
	 * window into a 6-hour one.
	 *
	 * @param array $row      Booking row.
	 * @param int   $order_id Order id.
	 * @return int Unix timestamp, or 0 when unreadable.
	 */
	protected static function row_created_timestamp( $row, $order_id ) {
		// THE ROW'S OWN created_at COMES FIRST, AND THAT ORDER MATTERS.
		//
		// The parent writes `created_at` when it inserts the booking row, which is the
		// moment the hold began. The order's get_date_created() is the same event in
		// principle, but it is not always populated the way one expects: a site that
		// back-fills rows, an order rebuilt by a plugin, or a WC_Order whose post dates were
		// rewritten all yield a timestamp that has nothing to do with when this hour was
		// taken. Reading the row first keeps the answer tied to the record we are judging.
		//
		// The ORDER is the fallback, not the primary: some imports leave created_at at
		// '0000-00-00 00:00:00', and there the order is the only usable clock.
		$candidates = array();

		if ( ! empty( $row['created_at'] ) ) {
			$candidates[] = (string) $row['created_at'];
		}

		foreach ( $candidates as $raw ) {
			$raw = trim( $raw );
			if ( '' === $raw || 0 === strpos( $raw, '0000-00-00' ) ) {
				continue;
			}
			try {
				// WHICH ZONE IS THIS TIMESTAMP IN? It is not knowable from the string, and
				// getting it wrong is not cosmetic: an hour of drift can turn a 2-hour
				// window into 1 hour or 5, releasing live payments or pinning dead ones.
				//
				// The parent writes `created_at` via the ORM, which on this stack emits
				// UTC (PHP's default zone) while `current_time('mysql')` emits site time —
				// and different parent versions/plugins have done both. So the value is
				// ambiguous by construction, and the only safe reading is the one that
				// cannot shorten a live hold.
				//
				// Take the LATER of the two interpretations: that yields the SMALLER age,
				// so a row is released later rather than earlier. A booking that is
				// genuinely abandoned still crosses the TTL; a payment still in flight is
				// never released by a timezone accident.
				$as_site = ( new DateTimeImmutable( $raw, wp_timezone() ) )->getTimestamp();
				$as_utc  = ( new DateTimeImmutable( $raw, new DateTimeZone( 'UTC' ) ) )->getTimestamp();
				$ts      = max( $as_site, $as_utc );
				if ( $ts > 0 ) {
					return $ts;
				}
			} catch ( Exception $e ) {
				continue;
			}
		}

		$order_id = (int) $order_id;
		if ( $order_id > 0 && function_exists( 'wc_get_order' ) ) {
			$order = wc_get_order( $order_id );
			if ( $order ) {
				$date_created = $order->get_date_created();
				if ( $date_created ) {
					return (int) $date_created->getTimestamp();
				}
			}
		}

		return 0;
	}

	/**
	 * Has this booking's ORDER moved past "nobody has paid yet"?
	 *
	 * Used by is_stale_unpaid() to decide whether a row that still says `pending` is a
	 * genuinely abandoned checkout or a settled payment the copied column never caught up
	 * with. Returns TRUE only when there is a readable order and its status proves the
	 * payment went through (or the customer's obligation otherwise ended); it returns
	 * FALSE for an unreadable/missing order so that callers keep their fail-closed path.
	 *
	 * @param int $order_id Order id from the booking row.
	 * @return bool
	 */
	protected static function order_is_paid_or_beyond( $order_id ) {
		$order_id = (int) $order_id;
		if ( $order_id <= 0 || ! function_exists( 'wc_get_order' ) ) {
			return false;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return false;
		}

		$status = strtolower( trim( (string) $order->get_status() ) );
		if ( 0 === strpos( $status, 'wc-' ) ) {
			$status = substr( $status, 3 );
		}

		// `on-hold` MEANS "WAITING FOR PAYMENT", so it is an unfinished checkout too.
		//
		// WooCommerce's own definition: a gateway has taken the order but not the money
		// (manual/BACS, an off-site gateway that has not called back, an authorisation that
		// has not been captured). Nobody has paid, so the hour must still be releasable by
		// age — that is exactly the case this module exists to stop pinning forever.
		//
		// Treating it as "paid" was a regression: it made abandoned on-hold checkouts hold
		// their hour indefinitely, which is the original reported bug in a new costume.
		$unfinished = array( 'pending', 'on-hold', 'checkout-draft', 'draft', '' );

		return ! in_array( $status, $unfinished, true );
	}

	/**
	 * Does any OTHER row legitimately hold this specialist at this hour?
	 *
	 * The narrow, honest question a diagnostic needs when it is judging one specific
	 * booking row: "has this row's hour really been freed, or is something else keeping
	 * this specialist busy?" It deliberately:
	 *
	 *   * EXCLUDES the row under judgement, so a cancelled row can never keep itself alive;
	 *   * scopes by SPECIALIST, not clinic — one person cannot be in two rooms, so a
	 *     booking at another clinic counts (this is why specialist_already_booked() is
	 *     clinic-agnostic too);
	 *   * uses row_holds_slot(), the same predicate the booking flow uses, so a diagnostic
	 *     can never disagree with the checkout about what a conflict is.
	 *
	 * A previous version of the version-check page compared `office_id` strings directly
	 * here. Because a clinic can be filed under a numeric post id AND under per-specialist
	 * custom-office UUIDs, a live booking under the other spelling was invisible and a
	 * correctly-released row was reported as a leak.
	 *
	 * @param int    $book_id      Row to ignore (the one being judged).
	 * @param int    $specialist_id Specialist whose hour is in question.
	 * @param string $date         Normalized Y-m-d.
	 * @param string $start_time   Normalized H:i:s.
	 * @return bool
	 */
	public static function peer_holds_slot( $book_id, $specialist_id, $date, $start_time ) {
		global $wpdb;

		$book_id       = (int) $book_id;
		$specialist_id = (int) $specialist_id;
		$date          = self::assert_queryable_date( $date );
		$start_time    = self::normalize_time( $start_time );

		if ( $specialist_id <= 0 || '' === $date || '' === $start_time ) {
			return false;
		}

		$statuses     = self::EXCLUDED_STATUSES;
		$placeholders = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );

		$sql = "SELECT book_id, order_status, order_id, created_at FROM " . self::booking_table() .
			" WHERE specialist_id = %d AND `date` = %s AND start_time = %s AND deleted = 0 AND " .
			self::active_status_predicate( $placeholders );

		$bind = array_merge( array( $specialist_id, $date, $start_time ), $statuses );
		if ( $book_id > 0 ) {
			$sql .= ' AND book_id <> %d';
			$bind[] = $book_id;
		}

		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $bind ), ARRAY_A );
		foreach ( (array) $rows as $row ) {
			if ( self::row_holds_slot( $row ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Write `cancelled` onto a booking row whose hold has expired, so every reader agrees.
	 *
	 * Called from row_holds_slot() when a row's age crosses the abandoned-checkout TTL.
	 * See the comment at the call site for why releasing the slot in this module alone is
	 * not enough: the parent theme reads the copied column, not this predicate.
	 *
	 * Deliberately narrow. It never touches a row whose order is readable and unpaid but
	 * still inside the window, never touches a paid/refunded/cancelled row, and does
	 * nothing at all if `book_id` is missing (the synthetic rows used by diagnostics have
	 * no row to update). A failure to write is swallowed — the predicate has already
	 * decided the correct answer, and turning a diagnostic read into a fatal error would
	 * make an abandoned checkout break the booking grid.
	 *
	 * @param array $row      Booking row (needs `book_id`).
	 * @param int   $order_id Order id the row points at.
	 * @return void
	 */
	protected static function retire_abandoned_row( $row, $order_id ) {
		global $wpdb;

		$book_id = isset( $row['book_id'] ) ? (int) $row['book_id'] : 0;
		if ( $book_id <= 0 ) {
			return; // Synthetic row from a diagnostic probe — nothing to retire.
		}

		$table = self::booking_table();

		$wpdb->update(
			$table,
			array( 'order_status' => 'cancelled' ),
			array( 'book_id' => $book_id ),
			array( '%s' ),
			array( '%d' )
		);

		self::flush_order_cache();

		DrPlus_Child_Capacity_Logger::diag( sprintf(
			'retired abandoned booking #%d (order #%d): its hold expired, so the row is marked cancelled and every reader now agrees the hour is free',
			$book_id,
			(int) $order_id
		) );

		// Only retire the order when this module can prove it is still an unpaid checkout.
		// A paid order must never be cancelled by a housekeeping step.
		$order_id = (int) $order_id;
		if ( $order_id <= 0 || ! function_exists( 'wc_get_order' ) ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$status = strtolower( trim( (string) $order->get_status() ) );
		if ( 0 === strpos( $status, 'wc-' ) ) {
			$status = substr( $status, 3 );
		}
		if ( ! in_array( $status, array( 'pending', 'on-hold' ), true ) ) {
			return; // Payment arrived, or an operator already decided. Leave it alone.
		}

		$order->update_status(
			'cancelled',
			__( 'این رزرو پرداخت نشد و ساعت رزروشده آزاد شد.', 'drplus-child' )
		);
	}

	/**
	 * Pick one clinic identifier that resolves to more than one spelling, for diagnostics.
	 *
	 * The version-check page uses this to prove that every spelling of a real clinic on
	 * this site takes the same booking lock. Returns '' when the site has no aliased
	 * clinic, which is a legitimate state rather than a failure.
	 *
	 * @return string An office identifier, or '' when nothing is aliased.
	 */
	public static function sample_aliased_clinic() {
		global $wpdb;

		$candidates = $wpdb->get_col(
			'SELECT DISTINCT office_id FROM ' . self::booking_table() . ' WHERE office_id <> %s LIMIT 20',
			array( '' )
		);

		foreach ( (array) $candidates as $candidate ) {
			$candidate = trim( (string) $candidate );
			if ( '' === $candidate ) {
				continue;
			}
			if ( count( (array) self::resolve_clinic_identifiers( $candidate ) ) > 1 ) {
				return $candidate;
			}
		}

		return '';
	}

	/**
	 * The id of one order that has genuinely been paid, for diagnostics.
	 *
	 * Returns 0 when this site has no paid order, so callers can skip the check instead
	 * of reporting a failure they cannot act on.
	 *
	 * @return int
	 */
	public static function first_paid_order_id() {
		global $wpdb;

		$order_id = $wpdb->get_var(
			'SELECT order_id FROM ' . self::booking_table() .
			" WHERE order_id > 0 AND deleted = 0 AND LOWER( TRIM( order_status ) ) IN ( 'processing', 'completed' ) LIMIT 1"
		);

		return (int) $order_id;
	}

	/**
	 * Copy an order's new status onto every non-deleted booking row that points at it.
	 *
	 * Hooked to `woocommerce_order_status_changed`. See the comment in bootstrap.php for
	 * why the copied column must not be allowed to drift: three separate defects were all
	 * symptoms of it. The capacity predicates deliberately do NOT rely on this having run —
	 * they still ask the order — so a site where the hook is unavailable behaves exactly
	 * as it did before, only with the old drift.
	 *
	 * @param int      $order_id Order id.
	 * @param string   $from     Previous status (unused, present for the hook signature).
	 * @param string   $to       New status, bare (`processing`) in WC 3.0+.
	 * @param WC_Order $order    Order object, when WooCommerce passes it.
	 * @return void
	 */
	public static function sync_row_status_from_order( $order_id, $from = '', $to = '', $order = null ) {
		global $wpdb;

		$order_id = (int) $order_id;
		if ( $order_id <= 0 ) {
			return;
		}

		// Prefer the object WooCommerce handed us; fall back to reading it ourselves.
		if ( ! $order instanceof WC_Order ) {
			if ( ! function_exists( 'wc_get_order' ) ) {
				return;
			}
			$order = wc_get_order( $order_id );
		}
		if ( ! $order ) {
			return;
		}

		$status = strtolower( trim( (string) $order->get_status() ) );
		if ( '' === $status ) {
			return;
		}

		$updated = $wpdb->update(
			self::booking_table(),
			array( 'order_status' => $status ),
			array(
				'order_id' => $order_id,
				'deleted'  => 0,
			),
			array( '%s' ),
			array( '%d', '%d' )
		);

		self::flush_order_cache();

		if ( false !== $updated && $updated > 0 ) {
			DrPlus_Child_Capacity_Logger::diag( sprintf(
				'order #%d moved %s -> %s; synced %d booking row(s)',
				$order_id,
				'' === $from ? '?' : $from,
				$status,
				(int) $updated
			) );
		}
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
