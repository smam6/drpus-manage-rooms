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
	 * Per-request cache of computed counts (spec 1.14, 5.6.90).
	 *
	 * @var array<string,int>
	 */
	protected static $count_cache = array();

	/**
	 * Per-request cache of the detected booking-table engine (spec 4.1).
	 *
	 * @var string|null 'InnoDB'|'MyISAM'|other|null
	 */
	protected static $engine = null;

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
	 * Detect the booking table engine once per request (spec 4.1, 5.5.76).
	 *
	 * Implements: spec 4.1
	 *
	 * @return string Engine name, lowercased; 'unknown' when detection fails (spec 4.12).
	 */
	public static function engine() {
		if ( null !== self::$engine ) {
			return self::$engine;
		}
		global $wpdb;
		$table = self::booking_table();
		$row   = $wpdb->get_row( $wpdb->prepare( 'SHOW TABLE STATUS LIKE %s', $table ) );
		if ( $row && ! empty( $row->Engine ) ) {
			self::$engine = strtolower( $row->Engine );
		} else {
			// Detection failed: callers must fall back to the GET_LOCK path (spec 4.12, 5.6.97).
			self::$engine = 'unknown';
		}
		return self::$engine;
	}

	/**
	 * Normalize a date to Y-m-d. Accepts Y-m-d or unix timestamp (spec 1.3, Finding 5).
	 *
	 * Implements: spec 1.3, 3.3
	 *
	 * @param string|int $date Date string or unix timestamp.
	 * @return string Y-m-d or empty string when invalid.
	 */
	public static function normalize_date( $date ) {
		if ( is_numeric( $date ) ) {
			return wp_date( 'Y-m-d', (int) $date );
		}
		$ts = strtotime( (string) $date );
		return $ts ? wp_date( 'Y-m-d', $ts ) : '';
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
		if ( is_numeric( $time ) && (int) $time >= 86400000 ) {
			// raw_time is milliseconds since midnight (spec 3.3): convert to H:i:s.
			$seconds = (int) floor( (int) $time / 1000 );
			return gmdate( 'H:i:s', $seconds % 86400 );
		}
		$ts = strtotime( '1970-01-01 ' . (string) $time );
		return $ts ? gmdate( 'H:i:s', $ts ) : '';
	}

	/**
	 * Build the shared WHERE fragment: deleted = 0 and excluded statuses (spec 1.1, 1.7).
	 *
	 * Implements: spec 1.1, 1.6, 1.7
	 *
	 * @param string $office_id   Clinic/office id, bound as string (spec 1.2, 5.5.82).
	 * @param string $date        Y-m-d.
	 * @param string $start_time  H:i:s.
	 * @return array{0:string,1:array} SQL fragment + values for $wpdb->prepare.
	 */
	protected static function slot_where( $office_id, $date, $start_time ) {
		$statuses = self::EXCLUDED_STATUSES;
		$placeholders = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );
		// office_id is tinytext: numeric clinic ids and online ids are both strings (spec 1.2).
		$sql = "office_id = %s AND `date` = %s AND start_time = %s AND deleted = 0 AND order_status NOT IN ({$placeholders})";
		return array( $sql, array_merge( array( $office_id, $date, $start_time ), $statuses ) );
	}

	/**
	 * Count distinct specialists booked in one slot, display path (spec 1.5, 1.8).
	 *
	 * Implements: spec 1.1, 1.5, 1.7, 1.8, 1.10, 1.14
	 *
	 * @param string $office_id   Clinic id.
	 * @param string $date        Y-m-d.
	 * @param string $start_time  H:i:s.
	 * @return int Distinct active specialist count.
	 */
	public static function count_slot( $office_id, $date, $start_time ) {
		$cache_key = $office_id . '|' . $date . '|' . $start_time;
		if ( isset( self::$count_cache[ $cache_key ] ) ) {
			return self::$count_cache[ $cache_key ];
		}
		global $wpdb;
		list( $where, $values ) = self::slot_where( $office_id, $date, $start_time );
		$sql = $wpdb->prepare(
			"SELECT COUNT(DISTINCT specialist_id) FROM " . self::booking_table() . " WHERE {$where}",
			$values
		);
		$count = (int) $wpdb->get_var( $sql );
		self::$count_cache[ $cache_key ] = $count;
		return $count;
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
		error_log( sprintf(
			'[DRPLUS COUNT SQL] Query args: office=%s | date=%s | start=%s | Result: %d',
			var_export( $office_id, true ),
			var_export( $date, true ),
			var_export( $start, true ),
			(int) $count
		) );
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

	/**
	 * Race-safe check: count under lock and decide atomically (spec 4.2-4.12).
	 *
	 * InnoDB: START TRANSACTION + SELECT ... FOR UPDATE + COMMIT/ROLLBACK.
	 * Fallback (MyISAM/unknown): GET_LOCK/RELEASE_LOCK (spec 4.5, 4.12).
	 * The DISTINCT keyword is never combined with a row-locking clause; locked rows are counted in PHP (spec 4.4, 5.5.79).
	 *
	 * Implements: spec 4.2, 4.3, 4.4, 4.5, 4.6, 4.12
	 *
	 * @param string $office_id   Clinic id.
	 * @param string $date        Y-m-d.
	 * @param string $start_time  H:i:s.
	 * @param int    $capacity    Capacity limit to compare against.
	 * @return array{0:bool,1:int,2:string} [allowed, current_count, error_code]
	 */
	public static function check_slot_with_lock( $office_id, $date, $start_time, $capacity ) {
		$engine = self::engine();
		if ( 'innodb' === $engine ) {
			// Rollback strategy: if anything fails after START TRANSACTION we ROLLBACK,
			// leaving the row locks released and no partial state (spec 4.8).
			return self::check_innodb( $office_id, $date, $start_time, $capacity );
		}
		return self::check_get_lock( $office_id, $date, $start_time, $capacity );
	}

	/**
	 * InnoDB path: transactional FOR UPDATE count (spec 4.2, 4.3, 4.4).
	 *
	 * Implements: spec 4.2, 4.3, 4.4, 4.8
	 *
	 * @return array{0:bool,1:int,2:string}
	 */
	protected static function check_innodb( $office_id, $date, $start_time, $capacity ) {
		global $wpdb;
		$wpdb->query( 'START TRANSACTION' );
		list( $where, $values ) = self::slot_where( $office_id, $date, $start_time );
		$sql = $wpdb->prepare(
			"SELECT specialist_id FROM " . self::booking_table() . " WHERE {$where} FOR UPDATE",
			$values
		);
		$rows  = $wpdb->get_results( $sql, ARRAY_A );
		$count = count( array_unique( wp_list_pluck( (array) $rows, 'specialist_id' ) ) );
		if ( false === $rows ) {
			$wpdb->query( 'ROLLBACK' );
			return array( false, $count, self::ERR_LOCK_FAILED );
		}
		$allowed = $count < $capacity;
		$wpdb->query( $allowed ? 'COMMIT' : 'ROLLBACK' );
		return array( $allowed, $count, $allowed ? '' : 'capacity_exceeded' );
	}

	/**
	 * GET_LOCK fallback path for MyISAM/unknown engines (spec 4.5, 4.12).
	 *
	 * Implements: spec 4.5, 4.6, 4.12
	 *
	 * @return array{0:bool,1:int,2:string}
	 */
	protected static function check_get_lock( $office_id, $date, $start_time, $capacity ) {
		global $wpdb;
		$lock = 'drplus_capacity_' . md5( $office_id . '|' . $date . '|' . $start_time );
		$got  = $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 5)', array( $lock ) ) );
		if ( 1 !== (int) $got ) {
			return array( false, 0, self::ERR_LOCK_FAILED );
		}
		$count   = self::count_slot( $office_id, $date, $start_time );
		$allowed = $count < $capacity;
		$wpdb->query( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', array( $lock ) ) );
		return array( $allowed, $count, $allowed ? '' : 'capacity_exceeded' );
	}
}
