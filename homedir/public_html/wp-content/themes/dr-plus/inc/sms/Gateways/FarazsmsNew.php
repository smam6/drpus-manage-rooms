<?php
namespace DrPlus\SMS;

use DrPlus\SMS\Utils;

class FarazsmsNew extends Gateway {
	public function __construct() {
		$settings = Utils::get_settings();
		parent::__construct( [
			'id'			=> 'farazsms_new',
			'variable_mode'	=> true,
			'url_template'	=> 'https://api.iranpayamak.com',
			'api_key'		=> $settings['farazsms_new']['api_key'],
			'from'			=> $settings['farazsms_new']['from'],
		] );

		return $this;
	}

	protected function prepare() {
		$this->set_data( [
			'request_options'	=> [
				'httpversion'	=> '1.1',
				'timeout'		=> 60,
				'headers'		=> [
					'Api-Key'		=> $this->api_key,
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
			'code'			=> $pattern,
			'recipient'		=> $this->to[0],
			'attributes'	=> $this->exploded_pattern,
			'line_number'	=> $this->from,
			'number_format'	=> 'english',
		] );

		$request = wp_remote_post( "{$this->base_url}/ws/v1/sms/pattern", $request_args );
		if( is_wp_error( $request ) ) {
			$this->wp_error = $request;
		}
		return $this;
	}
}