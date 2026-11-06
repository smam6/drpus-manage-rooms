<?php
namespace DrPlus\AJAX;

use DrPlus\AJAX;
use DrPlus\Model\OTP;
use DrPlus\SMS\SMS;
use DrPlus\Utils;
use DrPlus\Utils\Date;
use DrPlus\Utils\Options;
use DrPlus\Utils\Sanitizers;
use DrPlus\Utils\User;

class ProfileOTP extends AJAX {
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

		$mobile = Sanitizers::phone( $this->data['mobile'] );
		$user = User::find_user_by_mobile( $mobile );
		if( $user && $user->ID != get_current_user_id() ) {
			$this->result( 'error', [
				'code'		=> 'user_exists',
				'message'	=> esc_html__( "The mobile number already exists.", 'drplus' )
			] );
		} else {
			$send = SMS::send( $mobile, 'auth.login' );
		}

		if( empty( $send ) && !DRPLUS_IS_LOCAL ) {
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

		$user = User::find_user_by_mobile( $mobile );
		if( ( empty( $user ) || ( !empty( $user ) && $user->ID == get_current_user_id() ) ) && !is_wp_error( $user ) ) {
			$this->result( 'success', [
				'code'		=> 'otp_confirmed',
				'message'	=> esc_html__( "OTP is correct", 'drplus' ),
			] );
		} else {
			$this->result( 'error', [
				'code'		=> 'user_exists',
				'message'	=> esc_html__( "The mobile number already exists.", 'drplus' )
			] );
		}
	}
}