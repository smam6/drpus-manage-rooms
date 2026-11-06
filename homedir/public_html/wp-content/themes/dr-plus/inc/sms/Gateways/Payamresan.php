<?php
namespace DrPlus\SMS;

use DrPlus\SMS\Utils;

class Payamresan extends Gateway {
	public function __construct() {
		$settings = Utils::get_settings();
		parent::__construct( [
			'id'			=> 'payamresan',
			'url_template'	=> 'https://api.sms-webservice.com/api/V3',
			'api_key'		=> $settings['payamresan']['api_key'],
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
					'content-type'	=> 'application/json'
				],
			],
		] );

		return $this;
	}

	public function _send() {
		$request_args = $this->request_options;
		$request_args['body'] = [
			'ApiKey'		=> $this->api_key,
			'Sender'		=> $this->from,
			'Recipients'	=> $this->to,
			'Text'			=> $this->text,
		];
		$request = wp_remote_post( "{$this->base_url}/SendBulk", $request_args );
		if( is_wp_error( $request ) ) {
			$this->wp_error = $request;
		}
		return $this;
	}

	public function _send_by_pattern( string $pattern ) {
		$request_args = $this->request_options;

		$pattern_variables = array_values( $this->exploded_pattern );

		$query_args = [
			'ApiKey'		=> $this->api_key,
			'TemplateKey'	=> $pattern,
			'Destination'	=> $this->to[0],
		];
		if( !empty( $pattern_variables[0] ) ) {
			$query_args['P1'] = $pattern_variables[0];
		}
		if( !empty( $pattern_variables[1] ) ) {
			$query_args['P2'] = $pattern_variables[1];
		}
		if( !empty( $pattern_variables[2] ) ) {
			$query_args['P3'] = $pattern_variables[2];
		}
		$url = add_query_arg( $query_args, "{$this->base_url}/SendTokenSingle" );

		$request = wp_remote_get( $url, $request_args );
		print_r( $request ); die;
		if( is_wp_error( $request ) ) {
			$this->wp_error = $request;
		}
		return $this;
	}
}