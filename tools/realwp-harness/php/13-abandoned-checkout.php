<?php
/**
 * 13-abandoned-checkout.php — guard against a dead checkout pinning an hour forever.
 *
 * THE REPORTED BUG
 *
 *   «اصلا کلا برای هیچ متخصصی اجازه رزرو نمیده … در حالی که این متخصصین هیچ رزروی ندارند»
 *
 * A specialist with an EMPTY appointments list could not be booked at all. Two separate
 * mechanisms produce that, and this file covers the second:
 *
 *   1. The specialist id was not resolvable  -> covered by 12-stale-session.php.
 *   2. The row is REAL but its owner is GONE -> covered here.
 *
 * HOW (2) HAPPENS, with no phantom contradiction needed:
 *
 *   The customer reaches checkout, the parent INSERTs the booking row, then the payment
 *   fails / the tab closes / the gateway times out. The row is left at `pending` (or
 *   `on-hold`). The parent has a cron that is *supposed* to cancel those after 30 minutes,
 *   but this module cannot assume it runs — sites disable wp-cron, sites with no traffic
 *   never trigger it, plugins purge the schedule. When it does not run, that unpaid row
 *   holds its hour forever, and it is invisible in the appointments list (the parent
 *   filters `pending` out). Every abandoned attempt pins one more hour.
 *
 * THE FIX
 *
 * `row_holds_slot()` releases a `pending`/`on-hold` row once it is older than
 * `abandoned_checkout_ttl()` (default 2 h, 4x the parent's own 30-minute window). Real
 * appointments — `processing`, `completed`, or anything not yet paid-off — are never
 * released by age. An unreadable timestamp keeps holding (fail closed).
 *
 * TWO TRAPS THIS FILE PINS DOWN, both of which were live bugs while this was being written:
 *
 *   a) The SQL must SELECT `created_at`. Without it the age guard could not read the clock
 *      and silently returned "still holding" — the fix looked correct and did nothing.
 *   b) `created_at` carries no timezone, and the parent has written it both as UTC and as
 *      site-local time. The guard takes the LATER of the two readings so a timezone
 *      mismatch can only ever EXTEND a hold, never cut a live payment short.
 *
 * @package DrPlusChild\Capacity
 */

require_once __DIR__ . '/00-bootstrap.php';

global $wpdb;
$table  = $wpdb->prefix . 'drplus_booking';
$date   = '2027-09-01';
$results = array();
$fail    = 0;

/**
 * Assert.
 *
 * @param string $name What is being checked.
 * @param mixed  $got  Observed.
 * @param mixed  $want Expected.
 */
function chk( $name, $got, $want ) {
	global $results, $fail;
	$ok = ( $got === $want );
	if ( ! $ok ) {
		$fail++;
	}
	$results[] = $ok;
	printf( "%s %-58s got=%-6s want=%s\n", $ok ? 'PASS' : 'FAIL', $name, var_export( $got, true ), var_export( $want, true ) );
}

/**
 * Create a booking row aged by N minutes.
 *
 * `created_at` is written with the SITE clock, because that is what the parent's ORM
 * writes on this stack. Writing it with bare `date()` here would build a fixture that
 * disagrees with production by the host's UTC offset and would test nothing.
 *
 * @param int    $spec    Specialist id.
 * @param string $when    Y-m-d.
 * @param string $time    H:i:s.
 * @param string $status  order_status.
 * @param int    $age_min Minutes old.
 * @return int Order id.
 */
function mk_row( $spec, $when, $time, $status, $age_min ) {
	global $wpdb, $table;

	$order = wc_create_order();
	$order->set_status( $status );
	$order->save();

	$created = gmdate( 'Y-m-d H:i:s', current_time( 'timestamp', true ) - ( $age_min * 60 ) );

	$wpdb->insert(
		$table,
		array(
			'customer_id'  => 0,
			'specialist_id' => $spec,
			'office_id'    => '614',
			'date'         => $when,
			'start_time'   => $time,
			'end_time'     => '23:30:00',
			'total_price'  => 0,
			'order_id'     => $order->get_id(),
			'order_status' => $status,
			'deleted'      => 0,
			'created_at'   => $created,
		)
	);

	return $order->get_id();
}

/**
 * Wipe the fixture day.
 *
 * @param string $when Date.
 */
function clear_day( $when ) {
	global $wpdb, $table;
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE `date` = %s", $when ) );
}

$wpdb->query( "DELETE FROM {$table}" );

// ---------------------------------------------------------------------------------------
// 1. A payment still in flight keeps its hold.
// ---------------------------------------------------------------------------------------
clear_day( $date );
mk_row( 13, $date, '10:00:00', 'pending', 5 );
chk( 'fresh pending (5 min) still HOLDS', DrPlus_Child_Capacity_Repository::specialist_already_booked( 13, $date, '10:00:00' ), true );

// ---------------------------------------------------------------------------------------
// 2. An abandoned checkout releases its hour — this is the reported symptom.
// ---------------------------------------------------------------------------------------
clear_day( $date );
mk_row( 13, $date, '10:00:00', 'pending', 180 );
chk( 'abandoned pending (3 h) is RELEASED', DrPlus_Child_Capacity_Repository::specialist_already_booked( 13, $date, '10:00:00' ), false );

clear_day( $date );
mk_row( 13, $date, '10:00:00', 'on-hold', 180 );
chk( 'abandoned on-hold (3 h) is RELEASED', DrPlus_Child_Capacity_Repository::specialist_already_booked( 13, $date, '10:00:00' ), false );

// ---------------------------------------------------------------------------------------
// 3. A REAL appointment is never released by age, however old the row is.
//    If this ever passes with false, the module has started quietly double-booking.
// ---------------------------------------------------------------------------------------
clear_day( $date );
mk_row( 13, $date, '10:00:00', 'processing', 180 );
chk( 'old PROCESSING still HOLDS (real appointment)', DrPlus_Child_Capacity_Repository::specialist_already_booked( 13, $date, '10:00:00' ), true );

clear_day( $date );
mk_row( 13, $date, '10:00:00', 'completed', 10000 );
chk( 'ancient COMPLETED still HOLDS', DrPlus_Child_Capacity_Repository::specialist_already_booked( 13, $date, '10:00:00' ), true );

// ---------------------------------------------------------------------------------------
// 4. A cancelled row is free at any age, and an unreadable clock keeps holding.
// ---------------------------------------------------------------------------------------
clear_day( $date );
mk_row( 13, $date, '10:00:00', 'cancelled', 5 );
chk( 'cancelled stays FREE regardless of age', DrPlus_Child_Capacity_Repository::specialist_already_booked( 13, $date, '10:00:00' ), false );

clear_day( $date );
$order = wc_create_order();
$order->set_status( 'pending' );
$order->save();
$wpdb->insert(
	$table,
	array(
		'customer_id'  => 0,
		'specialist_id' => 13,
		'office_id'    => '614',
		'date'         => $date,
		'start_time'   => '10:00:00',
		'end_time'     => '10:30:00',
		'total_price'  => 0,
		'order_id'     => $order->get_id(),
		'order_status' => 'pending',
		'deleted'      => 0,
		'created_at'   => '0000-00-00 00:00:00',
	)
);
chk( 'unreadable created_at keeps holding (fail closed)',
	DrPlus_Child_Capacity_Repository::specialist_already_booked( 13, $date, '10:00:00' ), true );

// ---------------------------------------------------------------------------------------
// 5. The grid and checkout must agree. They share row_holds_slot(), but they run
//    different SQL, and a missing column in one of them is exactly how they diverged.
// ---------------------------------------------------------------------------------------
clear_day( $date );
mk_row( 13, $date, '10:00:00', 'pending', 180 );
$grid = DrPlus_Child_Capacity_Repository::specialist_booked_slots_for_day( 13, $date, '614' );
chk( 'grid does NOT hide a released hour', isset( $grid['10:00:00'] ), false );

clear_day( $date );
mk_row( 13, $date, '10:00:00', 'pending', 5 );
$grid = DrPlus_Child_Capacity_Repository::specialist_booked_slots_for_day( 13, $date, '614' );
chk( 'grid DOES hide a live hold', isset( $grid['10:00:00'] ), true );

// ---------------------------------------------------------------------------------------
// 6. The TTL can be tuned but never disabled.
// ---------------------------------------------------------------------------------------
$add_ttl = function ( $ttl ) {
	return 0;
};
add_filter( 'drplus_child_capacity/abandoned_checkout_ttl', $add_ttl );
$floor = DrPlus_Child_Capacity_Repository::abandoned_checkout_ttl();
remove_filter( 'drplus_child_capacity/abandoned_checkout_ttl', $add_ttl );
chk( 'a filter cannot set the TTL to 0 (hold forever)', $floor >= 30 * MINUTE_IN_SECONDS, true );

$default_ttl = DrPlus_Child_Capacity_Repository::abandoned_checkout_ttl();
chk( 'the default TTL is well above the parent 30-minute window', $default_ttl > 30 * MINUTE_IN_SECONDS, true );

clear_day( $date );
$wpdb->query( "DELETE FROM {$table}" );

echo "\n==> ABANDONED-CHECKOUT RESULT: " . count( $results ) . ' passed, ' . $fail . " failed\n";
exit( $fail > 0 ? 1 : 0 );
