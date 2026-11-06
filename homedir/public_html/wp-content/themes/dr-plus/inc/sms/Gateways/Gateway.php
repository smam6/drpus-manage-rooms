<?php
namespace DrPlus\SMS;

use DrPlus\Utils as DrPlusUtils;
use DrPlus\Utils\Sanitizers;
use DrPlus\SMS\Utils;

abstract class Gateway {
	public $id;
	protected $url_template;
	protected $base_url;
	protected $username;
	protected $password;
	protected $api_key;
	protected $from;
	protected $variable_mode = false;
	protected $request_options;

	protected $to;
	protected $text;
	protected $pattern;
	protected $exploded_pattern;

	protected $wp_error;

	public function __construct( array $args ) {
		$this->set_data( $args );
		$this->prepare();

		$this->set_base_url();

		return $this;
	}

	public function set_data( array $args ) {
		foreach( $args as $key => $value ) {
			$this->$key = $value;
		}

		return $this;
	}

	protected function set_base_url() {
		$variables = [
			'{username}'	=> $this->username,
			'{password}'	=> $this->password,
			'{api_key}'		=> $this->api_key,
			'{from}'		=> $this->from,
		];
		$this->base_url = str_replace( array_keys( $variables ), array_values( $variables ), $this->url_template );
		return $this;
	}

	abstract protected function prepare();
	/**
	 * Send SMS
	 *
	 * @param string|array $to
	 * @param string|array $text If the type is not empty, the text will get automatically from settings. For custom text it should be an array with index of each $to number.
	 * @param string $type (optional) Type of the message. The type should contain group and type separated by a dot. For example: 'group.type'. If type was empty, it mean's custom text
	 * @return void
	 */
	public function prepare_send( $to, string $text = '', string $type = '', array $custom_variables = [] ) {
		// Get type
		$settings = Utils::get_settings();

		if( !is_array( $to ) ) {
			$to = [$to];
		}
		$this->to = array_map( fn( $number ) => Sanitizers::phone( $number ), $to );

		if( !empty( $type ) ) {
			$text = DrPlusUtils::get_nested_value( $settings['messages'], $type );
			if( !empty( $text ) ) {
				$template_text = $text;
				$text = Utils::apply_variables( $template_text, $this->to, $type, $custom_variables );

				if( !$this->variable_mode ) {
					$keys = explode( ";", $template_text );
					$values = explode( ";", $text );
					$keys = array_map( fn( $string ) => sanitize_text_field( $string ), $keys ); // Sanitize
					$values = array_map( fn( $string ) => sanitize_text_field( $string ), $values ); // Sanitize
					$exploded_pattern = array_combine( $keys, $values );
					foreach( $exploded_pattern as $key => $value ) {
						$key = str_replace( ["{", "}"], "", $key );
						$this->exploded_pattern[$key] = $value;
					}
				} else {
					$variables = explode( ";", $text );
					foreach( $variables as $part ) {
						$part = explode( ":", $part, 2 );
						$part = array_map( fn( $string ) => sanitize_text_field( $string ), $part ); // Sanitize
						$this->exploded_pattern[$part[0]] = $part[1];
					}
				}

				$this->pattern = DrPlusUtils::get_nested_value( $settings['settings'], $type )['pattern'];
			} else {
				return new \WP_Error( 'invalid_message_type', __( 'Invalid message type', 'drplus' ) );
			}
		} else {
			$this->pattern = '';
		}

		$this->text = sanitize_textarea_field( $text );
		if( DRPLUS_DEV ) {
			$now = current_time( 'm-d-H-i' );
			$name = wp_unique_id( "{$to[0]}-$now-" );
			file_put_contents( DRPLUS_DIR . "sms_log/{$name}.txt", $text );
		}
	}

	abstract public function _send();
	abstract public function _send_by_pattern( string $pattern );

	public function send( $to, $text = '', string $type = '', array $custom_variables = [] ) {
		$this->prepare_send( $to, $text, $type, $custom_variables );
		return $this->_send( $this->to, $this->text );
	}

	public function send_by_pattern( $to, string $type, array $custom_variables = [] ) {
		$this->prepare_send( $to, '', $type, $custom_variables );
		if( empty( $this->pattern ) ) return;
		return $this->_send_by_pattern( $this->pattern );
	}
}