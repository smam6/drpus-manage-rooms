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
// 3b. THE PAID-BUT-STALE-ROW TRAP. This is the regression that shipped in 1.0.3 and is
//     the single worst defect found so far: it released a PAID appointment at the TTL,
//     handing the same specialist and the same hour back to the pool, so a second patient
//     could pay for it. Two paid patients, one room.
//
//     The row's copied `order_status` is written once, before the gateway settles, and
//     nothing in the parent updates it on payment. So `row.order_status = 'pending'` with
//     `order.status = 'processing'` is the NORMAL happy-path state, not a corruption.
//     Verified on the operator's own data (book #61 / order #2947).
//
//     The fix asks the order before calling anything abandoned. On a site where the
//     status-sync hook is unavailable the row can still say pending, so this test forces
//     exactly that state rather than relying on the hook.
// ---------------------------------------------------------------------------------------
clear_day( $date );
$paid_order_id = mk_row( 13, $date, '10:00:00', 'processing', 180 );
global $wpdb, $table;
$wpdb->update( $table, array( 'order_status' => 'pending' ), array( 'order_id' => $paid_order_id ) );
$paid_status = wc_get_order( $paid_order_id )->get_status();
chk( 'a PAID order is processing even though the row says pending', $paid_status, 'processing' );
chk( 'PAID + 3 h old + stale row still HOLDS (no double-booking)', DrPlus_Child_Capacity_Repository::specialist_already_booked( 13, $date, '10:00:00' ), true );

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
// 4a. A ROW WITH NO ORDER IS NOT AN ABANDONED CHECKOUT.
//
//     The TTL exists for one situation only: a customer reached checkout, the parent
//     INSERTed the row, and the payment never arrived. Those rows always have an order,
//     created in the same request.
//
//     A row with order_id = 0 was never part of a checkout — it is an appointment the
//     operator typed in by hand, or an import from an older system. Applying the TTL to it
//     silently erases a real appointment from the capacity picture after two hours. The
//     operator's own database contains one such row, so this is not hypothetical.
// ---------------------------------------------------------------------------------------
clear_day( $date );
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
		'order_id'     => 0,
		'order_status' => 'pending',
		'deleted'      => 0,
		'created_at'   => gmdate( 'Y-m-d H:i:s', current_time( 'timestamp', true ) - ( 6 * HOUR_IN_SECONDS ) ),
	)
);
chk(
	'a hand-entered appointment (no order) is NOT aged out',
	DrPlus_Child_Capacity_Repository::specialist_already_booked( 13, $date, '10:00:00' ),
	true
);

// ---------------------------------------------------------------------------------------
// 4b. THE COPIED STATUS COLUMN MUST FOLLOW THE ORDER.
//
//     `drplus_booking.order_status` is written once by the parent, before the gateway
//     settles, and the parent never updates it on payment. Everything downstream that
//     filters on that column — the parent's appointments list, its dashboard counters,
//     its per-specialist counts, and its own five-minute cron — therefore reads a status
//     that stopped being true at checkout.
//
//     The worst consequence is the cron: it selects past appointments by the stale column,
//     so an order the operator cancelled (but whose row still said `processing`) was
//     flipped to `completed`, which re-took the hour and corrupted the operator's reports.
//     This test pins the sync hook that prevents it.
// ---------------------------------------------------------------------------------------
clear_day( $date );
$sync_order_id = mk_row( 13, $date, '10:00:00', 'processing', 5 );
$sync_order    = wc_get_order( $sync_order_id );
$sync_order->set_status( 'cancelled' );
$sync_order->save();

$synced_status = strtolower( (string) $wpdb->get_var(
	$wpdb->prepare( "SELECT order_status FROM {$table} WHERE order_id = %d AND deleted = 0", $sync_order_id )
) );
chk( 'cancelling an order rewrites the copied row status', $synced_status, 'cancelled' );
chk( 'and the hour is released with it',
	DrPlus_Child_Capacity_Repository::specialist_already_booked( 13, $date, '10:00:00' ), false );

// Paying must sync forwards too, not only cancellations.
clear_day( $date );
$pay_order_id = mk_row( 13, $date, '10:00:00', 'pending', 5 );
$pay_order    = wc_get_order( $pay_order_id );
$pay_order->set_status( 'processing' );
$pay_order->save();
$paid_row_status = strtolower( (string) $wpdb->get_var(
	$wpdb->prepare( "SELECT order_status FROM {$table} WHERE order_id = %d AND deleted = 0", $pay_order_id )
) );
chk( 'paying an order rewrites the copied row status', $paid_row_status, 'processing' );

// ---------------------------------------------------------------------------------------
// 4c. ONE CLINIC, ONE LOCK.
//
//     Counting collapses a clinic's numeric id and its custom-office UUIDs onto one
//     canonical id, but the lock hashed whatever string it was handed. Two customers
//     booking the same hour through different spellings took two different MySQL locks,
//     so both passed the capacity count concurrently and the clinic accepted capacity+1.
//     Layer 2 caught it and cancelled a PAID order with no refund path.
// ---------------------------------------------------------------------------------------
$alias_uuid = '1b9d72ba-f2b2-4dfd-8a3c-5b0f13c5c650';
chk(
	'every spelling of one clinic takes the SAME lock',
	DrPlus_Child_Capacity_Lock_Manager::build_lock_name( '614', $date, '10:00:00' ),
	DrPlus_Child_Capacity_Lock_Manager::build_lock_name( $alias_uuid, $date, '10:00:00' )
);
chk(
	'a different clinic still takes a DIFFERENT lock',
	DrPlus_Child_Capacity_Lock_Manager::build_lock_name( '614', $date, '10:00:00' )
	=== DrPlus_Child_Capacity_Lock_Manager::build_lock_name( '999', $date, '10:00:00' ),
	false
);

// ---------------------------------------------------------------------------------------
// 4d. A RELEASED ROW MUST STOP LOOKING LIKE AN APPOINTMENT.
//
//     Releasing the hour in this module's predicates is only half the job. The parent
//     theme's appointments list, dashboard counters and per-specialist counts all filter
//     the copied `order_status` column directly. An abandoned checkout that this module
//     correctly stopped counting still appeared there as a live appointment — and every
//     new attempt added another one.
//
//     Measured on a rebuilt database before the fix: five abandoned attempts at one hour
//     produced FIVE phantom appointments in the operator's list; a sixth that really was
//     paid made six, with no way to tell which one was real.
// ---------------------------------------------------------------------------------------
clear_day( $date );
$phantom_orders = array();
foreach ( range( 1, 5 ) as $attempt ) {
	$phantom_orders[] = mk_row( 13, $date, '10:00:00', 'pending', 180 + ( $attempt * 30 ) );
}

$list_query = $wpdb->prepare(
	"SELECT COUNT(*) FROM {$table} WHERE `date` = %s AND LOWER( TRIM( order_status ) ) NOT IN ('cancelled','refunded','failed','on-hold')",
	$date
);
chk( 'before any read, 5 abandoned rows masquerade as appointments', (int) $wpdb->get_var( $list_query ), 5 );

// One grid render is all it takes to retire them.
DrPlus_Child_Capacity_Repository::count_day_slots( '614', $date );

chk( 'after a grid render the phantoms are gone', (int) $wpdb->get_var( $list_query ), 0 );

$phantom_cancelled = 0;
foreach ( $phantom_orders as $phantom_order_id ) {
	$phantom_order = wc_get_order( $phantom_order_id );
	if ( $phantom_order && 'cancelled' === $phantom_order->get_status() ) {
		$phantom_cancelled++;
	}
}
chk( 'and their orders are cancelled too, so the order list is clean', $phantom_cancelled, 5 );

// A real booking on the same hour must survive the same read untouched.
$real_grid_order = mk_row( 13, $date, '10:00:00', 'processing', 5 );
DrPlus_Child_Capacity_Repository::count_day_slots( '614', $date );
chk( 'a real appointment is NOT retired by the same read', (int) $wpdb->get_var( $list_query ), 1 );
chk( '  and its order is untouched', wc_get_order( $real_grid_order )->get_status(), 'processing' );

// ---------------------------------------------------------------------------------------
// 5. The grid and checkout must agree. They share row_holds_slot(), but they run
//    different SQL, and a missing column in one of them is exactly how they diverged.
// ---------------------------------------------------------------------------------------
clear_day( $date );
mk_row( 13, $date, '10:00:00', 'pending', 180 );
$grid = DrPlus_Child_Capacity_Repository::specialist_booked_slots_for_day( 13, $date, '614' );
chk( 'grid does NOT hide a released hour', isset( $grid['10:00:00'] ), false );

// ---------------------------------------------------------------------------------------
// 5b. THE DAY COUNTER THAT FEEDS THE GRID MUST USE THE SAME PREDICATE AS THE CHECKOUT GATE.
//
//     count_day_slots() used to be a bare `COUNT(DISTINCT specialist_id)` in SQL. That form
//     cannot call row_holds_slot() — it never selects `created_at` or `order_id`, which are
//     exactly what the predicate needs — so every case the predicate releases still counted
//     as taken on the grid. The observer sees a day with hours missing from the picker that
//     the checkout would in fact accept: the operator's "every hour is refused" report.
//
//     One case per divergence that was proven on a rebuilt database.
// ---------------------------------------------------------------------------------------
$grid_parity_cases = array(
	'abandoned pending (4 h)'   => array( 'status' => 'pending', 'age' => 240 ),
	'real processing'           => array( 'status' => 'processing', 'age' => 240 ),
	'cancelled'                 => array( 'status' => 'cancelled', 'age' => 240 ),
);

foreach ( $grid_parity_cases as $label => $case ) {
	clear_day( $date );
	mk_row( 13, $date, '10:00:00', $case['status'], $case['age'] );

	$day_counts = DrPlus_Child_Capacity_Repository::count_day_slots( '614', $date );
	$from_grid  = isset( $day_counts['10:00:00'] ) ? (int) $day_counts['10:00:00'] : 0;
	$from_gate  = (int) DrPlus_Child_Capacity_Repository::count_distinct_specialists( '614', $date, '10:00:00' );

	chk( "grid and checkout agree: {$label}", $from_grid, $from_gate );
}

// A row whose order_id points at nothing must not occupy the grid either.
clear_day( $date );
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
		'order_id'     => 999999,
		'order_status' => 'processing',
		'deleted'      => 0,
		'created_at'   => current_time( 'mysql' ),
	)
);
$orphan_grid = DrPlus_Child_Capacity_Repository::count_day_slots( '614', $date );
$orphan_gate = (int) DrPlus_Child_Capacity_Repository::count_distinct_specialists( '614', $date, '10:00:00' );
chk( 'grid and checkout agree: orphan order_id', isset( $orphan_grid['10:00:00'] ) ? (int) $orphan_grid['10:00:00'] : 0, $orphan_gate );

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
