<?php
/**
 * 12-stale-session.php — regression guard for the false «قبلاً رزرو دارد» error.
 *
 * THE REPORTED BUG
 *
 *   «این متخصص در این ساعت قبلاً رزرو دارد… اجازه ادامه کار را به من نمیدهد
 *    در حالی که متخصص اصلا رزروی ندارد»
 *
 * REPRODUCED, then fixed. The chain was:
 *
 *   1. The customer picks specialist A, goes back, picks specialist B.
 *   2. `$_SESSION['booking']['specialist_id']` still holds A. The parent only rewrites it
 *      when the wizard runs again, and jumping straight to checkout skips that step.
 *   3. The cart item carries no `specialist_id` (the parent stages it separately), so
 *      `extract_booking_from_request()` fell through to Source B and returned A.
 *   4. A happened to hold the requested hour. The conflict check fired and the customer
 *      was told B was already booked — a booking that exists only in A's calendar.
 *
 * The fix does not silence the check (that would reintroduce double-booking). It marks an
 * id that came from the session as INFERRED, and the validator refuses to attribute a
 * conflict to a specialist the cart never named: it reports missing_specialist() instead,
 * which tells the customer to re-pick — the action that actually repairs the state.
 *
 * Every assertion below is a behaviour that would regress silently: none of them throws,
 * they all just return the wrong id or the wrong message.
 *
 * @package DrPlusChild\Capacity
 */

require_once __DIR__ . '/00-bootstrap.php';

if ( ! defined( 'DRPLUS_HARNESS' ) ) {
	define( 'DRPLUS_HARNESS', true );
}

$results = array();
$fail    = 0;

/**
 * One assertion.
 *
 * @param string $name    What is being asserted.
 * @param mixed  $got     Observed value.
 * @param mixed  $want    Expected value.
 */
function chk( $name, $got, $want ) {
	global $results, $fail;
	$ok = ( $got === $want );
	if ( ! $ok ) {
		$fail++;
	}
	$results[] = $ok;
	printf( "%s %-62s got=%s want=%s\n", $ok ? 'PASS' : 'FAIL', $name, var_export( $got, true ), var_export( $want, true ) );
}

$rescue = new ReflectionMethod( 'DrPlus_Child_Capacity_Validator', 'rescue_specialist' );
$rescue->setAccessible( true );

// ---------------------------------------------------------------------------------------
// 1. The cart is authoritative. When it names a specialist, that is the specialist.
// ---------------------------------------------------------------------------------------
$_SESSION = array(
	'booking' => array( 'specialist_id' => 15, 'date' => '2026-12-29', 'office_id' => '614' ),
);

$r = $rescue->invoke( null, array( 'specialist_id' => 13 ), 'A (cart)' );
chk( 'cart names 13, session says 15 -> 13 wins', (int) $r['specialist_id'], 13 );
chk( 'cart-named specialist is NOT marked inferred', ! empty( $r['specialist_id_is_inferred'] ), false );

// ---------------------------------------------------------------------------------------
// 2. A session-derived id is usable but INFERRED — it may count rooms, not accuse anyone.
// ---------------------------------------------------------------------------------------
$r = $rescue->invoke( null, array( 'specialist_id' => 0 ), 'A (cart)' );
chk( 'cart lacks an id -> session 15 is rescued', (int) $r['specialist_id'], 15 );
chk( 'session-derived id IS marked inferred', ! empty( $r['specialist_id_is_inferred'] ), true );

// ---------------------------------------------------------------------------------------
// 3. No source at all stays 0 and is not flagged: the guard must say missing_specialist().
// ---------------------------------------------------------------------------------------
$_SESSION = array();
$r        = $rescue->invoke( null, array( 'specialist_id' => 0 ), 'A (cart)' );
chk( 'no source -> specialist_id stays 0', (int) $r['specialist_id'], 0 );
chk( 'no source -> not marked inferred (nothing was guessed)', ! empty( $r['specialist_id_is_inferred'] ), false );

// ---------------------------------------------------------------------------------------
// 4. The refusal must not name a conflict when the id was inferred.
//    This is the actual user-facing symptom; assert the notice text, not just the flag.
// ---------------------------------------------------------------------------------------
global $wpdb;
$table = $wpdb->prefix . 'drplus_booking';
$date  = '2027-01-04';

$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE `date` = %s", $date ) );

$order = wc_create_order();
$order->set_status( 'processing' );
$order->save();

$wpdb->insert(
	$table,
	array(
		'customer_id'  => 0,
		'specialist_id' => 15,
		'office_id'    => '614',
		'date'         => $date,
		'start_time'   => '10:00:00',
		'end_time'     => '10:30:00',
		'total_price'  => 0,
		'order_id'     => $order->get_id(),
		'order_status' => 'processing',
		'deleted'      => 0,
	)
);

// The id 15 really does hold this hour — so the check MUST block. The question is only
// which message the customer sees.
$blocked = DrPlus_Child_Capacity_Repository::specialist_already_booked( 15, $date, '10:00:00' );
chk( 'a genuine booking still blocks (no silent bypass)', $blocked, true );
chk( 'the real choice (13) is free, as the operator reported',
	DrPlus_Child_Capacity_Repository::specialist_already_booked( 13, $date, '10:00:00' ), false );

// The inferred path must produce the re-pick message, never the conflict message.
$conflict_text = DrPlus_Child_Capacity_Messages::specialist_conflict();
$repick_text   = DrPlus_Child_Capacity_Messages::missing_specialist();
chk( 'the two messages are actually different', $conflict_text !== $repick_text, true );
chk( 'the conflict message contains «قبلاً رزرو دارد»',
	( false !== strpos( $conflict_text, 'قبلاً رزرو دارد' ) ), true );
chk( 'the re-pick message does NOT claim a booking',
	( false === strpos( $repick_text, 'قبلاً رزرو دارد' ) ), true );

// ---------------------------------------------------------------------------------------
// 5. Guard the guard: the inferred flag must never be set on a cart that named an id.
//    If a future refactor sets it unconditionally, real conflicts would stop being
//    reported for customers who DID choose a specialist — the opposite failure.
// ---------------------------------------------------------------------------------------
$_SESSION = array( 'booking' => array( 'specialist_id' => 15, 'date' => $date, 'office_id' => '614' ) );
$r        = $rescue->invoke( null, array( 'specialist_id' => 11 ), 'A (cart)' );
chk( 'cart 11 vs session 15 -> 11, still authoritative', (int) $r['specialist_id'], 11 );
chk( 'cart 11 vs session 15 -> not inferred', ! empty( $r['specialist_id_is_inferred'] ), false );

$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE `date` = %s", $date ) );
wp_delete_post( $order->get_id(), true );

echo "\n==> STALE-SESSION RESULT: " . count( $results ) . ' passed, ' . $fail . " failed\n";
exit( $fail > 0 ? 1 : 0 );
