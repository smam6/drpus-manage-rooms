<?php
/**
 * 05-multi-uuid-clinic.php — regression test for the reported ticket:
 *
 *   «این متخصص در این ساعت قبلاً رزرو دارد» on an hour the grid offered.
 *
 * Root cause: one physical clinic is recorded under several custom-office UUIDs
 * (each specialist gets its own UUID, all carrying the SAME clinic name and phone).
 * The checkout rule is clinic-agnostic, so it found the booking and refused; the
 * slot filter was scoped to the single UUID the customer was on, so it did not.
 * The grid offered an hour that could never be booked.
 *
 * The fix resolves identifiers from the site's own data (name + phone recorded on
 * each custom office), so it works with NO manual alias configuration.
 */

require __DIR__ . '/00-bootstrap.php';

global $wpdb;
$P = $wpdb->prefix;

// This suite tests the AUTOMATIC resolution: no manual alias map allowed.
delete_option( 'drplus_capacity_clinic_aliases' );
DrPlus_Child_Capacity_Repository::flush_office_identity_cache();

$date = '2026-11-19';
$time = '14:00:00';
$uuid_a = '32c836b9-15c8-4d6e-ba83-140f8b5dcfbc';

$wpdb->query( $wpdb->prepare( "DELETE FROM {$P}drplus_booking WHERE `date` = %s", $date ) );

h_section( 'Automatic clinic identity (no manual alias map)' );

$resolved = DrPlus_Child_Capacity_Repository::resolve_clinic_identifiers( $uuid_a );
h_assert( 'the production UUID expands to more than itself', count( $resolved ) > 1, count( $resolved ) . ' identifiers' );
h_assert( 'it includes hospital post 614', in_array( '614', $resolved, true ), implode( ', ', $resolved ) );

$siblings = array( '1b9d72ba-f2b2-4dfd-8a3c-5b0f13c5c650', 'bfafd267-63c7-4a5b-95d8-02e2690cd7aa', '1ed7bf95-a064-4dc5-a41a-43e1b4494984' );
foreach ( $siblings as $sibling ) {
	h_assert( 'sibling UUID resolves to the same set: ' . substr( $sibling, 0, 8 ), in_array( $sibling, $resolved, true ) );
}

$from_clinic = DrPlus_Child_Capacity_Repository::resolve_clinic_identifiers( '614' );
sort( $resolved );
sort( $from_clinic );
h_assert( 'resolution is symmetric from the clinic id', $resolved === $from_clinic );

h_section( 'Two specialists, two UUIDs, one hour → 2/2 rooms' );

$bookings = array(
	array( 13, $uuid_a ),
	array( 50, 'bfafd267-63c7-4a5b-95d8-02e2690cd7aa' ),
);
foreach ( $bookings as $row ) {
	$wpdb->insert(
		$P . 'drplus_booking',
		array(
			'customer_id'   => 0,
			'specialist_id' => $row[0],
			'office_id'     => $row[1],
			'date'          => $date,
			'start_time'    => $time,
			'end_time'      => '14:45:00',
			'total_price'   => 1000,
			'order_status'  => 'completed',
			'created_at'    => current_time( 'mysql' ),
			'updated_at'    => current_time( 'mysql' ),
			'deleted'       => 0,
		)
	);
}

$count_a = DrPlus_Child_Capacity_Repository::count_distinct_specialists( $uuid_a, $date, $time );
$count_b = DrPlus_Child_Capacity_Repository::count_distinct_specialists( 'bfafd267-63c7-4a5b-95d8-02e2690cd7aa', $date, $time );
$count_c = DrPlus_Child_Capacity_Repository::count_distinct_specialists( '614', $date, $time );
$capacity = DrPlus_Child_Capacity_Service::capacity_for( $uuid_a );

h_assert( 'the hour is full from UUID A', $count_a === $capacity, "$count_a/$capacity" );
h_assert( 'the hour is full from UUID B', $count_b === $capacity, "$count_b/$capacity" );
h_assert( 'the hour is full from clinic 614', $count_c === $capacity, "$count_c/$capacity" );

h_section( 'The grid must not offer a full hour on ANY entry point' );

foreach ( array( '614', $uuid_a, '1b9d72ba-f2b2-4dfd-8a3c-5b0f13c5c650' ) as $entry ) {
	$third = 15; // a specialist with no booking at this hour
	$slots = class_exists( 'DrPlus\\Utils\\Booking' )
		? DrPlus\Utils\Booking::get_available_time_slots( $date, $third, $entry, 30 )
		: array();
	$filtered = DrPlus_Child_Capacity_Service::filter_full_slots(
		$slots,
		array( 'date' => $date, 'specialist_id' => $third, 'office_id' => $entry )
	);
	$offered = false;
	foreach ( (array) $filtered as $slot ) {
		if ( isset( $slot['from'] ) && '14:00' === $slot['from'] ) {
			$offered = true;
		}
	}
	h_assert( '14:00 hidden when entering via ' . substr( $entry, 0, 12 ), ! $offered );
}

h_section( 'A free hour is still offered (no over-blocking)' );

$free_time = '16:00:00';
$wpdb->insert(
	$P . 'drplus_booking',
	array(
		'customer_id'   => 0,
		'specialist_id' => 13,
		'office_id'     => $uuid_a,
		'date'          => $date,
		'start_time'    => $free_time,
		'end_time'      => '16:45:00',
		'total_price'   => 1000,
		'order_status'  => 'completed',
		'created_at'    => current_time( 'mysql' ),
		'updated_at'    => current_time( 'mysql' ),
		'deleted'       => 0,
	)
);
h_assert(
	'specialist 13 cannot take 16:00 again',
	DrPlus_Child_Capacity_Repository::specialist_already_booked( 13, $date, $free_time )
);
h_assert(
	'a different specialist CAN take 16:00 (1 of 2 rooms used)',
	! DrPlus_Child_Capacity_Repository::specialist_already_booked( 15, $date, $free_time )
);

// Leave the dataset as production has it.
$wpdb->query( $wpdb->prepare( "DELETE FROM {$P}drplus_booking WHERE `date` = %s", $date ) );

h_summary();
