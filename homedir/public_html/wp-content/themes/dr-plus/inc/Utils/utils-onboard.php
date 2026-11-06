<?php
namespace DrPlus\Utils;

use DrPlus\Utils;

class Onboard extends Utils {
	private static $errors = [];

	public static function steps() {
		static $steps = null;
		if( $steps === null ) {
			$steps = [
				'personal'		=> [
					'title'			=> __( "Personal Information", 'drplus' ),
					'description'	=> __( 'Please enter your personal information. This information is necessary for creating your profile.', 'drplus' ),
				],
				'identity'		=> [
					'title'			=> __( "Identity documents", 'drplus' ),
					'description'	=> __( "Please upload the required identification documents. The site administration will review the documents and approve your profile afterward.", 'drplus' ),
				],
				'services'		=> [
					'title'			=> __( "Specialized Services", 'drplus' ),
					'description'	=> __( "Please select your area of expertise from the list below. If needed, provide a description for each selected specialty.", 'drplus' ),
				],
				'insurances'		=> [
					'title'			=> __( "Insurances", 'drplus' ),
					'description'	=> __( "Please select your covered insurance.", 'drplus' ),
				],
				'offices'		=> [
					'title'			=> __( "Offices", 'drplus' ),
					'description'	=> __( "Please specify your place of practice. If you work in a hospital, search for and select the hospital's name. For private clinics, enter the required information.", 'drplus' ),
				],
				'certificates'	=> [
					'title'			=> __( "Certificates and Courses", 'drplus' ),
					'description'	=> __( "In this section, you can upload your academic credentials and specialized training certificates. This information will help enhance the credibility of your profile.", 'drplus' ),
				],
				'done'			=> [
					'title'			=> __( "Your request has been successfully submitted.", 'drplus' ),
					'description'	=> __( "Your request has been successfully submitted. After it is reviewed by the administration, the result will be sent to you via SMS and email. Please be patient.", 'drplus' ),
				],
			];

			$options = Options::get_options( [
				'insurance'	=> true,
			] );

			if( !$options['insurance'] || empty( UtilsSpecialists::get_insurances_terms() ) ) {
				unset( $steps['insurances'] );
			}

			if( empty( UtilsSpecialists::get_identity_types_terms() ) ) { // Skip the identity step when identity types is empty
				unset( $steps['identity'] );
			}
		}

		return $steps;
	}

	public static function get_next_step( string $current_step ) : string {
		$steps = array_keys( self::steps() );

		if( !$current_step || !in_array( $current_step, $steps ) ) return $steps[0];
		if( $current_step == $steps[count( $steps )-1] ) return '';

		return $steps[array_search( $current_step, $steps )+1];
	}

	public static function get_prev_step( string $current_step ) : string {
		$steps = array_keys( self::steps() );

		if( !$current_step || !in_array( $current_step, $steps ) ) return $steps[0];
		if( $current_step == $steps[0] ) return '';

		return $steps[array_search( $current_step, $steps )-1];
	}

	public static function is_onboard() : bool {
		return defined( 'DRPLUS_ONBOARD' ) && DRPLUS_ONBOARD;
	}

	public static function get_errors() {
		return apply_filters( 'drplus/onboard/errors', self::$errors );
	}

	public static function add_error( string $code, string $text ) {
		$code = apply_filters( 'drplus/onboard/error/code', $code, $text );
		$text = apply_filters( 'drplus/onboard/error/text', $text, $code );
		self::$errors[$code] = $text;
	}

	public static function get_user_step( $user_id = 0 ) {
		$user_id = parent::get_user_id( $user_id );
		$steps = array_keys( self::steps() );
		$steps[] = 'rejected';
		$user_step = get_user_meta( $user_id, 'drplus_onboard_step', true );
		$user_step = parent::ensure_values_in_array( $user_step, $steps, $steps[0] );
		return $user_step;
	}

	public static function update_user_step( $step, $is_current_step = true, $user_id = 0 ) {
		$user_id = parent::get_user_id( $user_id );
		$steps = array_keys( self::steps() );
		$steps[] = 'rejected';
		$step = parent::ensure_values_in_array( $step, $steps, '' );
		if( $is_current_step ) {
			$step = self::get_next_step( $step );
		}
		$step = parent::ensure_values_in_array( $step, $steps, $steps[0] );
		update_user_meta( $user_id, 'drplus_onboard_step', $step );
	}
}