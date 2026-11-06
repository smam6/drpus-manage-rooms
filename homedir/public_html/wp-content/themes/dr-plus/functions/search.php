<?php

use DrPlus\Components\ProIcon;
use DrPlus\Utils\Search;
use DrPlus\Utils\Speciality;

if( !function_exists( 'drplus_exclude_search_post_types' ) ) {
	function drplus_exclude_search_post_types( $query ) {
		if( is_admin() && !wp_doing_ajax() ) return;
		if( empty( $GLOBALS['drplus'] ) ) return;

		$options = $GLOBALS['drplus'];
		if( ( $query->is_main_query() && $query->is_search() ) ) {
			$post_types = get_post_types( ['exclude_from_search' => false] );
			$excludes = !empty( $options['exclude_post_types'] ) ? $options['exclude_post_types'] : [];
			$post_types = array_diff( array_keys( $post_types ), $excludes );
			$post_types = array_values( $post_types );
			$query->set( 'post_type', $post_types );
		}
	}
}
add_action( 'pre_get_posts', 'drplus_exclude_search_post_types' );

if( !function_exists( "drplus_modify_search_query" ) ) {
	function drplus_modify_search_query( $query ) {
		if( is_admin() || !$query->is_search() || !$query->is_main_query() ) return;

		$post_type = Search::get_post_type();

		if( $post_type ) {
			$query->set( 'post_type', $post_type );
			$query->set( 'posts_per_page', get_option( 'posts_per_page' ) );
			$query->set( 'ignore_sticky_posts', true );
			$query->set( 'nopaging', false );
		}
	}
}
add_action( 'pre_get_posts', 'drplus_modify_search_query' );

if ( ! function_exists( 'drplus_filter_search_results_by_city' ) ) {
	function drplus_filter_search_results_by_city( $query ) {
		if ( is_admin() || ! $query->is_search() || ! $query->is_main_query() ) {
			return;
		}
		
		$city = Search::get_city_from_GET( 'term_id' );
		if ( ! $city ) {
			return;
		}

		$city_term_id = absint( $city );

		$tax_query = $query->get( 'tax_query' );

		if ( ! is_array( $tax_query ) ) {
			$tax_query = [];
		}
		
		if ( empty( $tax_query['relation'] ) ) {
			$tax_query['relation'] = 'AND';
		}

		$tax_query[] = [
			'taxonomy' => 'location',
			'field'    => 'term_id',
			'terms'    => [ $city_term_id ],
			'operator' => 'IN',
		];

		$query->set( 'tax_query', $tax_query );
	}
}
add_action( 'pre_get_posts', 'drplus_filter_search_results_by_city' );

if( !function_exists( "drplus_search_main_speciality" ) ) {
	function drplus_search_main_speciality() {
		$speciality_options = Speciality::get_options( get_the_ID() );
		ProIcon::view( [
			'icon_type'	=> 'icon',
			'icon'		=> $speciality_options['icon'],
			'title'		=> get_the_title(),
			'subtitle'	=> sprintf( esc_html__( "%d specialists", 'drplus' ), Speciality::count_specialists( get_the_ID() ) ),
			'link'		=> [
				'url'	=> Speciality::get_archive_link( get_post() )
			],
			'is_slider'	=> true,
		] );
		return true;
	}
}
add_filter( 'drplus/search/main/speciality', 'drplus_search_main_speciality' );

if( !function_exists( "drplus_search_main_hospital" ) ) {
	function drplus_search_main_hospital() {
		get_template_part( "templates/archives/template-archives-hospital" );
		return true;
	}
}
add_filter( 'drplus/search/main/hospital', 'drplus_search_main_hospital' );

if( !function_exists( "drplus_search_main_hospital" ) ) {
	function drplus_search_main_hospital() {
		get_template_part( "templates/archives/template-archives-hospital" );
		return true;
	}
}
add_filter( 'drplus/search/main/hospital', 'drplus_search_main_hospital' );
add_filter( 'drplus/search/results/hospital', 'drplus_search_main_hospital' );

if( !function_exists( "drplus_search_results_speciality" ) ) {
	function drplus_search_results_speciality() {
		$speciality_options = Speciality::get_options( get_the_ID() );
		ProIcon::view( [
			'icon_type'	=> 'icon',
			'icon'		=> $speciality_options['icon'],
			'title'		=> get_the_title(),
			'subtitle'	=> sprintf( esc_html__( "%d specialists", 'drplus' ), Speciality::count_specialists( get_the_ID() ) ),
			'link'		=> [
				'url'	=> Speciality::get_archive_link( get_post() )
			],
		] );
		return true;
	}
}
add_filter( 'drplus/search/results/speciality', 'drplus_search_results_speciality' );