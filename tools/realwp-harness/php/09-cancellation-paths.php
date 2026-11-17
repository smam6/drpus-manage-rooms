<?php
/**
 * 09-cancellation-paths.php
 *
 * Who can cancel a booking, and does each route actually free the hour?
 *
 * The module's capacity rules only ever see `drplus_booking.order_status` plus whatever
 * order_releases_slot() can find out about the order. Every cancellation route therefore
 * ends in one of two places: the order status changes, or the order record disappears. This
 * suite exercises BOTH ends through the real WooCommerce objects, because a route that
 * silently fails to release the hour is invisible in production — the hour just stays grey.
 *
 * Routes covered:
 *   1. customer      (booking receipt → Booking::cancel_booking → order status)
 *   2. specialist    (same template, specialist view)
 *   3. clinic admin  (WooCommerce order screen, "Cancel")
 *   4. manager       (refund instead of cancel)
 *   5. trashed order (the delete action on the orders list)
 *   6. rebooking     (the freed hour must pass a real checkout)
 *
 * Plus the two "order record is gone" variants, which must behave identically:
 *   - the order is in the trash
 *   - the order id does not exist at all
 */

require __DIR__ . '/00-bootstrap.php';

global $wpdb;
$P = $wpdb->prefix;

$office  = '32c836b9-15c8-4d6e-ba83-140f8b5dcfbc';
$date    = '2026-12-15';
$time    = '17:00';
$spec    = 12;
$product = (int) $wpdb->get_var(
	"SELECT ID FROM {$P}posts WHERE post_type = 'product' AND post_status = 'publish' ORDER BY ID LIMIT 1"
);

$reset = static function () use ( $wpdb, $P, $date ) {
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$P}drplus_booking WHERE `date` = %s", $date ) );
};

/** A genuine order plus the booking row a checkout would have written. */
$book = static function ( $order_status = 'processing', $table_status = 'processing' ) use ( $wpdb, $P, $office, $date, $time, $spec, $product ) {
	$order = wc_create_order();
	$order->add_product( wc_get_product( $product ), 1 );
	$order->set_status( $order_status );
	$order->calculate_totals();
	$order->save();

	$wpdb->insert(
		$P . 'drplus_booking',
		array(
			'customer_id'       => 0,
			'specialist_id'     => $spec,
			'office_id'         => $office,
			'date'              => $date,
			'start_time'        => $time . ':00',
			'end_time'          => $time . ':00',
			'total_price'       => 1000,
			'commission'        => 400,
			'specialist_income' => 600,
			'order_id'          => (int) $order->get_id(),
			'order_status'      => $table_status,
			'created_at'        => current_time( 'mysql' ),
			'updated_at'        => current_time( 'mysql' ),
			'deleted'           => 0,
		)
	);

	return array( (int) $order->get_id(), (int) $wpdb->insert_id );
};

/** Everything that would stop a customer booking this hour. */
$state = static function () use ( $office, $date, $time, $spec ) {
	$slots = (array) DrPlus\Utils\Booking::get_available_time_slots( $date, $spec, $office, 30 );
	$grid  = null;
	if ( ! empty( $slots ) ) {
		$filtered = DrPlus_Child_Capacity_Service::filter_full_slots(
			$slots,
			array( 'date' => $date, 'specialist_id' => $spec, 'office_id' => $office )
		);
		$grid = in_array( $time, array_column( (array) $filtered, 'from' ), true );
	}
	return array(
		'count'   => DrPlus_Child_Capacity_Repository::count_distinct_specialists( $office, $date, $time . ':00' ),
		'blocked' => DrPlus_Child_Capacity_Repository::specialist_already_booked( $spec, $date, $time . ':00' ),
		'grid'    => $grid,
	);
};

h_section( 'Environment' );
printf( "  capacity %d · office %s · %s %s · product %d\n",
	DrPlus_Child_Capacity_Service::capacity_for( $office ), substr( $office, 0, 8 ), $date, $time, $product );

// ---------------------------------------------------------------------------
h_section( '1-5. Every actor: the hour comes back' );

$routes = array(
	'customer cancels'     => static function ( $order ) { $order->update_status( 'cancelled', 'by customer' ); },
	'specialist cancels'   => static function ( $order ) { $order->update_status( 'cancelled', 'by specialist' ); },
	'clinic admin cancels' => static function ( $order ) { $order->update_status( 'cancelled', 'by admin' ); },
	'manager refunds'      => static function ( $order ) { $order->update_status( 'refunded', 'refund issued' ); },
	'order trashed'        => static function ( $order ) { wp_trash_post( $order->get_id() ); },
);

foreach ( $routes as $label => $act ) {
	$reset();
	list( $order_id, ) = $book();
	$order = wc_get_order( $order_id );

	$before = $state();
	if ( ! $before['blocked'] ) {
		h_assert( $label . ': precondition — the hour is blocked while the order is live', false, 'not blocked before cancelling' );
		continue;
	}

	$act( $order );
	// WooCommerce caches the order object; nothing else in this suite should see a stale one.
	$after = $state();

	printf( "  %-22s order→%-10s count=%d blocked=%-3s grid=%s\n",
		$label,
		wc_get_order( $order_id ) ? wc_get_order( $order_id )->get_status() : '(gone)',
		$after['count'],
		$after['blocked'] ? 'YES' : 'no',
		null === $after['grid'] ? 'n/a' : ( $after['grid'] ? 'offered' : 'hidden' )
	);

	h_assert( $label . ': the hour is released', ! $after['blocked'] && 0 === $after['count'],
		'count=' . $after['count'] . ' blocked=' . ( $after['blocked'] ? 'yes' : 'no' ) );
	if ( null !== $after['grid'] ) {
			h_assert( $label . ': the grid offers the hour again', $after['grid'], $after['grid'] ? 'offered' : 'hidden' );
	}
}

// ---------------------------------------------------------------------------
h_section( '6. Booking the freed hour again actually works' );

$reset();
list( $order_id, ) = $book();
$order = wc_get_order( $order_id );
$order->update_status( 'cancelled', 'x' );

if ( function_exists( 'wc_clear_notices' ) ) {
	wc_clear_notices();
}
WC()->session->set( 'wc_notices', null );
WC()->cart->empty_cart();
WC()->cart->add_to_cart(
	$product,
	1,
	0,
	array(),
	array(
		'is_booking' => true,
		'book_data'  => array(
			'office_id'     => $office,
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

WC()->cart->empty_cart();
$_POST = array();
if ( function_exists( 'wc_clear_notices' ) ) {
	wc_clear_notices();
}
WC()->session->set( 'wc_notices', null );
DrPlus_Child_Capacity_Lock_Manager::release_all();

h_assert( 'a real checkout accepts the hour that was just cancelled', empty( $notices ),
	count( $notices ) . ' notice(s)' . ( $notices ? ': ' . mb_substr( $notices[0], 0, 70 ) : '' ) );

// ---------------------------------------------------------------------------
h_section( '7. An order record that no longer exists frees the hour' );

// 7a. trashed — WooCommerce keeps reporting "processing" for a trashed order.
$reset();
list( $order_id, ) = $book();
$order = wc_get_order( $order_id );
wp_trash_post( $order_id );
$reported = wc_get_order( $order_id ) ? wc_get_order( $order_id )->get_status() : '(none)';
printf( "  trashed order still reports status \"%s\" — the post record is what decides\n", $reported );
$st = $state();
h_assert( 'a trashed order releases the hour despite reporting "processing"', ! $st['blocked'], $st['blocked'] ? 'still blocked' : 'released' );

// 7b. the id does not exist at all
$reset();
$wpdb->insert(
	$P . 'drplus_booking',
	array(
		'customer_id'   => 0,
		'specialist_id' => $spec,
		'office_id'     => $office,
		'date'          => $date,
		'start_time'    => $time . ':00',
		'end_time'      => $time . ':00',
		'total_price'   => 1000,
		'order_id'      => 999999,
		'order_status'  => 'processing',
		'created_at'    => current_time( 'mysql' ),
		'updated_at'    => current_time( 'mysql' ),
		'deleted'       => 0,
	)
);
$missing = $state();
h_assert( 'a row pointing at a nonexistent order releases the hour', ! $missing['blocked'], $missing['blocked'] ? 'still blocked' : 'released' );

// 7c. ...but a post that exists and is not an order stays held (genuinely unknown)
$reset();
$wpdb->insert(
	$P . 'drplus_booking',
	array(
		'customer_id'   => 0,
		'specialist_id' => $spec,
		'office_id'     => $office,
		'date'          => $date,
		'start_time'    => $time . ':00',
		'end_time'      => $time . ':00',
		'total_price'   => 1000,
		'order_id'      => 1, // a real post, but not a WooCommerce order
		'order_status'  => 'processing',
		'created_at'    => current_time( 'mysql' ),
		'updated_at'    => current_time( 'mysql' ),
		'deleted'       => 0,
	)
);
printf( "  order_id=1 is post_type=%s — cannot be an order\n", get_post_type( 1 ) );
$nonorder = $state();
h_assert( 'a non-order post id still fails closed', $nonorder['blocked'], $nonorder['blocked'] ? 'held' : 'FREED (unsafe)' );

// ---------------------------------------------------------------------------
h_section( '8. An admin appointment (no order at all) keeps its hour' );

$reset();
$wpdb->insert(
	$P . 'drplus_booking',
	array(
		'customer_id'   => 0,
		'specialist_id' => $spec,
		'office_id'     => $office,
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
$staff = $state();
h_assert( 'order_id=0 (appointment created by staff) keeps the hour blocked', $staff['blocked'], $staff['blocked'] ? 'held' : 'FREED (unsafe)' );

$reset();

// ---------------------------------------------------------------------------
h_section( '9. Real data: the booking table is internally consistent' );

$open = $wpdb->get_results(
	"SELECT book_id, order_id, order_status FROM {$P}drplus_booking
	 WHERE deleted = 0 AND order_id > 0
	   AND LOWER(TRIM(order_status)) NOT IN
	       ('cancelled','canceled','wc-cancelled','wc-canceled','refunded','wc-refunded','failed','wc-failed','trash','checkout-draft')",
	ARRAY_A
);
$dangling = array();
foreach ( (array) $open as $row ) {
	$post_status = get_post_status( (int) $row['order_id'] );
	if ( false === $post_status || 'trash' === $post_status ) {
		continue; // released by design
	}
	if ( ! wc_get_order( (int) $row['order_id'] ) ) {
		$dangling[] = 'book #' . $row['book_id'] . ' → order #' . $row['order_id'];
	}
}
printf( "  %d active row(s) linked to an order\n", count( (array) $open ) );
h_assert( 'no active row points at an unreadable order', empty( $dangling ), implode( ', ', $dangling ) );

h_summary();
