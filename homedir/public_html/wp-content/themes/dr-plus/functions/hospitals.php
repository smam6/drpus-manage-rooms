<?php
use DrPlus\Utils\Search;

if( !function_exists( "drplus_hospitals_city_filter" ) ) {
	function drplus_hospitals_city_filter( $query ) {
		if( is_admin() || !$query->is_main_query() ) return;
		$post_type = $query->get( 'post_type' );
		$post_types = is_array( $post_type ) ? $post_type : ( $post_type ? [ $post_type ] : [] );
		$is_hospital_query = in_array( 'hospital', $post_types, true )
			|| $query->is_post_type_archive( 'hospital' )
			|| $query->is_tax( 'hospital_category' );
		if( !$is_hospital_query ) return;
		$city = Search::get_city_from_GET( "term_id" );
		if( !$city ) return;

		// Tax query for location taxonomy
		$tax_query = $query->get( 'tax_query' );
		if( !is_array( $tax_query ) ) {
			$tax_query = [];
		}

		$tax_query[] = [
			'taxonomy' => 'location',
			'field'    => 'term_id',
			'terms'    => [ absint( $city ) ],
		];

		$query->set( 'tax_query', $tax_query );
	}
}
add_action( 'pre_get_posts', 'drplus_hospitals_city_filter' );

// Backward compatibility: Redirect to new hospital category slug
if( !function_exists( 'drplus_redirect_hospital_category_template' ) ) {
	function drplus_redirect_hospital_category_template() {
		$request_uri = $_SERVER['REQUEST_URI'];

		if ( strpos( $request_uri, "/hospital_category/" ) !== false ) {
			$redirect_to = str_replace( "/hospital_category/", "/hospital-category/", $request_uri );
			wp_redirect( $redirect_to, 301 );
			exit;
		}
	}
}
add_action( 'parse_request', 'drplus_redirect_hospital_category_template' );
