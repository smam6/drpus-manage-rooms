<?php
/**
 * 07-order-status-truth.php
 *
 * The scenario the previous suite could not see.
 *
 * Every capacity count reads `drplus_booking.order_status`. That column is a COPY of the
 * WooCommerce order's status, refreshed by the parent theme's listener — which is
 * ionCube-encoded and therefore unauditable. A copy can go stale, and when it does the
 * failure is silent: a cancelled appointment keeps its hour blocked forever and no error is
 * ever displayed.
 *
 * These tests build REAL WooCommerce orders, cancel them, deliberately leave the copied
 * column stale, and check that the hour is still released — while an order that is genuinely
 * alive still blocks, and an order that cannot be read fails closed.
 */

require __DIR__ . '/00-bootstrap.php';

global $wpdb;
$P = $wpdb->prefix;

$office = '32c836b9-15c8-4d6e-ba83-140f8b5dcfbc';
$date   = '2026-12-15';
$time   = '17:00';
$spec   = 12;
$cap    = DrPlus_Child_Capacity_Service::capacity_for( $office );
$product = (int) $wpdb->get_var(
	"SELECT ID FROM {$P}posts WHERE post_type = 'product' AND post_status = 'publish' ORDER BY ID LIMIT 1"
);

h_section( 'Environment' );
printf( "  capacity %d · office %s · %s %s · product %d\n", $cap, substr( $office, 0, 8 ), $date, $time, $product);

/** A genuine WooCommerce order in a chosen state. */
$make_order = static function ( $status ) use ( $product ) {
	$order = wc_create_order();
	$order->add_product( wc_get_product( $product ), 1 );
	$order->set_status( $status );
	$order->calculate_totals();
	$order->save();
	return (int) $order->get_id();
};

/** A booking row pointing at an order, with a deliberately chosen (possibly stale) status. */
$make_row = static function ( $specialist, $order_id, $table_status ) use ( $wpdb, $P, $office, $date, $time ) {
	$wpdb->insert(
		$P . 'drplus_booking',
		array(
			'customer_id'   => 0,
			'specialist_id' => $specialist,
			'office_id'     => $office,
			'date'          => $date,
			'start_time'    => $time . ':00',
			'end_time'      => $time . ':00',
			'total_price'   => 1000,
			'order_id'      => $order_id,
			'order_status'  => $table_status,
			'created_at'    => current_time( 'mysql' ),
			'updated_at'    => current_time( 'mysql' ),
			'deleted'       => 0,
		)
	);
	return (int) $wpdb->insert_id;
};

$reset = static function () use ( $wpdb, $P, $date ) {
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$P}drplus_booking WHERE `date` = %s", $date ) );
};

$count = static function () use ( $office, $date, $time ) {
	return DrPlus_Child_Capacity_Repository::count_distinct_specialists( $office, $date, $time . ':00' );
};
$blocked = static function () use ( $spec, $date, $time ) {
	return DrPlus_Child_Capacity_Repository::specialist_already_booked( $spec, $date, $time . ':00' );
};

// ---------------------------------------------------------------------------
h_section( '1. A live order still holds the hour' );
$reset();
$live = $make_order( 'processing' );
$make_row( $spec, $live, 'processing' );
h_assert( 'order "processing" + row "processing" → blocked', $blocked() && 1 === $count(), 'count=' . $count() );

// ---------------------------------------------------------------------------
h_section( '2. Cancel the ORDER, leave the row stale → the hour must come back' );

$order = wc_get_order( $live );
$order->set_status( 'cancelled' );
$order->save();

$wc_now = wc_get_order( $live )->get_status();
$db_now = $wpdb->get_var( "SELECT order_status FROM {$P}drplus_booking WHERE order_id = {$live}" );
printf( "  order says \"%s\", the row still says \"%s\"\n", $wc_now, $db_now );

h_assert( 'the row is genuinely stale (this is the condition under test)', 'cancelled' === $wc_now && 'processing' === $db_now, "order={$wc_now} row={$db_now}" );
h_assert( 'count ignores the stale row', 0 === $count(), 'count=' . $count() );
h_assert( 'the specialist is not blocked by the stale row', ! $blocked(), $blocked() ? 'blocked' : 'free' );

$slots = DrPlus\Utils\Booking::get_available_time_slots( $date, $spec, $office, 30 );
$filtered = DrPlus_Child_Capacity_Service::filter_full_slots(
	$slots,
	array( 'date' => $date, 'specialist_id' => $spec, 'office_id' => $office )
);
$froms = array();
foreach ( (array) $filtered as $slot ) {
	$froms[] = $slot['from'];
}
h_assert( 'the grid offers the hour again', in_array( $time, $froms, true ), 'grid: ' . implode( ' ', $froms ) );

// ---------------------------------------------------------------------------
h_section( '3. reconcile_order_statuses() detects and repairs the stale row' );

$dry = DrPlus_Child_Capacity_Repository::reconcile_order_statuses( false );
printf( "  dry run : inspected=%d stale=%d repaired=%d\n", $dry['inspected'], $dry['stale'], $dry['repaired'] );
foreach ( $dry['examples'] as $example ) {
	printf( "            %s\n", $example );
}
h_assert( 'a dry run FINDS the stale row', $dry['stale'] >= 1, 'stale=' . $dry['stale'] );
h_assert( 'a dry run changes nothing', 0 === $dry['repaired'], 'repaired=' . $dry['repaired'] );

$fix = DrPlus_Child_Capacity_Repository::reconcile_order_statuses( true );
printf( "  repair  : inspected=%d stale=%d repaired=%d\n", $fix['inspected'], $fix['stale'], $fix['repaired'] );
$after = $wpdb->get_var( "SELECT order_status FROM {$P}drplus_booking WHERE order_id = {$live}" );
h_assert( 'the repair copies the order status into the row', 'cancelled' === $after, 'row now says ' . var_export( $after, true ) );
$again = DrPlus_Child_Capacity_Repository::reconcile_order_statuses( false );
h_assert( 'nothing is stale afterwards', 0 === $again['stale'], 'stale=' . $again['stale'] );

// ---------------------------------------------------------------------------
h_section( '4. Fail-closed cases (must NOT release the hour)' );

$reset();

// 4a. Order deleted outright.
$gone = $make_order( 'processing' );
$make_row( $spec, $gone, 'processing' );
wp_delete_post( $gone, true );
h_assert( 'a DELETED order keeps the hour blocked (unknown is not free)', $blocked(), $blocked() ? 'blocked' : 'FREED' );

$reset();

// 4b. No order at all — an admin-created appointment.
$make_row( $spec, 0, 'completed' );
h_assert( 'order_id=0 (admin appointment) keeps the hour blocked', $blocked(), $blocked() ? 'blocked' : 'FREED' );

$reset();

// 4c. Row with an empty status but a live order.
$alive = $make_order( 'processing' );
$make_row( $spec, $alive, '' );
h_assert( 'an empty column with a LIVE order still blocks', $blocked(), $blocked() ? 'blocked' : 'FREED' );

$reset();

// ---------------------------------------------------------------------------
h_section( '5. Duplicate rows and soft deletes are not double-counted' );

$dup = $make_order( 'processing' );
$make_row( 12, $dup, 'processing' );
$make_row( 12, $dup, 'processing' ); // same specialist, twice
$make_row( 16, $make_order( 'cancelled' ), 'processing' ); // stale, must not count
printf( "  3 rows on the slot: specialist 12 x2 (live), specialist 16 (stale-cancelled)\n" );
h_assert( 'distinct count is 1, not 3', 1 === $count(), 'count=' . $count() );

$wpdb->query( "UPDATE {$P}drplus_booking SET deleted = 1 WHERE specialist_id = 12 AND `date` = '{$date}'" );
h_assert( 'soft-deleted rows do not count', 0 === $count(), 'count=' . $count() );

$reset();

// ---------------------------------------------------------------------------
h_section( '6. This site\'s real data: is any row stale right now?' );

$real = DrPlus_Child_Capacity_Repository::reconcile_order_statuses( false );
printf( "  %d order-linked row(s) inspected\n", $real['inspected'] );
printf( "  %d stale\n", $real['stale'] );
printf( "  %d order(s) could not be read\n", $real['unreadable'] );
foreach ( $real['examples'] as $example ) {
	printf( "    %s\n", $example );
}
h_assert( 'the real data has no stale statuses (no silent blocked hours)', 0 === $real['stale'], $real['stale'] . ' stale' );

// ---------------------------------------------------------------------------
h_section( '7. An online consultation must never block a physical room' );

$virtual = array( 'chat_consultation', 'phone_consultation', 'video_consultation', 'consultation' );
$room_slot_free = array();
foreach ( $virtual as $virtual_office ) {
	$reset();
	$wpdb->insert(
		$P . 'drplus_booking',
		array(
			'customer_id'   => 0,
			'specialist_id' => $spec,
			'office_id'     => $virtual_office,
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
	$is_blocked = $blocked();
	if ( $is_blocked ) {
		$room_slot_free[] = $virtual_office;
	}
}
h_assert(
	'a virtual visit does not make the specialist read as "already booked" in a room',
	empty( $room_slot_free ),
	empty( $room_slot_free ) ? 'all 4 spellings exempt' : 'still blocking: ' . implode( ', ', $room_slot_free )
);

// The reverse must hold too: a real room booking is still a conflict.
$reset();
$make_row( $spec, $make_order( 'processing' ), 'processing' );
h_assert( 'control: a REAL room booking still blocks the same specialist', $blocked(), $blocked() ? 'blocked' : 'FREE (unsafe)' );

$reset();

h_summary();
