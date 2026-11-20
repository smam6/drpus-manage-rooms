<?php
/**
 * Capacity assignment — does the number the operator typed actually govern bookings?
 *
 * Every other suite asserts predicates (counts, holds, conflicts). This one walks the
 * REAL accept/refuse decision for the whole matrix an operator cares about, because a
 * predicate being right does not prove the checkout reaches the same verdict.
 *
 * Written after a false alarm while doing a final pre-delivery audit: a first draft of
 * this test reported "capacity 2 refuses the second booking". The cause was the test,
 * not the module — validate_booking_capacity() takes the booking lock in Layer 1 and
 * expects post_insert_verify_and_release() to release it after the parent's INSERT. A
 * harness that calls the validator in a loop without that hook leaves the lock held, so
 * every call after the first fails as "could not acquire lock". Releasing between calls
 * (what a real request does) makes the verdicts correct. Worth recording here: the lock
 * failing closed looks exactly like a capacity bug from the outside, and the operator
 * would report it as "it refuses bookings it should allow".
 */

require_once __DIR__ . '/00-bootstrap.php';
if ( false ) {
	wp_die();
}

global $wpdb;

$table  = DrPlus_Child_Capacity_Repository::booking_table();
$office = '614';
$ok     = 0;
$failed = 0;

function cap_chk( $label, $got, $want ) {
	global $ok, $failed;
	$pass = ( $got === $want );
	if ( $pass ) {
		$ok++;
	} else {
		$failed++;
	}
	printf( "%s %-48s got=%-9s want=%s\n", $pass ? 'PASS' : 'FAIL', $label, $got, $want );
}

/**
 * Ask the validator what it would do with this booking, from a clean lock state.
 *
 * @return string ACCEPTED or REFUSED
 */
function verdict( $specialist, $date, $time, $office = '614' ) {
	if ( function_exists( 'wc_clear_notices' ) ) {
		wc_clear_notices();
	}

	$method = new ReflectionMethod( 'DrPlus_Child_Capacity_Validator', 'validate_booking_capacity' );
	$method->setAccessible( true );
	$method->invoke(
		null,
		array(
			'office_id'     => $office,
			'date'          => $date,
			'start_time'    => $time,
			'specialist_id' => $specialist,
		)
	);

	$errors = function_exists( 'wc_get_notices' ) ? wc_get_notices( 'error' ) : array();

	// A real request releases the Layer 1 lock in post_insert_verify_and_release().
	DrPlus_Child_Capacity_Lock_Manager::release_all();

	return empty( $errors ) ? 'ACCEPTED' : 'REFUSED';
}

function cap_row( $specialist, $date, $time, $status, $office = '614' ) {
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
			'end_time'     => '23:30:00',
			'total_price'  => 0,
			'order_id'     => $order->get_id(),
			'order_status' => $status,
			'deleted'      => 0,
			'created_at'   => current_time( 'mysql' ),
		)
	);

	return $order->get_id();
}

update_option( 'drplus_capacity_conflict_mode', 'room' );
delete_option( 'drplus_capacity_clinic_' . $office );
update_option( 'drplus_capacity_clinic_' . $office, 2 );

$date = '2030-04-01';
$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE `date` = %s", $date ) );

printf( "capacity(%s) = %d\n\n", $office, (int) DrPlus_Child_Capacity_Service::capacity_for( $office ) );

cap_chk( 'empty room accepts', verdict( 101, $date, '10:00:00' ), 'ACCEPTED' );
$first = cap_row( 101, $date, '10:00:00', 'processing' );
cap_chk( '1 of 2 booked -> 2nd accepts', verdict( 102, $date, '10:00:00' ), 'ACCEPTED' );
cap_row( 102, $date, '10:00:00', 'processing' );

cap_chk( 'room full -> 3rd refused', verdict( 103, $date, '10:00:00' ), 'REFUSED' );
cap_chk( 'same specialist cannot double-book', verdict( 101, $date, '10:00:00' ), 'REFUSED' );
cap_chk( 'a full hour does not block another slot', verdict( 103, $date, '11:00:00' ), 'ACCEPTED' );
cap_chk( 'a full hour does not block another day', verdict( 103, '2030-04-02', '10:00:00' ), 'ACCEPTED' );
cap_chk( 'online visit is exempt from room capacity', verdict( 104, $date, '10:00:00', 'phone_consultation' ), 'ACCEPTED' );

$first_order = wc_get_order( $first );
$first_order->set_status( 'cancelled' );
$first_order->save();
cap_chk( 'cancelling frees a place', verdict( 103, $date, '10:00:00' ), 'ACCEPTED' );

// The operator changes the number: capacity 1 must immediately refuse a 2nd booking.
$one_date = '2030-04-03';
$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE `date` = %s", $one_date ) );
update_option( 'drplus_capacity_clinic_' . $office, 1 );
cap_chk( 'operator sets capacity 1', (int) DrPlus_Child_Capacity_Service::capacity_for( $office ), 1 );
cap_row( 201, $one_date, '10:00:00', 'processing' );
cap_chk( 'capacity 1 refuses the 2nd booking', verdict( 202, $one_date, '10:00:00' ), 'REFUSED' );

// And raising it must reopen the room.
update_option( 'drplus_capacity_clinic_' . $office, 3 );
cap_chk( 'operator raises it to 3', (int) DrPlus_Child_Capacity_Service::capacity_for( $office ), 3 );
cap_chk( 'capacity 3 accepts the 2nd booking', verdict( 202, $one_date, '10:00:00' ), 'ACCEPTED' );

// Leave the site as it was found.
delete_option( 'drplus_capacity_clinic_' . $office );
$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE `date` IN (%s, %s)", $date, $one_date ) );

printf( "\n==> CAPACITY-ASSIGNMENT RESULT: %d passed, %d failed\n", $ok, $failed );
