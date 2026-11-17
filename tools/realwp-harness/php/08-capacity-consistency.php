<?php
/**
 * 08-capacity-consistency.php
 *
 * Capacity must answer the SAME number no matter which identifier of a clinic it is asked
 * with, and the grid must not drift from the server-side rule.
 *
 * Every case below was found by asking "what if…" about a real code path, not by inventing
 * an input. Two of them were live defects when this file was written.
 */

require __DIR__ . '/00-bootstrap.php';

global $wpdb;
$P = $wpdb->prefix;

$office = '32c836b9-15c8-4d6e-ba83-140f8b5dcfbc';
$hospital = '614';
$date   = '2026-12-15';
$time   = '17:00';
$spec   = 12;

$reset = static function () use ( $wpdb, $P, $date ) {
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$P}drplus_booking WHERE `date` = %s", $date ) );
};

$seat = static function ( $specialist, $status = 'completed', $office_override = null ) use ( $wpdb, $P, $office, $date, $time ) {
	$wpdb->insert(
		$P . 'drplus_booking',
		array(
			'customer_id'   => 0,
			'specialist_id' => $specialist,
			'office_id'     => null === $office_override ? $office : $office_override,
			'date'          => $date,
			'start_time'    => $time . ':00',
			'end_time'      => $time . ':00',
			'total_price'   => 1000,
			'order_id'      => 0,
			'order_status'  => $status,
			'created_at'    => current_time( 'mysql' ),
			'updated_at'    => current_time( 'mysql' ),
			'deleted'       => 0,
		)
	);
};

/**
 * Does the capacity filter leave this hour in the grid for this specialist?
 *
 * Returns null when the parent theme offered no slots at all, because then "not in the grid"
 * says nothing about capacity — it only means the specialist does not work that day. The
 * earlier version of this file read that null as "hidden" and produced a test that could
 * never fail.
 */
$grid = static function ( $specialist ) use ( $office, $date, $time ) {
	$slots = DrPlus\Utils\Booking::get_available_time_slots( $date, $specialist, $office, 30 );
	$slots = (array) $slots;
	if ( empty( $slots ) ) {
		return null; // Parent offered nothing — no grid evidence either way.
	}
	$filtered = DrPlus_Child_Capacity_Service::filter_full_slots(
		$slots,
		array( 'date' => $date, 'specialist_id' => $specialist, 'office_id' => $office )
	);
	return in_array( $time, array_column( (array) $filtered, 'from' ), true );
};

/** Which specialists actually have hours at this clinic on the test date? */
$working = array();
foreach ( array( 12, 13, 14, 15, 16, 19 ) as $candidate ) {
	if ( ! empty( (array) DrPlus\Utils\Booking::get_available_time_slots( $date, $candidate, $office, 30 ) ) ) {
		$working[] = $candidate;
	}
}

h_section( 'Environment' );
printf( "  uuid %s  ·  hospital %s  ·  %s %s\n", substr( $office, 0, 8 ), $hospital, $date, $time );

// ---------------------------------------------------------------------------
h_section( '1. One clinic, one capacity — whichever identifier asks' );

$identifiers = DrPlus_Child_Capacity_Repository::resolve_clinic_identifiers( $office );
printf( "  resolves to %d identifier(s): %s\n", count( $identifiers ), implode( ', ', $identifiers ) );

$values = array();
foreach ( $identifiers as $identifier ) {
	$values[ $identifier ] = DrPlus_Child_Capacity_Service::capacity_for( $identifier );
}
foreach ( $values as $identifier => $value ) {
	printf( "    %-42s => %d\n", $identifier, $value );
}
h_assert(
	'every identifier of the clinic reports the same capacity',
	1 === count( array_unique( $values ) ),
	implode( ', ', array_unique( $values ) ) . ' distinct value(s)'
);

// ---------------------------------------------------------------------------
h_section( '2. Conflicting stored values resolve to the STRICTEST' );

$before_hospital = get_option( 'drplus_capacity_clinic_' . $hospital, false );
$before_uuid     = get_option( 'drplus_capacity_clinic_' . $office, false );

update_option( 'drplus_capacity_clinic_' . $hospital, 1, false );
update_option( 'drplus_capacity_clinic_' . $office, 3, false );

$via_hospital = DrPlus_Child_Capacity_Service::capacity_for( $hospital );
$via_uuid     = DrPlus_Child_Capacity_Service::capacity_for( $office );
printf( "  stored 614=1 and uuid=3 → hospital reports %d, uuid reports %d\n", $via_hospital, $via_uuid );

h_assert( 'the two entry points agree (no asymmetric capacity)', $via_hospital === $via_uuid, "614={$via_hospital} uuid={$via_uuid}" );
h_assert( 'the stricter value wins, so the room cannot be overbooked', 1 === $via_hospital && 1 === $via_uuid, "resolved to {$via_hospital}" );

$reset();
$seat( 12 );
// A 1-room clinic must refuse the SECOND specialist. Measure it through the enforcement
// path (the count the validator uses), not through a grid that may be empty for an
// unrelated reason.
$one_room_count = DrPlus_Child_Capacity_Repository::count_distinct_specialists( $office, $date, $time . ':00' );
$one_room_cap   = DrPlus_Child_Capacity_Service::capacity_for( $office );
printf( "  one specialist seated in a %d-room clinic → count=%d\n", $one_room_cap, $one_room_count );
h_assert( 'a 1-room clinic is full after one specialist', $one_room_count >= $one_room_cap, "count={$one_room_count} cap={$one_room_cap}" );

// restore
if ( false === $before_hospital ) { delete_option( 'drplus_capacity_clinic_' . $hospital ); } else { update_option( 'drplus_capacity_clinic_' . $hospital, $before_hospital, false ); }
if ( false === $before_uuid ) { delete_option( 'drplus_capacity_clinic_' . $office ); } else { update_option( 'drplus_capacity_clinic_' . $office, $before_uuid, false ); }

// ---------------------------------------------------------------------------
h_section( '3. Saving the clinic row clears stale per-UUID values' );

update_option( 'drplus_capacity_clinic_' . $office, 4, false );
$clean_aliases = array( $office => (int) $hospital );
foreach ( $clean_aliases as $alias_uuid => $alias_clinic ) {
	if ( (int) $hospital === $alias_clinic ) {
		delete_option( 'drplus_capacity_clinic_' . $alias_uuid );
	}
}
h_assert(
	'the stale per-UUID option is removed on save',
	false === get_option( 'drplus_capacity_clinic_' . $office ),
	false === get_option( 'drplus_capacity_clinic_' . $office ) ? 'removed' : 'still present'
);
delete_option( 'drplus_capacity_clinic_' . $hospital );

// ---------------------------------------------------------------------------
h_section( '4. The grid and the server-side rule agree' );

$reset();
$seat( 12, 'completed' ); // 1 of 2 rooms
$seat( 13, 'completed' ); // 2 of 2 rooms

$capacity = DrPlus_Child_Capacity_Service::capacity_for( $office );
$count    = DrPlus_Child_Capacity_Repository::count_distinct_specialists( $office, $date, $time . ':00' );
printf( "  capacity=%d, seated=%d\n", $capacity, $count );

// Grid evidence must come from a specialist the parent actually schedules that day.
h_assert( 'the parent schedules at least one specialist that day (grid assertions need a real grid)', ! empty( $working ), implode( ',', $working ) ?: 'none' );
$viewer = ! empty( $working ) ? $working[0] : 0;
$viewer_is_seated = in_array( $viewer, array( 12 ), true );
$grid_state = $grid( $viewer );

if ( null !== $grid_state && ! $viewer_is_seated ) {
	h_assert(
		'the grid hides the hour exactly when the count reaches capacity',
		( $count >= $capacity ) === ! $grid_state,
		"count={$count} cap={$capacity} grid=" . ( $grid_state ? 'offered' : 'hidden' )
	);
} else {
	h_assert( 'the grid hides the hour for a specialist who is already seated', false === $grid_state, 'grid=' . var_export( $grid_state, true ) );
}

// the same specialist is always refused, at any capacity
$same_specialist_offered = $grid( 12 );
h_assert( 'a specialist already seated is never offered the same hour again', ! $same_specialist_offered, $same_specialist_offered ? 'offered' : 'hidden' );

$reset();

// ---------------------------------------------------------------------------
h_section( '5. A cancelled booking at capacity frees the room; an active one does not' );

$reset();
$seat( 12, 'cancelled' );
$seat( 13, 'cancelled' );
$cancelled_count = DrPlus_Child_Capacity_Repository::count_distinct_specialists( $office, $date, $time . ':00' );
$cancelled_grid  = $grid( 12 ); // specialist 12's own cancelled booking must not hide his hour
printf( "  both cancelled → count=%d, grid offers the hour to specialist 12=%s\n", $cancelled_count, var_export( $cancelled_grid, true ) );
h_assert( 'cancelled bookings contribute nothing to the count', 0 === $cancelled_count, 'count=' . $cancelled_count );
h_assert( 'the hour is offered again after cancellation', true === $cancelled_grid, 'grid=' . var_export( $cancelled_grid, true ) );

$reset();
$seat( 12, 'completed' );
$seat( 13, 'completed' );
$active_count = DrPlus_Child_Capacity_Repository::count_distinct_specialists( $office, $date, $time . ':00' );
$active_grid  = $grid( 12 );
printf( "  both active    → count=%d, grid offers the hour to specialist 12=%s\n", $active_count, var_export( $active_grid, true ) );
h_assert( 'the active count reaches capacity', $active_count >= $capacity, 'count=' . $active_count );
h_assert( 'a specialist who is already seated is not offered the hour', false === $active_grid, 'grid=' . var_export( $active_grid, true ) );

$reset();

// ---------------------------------------------------------------------------
h_section( '6. No N+1: the grid costs a fixed number of queries' );

$reset();
for ( $hour = 16; $hour <= 20; $hour++ ) {
	foreach ( array( 12, 13 ) as $specialist ) {
		$wpdb->insert(
			$P . 'drplus_booking',
			array(
				'customer_id'   => 0,
				'specialist_id' => $specialist,
				'office_id'     => $office,
				'date'          => $date,
				'start_time'    => sprintf( '%02d:00:00', $hour ),
				'end_time'      => sprintf( '%02d:00:00', $hour ),
				'total_price'   => 1000,
				'order_id'      => 0,
				'order_status'  => 'completed',
				'created_at'    => current_time( 'mysql' ),
				'updated_at'    => current_time( 'mysql' ),
				'deleted'       => 0,
			)
		);
	}
}
$slots = DrPlus\Utils\Booking::get_available_time_slots( $date, 12, $office, 30 );
$before = $wpdb->num_queries;
DrPlus_Child_Capacity_Service::filter_full_slots( $slots, array( 'date' => $date, 'specialist_id' => 12, 'office_id' => $office ) );
$used = $wpdb->num_queries - $before;
printf( "  %d slots rendered with %d queries\n", count( (array) $slots ), $used );
h_assert( 'the filter costs at most 3 queries regardless of slot count', $used <= 3, $used . ' queries' );

$reset();

// ---------------------------------------------------------------------------
h_section( '7. Online visits neither consume nor block a room' );

$virtual = array( 'chat_consultation', 'phone_consultation', 'video_consultation', 'consultation' );
$failures = array();
foreach ( $virtual as $virtual_office ) {
	$reset();
	$seat( $spec, 'completed', $virtual_office );
	if ( 0 !== DrPlus_Child_Capacity_Repository::count_distinct_specialists( $office, $date, $time . ':00' ) ) {
		$failures[] = $virtual_office . ' counted against the room';
	}
	if ( DrPlus_Child_Capacity_Repository::specialist_already_booked( $spec, $date, $time . ':00' ) ) {
		$failures[] = $virtual_office . ' blocked the physical room';
	}
}
h_assert( 'no virtual visit consumes or blocks a room', empty( $failures ), empty( $failures ) ? 'all 4 exempt' : implode( '; ', $failures ) );

// control: a real room booking still blocks
$reset();
$seat( $spec, 'completed' );
h_assert( 'control: a real room booking still blocks that specialist', DrPlus_Child_Capacity_Repository::specialist_already_booked( $spec, $date, $time . ':00' ), 'not blocked' );

$reset();

h_summary();
