<?php
/**
 * 06-cancelled-frees-slot.php
 *
 * Question this suite answers: "when a booking is cancelled, does that hour become
 * bookable again?"
 *
 * Tested at three levels, because a passing count does not prove a bookable hour:
 *
 *   1. every spelling of a cancelled status, against real parent-generated slots,
 *      through the SAME filter the booking grid uses;
 *   2. the real checkout path (woocommerce_checkout_process + a real WC cart), so
 *      "no notice" is observed rather than inferred;
 *   3. every cancelled row in the production dump, cross-checked against whatever
 *      else is on that slot — a cancelled row must free the room, but a slot that
 *      ALSO holds an active booking must stay blocked.
 */

require __DIR__ . '/00-bootstrap.php';

global $wpdb;
$P = $wpdb->prefix;

$office  = '32c836b9-15c8-4d6e-ba83-140f8b5dcfbc';
$date    = '2026-12-15';
$spec    = 12;
$product = (int) $wpdb->get_var(
	"SELECT ID FROM {$P}posts WHERE post_type = 'product' AND post_status = 'publish' ORDER BY ID LIMIT 1"
);
$capacity = DrPlus_Child_Capacity_Service::capacity_for( $office );

/** Real slots the parent theme offers this specialist that day. */
$parent_slots = class_exists( 'DrPlus\\Utils\\Booking' )
	? DrPlus\Utils\Booking::get_available_time_slots( $date, $spec, $office, 30 )
	: array();
$hours = array();
foreach ( (array) $parent_slots as $slot ) {
	if ( ! empty( $slot['available'] ) && isset( $slot['from'] ) ) {
		$hours[] = $slot['from'];
	}
}

h_section( "Environment" );
printf( "  capacity            : %d\n", $capacity );
printf( "  parent offers       : %d hour(s) on %s — %s\n", count( $hours ), $date, implode( ' ', $hours ) );
h_assert( 'the parent theme offers slots to test against', count( $hours ) >= 4, count( $hours ) . ' slots' );

/** Fill a slot with $n rows carrying one status. */
$fill = static function ( $office_id, $d, $h, $status, $n ) use ( $wpdb, $P ) {
	for ( $i = 0; $i < $n; $i++ ) {
		$wpdb->insert(
			$P . 'drplus_booking',
			array(
				'customer_id'   => 0,
				'specialist_id' => 13 + $i,
				'office_id'     => $office_id,
				'date'          => $d,
				'start_time'    => $h . ':00',
				'end_time'      => $h . ':00',
				'total_price'   => 1000,
				'order_status'  => $status,
				'created_at'    => current_time( 'mysql' ),
				'updated_at'    => current_time( 'mysql' ),
				'deleted'       => 0,
			)
		);
	}
};

/** Is this hour still offered by the grid after our filter? */
$offered = static function ( $office_id, $d, $h, $specialist ) use ( $office ) {
	$slots = class_exists( 'DrPlus\\Utils\\Booking' )
		? DrPlus\Utils\Booking::get_available_time_slots( $d, $specialist, $office_id, 30 )
		: array();
	$filtered = DrPlus_Child_Capacity_Service::filter_full_slots(
		$slots,
		array( 'date' => $d, 'specialist_id' => $specialist, 'office_id' => $office_id )
	);
	foreach ( (array) $filtered as $slot ) {
		if ( isset( $slot['from'] ) && $h === $slot['from'] ) {
			return true;
		}
	}
	return false;
};

// ---------------------------------------------------------------------------
h_section( '1. Every cancelled spelling frees the hour (grid level)' );

$cancelled_forms = array(
	'cancelled', 'canceled', 'wc-cancelled', 'wc-canceled',
	'refunded', 'wc-refunded', 'failed', 'wc-failed',
	'trash', 'checkout-draft', 'CANCELLED', '  cancelled  ',
);

$checked = 0;
$failures = array();
foreach ( $cancelled_forms as $status ) {
	foreach ( $hours as $hour ) {
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$P}drplus_booking WHERE `date` = %s", $date ) );
		$fill( $office, $date, $hour, $status, $capacity ); // room full of cancellations

		$count = DrPlus_Child_Capacity_Repository::count_distinct_specialists( $office, $date, $hour . ':00' );
		$is_offered = $offered( $office, $date, $hour, $spec );
		$checked++;

		if ( 0 !== $count || ! $is_offered ) {
			$failures[] = sprintf( "'%s' at %s: count=%d offered=%s", $status, $hour, $count, $is_offered ? 'yes' : 'NO' );
		}
	}
}
h_assert(
	"all cancelled spellings free the room and re-offer the hour",
	0 === count( $failures ),
	$checked . ' checks, ' . count( $failures ) . ' failed'
);
foreach ( array_slice( $failures, 0, 5 ) as $f ) {
	printf( "      %s\n", $f );
}

h_section( '2. Active statuses keep the hour blocked (control)' );

$active_forms = array( 'completed', 'on-hold', 'pending', 'processing' );
$checked2 = 0;
$failures2 = array();
foreach ( $active_forms as $status ) {
	foreach ( $hours as $hour ) {
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$P}drplus_booking WHERE `date` = %s", $date ) );
		$fill( $office, $date, $hour, $status, $capacity );

		$count     = DrPlus_Child_Capacity_Repository::count_distinct_specialists( $office, $date, $hour . ':00' );
		$is_offered = $offered( $office, $date, $hour, $spec );
		$checked2++;

		if ( $count < $capacity || $is_offered ) {
			$failures2[] = sprintf( "'%s' at %s: count=%d offered=%s", $status, $hour, $count, $is_offered ? 'yes' : 'no' );
		}
	}
}
h_assert(
	"a full hour of ACTIVE bookings stays hidden",
	0 === count( $failures2 ),
	$checked2 . ' checks, ' . count( $failures2 ) . ' failed'
);
foreach ( array_slice( $failures2, 0, 5 ) as $f ) {
	printf( "      %s\n", $f );
}

// ---------------------------------------------------------------------------
h_section( '3. The real checkout accepts a previously-cancelled hour' );

$time = $hours[0];

/** Run a genuine checkout and return the error notices WooCommerce raised. */
$checkout = static function ( $office_id, $d, $h, $specialist, $product_id ) {
	if ( function_exists( 'wc_clear_notices' ) ) {
		wc_clear_notices();
	}
	WC()->session->set( 'wc_notices', null );
	WC()->cart->empty_cart();
	WC()->cart->add_to_cart(
		$product_id,
		1,
		0,
		array(),
		array(
			'is_booking' => true,
			'book_data'  => array(
				'office_id'     => $office_id,
				'date'          => $d,
				'start_time'    => $h,
				'specialist_id' => $specialist,
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

	return $notices;
};

// (a) room was full, every booking cancelled
$wpdb->query( $wpdb->prepare( "DELETE FROM {$P}drplus_booking WHERE `date` = %s", $date ) );
$fill( $office, $date, $time, 'cancelled', $capacity );
$notices = $checkout( $office, $date, $time, $spec, $product );
h_assert(
	'room full of CANCELLED bookings → checkout accepts',
	0 === count( $notices ),
	count( $notices ) . ' notice(s)' . ( $notices ? ': ' . mb_substr( $notices[0], 0, 70 ) : '' )
);

// (b) the same specialist cancelled his own booking there
$wpdb->query( $wpdb->prepare( "DELETE FROM {$P}drplus_booking WHERE `date` = %s", $date ) );
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
		'order_status'  => 'cancelled',
		'created_at'    => current_time( 'mysql' ),
		'updated_at'    => current_time( 'mysql' ),
		'deleted'       => 0,
	)
);
$notices = $checkout( $office, $date, $time, $spec, $product );
h_assert(
	'the SAME specialist whose booking was cancelled can rebook',
	0 === count( $notices ),
	count( $notices ) . ' notice(s)' . ( $notices ? ': ' . mb_substr( $notices[0], 0, 70 ) : '' )
);

// (c) control — an active booking must still be refused
$wpdb->query( $wpdb->prepare( "DELETE FROM {$P}drplus_booking WHERE `date` = %s", $date ) );
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
		'order_status'  => 'completed',
		'created_at'    => current_time( 'mysql' ),
		'updated_at'    => current_time( 'mysql' ),
		'deleted'       => 0,
	)
);
$notices = $checkout( $office, $date, $time, $spec, $product );
$refused = false;
foreach ( $notices as $notice ) {
	if ( false !== mb_strpos( $notice, 'قبلاً رزرو دارد' ) ) {
		$refused = true;
	}
}
h_assert( 'control: an ACTIVE booking is still refused', $refused, count( $notices ) . ' notice(s)' );

$wpdb->query( $wpdb->prepare( "DELETE FROM {$P}drplus_booking WHERE `date` = %s", $date ) );

// ---------------------------------------------------------------------------
h_section( '4. Every CANCELLED row in the production dump' );

$rows = $wpdb->get_results(
	"SELECT book_id, specialist_id, office_id, `date`, start_time
	 FROM {$P}drplus_booking
	 WHERE LOWER(TRIM(order_status)) = 'cancelled' AND deleted = 0
	 ORDER BY book_id",
	ARRAY_A
);

$freed        = 0;
$held_by_peer = 0;
$problems     = array();

foreach ( (array) $rows as $row ) {
	// Anything else — not this cancelled row — still occupying the slot?
	$peers = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$P}drplus_booking
			 WHERE office_id = %s AND `date` = %s AND start_time = %s
			   AND deleted = 0 AND book_id <> %d
			   AND LOWER(TRIM(order_status)) NOT IN
			       ('cancelled','canceled','wc-cancelled','wc-canceled','refunded','wc-refunded','failed','wc-failed','trash','checkout-draft')",
			$row['office_id'],
			$row['date'],
			$row['start_time'],
			$row['book_id']
		)
	);

	$count  = DrPlus_Child_Capacity_Repository::count_distinct_specialists( $row['office_id'], $row['date'], $row['start_time'] );
	$locked = DrPlus_Child_Capacity_Repository::specialist_already_booked( (int) $row['specialist_id'], $row['date'], $row['start_time'] );

	if ( 0 === $peers ) {
		// Nothing else on the slot: the cancellation alone must have freed it.
		if ( 0 === $count && ! $locked ) {
			$freed++;
		} else {
			$problems[] = sprintf( 'book %s: alone on its slot yet count=%d blocked=%s', $row['book_id'], $count, $locked ? 'yes' : 'no' );
		}
	} else {
		$held_by_peer++;
		if ( 0 === $count && ! $locked ) {
			$problems[] = sprintf( 'book %s: %d active peer row(s) but nothing counted', $row['book_id'], $peers );
		}
	}
}

printf( "  %d cancelled row(s) inspected\n", count( (array) $rows ) );
printf( "  %d slot(s) released by the cancellation alone\n", $freed );
printf( "  %d slot(s) still held by a DIFFERENT active booking (correctly blocked)\n", $held_by_peer );

h_assert( 'the dump actually contains cancelled rows to check', count( (array) $rows ) > 0, count( (array) $rows ) . ' rows' );
h_assert( 'no cancelled row keeps a room it should have released', 0 === count( $problems ), count( $problems ) . ' problem(s)' );
foreach ( array_slice( $problems, 0, 5 ) as $p ) {
	printf( "      %s\n", $p );
}

h_summary();
