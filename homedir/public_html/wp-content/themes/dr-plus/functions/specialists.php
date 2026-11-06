<?php

use DrPlus\Model\Specialists;
use DrPlus\Model\SpecialistSpecialitiesRel;
use DrPlus\Utils;
use DrPlus\Utils\Onboard;
use DrPlus\Utils\Options;
use DrPlus\Utils\Search;
use DrPlus\Utils\Speciality;
use DrPlus\Utils\UtilsSpecialists;
use DrPlus\Utils\User;

if( !function_exists( "drplus_specialist_disable_gutenberg" ) ) {
	function drplus_specialist_disable_gutenberg( $current_status, $post_type ) {
		if( $post_type === 'specialist' ) return false;
    	return $current_status;
	}
}
add_filter( 'use_block_editor_for_post_type', 'drplus_specialist_disable_gutenberg', 10, 2 );

if( !function_exists( "drplus_specialist_user_query" ) ) {
	function drplus_specialist_user_query( $query ) {
		if( isset( $query->query_vars['specialists'] ) || !empty( $query->query_vars['only_offline_visits'] ) || !empty( $query->query_vars['only_online_visits'] ) || !empty( $query->query_vars['only_verified'] ) ) {
			global $wpdb;
			
			$join_type = 'INNER';
			if( isset( $query->query_vars['specialists'] ) && Utils::to_bool( $query->query_vars['specialists'] ) === false ) {
				$join_type = 'LEFT';
				$query->query_where .= " AND dr_sp.`user_id` IS NULL ";
			}
			$specialists_table = Specialists::tableName();
			$query->query_from .= " {$join_type} JOIN `{$specialists_table}` AS dr_sp ON `{$wpdb->users}`.`ID`=dr_sp.`user_id`";

			if( !empty( $query->query_vars['only_offline_visits'] ) ) {
				$query->query_where .= " AND dr_sp.`offline_visit`=1";
			} else if( !empty( $query->query_vars['only_online_visits'] ) ) {
				$query->query_where .= " AND dr_sp.`online_visit`=1";
			}

			if( !empty( $query->query_vars['only_verified'] ) ) {
				$query->query_where .= " AND dr_sp.`is_verified`=1";
			}

			if( !isset( $query->query_vars['specialists'] ) || Utils::to_bool( $query->query_vars['specialists'] ) !== false ) {
				$query->query_where .= " AND dr_sp.`status`='active'";
			}

			$city = '';
			if( !empty( $query->query_vars['city'] ) ) {
				$city = Utils::convert_chars( $query->query_vars['city'], 'sanitize_title_with_dashes' );
			} else {
				$city = Search::get_city_from_GET();
				if( !$city && is_tax( 'location' ) ) {
					$term = get_queried_object();
					$city = $term && !is_wp_error( $term ) ? $term->slug : '';
				}
			}

			if( $city ) {
				$location_term = get_term_by( is_numeric( $city ) ? 'id' : 'slug', $city, 'location' );
				if( $location_term && !is_wp_error( $location_term ) ) {
					$term_relationships_table = $wpdb->term_relationships;
					if( strpos( $query->query_from, $term_relationships_table ) === false ) {
						$query->query_from .= " INNER JOIN `{$term_relationships_table}` AS dr_tr ON dr_tr.`object_id`=dr_sp.`post_id`";
					}
					$query->query_where .= $wpdb->prepare( " AND dr_tr.`term_taxonomy_id`=%d", $location_term->term_taxonomy_id );
				}
			}
		}
	}
}
add_action( 'pre_user_query', 'drplus_specialist_user_query' );

if( !function_exists( "drplus_onboard_create_post" ) ) {
	function drplus_onboard_create_post( $specialist ) {
		// check if exist
		$post_id = $specialist->post_id;
		if( !empty( $post_id ) ) return;

		// Insert post
		$post_status = 'draft';
		if( !empty( $specialist->status ) ) {
			$post_status = $specialist->status == 'active' ? "publish" : "draft";	
		}

		$post_title = $specialist->display_name;
		if( empty( $specialist->name ) ) {
			$subtitle_enabled = Options::get_options( ['onboard-info-field-subtitle-enabled' => true] )['onboard-info-field-subtitle-enabled'];
			if( $subtitle_enabled && !empty( $specialist->subtitle ) ) {
				$args['post_title'] = sprintf( '%s (%s)', $specialist->display_name, $specialist->subtitle );
			}
		}
		
		$args = [
			'post_title'		=> $post_title,
			'post_status'		=> $post_status,
			'post_type'			=> 'specialist',
			'post_name'			=> $specialist->slug,
			'post_content'		=> wp_kses_post( $data['about'] ?? '' ),
			'comment_status'	=> 'open',
			'ping_status'		=> 'closed',
			'meta_input'		=> [
				'_drplus_user_id'			=> $specialist->user_id,
				'_drplus_specialist_id'		=> $specialist->id,
				'specialist_subtitle'		=> $specialist->subtitle,
				'specialist_is_verified'	=> $specialist->is_verified,
			],
		];
		
		$post_id = wp_insert_post( $args, true );
		if( !is_wp_error( $post_id ) ) {
			// Set post thumbnail
			$thumbnail_id = Utils::convert_chars( $data['avatar'] ?? '' );
			if( !empty( $thumbnail_id ) ) {
				set_post_thumbnail( $post_id, $thumbnail_id );
			}

			do_action( 'drplus/specialist/post_updated', $post_id, $specialist );
		}
		return $post_id;
	}
}
add_action( 'drplus/specialist/created', 'drplus_onboard_create_post', 10, 2 );

if( !function_exists( "drplus_onboard_update_post" ) ) {
	function drplus_onboard_update_post( $specialist, $data ) {
		$post_id = $specialist->post_id;
		if( empty( $post_id ) ) {
			return drplus_onboard_create_post( $specialist );
		}

		$post_title = $specialist->display_name;
		if( empty( $specialist->name ) ) {
			$subtitle_enabled = Options::get_options( ['onboard-info-field-subtitle-enabled' => true] )['onboard-info-field-subtitle-enabled'];
			if( $subtitle_enabled && !empty( $specialist->subtitle ) ) {
				$args['post_title'] = sprintf( '%s (%s)', $specialist->display_name, $specialist->subtitle );
			}
		}

		$args = [
			'ID'			=> $post_id,
			'post_status'	=> $specialist->status == 'active' ? "publish" : "draft",
			'post_name'		=> $specialist->slug,
			'post_title'	=> $post_title,
		];

		if( isset( $data['about'] ) ) {
			$args['post_content'] = wp_kses_post( $data['about'] );
		}

		if( isset( $data['avatar'] ) ) {
			$thumbnail_id = Utils::convert_chars( $data['avatar'] );
			if( !empty( $thumbnail_id ) ) {
				set_post_thumbnail( $post_id, $thumbnail_id );
			} else {
				delete_post_thumbnail( $post_id );
			}
		}

		// Update meta
		$meta_input = [];
		$meta_input['specialist_subtitle'] = $specialist->subtitle;
		$meta_input['specialist_is_verified'] = $specialist->is_verified;
		$args['meta_input'] = $meta_input;

		$post_id = wp_update_post( $args, true );
		if( !is_wp_error( $post_id ) ) {
			do_action( 'drplus/specialist/post_updated', $post_id, $specialist );
		}
		return $post_id;
	}
}
add_action( 'drplus/specialist/updated', 'drplus_onboard_update_post', 10, 2);

if( !function_exists( "drplus_specialist_saved" ) ) {
	function drplus_specialist_saved( $specialist, $original_data, $data ) {
		if( is_admin() ) return;
		// Save next step for onboard
		$step = '';
		if( empty( $data['status'] ) ) return;
		if( $data['status'] != 'rejected' && !empty( $data['step'] ) ) {
			$step = Utils::convert_chars( $data['step'] );
		} else if( $data['status'] == 'rejected' ) {
			$step = 'rejected';
		}
		Onboard::update_user_step( $step );
	}
}
add_action( 'drplus/specialist/saved', 'drplus_specialist_saved', 10, 3 );

if( !function_exists( "drplus_specialist_after_post_updated" ) ) {
	function drplus_specialist_after_post_updated( $post_id, $specialist ) {
		$specialist = Specialists::find( $specialist->id );
		$specialist->post_id = $post_id;
		$specialist->save();
	}
}
add_action( 'drplus/specialist/post_updated', 'drplus_specialist_after_post_updated', 10, 2 );

if( !function_exists( "drplus_change_specialist_page_comments_title" ) ) {
	function drplus_change_specialist_page_comments_title( $title ) {
		if( !is_singular( 'specialist' ) ) return $title;

		return esc_html__( 'User Reviews', 'drplus' );
	}
}
add_filter( 'drplus/comments/title', 'drplus_change_specialist_page_comments_title' );

if( !function_exists( "drplus_save_specialist_as_recently_visited" ) ) {
	function drplus_save_specialist_as_recently_visited() {
		if( wp_doing_ajax() ) return;

		// only work in specialist page or any page with 'drplus_save_recent' query string
		if( !empty( $_GET['drplus_save_recent'] ) ) {
			$specialist_id = Utils::convert_chars( $_GET['drplus_save_recent'], true, 'absint' );
		} else if( is_singular( 'specialist' ) ) {
			$specialist_post_id = get_the_ID();
			$specialist_id = get_post_meta( $specialist_post_id, '_drplus_specialist_id', true );
		}
 		if( empty( $specialist_id ) ) return;
		User::add_recently_visited_specialist( 0, $specialist_id );

		if( !empty( $_GET['drplus_save_recent'] ) ) {
			// Redirect to remove query string
			$redirect_url = remove_query_arg( 'drplus_save_recent' );
			if( !empty( $redirect_url ) ) {
				if ( wp_safe_redirect( $redirect_url ) ) {
					exit;
				}
			}
		}
	}
}
add_action( 'init', 'drplus_save_specialist_as_recently_visited' );

if( !function_exists( "drplus_specialist_speciality_filter" ) ) {
	function drplus_specialist_speciality_filter( $clauses, $query ) {
		if( is_admin() || !$query->is_main_query() || $query->get( 'post_type' ) != 'specialist' ) return $clauses;

		$specialities = Speciality::get_specialities_from_GET();
		if( empty( $specialities ) ) return $clauses;

		$specialities_sql_placeholder = Utils::db_placeholder( $specialities, '%d' );

		global $wpdb;
		$specialists_table = Specialists::tableName();
		$specialist_speciality_rel_table = SpecialistSpecialitiesRel::tableName();
		$where = "AND `{$wpdb->posts}`.`ID` IN (SELECT DISTINCT `{$specialists_table}`.`post_id` FROM `{$specialists_table}` LEFT JOIN `{$specialist_speciality_rel_table}` ON `{$specialists_table}`.`user_id`=`{$specialist_speciality_rel_table}`.`user_id` WHERE `{$specialist_speciality_rel_table}`.`speciality_id` IN ({$specialities_sql_placeholder}))";
		$where = $wpdb->prepare( $where, $specialities );

		$clauses['where'] .= " {$where}";

		// print_r( $clauses ); die;
		
		return $clauses;
	}
}
add_filter( 'posts_clauses', 'drplus_specialist_speciality_filter', 10, 2 );

if( !function_exists( "drplus_specialist_type_filter" ) ) {
	function drplus_specialist_type_filter( $clauses, $query ) {
		if( is_admin() || !$query->is_main_query() || $query->get( 'post_type' ) != 'specialist' ) return $clauses;

		if( empty( $_GET['specialist-type'] ) ) return $clauses;
		$type = Utils::ensure_values_in_array( Utils::convert_chars( $_GET['specialist-type'] ), ['all', 'in-person', 'online'], 'all' );
		if( $type === 'all' ) return $clauses;

		$specialists_table = Specialists::tableName();
		global $wpdb;
		if( strpos( $clauses['join'], "JOIN `{$specialists_table}`" ) === false ) {
			$clauses['join'] .= " LEFT JOIN `{$specialists_table}` as dr_sp ON dr_sp.`post_id`=`{$wpdb->posts}`.`ID`";
		}
		if( $type == 'in-person' ) {
			$clauses['where'] .= " AND dr_sp.`offline_visit`=1";
		}
		if( $type == 'online' ) {
			$clauses['where'] .= " AND dr_sp.`online_visit`=1";
		}

		return $clauses;
	}
}
add_filter( 'posts_clauses', 'drplus_specialist_type_filter', 10, 2 );

if( !function_exists( "drplus_delete_specialist_cache_after_save" ) ) {
	function drplus_delete_specialist_cache_after_save( $specialist ) {
		UtilsSpecialists::delete_group_caches( [$specialist->id] );
	}
}
add_action( 'drplus/specialist/saved', 'drplus_delete_specialist_cache_after_save' );

if( !function_exists( "drplus_specialist_profile_section_title" ) ) {
	function drplus_specialist_profile_section_title( $title, $section ) {
		if( $section == 'insurances' ) {
			$title = '';
		}
		return $title;
	}
}
add_filter( 'drplus/wc/specialist/profile/section_title', 'drplus_specialist_profile_section_title', 10, 2 );

/**
* Modify term query clauses to filter by post type.
* Filter hook added in get_location_terms_by_post_type function in utils-location
*/
if( !function_exists( 'drplus_filter_location_terms_by_post_type' ) ) {
	function drplus_filter_location_terms_by_post_type( $clauses, $taxonomies, $args ) {
		if ( empty( $args['drplus_filter_post_type'] ) || ! in_array( 'location', (array) $taxonomies, true ) ) {
			return $clauses;
		}

		global $wpdb;

		$post_type = $args['drplus_filter_post_type'];
		$statuses  = ! empty( $args['post_status'] ) ? (array) $args['post_status'] : [ 'publish' ];

		$statuses_sql = "'" . implode( "','", array_map( 'esc_sql', $statuses ) ) . "'";

		$clauses['join'] .= "
			INNER JOIN {$wpdb->term_relationships} tr 
				ON tr.term_taxonomy_id = tt.term_taxonomy_id
			INNER JOIN {$wpdb->posts} p 
				ON p.ID = tr.object_id
		";

		$clauses['where'] .= $wpdb->prepare(
			" AND p.post_type = %s AND p.post_status IN ($statuses_sql) ",
			$post_type
		);

		$clauses['distinct'] = 'DISTINCT';

		return $clauses;
	}
}

if( !function_exists( "drplus_define_specialist_mode" ) ) {
	function drplus_define_specialist_mode() {
		$single_specialist_config = Options::get_options( [
			'single_specialist_mode'	=> false,
			'single_specialist_id'		=> 0,
		] );
		$specialist = Specialists::query()->select( 'id' )->where( 'post_id', $single_specialist_config['single_specialist_id'] )->first();
		if( !defined( 'DRPLUS_SINGLE_SPECIALIST_MODE' ) ) {
			define( 'DRPLUS_SINGLE_SPECIALIST_MODE', Utils::to_bool( $single_specialist_config['single_specialist_mode'] ) && !empty( $specialist->id ) );
		}
		if( !defined( 'DRPLUS_SPECIALIST_ID' ) ) {
			define( 'DRPLUS_SPECIALIST_ID', $specialist->id ?? 0 );
		}
	}
}
add_action( 'init', 'drplus_define_specialist_mode', 1 );