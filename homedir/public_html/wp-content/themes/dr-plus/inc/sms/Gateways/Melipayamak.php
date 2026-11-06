<?php
namespace DrPlus\SMS;

use DrPlus\SMS\Utils;

class Melipayamak extends Gateway {
	public function __construct() {
		$settings = Utils::get_settings();
		parent::__construct( [
			'id'			=> 'melipayamak',
			'url_template'	=> 'https://rest.payamak-panel.com/api/SendSMS',
			'username'		=> $settings['melipayamak']['username'],
			'password'		=> !empty( $settings['melipayamak']['api_key'] ) ? $settings['melipayamak']['api_key'] : $settings['melipayamak']['password'], // Backward
			'api_key'		=> !empty( $settings['melipayamak']['api_key'] ) ? $settings['melipayamak']['api_key'] : $settings['melipayamak']['password'], // Backward
			'from'			=> '',
		] );

		return $this;
	}

	protected function prepare() {
		$this->set_data( [
			'request_options'	=> [
				'httpversion'	=> '1.1',
				'timeout'		=> 60,
				'headers'	=> [
					'content-type'	=> 'application/x-www-form-urlencoded'
				],
			],
		] );

		return $this;
	}

	public function _send() {
		$request_args = $this->request_options;
		$request_args['body'] = http_build_query( [
			'username'	=> $this->username,
			'password'	=> $this->password,
			'from'		=> $this->from,
			'to'		=> implode( ',', $this->to ),
			'text'		=> $this->text,
		] );
		$request = wp_remote_post( "{$this->base_url}/SendSMS", $request_args );
		if( is_wp_error( $request ) ) {
			$this->wp_error = $request;
		}
		return $this;
	}

	public function _send_by_pattern( string $pattern ) {
		$request_args = $this->request_options;
		$request_args['body'] = http_build_query( [
			'username'	=> $this->username,
			'password'	=> $this->password,
			'to'		=> $this->to[0],
			'text'		=> $this->text,
			'bodyId'	=> $pattern,
		] );
		$request = wp_remote_post( "{$this->base_url}/BaseServiceNumber", $request_args );
		if( is_wp_error( $request ) ) {
			$this->wp_error = $request;
		}
		return $this;
	}
}