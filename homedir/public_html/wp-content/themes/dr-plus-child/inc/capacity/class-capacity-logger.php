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
	 * Memoized diag_enabled() result for this request.
	 *
	 * @var bool|null
	 */
	protected static $diag_enabled = null;

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

	/**
	 * Is the on-demand diagnostic trace switched on for this request?
	 *
	 * Opt-in only, so production logs stay empty. Accepted from:
	 *   - the admin bar button (?drplus_diag=1 is appended to every same-origin link)
	 *   - the query string
	 *   - a cookie, so the flag survives the checkout round-trip
	 *
	 * The query string alone is NOT enough: checkout happens on a different URL, and the
	 * interesting moment (the 3rd attempt) is a POST to /checkout/ — a flag that was only on
	 * the booking page would be forgotten exactly when it is needed. The cookie is what makes
	 * this work end-to-end.
	 *
	 * Restricted to administrators: an unauthenticated visitor must not be able to make the
	 * server write a trace, or read the page that displays it.
	 *
	 * @return bool
	 */
	public static function diag_enabled() {
		if ( null !== self::$diag_enabled ) {
			return self::$diag_enabled;
		}
		$enabled = self::compute_diag_enabled();
		self::$diag_enabled = $enabled;
		return $enabled;
	}

	/**
	 * Reset the memoized flag. Called when the flag is set mid-request (set_cookie), and by
	 * tests, so a cached "false" from before the cookie existed cannot shadow a real "true".
	 *
	 * @return void
	 */
	public static function diag_reset_cache() {
		self::$diag_enabled = null;
	}

	/**
	 * Evaluate the flag from scratch.
	 *
	 * @return bool
	 */
	protected static function compute_diag_enabled() {
		$on = isset( $_GET['drplus_diag'] ) && '1' === (string) $_GET['drplus_diag']; // phpcs:ignore WordPress.Security.NonceVerification
		if ( ! $on && isset( $_COOKIE['drplus_diag'] ) ) {
			$on = '1' === (string) $_COOKIE['drplus_diag']; // phpcs:ignore WordPress.Security.NonceVerification
		}
		if ( ! $on ) {
			return false;
		}
		return function_exists( 'current_user_can' ) && current_user_can( 'manage_options' );
	}

	/**
	 * Persist one diagnostic line, only when diag_enabled().
	 *
	 * Where the output goes: WordPress does not install an error handler by default, so
	 * error_log() lands in the host's PHP error log (often cPanel → Errors) and NOT in
	 * wp-content/debug.log unless WP_DEBUG_LOG is defined — which it is not on this site
	 * (wp-config.php:94-96). That makes "send me the log file" unreliable.
	 *
	 * So this writes to a dedicated, admin-only file it controls, and ALSO to error_log()
	 * when WP_DEBUG_LOG is on, so either route works. The file is created with a deny-all
	 * .htaccess next to it.
	 *
	 * Every line carries the same request id, so a single checkout is one greppable unit.
	 *
	 * @param string $message Trace message.
	 * @return void
	 */
	public static function diag( $message ) {
		if ( ! self::diag_enabled() ) {
			return;
		}
		$line = sprintf(
			"[DRPLUS DIAG] rid=%s uid=%d uri=%s :: %s\n",
			self::diag_request_id(),
			function_exists( 'get_current_user_id' ) ? (int) get_current_user_id() : 0,
			isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '-',
			$message
		);

		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			error_log( $line ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
		}

		$file = self::diag_file();
		if ( '' === $file ) {
			return;
		}
		// Cap the file so a forgotten flag cannot fill the disk.
		if ( file_exists( $file ) && filesize( $file ) > 1048576 ) {
			@file_put_contents( $file, '' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		}
		@file_put_contents( $file, $line, FILE_APPEND | LOCK_EX ); // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
	}

	/**
	 * Per-request identifier shared by every diag line of one checkout.
	 *
	 * @return string
	 */
	public static function diag_request_id() {
		static $rid = null;
		if ( null === $rid ) {
			$rid = substr( md5( uniqid( '', true ) . wp_rand() ), 0, 8 );
		}
		return $rid;
	}

	/**
	 * Absolute path to the diagnostic log file.
	 *
	 * Lives in wp-content (writable, and outside the theme so a theme update cannot wipe
	 * evidence mid-investigation). Returns '' when there is nowhere writable to put it.
	 *
	 * @return string
	 */
	public static function diag_file() {
		if ( ! defined( 'WP_CONTENT_DIR' ) ) {
			return '';
		}
		$dir = WP_CONTENT_DIR . '/drplus-diag';
		if ( ! is_dir( $dir ) ) {
			if ( ! @mkdir( $dir, 0755, true ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors
				return '';
			}
		}
		$htaccess = $dir . '/.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			@file_put_contents( $htaccess, "Require all denied\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n" ); // phpcs:ignore
			@file_put_contents( $dir . '/index.php', "<?php\n// Silence is golden.\n" ); // phpcs:ignore
		}
		return $dir . '/capacity-diag.log';
	}

	/**
	 * Read the diagnostic log back for the admin page / endpoint. Tail only.
	 *
	 * @param int $max_bytes How much of the end of the file to read.
	 * @return string
	 */
	public static function diag_tail( $max_bytes = 262144 ) {
		$file = self::diag_file();
		if ( '' === $file || ! file_exists( $file ) ) {
			return '';
		}
		$size = filesize( $file );
		$fh   = fopen( $file, 'rb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( ! $fh ) {
			return '';
		}
		if ( $size > $max_bytes ) {
			fseek( $fh, -$max_bytes, SEEK_END );
		}
		$data = stream_get_contents( $fh );
		fclose( $fh ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		return (string) $data;
	}

	/**
	 * Empty the diagnostic log.
	 *
	 * @return void
	 */
	public static function diag_clear() {
		$file = self::diag_file();
		if ( '' !== $file && file_exists( $file ) ) {
			@file_put_contents( $file, '' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
		}
	}
}
