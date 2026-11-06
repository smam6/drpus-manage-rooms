<?php
namespace DrPlus\SMS;

use DrPlus\Utils as DrPlusUtils;
use DrPlus\SMS\Utils;

class Kavenegar extends Gateway {
	public function __construct() {
		$settings = Utils::get_settings();
		parent::__construct( [
			'id'			=> 'kavenegar',
			'url_template'	=> 'https://api.kavenegar.com/v1/{api_key}',
			'api_key'		=> $settings['kavenegar']['api_key'],
			'from'			=> '',
		] );

		return $this;
	}

	protected function prepare() {
		$this->set_data( [
			'request_options'	=> [
				'httpversion'	=> '1.1',
				'timeout'		=> 60,
			],
		] );

		return $this;
	}

	public function _send() {
		$request_args = $this->request_options;
		
		$url = add_query_arg( [
			'receptor'	=> implode( ",", $this->to ),
			'message'	=> urlencode( $this->text )
		], "{$this->base_url}/sms/send.json" );

		$request = wp_remote_get( $url, $request_args );
		if( is_wp_error( $request ) ) {
			$this->wp_error = $request;
		}
		return $this;
	}

	public function _send_by_pattern( string $pattern ) {
		$request_args = $this->request_options;

		$pattern_variables = array_values( $this->exploded_pattern );

		$query_args = [
			'receptor'	=> $this->to[0],
			'template'	=> $pattern,
		];

		$kavenegar_vars = ['token', 'token2', 'token3', 'token10', 'token20'];
		
		foreach( $pattern_variables as $var ) {
			if( strpos( $var, 'token' ) !== 0 ) {
				foreach( $kavenegar_vars as $kavenegar_var ) {
					if( !isset( $query_args[$kavenegar_var] ) ) {
						$query_args[$kavenegar_var] = urlencode( $var );
						break;
					}
				}
			} else { // This variable pointed to a specific kavenegar var
				$var_explode = explode( ":", $var );
				$kavenegar_var = DrPlusUtils::convert_chars( $var_explode[0] );
				unset( $var_explode[0] );
				$query_args[$kavenegar_var] = urlencode( implode( "", $var_explode ) );
			}
		}
		$url = add_query_arg( $query_args, "{$this->base_url}/verify/lookup.json" );

		$request = wp_remote_get( $url, $request_args );
		if( is_wp_error( $request ) ) {
			$this->wp_error = $request;
		}
		return $this;
	}
}