<?php

use DrPlus\Utils;
use DrPlus\Utils\Options;
use DrPlus\Utils\Skyroom;
use DrPlus\Utils\User;
use DrPlus\Utils\UtilsSpecialists;
use DrPlus\Utils\Validators;

if( !function_exists( "drplus_orderby_random_user_query" ) ) {
	function drplus_orderby_random_user_query( $query ) {
		if( $query->query_vars["orderby"] === "rand" ) {
			$query->query_orderby = str_replace( "user_login", "RAND()", $query->query_orderby );
		}
	}
}
add_action( 'pre_user_query', 'drplus_orderby_random_user_query' );

if ( ! function_exists( "drplus_set_orderby_user_query" ) ) {
	function drplus_set_orderby_user_query( $query ) {
		global $wpdb;

		$orderby = $query->query_vars["orderby"] ?? '';
		$order = $query->query_vars['order'];

		if ( in_array( $orderby, ['first_name_alphabetic', 'first_name_reverse', 'last_name_alphabetic', 'last_name_reverse'], true ) ) {
			$replace_orderby = 'ORDER BY user_login ' . $order;
			if ( strpos( $query->query_from, "fm.meta_key = 'first_name'" ) === false ) {
				$query->query_from .= " LEFT JOIN {$wpdb->usermeta} AS fm ON {$wpdb->users}.ID = fm.user_id AND fm.meta_key = 'first_name'";
			}
			if ( strpos( $query->query_from, "lm.meta_key = 'last_name'" ) === false ) {
				$query->query_from .= " LEFT JOIN {$wpdb->usermeta} AS lm ON {$wpdb->users}.ID = lm.user_id AND lm.meta_key = 'last_name'";
			}

			switch ( $orderby ) {
				case 'first_name_alphabetic':
					$query->query_orderby = str_replace(
						$replace_orderby,
						"ORDER BY fm.meta_value ASC, lm.meta_value ASC",
						$query->query_orderby
					);
					break;

				case 'first_name_reverse':
					$query->query_orderby = str_replace(
						$replace_orderby,
						"ORDER BY fm.meta_value DESC, lm.meta_value DESC",
						$query->query_orderby
					);
					break;

				case 'last_name_alphabetic':
					$query->query_orderby = str_replace(
						$replace_orderby,
						"ORDER BY lm.meta_value ASC, fm.meta_value ASC",
						$query->query_orderby
					);
					break;

				case 'last_name_reverse':
					$query->query_orderby = str_replace(
						$replace_orderby,
						"ORDER BY lm.meta_value DESC, fm.meta_value DESC",
						$query->query_orderby
					);
					break;
			}
		}
	}
}
add_action( 'pre_user_query', 'drplus_set_orderby_user_query' );

// Enhanced search
if( !function_exists( "drplus_enhanced_user_search_columns" ) ) {
	function drplus_enhanced_user_search_columns( $columns, $search, $query ) {
		if( !empty( $query->query_vars['search'] ) ) {
			global $wpdb;

			$query->query_from .= " LEFT JOIN {$wpdb->usermeta} AS fm ON {$wpdb->users}.ID = fm.user_id";
			$query->query_from .= " LEFT JOIN {$wpdb->usermeta} AS lm ON {$wpdb->users}.ID = lm.user_id";

			$query->query_where .= " AND fm.`meta_key`='first_name' AND lm.`meta_key`='last_name'";

			foreach( $columns as $index => $column ) {
				if( strpos( $column, "." ) === false ) {
					$columns[$index] = $wpdb->users . ".{$column}";
				}
			}
			$columns[] = 'fm.meta_value';
			$columns[] = 'lm.meta_value';

			if( !empty( $query->query_vars['search_in_nid'] ) ) {
				$query->query_from .= " LEFT JOIN {$wpdb->usermeta} AS nid ON {$wpdb->users}.ID = nid.user_id AND nid.meta_key = 'nid' ";
				$columns[] = 'nid.meta_value';
			}
		}

		return $columns;
	}
}
add_filter( 'user_search_columns', 'drplus_enhanced_user_search_columns', 10, 3 );

// Custom avatar
if( !function_exists( "drplus_change_avatar" ) ) {
	function drplus_change_avatar( $avatar, $id_or_email, $size, $default, $alt, $args ) {
		if( is_a( $id_or_email, 'WP_Comment' ) ) {
			$id_or_email = !empty( $id_or_email->user_id ) ? $id_or_email->user_id : $id_or_email->comment_author_email;
		}
		$user = Utils::get_user_object( $id_or_email );
		if( !empty( $user ) ) {
			$avatar_id = User::get_avatar_id( $user->ID );
			if( $avatar_id ) {
				$args['alt'] = $alt;
				$avatar = wp_get_attachment_image( $avatar_id, [$size, $size], false, $args );
			} else {
				$avatar = '<img src="' . DRPLUS_URI . "assets/images/user.svg" . '" alt="">';
			}
		}
		return $avatar;
	}
}
add_filter( 'get_avatar', 'drplus_change_avatar', 1, 6 );

if( !function_exists( "drplus_change_avatar_url" ) ) {
	function drplus_change_avatar_url( $url, $id_or_email, $args ) {
		if( is_a( $id_or_email, 'WP_Comment' ) ) {
			$id_or_email = !empty( $id_or_email->user_id ) ? $id_or_email->user_id : $id_or_email->comment_author_email;
		}
		$user = Utils::get_user_object( $id_or_email );
		if( !empty( $user ) ) {
			$avatar_id = User::get_avatar_id( $user->ID );
			if( $avatar_id ) {
				$url = wp_get_attachment_image_url( $avatar_id, [$args['size'], $args['size']] );
			} else {
				$url = DRPLUS_URI . "assets/images/user.svg";
			}
		}
		return $url;
	}
}
add_filter( 'get_avatar_url', 'drplus_change_avatar_url', 10, 3 );

function drplus_allow_users_upload_files() {
    if ( ! current_user_can( 'upload_files' ) ) {
        $user = wp_get_current_user();
        $user->add_cap( 'upload_files' );
    }
}
add_action( 'init', 'drplus_allow_users_upload_files' );

if( !function_exists( "drplus_filter_attachments" ) ) {
	function drplus_filter_attachments( $query ) {
		if ( isset( $_POST['action'] ) && $_POST['action'] === 'query-attachments' ) {
			if ( is_user_logged_in() && ! current_user_can( 'administrator' ) ) {
				$current_user_id = get_current_user_id();
				$query->set( 'author', $current_user_id );
				$query->set( 'post_type', 'attachment' );
			}
		}
	}
}
add_action( 'pre_get_posts', 'drplus_filter_attachments' );

if( !function_exists( "drplus_user_update_display_names" ) ) {
	function drplus_user_update_display_names( $settings ) {
		if( !empty( $settings['security']['hide_mobile'] ) && $settings['security']['hide_mobile'] != 'disabled' ) {
			global $wpdb;
			$query = "SELECT `ID`, `display_name` FROM `{$wpdb->users}` WHERE `user_login`=`display_name`";
			$users = $wpdb->get_results( $query );
			foreach( $users as $user ) {
				$wpdb->query( 'START TRANSACTION' );
				if( Validators::phone( $user->display_name ) || User::check_display_name_hidden( $user->display_name ) ) {
					$update = User::change_display_name( $user, $settings );
					if( !$update ) {
						$wpdb->query( 'ROLLBACK' );
						break;
					}
				}
				$wpdb->query( 'COMMIT' );
			}
		}
	}
}
add_action( 'drplus/sms/settings/updated', 'drplus_user_update_display_names' );

if( !function_exists( "drplus_user_update_user" ) ) {
	function drplus_user_update_user( $user_id ) {
		remove_action( 'wp_update_user', 'drplus_user_update_user' );
		$user = get_user_by( 'id', $user_id );
		if( Validators::phone( $user->display_name ) || User::check_display_name_hidden( $user->display_name ) ) {
			User::change_display_name( get_user_by( 'id', $user_id ) );
		}

		Skyroom::update_skyroom_user( $user_id );
	}
}
add_action( 'wp_update_user', 'drplus_user_update_user' );
add_action( 'user_register', 'drplus_user_update_user' );

if( !function_exists( "drplus_delete_user" ) ) {
	function drplus_delete_user( $user_id ) {
		UtilsSpecialists::delete_all_specialist_data( $user_id );

		Skyroom::delete_skyroom_user( $user_id );
	}
}
add_action( 'delete_user', 'drplus_delete_user' );

///////// Bypass update user & set password when user registered with SMS
if( !function_exists( "drplus_bypass_user_password" ) ) {
	function drplus_bypass_user_password( $check, $password, $hash, $user_id ) {
		if( class_exists( "DrPlus\Utils\Options" ) ) {
			$options = Options::get_options( [
				'auth'  => true
			] );
		} else {
			$options = get_option( 'drplus', [] );
			if( !isset( $options['auth'] ) ) {
				$options['auth'] = true;
			}
		}
		if( !$options['auth'] ) return $check;
		$has_password = get_user_meta( $user_id, 'has_password', true );
		if( ( class_exists( "DrPlus\Utils" ) && !Utils::to_bool( $has_password ) ) || ( $has_password === 'false' || $has_password === false || $has_password === "" || $has_password === 0 ) ) {
			return !wp_doing_ajax(); // Don't return true on ajax functions
		}

		return $check;
	}
}
add_filter( 'check_password', 'drplus_bypass_user_password', 10, 4 );

if( !function_exists( "drplus_set_password_for_users_without_password" ) ) {
	function drplus_set_password_for_users_without_password() {
		if( empty( $_POST ) ) return;
		if( ( is_admin() && !wp_doing_ajax() ) || is_admin() ) return;

		// WC
		if( !empty( $_POST['password_1'] ) && !empty( $_POST['password_2'] ) ) {
			if( !Utils::to_bool( get_user_meta( get_current_user_id(), 'has_password', true ) ) ) {
				$_POST['password_current'] = 'drplus_bypass';
			}
		}
	}
}
add_action( 'init', 'drplus_set_password_for_users_without_password', 9 );

if( !function_exists( "drplus_change_has_password" ) ) {
	function drplus_change_has_password( $user_id, $userdata ) {
		if( !empty( $userdata['user_pass'] ) ) {
			update_user_meta( $user_id, 'has_password', true );
		}
	}
}
add_action( 'wp_update_user', 'drplus_change_has_password', 10, 2 );
//////////////// END Bypass