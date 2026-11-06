<?php
namespace DrPlus\PostTypes;

if( !defined( 'ABSPATH' ) ) exit;

class Speciality {
	PRIVATE STATIC $POST_TYPE_NAME = 'speciality'; // Singular word

	public static function register() {
		$labels = [
			'name'					=> __( 'Specialities', 'drplus' ),
			'singular_name'			=> __( 'Speciality', 'drplus' ),
			'menu_name'				=> __( 'Specialities', 'drplus' ),
			'name_admin_bar'		=> __( 'Specialities', 'drplus' ),
			'add_new'				=> __( 'Add New speciality', 'drplus' ),
			'add_new_item'			=> __( 'Add New speciality', 'drplus' ),
			'new_item'				=> __( 'New speciality', 'drplus' ),
			'edit_item'				=> __( 'Edit speciality', 'drplus' ),
			'all_items'				=> __( 'All specialities', 'drplus' ),
			'search_items'			=> __( 'Search specialities', 'drplus' ),
			'not_found'				=> __( 'No speciality found', 'drplus' ),
			'not_found_in_trash'	=> __( 'No speciality found', 'drplus' ),
		];
		$args = [
			'labels'			=> $labels,
			'public'			=> true,
			'show_ui'			=> true,
			'show_in_menu'		=> true,
			'show_in_rest'		=> true,
			'menu_icon'			=> 'data:image/svg+xml;base64,' . base64_encode( file_get_contents( DRPLUS_DIR . 'assets/images/speciality-icon.svg' ) ),
			'capability_type'	=> 'post',
 			'has_archive'		=> get_option( 'drplus_specialities', 'specialities' ),
 			'hierarchical'		=> true,
 			'supports'			=> ['title', 'editor', 'thumbnail', 'page-attributes'],
 			'rewrite'			=> [
 				'slug'			=> get_option( 'drplus_speciality', 'speciality' ),
				'with_front'	=> false,
 			]
		];
		register_post_type( self::$POST_TYPE_NAME, $args );
	}
}
Speciality::register();