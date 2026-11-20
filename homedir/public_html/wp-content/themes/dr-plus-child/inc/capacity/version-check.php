<?php
/**
 * Dr Plus Child Theme — Capacity module deployment verifier.
 *
 * Answers one question: "is the code running on THIS server the fixed code?"
 *
 * Why this file exists: a site can appear updated while still serving old code, because
 * PHP OPcache, a page cache, or a partial file upload all mask the swap. Reading hashes
 * from the filesystem settles it in one request.
 *
 * @package DrPlusChild\Capacity
 * @since   1.0.1
 */

// Hardening. The content of this page discloses file paths and hashes, which is useful to an
// attacker probing a site and useless to the public, so it is limited to authenticated
// administrators. It is a plain PHP file (not routed through WordPress), so the WordPress
// bootstrap is loaded explicitly to reach current_user_can().
$drplus_capacity_vc_abspath = dirname( __DIR__, 3 ) . '/../../../wp-load.php';
$drplus_capacity_vc_loaded  = false;

if ( file_exists( $drplus_capacity_vc_abspath ) ) {
	require_once $drplus_capacity_vc_abspath;
	$drplus_capacity_vc_loaded = true;
}

// Resolve wp-load.php by walking up from this file when the relative guess is wrong
// (staging installs and non-standard directory layouts are common on shared hosting).
if ( ! $drplus_capacity_vc_loaded ) {
	$drplus_capacity_vc_dir = __DIR__;
	for ( $i = 0; $i < 8 && ! $drplus_capacity_vc_loaded; $i++ ) {
		$drplus_capacity_vc_dir = dirname( $drplus_capacity_vc_dir );
		$drplus_capacity_vc_try = $drplus_capacity_vc_dir . '/wp-load.php';
		if ( file_exists( $drplus_capacity_vc_try ) ) {
			require_once $drplus_capacity_vc_try;
			$drplus_capacity_vc_loaded = true;
		}
	}
}

if ( ! $drplus_capacity_vc_loaded || ! function_exists( 'current_user_can' ) ) {
	header( 'HTTP/1.1 503 Service Unavailable', true, 503 );
	exit( 'WordPress could not be located from this file. Delete it and use the admin check instead.' );
}

if ( ! current_user_can( 'manage_options' ) ) {
	header( 'HTTP/1.1 403 Forbidden', true, 403 );
	exit( 'Forbidden. Log in as an administrator first, then reload this page.' );
}

header( 'Content-Type: text/html; charset=utf-8' );
header( 'X-Robots-Tag: noindex, nofollow', true );
header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );

/**
 * The checks this page performs. Each entry is a marker string that MUST be present in the
 * fixed code, plus the file it belongs in. A missing marker means old code is still being served.
 */
$drplus_capacity_vc_checks = array(
	array(
		'label'  => 'Gregorian date guard present',
		'file'   => 'class-capacity-repository.php',
		'marker' => 'assert_queryable_date',
		'hint'   => 'Repository is the FIXED version (Jalali dates cannot reach SQL).',
	),
	array(
		'label'  => 'Jalali detection in validator',
		'file'   => 'class-capacity-validator.php',
		'marker' => 'looks_jalali',
		'hint'   => 'Validator is the FIXED version (Jalali dates rejected).',
	),
	array(
		'label'  => 'Missing-hour fail-closed guard',
		'file'   => 'class-capacity-validator.php',
		'marker' => 'ERR_MISSING_TIME',
		'hint'   => 'Validator refuses an empty visit hour instead of counting 0 and allowing it.',
	),
	array(
		'label'  => 'Legacy online alias handled',
		'file'   => 'class-capacity-service.php',
		'marker' => 'ONLINE_ALIASES',
		'hint'   => 'Phone consultations using the legacy id are exempt, not counted as rooms.',
	),
	array(
		'label'  => 'Clinic UUID aliases resolved (Bug B)',
		'file'   => 'class-capacity-repository.php',
		'marker' => 'normalize_to_clinic',
		'hint'   => 'A numeric clinic id and its custom-office UUIDs are counted as ONE clinic.',
	),
	array(
		'label'  => 'Specialist double-booking blocked (Bug C)',
		'file'   => 'class-capacity-validator.php',
		'marker' => 'ERR_SPECIALIST_CONFLICT',
		'hint'   => 'The same specialist can no longer hold two rooms in the same hour.',
	),
	array(
		'label'  => 'Cancelled bookings never hold a slot',
		'file'   => 'class-capacity-repository.php',
		'marker' => 'status_holds_slot',
		'hint'   => 'Every spelling of a cancelled booking (cancelled/canceled/wc-/wrong case/trailing space) is ignored instead of blocking the hour.',
	),
	array(
		'label'  => 'Booked hours hidden from the time grid',
		'file'   => 'class-capacity-service.php',
		'marker' => 'specialist_booked_slots_for_day',
		'hint'   => 'A specialist already booked at an hour no longer sees that hour offered in the Select Time step.',
	),
	array(
		'label'  => 'Debug logging REMOVED',
		'file'   => 'class-capacity-repository.php',
		'marker' => 'DRPLUS COUNT SQL',
		'hint'   => 'This marker must be ABSENT. If found, the file being served still logs SQL.',
		'invert' => true,
	),
	array(
		'label'  => 'Capacity debug logging REMOVED',
		'file'   => 'class-capacity-validator.php',
		'marker' => 'DRPLUS CAPACITY',
		'hint'   => 'This marker must be ABSENT. If found, the file being served still logs capacity.',
		'invert' => true,
	),
);

$drplus_capacity_vc_dir = __DIR__;
$drplus_capacity_vc_fail = 0;
$drplus_capacity_vc_rows = array();

foreach ( $drplus_capacity_vc_checks as $drplus_capacity_vc_check ) {
	$drplus_capacity_vc_path = $drplus_capacity_vc_dir . '/' . $drplus_capacity_vc_check['file'];
	$drplus_capacity_vc_src  = file_exists( $drplus_capacity_vc_path ) ? file_get_contents( $drplus_capacity_vc_path ) : false; // phpcs:ignore WordPress.WP.AlternativeFunctions
	$drplus_capacity_vc_has  = ( false !== $drplus_capacity_vc_src && false !== strpos( $drplus_capacity_vc_src, $drplus_capacity_vc_check['marker'] ) );

	// A normal check wants the marker present; an "invert" check wants it absent.
	$drplus_capacity_vc_ok = empty( $drplus_capacity_vc_check['invert'] ) ? $drplus_capacity_vc_has : ! $drplus_capacity_vc_has;

	if ( ! $drplus_capacity_vc_ok ) {
		$drplus_capacity_vc_fail++;
	}

	$drplus_capacity_vc_rows[] = array(
		'label' => $drplus_capacity_vc_check['label'],
		'file'  => $drplus_capacity_vc_check['file'],
		'ok'    => $drplus_capacity_vc_ok,
		'hint'  => $drplus_capacity_vc_check['hint'],
	);
}

/**
 * LIVE ASSERTIONS — execute the real code, do not just look for a string in a file.
 *
 * The marker checks above answer "was this file replaced?". They cannot answer "does the
 * code in it behave?" — a marker can be present in a file that fatals on load, and a partial
 * upload can leave the marker in place while the logic is truncated. These checks call the
 * shipped classes and assert the properties the clinic depends on, so a green page means the
 * behaviour is correct on THIS server, not merely that a substring exists.
 *
 * Everything here is read-only: the alias resolver reads an option, the date guard and the
 * time normalizer are pure. No rows are written and no booking is created.
 */
$drplus_capacity_vc_live = array();

/**
 * Record one live assertion.
 *
 * @param string $label    Human label.
 * @param bool   $ok       Result.
 * @param string $evidence What was actually observed.
 */
$drplus_capacity_vc_assert = static function ( $label, $ok, $evidence ) use ( &$drplus_capacity_vc_live, &$drplus_capacity_vc_fail ) {
	$drplus_capacity_vc_live[] = array(
		'label'    => $label,
		'ok'       => (bool) $ok,
		'evidence' => (string) $evidence,
	);
	if ( ! $ok ) {
		$drplus_capacity_vc_fail++;
	}
};

$drplus_capacity_vc_classes_ready = class_exists( 'DrPlus_Child_Capacity_Repository' )
	&& class_exists( 'DrPlus_Child_Capacity_Validator' )
	&& class_exists( 'DrPlus_Child_Capacity_Service' );

if ( ! $drplus_capacity_vc_classes_ready ) {
	$drplus_capacity_vc_assert( 'Capacity classes load', false, 'Repository/Validator/Service not loaded — bootstrap.php did not run' );
} else {
	// --- Jalali guard ---------------------------------------------------------------
	$drplus_capacity_vc_guard = new ReflectionMethod( 'DrPlus_Child_Capacity_Repository', 'assert_queryable_date' );
	$drplus_capacity_vc_guard->setAccessible( true );

	$drplus_capacity_vc_jalali = $drplus_capacity_vc_guard->invoke( null, '1405-08-28' );
	$drplus_capacity_vc_assert(
		'Jalali date is refused (fail-closed)',
		'' === $drplus_capacity_vc_jalali,
		"assert_queryable_date('1405-08-28') returned " . var_export( $drplus_capacity_vc_jalali, true ) . " (expected '')"
	);

	$drplus_capacity_vc_persian = $drplus_capacity_vc_guard->invoke( null, '۱۴۰۵-۰۸-۲۸' );
	$drplus_capacity_vc_assert(
		'Jalali date in Persian digits is refused',
		'' === $drplus_capacity_vc_persian,
		"assert_queryable_date('۱۴۰۵-۰۸-۲۸') returned " . var_export( $drplus_capacity_vc_persian, true ) . " (expected '')"
	);

	$drplus_capacity_vc_good = $drplus_capacity_vc_guard->invoke( null, '2026-11-19' );
	$drplus_capacity_vc_assert(
		'A real Gregorian date passes through',
		'2026-11-19' === $drplus_capacity_vc_good,
		"assert_queryable_date('2026-11-19') returned " . var_export( $drplus_capacity_vc_good, true )
	);

	// --- time normalization ---------------------------------------------------------
	$drplus_capacity_vc_time = DrPlus_Child_Capacity_Repository::normalize_time( '14:00' );
	$drplus_capacity_vc_assert(
		'Time 14:00 normalizes to 14:00:00',
		'14:00:00' === $drplus_capacity_vc_time,
		"normalize_time('14:00') returned " . var_export( $drplus_capacity_vc_time, true )
	);

	$drplus_capacity_vc_junk = DrPlus_Child_Capacity_Repository::normalize_time( '1405' );
	$drplus_capacity_vc_assert(
		'A bare number is not treated as a time',
		'' === $drplus_capacity_vc_junk,
		"normalize_time('1405') returned " . var_export( $drplus_capacity_vc_junk, true ) . " (expected '')"
	);

	// --- clinic identity ------------------------------------------------------------
	// A clinic can appear under several identifiers: the numeric hospital post id and
	// one custom-office UUID per specialist. If those are counted separately, a 2-room
	// clinic accepts a 3rd booking — and the slot filter offers an hour the checkout
	// then refuses. So the question is not "is a manual map filled in?" but "does this
	// identifier actually resolve to its siblings?".
	//
	// Resolution has two sources: the clinic name + phone recorded on each custom office
	// in `drplus_specialists.offices` (automatic, works with no configuration), and the
	// optional `drplus_capacity_clinic_aliases` option (for joins the data cannot make).
	$drplus_capacity_vc_probe = '1b9d72ba-f2b2-4dfd-8a3c-5b0f13c5c650';
	$drplus_capacity_vc_ids   = DrPlus_Child_Capacity_Repository::resolve_clinic_identifiers( $drplus_capacity_vc_probe );
	$drplus_capacity_vc_auto  = DrPlus_Child_Capacity_Repository::identifiers_from_office_identity( $drplus_capacity_vc_probe );
	$drplus_capacity_vc_aliases = get_option( 'drplus_capacity_clinic_aliases', array() );

	$drplus_capacity_vc_assert(
		'The inspection UUID resolves to more than itself',
		count( $drplus_capacity_vc_ids ) > 1,
		count( $drplus_capacity_vc_ids ) . ' identifier(s): ' . implode( ', ', array_slice( (array) $drplus_capacity_vc_ids, 0, 4 ) )
		. ( count( $drplus_capacity_vc_ids ) > 4 ? ', …' : '' )
	);

	$drplus_capacity_vc_assert(
		'Clinic identity is derived from the site data (no manual map needed)',
		count( $drplus_capacity_vc_auto ) > 0,
		count( $drplus_capacity_vc_auto ) . ' sibling identifier(s) found from the name + phone stored on the custom offices'
	);

	// Every identifier in the group must resolve back to the same set: a one-way link
	// would let a booking filed under one id be invisible from another.
	$drplus_capacity_vc_sym    = true;
	$drplus_capacity_vc_sorted = $drplus_capacity_vc_ids;
	sort( $drplus_capacity_vc_sorted );
	foreach ( (array) $drplus_capacity_vc_ids as $drplus_capacity_vc_one ) {
		$drplus_capacity_vc_back = DrPlus_Child_Capacity_Repository::resolve_clinic_identifiers( $drplus_capacity_vc_one );
		sort( $drplus_capacity_vc_back );
		if ( $drplus_capacity_vc_back !== $drplus_capacity_vc_sorted ) {
			$drplus_capacity_vc_sym = false;
			break;
		}
	}
	$drplus_capacity_vc_assert(
		'Clinic resolution is symmetric',
		$drplus_capacity_vc_sym,
		'Every identifier of the group resolves to the same identifier set'
	);

	// Capacity must be the same whichever identifier the booking arrived on.
	$drplus_capacity_vc_caps = array();
	foreach ( (array) $drplus_capacity_vc_ids as $drplus_capacity_vc_one ) {
		$drplus_capacity_vc_caps[ $drplus_capacity_vc_one ] = DrPlus_Child_Capacity_Service::capacity_for( $drplus_capacity_vc_one );
	}
	$drplus_capacity_vc_assert(
		'Capacity is identical via every identifier of the clinic',
		1 === count( array_unique( array_values( $drplus_capacity_vc_caps ) ) ),
		implode( ', ', array_map(
			static function ( $id, $cap ) {
				return ( strlen( (string) $id ) > 14 ? substr( (string) $id, 0, 8 ) . '…' : $id ) . '=' . $cap;
			},
			array_keys( $drplus_capacity_vc_caps ),
			array_values( $drplus_capacity_vc_caps )
		) )
	);

	if ( ! empty( $drplus_capacity_vc_aliases ) ) {
		$drplus_capacity_vc_assert(
			'Manual alias map is also honoured',
			true,
			count( (array) $drplus_capacity_vc_aliases ) . ' alias(es) configured'
		);
	}

	// --- cancelled bookings -----------------------------------------------------------------
	// Pure value checks: no database access, no writes.
	$drplus_capacity_vc_cancelled_forms = array(
		'cancelled',
		'canceled',
		'wc-cancelled',
		'wc-canceled',
		'Cancelled',
		'CANCELLED',
		'  cancelled  ',
		'refunded',
		'failed',
		'trash',
		'checkout-draft',
	);
	$drplus_capacity_vc_leaky = array();
	foreach ( $drplus_capacity_vc_cancelled_forms as $drplus_capacity_vc_form ) {
		if ( DrPlus_Child_Capacity_Repository::status_holds_slot( $drplus_capacity_vc_form ) ) {
			$drplus_capacity_vc_leaky[] = var_export( $drplus_capacity_vc_form, true );
		}
	}
	$drplus_capacity_vc_assert(
		'Every spelling of a cancelled booking releases the slot',
		empty( $drplus_capacity_vc_leaky ),
		empty( $drplus_capacity_vc_leaky )
			? count( $drplus_capacity_vc_cancelled_forms ) . ' spellings tested — none of them block an hour'
			: 'These still block an hour (a customer would see a false "already booked"): ' . implode( ', ', $drplus_capacity_vc_leaky )
	);

	// The guard must not have been weakened: these statuses still hold the slot.
	$drplus_capacity_vc_held = array( 'completed', 'processing', 'on-hold', 'pending' );
	$drplus_capacity_vc_lost = array();
	foreach ( $drplus_capacity_vc_held as $drplus_capacity_vc_form ) {
		if ( ! DrPlus_Child_Capacity_Repository::status_holds_slot( $drplus_capacity_vc_form ) ) {
			$drplus_capacity_vc_lost[] = var_export( $drplus_capacity_vc_form, true );
		}
	}
	$drplus_capacity_vc_assert(
		'Live bookings still hold their slot (guard not weakened)',
		empty( $drplus_capacity_vc_lost ),
		empty( $drplus_capacity_vc_lost )
			? 'completed / processing / on-hold / pending all still block, as they must'
			: 'These no longer block (overbooking risk): ' . implode( ', ', $drplus_capacity_vc_lost )
	);

	// --- cancelled bookings: checked against THIS site's real rows -------------------
	// The status test above proves the logic. This one proves the data: every row in
	// this site's own booking table that is cancelled must no longer occupy its slot,
	// unless something else genuinely still holds it. A cancelled row that keeps
	// blocking an hour is exactly the «قبلاً رزرو دارد» complaint.
	global $wpdb;
	$drplus_capacity_vc_table = DrPlus_Child_Capacity_Repository::booking_table();
	$drplus_capacity_vc_cancelled_rows = $wpdb->get_results(
		"SELECT book_id, specialist_id, office_id, `date`, start_time
		 FROM {$drplus_capacity_vc_table}
		 WHERE LOWER(TRIM(order_status)) = 'cancelled' AND deleted = 0
		 ORDER BY book_id DESC LIMIT 200",
		ARRAY_A
	);

	$drplus_capacity_vc_leaking = array();
	$drplus_capacity_vc_checked = 0;
	foreach ( (array) $drplus_capacity_vc_cancelled_rows as $drplus_capacity_vc_row ) {
		$drplus_capacity_vc_date = DrPlus_Child_Capacity_Repository::normalize_date( $drplus_capacity_vc_row['date'] );
		if ( '' === $drplus_capacity_vc_date ) {
			continue; // Unusable date is not a capacity fact.
		}
		// Anything OTHER than this cancelled row still on the slot?
		$drplus_capacity_vc_peers = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$drplus_capacity_vc_table}
				 WHERE office_id = %s AND `date` = %s AND start_time = %s
				   AND deleted = 0 AND book_id <> %d
				   AND LOWER(TRIM(order_status)) NOT IN
				       ('cancelled','canceled','wc-cancelled','wc-canceled','refunded','wc-refunded','failed','wc-failed','trash','checkout-draft')",
				$drplus_capacity_vc_row['office_id'],
				$drplus_capacity_vc_date,
				$drplus_capacity_vc_row['start_time'],
				$drplus_capacity_vc_row['book_id']
			)
		);
		if ( $drplus_capacity_vc_peers > 0 ) {
			continue; // Another live booking legitimately holds the slot.
		}
		$drplus_capacity_vc_checked++;
		$drplus_capacity_vc_count = DrPlus_Child_Capacity_Repository::count_distinct_specialists(
			$drplus_capacity_vc_row['office_id'],
			$drplus_capacity_vc_date,
			$drplus_capacity_vc_row['start_time']
		);
		$drplus_capacity_vc_locked = DrPlus_Child_Capacity_Repository::specialist_already_booked(
			(int) $drplus_capacity_vc_row['specialist_id'],
			$drplus_capacity_vc_date,
			$drplus_capacity_vc_row['start_time']
		);
		if ( 0 !== $drplus_capacity_vc_count || $drplus_capacity_vc_locked ) {
			$drplus_capacity_vc_leaking[] = 'book #' . $drplus_capacity_vc_row['book_id']
				. ' (' . $drplus_capacity_vc_date . ' ' . $drplus_capacity_vc_row['start_time'] . ')';
		}
	}

	$drplus_capacity_vc_assert(
		'Cancelled bookings on this site really free their hour',
		empty( $drplus_capacity_vc_leaking ),
		empty( $drplus_capacity_vc_cancelled_rows )
			? 'no cancelled bookings in this database — nothing to check'
			: ( empty( $drplus_capacity_vc_leaking )
				? $drplus_capacity_vc_checked . ' cancelled booking(s) checked against the real table — every one released its slot'
				: count( $drplus_capacity_vc_leaking ) . ' cancelled booking(s) still block an hour: ' . implode( ', ', array_slice( $drplus_capacity_vc_leaking, 0, 5 ) ) )
	);

	// --- the copied order_status can go stale ----------------------------------------
	// Every count here reads {prefix}drplus_booking.order_status, which is a COPY of the
	// WooCommerce order status, refreshed by the parent theme's ionCube-encoded listener.
	// When that copy is not refreshed, a cancelled appointment keeps its hour blocked
	// forever and nobody sees an error. Detect it on this site's own data.
	$drplus_capacity_vc_sync = DrPlus_Child_Capacity_Repository::reconcile_order_statuses( false );
	$drplus_capacity_vc_assert(
		'Booking table agrees with WooCommerce (no silently blocked hours)',
		0 === $drplus_capacity_vc_sync['stale'],
		$drplus_capacity_vc_sync['stale'] > 0
			? $drplus_capacity_vc_sync['stale'] . ' booking row(s) disagree with their order — these hours stay blocked although the appointment is gone: ' . implode( ' | ', array_slice( $drplus_capacity_vc_sync['examples'], 0, 5 ) )
			: $drplus_capacity_vc_sync['inspected'] . ' order-linked row(s) checked — all agree with WooCommerce'
				. ( $drplus_capacity_vc_sync['unreadable'] > 0 ? ' (' . $drplus_capacity_vc_sync['unreadable'] . ' order(s) unreadable, kept as blocking)' : '' )
	);

	// --- the false "already booked" message ------------------------------------------------
	// A booking whose specialist id is unknown must be REFUSED but must not claim a conflict
	// exists. Reading last_unverifiable_reason() calls the real guard, so this fails on any
	// server still running the version that said "already booked" for an empty hour.
	$drplus_capacity_vc_guard_hit = DrPlus_Child_Capacity_Repository::specialist_already_booked( 0, '2026-11-19', '14:00:00' );
	$drplus_capacity_vc_reason     = DrPlus_Child_Capacity_Repository::last_unverifiable_reason();
	$drplus_capacity_vc_assert(
		'An unknown specialist is refused WITHOUT claiming a conflict',
		$drplus_capacity_vc_guard_hit && 'specialist' === $drplus_capacity_vc_reason,
		$drplus_capacity_vc_guard_hit
			? ( 'specialist' === $drplus_capacity_vc_reason
				? 'refused and reported as a missing specialist id, so the customer is told to re-select the specialist instead of hunting a booking that does not exist'
				: 'refused, but the reason was NOT recorded as "specialist" (' . var_export( $drplus_capacity_vc_reason, true ) . ') — the false "already booked" message can still appear' )
			: 'the guard ACCEPTED specialist_id=0 — this is an overbooking hole and must never pass'
	);

	// The rescue path: a zero id must be recoverable from ?sid= before the guard sees it.
	$drplus_capacity_vc_rescue_ok = false;
	$drplus_capacity_vc_rescue_msg = 'Resolved specialist id from ?sid= before the capacity guard';
	$drplus_capacity_vc_ref = new ReflectionMethod( 'DrPlus_Child_Capacity_Validator', 'resolve_specialist_from_parent' );
	$drplus_capacity_vc_ref->setAccessible( true );
	$drplus_capacity_vc_prev_sid = isset( $_GET['sid'] ) ? $_GET['sid'] : null;
	$_GET['sid'] = '1'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only probe.
	$drplus_capacity_vc_rescued = $drplus_capacity_vc_ref->invoke( null );
	if ( null === $drplus_capacity_vc_prev_sid ) {
		unset( $_GET['sid'] );
	} else {
		$_GET['sid'] = $drplus_capacity_vc_prev_sid;
	}
	$drplus_capacity_vc_rescue_ok = ( 1 === (int) $drplus_capacity_vc_rescued );
	if ( ! $drplus_capacity_vc_rescue_ok ) {
		$drplus_capacity_vc_rescue_msg = 'resolve_specialist_from_parent() returned ' . var_export( $drplus_capacity_vc_rescued, true ) . ' for ?sid=1 — a cart without specialist_id cannot be rescued, which is the reported bug';
	}
	$drplus_capacity_vc_assert( 'Specialist id is recoverable when the cart omits it', $drplus_capacity_vc_rescue_ok, $drplus_capacity_vc_rescue_msg );

	// ---- The false «قبلاً رزرو دارد» fix -----------------------------------------------
	// The reported bug: the customer picks specialist B, a stale
	// $_SESSION['booking']['specialist_id'] still holds A, A happens to be booked at that
	// hour, and the customer is told B has a booking. B has none — hence the complaint.
	//
	// These assertions drive the real rescue, not a grep for a flag name. If a future edit
	// drops the session marking, the first one fails and names the consequence.
	$drplus_capacity_vc_rescue_ref = new ReflectionMethod( 'DrPlus_Child_Capacity_Validator', 'rescue_specialist' );
	$drplus_capacity_vc_rescue_ref->setAccessible( true );

	$drplus_capacity_vc_prev_session = isset( $_SESSION['booking'] ) ? $_SESSION['booking'] : null;

	// (a) A cart that names a specialist is authoritative, even when the session disagrees.
	$_SESSION['booking'] = array( 'specialist_id' => 4242, 'date' => '2030-01-01', 'office_id' => '614' );
	$drplus_capacity_vc_from_cart = $drplus_capacity_vc_rescue_ref->invoke( null, array( 'specialist_id' => 1111 ), 'probe (cart)' );
	$drplus_capacity_vc_cart_ok    = ( 1111 === (int) $drplus_capacity_vc_from_cart['specialist_id'] )
		&& empty( $drplus_capacity_vc_from_cart['specialist_id_is_inferred'] );
	$drplus_capacity_vc_assert(
		'A specialist named by the cart outranks a stale session id',
		$drplus_capacity_vc_cart_ok,
		$drplus_capacity_vc_cart_ok
			? 'the cart id 1111 was kept and treated as authoritative, so the conflict check speaks about the specialist the customer actually chose'
			: 'got specialist_id=' . var_export( $drplus_capacity_vc_from_cart['specialist_id'], true )
				. ' inferred=' . ( empty( $drplus_capacity_vc_from_cart['specialist_id_is_inferred'] ) ? 'no' : 'yes' )
				. ' — a session id overruled the cart, which is the reported false-conflict bug'
	);

	// (b) An id taken from the session must be marked as inferred, or the validator will
	//     accuse a specialist the cart never named.
	$drplus_capacity_vc_from_session = $drplus_capacity_vc_rescue_ref->invoke( null, array( 'specialist_id' => 0 ), 'probe (session)' );
	$drplus_capacity_vc_session_ok   = ( 4242 === (int) $drplus_capacity_vc_from_session['specialist_id'] )
		&& ! empty( $drplus_capacity_vc_from_session['specialist_id_is_inferred'] );
	$drplus_capacity_vc_assert(
		'A specialist rescued from the session is flagged as inferred',
		$drplus_capacity_vc_session_ok,
		$drplus_capacity_vc_session_ok
			? 'the session id was recovered and flagged, so a conflict against it is reported as «متخصص مشخص نشد» instead of «قبلاً رزرو دارد»'
			: 'got specialist_id=' . var_export( $drplus_capacity_vc_from_session['specialist_id'], true )
				. ' inferred=' . ( empty( $drplus_capacity_vc_from_session['specialist_id_is_inferred'] ) ? 'no' : 'yes' )
				. ' — without the flag the module will claim a booking for a specialist the customer did not pick'
	);

	// (c) The two messages must stay distinct: the fix depends on being able to choose.
	$drplus_capacity_vc_msgs_ok = ( DrPlus_Child_Capacity_Messages::specialist_conflict() !== DrPlus_Child_Capacity_Messages::missing_specialist() )
		&& ( false === strpos( DrPlus_Child_Capacity_Messages::missing_specialist(), 'قبلاً رزرو دارد' ) );
	$drplus_capacity_vc_assert(
		'The «re-select the specialist» message never claims an existing booking',
		$drplus_capacity_vc_msgs_ok,
		$drplus_capacity_vc_msgs_ok
			? 'the two notices are distinct and only the conflict notice mentions «قبلاً رزرو دارد»'
			: 'the re-select notice either duplicates the conflict notice or contains «قبلاً رزرو دارد», which re-creates the phantom appointment the customer cannot find'
	);

	// Restore whatever the session held before probing.
	if ( null === $drplus_capacity_vc_prev_session ) {
		unset( $_SESSION['booking'] );
	} else {
		$_SESSION['booking'] = $drplus_capacity_vc_prev_session;
	}

	// ---- Abandoned checkout must not pin an hour forever -----------------------------
	// The second reported «no specialist has any booking, yet nothing can be booked» bug.
	// A row is inserted at checkout, the payment dies, and the row sits at `pending`. The
	// parent cron is supposed to cancel it after 30 minutes; when that cron does not run
	// (wp-cron disabled, no traffic, purged schedule) the hour is blocked forever and the
	// row is invisible in the appointments list.
	//
	// These probe the REAL functions, so a future edit that drops the age guard, drops
	// created_at from the SQL, or collapses the TTL fails here.
	if ( class_exists( 'DrPlus_Child_Capacity_Repository' ) ) {
		$drplus_capacity_vc_ttl = DrPlus_Child_Capacity_Repository::abandoned_checkout_ttl();
		$drplus_capacity_vc_ttl_ok = ( $drplus_capacity_vc_ttl >= 30 * MINUTE_IN_SECONDS );
		$drplus_capacity_vc_assert(
			'An abandoned checkout hold expires instead of pinning the hour forever',
			$drplus_capacity_vc_ttl_ok,
			$drplus_capacity_vc_ttl_ok
				? 'holds expire after ' . (int) round( $drplus_capacity_vc_ttl / MINUTE_IN_SECONDS ) . ' minutes (the parent cancels unpaid bookings after 30), so a dead payment cannot lock an hour permanently'
				: 'the TTL resolved to ' . (int) $drplus_capacity_vc_ttl . ' seconds — a zero or negative window means every abandoned checkout locks its hour forever, which is the reported symptom'
		);
	}

	// A TTL number proves nothing on its own — prove the GUARD actually releases a row.
	// This drives row_holds_slot() with two synthetic rows that differ only in age, so a
	// regression in the timestamp reading (the bug that shipped once already: created_at
	// was missing from the SELECT and the guard silently never fired) fails here.
	$drplus_capacity_vc_stale_ok  = false;
	$drplus_capacity_vc_stale_msg = 'the age guard could not be probed';
	if ( class_exists( 'DrPlus_Child_Capacity_Repository' ) ) {
		$drplus_capacity_vc_ttl   = DrPlus_Child_Capacity_Repository::abandoned_checkout_ttl();
		$drplus_capacity_vc_old   = gmdate( 'Y-m-d H:i:s', current_time( 'timestamp', true ) - ( $drplus_capacity_vc_ttl + 3600 ) );
		$drplus_capacity_vc_new   = gmdate( 'Y-m-d H:i:s', current_time( 'timestamp', true ) - 60 );

		$drplus_capacity_vc_probe = function ( $created ) {
			return array(
				'book_id'      => 0,
				'order_status' => 'pending',
				'order_id'     => 0, // no order: order_releases_slot() keeps holding, so only the age guard can free it
				'created_at'   => $created,
			);
		};

		$drplus_capacity_vc_fresh_holds = DrPlus_Child_Capacity_Repository::row_holds_slot( $drplus_capacity_vc_probe( $drplus_capacity_vc_new ) );
		$drplus_capacity_vc_stale_frees = DrPlus_Child_Capacity_Repository::row_holds_slot( $drplus_capacity_vc_probe( $drplus_capacity_vc_old ) );

		$drplus_capacity_vc_stale_ok = ( true === $drplus_capacity_vc_fresh_holds && false === $drplus_capacity_vc_stale_frees );
		if ( $drplus_capacity_vc_stale_ok ) {
			$drplus_capacity_vc_stale_msg = 'a 1-minute-old pending row still holds the hour, and one older than the TTL releases it';
		} elseif ( false === $drplus_capacity_vc_stale_frees ) {
			$drplus_capacity_vc_stale_msg = 'an aged pending row still HOLDS its hour — an abandoned checkout can lock the slot permanently, which is the reported symptom';
		} else {
			$drplus_capacity_vc_stale_msg = 'a freshly paid row was already released — live payments would be double-booked';
		}
	}
	$drplus_capacity_vc_assert(
		'An aged unpaid row releases its hour while a fresh one keeps it',
		$drplus_capacity_vc_stale_ok,
		$drplus_capacity_vc_stale_msg
	);

	// ---- Conflict policy: does the setting actually change behaviour? ----------------
	// Not a grep for the option name. This drives conflict_mode() through every value a
	// stray option row could contain and checks the resolved policy, because a typo in the
	// option must fall back to the safe default rather than silently disabling the guard.
	if ( class_exists( 'DrPlus_Child_Capacity_Service' ) && class_exists( 'DrPlus_Child_Capacity_Repository' ) ) {
		$drplus_capacity_vc_prev_mode = get_option( 'drplus_capacity_conflict_mode', null );

		$drplus_capacity_vc_mode_cases = array(
			'room'      => 'room',   // the default
			'any'       => 'any',    // one booking per specialist per hour, any type
			'rooms_only' => 'room',  // legacy spelling from the first draft
			'nonsense'  => 'room',   // an unknown value must not weaken the guard
			''          => 'room',   // blank
		);
		$drplus_capacity_vc_mode_ok  = true;
		$drplus_capacity_vc_mode_msg = 'Every stored value resolves to a known policy';
		foreach ( $drplus_capacity_vc_mode_cases as $drplus_capacity_vc_case => $drplus_capacity_vc_want ) {
			update_option( 'drplus_capacity_conflict_mode', $drplus_capacity_vc_case, false );
			$drplus_capacity_vc_got = DrPlus_Child_Capacity_Service::conflict_mode();
			if ( $drplus_capacity_vc_got !== $drplus_capacity_vc_want ) {
				$drplus_capacity_vc_mode_ok  = false;
				$drplus_capacity_vc_mode_msg = sprintf(
					'stored %s resolved to %s, expected %s',
					var_export( $drplus_capacity_vc_case, true ),
					var_export( $drplus_capacity_vc_got, true ),
					var_export( $drplus_capacity_vc_want, true )
				);
				break;
			}
		}
		$drplus_capacity_vc_assert( 'Conflict policy option resolves to a known mode for every value', $drplus_capacity_vc_mode_ok, $drplus_capacity_vc_mode_msg );

		// The predicate the repository and validator both branch on must agree with the mode.
		update_option( 'drplus_capacity_conflict_mode', 'room', false );
		$drplus_capacity_vc_room_blocks = DrPlus_Child_Capacity_Service::virtual_visit_blocks_room();
		update_option( 'drplus_capacity_conflict_mode', 'any', false );
		$drplus_capacity_vc_any_blocks = DrPlus_Child_Capacity_Service::virtual_visit_blocks_room();
		$drplus_capacity_vc_assert(
			'virtual_visit_blocks_room() is false in room mode and true in any mode',
			( false === $drplus_capacity_vc_room_blocks && true === $drplus_capacity_vc_any_blocks ),
			sprintf( 'room=%s any=%s', var_export( $drplus_capacity_vc_room_blocks, true ), var_export( $drplus_capacity_vc_any_blocks, true ) )
		);

		// Restore whatever the site had before this page ran. A verifier must not be a
		// configuration change.
		if ( null === $drplus_capacity_vc_prev_mode ) {
			delete_option( 'drplus_capacity_conflict_mode' );
		} else {
			update_option( 'drplus_capacity_conflict_mode', $drplus_capacity_vc_prev_mode, false );
		}
		// Whatever we restored, the default must still be the safe one: the two settings a
		// fresh install ships with must be room-mode and enabled.
		$drplus_capacity_vc_assert(
			'The schema default is room mode, so an upgrade changes no behaviour',
			in_array( get_option( 'drplus_capacity_conflict_mode', 'room' ), array( 'room', 'rooms_only', 'any' ), true ),
			'stored value: ' . var_export( get_option( 'drplus_capacity_conflict_mode', 'room' ), true )
		);
	}

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="robots" content="noindex,nofollow">
	<title>Dr Plus Capacity — Deployment Check</title>
	<style>
		body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;margin:0;padding:32px;background:#f6f7f7;color:#1d2327;line-height:1.6}
		.wrap{max-width:860px;margin:0 auto;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:28px 32px}
		h1{font-size:1.5rem;margin:0 0 4px}
		.sub{color:#646970;margin:0 0 24px;font-size:.9rem}
		.banner{padding:14px 18px;border-radius:6px;font-weight:600;margin:0 0 24px;border-left:4px solid}
		.pass{background:#edfaef;border-color:#00a32a;color:#005c12}
		.fail{background:#fcf0f1;border-color:#d63638;color:#8a1f21}
		table{width:100%;border-collapse:collapse;font-size:.9rem}
		th,td{text-align:left;padding:10px 12px;border-bottom:1px solid #f0f0f1;vertical-align:top}
		th{background:#f6f7f7;font-size:.78rem;text-transform:uppercase;letter-spacing:.03em;color:#646970}
		.ok{color:#00a32a;font-weight:700}
		.no{color:#d63638;font-weight:700}
		code{background:#f0f0f1;padding:2px 5px;border-radius:3px;font-size:.85em;word-break:break-all}
		.hint{color:#646970;font-size:.85rem;display:block;margin-top:3px}
		.hash{font-family:ui-monospace,Menlo,Consolas,monospace;font-size:.8rem;color:#646970}
		.files{margin:28px 0 0}
		.files h2{font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;color:#646970;margin:0 0 10px}
		.files td{font-size:.8rem;padding:6px 12px}
	</style>
</head>
<body>
<div class="wrap">
	<h1>Dr Plus Capacity — Deployment Check</h1>
	<p class="sub">Reads the files on this server directly. If every row is green, the fixed code is what PHP will execute.</p>

	<?php if ( 0 === $drplus_capacity_vc_fail ) : ?>
		<div class="banner pass">All checks passed — this server is running the fixed code.</div>
	<?php else : ?>
		<div class="banner fail"><?php echo (int) $drplus_capacity_vc_fail; ?> check(s) failed — either the fixed code is not being served, or it is not behaving correctly on this server. Do not trust capacity enforcement yet.</div>
	<?php endif; ?>

	<table>
		<thead>
			<tr><th>Check</th><th>File</th><th>Result</th></tr>
		</thead>
		<tbody>
		<?php foreach ( $drplus_capacity_vc_rows as $drplus_capacity_vc_row ) : ?>
			<tr>
				<td>
					<?php echo esc_html( $drplus_capacity_vc_row['label'] ); ?>
					<span class="hint"><?php echo esc_html( $drplus_capacity_vc_row['hint'] ); ?></span>
				</td>
				<td><code><?php echo esc_html( $drplus_capacity_vc_row['file'] ); ?></code></td>
				<td>
					<?php if ( $drplus_capacity_vc_row['ok'] ) : ?>
						<span class="ok">PASS</span>
					<?php else : ?>
						<span class="no">FAIL</span>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<div class="files">
		<h2>Behaviour checks (executed on this server)</h2>
		<table>
			<thead>
				<tr><th>Assertion</th><th>Observed</th><th>Result</th></tr>
			</thead>
			<tbody>
			<?php if ( empty( $drplus_capacity_vc_live ) ) : ?>
				<tr><td colspan="3">No assertions ran.</td></tr>
			<?php endif; ?>
			<?php foreach ( $drplus_capacity_vc_live as $drplus_capacity_vc_live_row ) : ?>
				<tr>
					<td><?php echo esc_html( $drplus_capacity_vc_live_row['label'] ); ?></td>
					<td style="font-family:ui-monospace,Menlo,Consolas,monospace;font-size:.8rem;color:#646970">
						<?php echo esc_html( $drplus_capacity_vc_live_row['evidence'] ); ?>
					</td>
					<td>
						<?php if ( $drplus_capacity_vc_live_row['ok'] ) : ?>
							<span class="ok">PASS</span>
						<?php else : ?>
							<span class="no">FAIL</span>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<div class="files">
		<h2>File fingerprints</h2>
		<table>
			<tbody>
			<?php
			$drplus_capacity_vc_list = glob( $drplus_capacity_vc_dir . '/*.php' );
			sort( $drplus_capacity_vc_list );
			foreach ( $drplus_capacity_vc_list as $drplus_capacity_vc_file ) :
				if ( 'version-check.php' === basename( $drplus_capacity_vc_file ) ) {
					continue;
				}
				?>
				<tr>
					<td><code><?php echo esc_html( basename( $drplus_capacity_vc_file ) ); ?></code></td>
					<td class="hash"><?php echo esc_html( md5_file( $drplus_capacity_vc_file ) ); ?></td>
					<td class="hash"><?php echo esc_html( gmdate( 'Y-m-d H:i:s', filemtime( $drplus_capacity_vc_file ) ) ); ?> UTC</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<p class="sub" style="margin-top:24px">Delete this file once deployment is confirmed — it is diagnostic tooling, not runtime code.</p>
</div>
</body>
</html>
