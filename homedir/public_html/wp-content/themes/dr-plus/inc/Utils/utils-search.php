<?php
namespace DrPlus\Utils;

use DrPlus\Model\Specialists;
use DrPlus\Utils;

class Search extends Utils {
	public static function get_post_types_object() {
		$post_types = get_post_types( [
			'public'				=> true,
			'exclude_from_search'	=> false,
		], 'objects' );

		return $post_types;
	}
	/**
	 * Use this function only when is_search is true
	 *
	 * @return array
	 */
	public static function get_categorized_results() : array {
		static $categorized_results = null;
		if( $categorized_results === null ) {
			$section = self::get_post_type();
			$categorized_results = [];
			if( is_search() ) {
				$search_term = get_search_query();

				$options = Options::get_options( [
					'search_specialist'			=> true,
					'search_specialist_icon'	=> 'drplus-icon-stethoscope',
					'search_specialist_title'	=> __( 'Specialists', 'drplus' ),
				] );

				if( DRPLUS_SINGLE_SPECIALIST_MODE ) $options['search_specialist'] = false;

				$total_specialists = 0;
				if( parent::to_bool( $options['search_specialist'] ) ) {
					if( !$section ) {
						$specialists_args = [
							'number'		=> 8,
							'count_total'	=> true,
							'fields'		=> 'ids',
						];
					} else if( $section == 'specialist' ) {
						$specialists_args = [
							'number'		=> get_option( 'posts_per_page', 8 ),
							'paged'			=> Archive::get_paged(),
							'count_total'	=> true,
							'fields'		=> 'ids',
						];
					}
					if( !empty( $specialists_args ) ) {
						if( $section == 'specialist' ) {
							global $wp_query;
							$specialists = UtilsSpecialists::search( $wp_query, $specialists_args, 'query' );
						} else {
							$specialists = UtilsSpecialists::search( $search_term, $specialists_args, 'query' );
						}
						$total_specialists = $specialists->found_posts ?? 0;
					}
				}

				if( have_posts() || $total_specialists ) {
					// Convert founded users to specialist
					if( $total_specialists ) {
						$categorized_results['specialist'] = [
							'icon'		=> $options['search_specialist_icon'],
							'label'		=> $options['search_specialist_title'],
							'count'		=> $total_specialists,
							'results'	=> [],
						];
						$specialist_posts_ids = is_array( $specialists->posts ) && !empty( $specialists->posts[0] ) && is_object( $specialists->posts[0] ) ? wp_list_pluck( (array) $specialists->posts, 'ID' ) : $specialists->posts;
						$specialist_posts_ids = array_map( 'absint', $specialist_posts_ids );
						if( !empty( $specialists->drplus_specialists ) ) {
							$categorized_results['specialist']['results'] = $specialists->drplus_specialists;
						} else if( !empty( $specialist_posts_ids ) ) {
							$query = Specialists::query()->whereIn( 'post_id', $specialist_posts_ids );
							if( !empty( $specialist_posts_ids ) ) {
								$query->orderByRaw( 'FIELD(post_id, ' . implode( ',', $specialist_posts_ids ) . ')' );
							}
							$categorized_results['specialist']['results'] = $query->get();
						}
					}

					$post_types = self::get_post_types_object();

					// Get other post types posts
					$exclude_search_post_types = Options::get_options( [
						'exclude_post_types'	=> ['page', 'attachment', 'e-floating-buttons'],
					] )['exclude_post_types'] ?? [];
					$default_section_icons = [
						'speciality'	=> 'drplus-icon-plus',
						'product'		=> 'drplus-icon-shopping-cart',
						'hospital'		=> 'drplus-icon-hospital-pin',
						'post'			=> 'drplus-icon-diamond',
					];
					foreach( $post_types as $post_type => $post_type_object ) {
						if( !isset( $categorized_results[$post_type] ) && !in_array( $post_type, $exclude_search_post_types ) ) {
							$post_type_options = Options::get_options( [
								"search_{$post_type}_icon"	=> $default_section_icons[$post_type] ?? 'drplus-icon-grid-fill',
							] );

							$categorized_results[$post_type] = [
								'icon'		=> $post_type_options["search_{$post_type}_icon"],
								'label'		=> $post_type_object->labels->name,
								'count'		=> 0,
								'results'	=> [],
							];
							$query = new \WP_Query( [
								'post_type'				=> $post_type,
								's'						=> $search_term,
								'posts_per_page'		=> 8,
								'ignore_sticky_posts'	=> true,
							] );
							$categorized_results[$post_type]['count'] = $query->found_posts;
							if( !$categorized_results[$post_type]['count'] ) {
								unset( $categorized_results[$post_type] );
								continue;
							}
							while( $query->have_posts() ) {
								$query->the_post();
								$categorized_results[$post_type]['results'][] = get_post();
							}
							wp_reset_postdata();
						}
					}

					// Reposition founded items
					// First place for specialists
					if( !empty( $categorized_results['speciality'] ) ) {
						parent::reposition_array_element( $categorized_results, 'speciality', 1 ); // Move the specialities
					}
					if( !empty( $categorized_results['hospital'] ) ) {
						parent::reposition_array_element( $categorized_results, 'hospital', 2 ); // Move the hospitals
					}
					if( !empty( $categorized_results['product'] ) ) {
						parent::reposition_array_element( $categorized_results, 'product', 3 ); // Move the products
					}
					if( !empty( $categorized_results['post'] ) ) {
						parent::reposition_array_element( $categorized_results, 'post', 99 ); // Move the posts
					}
				}
			}
		}
		return $categorized_results;
	}

	/**
	 * Use this function only when is_search is true. Return the current post type
	 *
	 * @return string
	 */
	public static function get_post_type() : string {
		if( empty( $_GET['post_type'] ) && empty( $_GET['section'] ) ) {
			return '';
		} else {
			if( !empty( $_GET['post_type'] ) ) {
				$post_type = parent::convert_chars( $_GET['post_type'] );
			} else {
				$post_type = parent::convert_chars( $_GET['section'] );
			}
			return $post_type;
		}
	}

	/**
	 * Get city from GET parameter
	 *
	 * @param  string $return term | term_id
	 * @return mixed 
	 */
	public static function get_city_from_GET( string $return = "term" ) {
		if( empty( $_GET['city'] ) || empty( parent::convert_chars( $_GET['city'], 'sanitize_title_with_dashes' ) ) ) return '';
		
		$city = parent::convert_chars( $_GET['city'], 'sanitize_title_with_dashes' );
		$city_is_location_term_slug = false;
		$city_term = get_term_by( 'slug', $city, 'location' );
		if( $city_term && !is_wp_error( $city_term ) ) {
			$city_is_location_term_slug = true;
			if( $return == 'term' ) {
				return $city_term;
			}
		}
		if( !$city || !$city_is_location_term_slug ) return '';

		if( $return == 'term_id' ) {
			$city = $city_term->term_id;
		}
		return $city;
	}
}
