<?php
namespace DrPlus\Utils;

use DrPlus\Utils;

class User extends Utils {
	public static function get_wishlist_products( $user_id = 0, $retrieve = 'ids', array $get_products_args = [] ) {
		$user_id = parent::get_user_id( $user_id );
		static $products = null;
		if( $products === null ) {
			$products = Wishlist::get_user_wishlist( $user_id )->toArray();
			if( !empty( $products ) ) {
				if( $retrieve == 'ids' ) {
					$products = wp_list_pluck( $products, 'product_id' );
				} else {
					$get_products_args['include'] = wp_list_pluck( $products, 'product_id' );
					$products = wc_get_products( $get_products_args );
				}
			}
		}

		return $products;
	}

	public static function get_account_menu_items() {
		$account_btn_items = [];
		if( is_user_logged_in() ) {
			$home_url = home_url();
			$my_account_link = parent::is_wc_active() ? get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) : $home_url;
			if( has_nav_menu( 'account-menu' ) ) {
				$menu_args = (object)['theme_location' => 'account-menu'];
				foreach( parent::get_nav_menu_items_by_location( 'account-menu' ) as $menu_item ) {
					$account_btn_items[] = [
						'label'	=> apply_filters( 'nav_menu_item_title', $menu_item->post_title ?: $menu_item->title, $menu_item, $menu_args, 1),
						'link'	=> $menu_item->url,
						'icon'	=> UI::get_menu_icon( $menu_item->ID, 'icon' ),
					];
				}
			} else {
				$account_btn_items = [
					[
						'label'	=> __( 'Dashboard', 'drplus' ),
						'link'	=> $my_account_link,
						'icon'	=> 'drplus-icon-element-3',
					]
				];
				if( parent::is_wc_active() ) {
					$account_btn_items = array_merge( $account_btn_items, [
						[
							'label'	=> __( 'Your purchases', 'drplus' ),
							'link'	=> esc_url( wc_get_endpoint_url( 'orders' ) ),
							'icon'	=> 'drplus-icon-bag-1',
						],
						[
							'label'	=> __( 'Downloads', 'drplus' ),
							'link'	=> esc_url( wc_get_endpoint_url( 'downloads' ) ),
							'icon'	=> 'drplus-icon-download',
						],
						[
							'label'	=> __( 'Addresses', 'drplus' ),
							'link'	=> esc_url( wc_get_endpoint_url( 'edit-address' ) ),
							'icon'	=> 'drplus-icon-location',
						],
						[
							'label'	=> _x( 'Wishlist', 'Plural', 'drplus' ),
							'link'	=> $my_account_link . "/wishlist",
							'icon'	=> 'drplus-icon-heart',
						],
						[
							'label'	=> __( 'Edit account', 'drplus' ),
							'link'	=> esc_url( wc_get_endpoint_url( 'edit-account' ) ),
							'icon'	=> 'drplus-icon-message-edit',
						],
						[
							'label'	=> __( 'Logout', 'drplus' ),
							'link'	=> parent::is_wc_active() ? wc_logout_url( $home_url ) : wp_logout_url( $home_url ),
							'icon'	=> 'drplus-icon-logout',
						],
					]);
				}
			}
		} else {
			$default_options = [
				'show-login-item'	=> true,
				'login-text'		=> esc_html__( "Login", 'drplus' ),
				'login-icon'		=> "drplus-icon-login",
				'guest-login-url'	=> home_url( "?login=true" ),
				
				'show-signup-item'	=> true,
				'signup-text'		=> esc_html__( "Signup", 'drplus' ),
				'signup-icon'		=> "drplus-icon-user-add",
				'signup-link'		=> home_url( "?login=true&section=signup" ),
			];
			$auth_options = Options::get_options( [
				'auth'				=> true,
				'auth_sms'			=> true,
			] );
			if( parent::to_bool( $auth_options['auth'] ) && parent::to_bool( $auth_options['auth_sms'] ) ) {
				$default_options['signup-link'] = remove_query_arg( 'section', $default_options['signup-link'] );
			}
			$options = Options::get_options( $default_options );
			$account_btn_items = [
				'login'	=> [
					'label'	=> $options['login-text'],
					'link'	=> $options['guest-login-url'],
					'icon'	=> $options['login-icon'],
				],
				'signup'	=> [
					'label'	=> $options['signup-text'],
					'link'	=> $options['signup-link'],
					'icon'	=> $options['signup-icon'],
				],
			];

			if( !parent::to_bool( $options['show-login-item'] ) ) {
				unset( $account_btn_items['login'] );
			}
			if( !parent::to_bool( $options['show-signup-item'] ) ) {
				unset( $account_btn_items['signup'] );
			}
		}
		return $account_btn_items;
	}

	public static function get_avatar_id( $user_id = 0 ) {
		$user_id = parent::get_user_id( $user_id );
		return absint( get_user_meta( $user_id, 'avatar', true ) );
	}

	public static function save_avatar_id( $avatar_id, $user_id = 0 ) {
		$user_id = parent::get_user_id( $user_id );
		update_user_meta( $user_id, 'avatar', $avatar_id );
	}

	public static function get_phone( $user_id = 0 ) {
		$user_id = parent::get_user_id( $user_id );
		$user_phone = get_user_meta( $user_id, 'mobile', true );
		if( !$user_phone ) {
			$user_phone = get_user_meta( $user_id, 'billing_phone', true );
			if( !$user_phone ) {
				$user_phone = get_user_meta( $user_id, 'shipping_phone', true );
			}
		}
		return $user_phone;
	}

	public static function update_phone( $phone, $user_id = 0 ) {
		$phone = Sanitizers::phone( $phone );
		$user_id = parent::get_user_id( $user_id );
		update_user_meta( $user_id, 'mobile', $phone );
		update_user_meta( $user_id, 'billing_phone', $phone );
		update_user_meta( $user_id, 'shipping_phone', $phone );
	}

	public static function get_birthday( $user_id = 0, string $format = 'U' ) {
		$user_id = parent::get_user_id( $user_id );
		$birthday = get_user_meta( $user_id, 'birthday', true );
		if( !is_numeric( $birthday ) ) {
			$birthday = strtotime( $birthday );
		}
		return date_i18n( $format, $birthday );
	}

	public static function get_nid( $user_id = 0 ) {
		$user_id = parent::get_user_id( $user_id );
		return get_user_meta( $user_id, 'nid', true );
	}

	public static function get_gender( $user_id = 0 ) {
		$user_id = parent::get_user_id( $user_id );
		return parent::ensure_values_in_array( get_user_meta( $user_id, 'gender', true ), ['male', 'female'], 'male' );
	}

	public static function get_email( $user = null ) {
		$user = parent::get_user_object( $user );
		return $user ? $user->user_email : '';
	}

	public static function find_user_by_mobile( string $mobile ) {
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
			] );
			$user = !empty( $user[0] ) ? $user[0] : '';
		}
		return $user;
	}

	/**
	 * Update user meta and user data
	 *
	 * @param array $data
	 * @param mixed $user_id
	 * @param boolean $start_transaction
	 * @return boolean
	 */
	public static function update_user( array $data, $user_id = 0, bool $start_transaction = true ) : bool {
		$user_id = parent::get_user_id( $user_id );

		if( $start_transaction ) {
			global $wpdb;
			$wpdb->query( "START TRANSACTION" );
		}

		$meta_keys = ['first_name', 'last_name', 'birthday', 'specialist_code', 'nid', 'mobile', 'gender', 'avatar', 'height', 'weight', 'blood_type'];
		foreach( $meta_keys as $meta_key ) {
			if( isset( $data[$meta_key] ) ) {
				$data[$meta_key] = parent::convert_chars( $data[$meta_key] );
				// Sanitize
				if( $meta_key == 'birthday' ) {
					if( is_numeric( $data[$meta_key] ) ) {
						$data[$meta_key] = date_i18n( "Y-m-d", $data[$meta_key] );
					}
					$data[$meta_key] = Date::maybe_j2g( $data[$meta_key] );
				} else if( $meta_key == 'nid' ) {
					if( !Validators::id_code( $data[$meta_key] ) ) {
						if( !empty( $_POST['action'] ) && 'save_account_details' === $_POST['action'] ) {
							wc_add_notice( __( 'National ID is not valid.', 'drplus' ), 'error' );
						}
						continue;
					}
				} else if( $meta_key == 'mobile' ) {
					$data[$meta_key] = Utils::convert_chars( $data[$meta_key] );
				} else if( $meta_key == 'gender' ) {
					$data[$meta_key] = parent::ensure_values_in_array( $data[$meta_key], ['male', 'female'], 'male' );
				} else if( $meta_key == 'avatar' ) {
					$data[$meta_key] = absint( $data[$meta_key] );
				}
				if( $meta_key == 'mobile' ) {
					self::update_phone( $data[$meta_key], $user_id );
				} else {
					update_user_meta( $user_id, $meta_key, $data[$meta_key] );
				}
			}
		}

		// Update user data
		$user_data_keys = ['email'];
		// Update display_name
		if( isset( $data['first_name'] ) || isset( $data['last_name'] ) ) {
			$first_name = $data['first_name'] ?? get_user_meta( $user_id, 'first_name', true );
			$last_name = $data['last_name'] ?? get_user_meta( $user_id, 'last_name', true );
			$data['display_name'] = "{$first_name} {$last_name}";
			$user_data_keys[] = 'display_name';
		}
		$user_data = [
			'ID'	=> $user_id,
		];
		foreach( $user_data_keys as $user_data_key ) {
			if( isset( $data['email'] ) ) {
				$user_data['user_email'] = parent::convert_chars( $data['email'], true, 'sanitize_email' );
			}
			if( isset( $data['display_name'] ) ) {
				$user_data['display_name'] = parent::convert_chars( $data['display_name'] );
			}
		}
		wp_update_user( $user_data );

		if( $start_transaction ) {
			$wpdb->query( 'COMMIT' );
		}

		return true;
	}

	public static function add_recently_visited_specialist( $user_id = 0, $specialist_id = 0 ) {
		$user_id = parent::get_user_id( $user_id );
	
		// if user is guest save in cookie
		$recently_visited = self::get_user_recently_visited_specialists_ids( $user_id );
		// Add specialist id to first position
		array_unshift( $recently_visited, $specialist_id );
		// Remove duplicates
		$recently_visited = array_unique( $recently_visited );
		// Remove oldest 10
		if( count( $recently_visited ) > 10 ) {
			$recently_visited = array_slice( $recently_visited, 0, 10 );
		}

		if( !$user_id ) {
			$recently_visited = json_encode( $recently_visited );
			setcookie( 'drplus_recent_specialists_visited', $recently_visited, time() + 60 * 60 * 24 * 30, COOKIEPATH, COOKIE_DOMAIN );
		} else {
			update_user_meta( $user_id, '_drplus_recent_specialists_visited', $recently_visited );
		}
	}

	public static function get_user_recently_visited_specialists_ids( $user_id = 0 ) {
		$user_id = parent::get_user_id( $user_id );
		$recently_visited = [];
		if( !$user_id ) {
			$recently_visited = isset( $_COOKIE['drplus_recent_specialists_visited'] ) ? json_decode( stripslashes( $_COOKIE['drplus_recent_specialists_visited'] ), true ) : [];
		} else {
			$recently_visited = get_user_meta( $user_id, '_drplus_recent_specialists_visited', true );
		}
		if( !is_array( $recently_visited ) ) {
			$recently_visited = [];
		}
		return $recently_visited;
	}

	public static function order_by( $add_auto_option = false ) {
		$order_by = [
			'ID'					=> esc_html__( 'ID', 'drplus' ),
			'display_name'			=> esc_html__( 'User display name', 'drplus' ),
			'user_login'			=> esc_html__( 'Username', 'drplus' ),
			'user_registered'		=> esc_html__( 'Registered date', 'drplus' ),
			'rand'					=> esc_html__( 'Random', 'drplus' ),
			'first_name_alphabetic'	=> esc_html__( 'First Name (A-Z)', 'drplus' ),
			'first_name_reverse'	=> esc_html__( 'First Name (Z-A)', 'drplus' ),
			'last_name_alphabetic'	=> esc_html__( 'Last Name (A-Z)', 'drplus' ),
			'last_name_reverse'		=> esc_html__( 'Last Name (Z-A)', 'drplus' ),
		];

		if( $add_auto_option ) $order_by['auto'] = esc_html__( 'Auto (From URL)', 'drplus' );

		return $order_by;
	}

	public static function get_user_appointments_reviews( $user_id = 0 ) {
		$user_id = parent::get_user_id( $user_id );
		$appointments_reviews = [];
		if( $user_id ) {
			$appointments_reviews = get_user_meta( $user_id, 'appointments_reviews', true );
			if( !is_array( $appointments_reviews ) ) {
				$appointments_reviews = [];
			}
		}
		return $appointments_reviews;
	}

	public static function update_user_appointments_reviews( $order_id, $review_id, $user_id = 0 ) {
		$user_id = parent::get_user_id( $user_id );

		if( $user_id ) {
			$appointments_reviews = self::get_user_appointments_reviews( $user_id );
			$appointments_reviews[$order_id] = $review_id;
			update_user_meta( $user_id, 'appointments_reviews', $appointments_reviews );
		}
	}

	public static function change_display_name( $user_object, $sms_settings = [] ) {
		$options = Options::get_options( [
			'sms'	=> true,
		] );
		if( !$options['sms'] ) return false;
		if( empty( $sms_settings ) ) {
			$sms_settings = SMS::get_settings();
		}
		$display_name = $user_object->display_name;
		if( $sms_settings['security']['hide_mobile'] == 'mid_star' ) {
			$display_name = Utils::convert_chars( $display_name );
			if( is_rtl() ) {
				$display_name = substr( $display_name, 7, 4 ) . "***" . substr( $display_name, 0, 4 );
			} else {
				$display_name = substr( $display_name, 0, 4 ) . "***" . substr( $display_name, 7, 4 );
			}
		} else if( $sms_settings['security']['hide_mobile'] == 'end_star' ) {
			$display_name = Utils::convert_chars( $display_name );
			if( is_rtl() ) {
				$display_name = substr( $display_name, 0, 7 ) . "****";
			} else {
				$display_name = "****" . substr( $display_name, 0, 7 );
			}
		} else if( $sms_settings['security']['hide_mobile'] == 'sitename' ) {
			$display_name = get_bloginfo( 'blogname' );
		} else if( $sms_settings['security']['hide_mobile'] == 'custom' && !empty( $sms_settings['security']['hide_mobile_custom'] ) ) {
			$display_name = parent::apply_general_variables( $sms_settings['security']['hide_mobile_custom'] );
		}
		$update = wp_update_user( [
			'ID'			=> $user_object->ID,
			'display_name'	=> $display_name,
		] );
		if( !$update || is_wp_error( $update ) ) {
			return false;
		}
		return true;
	}

	public static function check_display_name_hidden( $string ) {
		$pattern = '/^\d{4}\*\*\*\d{4}$/';
		$pattern2 = '/^\d{7}\*{4}$/';
		if( preg_match( $pattern, $string ) || preg_match( $pattern2, $string ) ) {
			return true;
		} else {
			return false;
		}
	}

	public static function get_blood_type( $user_id = 0 ) {
		$user_id = parent::get_user_id( $user_id );
		return get_user_meta( $user_id, 'blood_type', true );
	}

	public static function get_height( $user_id = 0 ) {
		$user_id = parent::get_user_id( $user_id );
		return get_user_meta( $user_id, 'height', true );
	}

	public static function get_weight( $user_id = 0 ) {
		$user_id = parent::get_user_id( $user_id );
		return get_user_meta( $user_id, 'weight', true );
	}

	public static function get_specialist_code( $user_id = 0 ) {
		$user_id = parent::get_user_id( $user_id );
		return get_user_meta( $user_id, 'specialist_code', true );
	}

	public static function create_user( $username, $password = '', $email = '', $mobile = '', $meta = [] ) {
		$mobile = Sanitizers::phone( $mobile );

		$user = get_user_by( 'login', $username );
		if( !$user ) {
			$user = get_user_by( 'email', $email );
			if( !$user ) {
				if( !empty( $mobile ) ) {
					$user = self::find_user_by_mobile( $mobile );
					if( $user ) {
						return new \WP_Error( 'mobile_exists', __( "This mobile is already exists.", 'drplus' ) );
					}
				}
			} else {
				return new \WP_Error( 'email_exists', __( "This email is already exists.", 'drplus' ) );
			}
		} else {
			return new \WP_Error( 'username_exists', __( "This username is already exists.", 'drplus' ) );
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
}