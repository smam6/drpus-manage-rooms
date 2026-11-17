<?php
/**
 * Replay the real MariaDB dump through WordPress's own SQLite driver.
 *
 * Nothing is hand-translated: every CREATE TABLE and INSERT is handed to
 * $wpdb->query(), so the official MySQL→SQLite translator from
 * WordPress/sqlite-database-integration does the conversion. That is the
 * strongest available guarantee that the data the tests read is the data the
 * dump contains.
 */

require __DIR__ . '/00-bootstrap.php';

global $wpdb;

$dump = '/import/aghazga1_new.sql';
if ( ! file_exists( $dump ) ) {
	echo "dump not mounted at $dump\n";
	exit( 1 );
}

// Tables that carry no booking data and only add ~15 MB of cron/log noise.
$skip_tables = array(
	'actionscheduler_actions', 'actionscheduler_claims', 'actionscheduler_groups',
	'actionscheduler_logs', 'aiowps_audit_log', 'aiowps_debug_log', 'aiowps_events',
	'aiowps_global_meta', 'aiowps_logged_in_users', 'aiowps_login_lockdown',
	'aiowps_message_store', 'aiowps_permanent_block', 'litespeed_url',
	'litespeed_url_file', 'rank_math_404_logs', 'rank_math_internal_links',
	'rank_math_internal_meta', 'rtl_rsm_logs', 'sync_basalam_debug_logs',
	'sync_basalam_job_manager', 'tm_taskmeta', 'tm_tasks',
	'e_submissions_actions_log', 'e_submissions_values', 'e_submissions',
	'e_notes', 'e_notes_users_relations', 'e_events',
);

$raw = file_get_contents( $dump );
$raw = preg_replace( '/^--\s.*$/m', '', $raw );
$raw = preg_replace( '/^\/\*![0-9]+\s(.*?)\*\/;?$/m', '', $raw );
$statements = preg_split( '/;\s*\n/', $raw );

/** Extract the backticked table name from a statement. */
function h_table_of( $sql ) {
	return preg_match( '/^(?:CREATE TABLE|INSERT|REPLACE)\s+(?:IGNORE\s+)?(?:INTO\s+)?`([^`]+)`/i', $sql, $m )
		? $m[1] : '';
}

/** Should this table be skipped? */
function h_skip( $table ) {
	global $skip_tables;
	if ( '' === $table ) {
		return false;
	}
	return in_array( preg_replace( '/^Dj3i_/', '', $table ), $skip_tables, true );
}

h_section( 'Schema (CREATE TABLE) — translated by the WordPress SQLite driver' );

$created = 0;
$create_errors = array();
foreach ( $statements as $sql ) {
	$sql = trim( $sql );
	if ( ! preg_match( '/^CREATE TABLE/i', $sql ) ) {
		continue;
	}
	$table = h_table_of( $sql );
	$is_as = 0 === strpos( $table, $wpdb->prefix . 'actionscheduler' );
	if ( h_skip( $table ) && ! $is_as ) {
		continue;
	}
	// Full fidelity: replace whatever the fresh install created with the
	// production schema from the dump.
	$wpdb->query( "DROP TABLE IF EXISTS `$table`" );
	if ( false === $wpdb->query( $sql ) ) {
		$create_errors[] = $table . ' :: ' . preg_replace( '/\s+/', ' ', $wpdb->last_error );
	} else {
		$created++;
	}
}
printf( "  tables created : %d\n", $created );
printf( "  create errors  : %d\n", count( $create_errors ) );
foreach ( array_slice( $create_errors, 0, 5 ) as $e ) {
	printf( "    - %s\n", substr( $e, 0, 160 ) );
}

h_section( 'Data (INSERT) — translated by the WordPress SQLite driver' );

$inserted = 0;
$rows     = 0;
$insert_errors = array();
$per_table = array();
foreach ( $statements as $sql ) {
	$sql = trim( $sql );
	if ( ! preg_match( '/^(INSERT|REPLACE)\s/i', $sql ) ) {
		continue;
	}
	$table = h_table_of( $sql );
	if ( h_skip( $table ) ) {
		// Action Scheduler keeps its schema (WooCommerce queries it on shutdown)
		// but not its job history.
		continue;
	}
	$affected = $wpdb->query( $sql );
	if ( false === $affected ) {
		$insert_errors[] = $table . ' :: ' . preg_replace( '/\s+/', ' ', $wpdb->last_error );
		continue;
	}
	$inserted++;
	$rows += (int) $affected;
	$per_table[ $table ] = ( $per_table[ $table ] ?? 0 ) + (int) $affected;
}
printf( "  insert statements : %d\n", $inserted );
printf( "  rows written      : %d\n", $rows );
printf( "  insert errors     : %d\n", count( $insert_errors ) );
foreach ( array_slice( $insert_errors, 0, 5 ) as $e ) {
	printf( "    - %s\n", substr( $e, 0, 160 ) );
}

h_section( 'Key tables as seen by WordPress' );
$key = array(
	'drplus_booking'   => $wpdb->prefix . 'drplus_booking',
	'drplus_specialists' => $wpdb->prefix . 'drplus_specialists',
	'drplus_times'     => $wpdb->prefix . 'drplus_times',
	'wc_orders'        => $wpdb->prefix . 'wc_orders',
	'posts'            => $wpdb->prefix . 'posts',
	'options'          => $wpdb->prefix . 'options',
);
foreach ( $key as $label => $table ) {
	printf( "  %-20s %s\n", $label, (int) $wpdb->get_var( "SELECT COUNT(*) FROM `$table`" ) );
}

// ---------------------------------------------------------------------------
// Imported options point at the live host. Repoint them at the harness and
// keep only WooCommerce active (the other 18 plugins are not in this checkout).
// ---------------------------------------------------------------------------
h_section( 'Post-import fixes' );

update_option( 'siteurl', 'http://localhost:8080' );
update_option( 'home', 'http://localhost:8080' );

$serialize = static function ( array $items ) {
	$out = 'a:' . count( $items ) . ':{';
	foreach ( $items as $i => $v ) {
		$out .= 'i:' . $i . ';s:' . strlen( $v ) . ':"' . $v . '";';
	}
	return $out . '}';
};
$wpdb->update(
	$wpdb->options,
	array( 'option_value' => $serialize( array( 'woocommerce/woocommerce.php' ) ) ),
	array( 'option_name' => 'active_plugins' )
);
printf( "  active_plugins => woocommerce only\n" );
printf( "  siteurl/home   => http://localhost:8080\n" );

echo "\nDone.\n";
