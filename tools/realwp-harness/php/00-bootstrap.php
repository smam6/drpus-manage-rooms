<?php
/**
 * Shared bootstrap. Every harness script is included by run.mjs inside a real
 * PHP request, so this file must fake just enough of a front-end request for
 * WordPress to boot normally.
 */

if ( ! defined( 'HARNESS' ) ) {
	define( 'HARNESS', true );
}

$_SERVER['HTTP_HOST']       = 'localhost:8080';
$_SERVER['SERVER_NAME']     = 'localhost';
$_SERVER['SERVER_PORT']     = '8080';
$_SERVER['REQUEST_URI']     = '/';
$_SERVER['REQUEST_METHOD']  = 'GET';
$_SERVER['SCRIPT_NAME']     = '/index.php';
$_SERVER['PHP_SELF']        = '/index.php';
$_SERVER['REMOTE_ADDR']     = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'drplus-harness';

if ( ! isset( $GLOBALS['wpdb'] ) ) {
	require_once '/wordpress/wp-load.php';
}

/**
 * Tiny assertion helper shared by every harness script.
 */
function h_assert( $label, $condition, $detail = '' ) {
	$GLOBALS['HARNESS_TOTAL'] = ( $GLOBALS['HARNESS_TOTAL'] ?? 0 ) + 1;
	if ( $condition ) {
		$GLOBALS['HARNESS_PASS'] = ( $GLOBALS['HARNESS_PASS'] ?? 0 ) + 1;
		printf( "  \u{2713} %s%s\n", $label, '' !== $detail ? "  [$detail]" : '' );
		return true;
	}
	$GLOBALS['HARNESS_FAIL'] = ( $GLOBALS['HARNESS_FAIL'] ?? 0 ) + 1;
	printf( "  \u{2717} %s%s\n", $label, '' !== $detail ? "  [$detail]" : '' );
	return false;
}

function h_section( $title ) {
	printf( "\n=== %s ===\n", $title );
}

function h_summary() {
	$total = $GLOBALS['HARNESS_TOTAL'] ?? 0;
	$pass  = $GLOBALS['HARNESS_PASS'] ?? 0;
	$fail  = $GLOBALS['HARNESS_FAIL'] ?? 0;
	printf( "\nRESULT: %d passed, %d failed, %d total\n", $pass, $fail, $total );
	return 0 === $fail;
}
