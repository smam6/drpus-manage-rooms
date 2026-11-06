<?php
namespace DrPlus\Backend\Taxonomies\Hospital;

use DrPlus\Utils;

if( !defined( 'ABSPATH' ) ) exit;

class Service {
	public static function add() {
		$labels = [
			'name'			=> __( 'Hospital Services', 'drplus' ),
			'singular_name'	=> __( 'Hospital Service', 'drplus' ),
			'search_items'	=> __( 'Search Hospital Services', 'drplus' ),
			'all_items'		=> __( 'All Hospital Services', 'drplus' ),
			'edit_item'		=> __( 'Edit Hospital Service', 'drplus' ),
			'update_item'	=> __( 'Update Hospital Service', 'drplus' ),
			'add_new_item'	=> __( 'Add New Hospital Service', 'drplus' ),
			'new_item_name'	=> __( 'New Hospital Service Name', 'drplus' ),
			'menu_name'		=> __( 'Hospital Services', 'drplus' ),
		];
		$args = [
			'labels'				=> $labels,
			'publicly_queryable'	=> true,
			'show_ui'				=> true,
			'show_in_menu'			=> true,
			'show_in_nav_menus'		=> true,
			'show_in_rest'			=> true,
			'show_in_quick_edit'	=> true,
			'hierarchical'			=> true,
			'rewrite'			=> [
				'slug'			=> 'hospital-service',
				'with_front'	=> false,
				'hierarchical'	=> false
			]
		];

		register_taxonomy( 'hospital-service', 'hospital', $args );
	}

	public static function columns( $columns ) {
		$columns = Utils::unset( $columns, ['slug'] );
		return $columns;
	}
}
Service::add();
add_filter( 'manage_edit-hospital-service_columns', [Service::class, 'columns'] );