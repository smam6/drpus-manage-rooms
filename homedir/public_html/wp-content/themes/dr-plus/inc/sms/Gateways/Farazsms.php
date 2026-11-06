<?php
namespace DrPlus\SMS;

use DrPlus\SMS\Utils;

class Farazsms extends Gateway {
	public function __construct() {
		$settings = Utils::get_settings();
		parent::__construct( [
			'id'			=> 'farazsms',
			'variable_mode'	=> true,
			'url_template'	=> 'https://edge.ippanel.com/v1',
			'api_key'		=> $settings['farazsms']['api_key'],
			'from'			=> $settings['farazsms']['from'],
		] );

		return $this;
	}

	protected function prepare() {
		$this->set_data( [
			'request_options'	=> [
				'httpversion'	=> '1.1',
				'timeout'		=> 60,
				'headers'		=> [
					'Authorization'		=> $this->api_key,
					'Content-Type'	=> 'application/json',
					'accept'		=> 'application/json',
				],
			],
		] );

		return $this;
	}

	public function _send() {
		$request_args = $this->request_options;
		$request_args['body'] = wp_json_encode( [
			'message'		=> $this->text,
			'params'		=> [
				'recipients'		=> $this->to,
			],
			'from_number'	=> $this->from,
		] );
		$request = wp_remote_post( "{$this->base_url}/api/send", $request_args );
		if( is_wp_error( $request ) ) {
			$this->wp_error = $request;
		}
		return $this;
	}

	public function _send_by_pattern( string $pattern ) {
		$request_args = $this->request_options;

		$request_args['body'] = wp_json_encode( [
			'sending_type'	=> 'pattern',
			'code'			=> $pattern,
			'from_number'	=> $this->from,
			'recipients'	=> [$this->to[0]],
			'params'		=> $this->exploded_pattern,
		] );

		$request = wp_remote_post( "{$this->base_url}/api/send", $request_args );
		if( is_wp_error( $request ) ) {
			$this->wp_error = $request;
		}
		return $this;
	}
}