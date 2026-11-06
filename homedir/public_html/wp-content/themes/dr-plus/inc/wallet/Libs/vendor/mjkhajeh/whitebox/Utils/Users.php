<?php
namespace MJ\Whitebox\Utils;

use MJ\Whitebox\Utils;

class Users extends Utils {
	/**
	 * Get a user object by various means.
	 *
	 * @param mixed $user The user identifier (ID, object, or array).
	 * @return WP_User|null The user object or null if not found.
	 */
	public static function get_user_object( $user = null ) {
		if( !empty( $user ) ) {
			if( is_numeric( $user ) ) {
				$user = get_user_by( 'id', $user );
			} else if( is_array( $user ) ) {
				$user = get_user_by( 'id', $user['ID'] );
			} else if( is_string( $user ) && is_email( $user ) ) {
				$user = get_user_by( 'email', $user );
			}
		} else {
			if( is_user_logged_in() ) {
				$user = wp_get_current_user();
			}
		}
		return $user;
	}

	/**
	 * Get the ID of a user by various means.
	 *
	 * @param mixed $user The user identifier (ID, object, or array).
	 * @return int The user ID or 0 if not found.
	 */
	public static function get_user_id( $user = 0 ) {
		if( !empty( $user ) ) {
			if( is_numeric( $user ) ) {
				return $user;
			}
			if( is_object( $user ) ) {
				if( !empty( $user->ID ) ) {
					return $user->ID;
				}
			}
			if( is_array( $user ) ) {
				if( !empty( $user['ID'] ) ) {
					return $user['ID'];
				} else if( !empty( $user['id'] ) ) {
					return $user['id'];
				}
			}
			$user = self::get_user_object( $user );
			if( !empty( $user ) ) {
				return $user->ID;
			}
		}

		return get_current_user_id();
	}

	/**
	 * Find a WordPress user by mobile number.
	 *
	 * Searches the user login and user meta fields: 'mobile', 'billing_phone', and 'shipping_phone'.
	 *
	 * @param string $mobile The mobile number to search for.
	 * @param string $return Set the return value. Accepts: ID | OBJECT
	 *
	 * @return mixed
	 */
	public static function find_user_by_mobile( string $mobile, $return = 'ID' ) {
		$return = strtoupper( $return );

		$user = get_user_by( 'login', $mobile );
		if( empty( $user ) ) {
			$user = get_users( [
				'meta_query' => array(
					'relation' => 'OR',
					array(
						'key'   => 'mobile',
						'value' => $mobile,
					),
					array(
						'key'   => 'billing_phone',
						'value' => $mobile,
					),
					array(
						'key'   => 'shipping_phone',
						'value' => $mobile,
					),
				),
				'number' => 1,
				'fields' => $return == 'ID' ? 'ID' : 'all',
			] );
			$user = !empty( $user[0] ) ? $user[0] : '';
		} else {
			if( $return == 'ID' ) {
				$user = $user->ID;
			}
		}
		return $user;
	}

	/**
	 * Retrieve a user's mobile number.
	 *
	 * Checks the meta fields 'mobile', 'billing_phone', and 'shipping_phone' in order.
	 *
	 * @param int|string $user_id User ID or value resolvable to a user ID.
	 *
	 * @return string User's mobile number, or empty string if not found.
	 */
	public static function get_user_mobile( $user_id ) {
		$user_id = self::get_user_id( $user_id );
		$mobile = get_user_meta( $user_id, 'mobile', true );
		if( empty( $mobile ) ) {
			$mobile = get_user_meta( $user_id, 'billing_phone', true );
		}
		if( empty( $mobile ) ) {
			$mobile = get_user_meta( $user_id, 'shipping_phone', true );
		}
		return $mobile;
	}

	/**
	 * Create a new WordPress user with optional mobile, email, password, and meta.
	 *
	 * Checks for existing username, email, or mobile before creating the user.
	 * Returns a WP_Error if a conflict is found, otherwise returns the new user ID.
	 *
	 * @param string $username The desired username.
	 * @param string $password Optional. User password. Generates a random one if empty.
	 * @param string $email Optional. User email address.
	 * @param string $mobile Optional. User mobile number.
	 * @param array $meta Optional. Additional user meta to save.
	 *
	 * @return int|WP_Error New user ID on success, or WP_Error on failure.
	 */
	public static function create_user( $username, $password = '', $email = '', $mobile = '', $meta = [] ) {
		$mobile = Sanitizers::phone( $mobile );

		$user = get_user_by( 'login', $username );
		if( !$user ) {
			$user = get_user_by( 'email', $email );
			if( !$user ) {
				if( !empty( $mobile ) ) {
					$user = self::find_user_by_mobile( $mobile );
					if( $user ) {
						return new \WP_Error( 'mobile_exists', __( "This mobile is already exists.", 'mj-whitebox' ) );
					}
				}
			} else {
				return new \WP_Error( 'email_exists', __( "This email is already exists.", 'mj-whitebox' ) );
			}
		} else {
			return new \WP_Error( 'username_exists', __( "This username is already exists.", 'mj-whitebox' ) );
		}

		$data = [
			'user_login'	=> sanitize_user( $username ),
			'user_pass'		=> sanitize_text_field( $password ? $password : wp_generate_password() ),
			'user_email'	=> $email ? parent::convert_chars( $email, 'sanitize_email' ) : "",
			'meta_input'	=> [
				'mobile'			=> $mobile,
				'billing_phone'		=> $mobile,
				'shipping_phone'	=> $mobile,
			]
		];
		if( $meta ) {
			$data['meta_input'] = array_merge( $data['meta_input'], $meta );
		}
		$user_id = wp_insert_user( $data );
		if( !is_wp_error( $user_id ) ) {
			update_user_meta( $user_id, 'has_password', !empty( $password ) );
		}
		return $user_id;
	}

	/**
	 * Retrieve the avatar ID for a user.
	 *
	 * Gets the 'avatar' meta field and returns it as an absolute integer.
	 *
	 * @param int|string $user_id Optional. User ID or value resolvable to a user ID. Defaults to current user.
	 *
	 * @return int Avatar attachment ID, or 0 if not set.
	 */
	public static function get_avatar_id( $user_id = 0 ) {
		$user_id = self::get_user_id( $user_id );
		return absint( get_user_meta( $user_id, 'avatar', true ) );
	}

	/**
	 * Save or update a user's avatar ID.
	 *
	 * Stores the avatar attachment ID in the 'avatar' user meta field.
	 *
	 * @param int $avatar_id Avatar attachment ID to save.
	 * @param int|string $user_id Optional. User ID or value resolvable to a user ID. Defaults to current user.
	 *
	 * @return void
	 */
	public static function save_avatar_id( $avatar_id, $user_id = 0 ) {
		$user_id = self::get_user_id( $user_id );
		update_user_meta( $user_id, 'avatar', $avatar_id );
	}
}