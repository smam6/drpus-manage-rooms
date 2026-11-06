<?php

use DrPlus\Utils;
use DrPlus\Utils\Formatters;
use DrPlus\Utils\Options;
use DrPlus\Utils\SMS;
use DrPlus\Utils\User;
use DrPlus\Utils\UtilsSpecialists;
use DrPlus\Utils\WC;
use DrPlus\Model\OTP;
use DrPlus\Utils\Date;
use DrPlus\Utils\Sanitizers;

if( !function_exists( "drplus_wc_my_account_items" ) ) {
	function drplus_wc_my_account_items( $menu_links ) {
		$menu_links = array_merge( $menu_links, WC::my_account_custom_links() );
		$endpoint = Utils::get_wc_account_endpoint( $menu_links );

		if( $endpoint != 'specialist-dashboard' ) {
			// get wc shop status
			$enable_wc_shop = WC::get_wc_shop_status();
			$links_orders = [
				'dashboard',
				'specialist-dashboard',
				'orders',
				'appointments',
				'chats',
				'downloads',
				'notifications',
				'tickets',
				'edit-address',
				'wishlist',
				'edit-account',
				'customer-logout',
			];

			$options = Options::get_options( [
				'enable-booking'				=> true,
				'booking-active-online-visits'	=> [],
				'notifications'					=> true,
				'tickets'						=> true,
				'wishlist'						=> true,
			] );
			$unset = [];
			if( !Utils::to_bool( $options['enable-booking'] ) ) {
				$unset[] = 'appointments';
				$unset[] = 'chats';
			} else {
				if( empty( $options['booking-active-online-visits']['chat_consultation'] ) && empty( $options['booking-active-online-visits']['instant_chat_consultation'] ) ) {
					$unset[] = 'chats';
				}
			}
			if( !Utils::to_bool( $options['notifications'] ) ) {
				$unset[] = 'notifications';
			}
			if( !Utils::to_bool( $options['tickets'] ) ) {
				$unset[] = 'tickets';
			}
			if( !Utils::to_bool( $options['wishlist'] ) ) {
				$unset[] = 'wishlist';
			}
			if( !$enable_wc_shop ) {
				$unset[] = 'orders';
				$unset[] = 'downloads';
				$unset[] = 'wishlist';
			}
			
			if( !empty( $unset ) ) {
				$menu_links = Utils::unset( $menu_links, $unset, [] );
			}
			$links_orders = array_values( array_intersect( $links_orders, array_keys( $menu_links ) ) );

			foreach( array_keys( $menu_links ) as $link ) {
				Utils::reposition_array_element( $menu_links, $link, array_search( $link, $links_orders ) );
			}

			if( array_key_exists( 'specialist-dashboard', $menu_links ) && !UtilsSpecialists::is_user_specialist( 0, true ) ) {
				Utils::reposition_array_element( $menu_links, 'specialist-dashboard', count( $menu_links )-2 ); // move to the last of list before logout button
			}
		} else {
			$menu_links = [];
			foreach( WC::specialist_profile_sections() as $key => $value ) {
				$menu_links['specialist-dashboard/' . $key] = $value['label'];
			}
			$menu_links = array_merge( [
				'dashboard'					=> __( "Dashboard", 'drplus' ),
				'specialist-dashboard'		=> _x( "Specialist dashboard", 'My Account Link', 'drplus' ),
			], $menu_links );
		}

		return $menu_links;
	}
}
add_filter( 'woocommerce_account_menu_items', 'drplus_wc_my_account_items' );

if( !function_exists( "drplus_wc_my_account_links_endpoint" ) ) {
	function drplus_wc_my_account_links_endpoint() {
		foreach( array_keys( WC::my_account_custom_links() ) as $link ) {
			add_rewrite_endpoint( $link, EP_PAGES );
		}
	}
}
add_action( 'init', 'drplus_wc_my_account_links_endpoint' );

if( !function_exists( "drplus_wc_my_account_start" ) ) {
	function drplus_wc_my_account_start() {
		$endpoint = Utils::get_wc_account_endpoint();
		if( $endpoint != 'dashboard' ) {
			echo "<div class=\"drplus-myaccount-section drplus-myaccount-section-{$endpoint}\">";
		}
	}
}
add_action( 'woocommerce_account_content', 'drplus_wc_my_account_start', 7 );

if( !function_exists( "drplus_wc_my_account_end" ) ) {
	function drplus_wc_my_account_end() {
		$endpoint = Utils::get_wc_account_endpoint();
		if( $endpoint != 'dashboard' ) {
			echo '</div>';
		}
	}
}
add_action( 'woocommerce_account_content', 'drplus_wc_my_account_end', 999 );

if( !function_exists( "drplus_wc_my_account_redirect_guest" ) ) {
	function drplus_wc_my_account_redirect_guest() {
		$options = Options::get_options( [
			'auth'				=> true,
			'auth_sms'			=> true,
			'auth_email'		=> true,
			'guest-login-url'	=> home_url( "?login=true" ),
		] );
		
		if( !Utils::to_bool( $options['auth'] ) || ( !Utils::to_bool( $options['auth_sms'] ) && !Utils::to_bool( $options['auth_email'] ) ) ) return;

		if( is_account_page() ) {
			wp_redirect( $options['guest-login-url'] );
			die;
		}
	}
}
if( !is_user_logged_in() ) {
	add_action( 'template_redirect', 'drplus_wc_my_account_redirect_guest' );
}

if( !function_exists( "drplus_modify_my_account_my_orders_actions" ) ) {
	function drplus_modify_my_account_my_orders_actions( $actions ) {
		if( isset( $actions['view'] ) ) {
			$actions['view']['name'] = __( 'View', 'drplus' );
		}
		return $actions;
	}
}
add_filter( 'woocommerce_my_account_my_orders_actions', 'drplus_modify_my_account_my_orders_actions' );

if( !function_exists( "drplus_modify_woocommerce_my_account_edit_address_title" ) ) {
	function drplus_modify_woocommerce_my_account_edit_address_title( $title ) {
		return sprintf( __( 'Edit %s', 'drplus' ), $title );
	}
}
add_filter( 'woocommerce_my_account_edit_address_title', 'drplus_modify_woocommerce_my_account_edit_address_title' );

if( !function_exists( "drplus_edit_account_title" ) ) {
	function drplus_edit_account_title() {
		?>
		<h2 class="drplus-edit-account-title drplus-myaccount-page-title"><?php echo esc_html( __( 'Edit Account', 'drplus' ) ); ?></h2>
		<?php
	}
}
add_action( 'woocommerce_before_edit_account_form', 'drplus_edit_account_title' );

// Custom links functions
$custom_links = WC::my_account_custom_links();
if( !function_exists( "drplus_wc_my_account_appointments_content" ) && isset( $custom_links['appointments'] ) ) {
	function drplus_wc_my_account_appointments_content() {
		include( DRPLUS_DIR . "woocommerce/myaccount/custom-links/appointments.php" );
	}
}
if( !function_exists( "drplus_wc_my_account_specialist_dashboard_content" ) && isset( $custom_links['chats'] ) ) {
	function drplus_wc_my_account_chats_content() {
		include( DRPLUS_DIR . "woocommerce/myaccount/custom-links/chats.php" );
	}
}

if( !function_exists( "drplus_wc_my_account_notifications_content" ) && isset( $custom_links['notifications'] ) ) {
	function drplus_wc_my_account_notifications_content() {
		include( DRPLUS_DIR . "woocommerce/myaccount/custom-links/notifications.php" );
	}
}
if( !function_exists( "drplus_wc_my_account_tickets_content" ) && isset( $custom_links['tickets'] ) ) {
	function drplus_wc_my_account_tickets_content() {
		include( DRPLUS_DIR . "woocommerce/myaccount/custom-links/tickets.php" );
	}
}
if( !function_exists( "drplus_wc_my_account_wishlist_content" ) && isset( $custom_links['wishlist'] ) ) {
	function drplus_wc_my_account_wishlist_content() {
		include( DRPLUS_DIR . "woocommerce/myaccount/custom-links/wishlist.php" );
	}
}
if( !function_exists( "drplus_wc_my_account_specialist_dashboard_content" ) && isset( $custom_links['specialist-dashboard'] ) ) {
	function drplus_wc_my_account_specialist_dashboard_content() {
		include( DRPLUS_DIR . "woocommerce/myaccount/custom-links/specialist_dashboard.php" );
	}
}

foreach( array_keys( WC::my_account_custom_links() ) as $my_account_link ) {
	$function = str_replace( "-", "_", $my_account_link );
	if( function_exists( "drplus_wc_my_account_{$function}_content" ) ) {
		add_action( "woocommerce_account_{$my_account_link}_endpoint", "drplus_wc_my_account_{$function}_content" );
	}
}

if( !function_exists( "drplus_wc_my_account_avatar" ) ) {
	function drplus_wc_my_account_avatar() {
		$user = wp_get_current_user();
		$user_custom_avatar = User::get_avatar_id( $user );
		?>
		<div class="woocommerce-form-row woocommerce-form-row--wide drplus-edit-avatar-wrap-row">
			<input type="hidden" name="drplus_account_avatar_id" id="account_avatar_id" value="<?php echo esc_attr( $user_custom_avatar ) ?>">
			<div class="drplus-edit-avatar-wrap" id="drplus-edit-avatar">
				<?php echo get_avatar( $user->ID, 96 ) ?>
				<i class="drplus-icon-edit drplus-edit-avatar-icon"></i>
				<?php if( !empty( $user_custom_avatar ) ) { ?>
					<i class="drplus-icon-trash drplus-delete-avatar-icon"></i>
				<?php } ?>
			</div>
		</div>
		<?php
	}
}
add_action( 'woocommerce_edit_account_form_start', 'drplus_wc_my_account_avatar' );

if( !function_exists( "drplus_wc_my_account_fields" ) ) {
	function drplus_wc_my_account_fields() {
		$options = Options::get_options( [
			'sms'				=> true,
			'auth'				=> true,
			'auth_sms'			=> true,
			'use-outside-iran'	=> false,
		] );

		$user_id = get_current_user_id();
		$birthday = User::get_birthday( $user_id );
		$nid = User::get_nid( $user_id );
		$phone = User::get_phone( $user_id );
		?>
		<div class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="drplus_birthday"><?php esc_html_e( 'Birthday', 'drplus' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
			<input
				type="text"
				class="woocommerce-Input input-text drplus-datepicker-input drplus-datepicker-input"
				id="drplus_birthday"
				data-time="<?php echo esc_attr( $birthday ) ?>"
				aria-required="true"
				readonly
				<?php echo UtilsSpecialists::is_user_specialist( $user_id ) ? 'data-options=\'' . wp_json_encode( ['maxDate' => strtotime( '-18 years' )*1000] ) . '\'' : '' ?>
			>
			<input type="hidden" name="drplus_birthday" id="drplus_birthday_alt" value="<?php echo esc_attr( $birthday ) ?>">
		</div>

		<div class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="drplus_nid"><?php esc_html_e( 'National ID', 'drplus' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
			<input
				type="text"
				class="woocommerce-Input input-text drplus-nid-input drplus-numeric-input input-ltr"
				id="drplus_nid"
				name="drplus_nid"
				value="<?php echo esc_attr( $nid ) ?>"
				aria-required="true"
				inputmode="numeric"
				minlength="10"
				maxlength="10"
			>
		</div>

		<div class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<div class="drplus_mobile-wrap">
				<label for="drplus_mobile"><?php esc_html_e( 'Phone number', 'drplus' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
				<input
					type="text"
					class="woocommerce-Input input-text <?php echo !$options['use-outside-iran'] ? 'drplus-phone-input' : 'drplus-numeric-input' ?> input-ltr"
					id="drplus_mobile"
					name="drplus_mobile"
					value="<?php echo esc_attr( !$options['use-outside-iran'] ? Formatters::phone( $phone ) : $phone ) ?>"
					aria-required="true"
					autocomplete="tel"
					inputmode="tel"
					<?php if( !$options['use-outside-iran'] ) { ?>
						minlength="13"
						maxlength="13"
						placeholder="09..."
					<?php } ?>
				>
				<div class="input-error">
					<i class="drplus-icon-error"></i>
					<span class="input-error-text"></span>
				</div>
			</div>
			<?php
			if( !$options['use-outside-iran'] && $options['sms'] && $options['auth'] && $options['auth_sms'] ) {
				$sms_settings = SMS::get_settings();
				?>
				<div class="drplus_otp_code-wrap" style="display:none">
					<label for="drplus_otp_code"><?php _e( 'Enter the verification code sent to number <span class="auth-otp-number"></span>', 'drplus' ) ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
					<input
						type="text"
						class="woocommerce-Input input-text drplus-numeric-input input-ltr"
						id="drplus_otp_code"
						name="drplus_otp_code"
						aria-required="true"
						autocomplete="one-time-code"
						inputmode="numeric"
						maxlength="4"
						minlength="4"
					>
					<div class="input-error">
						<i class="drplus-icon-error"></i>
						<span class="input-error-text"></span>
					</div>
					<div class="drplus_otp-timer" style="display:none"><?php echo Utils::second_to_string( $sms_settings['settings']['auth']['login']['otp_timer'] ) ?></div>
					<div class="drplus_otp-timer-resend outline" tabindex="6" style="display:none"><?php esc_html_e( 'Resend code', 'drplus' ) ?></div>
				</div>
			<?php } ?>
		</div>
		<?php
	}
}
add_action( 'woocommerce_edit_account_form_fields', 'drplus_wc_my_account_fields' );

if( !function_exists( "drplus_wc_my_account_required_fields" ) ) {
	function drplus_wc_my_account_required_fields( $fields ) {
		$options = Options::get_options( [
			'auth_email_required'	=> true,
		] );
		if( !$options['auth_email_required'] ) {
			$fields = Utils::unset( $fields, ['account_email'] );
		}
		return $fields;
	}
}
add_filter( 'woocommerce_save_account_details_required_fields', 'drplus_wc_my_account_required_fields' );

if( !function_exists( "drplus_wc_save_account_details" ) ) {
	function drplus_wc_save_account_details( $user_id ) {
		$options = Options::get_options( [
			'auth_email_required'	=> true,
		] );

		$data_to_save = [];
		if( isset( $_POST['drplus_account_avatar_id'] ) ) {
			$data_to_save['avatar'] = Utils::convert_chars( $_POST['drplus_account_avatar_id'], true, 'absint' );
		}

		if( isset( $_POST['drplus_birthday'] ) ) {
			$data_to_save['birthday'] = Utils::convert_chars( $_POST['drplus_birthday'] );
		}
		if( isset( $_POST['drplus_nid'] ) ) {
			$data_to_save['nid'] = Utils::convert_chars( $_POST['drplus_nid'] );
		}
		if( isset( $_POST['drplus_mobile'] ) ) {
			$mobile = Sanitizers::phone( $_POST['drplus_mobile'] );
			$old_mobile = User::get_phone();
			if( $mobile != $old_mobile ) {
				$options = Options::get_options( [
					'auth'				=> true,
					'auth_sms'			=> true,
					'use-outside-iran'	=> false,
				] );
				if( !$options['use-outside-iran'] && $options['auth'] && $options['auth_sms'] ) {
					$otp = Utils::convert_chars( $_POST['drplus_otp_code'] );
					if( !empty( $otp ) ) {
						$find_otp = OTP::query()->where( [
							['mobile', $mobile],
							['otp', $otp],
							['expire', '>', new \DateTime( Date::maybe_j2g( wp_date( 'Y-m-d H:i:s' ) ) )],
						] )->first();
						if( !$find_otp ) {
							wc_add_notice( __( 'OTP code does not match', 'drplus' ), 'error' );
						} else {
							$user = User::find_user_by_mobile( $mobile );
							if( ( empty( $user ) || ( !empty( $user ) && $user->ID == get_current_user_id() ) ) && !is_wp_error( $user ) ) {
								$data_to_save['mobile'] = $mobile;
							}
						}
					}
				} else {
					$data_to_save['mobile'] = $mobile;
				}
			}
		}

		if( !$options['auth_email_required'] && empty( $_POST['account_email'] ) ) {
			$data_to_save['email'] = '';
		}

		User::update_user( $data_to_save, $user_id );

		// Delete specialist cache
		if( UtilsSpecialists::is_user_specialist( $user_id ) !== false ) {
			$specialist = UtilsSpecialists::get_by_user_id( $user_id );
			UtilsSpecialists::delete_group_caches( [intval( $specialist->id )] );
		}
	}
}
add_action( 'woocommerce_save_account_details', 'drplus_wc_save_account_details' );

if( !function_exists( "drplus_specialist_account_menu_item_classes" ) ) {
	function drplus_specialist_account_menu_item_classes( $classes ) {
		$endpoint = Utils::get_wc_account_endpoint();
		if( $endpoint == 'specialist-dashboard' ) {
			$current_section = WC::get_current_specialist_profile_section();
			if( $current_section != 'dashboard' && in_array( 'woocommerce-MyAccount-navigation-link--specialist-dashboard', $classes ) && in_array( 'is-active', $classes ) ) {
				unset( $classes[array_search( 'is-active', $classes )] );
			}

			foreach( array_keys( WC::specialist_profile_sections() ) as $section ) {
				if( in_array( 'woocommerce-MyAccount-navigation-link--specialist-dashboard/' . $section, $classes ) ) {
					unset( $classes[array_search( 'woocommerce-MyAccount-navigation-link--specialist-dashboard/' . $section, $classes )] );
					$classes[] = 'woocommerce-MyAccount-navigation-link--specialist-dashboard';
					$classes[] = $section;
					if( $section == $current_section ) {
						$classes[] = 'is-active';
					}
				}
			}
		}

		return $classes;
	}
}
add_filter( 'woocommerce_account_menu_item_classes', 'drplus_specialist_account_menu_item_classes', 100000 );