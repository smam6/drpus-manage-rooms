<?php
namespace DrPlus\Utils;

use DrPlus\Model\SpecialistHospitalsRel;
use DrPlus\Model\Specialists;
use DrPlus\Utils;

class Location extends Utils {
	public static function locations( $parent = null, $only_id = false, $additional_args = [], $only_cities = false ) {
		$args = [
			'taxonomy'		=> 'location',
			'hide_empty'	=> false,
		];
		if( $only_id ) {
			$args['fields'] = 'ids';
		}
		if( $parent !== null ) {
			$args['parent'] = $parent;
		}
		$args = array_merge( $args, $additional_args );
		$terms = get_terms( $args );

		if( $only_cities && !$only_id ) {
			$cities = [];
			foreach( $terms as $term ) {
				if( !empty( $term->parent ) ) {
					$cities[] = $term;
				}
			}

			if( !empty( $cities ) ) {
				$terms = $cities;
			}
		}

		return $terms;
	}

	public static function get_locations_by_parent( $parent ) {
		return self::locations( $parent );
	}

	/**
	 * Sync specialist location terms with their connected hospitals.
	 *
	 * @param int $hospital_id
	 * @return void
	 */
	public static function sync_specialists_location_terms( $hospital_id ) {
		if( !\taxonomy_exists( 'location' ) ) return;

		$hospital_id = Utils::get_post_id( $hospital_id );
		if( empty( $hospital_id ) ) return;

		$relations = SpecialistHospitalsRel::query()
			->select( ['user_id'] )
			->where( 'hospital_id', $hospital_id )
			->get();

		if( empty( $relations ) ) return;

		$user_ids = [];
		foreach( $relations as $relation ) {
			$user_id = absint( $relation->user_id );
			if( $user_id ) {
				$user_ids[] = $user_id;
			}
		}

		$user_ids = array_values( array_unique( $user_ids ) );
		if( empty( $user_ids ) ) return;

		$specialists = Specialists::query()
			->select( ['user_id', 'post_id', 'offices'] )
			->whereIn( 'user_id', $user_ids )
			->get();

		if( empty( $specialists ) ) return;

		$relations_by_user = SpecialistHospitalsRel::query()
			->select( ['user_id', 'hospital_id'] )
			->whereIn( 'user_id', $user_ids )
			->get();

		$hospitals_by_user = [];
		foreach( $relations_by_user as $relation ) {
			$uid = absint( $relation->user_id );
			$hid = Utils::get_post_id( $relation->hospital_id );

			if( !$uid || !$hid ) continue;

			if( empty( $hospitals_by_user[$uid] ) ) {
				$hospitals_by_user[$uid] = [];
			}

			$hospitals_by_user[$uid][] = $hid;
		}

		if( empty( $hospitals_by_user ) ) return;

		$hospital_locations_cache = [];

		foreach( $specialists as $specialist ) {
			$post_id = absint( $specialist->post_id );
			$user_id = absint( $specialist->user_id );

			if( !$post_id || !$user_id ) continue;

			$offices = self::normalize_offices( $specialist->offices );
			$hospital_ids = $hospitals_by_user[$user_id] ?? [];

			$location_terms = self::collect_hospitals_location_term_ids( $offices, $hospital_ids, $hospital_locations_cache, $post_id );

			\wp_set_object_terms( $post_id, $location_terms, 'location', false );
		}
	}

	/**
	 * Sync location terms for a single specialist (by user id) using offices and hospital relations.
	 *
	 * @param int $user_id
	 * @param array|null $offices Optional offices array to avoid re-fetching.
	 * @return void
	 */
	public static function sync_specialist_location_terms_by_user( $user_id, $offices = null ) {
		if( !\taxonomy_exists( 'location' ) ) return;

		$user_id = Utils::get_user_id( $user_id );
		if( !$user_id ) return;

		$specialist = Specialists::query()
			->select( ['user_id', 'post_id', 'offices'] )
			->where( 'user_id', $user_id )
			->first();

		if( empty( $specialist ) ) return;

		$post_id = absint( $specialist->post_id );
		if( !$post_id ) return;

		$offices = self::normalize_offices( $offices ?? $specialist->offices );

		$relations = SpecialistHospitalsRel::query()
			->select( ['hospital_id'] )
			->where( 'user_id', $user_id )
			->get();

		$hospital_ids = [];
		foreach( $relations as $relation ) {
			$hid = Utils::get_post_id( $relation->hospital_id );
			if( $hid ) {
				$hospital_ids[] = $hid;
			}
		}

		$hospital_locations_cache = [];
		$location_terms = self::collect_hospitals_location_term_ids( $offices, $hospital_ids, $hospital_locations_cache, $post_id );

		\wp_set_object_terms( $post_id, $location_terms, 'location', false );
	}

	/**
	 * Normalize offices data to array.
	 *
	 * @param mixed $offices
	 * @return array
	 */
	private static function normalize_offices( $offices ) : array {
		if( is_array( $offices ) ) {
			return $offices;
		}

		$decoded = json_decode( (string) $offices, true );
		return is_array( $decoded ) ? $decoded : [];
	}

	/**
	 * Collect unique location term ids from hospitals and custom offices.
	 *
	 * @param array $offices
	 * @param array $hospital_ids
	 * @param array $hospital_locations_cache
	 * @return array
	 */
	private static function collect_hospitals_location_term_ids( array $offices, array $hospital_ids, array &$hospital_locations_cache, $post_id ) : array {
		$location_term_ids = [];
		$hospital_ids = array_values( array_unique( array_map( 'absint', $hospital_ids ) ) );

		// Collect hospital ids from offices as well
		foreach( $offices as $office ) {
			if( empty( $office['type'] ) ) continue;
			if( $office['type'] === 'hospital' && !empty( $office['id'] ) ) {
				$hid = Utils::get_post_id( $office['id'] );
				if( $hid ) {
					$hospital_ids[] = $hid;
				}
			}
		}

		$hospital_ids = array_values( array_unique( array_filter( $hospital_ids ) ) );

		foreach( $hospital_ids as $hid ) {
			if( !isset( $hospital_locations_cache[$hid] ) ) {
				$hospital_locations_cache[$hid] = [];

				$hospital_terms = \wp_get_object_terms( $hid, 'location', ['fields' => 'ids'] );
				if( !\is_wp_error( $hospital_terms ) ) {
					$hospital_locations_cache[$hid] = array_map( 'intval', $hospital_terms );
				}
			}

			$location_term_ids = array_merge( $location_term_ids, $hospital_locations_cache[$hid] );
		}

		// Custom offices store province/city term ids directly
		foreach( $offices as $office ) {
			if( empty( $office['type'] ) || $office['type'] !== 'custom' ) continue;

			if( !empty( $office['province'] ) && \is_numeric( $office['province'] ) ) {
				$location_term_ids[] = absint( $office['province'] );
			}
			if( !empty( $office['city'] ) && \is_numeric( $office['city'] ) ) {
				$location_term_ids[] = absint( $office['city'] );
			}
		}

		$location_term_ids = apply_filters( 'drplus/specialist/location_term_ids', $location_term_ids, $post_id );

		return array_values( array_unique( array_filter( array_map( 'intval', $location_term_ids ) ) ) );
	}

	/**
	 * Get location terms that are used by a specific post type.
	 *
	 * @param string $post_type specialist | hospital
	 * @param array  $args      Optional get_terms args
	 *
	 * @return WP_Term[]|int[]|WP_Error
	 */
	public static function get_location_terms_by_post_type( string $post_type, array $args = [], $only_cities = false, $force_update = false ) {
		$cache_key = "drplus_location_terms_{$post_type}";
		if( $only_cities ) {
			$cache_key .= "_cities";
		}
		$terms = wp_cache_get( $cache_key, 'drplus' );

		if ( false !== $terms && !$force_update ) {
			return $terms;
		}

		$args = wp_parse_args( $args, [
			'taxonomy'                	=> 'location',
			'hide_empty'              	=> true,
			'fields'                  	=> 'all',
			'post_status'             	=> ['publish'],
			'drplus_filter_post_type'	=> $post_type
		] );
		
		add_filter( 'terms_clauses', 'drplus_filter_location_terms_by_post_type', 10, 3 );
		$terms = get_terms( $args );
		remove_filter( 'terms_clauses', 'drplus_filter_location_terms_by_post_type', 10 );

		if( $only_cities ) {
			$cities = [];
			foreach( $terms as $term ) {
				if( !empty( $term->parent ) ) {
					$cities[] = $term;
				}
			}

			if( !empty( $cities ) ) {
				$terms = $cities;
			}
		}

		wp_cache_set( $cache_key, $terms, 'drplus', 10 * MINUTE_IN_SECONDS );

		return $terms;
	}
}