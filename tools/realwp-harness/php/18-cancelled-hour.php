<?php
/**
 * A cancelled booking must free its hour — and the diagnostic must be able to tell.
 *
 * Field report: the version-check page said
 *
 *   «1 cancelled booking(s) still block an hour: book #133 (2026-11-26 13:00:00)»  FAIL
 *
 * on a site where that row was correctly released. The row was not the fault. The
 * check was, in three separate ways:
 *
 *   1. Its "peer" query filtered `office_id = <one spelling>`, but the counters expand
 *      a clinic to ALL its identifiers (numeric post id plus every custom-office UUID
 *      for the same clinic). A live booking filed under the other spelling was
 *      invisible to the peer query and visible to the counter, so a normal hour read
 *      as "blocked by the cancelled row".
 *   2. The peer query ignored specialist_id. A different specialist holding the same
 *      hour is not the cancelled row blocking anything — an hour may hold several
 *      specialists up to the room capacity.
 *   3. It called specialist_already_booked() with no clinic, which is deliberately
 *      clinic-agnostic: a specialist booked at ANOTHER clinic at that hour makes it
 *      true, and that is correct, not a leak.
 *
 * The fix is peer_holds_slot(): it asks the same row_holds_slot() predicate the
 * booking flow uses, scoped to the specialist, excluding the row under judgement.
 *
 * This suite pins BOTH directions. A check that always passes is worse than one that
 * always fails, so every "no leak" assertion here is paired with a case that must be
 * flagged if the predicate ever stops discriminating.
 */

require_once __DIR__ . '/00-bootstrap.php';
if ( false ) {
	wp_die();
}

global $wpdb;

$table = DrPlus_Child_Capacity_Repository::booking_table();
$ok    = 0;
$failed = 0;

function cancel_chk( $label, $got, $want ) {
	global $ok, $failed;
	$pass = ( $got === $want );
	if ( $pass ) {
		$ok++;
	} else {
		$failed++;
	}
	printf( "%s %-56s got=%-6s want=%s\n", $pass ? 'PASS' : 'FAIL', $label, var_export( $got, true ), var_export( $want, true ) );
}

function cancel_row( $specialist, $office, $date, $time, $status ) {
	global $wpdb, $table;

	$order = wc_create_order();
	$order->set_status( $status );
	$order->save();

	$wpdb->insert(
		$table,
		array(
			'customer_id'  => 0,
			'specialist_id' => $specialist,
			'office_id'    => $office,
			'date'         => $date,
			'start_time'   => $time,
			'end_time'     => '13:30:00',
			'total_price'  => 0,
			'order_id'     => $order->get_id(),
			'order_status' => $status,
			'deleted'      => 0,
			'created_at'   => current_time( 'mysql' ),
		)
	);

	return (int) $wpdb->insert_id;
}

$clinic = '614';
$uuid   = '1b9d72ba-f2b2-4dfd-8a3c-5b0f13c5c650'; // a custom office of the SAME clinic
$date   = '2026-12-10';
$time   = '13:00:00';

// ---------------------------------------------------------------------------------------
// 1. The reported situation: a cancelled row alone. It must free its hour.
// ---------------------------------------------------------------------------------------
$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE `date` = %s", $date ) );
$cancelled_id = cancel_row( 6, $clinic, $date, $time, 'cancelled' );

cancel_chk( 'cancelled row frees its specialist', DrPlus_Child_Capacity_Repository::specialist_already_booked( 6, $date, $time ), false );
cancel_chk( 'no peer holds the hour', DrPlus_Child_Capacity_Repository::peer_holds_slot( $cancelled_id, 6, $date, $time ), false );

// ---------------------------------------------------------------------------------------
// 2. The trap that produced the false alarm: a live booking at the SAME clinic and hour,
//    filed under the clinic's other identifier. It is a legitimate holder, so the
//    cancelled row is NOT leaking — the hour is genuinely taken.
// ---------------------------------------------------------------------------------------
cancel_row( 6, $uuid, $date, $time, 'processing' );

cancel_chk( 'a live booking under the UUID counts as a peer', DrPlus_Child_Capacity_Repository::peer_holds_slot( $cancelled_id, 6, $date, $time ), true );
cancel_chk( '  and the hour is legitimately held', DrPlus_Child_Capacity_Repository::specialist_already_booked( 6, $date, $time ), true );

// ---------------------------------------------------------------------------------------
// 3. A DIFFERENT specialist holding the same hour must not be mistaken for a peer.
//    The hour can hold several specialists; that is capacity, not a conflict.
// ---------------------------------------------------------------------------------------
$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE `date` = %s", $date ) );
$cancelled_id2 = cancel_row( 6, $clinic, $date, $time, 'cancelled' );
cancel_row( 11, $clinic, $date, $time, 'processing' );

cancel_chk( 'another specialist is NOT a peer for specialist 6', DrPlus_Child_Capacity_Repository::peer_holds_slot( $cancelled_id2, 6, $date, $time ), false );
cancel_chk( '  so specialist 6 is genuinely free', DrPlus_Child_Capacity_Repository::specialist_already_booked( 6, $date, $time ), false );

// ---------------------------------------------------------------------------------------
// 4. The predicate must still SAY NO when something really does hold the hour.
//    Without this, the check above could pass by always returning false.
// ---------------------------------------------------------------------------------------
cancel_chk(
	'the predicate is not stuck at false (live row blocks)',
	DrPlus_Child_Capacity_Repository::specialist_already_booked( 11, $date, $time ),
	true
);

// ---------------------------------------------------------------------------------------
// 5. Every released status behaves the same way.
// ---------------------------------------------------------------------------------------
foreach ( array( 'cancelled', 'refunded', 'failed' ) as $released ) {
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE `date` = %s", $date ) );
	cancel_row( 6, $clinic, $date, $time, $released );
	cancel_chk( "a {$released} row frees its hour", DrPlus_Child_Capacity_Repository::specialist_already_booked( 6, $date, $time ), false );
}

$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE `date` = %s", $date ) );

printf( "\n==> CANCELLED-HOUR RESULT: %d passed, %d failed\n", $ok, $failed );
