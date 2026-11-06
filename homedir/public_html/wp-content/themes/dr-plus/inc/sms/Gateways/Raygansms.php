<?php
namespace DrPlus\SMS;

use DrPlus\SMS\Utils;

class Raygansms extends Gateway {
	public function __construct() {
		$settings = Utils::get_settings();
		parent::__construct( [
			'id'			=> 'raygansms',
			'url_template'	=> 'https://smspanel.trez.ir/api/smsAPI',
			'username'		=> $settings['raygansms']['username'],
			'password'		=> $settings['raygansms']['password'],
			'from'			=> '',
			'api_key'		=> $settings['raygansms']['api_key'],
		] );

		return $this;
	}

	protected function prepare() {
		$this->set_data( [
			'request_options'	=> [
				'httpversion'	=> '1.1',
				'timeout'		=> 60,
				'headers'	=> [
					'content-type'	=> 'application/json',
					'Authorization'	=> 'Basic ' . base64_encode( $this->username . ':' . $this->password )
				],
			],
		] );

		return $this;
	}

	public function _send() {
		$request_args = $this->request_options;

		$to_numbers = $this->to;
		if( strpos( $this->from, '3000' ) === 0 ) {
			$to_numbers = array_map( fn( $number ) => "98" . substr( $number, 1 ), $to_numbers );
		}

		$request_args['body'] = http_build_query( [
			'PhoneNumber'	=> $this->from,
			'Mobiles'		=> $to_numbers,
			'Message'		=> $this->text,
		] );
		$request = wp_remote_post( "{$this->base_url}/SendMessage", $request_args );
		if( is_wp_error( $request ) ) {
			$this->wp_error = $request;
		}
		return $this;
	}

	public function _send_by_pattern( string $pattern ) {
		$request_args = $this->request_options;
		$request_args['body'] = [
			'AccessHash'	=> $this->username,
			'Mobile'		=> $this->to[0],
			'PatternId'		=> $pattern,
		];
		$exploded_pattern = array_values( $this->exploded_pattern );
		for( $pattern_index = 0; $pattern_index <= 8; $pattern_index++ ) {
			if( !empty( $exploded_pattern[$pattern_index] ) ) {
				$request_args['body']['token' . ($pattern_index+1)] = $exploded_pattern[$pattern_index];
			}
		}
		$request_args['body'] = http_build_query( $request_args['body'] );
		$request = wp_remote_post( "https://smspanel.trez.ir/SendPatternCodeWithUrl.ashx", $request_args );
		if( is_wp_error( $request ) ) {
			$this->wp_error = $request;
		}
		return $this;
	}
}