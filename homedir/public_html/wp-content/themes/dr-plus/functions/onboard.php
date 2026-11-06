<?php

use DrPlus\Model\Specialists;
use DrPlus\SMS\SMS;
use DrPlus\Utils;
use DrPlus\Utils\Onboard;
use DrPlus\Utils\Options;
use DrPlus\Utils\SMS as SMSUtils;
use DrPlus\Utils\UtilsSpecialists;

if( !function_exists( "drplus_onboard_redirect" ) ) {
	function drplus_onboard_redirect() {
		if( !Utils::is_wc_active() ) return;

		if( !wc_is_current_account_menu_item( 'specialist-dashboard' ) ) return;
		if( !Utils::to_bool( Options::get_options( ['specialist_onboard' => true] )['specialist_onboard'] ) ) return;
		$specialist = (new Specialists)->where( 'user_id', get_current_user_id() )->first();
		if( !$specialist ) {
			$specialist = new Specialists;
		}
		if( empty( $specialist->status ) || $specialist->status != 'active' ) {
			Utils::maybe_define( 'DRPLUS_ONBOARD', true );

			if( empty( $specialist->user_id ) ) {
				$specialist->user_id = get_current_user_id();
				$specialist->user = get_user_by( 'id', $specialist->user_id );
			}

			get_template_part( "templates/specialists/onboard/template-specialists-onboard", null, [
				'specialist'	=> $specialist
			] );
			die;
		}
	}
}
if( is_user_logged_in() ) {
	add_action( 'wp', 'drplus_onboard_redirect' );
}

if( !function_exists( "drplus_onboard_save" ) ) { // This function will run for onboard and my account profile
	function drplus_onboard_save() {
		if( !Utils::is_wc_active() ) return;

		if( empty( $_POST ) || empty( $_POST['nonce'] ) ) return;

		$nonce = Utils::convert_chars( $_POST['nonce'] );
		if( !$nonce || !wp_verify_nonce( $nonce, 'drplus_specialist-save' ) ) return;

		$options = Options::get_options( [
			'use-outside-iran'	=> false,
			'sms'				=> true,
		] );
		$use_outside_iran = $options['use-outside-iran'];

		$specialist_data = Utils::remove_prefix_from_array_keys( $_POST, 'specialist_' );
		$old_specialist = UtilsSpecialists::get_by_user_id( get_current_user_id() );

		$old_rejected = !empty( $old_specialist['status'] ) && $old_specialist['status'] == 'rejected';
		if( $old_rejected ) {
			$specialist_data = [
				'status'	=> 'incomplete',
			];
		}

		if( !empty( $_POST['is_onboard'] ) ) {
			if( isset( $specialist_data['meta'] ) && isset( $specialist_data['meta']['certificates'] ) ) {
				$specialist_data['status'] = 'pending';
			}
		}
		$step = $specialist_data['section'] ?? '';
		if( isset( $_POST['step'] ) ) {
			$step = Utils::convert_chars( $_POST['step'] );
		}
		if( isset( $_GET['step'] ) ) {
			$step = Utils::convert_chars( $_GET['step'] );
		}

		if( isset( $specialist_data['drplus_account_avatar_id'] ) ) {
			$specialist_data['avatar'] = $specialist_data['drplus_account_avatar_id'];
			unset( $specialist_data['drplus_account_avatar_id'] );
		}
		
		$specialist_data['user_id'] = $specialist_data['user_id'] ?? get_current_user_id();
		$specialist_data['section'] = $step;
		$sid = UtilsSpecialists::save(
			$specialist_data,
			$old_specialist['id'] ?? 0,
			$old_specialist
		);
		if( !empty( $_POST['is_onboard'] ) ) {
			if( !$sid ) {
				Onboard::add_error( 'empty_response', __( "Something went wrong!", 'drplus' ) );
				return;
			}
			if( is_wp_error( $sid ) ) {
				Onboard::add_error( array_key_first( $sid->errors ), $sid->get_error_message() );
				return;
			}
			if( $old_rejected ) {
				wp_redirect( add_query_arg( ['step' => 'personal'] ) );
				die;
			}
			if( $step ) {
				$steps = array_keys( Onboard::steps() );
				if( !in_array( $step, $steps ) ) {
					wp_redirect( remove_query_arg( 'step' ) );
					die;
				} else {
					if( $step == 'certificates' ) {
						// Send sms
						if( $options['sms'] && !$use_outside_iran ) {
							$sms_settings = SMSUtils::get_specialist_panel_settings();
							if( $sms_settings['settings']['new_request']['enabled'] ?? false ) {
								$first_name = get_user_meta( $specialist_data['user_id'], 'first_name', true );
								$last_name = get_user_meta( $specialist_data['user_id'], 'last_name', true );
								$sms_vars = [
									'user_fullname'		=> "{$first_name} {$last_name}",
									'requested_date'	=> date_i18n( 'Y M d' ),
								];							
								SMS::send( $sms_settings['settings']['new_request']['recipients'], 'specialist_panel.new_request', $sms_vars );
							}
						}
					}
					wp_redirect( add_query_arg( ['step' => Onboard::get_next_step( $step )] ) );
					die;
				}
			}
		} else { // It's from my account
			if( !$sid ) {
				wc_add_notice( __( "Something went wrong!", 'drplus' ), 'error' );
				return;
			}
			if( is_wp_error( $sid ) ) {
				wc_add_notice( $sid->get_error_message(), 'error' );
				return;
			}
			wc_add_notice( __( "Your data saved successfully", 'drplus' ) );
		}
	}
}
add_action( 'init', 'drplus_onboard_save' );

if( !function_exists( "drplus_onboard_show_errors" ) ) {
	function drplus_onboard_show_errors() {
		if( !Utils::is_wc_active() ) return;
		
		$errors = Onboard::get_errors();
		if( empty( $errors ) ) return;
		?>
		<div class="onboard-errors">
			<?php foreach( $errors as $code => $error ) { ?>
				<div class="onboard-error" data-code="<?php echo esc_attr( $code ) ?>"><?php echo wp_kses_post( $error ) ?></div>
			<?php } ?>
		</div>
		<?php
	}
}
add_action( 'drplus/onboard/before_form', 'drplus_onboard_show_errors' );