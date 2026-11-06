<?php
/**************************************************************************
***************************************************************************
***************************************************************************
***************************************************************************
⛔ DON'T EDIT THIS FILE ⛔
***************************************************************************
***************************************************************************
***************************************************************************
***************************************************************************/

if( !defined( 'ABSPATH' ) ) exit;

// Define constants
if( !defined( 'DRPLUS_CHILD_DIR' ) ) {
	define( 'DRPLUS_CHILD_DIR', trailingslashit( get_stylesheet_directory() ) );
}

if( !defined( 'DRPLUS_CHILD_URI' ) ) {
	define( 'DRPLUS_CHILD_URI', trailingslashit( get_stylesheet_directory_uri() ) );
}

if( !defined( 'DRPLUS_CHILD_VERSION' ) ) {
	define( 'DRPLUS_CHILD_VERSION', "1.0.0.0" );
}

if( !defined( 'DRPLUS_CHILD_DEV' ) ) {
	define( 'DRPLUS_CHILD_DEV', true );
}

if( !function_exists( "drplus_child_init" ) ) {
	function drplus_child_init() {
		load_theme_textdomain( 'drplus',  trailingslashit( get_template_directory() ) . 'languages' );
	}
}
add_action( 'init', 'drplus_child_init' );

if( !function_exists( "drplus_child_enqueue_styles" ) ) {
	function drplus_child_enqueue_styles() {
		include( trailingslashit( get_template_directory() ) . "inc/Scripts.php" );
		wp_enqueue_style( 'drplus-parent-rtl', trailingslashit( get_template_directory_uri() ) . 'rtl.css' );
		wp_enqueue_style( 'drplus-child-style', DRPLUS_CHILD_URI . 'style.css', [], DRPLUS_CHILD_VERSION );
	}
}
add_action( 'wp_enqueue_scripts', 'drplus_child_enqueue_styles' );
/***************************************************************************/