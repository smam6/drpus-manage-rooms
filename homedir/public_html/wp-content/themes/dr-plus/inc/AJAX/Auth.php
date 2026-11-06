<?php
namespace DrPlus\AJAX;

use DrPlus\AJAX;
use DrPlus\Model\OTP;
use DrPlus\SMS\SMS;
use DrPlus\Utils;
use DrPlus\Utils\Date;
use DrPlus\Utils\Options;
use DrPlus\Utils\Sanitizers;
use DrPlus\Utils\SMS as UtilsSMS;
use DrPlus\Utils\User;

class Auth extends AJAX {
	public static function get_instance() {
		static $instance = null;
		if( $instance === null ) {
			$instance = new self;
		}
		return $instance;
	}

	public function __construct() {
		return $this;
	}

	private function find_user_with_everything( $entry ) {
		$user = get_user_by( 'email', $entry );
		if( !$user ) {
			$user = get_user_by( 'login', $entry );
			if( !$user ) {
				$user = User::find_user_by_mobile( $entry );
				if( !$user ) {
					$user = get_user_by( 'ID', $user );
				}
			}
		}
		return $user;
	}

	public function login( $args = [] ) {
		if( empty( $args ) ) {
			$this->set_request_data();
		} else {
			$this->data = $args;
		}

		$username = Utils::convert_chars( $this->data['username'], true, 'sanitize_user' );
		$password = sanitize_text_field( $this->data['password'] );
		$user = $this->find_user_with_everything( $username );
		if( !$user ) {
			$this->result( 'error', [
				'code'		=> 'user_not_found',
				'message'	=> esc_html__( 'User not found.', 'drplus' ),
			] );
		}
		if( is_wp_error( $user ) ) {
			$this->result( 'error', [
				'code'		=> array_keys( $user->errors )[0],
				'message'	=> $user->get_error_message(),
			] );
		}
		if( !wp_check_password( $password, $user->data->user_pass, $user->ID ) ) {
			$this->result( 'error', [
				'code'		=> 'wrong_password',
				'message'	=> esc_html__( 'Password is not correct.', 'drplus' ),
			] );
		}

		$login = wp_signon( [
			'user_login'	=> $username,
			'user_password'	=> sanitize_text_field( $this->data['password'] ),
			'remember'		=> Utils::to_bool( $this->data['remember'] )
		] );
		if( is_wp_error( $login ) ) {
			$this->result( 'error', [
				'code'		=> array_keys( $login->errors )[0],
				'message'	=> $login->get_error_message(),
			] );
		} else {
			$this->result( 'success', [
				'code'		=> 'login_success',
				'message'	=> esc_html__( 'Login successful. The page will reload.', 'drplus' ),
			] );
		}
	}

	public function signup() {
		$this->set_request_data();

		if( !empty( $this->data['mobile'] ) && $this->find_user_with_everything( $this->data['mobile'] ) ) {
			$this->result( 'error', [
				'code'	=> 'user_exists',
				'msg'	=> esc_html__( 'User already exists', 'drplus' ),
			] );
		}

		if( empty( $this->data['username'] ) ) {
			$this->data['username'] = $this->data['mobile'];
		}

		$this->data['password'] = !empty( $this->data['password'] ) ? $this->data['password'] : '';
		$this->data['email'] = !empty( $this->data['email'] ) ? $this->data['email'] : '';

		$user_id = User::create_user( $this->data['username'], $this->data['password'], $this->data['email'], $this->data['mobile'] ?? '' );
		if( is_wp_error( $user_id ) ) {
			$this->result( 'error', [
				'code'		=> array_keys( $user_id->errors )[0],
				'message'	=> $user_id->get_error_message(),
			] );
		} else {
			if( $this->data['password'] ) {
				$this->login( [
					'username'	=> $this->data['username'],
					'password'	=> $this->data['password'],
					'remember'	=> true
				] );
			} else {
				$user = get_user_by( 'id', $user_id );
				wp_set_current_user( $user->ID, $user->user_login );
				wp_set_auth_cookie( $user->ID, true );
				do_action( 'wp_login', $user->user_login, $user );
			}
			$this->result( 'success', [
				'code'		=> 'user_created',
				'message'	=> esc_html__( 'Your account has been created. The page will reload.', 'drplus' ),
			] );
		}
	}

	public function send_otp() {
		$this->set_request_data();

		$options = Options::get_options( [
			'use-outside-iran'	=> false,
			'sms'				=> true,
		] );
		if( $options['use-outside-iran'] ) {
			$this->result( 'error', [
				'code'		=> 'sms_not_supported_outside_iran',
				'message'	=> esc_html__( 'SMS is not supported outside Iran.', 'drplus' ),
			] );
		}
		if( !$options['sms'] ) {
			$this->result( 'error', [
				'code'		=> 'sms_not_active',
				'message'	=> esc_html__( 'SMS is not active.', 'drplus' ),
			] );
		}

		$sms_settings = UtilsSMS::get_settings();

		$mobile = Sanitizers::phone( $this->data['mobile'] );
		$user = User::find_user_by_mobile( $mobile );
		if( $user ) { // Login
			if( empty( $sms_settings['settings']['auth']['login']['enabled'] ) ) {
				$this->result( 'error', [
					'code'		=> 'login_not_active',
					'message'	=> esc_html__( 'Login via SMS is not active.', 'drplus' ),
				] );
			}
			$send = SMS::send( $mobile, 'auth.login' );
		} else { // Register
			if( empty( $sms_settings['settings']['auth']['register']['enabled'] ) ) {
				$this->result( 'error', [
					'code'		=> 'register_not_active',
					'message'	=> esc_html__( 'Register via SMS is not active.', 'drplus' ),
				] );
			}
			$send = SMS::send( $mobile, 'auth.register' );
		}

		if( !$send && !DRPLUS_IS_LOCAL ) {
			$this->result( 'error', [
				'code'		=> 'send_failed',
				'message'	=> esc_html__( 'Error sending SMS', 'drplus' ),
			] );
		}
		if( is_wp_error( $send ) ) {
			$this->result( 'error', [
				'code'		=> array_key_first( $send->errors ),
				'message'	=> $send->get_error_message(),
			] );
		}

		$this->result( 'success', [
			'code'		=> 'otp_sent',
			'message'	=> esc_html__( 'The verification code has been sent to your mobile number.', 'drplus' ),
			'mode'	=> $user ? 'login' : 'register',
		] );
	}

	public function check_otp() {
		$this->set_request_data();

		$mobile = Utils::convert_chars( $this->data['mobile'] );
		$otp = Sanitizers::otp( $this->data['otp'] );

		$find_otp = OTP::query()->where( [
			['mobile', $mobile],
			['otp', $otp],
			['expire', '>', new \DateTime( Date::maybe_j2g( wp_date( 'Y-m-d H:i:s' ) ) )],
		] )->first();

		// OTP expired
		if( !$find_otp ) {
			$this->result( 'error', [
				'code'		=> 'otp_not_match',
				'message'	=> esc_html__( 'OTP code does not match', 'drplus' ),
			] );
		}

		$sms_settings = UtilsSMS::get_settings();

		$user = User::find_user_by_mobile( $mobile );
		if( $user ) { // Login
			if( empty( $sms_settings['settings']['auth']['login']['enabled'] ) ) {
				$this->result( 'error', [
					'code'		=> 'login_not_active',
					'message'	=> esc_html__( 'Login via SMS is not active.', 'drplus' ),
				] );
			}
		} else { // Register
			if( empty( $sms_settings['settings']['auth']['register']['enabled'] ) ) {
				$this->result( 'error', [
					'code'		=> 'register_not_active',
					'message'	=> esc_html__( 'Register via SMS is not active.', 'drplus' ),
				] );
			}

			$auth_email = Options::get_options( [
				'auth_email'	=> true,
			] )['auth_email'];
			if( $sms_settings['settings']['auth']['one_form'] || !$auth_email ) {
				// IF !$auth_email => Signup is only with mobile and otp, So login the new user
				$user_id = User::create_user( $mobile, '', '', $mobile );
				$user = get_user_by( 'id', $user_id );
			} else {
				if( !is_user_logged_in() ) {
					$find_otp->delete();
					$this->result( 'success', [
						'code'	=> 'more_info',
					] );
				}
			}
		}
		if( $user && !is_wp_error( $user ) ) {
			if( !is_user_logged_in() ) {
				wp_set_current_user( $user->ID, $user->user_login );
				wp_set_auth_cookie( $user->ID, true );
				do_action( 'wp_login', $user->user_login, $user );
				$find_otp->delete();
				$this->result( 'success', [
					'code'		=> 'login_success',
					'message'	=> esc_html__( 'Login successful. The page will reload.', 'drplus' ),
				] );
			} else {
				$this->result( 'success', [
					'code'		=> 'otp_confirmed',
					'message'	=> esc_html__( "OTP is correct", 'drplus' ),
				] );
			}
		} else {
			if( !$user ) {
				$this->result( 'error', [
					'code'		=> 'user_not_found',
					'message'	=> esc_html__( 'User not found', 'drplus' ),
				] );
			}
			$this->result( 'error', [
				'code'		=> array_keys( $user_id->errors )[0],
				'message'	=> $user_id->get_error_message(),
			] );
		}
	}

	public function lost_password() {
		$this->set_request_data();

		$user = $this->find_user_with_everything( $this->data['entry'] );
		if( $user ) {
			$user_id = $user->ID;
			$new_password = wp_generate_password( 8, false );
			$update = wp_update_user( [ 'ID' => $user_id, 'user_pass' => $new_password ] );
			if( is_wp_error( $update ) ) {
				$this->result( 'error', [
					'code'		=> array_keys( $update->errors )[0],
					'message'	=> $update->get_error_message(),
				] );
			} else {
				$options = Options::get_options( [
					'lost-password-email-subject'	=> '',
					'lost-password-email-template'	=> '',
				] );

				if( empty( $options['lost-password-email-template'] ) ) {
					$options['lost-password-email-template'] = get_bloginfo( 'name' ) . "<br>" . __( 'Your new password is: <strong>{password}</strong>', 'drplus' );
				}

				$email_msg = str_replace( "{password}", $new_password, $options['lost-password-email-template'] );

				if( $user->user_email ) {
					$send_email = wp_mail( $user->user_email, wp_strip_all_tags( $options['lost-password-email-subject'] ), $email_msg );
					if( !$send_email ) {
						$email_error = true;
						$email_error_text = __( "Send email is failed", 'drplus' );
					}
					if( is_wp_error( $send_email ) ) {
						$email_error = true;
						$email_error_text = $send_email->get_error_message();
					}
				}

				$options = Options::get_options( [
					'use-outside-iran'	=> false,
					'sms'				=> true,
				] );
				if( $options['sms'] && !$options['use-outside-iran'] ) {
					$sms_settings = UtilsSMS::get_settings();
					if( !empty( $sms_settings['settings']['auth']['lost_password']['enabled'] ) ) {
						$mobile = User::get_phone( $user_id );
						if( $mobile ) {
							$sms_send = SMS::send( $mobile, 'auth.lost_password', [ 'password' => $new_password ] );
							if( !$sms_send && !DRPLUS_IS_LOCAL ) {
								$sms_error = true;
								$sms_error_text = esc_html__( 'Error sending SMS', 'drplus' );
							} else if( is_wp_error( $sms_send ) ) {
								$sms_error = true;
								$sms_error_text = $sms_send->get_error_message();
							}
						}
					}
				}

				if( !empty( $email_error ) && !empty( $sms_error ) ) {
					// Both way caused error
					$this->result( 'error', [
						'code'		=> 'lost_password_failed',
						'message'	=> "{$sms_error_text} \n {$email_error_text}",
					] );
				} else if( !empty( $email_error ) ) {
					$this->result( 'success', [
						'code'		=> 'new_password_sent',
						'message'	=> __( 'Your new password has been sent to your mobile phone.', 'drplus' )
					] );
				} else if( !empty( $sms_error ) ) {
					$this->result( 'success', [
						'code'		=> 'new_password_sent',
						'message'	=> __( 'Your new password has been sent to your email address.', 'drplus' )
					] );
				} else { // Both success
					$this->result( 'success', [
						'code'		=> 'new_password_sent',
						'message'	=> __( 'Your new password has been sent to your email and mobile phone.', 'drplus' )
					] );
				}
			}
		} else {
			$this->result( 'error', [
				'code'		=> 'user_not_found',
				'message'	=> esc_html__( 'User not found', 'drplus' ),
			] );
		}
	}
}