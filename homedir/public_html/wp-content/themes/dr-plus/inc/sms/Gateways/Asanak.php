<?php
namespace DrPlus\SMS;

use DrPlus\SMS\Utils;

class Asanak extends Gateway {
	public function __construct() {
		$settings = Utils::get_settings();
		parent::__construct( [
			'id'			=> 'asanak',
			'variable_mode'	=> true,
			'url_template'	=> 'https://sms.asanak.ir/webservice/v2rest',
			'username'		=> $settings['asanak']['username'],
			'password'		=> $settings['asanak']['password'],
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
					'content-type'	=> 'application/json',
					'accept'		=> 'application/json'
				],
			],
		] );

		return $this;
	}

	public function _send() {
		$request_args = $this->request_options;
		$request_args['body'] = wp_json_encode( [
			'username'		=> $this->username,
			'password'		=> $this->password,
			'source'		=> $this->from,
			'destination'	=> implode( ',', $this->to ),
			'message'		=> $this->text,
		] );
		$request = wp_remote_post( "{$this->base_url}/sendsms", $request_args );
		if( is_wp_error( $request ) ) {
			$this->wp_error = $request;
		}
		return $this;
	}

	public function _send_by_pattern( string $pattern ) {
		$request_args = $this->request_options;
		$request_args['body'] = wp_json_encode( [
			'username'		=> $this->username,
			'password'		=> $this->password,
			'destination'	=> $this->to[0],
			'parameters'	=> $this->exploded_pattern,
			'template_id'	=> $pattern,
		] );
		$request = wp_remote_post( "{$this->base_url}/template", $request_args );
		if( is_wp_error( $request ) ) {
			$this->wp_error = $request;
		}
		return $this;
	}
}