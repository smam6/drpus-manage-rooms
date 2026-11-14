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
	 */
	const EXCLUDED_STATUSES = array( 'cancelled', 'refunded', 'failed', 'trash' );

	/**
	 * Error code when the row lock cannot be acquired (spec 3.15, 5.6.67).
	 */
	const ERR_LOCK_FAILED = 'lock_failed';

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
		global $wpdb;
		$statuses     = self::EXCLUDED_STATUSES;
		$placeholders = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );
		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT specialist_id) FROM " . self::booking_table() .
				" WHERE office_id = %s AND `date` = %s AND start_time = %s AND deleted = 0 AND order_status NOT IN ({$placeholders})",
				array_merge( array( $office_id, $date, $start ), $statuses )
			)
		);
		return (int) $count;
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
		global $wpdb;
		$statuses     = self::EXCLUDED_STATUSES;
		$placeholders = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );
		$sql = $wpdb->prepare(
			"SELECT start_time, COUNT(DISTINCT specialist_id) AS cnt FROM " . self::booking_table() .
			" WHERE office_id = %s AND `date` = %s AND deleted = 0 AND order_status NOT IN ({$placeholders}) GROUP BY start_time",
			array_merge( array( $office_id, $date ), $statuses )
		);
		$rows  = $wpdb->get_results( $sql, ARRAY_A );
		$map   = array();
		foreach ( (array) $rows as $row ) {
			$map[ $row['start_time'] ] = (int) $row['cnt'];
		}
		return $map;
	}

}
