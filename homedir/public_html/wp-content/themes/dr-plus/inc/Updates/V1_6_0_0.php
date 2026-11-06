<?php
namespace DrPlus\Updates;

use MJ\IR;

class V1_6_0_0 {
	public static $run_at_install = false;
	
	private const LOCATION_SYNC_OPTION = 'drplus_location_taxonomy_synced';

	public static function update() {
		self::sync_specialist_with_post();
		self::register_location_sync_hook();

		flush_rewrite_rules();
	}

	private static function sync_specialist_with_post() {
		global $wpdb;

		// Start transaction
		$wpdb->query( "START TRANSACTION;" );

		try {
			$table = "{$wpdb->prefix}drplus_specialists";
			$specialists = $wpdb->get_results(
				"SELECT `post_id`, `user_id`, `about` FROM `{$table}` WHERE `post_id` IS NOT NULL AND `post_id` != 0"
			);

			if( empty( $specialists ) ) return;

			foreach( $specialists as $specialist ) {
				$post_id = absint( $specialist->post_id );
				if( !$post_id || empty( $specialist->about ) ) continue;

				$post = \get_post( $post_id );
				if( empty( $post ) ) continue;

				// Move the about column content into the related post content.
				\wp_update_post(
					\wp_slash( [
						'ID'           => $post_id,
						'post_content' => $specialist->about,
					] )
				);

				$user_id = absint( $specialist->user_id );
				if( $user_id ) {
					$avatar_id = absint( \get_user_meta( $user_id, 'avatar', true ) );
					if( $avatar_id ) {
						\set_post_thumbnail( $post_id, $avatar_id );
					}
				}
			}

			// Remove about col
			$wpdb->query( "ALTER TABLE `{$table}` DROP COLUMN `about`;" );

			// Commit transaction
			$wpdb->query( "COMMIT;" );
		} catch (\Throwable $th) {
			// Rollback transaction
			$wpdb->query( "ROLLBACK;" );
		}
	}

	private static function register_location_sync_hook() {
		if( !\is_admin() ) return;
		if( \get_option( self::LOCATION_SYNC_OPTION ) ) return;

		\add_action( 'wp_loaded', [ __CLASS__, 'handle_location_sync_request' ] );
	}

	public static function handle_location_sync_request() {
		if( \get_option( self::LOCATION_SYNC_OPTION ) ) {
			return;
		}

		include_once( DRPLUS_DIR . "inc/Libs/ir-cities.php" );

		self::sync_specialist_location_taxonomy();
		self::sync_hospital_location_taxonomy();
		\update_option( self::LOCATION_SYNC_OPTION, 1, true );
	}

	public static function sync_specialist_location_taxonomy() {
		global $wpdb;

		// Ensure taxonomy is available before continuing.
		if( !\taxonomy_exists( 'location' ) ) return;

		$relations_table = "{$wpdb->prefix}drplus_specialist_city_rel";
		$specialists_table = "{$wpdb->prefix}drplus_specialists";

		$relations = $wpdb->get_results(
			"SELECT rel.user_id, rel.city, s.post_id FROM `{$relations_table}` AS rel
			 LEFT JOIN `{$specialists_table}` AS s ON s.user_id = rel.user_id
			 WHERE rel.city IS NOT NULL AND rel.city != '' AND s.post_id IS NOT NULL AND s.post_id > 0"
		);

		if( empty( $relations ) ) return;

		$province_terms = [];
		$city_terms = [];

		foreach( $relations as $relation ) {
			$post_id = \absint( $relation->post_id );
			$city_name = \trim( (string) $relation->city );

			if( !$post_id || empty( $city_name ) ) continue;

			$province = IR::find_province_by_city( $city_name );
			if( empty( $province ) || empty( $province['fa'] ) || empty( $province['code'] ) ) continue;

			$province_term_id = self::ensure_province_term( $province['code'], $province['fa'], $province_terms );
			if( !$province_term_id ) continue;

			$city_term_id = self::ensure_city_term( $city_name, $province_term_id, $province['code'], $city_terms );
			if( !$city_term_id ) continue;

			\wp_set_object_terms( $post_id, [$province_term_id, $city_term_id], 'location', true );
		}

		self::update_specialist_offices_locations( $province_terms, $city_terms );
	}

	public static function sync_hospital_location_taxonomy() {
		if( !\taxonomy_exists( 'location' ) ) return;

		global $wpdb;
		$province_terms = [];
		$city_terms = [];

		$hospitals = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.ID, prov.meta_value AS province_code, city.meta_value AS city_name
				 FROM {$wpdb->posts} AS p
				 LEFT JOIN {$wpdb->postmeta} AS prov ON (p.ID = prov.post_id AND prov.meta_key = %s)
				 LEFT JOIN {$wpdb->postmeta} AS city ON (p.ID = city.post_id AND city.meta_key = %s)
				 WHERE p.post_type = %s AND p.post_status IN ('publish','private','pending','draft','future','trash')",
				'_province',
				'_hospital_city',
				'hospital'
			)
		);

		if( empty( $hospitals ) ) return;

		$provinces = IR::provinces();

		foreach( $hospitals as $hospital ) {
			$post_id       = (int) $hospital->ID;
			$city_name     = \trim( (string) $hospital->city_name );
			$province_code = \trim( (string) $hospital->province_code );

			if( empty( $city_name ) && empty( $province_code ) ) continue;

			$province_data = [];
			if( $province_code && isset( $provinces[$province_code] ) ) {
				$province_data = array_merge( ['code' => $province_code], $provinces[$province_code] );
			} elseif( $city_name ) {
			$province_data = IR::find_province_by_city( $city_name );
		}

		if( empty( $province_data ) || empty( $province_data['fa'] ) || empty( $province_data['code'] ) ) continue;

		$province_term_id = self::ensure_province_term( $province_data['code'], $province_data['fa'], $province_terms );
		if( !$province_term_id ) continue;

		if( empty( $city_name ) ) {
			\wp_set_object_terms( $post_id, [$province_term_id], 'location', true );
			continue;
		}

		$city_term_id = self::ensure_city_term( $city_name, $province_term_id, $province_data['code'], $city_terms );
		if( !$city_term_id ) continue;
		\wp_set_object_terms( $post_id, [$province_term_id, $city_term_id], 'location', true );
	}
}

	private static function update_specialist_offices_locations( array &$province_terms, array &$city_terms ) {
		global $wpdb;

		$provinces = IR::provinces();
		$province_lookup = [];
		foreach( $provinces as $code => $province_data ) {
			if( !empty( $province_data['fa'] ) ) {
				$province_lookup[$province_data['fa']] = $code;
			}
			if( !empty( $province_data['en'] ) ) {
				$province_lookup[$province_data['en']] = $code;
			}
		}

		$specialists = $wpdb->get_results(
			"SELECT `id`, `offices` FROM `{$wpdb->prefix}drplus_specialists` WHERE `offices` IS NOT NULL AND `offices` != ''"
		);

		if( empty( $specialists ) ) return;

		foreach( $specialists as $specialist ) {
			$offices = \json_decode( $specialist->offices, true );
			if( empty( $offices ) || !\is_array( $offices ) ) continue;

			$offices_updated = false;

			foreach( $offices as $office_key => $office ) {
				$province_code_for_city = '';
				if( empty( $office['type'] ) || $office['type'] !== 'custom' ) continue;

				$province_value = isset( $office['province'] ) ? \trim( (string) $office['province'] ) : '';
				$city_value = isset( $office['city'] ) ? \trim( (string) $office['city'] ) : '';

				$province_term_id = self::get_province_term_id( $province_value, $province_terms, $provinces, $province_lookup );
				if( $province_term_id ) {
					if( isset( $province_lookup[$province_value] ) ) {
						$province_code_for_city = $province_lookup[$province_value];
					} elseif( isset( $provinces[$province_value] ) ) {
						$province_code_for_city = $province_value;
					}
				}
				if( !$province_term_id && $city_value ) {
					$province_data = IR::find_province_by_city( $city_value );
					if( !empty( $province_data['code'] ) ) {
						$province_term_id = self::get_province_term_id( $province_data['code'], $province_terms, $provinces, $province_lookup );
						$province_code_for_city = $province_data['code'];
					}
				}

				if( $province_term_id && (!isset( $office['province'] ) || $office['province'] !== $province_term_id) ) {
					$offices[$office_key]['province'] = $province_term_id;
					$offices_updated = true;
				}

				$city_term_id = self::get_city_term_id( $city_value, $province_term_id, $city_terms, $province_code_for_city );
				if( $city_term_id && (!isset( $office['city'] ) || $office['city'] !== $city_term_id) ) {
					$offices[$office_key]['city'] = $city_term_id;
					$offices_updated = true;
				}
			}

			if( $offices_updated ) {
				$wpdb->update(
					"{$wpdb->prefix}drplus_specialists",
					[
						'offices' => \wp_json_encode( $offices ),
					],
					[
						'id' => $specialist->id,
					],
					[
						'%s',
					],
					[
						'%d',
					]
				);
			}
		}
	}

	private static function get_province_term_id( $province_value, array &$province_terms, array $provinces, array $province_lookup ) : int {
		if( $province_value === '' ) return 0;

		if( \is_numeric( $province_value ) ) {
			$province_term = \term_exists( (int) $province_value, 'location' );
			if( $province_term ) {
				return self::parse_term_id( $province_term );
			}
		}

		$province_code = $province_value;
		if( isset( $province_lookup[$province_value] ) ) {
			$province_code = $province_lookup[$province_value];
		}

		if( empty( $provinces[$province_code]['fa'] ) ) return 0;

		return self::ensure_province_term( $province_code, $provinces[$province_code]['fa'], $province_terms );
	}

	private static function get_city_term_id( string $city_value, int $province_term_id, array &$city_terms, string $province_code = '' ) : int {
		if( empty( $city_value ) || !$province_term_id ) return 0;

		if( \is_numeric( $city_value ) ) {
			$city_term = \term_exists( (int) $city_value, 'location' );
			if( $city_term ) {
				return self::parse_term_id( $city_term );
			}
		}

		return self::ensure_city_term( $city_value, $province_term_id, $province_code, $city_terms );
	}

	private static function ensure_province_term( string $province_code, string $province_name, array &$province_terms ) : int {
		if( $province_code === '' || $province_name === '' ) return 0;

		if( isset( $province_terms[$province_code] ) ) {
			return (int) $province_terms[$province_code];
		}

		$slug = \sanitize_title( "province-{$province_code}" );

		$province_term = \get_term_by( 'slug', $slug, 'location' );
		if( !$province_term ) {
			$province_term = \term_exists( $province_name, 'location' );
		}

		if( !$province_term ) {
			$province_term = \wp_insert_term(
				$province_name,
				'location',
				[
					'slug' => $slug,
				]
			);
		}

		if( \is_wp_error( $province_term ) ) return 0;

		$province_term_id = self::parse_term_id( $province_term );
		$province_terms[$province_code] = $province_term_id;

		return $province_term_id;
	}

	private static function ensure_city_term( string $city_name, int $province_term_id, string $province_code, array &$city_terms ) : int {
		if( $city_name === '' || !$province_term_id ) return 0;

		$city_key = "{$province_term_id}|{$city_name}";

		if( isset( $city_terms[$city_key] ) ) {
			return (int) $city_terms[$city_key];
		}

		$slug_suffix = $province_code !== '' ? $province_code : "p{$province_term_id}";
		$slug = \sanitize_title( "{$slug_suffix}-{$city_name}" );

		$city_term = \get_term_by( 'slug', $slug, 'location' );
		if( $city_term && (int) $city_term->parent !== $province_term_id ) {
			$city_term = false;
		}

		if( !$city_term ) {
			$city_term = \term_exists( $city_name, 'location', $province_term_id );
		}

		if( !$city_term ) {
			$city_term = \wp_insert_term(
				$city_name,
				'location',
				[
					'parent' => $province_term_id,
					'slug'   => $slug,
				]
			);
		}

		if( \is_wp_error( $city_term ) ) return 0;

		$city_term_id = self::parse_term_id( $city_term );

		$city_terms[$city_key] = $city_term_id;

		return $city_term_id;
	}

	private static function parse_term_id( $term ) : int {
		if( $term instanceof \WP_Term ) {
			return (int) $term->term_id;
		}
		return is_array( $term ) ? (int) $term['term_id'] : (int) $term;
	}
}
