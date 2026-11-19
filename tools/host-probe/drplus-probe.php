<?php
/**
 * drplus-probe.php — READ-ONLY behavioural probe for the Dr Plus parent theme.
 *
 * WHY THIS EXISTS
 *
 * Ten parent-theme files are ionCube-encrypted (SaveBookingProcess.php, the Redux
 * options layer, the wallet, and utils-chat). Their logic cannot be read here, and
 * it cannot be decrypted without the loader's private key — which is not in the
 * files. So the only honest way to know what that code does is to ASK IT, on a
 * machine where it actually runs: yours.
 *
 * This file creates nothing, writes nothing, cancels nothing, and books nothing.
 * It calls the theme's own public functions and prints what they return. Every
 * check is a pure query.
 *
 * HOW TO USE
 *
 *   1. Upload this file to public_html/ (next to wp-load.php).
 *   2. Open  https://YOUR-SITE/drplus-probe.php  while logged in as an administrator.
 *   3. Copy the whole output back. Then DELETE the file.
 *
 * It refuses to run for anyone who is not an administrator.
 *
 * @package DrPlusHostProbe
 */

// ---------------------------------------------------------------------------
// Load WordPress, then refuse to continue unless this is a real admin.
// ---------------------------------------------------------------------------
$root = __DIR__;
if ( ! file_exists( $root . '/wp-load.php' ) ) {
	$root = dirname( __DIR__ );
}
if ( ! file_exists( $root . '/wp-load.php' ) ) {
	header( 'Content-Type: text/plain; charset=utf-8' );
	exit( "wp-load.php not found. Put this file in public_html/ next to wp-load.php.\n" );
}

require_once $root . '/wp-load.php';

if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
	header( 'Content-Type: text/plain; charset=utf-8' );
	exit( "Log in as an administrator, then reload this page.\n" );
}

header( 'Content-Type: text/plain; charset=utf-8' );

$lines = array();
function p( $s = '' ) {
	global $lines;
	$lines[] = $s;
}
function head( $s ) {
	p();
	p( str_repeat( '=', 74 ) );
	p( $s );
	p( str_repeat( '=', 74 ) );
}
function kv( $k, $v ) {
	p( sprintf( '  %-46s %s', $k, $v ) );
}

// ---------------------------------------------------------------------------
head( '1. ENVIRONMENT' );
kv( 'WordPress', get_bloginfo( 'version' ) );
kv( 'PHP', PHP_VERSION );
kv( 'WooCommerce', defined( 'WC_VERSION' ) ? WC_VERSION : 'not loaded' );
kv( 'Parent theme', wp_get_theme( 'dr-plus' )->get( 'Version' ) ?: 'unknown' );
kv( 'Child theme active', get_stylesheet() );
kv( 'Child theme', wp_get_theme()->get( 'Version' ) ?: 'unknown' );
kv( 'ionCube Loader', extension_loaded( 'ionCube Loader' ) ? 'LOADED (encrypted files run)' : 'not loaded' );
kv( 'Timezone', wp_timezone_string() );
kv( 'Today (site)', current_time( 'Y-m-d H:i:s' ) );

// ---------------------------------------------------------------------------
head( '2. WHICH PARENT FILES ARE ENCRYPTED (and therefore unreadable to any auditor)' );

$theme_dir  = get_template_directory();
	if ( ! is_dir( $theme_dir ) ) {
		$theme_dir = dirname( $root ) . '/wp-content/themes/dr-plus';
	}
$encrypted  = array();
$iter       = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $theme_dir, FilesystemIterator::SKIP_DOTS ) );
foreach ( $iter as $file ) {
	// Guard: some filesystems hand back directories or unreadable entries.
	if ( ! $file->isFile() ) {
		continue;
	}
	if ( 'php' !== strtolower( $file->getExtension() ) ) {
		continue;
	}
	$fh = @fopen( $file->getPathname(), 'rb' );
	if ( ! $fh ) {
		continue;
	}
	$chunk = fread( $fh, 512 );
	fclose( $fh );
	// ionCube always stamps ICB0 in the first line of an encoded file.
	if ( false !== strpos( (string) $chunk, 'ICB0' ) ) {
		$encrypted[] = str_replace( $theme_dir . '/', '', str_replace( '\\', '/', $file->getPathname() ) )
			. '  (' . number_format( $file->getSize() ) . ' B)';
	}
}
kv( 'Encrypted files found', count( $encrypted ) );
foreach ( $encrypted as $e ) {
	p( '    - ' . $e );
}

// ---------------------------------------------------------------------------
head( '3. IS THE CAPACITY MODULE ACTIVE?' );

kv( 'DrPlus_Child_Capacity_Service exists', class_exists( 'DrPlus_Child_Capacity_Service' ) ? 'YES' : 'NO' );
kv( 'Capacity filter hooked', (bool) has_filter( 'drplus_child_capacity/enabled' ) || class_exists( 'DrPlus_Child_Capacity_Service' ) ? 'class present' : 'absent' );
if ( class_exists( 'DrPlus_Child_Capacity_Service' ) ) {
	kv( 'Module enabled', DrPlus_Child_Capacity_Service::enabled() ? 'yes' : 'NO' );
	kv( 'Default capacity', defined( 'DRPLUS_ROOM_CAPACITY_DEFAULT' ) ? DRPLUS_ROOM_CAPACITY_DEFAULT : '?' );
}

// ---------------------------------------------------------------------------
head( '4. DOES THE PARENT THEME ALREADY PREVENT A MIXED DOUBLE-BOOKING?' );
p( 'The question: for ONE specialist at ONE hour, if an in-person booking exists,' );
p( 'does the parent still offer online visits, and vice versa?' );
p( 'This is answered by calling the parent\'s own slot generator. Nothing is written.' );

if ( ! class_exists( 'DrPlus\\Utils\\Booking' ) ) {
	p( '  !! DrPlus\Utils\Booking not found — cannot probe the parent.' );
} else {
	// Pick a specialist and a date that the parent actually has hours for.
	$probe_specialist = 0;
	$probe_date       = '';
	$probe_office     = '';

	global $wpdb;
	$spec_table = $wpdb->prefix . 'drplus_specialists';
	$candidates = array();
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $spec_table ) ) === $spec_table ) {
		$candidates = array_map( 'intval', (array) $wpdb->get_col( "SELECT id FROM {$spec_table} ORDER BY id" ) );
	}
	kv( 'Specialists in the table', count( $candidates ) );

	// Try every specialist x next 21 days x each custom office they own, and keep the
	// first combination the parent actually has open hours for. The parent's slot
	// generator needs (date, specialist, office) that all line up, so searching is the
	// only reliable way to find a live probe point.
	$found = null;
	foreach ( $candidates as $sid ) {
		if ( $found ) {
			break;
		}
		$sp = null;
		if ( class_exists( 'DrPlus\Utils\Specialists' ) ) {
			$sp = ( new DrPlus\Utils\Specialists() )->find( $sid );
		} else {
			// Fallback: read the offices blob directly (it is double-encoded in this theme).
			$raw = $wpdb->get_var( $wpdb->prepare( "SELECT offices FROM {$spec_table} WHERE id = %d", $sid ) );
			$dec = json_decode( (string) $raw, true );
			if ( is_string( $dec ) ) {
				$dec = json_decode( $dec, true );
			}
			if ( is_array( $dec ) ) {
				$sp = new stdClass();
				$sp->offices = $dec;
			}
		}
		if ( empty( $sp->offices ) ) {
			continue;
		}

		$offices = array();
		foreach ( (array) $sp->offices as $oid => $o ) {
			$type = isset( $o['type'] ) ? $o['type'] : '';
			if ( 'custom' === $type || 'hospital' === $type ) {
				$offices[] = (string) ( isset( $o['id'] ) ? $o['id'] : $oid );
			}
		}

		for ( $d = 1; $d <= 21 && ! $found; $d++ ) {
			$date = gmdate( 'Y-m-d', strtotime( '+' . $d . ' days' ) );
			foreach ( $offices as $oid ) {
				$slots = DrPlus\Utils\Booking::get_available_time_slots( $date, $sid, $oid, 30 );
				if ( ! empty( $slots ) ) {
					$found = array( 'spec' => $sid, 'date' => $date, 'office' => $oid, 'slots' => $slots );
					break;
				}
			}
		}
	}

	if ( ! $found ) {
		p( '  Could not find a specialist with open hours in the next 21 days.' );
		p( '  (Not a failure — it only means nothing to probe right now.)' );
	} else {
		kv( 'Probe specialist', $found['spec'] );
		kv( 'Probe date', $found['date'] );
		kv( 'Probe office', $found['office'] );
		kv( 'In-person slots offered', count( $found['slots'] ) );

		$times = array();
		foreach ( $found['slots'] as $s ) {
			$times[] = $s['from'];
		}
		kv( 'First hours', implode( ' ', array_slice( $times, 0, 6 ) ) );
		$hour = $times[0];

		// --- what the parent offers for ONLINE visits on the same day -------------
		p();
		p( '  -- the parent\'s own view of ONLINE visits for this specialist/day --' );
		foreach ( array( 'phone_consultation', 'chat_consultation', 'video_consultation' ) as $online ) {
			$os = DrPlus\Utils\Booking::get_available_time_slots( $found['date'], $found['spec'], $online, 30 );
			$of = array();
			foreach ( (array) $os as $x ) {
				$of[] = $x['from'];
			}
			kv( $online, count( $of ) . ' slot(s)' . ( $of ? ' : ' . implode( ' ', array_slice( $of, 0, 6 ) ) : ' (none)' ) );
		}

		// --- read-only look at the booking table for that hour -------------------
		global $wpdb;
		$table = $wpdb->prefix . 'drplus_booking';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
			p();
			p( '  -- existing rows for that specialist at that hour (read-only) --' );
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT book_id, office_id, start_time, end_time, order_status FROM {$table}
					 WHERE specialist_id = %d AND `date` = %s AND start_time = %s AND deleted = 0",
					$found['spec'],
					$found['date'],
					$hour . ':00'
				),
				ARRAY_A
			);
			if ( empty( $rows ) ) {
				p( '    (no rows — the hour is empty, so there is nothing to conflict)' );
			} else {
				foreach ( $rows as $r ) {
					p( sprintf( '    book #%s office=%s %s-%s status=%s', $r['book_id'], $r['office_id'], $r['start_time'], $r['end_time'], $r['order_status'] ) );
				}
			}
		}
	}
}

// ---------------------------------------------------------------------------
head( '5. WHICH CANCEL PATHS ACTUALLY EXIST IN THIS INSTALL' );

kv( 'Booking::cancel_booking()', class_exists( 'DrPlus\\Utils\\Booking' ) && method_exists( 'DrPlus\\Utils\\Booking', 'cancel_booking' ) ? 'EXISTS' : 'MISSING' );
kv( 'Booking::get_available_time_slots()', class_exists( 'DrPlus\\Utils\\Booking' ) && method_exists( 'DrPlus\\Utils\\Booking', 'get_available_time_slots' ) ? 'EXISTS' : 'MISSING' );
kv( 'Cron drplus_update_booking_statuses_hook', wp_next_scheduled( 'drplus_update_booking_statuses_hook' ) ? gmdate( 'Y-m-d H:i:s', wp_next_scheduled( 'drplus_update_booking_statuses_hook' ) ) . ' (UTC)' : 'not scheduled' );

if ( class_exists( 'DrPlus\\Utils\\Booking' ) && method_exists( 'DrPlus\\Utils\\Booking', 'get_order_statuses' ) ) {
	p();
	p( '  -- statuses the parent itself considers valid --' );
	foreach ( (array) DrPlus\Utils\Booking::get_order_statuses() as $k => $v ) {
		p( sprintf( '    %-18s %s', $k, is_string( $v ) ? $v : '' ) );
	}
}

// ---------------------------------------------------------------------------
head( '6. CAPACITY MODULE: CURRENT STATE' );

if ( class_exists( 'DrPlus_Child_Capacity_Repository' ) ) {
	global $wpdb;
	$table = DrPlus_Child_Capacity_Repository::booking_table();

	$stale = DrPlus_Child_Capacity_Repository::reconcile_order_statuses( false );
	kv( 'Rows checked against WooCommerce', $stale['inspected'] );
	kv( 'Rows whose stored status is stale', $stale['stale'] );
	foreach ( array_slice( $stale['examples'], 0, 5 ) as $e ) {
		p( '    ' . $e );
	}

	$cancelled = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE LOWER(TRIM(order_status)) = 'cancelled' AND deleted = 0" );
	kv( "Cancelled rows in the table", $cancelled );

	$active = (int) $wpdb->get_var(
		"SELECT COUNT(*) FROM {$table} WHERE deleted = 0
		 AND LOWER(TRIM(order_status)) NOT IN
		 ('cancelled','canceled','wc-cancelled','wc-canceled','refunded','wc-refunded','failed','wc-failed','trash','checkout-draft')"
	);
	kv( 'Active (slot-holding) rows', $active );
} else {
	p( '  Capacity repository class not loaded.' );
}

// ---------------------------------------------------------------------------
head( '7. THE ANSWER, STATED PLAINLY' );
p( 'If section 4 printed online slot counts for this specialist, that is the parent');
p( 'theme\'s own answer, measured — not guessed. Compare it with the in-person count.');
p( 'If online shows slots at hours where the in-person list is also open, the parent');
p( 'does NOT block a mixed double-booking, and neither does the child module.');
p();
p( 'Send this whole output back and the behaviour can be described exactly.' );

echo implode( "\n", $lines ) . "\n";
