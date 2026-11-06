<?php
namespace DrPlus\SMS;

use DrPlus\Model\OTP;
use DrPlus\Utils as DrPlusUtils;
use MJ\Whitebox\Utils\Date;

class Utils extends DrPlusUtils {
	public static function defaults() {
		return [
			'gateway'	=> '',
			'messages'	=> [
				'auth'	=> [
					'login'			=> '{otp}',
					'register'		=> '{otp}',
					'lost_password'	=> '{password}',
				]
			],
			'settings'	=> [
				'auth'	=> [
					'login'	=> [
						'enabled'	=> true,
						'pattern'	=> '',
						'otp_timer'	=> 60,
					],
					'register'	=> [
						'enabled'	=> true,
						'pattern'	=> '',
						'otp_timer'	=> 60,
					],
					'one_form'	=> true,
					'lost_password'	=> [
						'enabled'	=> true,
						'pattern'	=> '',
					],
				],
			],
			'security'	=> [
				'hide_mobile'			=> 'mid_star',
				'hide_mobile_custom'	=> sprintf( __( "{name}'s user", 'drplus' ), "\r\n\r\n" . get_option( 'blogname', '' ) ),
			],
		];
	}

	public static function gateways() {
		return apply_filters( 'drplus/sms/gateways', [
			'melipayamak'	=> [
				'label'			=> __( 'Melipayamak', 'drplus' ),
				'logo'			=> 'melipayamak.jpg',
				'fields'		=> ['username', 'api_key'],
				'variable_mode'	=> false,
			],
			'farazsms'	=> [
				'label'			=> __( 'Farazsms (ippanel)', 'drplus' ),
				'logo'			=> 'farazsms.png',
				'fields'		=> ['api_key', 'from'],
				'variable_mode'	=> true,
			],
			'farazsms_new'	=> [
				'label'			=> __( 'New Farazsms (iranpayamak)', 'drplus' ),
				'logo'			=> 'farazsms.png',
				'fields'		=> ['api_key', 'from'],
				'variable_mode'	=> true,
			],
			'smsir'	=> [
				'label'			=> __( 'SMS.ir', 'drplus' ),
				'logo'			=> 'sms.ir.svg',
				'fields'		=> ['api_key'],
				'variable_mode'	=> true,
			],
			'kavenegar'	=> [
				'label'			=> __( 'Kavenegar', 'drplus' ),
				'logo'			=> 'kavenegar.png',
				'fields'		=> ['api_key'],
				'variable_mode'	=> true,
			],
			'farapayamak'	=> [
				'label'			=> __( 'Farapayamak', 'drplus' ),
				'logo'			=> 'farapayamak.png',
				'fields'		=> ['username', 'password'],
				'variable_mode'	=> false,
			],
			'payamresan'	=> [
				'label'			=> __( 'Payamresan', 'drplus' ),
				'logo'			=> 'payamresan.svg',
				'fields'		=> ['api_key'],
				'variable_mode'	=> false,
			],
			'raygansms'	=> [
				'label'			=> __( 'Raygansms', 'drplus' ),
				'logo'			=> 'raygansms.png',
				'fields'		=> ['username', 'password', 'api_key'],
				'variable_mode'	=> false,
			],
			'asanak'	=> [
				'label'			=> __( 'Asanak', 'drplus' ),
				'logo'			=> 'asanak.png',
				'fields'		=> ['username', 'password'],
				'variable_mode'	=> true,
			],
		] );
	}
	
	public static function get_settings() {
		static $settings = null;
		if( $settings === null ) {
			$settings = parent::check_default( get_option( 'drplus_sms_settings', self::defaults() ), self::defaults() );

			if( !empty( $settings['gateway'] ) ) {
				if( !isset( self::gateways()[$settings['gateway']] ) ) {
					$settings = parent::unset( $settings, [$settings['gateway']] ); // Remove the gateway settings if it's not valid
					$settings['gateway'] = '';
				} else {
					foreach( self::gateways()[$settings['gateway']]['fields'] as $field ) {
						$settings[$settings['gateway']][$field] = $settings[$settings['gateway']][$field] ?? '';
					}
				}
			}
		}

		return $settings;
	}

	public static function sanitize_gateway( string $gateway, array $gateways = [] ) {
		if( empty( $gateways ) ) {
			$gateways = self::gateways();
		}
		return parent::ensure_values_in_array( parent::convert_chars( $gateway, true, 'strtolower' ), array_keys( $gateways ) );
	}

	public static function save_settings( $settings ) {
		$gateways = self::gateways();

		// Save gateway settings
		$settings["drplus_sms_gateway"] = !empty( $settings["drplus_sms_gateway"] ) ? $settings["drplus_sms_gateway"] : '';
		$result_settings['gateway'] = self::sanitize_gateway( $settings["drplus_sms_gateway"], $gateways );
		foreach( $gateways as $id => $gateway ) {
			foreach( $gateway['fields'] as $field ) {
				$result_settings[$id][$field] = $settings["drplus_sms_{$id}"][$field] ?? '';
			}
		}

		// Save messages settings
		$messages_settings = $settings['drplus_sms_settings'];

		// Auth: Login
		$result_settings['settings']['auth']['login']['enabled'] = !empty( $messages_settings['auth']['login']['enabled'] );
		$result_settings['settings']['auth']['login']['pattern'] = parent::convert_chars( $messages_settings['auth']['login']['pattern'] );
		$result_settings['settings']['auth']['login']['otp_timer'] = parent::convert_chars( $messages_settings['auth']['login']['otp_timer'], true, 'absint' );
		$result_settings['messages']['auth']['login'] = !empty( $messages_settings['auth']['login']['message'] ) ? self::prepare_variables_to_save( $result_settings['gateway'], $messages_settings['auth']['login']['message'] ) : '';

		// Auth: Register
		$result_settings['settings']['auth']['register']['enabled'] = !empty( $messages_settings['auth']['register']['enabled'] );
		$result_settings['settings']['auth']['register']['pattern'] = parent::convert_chars( $messages_settings['auth']['register']['pattern'] );
		$result_settings['settings']['auth']['register']['otp_timer'] = parent::convert_chars( $messages_settings['auth']['register']['otp_timer'], true, 'absint' );
		$result_settings['messages']['auth']['register'] = !empty( $messages_settings['auth']['register']['message'] ) ? self::prepare_variables_to_save( $result_settings['gateway'], $messages_settings['auth']['register']['message'] ) : '';

		$result_settings['settings']['auth']['one_form'] = $result_settings['settings']['auth']['login']['enabled'] && $result_settings['settings']['auth']['register']['enabled'] && !empty( $messages_settings['auth']['one_form'] );

		// Auth: Lost password
		$result_settings['settings']['auth']['lost_password']['enabled'] = !empty( $messages_settings['auth']['lost_password']['enabled'] );
		$result_settings['settings']['auth']['lost_password']['pattern'] = parent::convert_chars( $messages_settings['auth']['lost_password']['pattern'] );
		$result_settings['messages']['auth']['lost_password'] = !empty( $messages_settings['auth']['lost_password']['message'] ) ? self::prepare_variables_to_save( $result_settings['gateway'], $messages_settings['auth']['lost_password']['message'] ) : '';

		update_option( 'drplus_sms_settings', $result_settings, false );
		do_action( 'drplus/sms/settings/updated', $result_settings );
		add_settings_error( 'drplus-sms-settings', 'updated', __( 'Settings updated', 'drplus' ), 'success' );
	}

	public static function auth_variables( $additional = [], $excludes = [] ) : array {
		$variables = [
			'otp'		=> __( "The OTP code", 'drplus' ),
			'end_time'	=> __( "The end time of the OTP code", 'drplus' ),
			'domain'	=> __( "The domain name", 'drplus' ),
			'name'		=> __( "The website name", 'drplus' ),
		];
		$variables = array_merge( $variables, $additional );
		return parent::unset( $variables, $excludes );
	}

	public static function security_variables() {
		return [
			'domain'	=> __( "The domain name", 'drplus' ),
			'name'		=> __( "The website name", 'drplus' ),
		];
	}

	public static function apply_variables( string $text, $to, string $type = '', array $custom_variables = [] ) {
		if( strpos( $text, "{otp}" ) !== false ) {
			$otp = rand( 1000, 9999 );
			$text = str_replace( "{otp}", $otp, $text );
			
			$timer = parent::get_nested_value( self::get_settings()['settings'], $type )['otp_timer'];
			$end_time = parent::convert_chars( date_i18n( 'U' ) ) + $timer;
			$end_time = date_i18n( "Y-m-d H:i:s", $end_time );
			$text = str_replace( "{end_time}", $end_time, $text );

			$otp_db = new OTP;
			$otp_db->updateOrCreate( [
				'mobile'	=> $to[0]
			], [
				'mobile'	=> $to[0],
				'otp'		=> $otp,
				'expire'	=> Date::maybe_j2g( parent::convert_chars( $end_time ) ),
			] );
		}

		$text = parent::apply_general_variables( $text, $custom_variables );

		return $text;
	}

	public static function hide_mobile_types() {
		return [
			'disabled'	=> esc_html__( "Disabled", 'drplus' ),
			'mid_star'	=> '0999***9999',
			'end_star'	=> '0999999****',
			'sitetitle'	=> esc_html__( "Site title", 'drplus' ),
			'custom'	=> esc_html__( "Custom", 'drplus' ),
		];
	}

	public static function convert_string_to_variables( $variables_from_settings ) {
		$result = [];
		if( substr( $variables_from_settings, -1, 1 ) == ';' ) {
			$variables_from_settings = substr( $variables_from_settings, 0, strlen( $variables_from_settings )-1 );
		}
		foreach( explode( ";", $variables_from_settings ) as $piece ) {
			if( strpos( $piece, ":" ) !== false ) {
				$piece = explode( ":", $piece );
				$result[$piece[0]] = $piece[1];
			} else {
				$result[] = $piece;
			}
		}

		return $result;
	}

	public static function prepare_variables_to_save( string $gateway, array $variables ) : string {
		$result = '';

		$gateways = self::gateways();
		if( !empty( $gateways[$gateway] ) ) {
			$message_variables = [];

			$variable_mode = $gateways[$gateway]['variable_mode'];
			foreach( $variables['vars'] as $var_index => $variable ) {
				$var_index = absint( $var_index );
				$variable = parent::convert_chars( $variable );
				if( empty( $variable ) ) continue;

				if( substr( $variable, 0, 1 ) !== "{" ) {
					$variable = '{' . $variable;
				}
				if( substr( $variable, -1, 1 ) !== "}" ) {
					$variable = $variable . "}";
				}

				if( $variable_mode ) {
					if( isset( $variables['gateway_vars'][$var_index] ) && $variables['gateway_vars'][$var_index] !== '' ) {
						$gateway_var = parent::convert_chars( $variables['gateway_vars'][$var_index] );
						if( !empty( $gateway_var ) ) {
							$message_variables[] = "{$gateway_var}:{$variable}";
						}
					}
				} else {
					$message_variables[] = $variable;
				}
			}

			$result = implode( ";", $message_variables );
		}

		return $result;
	}
}