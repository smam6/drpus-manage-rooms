<?php

if( !defined( 'ABSPATH' ) ) exit;

// Define constants
if( !defined( 'DRPLUS_DIR' ) ) {
	define( 'DRPLUS_DIR', trailingslashit( get_template_directory() ) );
}

if( !defined( 'DRPLUS_URI' ) ) {
	define( 'DRPLUS_URI', trailingslashit( get_template_directory_uri() ) );
}

if( !defined( 'DRPLUS_VERSION' ) ) {
	define( 'DRPLUS_VERSION', "2.8.0.1" );
}

if( !defined( 'DRPLUS_DEV' ) ) {
	define( 'DRPLUS_DEV', false );
}

if( !defined( 'DRPLUS_IS_LOCAL' ) ) {
	define( 'DRPLUS_IS_LOCAL', !empty( $_SERVER['SERVER_NAME'] ) && $_SERVER['SERVER_NAME'] == 'localhost' );
}

include( DRPLUS_DIR . "functions/init.php" );
include( DRPLUS_DIR . "functions/widgets.php" );
include( DRPLUS_DIR . "functions/elementor.php" );
include( DRPLUS_DIR . "functions/comments.php" );
include( DRPLUS_DIR . "functions/archive.php" );
include( DRPLUS_DIR . "functions/search.php" );
include( DRPLUS_DIR . "functions/hospitals.php" );
include( DRPLUS_DIR . "functions/users.php" );
include( DRPLUS_DIR . "functions/auth.php" );
include( DRPLUS_DIR . "functions/onboard.php" );
include( DRPLUS_DIR . "functions/specialists.php" );
include( DRPLUS_DIR . "functions/booking.php" );
include( DRPLUS_DIR . "functions/singles.php" );
include( DRPLUS_DIR . "functions/cache.php" );
include( DRPLUS_DIR . "functions/chat.php" );
include( DRPLUS_DIR . "functions/wallet.php" );
include( DRPLUS_DIR . "functions/theme.php" );