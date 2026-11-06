<?php
namespace DrPlus\Utils;

use DrPlus\Model\Specialists;
use DrPlus\Model\SpecialistSpecialitiesRel;
use DrPlus\Utils;
use DrPlus\Utils\Search;
use MJ\Whitebox\Utils\Posts as WhiteboxPosts;

class Speciality extends Utils {
	private static $options = [];
	private static $count_specialists = [];

	public static function default_options() {
		return [
			'subtitle'	=> '',
			'icon'		=> '',
		];
	}

	public static function get_options( $post_id = null ) {
		$post_id = WhiteboxPosts::get_post_id( $post_id );
		if( !isset( self::$options[$post_id] ) || !is_array( self::$options[$post_id] ) ) {
			self::$options[$post_id] = WhiteboxPosts::get_post_options( self::default_options(), $post_id );
		}
		return self::$options[$post_id];
	}

	public static function save_options( array $options, $post_id = null ) {
		WhiteboxPosts::save_post_options( $options, self::default_options(), $post_id );
		if( !isset( self::$options[$post_id] ) || !is_array( self::$options[$post_id] ) ) {
			self::$options[$post_id] = [];
		}
		self::$options[$post_id] = array_merge( self::$options[$post_id], $options );
	}

	/**
	 * Count specialists by speciality id
	 *
	 * @param integer $post_id Speciality post id
	 * @return integer
	 */
	public static function count_specialists( $post_id = null, $location_term_id = null ) : int {
		$post_id = parent::get_post_id( $post_id );
		$location_term_id = self::resolve_location_term_id( $location_term_id );

		$location_cache_key = $location_term_id ?: 0;
		if( empty( self::$count_specialists[$post_id][$location_cache_key] ) ) {
			global $wpdb;

			$specialists_table = Specialists::tableName();
			$specialities_table = SpecialistSpecialitiesRel::tableName();
			$query = SpecialistSpecialitiesRel::query()
				->select( "COUNT(`{$specialities_table}`.`id`) AS counts" )
				->leftJoin( $specialists_table, "{$specialists_table}.user_id", '=', "{$specialities_table}.user_id" )
				->where( [
					"{$specialists_table}.status"			=> 'active',
					"{$specialities_table}.speciality_id"	=> $post_id,
				] );

			if( $location_term_id ) {
				$term = get_term( $location_term_id, 'location' );
				if( $term && !is_wp_error( $term ) ) {
					$term_relationships_table = $wpdb->term_relationships;
					$query->leftJoin( $term_relationships_table, "{$term_relationships_table}.object_id", '=', "{$specialists_table}.post_id" )
						->where( "{$term_relationships_table}.term_taxonomy_id", absint( $term->term_taxonomy_id ) );
				}
			}

			$result = $query->first();
			self::$count_specialists[$post_id][$location_cache_key] = (int) ( $result->counts ?? 0 );
		}
		return self::$count_specialists[$post_id][$location_cache_key];
	}

	private static function resolve_location_term_id( $location_term_id = null ) : int {
		if( $location_term_id !== null ) {
			return absint( $location_term_id );
		}

		$location_term_id = Search::get_city_from_GET( "term_id" );
		if( !$location_term_id && is_tax( 'location' ) ) {
			$term = get_queried_object();
			$location_term_id = $term && !is_wp_error( $term ) ? $term->term_id : 0;
		}

		return absint( $location_term_id );
	}

	public static function get_archive_link( $post_id = null ) : string {
		return get_permalink( parent::get_post_id( $post_id ) );
	}

	public static function all( array $args = [], bool $id_indexed = false, bool $include_options = false ) : array {
		$args = parent::check_default( $args, [
			'numberposts'			=> -1,
			'ignore_sticky_posts'	=> true,
		]);
		$args['post_type'] = 'speciality';

		if( $args['numberposts'] === -1 ) {
			$args['nopaging'] = true;
		}

		$specialities = get_posts( $args );
		if( $id_indexed ) {
			foreach( $specialities as $index => $speciality ) {
				if( $include_options ) {
					$speciality->options = self::get_options( $speciality->ID );
				}
				$specialities[$speciality->ID] = $speciality;
				unset( $specialities[$index] );
			}
		}

		return $specialities;
	}

	public static function get_specialities_from_GET() : array {
		$specialities = !empty( $_GET['specialities'] ) ? $_GET['specialities'] : [];
		if( empty( $specialities ) ) return [];
		$specialities = is_array( $specialities ) ? $specialities : [$specialities];
		$specialities = array_filter( array_map( fn( $speciality ) => Utils::convert_chars( $speciality, true, 'absint' ), $specialities ) );
		return $specialities;
	}
}
