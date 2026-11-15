<?php
/**
 * Dr Plus Child Theme — Room Capacity Management
 *
 * On-demand diagnostic trace for the capacity module.
 *
 * Implements: operational tooling only. No business logic lives here.
 *
 * @package DrPlusChild\Capacity
 * @since   1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin-only switch and viewer for the capacity trace.
 *
 * Why this exists: capacity enforcement fails OPEN on every path that cannot identify a
 * booking (not a booking cart, empty office_id, empty hour). When a booking slips through,
 * nothing is written to the blocked-attempts table by design, so the log looks clean and the
 * failure is invisible. This records the decision path instead of just the outcome.
 *
 * Everything is behind ?drplus_diag=1 + manage_options, so a normal visit is unaffected.
 */
class DrPlus_Child_Capacity_Diag {

	/**
	 * Cookie name for the flag.
	 */
	const COOKIE = 'drplus_diag';

	/**
	 * Admin-bar query var.
	 */
	const QUERY = 'drplus_diag';

	/**
	 * Wire the admin-bar button and the log viewer endpoint.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_bar_menu', array( __CLASS__, 'admin_bar_button' ), 999 );
		add_action( 'admin_post_drplus_capacity_diag_view', array( __CLASS__, 'render_view' ) );
	}

	/**
	 * Add the "Capacity Diag: ON/OFF" toggle to the admin bar.
	 *
	 * @param WP_Admin_Bar $bar Admin bar instance.
	 * @return void
	 */
	public static function admin_bar_button( $bar ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$on = DrPlus_Child_Capacity_Logger::diag_enabled();

		// Toggle: turn it on (this page) or off (any page).
		$toggle_url = $on
			? remove_query_arg( self::QUERY )
			: add_query_arg( self::QUERY, '1' );

		$bar->add_node(
			array(
				'id'    => 'drplus-capacity-diag',
				'title' => $on ? '🔴 Capacity Diag: ON' : '⚪ Capacity Diag: OFF',
				'href'  => $toggle_url,
			)
		);

		if ( $on ) {
			$bar->add_node(
				array(
					'id'     => 'drplus-capacity-diag-view',
					'parent' => 'drplus-capacity-diag',
					'title'  => 'View trace log',
					'href'   => wp_nonce_url( admin_url( 'admin-post.php?action=drplus_capacity_diag_view' ), 'drplus_capacity_diag' ),
				)
			);
			$bar->add_node(
				array(
					'id'     => 'drplus-capacity-diag-clear',
					'parent' => 'drplus-capacity-diag',
					'title'  => 'Clear trace log',
					'href'   => wp_nonce_url( admin_url( 'admin-post.php?action=drplus_capacity_diag_view&clear=1' ), 'drplus_capacity_diag' ),
				)
			);
		}
	}

	/**
	 * Persist the flag into a cookie so it survives the checkout POST to a different URL.
	 *
	 * Without this the flag is forgotten the moment the customer leaves the booking page —
	 * which is exactly the request we need to trace.
	 *
	 * @return void
	 */
	public static function set_cookie() {
		$on = isset( $_GET[ self::QUERY ] ) && '1' === (string) $_GET[ self::QUERY ]; // phpcs:ignore WordPress.Security.NonceVerification
		if ( ! $on || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		// phpcs:ignore WordPress.PHP.NoSilencedErrors -- headers may already be sent.
		@setcookie( self::COOKIE, '1', time() + 3600, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
		$_COOKIE[ self::COOKIE ] = '1';
		// The flag may already have been evaluated earlier in this request; drop that answer.
		DrPlus_Child_Capacity_Logger::diag_reset_cache();
	}

	/**
	 * Render the trace log for an administrator.
	 *
	 * @return void
	 */
	public static function render_view() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'دسترسی غیرمجاز.', 'drplus-child' ), 403 );
		}
		check_admin_referer( 'drplus_capacity_diag' );

		if ( isset( $_GET['clear'] ) ) {
			DrPlus_Child_Capacity_Logger::diag_clear();
		}

		$log = DrPlus_Child_Capacity_Logger::diag_tail();

		header( 'Content-Type: text/html; charset=utf-8' );
		header( 'X-Robots-Tag: noindex, nofollow', true );
		header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );

		echo '<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="utf-8">';
		echo '<meta name="robots" content="noindex,nofollow">';
		echo '<title>Dr Plus — Capacity Trace</title><style>';
		echo 'body{font-family:ui-monospace,Menlo,Consolas,monospace;background:#1d2327;color:#c3c4c7;margin:0;padding:20px;direction:ltr}';
		echo 'h1{font-family:system-ui,sans-serif;font-size:1.1rem;color:#fff;margin:0 0 4px;direction:rtl}';
		echo '.bar{font-family:system-ui,sans-serif;margin:0 0 18px;direction:rtl}';
		echo 'a{color:#72aee6;margin-left:10px}';
		echo 'pre{white-space:pre-wrap;word-break:break-all;font-size:.8rem;line-height:1.55;background:#2c3338;padding:16px;border-radius:6px;margin:0}';
		echo '.hi{color:#ffb900}.ok{color:#00d084}.bad{color:#ff6b6b}';
		echo '</style></head><body>';
		echo '<h1>Dr Plus — Capacity Trace</h1>';
		echo '<div class="bar">';
		echo '<a href="' . esc_url( admin_url( 'admin-post.php?action=drplus_capacity_diag_view' ) ) . '">&#8635; Refresh</a>';
		echo '<a href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=drplus_capacity_diag_view&clear=1' ), 'drplus_capacity_diag' ) ) . '">&#128465; Clear</a>';
		echo '<a href="' . esc_url( home_url( '/' ) ) . '">&#8592; Back to site</a>';
		echo '</div>';

		if ( '' === $log ) {
			echo '<pre>No trace yet.';

			echo "\n\nHow to capture a rejected-allowed booking:";
			echo "\n  1. Click 'Capacity Diag: OFF' in the admin bar (turns it red / ON).";
			echo "\n  2. Book 2 appointments for the SAME clinic + date + hour.";
			echo "\n  3. Attempt the 3rd one. This is the request that gets traced.";
			echo "\n  4. Come back here and refresh.";
			echo "\n\nFlag state on this request: " . ( DrPlus_Child_Capacity_Logger::diag_enabled() ? 'ON' : 'OFF' );
			echo '</pre>';
		} else {
			echo '<pre>' . esc_html( $log ) . '</pre>';
		}
		echo '</body></html>';
		exit;
	}
}
