<?php
/**
 * Dr Plus Child Theme — Room Capacity Management
 *
 * Implements: spec 2.1, 2.2, 3.1, 7.11, 8.6, 8.7, 8.8, 5.5.58, 5.5.59, 5.5.60, 5.5.61, 5.5.62, 5.5.63
 * Category: 8 — Deployment & compatibility (final hook wiring + asset enqueue)
 *
 * @package DrPlusChild\Capacity
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Module constants. The default capacity value 2 lives ONLY here (spec 5.6.63).
if ( ! defined( 'DRPLUS_ROOM_CAPACITY_DEFAULT' ) ) {
	define( 'DRPLUS_ROOM_CAPACITY_DEFAULT', 2 );
}

if ( ! defined( 'DRPLUS_CAPACITY_DB_VERSION' ) ) {
	define( 'DRPLUS_CAPACITY_DB_VERSION', '1.0.0' );
}

/**
 * Load a capacity module class file from inc/capacity/.
 *
 * Implements: spec 5.5.27, 8.2
 *
 * @param string $class_file File name without extension, e.g. 'class-capacity-repository'.
 * @return void
 */
function drplus_child_capacity_require( $class_file ) {
	$path = trailingslashit( get_stylesheet_directory() ) . 'inc/capacity/' . $class_file . '.php';
	if ( file_exists( $path ) ) {
		require_once $path;
	}
}

drplus_child_capacity_require( 'class-capacity-installer' );
drplus_child_capacity_require( 'class-capacity-repository' );
drplus_child_capacity_require( 'class-capacity-service' );
drplus_child_capacity_require( 'class-capacity-logger' );
drplus_child_capacity_require( 'class-capacity-validator' );
drplus_child_capacity_require( 'class-capacity-admin' );
drplus_child_capacity_require( 'class-capacity-messages' );

/**
 * Is the current request the booking page? Checks both the parent option and the shortcode (spec 5.5.58).
 *
 * Implements: spec 5.5.58, 7.11, 5.5.19, 5.5.20
 *
 * @return bool
 */
function drplus_child_capacity_is_booking_page() {
	$page_id = 0;
	if ( class_exists( 'DrPlus\\Utils\\Booking' ) ) {
		$page_id = (int) DrPlus\Utils\Booking::get_booking_page_id();
	} else {
		$page_id = (int) get_option( 'drplus_booking_page_id', 0 );
	}
	if ( $page_id && is_page( $page_id ) ) {
		return true;
	}
	$post = get_post();
	return $post && has_shortcode( $post->post_content, 'drplus_booking' );
}

/**
 * Wire the slot filter. Priority 20, 2 args — matches the parent contract (spec 2.1, 2.2, 5.5.61-5.5.63).
 *
 * Implements: spec 2.1, 2.2, 5.5.61, 5.5.62, 5.5.63
 *
 * @return void
 */
function drplus_child_capacity_wire_hooks() {
	if ( ! DrPlus_Child_Capacity_Service::enabled() ) {
		return;
	}
	// Slot hiding: full in-person hours are removed from the AJAX/template response (spec 2.3).
	add_filter( 'drplus/booking/available_time_slots', array( 'DrPlus_Child_Capacity_Service', 'filter_full_slots' ), 20, 2 );

	// Server-side validation on checkout, before the booking row is written
	// (woocommerce_checkout_process at class-wc-checkout.php:1378 < create_order :1411).
	// Date check first (fail fast), then capacity (spec 3.1, conflict resolution #1).
	add_action( 'woocommerce_checkout_process', array( 'DrPlus_Child_Capacity_Validator', 'check_missing_date' ), 4 );
	add_action( 'woocommerce_checkout_process', array( 'DrPlus_Child_Capacity_Validator', 'validate_capacity' ), 5 );
}
add_action( 'init', 'drplus_child_capacity_wire_hooks', 20 );

/**
 * Create the log table on theme activation and on version bump (spec 6.4, 5.5.74).
 *
 * Implements: spec 6.4, 5.5.74
 *
 * @return void
 */
function drplus_child_capacity_install() {
	DrPlus_Child_Capacity_Installer::maybe_install();
}
add_action( 'after_switch_theme', 'drplus_child_capacity_install' );
add_action( 'init', 'drplus_child_capacity_install', 1 );

/**
 * Remove module options when the site switches AWAY from this child theme (audit W11).
 * The log table is intentionally kept for data retention.
 *
 * Implements: spec 5.5.16 (non-destructive activation), audit item W11
 *
 * @return void
 */
function drplus_child_capacity_cleanup_on_switch_away() {
	if ( get_option( 'stylesheet' ) === 'dr-plus-child' ) {
		return;
	}
	global $wpdb;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery — option cleanup by prefix, no user input.
	$option_names = $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s", 'drplus_capacity_%' ) );
	foreach ( (array) $option_names as $option_name ) {
		delete_option( $option_name );
	}
}
add_action( 'after_switch_theme', 'drplus_child_capacity_cleanup_on_switch_away', 20 );

/**
 * Register the admin settings page and its save handler (spec 5.1).
 *
 * Implements: spec 5.1, 5.5.66
 *
 * @return void
 */
function drplus_child_capacity_wire_admin() {
	if ( ! is_admin() ) {
		return;
	}
	add_action( 'admin_menu', array( 'DrPlus_Child_Capacity_Admin', 'register_menu' ) );
	add_action( 'admin_init', array( 'DrPlus_Child_Capacity_Admin', 'handle_save' ) );
}
add_action( 'init', 'drplus_child_capacity_wire_admin', 5 );

/**
 * Enqueue module assets ONLY on the booking page, versioned by DRPLUS_CHILD_VERSION (spec 7.11, 8.8, 5.5.59, 5.5.60).
 *
 * Implements: spec 7.11, 8.8, 5.5.59, 5.5.60, 5.5.87, 7.10
 *
 * @return void
 */
function drplus_child_capacity_enqueue_assets() {
	if ( ! drplus_child_capacity_is_booking_page() ) {
		return;
	}
	$base = trailingslashit( get_stylesheet_directory_uri() );
	wp_enqueue_style( 'drplus-capacity', $base . 'assets/css/capacity.css', array(), DRPLUS_CHILD_VERSION );
	wp_enqueue_script( 'drplus-capacity', $base . 'assets/js/capacity.js', array( 'jquery' ), DRPLUS_CHILD_VERSION, true );
	// All client strings come from here — no hardcoded Persian in JS (spec 7.10, 5.5.87, 5.5.88).
	wp_localize_script( 'drplus-capacity', 'drplusCapacity', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'i18n'    => array(
			'noDate' => DrPlus_Child_Capacity_Messages::missing_date(),
		),
	) );
}
add_action( 'wp_enqueue_scripts', 'drplus_child_capacity_enqueue_assets' );
