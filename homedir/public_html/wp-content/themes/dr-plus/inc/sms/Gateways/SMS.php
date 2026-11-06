<?php
namespace DrPlus\SMS;

use DrPlus\Utils;
use DrPlus\SMS\Utils as UtilsSMS;

class SMS {
	private static $gateway;

	private static function get_gateway_class( $gateway = '' ) {
		if( !$gateway ) {
			$settings = UtilsSMS::get_settings();
			if( empty( $settings['gateway'] ) ) return false;
			$selected_gateway = $settings['gateway'];
		} else {
			$selected_gateway = $gateway;
		}

		$gateway_id = $selected_gateway;
		
		$gateway_class = "DrPlus\\SMS\\" . Utils::convert_to_pascal_case( $gateway_id );
		if( class_exists( $gateway_class ) ) {
			self::$gateway = new $gateway_class();
			return self::$gateway;
		} else {
			return new \WP_Error( 'gateway_class_not_found', sprintf( esc_html__( 'The gateway class %s does not exist.', 'drplus' ), $gateway_class ) );
		}
	}

	/**
	 * Send SMS
	 *
	 * @param array|string $to
	 * @param string $type
	 * @return mixed
	 */
	public static function send( $to, string $type, array $variables = [] ) {
		$gateway = self::get_gateway_class();
		if( !$gateway || is_wp_error( $gateway ) ) return $gateway;
		return $gateway->send_by_pattern( $to, $type, $variables );
	}

	/**
	 * Send custom SMS
	 *
	 * @param array|string $to
	 * @param string $text
	 * @param string $gateway The gateway ID
	 * @return mixed
	 */
	public static function send_custom_text( $to, string $text, string $gateway ) {
		$gateway = self::get_gateway_class( $gateway );
		if( !$gateway || is_wp_error( $gateway ) ) return $gateway;
		return $gateway->send( $to, $text );
	}
}