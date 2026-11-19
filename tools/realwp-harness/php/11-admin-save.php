<?php
/**
 * 11-admin-save.php
 *
 * The settings page has to survive a careless or hostile POST, not just a happy path.
 *
 * Two things are asserted, and both of them through the REAL handler — the same
 * handle_save() WordPress runs on admin_init, nonce and capability included:
 *
 *   1. a legitimate 'any' selection is stored as 'any'
 *   2. anything else is stored as 'room'
 *
 * Point 2 is the one that matters. The double-booking policy decides whether a specialist
 * can be double-booked; a value that silently disables it because of a typo in a hidden
 * field, a stale browser cache or a hand-edited POST is exactly the failure that would be
 * invisible in testing and expensive in production.
 */

require_once __DIR__ . '/00-bootstrap.php';

require_once '/wordpress/wp-admin/includes/admin.php';

global $wpdb;

$fail = 0;
$pass = 0;
function chk( $label, $cond, $detail = '' ) {
	global $fail, $pass;
	if ( $cond ) {
		$pass++;
		printf( "  \033[32mPASS\033[0m  %s\n", $label );
	} else {
		$fail++;
		printf( "  \033[31mFAIL\033[0m  %s%s\n", $label, '' !== $detail ? ' — ' . $detail : '' );
	}
}

// Snapshot everything this suite touches. A test that leaves the site altered makes the
// NEXT suite fail for a reason that has nothing to do with it — the alias map in
// particular is read by version-check.php, and clobbering it produced a spurious
// "Clinic resolution is symmetric" failure two steps later.
$saved_aliases = get_option( 'drplus_capacity_clinic_aliases', array() );
// Capacities are one option PER clinic id (drplus_capacity_clinic_<id>), not a single
// array, so they have to be collected by prefix rather than by name.
$saved_capacities = array();
$saved_capacity_ids = array();
foreach ( (array) $wpdb->get_col(
	"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'drplus_capacity_clinic\\_%'"
) as $option_name ) {
	if ( preg_match( '/^drplus_capacity_clinic_(\\d+)$/', $option_name, $m ) ) {
		$saved_capacity_ids[]             = (int) $m[1];
		$saved_capacities[ (int) $m[1] ] = get_option( $option_name );
	}
}
$saved_mode = get_option( 'drplus_capacity_conflict_mode', null );

wp_set_current_user( 30 );
chk( 'an administrator is authenticated', current_user_can( 'manage_options' ) );

/**
 * Drive the real handler. check_admin_referer() ends the request on a bad nonce, so the
 * nonce is minted properly rather than bypassed — the point is to test the filter, and
 * skipping the nonce would test a different code path than production uses.
 */
$save = static function ( $posted_mode ) {
	$nonce = wp_create_nonce( DrPlus_Child_Capacity_Admin::NONCE_ACTION );
	$_POST = array(
		'drplus_capacity_save_submit' => '1',
		'drplus_capacity_clinic'      => array( 32 => '2' ),
		'drplus_capacity_alias'       => array( 32 => '1b9d72ba-f2b2-4dfd-8a3c-5b0f13c5c650' ),
		DrPlus_Child_Capacity_Admin::NONCE_FIELD => $nonce,
	);
	if ( null !== $posted_mode ) {
		$_POST['drplus_capacity_conflict_mode'] = $posted_mode;
	}
	// check_admin_referer() reads $_REQUEST, not $_POST. In a real request PHP populates
	// $_REQUEST from the POST body before WordPress runs; here it is already built, so the
	// two have to be kept in step by hand or the nonce check fails and every case dies.
	$_REQUEST = $_POST;
	// handle_save() redirects and exits on the success path. Capture that as a signal
	// rather than letting it kill the suite: a shutdown function would be too late to
	// assert anything useful afterwards.
	// handle_save() ends the request through wp_die() on several paths. In a real admin
	// request that ends the page; here it must not end the SUITE, so wp_die is swapped for
	// an exception and the stored value is read afterwards.
	$died = false;
	$original_die = $GLOBALS['wp_die_handler'] ?? null;
	add_filter( 'wp_die_handler', static function () {
		return static function ( $message ) {
			throw new RuntimeException( 'wp_die: ' . ( is_string( $message ) ? $message : 'blocked' ) );
		};
	} );
	try {
		DrPlus_Child_Capacity_Admin::handle_save();
	} catch ( RuntimeException $e ) {
		$died = true;
	}
	unset( $GLOBALS['wp_die_handler'] );
	if ( null !== $original_die ) {
		$GLOBALS['wp_die_handler'] = $original_die;
	}
	$_POST = array();
	return array( get_option( 'drplus_capacity_conflict_mode', '<missing>' ), $died );
};

$cases = array(
	'any'                   => 'any',
	'room'                  => 'room',
	'ANY'                   => 'room', // not whitelisted: only the exact lowercase token counts
	'Any'                   => 'room',
	' any'                  => 'any',  // sanitize_text_field() trims — ' any' IS 'any'
	'any '                  => 'any',
	"\tany\n"              => 'any',  // real tab/newline, trimmed like any other whitespace
	'rooms_only'            => 'room', // legacy spelling is not a stored value
	'nonsense'              => 'room',
	'<script>alert(1)</script>' => 'room',
	''                      => 'room',
	null                    => 'room', // field absent entirely
);

foreach ( $cases as $posted => $expected ) {
	update_option( 'drplus_capacity_conflict_mode', 'sentinel', false );
	list( $got, $died ) = $save( null === $posted ? null : (string) $posted );
	$label = null === $posted ? 'POST omits the field' : 'POST sends ' . var_export( (string) $posted, true );
	chk( $label . ' → stored ' . var_export( $expected, true ), ! $died && $expected === $got, 'died=' . var_export( $died, true ) . ' stored ' . var_export( $got, true ) );
}

// Capability gate: a subscriber must not be able to change the policy at all.
wp_set_current_user( 2 );
update_option( 'drplus_capacity_conflict_mode', 'room', false );
$before = get_option( 'drplus_capacity_conflict_mode' );
$nonce  = wp_create_nonce( DrPlus_Child_Capacity_Admin::NONCE_ACTION );
$_POST  = array(
	'drplus_capacity_save_submit'           => '1',
	'drplus_capacity_clinic'                => array( 32 => '2' ),
	'drplus_capacity_conflict_mode'         => 'any',
	DrPlus_Child_Capacity_Admin::NONCE_FIELD => $nonce,
);
$_REQUEST = $_POST;
// handle_save() calls wp_die() for a non-administrator; catch the exception WordPress
// raises in CLI when wp_die() is reached.
$blocked = false;
try {
	DrPlus_Child_Capacity_Admin::handle_save();
} catch ( Throwable $e ) {
	$blocked = true;
}
$_POST = array();
chk(
	'a subscriber cannot change the policy',
	$blocked || $before === get_option( 'drplus_capacity_conflict_mode' ),
	'blocked=' . var_export( $blocked, true ) . ' value=' . var_export( get_option( 'drplus_capacity_conflict_mode' ), true )
);

// Restore exactly what was here before. The suite must not change behaviour, and that
// includes the alias map and the capacities, not just the policy it was testing.
update_option( 'drplus_capacity_clinic_aliases', $saved_aliases, false );
foreach ( $saved_capacities as $clinic_id => $value ) {
	update_option( 'drplus_capacity_clinic_' . $clinic_id, $value, false );
}
if ( null === $saved_mode ) {
	delete_option( 'drplus_capacity_conflict_mode' );
} else {
	update_option( 'drplus_capacity_conflict_mode', $saved_mode, false );
}
$restored = get_option( 'drplus_capacity_clinic_aliases', array() ) === $saved_aliases;
chk( 'the suite restores the alias map it overwrote', $restored );

printf( "\nRESULT: %d passed, %d failed\n", $pass, $fail );
exit( $fail > 0 ? 1 : 0 );
