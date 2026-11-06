<?php
/**
 * Dr Plus Child Theme — Room Capacity Management
 *
 * Implements: spec 6.6, 6.7, 6.8, 6.9, 6.10, 5.6.54
 * Category: 6 — Logging (blocked attempts only; never notifies admin, spec 6.8)
 *
 * @package DrPlusChild\Capacity
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Logger: writes blocked booking attempts to the capacity_log table.
 *
 * Single responsibility: logging only (spec 5.6.54). Never sends admin notifications (spec 6.8).
 */
class DrPlus_Child_Capacity_Logger {

	/**
	 * Persist one blocked attempt. Uses $wpdb->insert with a format array (spec 6.10).
	 *
	 * Implements: spec 6.6, 6.7, 6.8, 6.9, 6.10
	 *
	 * @param array $args {
	 *     @type string $office_id      Clinic id.
	 *     @type string $booking_date   Y-m-d.
	 *     @type string $start_time     H:i:s.
	 *     @type int    $specialist_id  Specialist attempting the booking.
	 *     @type int    $current_count  Distinct specialists already booked.
	 *     @type int    $capacity_limit Capacity limit that was exceeded.
	 *     @type string $reason         Error code: capacity_exceeded|missing_date|lock_failed.
	 * }
	 * @return bool True when the row was written; false when logging is unavailable (spec 6.9).
	 */
	public static function log_blocked( $args ) {
		if ( ! DrPlus_Child_Capacity_Installer::table_exists() ) {
			return false; // Graceful degradation: enforce capacity, skip logging (spec 6.9).
		}
		global $wpdb;
		$ip = '';
		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}
		$inserted = $wpdb->insert(
			DrPlus_Child_Capacity_Installer::log_table(),
			array(
				'office_id'      => (string) $args['office_id'],
				'booking_date'   => (string) $args['booking_date'],
				'start_time'     => (string) $args['start_time'],
				'specialist_id'  => (int) $args['specialist_id'],
				'current_count'  => (int) $args['current_count'],
				'capacity_limit' => (int) $args['capacity_limit'],
				'reason'         => (string) $args['reason'],
				'ip_address'     => $ip,
				'user_id'        => get_current_user_id(),
				'created_at'     => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%d', '%s' )
		);
		return false !== $inserted;
	}
}
