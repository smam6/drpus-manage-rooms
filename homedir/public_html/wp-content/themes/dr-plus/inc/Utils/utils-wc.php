<?php
namespace DrPlus\Utils;

use DrPlus\Utils;

class WC extends Utils {
	public static function get_wc_shop_status() {
		$enable_wc_shop = Options::get_options( ['enable_wc_shop' => true] )['enable_wc_shop'];
		return $enable_wc_shop;
	}

	public static function default_attribute_settings() {
		return [
			'display_type'	=> 'select',
			'icon'			=> '',
		];
	}
	
	public static function get_attribute_settings( int $id ) {
		$settings = get_option( "drplus_wc_attr_{$id}", self::default_attribute_settings() );
		if( !is_array( $settings ) ) $settings = [];
		return parent::check_default( $settings, self::default_attribute_settings() );
	}

	public static function update_attribute_settings( int $id, array $settings ) {
		$settings = parent::check_default( $settings, self::default_attribute_settings() );
		update_option( "drplus_wc_attr_{$id}", $settings, false );
	}

	public static function get_term_color( int $id ) {
		return sanitize_hex_color( get_term_meta( $id, '_drplus_color', true ) );
	}

	public static function update_term_color( int $id, string $color ) {
		update_term_meta( $id, '_drplus_color', $color );
	}

	public static function get_term_img( int $id ) {
		return absint( get_term_meta( $id, '_drplus_img', true ) );
	}

	public static function update_term_img( int $id, $img ) {
		update_term_meta( $id, '_drplus_img', absint( $img ) );
	}

	public static function my_account_custom_links() {
		$links = [
			'appointments'			=> _x( "Your reservations", 'My Account Link', 'drplus' ),
			'chats'					=> __( 'Chats', 'drplus' ),
			'specialist-dashboard'	=> UtilsSpecialists::is_user_specialist( 0, true ) ? _x( "Specialist dashboard", 'My Account Link', 'drplus' ) : _x( "Request a specialist panel", 'My Account Link', 'drplus' ),
			'notifications'			=> _x( "Notifications", 'My Account Link', 'drplus' ),
			// 'tickets'				=> _x( "Your tickets", 'My Account Link', 'drplus' ),
			'wishlist'				=> _x( "Wishlist", 'My Account Link', 'drplus' ),
		];
		$options = Options::get_options( [
			'enable-booking'				=> true,
			'notifications'					=> true,
			'booking-active-online-visits'	=> [],
			'tickets'						=> true,
			'wishlist'						=> true,
			'specialist_onboard'			=> true,
		] );
		$unset = [];
		if( !parent::to_bool( $options['enable-booking'] ) ) {
			$unset[] = 'appointments';
			$unset[] = 'chats';
			} else {
				if( empty( $options['booking-active-online-visits']['chat_consultation'] ) && empty( $options['booking-active-online-visits']['instant_chat_consultation'] ) ) {
					$unset[] = 'chats';
				}
			}
		if( !parent::to_bool( $options['notifications'] ) ) {
			$unset[] = 'notifications';
		}
		if( !parent::to_bool( $options['tickets'] ) ) {
			$unset[] = 'tickets';
		}
		if( !parent::to_bool( $options['wishlist'] ) ) {
			$unset[] = 'wishlist';
		}
		if( !parent::to_bool( $options['specialist_onboard'] ) ) {
			if( !UtilsSpecialists::is_user_specialist() ) {
				$unset[] = 'specialist-dashboard';
			}
		}

		if( !empty( $unset ) ) {
			$links = parent::unset( $links, $unset );
		}
		return $links;
	}

	public static function my_account_menu_link_icons() {
		$icons = [
			'dashboard'				=> 'element-3',
			'orders'				=> 'bag-1',
			'appointments'			=> 'edit',
			'chats'					=> 'messages-2',
			'specialist-dashboard'	=> 'stethoscope',
			'downloads'				=> 'download',
			'notifications'			=> 'notification',
			'tickets'				=> 'document-text',
			'edit-address'			=> 'location',
			'wishlist'				=> 'heart',
			'edit-account'			=> 'message-edit',
			'sheyda-wallet'			=> 'wallet',
			'customer-logout'		=> 'logout',
		];
		$items = [];
		foreach( array_keys( wc_get_account_menu_items() ) as $endpoint ) {
			$items[$endpoint] = $icons[$endpoint] ?? "documentmoney";
		}

		foreach( WC::specialist_profile_sections() as $key => $value ) {
			$items['specialist-dashboard/' . $key] = $value['icon'];
		}

		$options = Options::get_options( [
			'enable-booking'				=> true,
			'notifications'					=> true,
			'booking-active-online-visits'	=> [],
			'tickets'						=> true,
			'wishlist'						=> true,
		] );
		$unset = [];
		if( !$options['enable-booking'] ) {
			$unset[] = 'appointments';
			$unset[] = 'chats';
		} else {
			if( empty( $options['booking-active-online-visits']['chat_consultation'] ) && empty( $options['booking-active-online-visits']['instant_chat_consultation'] ) ) {
				$unset[] = 'chats';
			}
		}
		if( !$options['notifications'] ) {
			$unset[] = 'notifications';
		}
		if( !$options['tickets'] ) {
			$unset[] = 'tickets';
		}
		if( !$options['wishlist'] ) {
			$unset[] = 'wishlist';
		}
		if( !empty( $unset ) ) {
			$items = parent::unset( $items, $unset );
		}

		return apply_filters( "drplus/wc/my-account/links/icons", $items );
	}

	public static function specialist_profile_sections() : array {
		$sections = [
			'specialist-appointments'	=> [
				'label'	=> __( "Appointments", 'drplus' ),
				'icon'	=> 'calendar-2',
			],
			'specialist-chats'	=> [
				'label'	=> __( "Chats", 'drplus' ),
				'icon'	=> 'messages-2',
			],
			'personal'		=> [
				'label'	=> __( "Edit personal info", 'drplus' ),
				'icon'	=> 'author',
			],
			'identity'		=> [
				'label'	=> __( "Identity documents", 'drplus' ),
				'icon'	=> 'copy-2',
			],
			'services'		=> [
				'label'	=> __( "Specialized Services", 'drplus' ),
				'icon'	=> 'stethoscope',
			],
			'insurances'	=> [
				'label'	=> __( "Insurances", 'drplus' ),
				'icon'	=> 'element-3',
			],
			'offices'		=> [
				'label'	=> __( "Offices", 'drplus' ),
				'icon'	=> 'hospital',
			],
			'certificates'	=> [
				'label'	=> __( "Certificates and Courses", 'drplus' ),
				'icon'	=> 'verify',
			],
			'faqs'			=> [
				'label'	=> __( "FAQs", 'drplus' ),
				'icon'	=> 'menu',
			],
			'reserve'		=> [
				'label'	=> __( "Reservation Settings", 'drplus' ),
				'icon'	=> 'archive-book',
			],
			'financial'		=> [
				'label'	=> __( "Financial Information", 'drplus' ),
				'icon'	=> 'coin',
			],
		];

		$options = Options::get_options( [
			'insurance'	=> true,
		] );

		if( !$options['insurance'] || empty( UtilsSpecialists::get_insurances_terms() ) ) {
			unset( $sections['insurances'] );
		}

		if( empty( UtilsSpecialists::get_identity_types_terms() ) ) { // Skip the identity step when identity types is empty
			unset( $sections['identity'] );
		}

		// Check for subscription plan
		$subs_plans = SubscriptionPlans::get_settings();
		if( $subs_plans['enable'] ) {
			// Add plan item to nav items
			$sections['subscription'] = [
				'label'	=> __( "Subscription plans", 'drplus' ),
				'icon'	=> 'archive-book',
			];

			// Get specialist plan expire date
			$user_subs_plan = SubscriptionPlans::get_specialist_plan( get_current_user_id() );

			if( $user_subs_plan['plan_expired'] ) {
				$removed_nav_items = [
					'specialist-appointments',
					'specialist-chats',
					'services',
					'insurances',
					'offices',
					'reserve',
					'faqs',
					'certificates',
				];
				$removed_nav_items = apply_filters( 'drplus/specialist/dashboard/removed_nav_items_for_expired_plans', $removed_nav_items, $user_subs_plan, get_current_user_id() );
				foreach( $removed_nav_items as $item ) {
					if( isset( $sections[$item] ) ) unset( $sections[$item] );
				}
			}

		}

		return apply_filters( 'drplus/wc/specialist/profile/sections', $sections );
	}

	public static function get_current_specialist_profile_section() : string {
		static $section = null;
		if( $section === null ) {
			$section = parent::convert_chars( get_query_var( 'specialist-dashboard', 'dashboard' ) );
			$section = explode( '/', $section )[0];
			if( empty( $section ) || !isset( self::specialist_profile_sections()[$section] ) ) {
				$section = 'dashboard';
			}
		}
		return $section;
	}

	public static function get_product_badge( $product_id = 0 ) {
		$product_id = parent::get_post_id( $product_id );
		$options = Options::get_options( [
			'product_badge'	=> true,
		] );
		if( !Utils::to_bool( $options['product_badge'] ) ) return [];
		$terms = get_the_terms( $product_id, 'product-badge' );
		if( is_wp_error( $terms ) || empty( $terms[0] ) ) return [];
		$image = absint( get_term_meta( $terms[0]->term_id, 'badge_image', true ) );
		if( !$image ) return [];
		return [
			'image'	=> $image,
			'name'	=> $terms[0]->name
		];
	}

	public static function attr_display_types() {
		static $types = null;
		if( $types === null ) {
			$types = [
				'select'	=> __( 'Dropdown list', 'drplus' ),
				'color'		=> __( 'Color', 'drplus' ),
				'image'		=> __( 'Image', 'drplus' ),
			];
		}
		return $types;
	}
}