<?php
/**
 * 03-live-scenarios.php — the six live scenarios, ×3 loops, through the REAL
 * `woocommerce_checkout_process` hook and the REAL WooCommerce cart.
 *
 * Nothing here calls the validator directly: the harness fills a genuine
 * WC()->cart item the way the parent theme does, then fires the hook with
 * do_action( 'woocommerce_checkout_process' ) — the exact path a customer's
 * checkout takes — and reads the notices WooCommerce actually holds.
 *
 * Usage: node run.mjs php/03-live-scenarios.php [loop]
 */

require __DIR__ . '/00-bootstrap.php';

global $wpdb;
$P = $wpdb->prefix;

/** Clear every WooCommerce notice so each scenario starts from a clean slate. */
function h_clean_notices() {
	if ( function_exists( 'wc_clear_notices' ) ) {
		wc_clear_notices();
	}
	WC()->session->set( 'wc_notices', null );
}

/**
 * Build a genuine WooCommerce cart containing one booking, then run the checkout
 * validation hook. Mirrors how the parent theme localises booking data.
 */
function h_checkout( array $book_data, array $post = array() ) {
	h_clean_notices();

	WC()->cart->empty_cart();
	$cart_item_key = WC()->cart->add_to_cart(
		h_product_id(),
		1,
		0,
		array(),
		array(
			'is_booking' => true,
			'book_data'  => $book_data,
		)
	);

	$_POST = array_merge(
		array(
			'payment_method' => 'cod',
			'billing_email'  => 'qa@example.com',
		),
		$post
	);

	if ( defined( 'HARNESS_DEBUG' ) && HARNESS_DEBUG ) {
		printf( "  [cart] key=%s items=%d contents=%s\n",
			var_export( $cart_item_key, true ),
			count( (array) WC()->cart->get_cart() ),
			wp_json_encode( array_keys( (array) WC()->cart->get_cart() ) ) );
	}

	if ( defined( 'HARNESS_DEBUG' ) && HARNESS_DEBUG ) {
		foreach ( (array) WC()->cart->get_cart() as $k => $it ) {
			printf( "  [debug] item %s keys=%s book_data=%s\n", substr( $k, 0, 8 ),
				implode( ',', array_keys( $it ) ),
				wp_json_encode( $it['book_data'] ?? null, JSON_UNESCAPED_UNICODE ) );
		}
	}

	if ( defined( 'HARNESS_DEBUG' ) && HARNESS_DEBUG && ! empty( $GLOBALS['HARNESS_TRACE'] ) ) {
		$trace = Debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
		printf( "  [trace] hook call stack size=%d\n", count( $trace ) );
		$ex = DrPlus_Child_Capacity_Validator::extract_booking_from_request();
		printf( "  [trace] extract BEFORE action => %s\n", wp_json_encode( $ex ) );
	}

	if ( defined( 'HARNESS_DEBUG' ) && HARNESS_DEBUG ) {
		printf( "  [pre-hook] extract=%s\n", wp_json_encode( DrPlus_Child_Capacity_Validator::extract_booking_from_request() ) );
		global $wp_hook_probe;
	}
	// The real hook: priority 4 (date guard) then priority 5 (capacity guard).
	do_action( 'woocommerce_checkout_process' );
	if ( defined( 'HARNESS_DEBUG' ) && HARNESS_DEBUG ) {
		printf( "  [post-hook] notices=%d\n", count( (array) wc_get_notices( 'error' ) ) );
	}

	$notices = wc_get_notices( 'error' );
	$texts   = array();
	foreach ( (array) $notices as $notice ) {
		$texts[] = is_array( $notice ) ? (string) ( $notice['notice'] ?? '' ) : (string) $notice;
	}

	if ( $cart_item_key ) {
		WC()->cart->remove_cart_item( $cart_item_key );
	}
	$_POST = array();
	h_clean_notices();

	// In production each checkout is its own HTTP request: locks are released on
	// `shutdown` and every static property starts fresh. The harness runs all
	// scenarios inside one PHP process, so emulating that boundary is required for
	// the results to mean anything.
	h_end_request();

	return $texts;
}

/**
 * A real, purchasable product id from the imported database.
 */
function h_product_id() {
	static $id = null;
	if ( null !== $id ) {
		return $id;
	}
	global $wpdb;
	$found = (int) $wpdb->get_var(
		"SELECT ID FROM {$wpdb->prefix}posts
		 WHERE post_type IN ('product','product_variation') AND post_status = 'publish'
		 ORDER BY ID ASC LIMIT 1"
	);
	if ( $found <= 0 ) {
		$found = wp_insert_post(
			array(
				'post_title'  => 'QA Booking Product',
				'post_type'   => 'product',
				'post_status' => 'publish',
			)
		);
		wp_set_object_terms( $found, 'simple', 'product_type' );
		update_post_meta( $found, '_price', '1000' );
		update_post_meta( $found, '_regular_price', '1000' );
	}
	$id = $found;
	return $id;
}

/**
 * Emulate the end of an HTTP request: release locks and clear every static
 * property the module uses to remember state within a request.
 */
function h_end_request() {
	DrPlus_Child_Capacity_Lock_Manager::release_all();
	// Only the memoised engine probe and the "already rejected" flag are per-request
	// state that would leak between emulated requests.
	$GLOBALS['HARNESS_RESET_OK'] = true;
	foreach ( array( 'DrPlus_Child_Capacity_Validator', 'DrPlus_Child_Capacity_Lock_Manager' ) as $class ) {
		if ( ! class_exists( $class ) ) {
			continue;
		}
		$ref = new ReflectionClass( $class );
		foreach ( $ref->getProperties( ReflectionProperty::IS_STATIC ) as $prop ) {
			if ( ! $prop->isInitialized() ) {
				continue;
			}
			$default = $ref->getDefaultProperties()[ $prop->getName() ] ?? null;
			$prop->setAccessible( true );
			$prop->setValue( null, $default );
		}
	}
}

/** Does a notice contain this exact Persian sentence? */
function h_saw( array $notices, $needle ) {
	foreach ( $notices as $n ) {
		if ( false !== mb_strpos( $n, $needle ) ) {
			return $n;
		}
	}
	return false;
}

/**
 * Pick a real clinic identifier that exists in the imported booking table and is
 * NOT an online visit type, so the capacity path is actually exercised.
 */
function h_inperson_office() {
	static $office = null;
	if ( null !== $office ) {
		return $office;
	}
	global $wpdb;
	$candidates = $wpdb->get_col(
		"SELECT DISTINCT office_id FROM {$wpdb->prefix}drplus_booking
		 WHERE office_id <> '' AND deleted = 0"
	);
	$online = array( 'phone_consultation', 'chat_consultation', 'video_consultation', 'consultation' );
	foreach ( $candidates as $c ) {
		$c = (string) $c;
		if ( '' === $c || in_array( $c, $online, true ) ) {
			continue;
		}
		// Must be a clinic whose slots actually carry bookings.
		$n = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}drplus_booking
				 WHERE office_id = %s AND deleted = 0 AND order_status <> 'cancelled'",
				$c
			)
		);
		if ( $n > 0 ) {
			$office = $c;
			return $office;
		}
	}
	// Fall back to any non-online office seen in the data.
	foreach ( $candidates as $c ) {
		if ( ! in_array( (string) $c, $online, true ) && '' !== (string) $c ) {
			$office = (string) $c;
			return $office;
		}
	}
	$office = '614';
	return $office;
}

/**
 * Find a real slot in the imported data plus the number of distinct genuine
 * specialists occupying it.
 */
function h_slot_with_count( $office_id, $min_count ) {
	global $wpdb;
	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT `date`, start_time, COUNT(DISTINCT specialist_id) AS n
			 FROM {$wpdb->prefix}drplus_booking
			 WHERE office_id = %s AND deleted = 0 AND order_status <> 'cancelled'
			   AND specialist_id > 0
			 GROUP BY `date`, start_time
			 HAVING n >= %d
			 ORDER BY `date`, start_time LIMIT 1",
			$office_id,
			$min_count
		),
		ARRAY_A
	);
	return $rows ? $rows[0] : null;
}

/** A specialist who does NOT already hold the given slot. */
function h_free_specialist( $date, $time ) {
	global $wpdb;
	$id = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT s.user_id FROM {$wpdb->prefix}drplus_specialists s
			 WHERE s.user_id NOT IN (
				SELECT specialist_id FROM {$wpdb->prefix}drplus_booking
				WHERE `date` = %s AND start_time = %s AND deleted = 0
				  AND order_status <> 'cancelled' AND specialist_id > 0
			 )
			 ORDER BY s.user_id ASC LIMIT 1",
			$date,
			$time
		)
	);
	return (int) $id;
}

$GLOBALS['HARNESS_DEBUG_CART'] = true; // set by run.mjs via HARNESS_DEBUG
$loop = isset( $argv[1] ) ? (int) $argv[1] : 1;
$online_type = 'phone_consultation';
$office      = h_inperson_office();

h_section( "Environment (loop $loop)" );
printf( "  clinic in use     : %s\n", $office );
printf( "  product id        : %d\n", h_product_id() );
printf( "  capacity default  : %d\n", DrPlus_Child_Capacity_Service::capacity_for( $office ) );

// ---------------------------------------------------------------- Scenario 1
h_section( 'Scenario 1 — genuine booking, hour under capacity must be ACCEPTED' );

// Find any real slot with exactly one specialist, then book with a different one.
$s1_slot = h_slot_with_count( $office, 1 );
if ( ! $s1_slot ) {
	$s1_slot = array( 'date' => '2026-08-17', 'start_time' => '14:45:00', 'n' => 1 );
}
// Prefer a slot with a single occupant so capacity_for=2 leaves room.
$s1 = $wpdb->get_row(
	$wpdb->prepare(
		"SELECT `date`, start_time, COUNT(DISTINCT specialist_id) AS n
		 FROM {$wpdb->prefix}drplus_booking
		 WHERE office_id = %s AND deleted = 0 AND order_status <> 'cancelled' AND specialist_id > 0
		 GROUP BY `date`, start_time HAVING n = 1
		 ORDER BY `date`, start_time LIMIT 1",
		$office
	),
	ARRAY_A
);
if ( ! $s1 ) {
	$s1 = $s1_slot;
}
$s1_free = h_free_specialist( $s1['date'], $s1['start_time'] );
printf( "  slot %s %s already holds %d specialist(s); booking as specialist %d\n",
	$s1['date'], $s1['start_time'], $s1['n'], $s1_free );

$notices = h_checkout(
	array(
		'office_id'     => $office,
		'date'          => $s1['date'],
		'start_time'    => substr( $s1['start_time'], 0, 5 ),
		'raw_time'      => (int) strtotime( '1970-01-01 ' . $s1['start_time'] ) * 1000,
		'specialist_id' => $s1_free,
	)
);
h_assert( 'no error notice for a valid booking', 0 === count( $notices ), implode( ' | ', $notices ) );

// ---------------------------------------------------------------- Scenario 2
h_section( 'Scenario 2 — FULL hour (2/2 rooms) must be REJECTED with the exact Persian text' );

/*
 * The production data contains NO hour holding two genuine specialists — that is
 * the module working. To test the rejection path we must create the condition:
 * add one more real booking to a real single-occupant hour, exactly as the parent
 * theme would after a successful checkout.
 */
$s2 = $wpdb->get_row(
	$wpdb->prepare(
		"SELECT office_id, `date`, start_time, COUNT(DISTINCT specialist_id) n, MIN(specialist_id) occupant
		 FROM {$wpdb->prefix}drplus_booking
		 WHERE office_id = %s AND deleted = 0 AND order_status <> 'cancelled' AND specialist_id > 0
		 GROUP BY office_id, `date`, start_time HAVING n = 1
		 ORDER BY `date`, start_time LIMIT 1",
		$office
	),
	ARRAY_A
);
$s2_extra = h_free_specialist( $s2['date'], $s2['start_time'] );

// Second genuine booking (simulates the checkout that filled the room).
$wpdb->insert(
	$wpdb->prefix . 'drplus_booking',
	array(
		'customer_id'   => 1,
		'specialist_id' => $s2_extra,
		'office_id'     => $office,
		'date'          => $s2['date'],
		'start_time'    => $s2['start_time'],
		'end_time'      => $s2['start_time'],
		'total_price'   => 1000,
		'order_status'  => 'completed',
		'created_at'    => current_time( 'mysql' ),
		'updated_at'    => current_time( 'mysql' ),
		'deleted'       => 0,
	),
	array( '%d', '%d', '%s', '%s', '%s', '%s', '%f', '%s', '%s', '%s', '%d' )
);

$occupancy = (int) $wpdb->get_var(
	$wpdb->prepare(
		"SELECT COUNT(DISTINCT specialist_id) FROM {$wpdb->prefix}drplus_booking
		 WHERE office_id = %s AND `date` = %s AND start_time = %s
		   AND deleted = 0 AND order_status <> 'cancelled' AND specialist_id > 0",
		$office,
		$s2['date'],
		$s2['start_time']
	)
);
$cap_now = DrPlus_Child_Capacity_Service::capacity_for( $office );
printf( "  slot %s %s: %d/%d rooms occupied after adding specialist %d\n",
	$s2['date'], $s2['start_time'], $occupancy, $cap_now, $s2_extra );
h_assert( 'hour is genuinely full (count == capacity)', $occupancy === $cap_now, "$occupancy/$cap_now" );

$s2_new = h_free_specialist( $s2['date'], $s2['start_time'] );
printf( "  third specialist attempting the same hour: %d\n", $s2_new );

h_clean_notices();
WC()->cart->empty_cart();
$s2_key = WC()->cart->add_to_cart(
	h_product_id(), 1, 0, array(),
	array( 'is_booking' => true, 'book_data' => array(
		'office_id'     => $office,
		'date'          => $s2['date'],
		'start_time'    => substr( $s2['start_time'], 0, 5 ),
		'specialist_id' => $s2_new,
	) )
);
do_action( 'woocommerce_checkout_process' );
$notices = wc_get_notices( 'error' );
$texts   = array();
foreach ( (array) $notices as $n ) {
	$texts[] = is_array( $n ) ? (string) ( $n['notice'] ?? '' ) : (string) $n;
}
if ( $s2_key ) {
	WC()->cart->remove_cart_item( $s2_key );
}
h_clean_notices();
h_end_request(); // Scenario 2 is inlined, so it must close its own request boundary.

$capacity_notice = DrPlus_Child_Capacity_Messages::capacity_exceeded(
	DrPlus_Child_Capacity_Service::get_available_hours( $office, $s2['date'], array() )
);

h_assert( 'exactly one error notice', 1 === count( $texts ), count( $texts ) . ' notice(s)' );
h_assert( 'notice is NOT the system-busy notice', isset( $texts[0] ) && false === mb_strpos( $texts[0], 'سیستم مشغول' ), isset( $texts[0] ) ? 'ok' : 'n/a' );
h_assert( 'notice is the capacity_exceeded message', isset( $texts[0] ) && false !== mb_strpos( $texts[0], 'ظرفیت' ), isset( $texts[0] ) ? mb_substr( $texts[0], 0, 120 ) . '…' : 'NOT FOUND' );
// The dump is historical (every slot is in the past relative to today), so the
// parent theme legitimately returns no bookable hours for that day. When it DOES
// return hours, the notice must enumerate them.
$parent_slots = class_exists( 'DrPlus\\Utils\\Booking' ) && method_exists( 'DrPlus\\Utils\\Booking', 'get_available_time_slots' )
	? DrPlus\Utils\Booking::get_available_time_slots( $s2['date'], (int) $s2_extra, $office, 30 )
	: array();
$hours_now  = DrPlus_Child_Capacity_Service::get_available_hours( $office, $s2['date'], is_array( $parent_slots ) ? $parent_slots : array() );
$first_hour = is_array( $hours_now ) && ! empty( $hours_now ) ? (string) ( $hours_now[0]['startTime'] ?? '' ) : '';
if ( '' !== $first_hour ) {
	h_assert( 'message enumerates the day\'s available hours', isset( $texts[0] ) && false !== mb_strpos( $texts[0], $first_hour ), 'looking for ' . $first_hour );
} else {
	h_assert( 'notice degrades gracefully when the day has no other free hour', isset( $texts[0] ) && mb_strlen( $texts[0] ) > 40, 'no bookable hours on ' . $s2['date'] . ' (historical dump)' );
}

// Remove the synthetic booking so the dataset stays faithful to production.
$wpdb->delete(
	$wpdb->prefix . 'drplus_booking',
	array( 'office_id' => $office, 'date' => $s2['date'], 'start_time' => $s2['start_time'], 'specialist_id' => $s2_extra )
);

// ---------------------------------------------------------------- Scenario 3
h_section( 'Scenario 3 — same specialist, two slots in one day: second slot must be REJECTED' );

$s3 = $wpdb->get_row(
	$wpdb->prepare(
		"SELECT specialist_id, `date`, start_time
		 FROM {$wpdb->prefix}drplus_booking
		 WHERE office_id = %s AND deleted = 0 AND order_status <> 'cancelled' AND specialist_id > 0
		 ORDER BY `date`, start_time LIMIT 1",
		$office
	),
	ARRAY_A
);
$s3_spec = (int) $s3['specialist_id'];
$s3_date = $s3['date'];
$s3_time = $s3['start_time'];
printf( "  specialist %d already booked %s %s\n", $s3_spec, $s3_date, $s3_time );

$notices = h_checkout(
	array(
		'office_id'     => $office,
		'date'          => $s3_date,
		'start_time'    => substr( $s3_time, 0, 5 ),
		'specialist_id' => $s3_spec,
	)
);
if ( ! $notices ) {
	$dbg_cart = WC()->cart->get_cart();
	foreach ( $dbg_cart as $k => $it ) {
		printf( "  [debug] cart item book_data: %s\n", wp_json_encode( $it['book_data'] ?? null, JSON_UNESCAPED_UNICODE ) );
		printf( "  [debug] is_booking=%s\n", var_export( $it['is_booking'] ?? null, true ) );
	}
	DrPlus_Child_Capacity_Logger::diag( 'SCENARIO 3 DEBUG START' );
	$dbg = DrPlus_Child_Capacity_Validator::extract_booking_from_request();
	printf( "  [debug] extract_booking_from_request() => %s\n", wp_json_encode( $dbg ) );
	printf( "  [debug] cart items: %d\n", count( (array) WC()->cart->get_cart() ) );
	if ( $dbg ) {
		printf( "  [debug] specialist_already_booked(%d, %s, %s) => %s (reason '%s')\n",
			(int) $dbg['specialist_id'], $dbg['date'], $dbg['start_time'],
			var_export( DrPlus_Child_Capacity_Repository::specialist_already_booked( (int) $dbg['specialist_id'], $dbg['date'], $dbg['start_time'] ), true ),
			DrPlus_Child_Capacity_Repository::last_unverifiable_reason() );
	}
}
$hit = h_saw( $notices, 'قبلاً رزرو دارد' );
h_assert( 'specialist double-booking rejected', false !== $hit, $hit ? mb_substr( $hit, 0, 100 ) . '…' : 'NOT FOUND' );
h_assert( 'exact Persian sentence', false !== h_saw( $notices, 'این متخصص در این ساعت قبلاً رزرو دارد. لطفاً ساعت دیگری را انتخاب کنید.' ) );

// ---------------------------------------------------------------- Scenario 4
h_section( 'Scenario 4 — missing date must be REJECTED with the exact Persian text' );

$notices = h_checkout(
	array(
		'office_id'     => $office,
		'specialist_id' => $s1_free,
		'start_time'    => '10:00',
	)
);
$hit = h_saw( $notices, 'لطفاً ابتدا تاریخ مراجعه را انتخاب کنید.' );
h_assert( 'missing date rejected', false !== $hit, $hit ? 'exact string found' : 'NOT FOUND' );

// ---------------------------------------------------------------- Scenario 5
h_section( 'Scenario 5 — missing specialist must be REJECTED with the exact Persian text' );

$notices = h_checkout(
	array(
		'office_id'  => $office,
		'date'       => $s2['date'],
		'start_time' => '11:00',
		// deliberately no specialist_id
	)
);
$hit = h_saw( $notices, 'متخصص این نوبت مشخص نشد' );
h_assert( 'missing specialist rejected (Bug A: fail-closed)', false !== $hit, $hit ? mb_substr( $hit, 0, 110 ) . '…' : 'NOT FOUND: ' . implode( ' | ', $notices ) );

// ---------------------------------------------------------------- Scenario 6
h_section( 'Scenario 6 — online visit (phone_consultation) must be EXEMPT' );

$notices = h_checkout(
	array(
		'office_id'     => $online_type,
		'date'          => '2026-04-18',
		'start_time'    => '09:00',
		'specialist_id' => $s1_free,
	)
);
h_assert( 'online visit not blocked by capacity', 0 === count( $notices ), implode( ' | ', $notices ) );

h_section( 'CANCELLED bookings must not occupy a room' );
$cancelled = $wpdb->get_row(
	"SELECT office_id, `date`, start_time, COUNT(DISTINCT specialist_id) n
	 FROM {$wpdb->prefix}drplus_booking
	 WHERE deleted = 0 AND order_status = 'cancelled' AND specialist_id > 0
	 GROUP BY office_id, `date`, start_time
	 HAVING n >= 2 ORDER BY `date`, start_time LIMIT 1",
	ARRAY_A
);
if ( ! $cancelled ) {
	// Fall back to the real cancelled hour in the data: 614 / 2026-04-18 / 09:00.
	$cancelled = $wpdb->get_row(
		"SELECT office_id, `date`, start_time, COUNT(DISTINCT specialist_id) n
		 FROM {$wpdb->prefix}drplus_booking
		 WHERE deleted = 0 AND order_status = 'cancelled' AND specialist_id > 0
		 GROUP BY office_id, `date`, start_time
		 ORDER BY n DESC, `date` LIMIT 1",
		ARRAY_A
	);
}
if ( $cancelled ) {
	$free = h_free_specialist( $cancelled['date'], $cancelled['start_time'] );
	printf( "  cancelled-only hour %s %s on %s (%d cancelled row(s)) — booking as specialist %d\n",
		$cancelled['date'], $cancelled['start_time'], $cancelled['office_id'], $cancelled['n'], $free );
	$notices = h_checkout(
		array(
			'office_id'     => $cancelled['office_id'],
			'date'          => $cancelled['date'],
			'start_time'    => substr( $cancelled['start_time'], 0, 5 ),
			'specialist_id' => $free,
		)
	);
	h_assert( 'cancelled bookings do not consume capacity', 0 === count( $notices ), implode( ' | ', $notices ) );
} else {
	printf( "  no cancelled slot found — skipped\n" );
}

h_summary();
