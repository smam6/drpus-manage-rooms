<?php
/**
 * Dr Plus Child Theme — Room Capacity Management
 *
 * Implements: spec 6.1, 6.2, 6.3, 6.4, 6.5, 5.5.72, 5.5.73, 5.5.74, 5.5.75
 * Category: 6 — Logging (dbDelta installer for the capacity_log table)
 *
 * @package DrPlusChild\Capacity
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Installer: creates the {$wpdb->prefix}drplus_capacity_log table via dbDelta.
 *
 * Single responsibility: schema management only (spec 5.6.51-5.6.57).
 */
class DrPlus_Child_Capacity_Installer {

	/**
	 * Log table name without prefix (spec 5.6.66).
	 */
	const LOG_TABLE = 'drplus_capacity_log';

	/**
	 * Option key storing the installed schema version (spec 5.5.74).
	 */
	const VERSION_OPTION = 'drplus_capacity_db_version';

	/**
	 * Return the fully-qualified log table name using the dynamic prefix (spec 6.1, 5.5.72).
	 *
	 * Implements: spec 6.1, 5.5.72, 5.5.73
	 *
	 * @return string Table name with the dynamic prefix applied (prefix never hardcoded).
	 */
	public static function log_table() {
		global $wpdb;
		return $wpdb->prefix . self::LOG_TABLE;
	}

	/**
	 * Maybe create or upgrade the log table. Idempotent (spec 6.5).
	 *
	 * Implements: spec 6.4, 6.5, 5.5.74, 5.5.75
	 *
	 * @return bool True when the table exists after the call.
	 */
	public static function maybe_install() {
		$installed = get_option( self::VERSION_OPTION, '' );
		if ( $installed === DRPLUS_CAPACITY_DB_VERSION && self::table_exists() ) {
			return true;
		}
		self::create_table();
		if ( self::table_exists() ) {
			update_option( self::VERSION_OPTION, DRPLUS_CAPACITY_DB_VERSION, false );
			return true;
		}
		// Graceful degradation: capacity is still enforced, logging is skipped (spec 6.9).
		return false;
	}

	/**
	 * Check whether the log table exists.
	 *
	 * Implements: spec 6.5
	 *
	 * @return bool
	 */
	public static function table_exists() {
		global $wpdb;
		$table  = self::log_table();
		$found  = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		return $found === $table;
	}

	/**
	 * Create the log table with dbDelta. Columns and indexes per spec 6.2, 6.3.
	 *
	 * Implements: spec 6.1, 6.2, 6.3, 6.5
	 *
	 * @return void
	 */
	public static function create_table() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$table    = self::log_table();
		$charset  = $wpdb->get_charset_collate();
		$sql      = "CREATE TABLE {$table} (
			log_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			office_id VARCHAR(255) NOT NULL,
			booking_date DATE NOT NULL,
			start_time TIME NOT NULL,
			specialist_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			current_count INT UNSIGNED NOT NULL DEFAULT 0,
			capacity_limit INT UNSIGNED NOT NULL DEFAULT 0,
			reason VARCHAR(32) NOT NULL DEFAULT 'capacity_exceeded',
			ip_address VARCHAR(45) NOT NULL DEFAULT '',
			user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (log_id),
			KEY idx_slot (office_id(191), booking_date, start_time),
			KEY idx_created (created_at)
		) {$charset};";
		dbDelta( $sql );
	}
}
