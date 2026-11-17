<?php
/**
 * 02-doctor.php — prove the environment is real and the module is loaded.
 */

require __DIR__ . '/00-bootstrap.php';

h_section( 'Runtime' );
printf( "  PHP                : %s\n", PHP_VERSION );
printf( "  WordPress          : %s\n", get_bloginfo( 'version' ) );
printf( "  DB driver          : %s\n", get_class( $GLOBALS['wpdb'] ) );
printf( "  db version         : %s\n", $GLOBALS['wpdb']->db_version() );
printf( "  table prefix       : %s\n", $GLOBALS['wpdb']->prefix );
printf( "  WooCommerce        : %s\n", defined( 'WC_VERSION' ) ? WC_VERSION : 'NOT LOADED' );
printf( "  active theme       : %s (parent: %s)\n", get_option( 'stylesheet' ), get_option( 'template' ) );
printf( "  is child theme     : %s\n", get_stylesheet() !== get_template() ? 'yes' : 'NO' );

h_section( 'Data (must match the production dump)' );
$expect = array(
	'drplus_booking'     => 53,
	'drplus_specialists' => 11,
	'wc_orders'          => 14,
);
foreach ( $expect as $t => $n ) {
	$actual = (int) $GLOBALS['wpdb']->get_var( "SELECT COUNT(*) FROM {$GLOBALS['wpdb']->prefix}$t" );
	h_assert( "$t = $n", $actual === $n, "actual $actual" );
}

h_section( 'Capacity module classes' );
foreach ( array(
	'DrPlus_Child_Capacity_Installer',
	'DrPlus_Child_Capacity_Lock_Manager',
	'DrPlus_Child_Capacity_Repository',
	'DrPlus_Child_Capacity_Service',
	'DrPlus_Child_Capacity_Logger',
	'DrPlus_Child_Capacity_Validator',
	'DrPlus_Child_Capacity_Diag',
	'DrPlus_Child_Capacity_Admin',
	'DrPlus_Child_Capacity_Messages',
) as $class ) {
	h_assert( $class, class_exists( $class ) );
}

h_section( 'Hooks actually registered by the child theme' );
$svc_hook = has_filter( 'drplus/booking/available_time_slots', array( 'DrPlus_Child_Capacity_Service', 'filter_full_slots' ) );
h_assert( 'filter drplus/booking/available_time_slots @20', false !== $svc_hook, 'priority ' . var_export( $svc_hook, true ) );

$date_hook = has_action( 'woocommerce_checkout_process', array( 'DrPlus_Child_Capacity_Validator', 'check_missing_date' ) );
h_assert( 'action woocommerce_checkout_process check_missing_date @4', 4 === $date_hook, 'priority ' . var_export( $date_hook, true ) );

$cap_hook = has_action( 'woocommerce_checkout_process', array( 'DrPlus_Child_Capacity_Validator', 'validate_capacity' ) );
h_assert( 'action woocommerce_checkout_process validate_capacity @5', 5 === $cap_hook, 'priority ' . var_export( $cap_hook, true ) );

$post_hook = has_action( 'woocommerce_checkout_order_processed', array( 'DrPlus_Child_Capacity_Validator', 'post_insert_verify_and_release' ) );
h_assert( 'action woocommerce_checkout_order_processed @9999', 9999 === $post_hook, 'priority ' . var_export( $post_hook, true ) );

h_section( 'WooCommerce session is real' );
h_assert( 'WC() available', function_exists( 'WC' ) && WC() instanceof WooCommerce );
h_assert( 'WC()->cart is a WC_Cart', WC()->cart instanceof WC_Cart, get_class( WC()->cart ) );
h_assert( 'WC()->session is a WC_Session', WC()->session instanceof WC_Session, get_class( WC()->session ) );

h_section( 'Reference data used by the six live scenarios' );
global $wpdb;
$rows = $wpdb->get_results(
	"SELECT specialist_id, `date`, start_time, order_status, deleted
	 FROM {$wpdb->prefix}drplus_booking
	 WHERE order_status <> 'cancelled' AND deleted = 0
	 ORDER BY specialist_id, `date`, start_time LIMIT 12",
	ARRAY_A
);
printf( "  %d genuine (non-cancelled) bookings sampled:\n", count( $rows ) );
foreach ( $rows as $r ) {
	printf( "    specialist %-4s %s %s  [%s]\n", $r['specialist_id'], $r['date'], $r['start_time'], $r['order_status'] );
}

$cancelled = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}drplus_booking WHERE order_status = 'cancelled'" );
$total     = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}drplus_booking" );
printf( "  bookings: %d total, %d cancelled, %d genuine\n", $total, $cancelled, $total - $cancelled );

h_section( 'Option / filter wiring' );
printf( "  capacity default   : %d (constant)\n", defined( 'DRPLUS_ROOM_CAPACITY_DEFAULT' ) ? DRPLUS_ROOM_CAPACITY_DEFAULT : -1 );
printf( "  module enabled()   : %s\n", DrPlus_Child_Capacity_Service::enabled() ? 'true' : 'FALSE' );

h_summary();
