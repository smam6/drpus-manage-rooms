<?php
/**
 * Dr Plus Child Theme — Room Capacity Management
 *
 * Implements: spec 8.2, 8.8, 5.5.1, 5.5.2, 5.5.3, 5.5.4, 5.5.5, 5.5.6, 5.5.7, 5.5.8, 5.5.9, 5.5.10
 * Category: 8 — Deployment & compatibility (module loader skeleton; hooks wired in final commit)
 *
 * @package DrPlusChild\Capacity
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Module constants. The default capacity value 2 lives ONLY here (spec 5.6.63).
if ( ! defined( 'DRPLUS_ROOM_CAPACITY_DEFAULT' ) ) {
	define( 'DRPLUS_ROOM_CAPACITY_DEFAULT', 2 );
}

if ( ! defined( 'DRPLUS_CAPACITY_DB_VERSION' ) ) {
	define( 'DRPLUS_CAPACITY_DB_VERSION', '1.0.0' );
}

/**
 * Load a capacity module class file from inc/capacity/.
 *
 * Implements: spec 5.5.27, 8.2
 *
 * @param string $class_file File name without extension, e.g. 'class-capacity-repository'.
 * @return void
 */
function drplus_child_capacity_require( $class_file ) {
	$path = trailingslashit( get_stylesheet_directory() ) . 'inc/capacity/' . $class_file . '.php';
	if ( file_exists( $path ) ) {
		require_once $path;
	}
}

// Class files are loaded here; hook wiring happens in the final bootstrap commit (spec 8.2).
drplus_child_capacity_require( 'class-capacity-installer' );
drplus_child_capacity_require( 'class-capacity-repository' );
drplus_child_capacity_require( 'class-capacity-service' );
drplus_child_capacity_require( 'class-capacity-logger' );
drplus_child_capacity_require( 'class-capacity-validator' );
drplus_child_capacity_require( 'class-capacity-admin' );
drplus_child_capacity_require( 'class-capacity-messages' );
