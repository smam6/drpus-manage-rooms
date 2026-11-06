<?php
namespace DrPlus\SMS;

use DrPlus\SMS\Utils;

class Smsir extends Gateway {
	public function __construct() {
		$settings = Utils::get_settings();
		parent::__construct( [
			'id'			=> 'smsir',
			'variable_mode'	=> true,
			'url_template'	=> 'https://api.sms.ir/v1/send',
			'api_key'		=> $settings['smsir']['api_key'],
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
					'X-API-KEY'		=> $this->api_key,
					'Accept'		=> 'text/plain'
				],
			],
		] );

		return $this;
	}

	public function _send() {
		$request_args = $this->request_options;
		$request_args['body'] = [
			'lineNumber'	=> $this->from,
			'mobiles'		=> $this->to,
			'messageText'	=> $this->text,
			'sendDateTime'	=> null,
		];
		$request = wp_remote_post( "{$this->base_url}/bulk", $request_args );
		if( is_wp_error( $request ) ) {
			$this->wp_error = $request;
		}
		return $this;
	}

	public function _send_by_pattern( string $pattern ) {
		$request_args = $this->request_options;

		$pattern_vars = [];
		foreach( $this->exploded_pattern as $variable => $value ) {
			$pattern_vars[] = [
				'name'	=> $variable,
				'value'	=> $value
			];
		}

		$request_args['body'] = wp_json_encode( [
			'mobile'		=> $this->to[0],
			'templateId'	=> $pattern,
			'parameters'	=> $pattern_vars,
		] );
		$request = wp_remote_post( "{$this->base_url}/verify", $request_args );
		if( is_wp_error( $request ) ) {
			$this->wp_error = $request;
		}
		return $this;
	}
}