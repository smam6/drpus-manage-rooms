<?php
namespace DrPlus\Backend\Taxonomies;

if( !defined( 'ABSPATH' ) ) exit;

class Locations {
	public static function add() {
		$labels = [
			'name'			=> __( 'Locations', 'drplus' ),
			'singular_name'	=> __( 'Location', 'drplus' ),
			'search_items'	=> __( 'Search locations', 'drplus' ),
			'all_items'		=> __( 'All locations', 'drplus' ),
			'edit_item'		=> __( 'Edit location', 'drplus' ),
			'update_item'	=> __( 'Update location', 'drplus' ),
			'add_new_item'	=> __( 'Add New location', 'drplus' ),
			'new_item_name'	=> __( 'New location Name', 'drplus' ),
			'menu_name'		=> __( 'Locations', 'drplus' ),
			'not_found'		=> __( 'No location found.', 'drplus' ),
		];
		$args = [
			'labels'				=> $labels,
			'public'				=> true,
			'publicly_queryable'	=> true,
			'show_in_rest'			=> true,
			'show_ui'				=> true,
			'show_in_menu'			=> true,
			'show_in_nav_menus'		=> true,
			'show_in_quick_edit'	=> false,
			'hierarchical'			=> true,
			'rewrite'			=> [
				'slug'			=> 'location',
				'with_front'	=> false,
				'hierarchical'	=> true
			]
		];

		register_taxonomy( 'location', ['specialist', 'hospital'], $args );
	}
}
Locations::add();