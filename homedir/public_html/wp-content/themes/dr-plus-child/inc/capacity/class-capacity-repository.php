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
	 * returning 0 rows.
	 *
	 * Implements: spec 1.3, 3.3, Matrix C
	 *
	 * @param string|int $date            Date string (Y-m-d or another unambiguous calendar format) or unix timestamp.
	 * @param bool       $treat_as_number When true, a numeric value is always a unix timestamp even if it looks like a date.
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

		// Already a Gregorian Y-m-d: reject Jalali years (1405-08-28) and impossible dates.
		$parsed = self::parse_ymd( $date );
		if ( null !== $parsed ) {
			list( $year, $month, $day ) = $parsed;
			if ( $year < 1900 || ! checkdate( $month, $day, $year ) ) {
				return '';
			}
			return sprintf( '%04d-%02d-%02d', $year, $month, $day );
		}

		// ISO-8601 timestamps (the booking datepicker submits `altFormat: "X"`, which may be an ISO string).
		$iso = self::parse_iso8601( $date );
		if ( null !== $iso ) {
			return gmdate( 'Y-m-d', $iso );
		}

		// Unambiguous textual date (e.g. 2026-11-19). Localized Jalali text ("28 آبان 1405") is not parsable
		// and correctly falls through to '' so callers can fall back to raw_date.
		$ts = strtotime( $date );
		return $ts ? gmdate( 'Y-m-d', $ts ) : '';
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
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $date ) ) {
			return null;
		}
		$ts = strtotime( $date );
		return false === $ts ? null : (int) $ts;
	}

	/**
	 * Normalize a time to H:i:s. Accepts H:i:s, H:i, or milliseconds since midnight (spec 1.4, 2.8, Finding 5).
	 *
	 * Implements: spec 1.4, 2.8, 3.3
	 *
	 * @param string|int $time Time string or ms-since-midnight (raw_time).
	 * @return string H:i:s or empty string when invalid.
	 */
	public static function normalize_time( $time ) {
		$time = trim( (string) $time );
		if ( '' === $time ) {
			return '';
		}
		if ( is_numeric( $time ) && (int) $time >= 86400000 ) {
			// raw_time is milliseconds since midnight (spec 3.3): convert to H:i:s.
			$seconds = (int) floor( (int) $time / 1000 );
			return gmdate( 'H:i:s', $seconds % 86400 );
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
		if ( preg_match( '/^\d{1,2}$/', $time ) ) {
			// Bare hour ("15"); never let strtotime turn "15" into an unrelated calendar date.
			$hour = (int) $time;
			return $hour <= 23 ? sprintf( '%02d:00:00', $hour ) : '';
		}
		$ts = strtotime( '1970-01-01 ' . $time );
		return $ts ? gmdate( 'H:i:s', $ts ) : '';
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
