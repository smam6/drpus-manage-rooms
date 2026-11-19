<?php
/**
 * 10-conflict-policy.php
 *
 * The "one specialist, one hour" policy.
 *
 * A specialist is one person. A room booking and a phone call cannot both happen at 17:00.
 * The parent theme never checks this — its slot generator asks only "is this specialist busy
 * at THIS office?", so a room hour and a phone hour are independent (measured, not assumed).
 *
 * Two policies exist:
 *
 *   room (default)  a specialist may not occupy two ROOMS at once; a virtual visit is free
 *   any             a specialist may hold only ONE booking of any kind per hour
 *
 * Each scenario runs in a FRESH PROCESS. That matters: the WC cart, the notices store and the
 * advisory lock all survive between calls inside one process, and an earlier version of this
 * suite produced a false "accept" for online->online purely because of that leakage.
 */

require_once __DIR__ . '/00-bootstrap.php';

global $wpdb;
$P = $wpdb->prefix;

$office  = '32c836b9-15c8-4d6e-ba83-140f8b5dcfbc';
$date    = '2026-12-15';
$time    = '17:00';
$spec    = 12;
$product = (int) $wpdb->get_var(
	"SELECT ID FROM {$P}posts WHERE post_type = 'product' AND post_status = 'publish' ORDER BY ID LIMIT 1"
);

/** The scenario to run, chosen by the caller via a constant so it cannot leak. */
$scenario = defined( 'DRPLUS_SCENARIO' ) ? DRPLUS_SCENARIO : 'in_person_then_online';
$mode     = defined( 'DRPLUS_MODE' ) ? DRPLUS_MODE : 'room';

update_option( 'drplus_capacity_conflict_mode', $mode, false );
$wpdb->query( $wpdb->prepare( "DELETE FROM {$P}drplus_booking WHERE `date` = %s", $date ) );
$wpdb->query( "DELETE FROM {$P}drplus_capacity_lock" );

$add = static function ( $office_id, $specialist ) use ( $wpdb, $P, $office, $date, $time ) {
	$wpdb->insert(
		$P . 'drplus_booking',
		array(
			'customer_id'   => 0,
			'specialist_id' => $specialist,
			'office_id'     => $office_id,
			'date'          => $date,
			'start_time'    => $time . ':00',
			'end_time'      => $time . ':00',
			'total_price'   => 1000,
			'order_id'      => 0,
			'order_status'  => 'completed',
			'created_at'    => current_time( 'mysql' ),
			'updated_at'    => current_time( 'mysql' ),
			'deleted'       => 0,
		)
	);
};

$targets = array(
	'in_person_then_online' => array( 'seed' => $office, 'ask' => 'phone_consultation' ),
	'online_then_in_person' => array( 'seed' => 'phone_consultation', 'ask' => $office ),
	'same_specialist_twice' => array( 'seed' => $office, 'ask' => $office ),
	'full_room_then_online' => array( 'seed' => 'FULL', 'ask' => 'phone_consultation' ),
	'full_room_then_person' => array( 'seed' => 'FULL', 'ask' => $office ),
	'online_then_online'    => array( 'seed' => 'phone_consultation', 'ask' => 'chat_consultation' ),
);

if ( ! isset( $targets[ $scenario ] ) ) {
	echo "unknown scenario: {$scenario}\n";
	exit( 1 );
}

$cfg = $targets[ $scenario ];
if ( 'FULL' === $cfg['seed'] ) {
	$add( $office, 13 );
	$add( $office, 14 );
} else {
	$add( $cfg['seed'], $spec );
}

if ( function_exists( 'wc_clear_notices' ) ) {
	wc_clear_notices();
}
WC()->session->set( 'wc_notices', null );
WC()->cart->empty_cart();
DrPlus_Child_Capacity_Lock_Manager::release_all();

WC()->cart->add_to_cart(
	$product,
	1,
	0,
	array(),
	array(
		'is_booking' => true,
		'book_data'  => array(
			'office_id'     => $cfg['ask'],
			'date'          => $date,
			'start_time'    => $time,
			'specialist_id' => $spec,
		),
	)
);
$_POST = array( 'payment_method' => 'cod', 'billing_email' => 'qa@example.com' );
do_action( 'woocommerce_checkout_process' );

$notices = array();
foreach ( (array) wc_get_notices( 'error' ) as $notice ) {
	$notices[] = is_array( $notice ) ? (string) ( $notice['notice'] ?? '' ) : (string) $notice;
}

$wpdb->query( $wpdb->prepare( "DELETE FROM {$P}drplus_booking WHERE `date` = %s", $date ) );

echo 'RESULT ' . $mode . ' ' . $scenario . ' ' . ( empty( $notices ) ? 'ACCEPT' : 'REJECT' ) . "\n";
