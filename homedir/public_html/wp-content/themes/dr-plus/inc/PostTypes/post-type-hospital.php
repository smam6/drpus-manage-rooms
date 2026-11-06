<?php
namespace DrPlus\PostTypes;

if( !defined( 'ABSPATH' ) ) exit;

class Hospital {
	PRIVATE STATIC $POST_TYPE_NAME = 'hospital'; // Singular word

	public static function register() {
		$labels = [
			'name'					=> __( 'Hospitals', 'drplus' ),
			'singular_name'			=> __( 'Hospital', 'drplus' ),
			'menu_name'				=> __( 'Hospitals', 'drplus' ),
			'name_admin_bar'		=> __( 'Hospitals', 'drplus' ),
			'add_new'				=> __( 'Add New hospital', 'drplus' ),
			'add_new_item'			=> __( 'Add New hospital', 'drplus' ),
			'new_item'				=> __( 'New hospital', 'drplus' ),
			'edit_item'				=> __( 'Edit hospital', 'drplus' ),
			'all_items'				=> __( 'All hospitals', 'drplus' ),
			'search_items'			=> __( 'Search hospitals', 'drplus' ),
			'not_found'				=> __( 'No hospital found', 'drplus' ),
			'not_found_in_trash'	=> __( 'No hospital found', 'drplus' ),
		];
		$args = [
			'labels'			=> $labels,
			'public'			=> true,
			'show_ui'			=> true,
			'show_in_menu'		=> true,
			'show_in_rest'		=> true,
			'menu_icon'			=> 'data:image/svg+xml;base64,' . base64_encode( file_get_contents( DRPLUS_DIR . 'assets/images/hospital-icon.svg' ) ),
			'capability_type'	=> 'post',
			'has_archive'		=> get_option( 'drplus_hospitals', 'hospitals' ),
			'hierarchical'		=> false,
			'supports'			=> ['title', 'editor', 'thumbnail', 'comments'],
			'rewrite'			=> [
				'slug'			=> get_option( 'drplus_hospital', 'hospital' ),
				'with_front'	=> false,
			]
		];
		register_post_type( self::$POST_TYPE_NAME, $args );
	}
}
Hospital::register();