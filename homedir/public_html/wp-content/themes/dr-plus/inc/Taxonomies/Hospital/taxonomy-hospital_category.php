<?php
namespace DrPlus\Backend\Taxonomies\Hospital;

if( !defined( 'ABSPATH' ) ) exit;

class Category {
	public static function add() {

		$args = [
			'publicly_queryable'	=> true,
			'show_ui'				=> true,
			'show_in_menu'			=> true,
			'show_in_nav_menus'		=> true,
			'show_in_rest'			=> true,
			'show_in_quick_edit'	=> true,
			'hierarchical'			=> true,
			'rewrite'			=> [
				'slug'			=> get_option( 'drplus_hospital-category', 'hospital-category' ),
				'with_front'	=> false,
				'hierarchical'	=> true
			]
		];

		register_taxonomy( 'hospital_category', 'hospital', $args );
	}
}
Category::add();