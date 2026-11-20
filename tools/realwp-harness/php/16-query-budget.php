<?php
/**
 * Query budget for one day-grid render.
 *
 * The booking grid is on the critical path of every visit to the booking page, and it
 * loads every row for the selected day. Two places used to re-do per-request work per
 * ROW instead of per request:
 *
 *   * resolve_clinic_identifiers() re-read the specialists table and the posts/postmeta
 *     index for every row (9 identical read pairs for 30 rows);
 *   * order_releases_slot() loaded the same order repeatedly.
 *
 * Neither was a correctness bug, but both multiplied a fixed cost by the number of rows
 * in a day, which is exactly the number that grows with a clinic's success. This pins
 * the budget so a future edit cannot quietly reintroduce it.
 */

require_once __DIR__ . '/00-bootstrap.php';
if ( false ) {
	wp_die();
}

global $wpdb;

$table       = DrPlus_Child_Capacity_Repository::booking_table();
$date        = '2029-12-20';
$ok          = 0;
$failed      = 0;
$rows        = 300; // 30 orders x 10 hours — a busy day, and few distinct orders.
$order_ids   = array();
$times       = array( '09:00:00', '10:00:00', '11:00:00', '12:00:00', '13:00:00', '14:00:00', '15:00:00', '16:00:00', '17:00:00', '18:00:00' );

function budget_chk( $label, $got, $want ) {
	global $ok, $failed;
	$good = ( $got <= $want );
	if ( $good ) {
		$ok++;
	} else {
		$failed++;
	}
	printf(
		"%s %-54s %d (budget %d)\n",
		$good ? 'PASS' : 'FAIL',
		$label,
		(int) $got,
		(int) $want
	);
}

$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE `date` = %s", $date ) );

// 30 orders, 10 rows each: 300 rows but only 30 distinct order ids, so a per-row
// implementation and a per-order one are easy to tell apart.
foreach ( range( 1, 30 ) as $i ) {
	$order = wc_create_order();
	$order->set_status( 'processing' );
	$order->save();
	$order_ids[ $i ] = $order->get_id();

	foreach ( $times as $time ) {
		$wpdb->insert(
			$table,
			array(
				'customer_id'  => 0,
				'specialist_id' => 200 + $i,
				'office_id'    => '614',
				'date'         => $date,
				'start_time'   => $time,
				'end_time'     => '09:30:00',
				'total_price'  => 0,
				'order_id'     => $order->get_id(),
				'order_status' => 'processing',
				'deleted'      => 0,
				'created_at'   => current_time( 'mysql' ),
			)
		);
	}
}

if ( ! defined( 'SAVEQUERIES' ) ) {
	define( 'SAVEQUERIES', true );
}

$wpdb->queries = array();
$started       = microtime( true );
$day_counts    = DrPlus_Child_Capacity_Repository::count_day_slots( '614', $date );
$elapsed_ms    = ( microtime( true ) - $started ) * 1000;
$query_count   = count( $wpdb->queries );

printf( "rendered %d rows in %.1f ms, %d queries\n", $rows, $elapsed_ms, $query_count );

// The clinic alias must be resolved ONCE per request, not once per row. Its two reads
// are the specialists table and the posts/postmeta index.
$alias_reads = 0;
foreach ( $wpdb->queries as $logged ) {
	if ( false !== strpos( $logged[0], 'drplus_specialists' ) || false !== strpos( $logged[0], 'postmeta' ) && false !== strpos( $logged[0], 'p.ID' ) ) {
		$alias_reads++;
	}
}
budget_chk( 'clinic alias resolved once per render, not per row', $alias_reads, 2 );

// Coarse ceiling. A per-row order load was ~3 queries x 300 rows; per-order it is
// 3 x 30. The exact number is an implementation detail, the order of magnitude is not.
budget_chk( 'total queries stay proportional to ORDERS not ROWS', $query_count, 130 );

// Correctness must not have been traded for the budget: every hour must be full.
$expected = array_fill_keys( $times, 30 );
$actual   = array();
foreach ( $day_counts as $slot => $count ) {
	$actual[ $slot ] = $count;
}
$shape_ok = ( count( $actual ) === count( $expected ) );
foreach ( $expected as $slot => $want ) {
	if ( ! isset( $actual[ $slot ] ) || $actual[ $slot ] !== $want ) {
		$shape_ok = false;
	}
}
if ( $shape_ok ) {
	$ok++;
} else {
	$failed++;
}
printf(
	"%s %-54s %s\n",
	$shape_ok ? 'PASS' : 'FAIL',
	'every one of the 10 hours still reports 30 bookings',
	$shape_ok ? 'yes' : wp_json_encode( $actual )
);

$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE `date` = %s", $date ) );

printf( "\n==> QUERY-BUDGET RESULT: %d passed, %d failed\n", $ok, $failed );
