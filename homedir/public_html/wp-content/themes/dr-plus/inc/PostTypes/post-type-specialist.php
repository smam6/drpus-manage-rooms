<?php
namespace DrPlus\PostTypes;

use DrPlus\Model\Specialists;
use DrPlus\Utils\Options;

if( !defined( 'ABSPATH' ) ) exit;

class Specialist {
	public static function register() {
		$labels = [
			'name'					=> __( 'Specialists', 'drplus' ),
			'singular_name'			=> __( 'Specialist', 'drplus' ),
			'menu_name'				=> __( 'Specialists', 'drplus' ),
			'name_admin_bar'		=> __( 'Specialists', 'drplus' ),
			'add_new'				=> __( 'Add New specialist', 'drplus' ),
			'add_new_item'			=> __( 'Add New specialist', 'drplus' ),
			'new_item'				=> __( 'New specialist', 'drplus' ),
			'edit_item'				=> __( 'Edit specialist', 'drplus' ),
			'view_item'				=> __( 'View specialist', 'drplus' ),
			'view_items'			=> __( 'View specialists', 'drplus' ),
			'all_items'				=> __( 'All specialists', 'drplus' ),
			'search_items'			=> __( 'Search specialists', 'drplus' ),
			'not_found'				=> __( 'No specialist found', 'drplus' ),
			'not_found_in_trash'	=> __( 'No specialist found', 'drplus' ),
		];
		$args = [
			'labels'				=> $labels,
			'public'				=> true,
			'show_ui'				=> true,
			'show_in_menu'			=> true,
			'show_in_rest'			=> true,
			'show_in_nav_menus'		=> true,
			'show_in_admin_bar'		=> true,
			'exclude_from_search'	=> true,
			'publicly_queryable'	=> true,
			'menu_icon'				=> 'data:image/svg+xml;base64,' . base64_encode( file_get_contents( DRPLUS_DIR . 'assets/images/specialists-icon.svg' ) ),
			'capability_type'		=> 'page',
			'has_archive'			=> DRPLUS_SINGLE_SPECIALIST_MODE ? false : get_option( 'drplus_specialists', 'specialists' ),
			'hierarchical'			=> false,
			'supports'				=> ['title', 'comments', 'editor', 'thumbnail'],
			'rewrite'				=> [
				'slug'			=> get_option( 'drplus_specialist', 'specialist' ),
				'with_front'	=> false,
			]
		];
		register_post_type( 'specialist', $args );
	}

	public static function pending_bubble() {
		$count = Specialists::query()->select( 'COUNT( `id` ) AS counts' )->where( 'status', 'pending' )->first();
		if( !empty( $count ) )  {
			$count = $count->counts;
		} else {
			$count = 0;
		}

		if( !$count ) return;

		$target = 'edit.php?post_type=specialist';

		global $menu;
		foreach( $menu as $index => $item ) {
			if ( isset($item[2]) && $item[2] === $target ) {
				$badge = '<span class="update-plugins count-' . $count . '"><span class="plugin-count">' . $count . '</span></span>';
				$menu[$index][0] .= ' ' . $badge;
				break;
			}
		}
	}

	public static function enqueue() {
		$screen = get_current_screen();
		if( $screen->base == 'post' && $screen->post_type == 'specialist' ) {
			wp_enqueue_style( 'drplus-specialist-post-type', DRPLUS_URI . "assets/css/backend/specialists/post-type.min.css", [], DRPLUS_VERSION );
		}
	}
}
add_action( 'init', [Specialist::class, 'register'] );
add_action( 'admin_menu', [Specialist::class, 'pending_bubble'] );
add_action( 'admin_enqueue_scripts', [Specialist::class, 'enqueue'] );