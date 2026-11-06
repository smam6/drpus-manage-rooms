<?php
namespace DrPlus\AJAX;

use DrPlus\AJAX;
use DrPlus\Utils;
use DrPlus\Utils\User;

class GetUsers extends AJAX {
	public static function get_instance() {
		static $instance = null;
		if( $instance === null ) {
			$instance = new self;
		}
		return $instance;
	}

	public function __construct() {
		return $this;
	}

	/**
	 * Undocumented function
	 *
	 * $template values:
	 * %display_name%
	 * %email%
	 * %phone%
	 * %nid%
	 * 
	 * @return void
	 */
	public function get_users() {
		$this->set_request_data();

		$type = !empty( $this->data['type'] ) ? Utils::convert_chars( $this->data['type'] ) : 'users';
		$type = Utils::ensure_values_in_array( $type, ['users', 'specialists', 'non_specialists'], 'users' );
		$template = !empty( $this->data['template'] ) ? $this->data['template'] : '%display_name% (%email%)';

		$search_term = Utils::convert_chars( $this->data['name'] );

		$get_users_args = [
			'search'	=> "*{$search_term}*",
			'number'	=> 50,
		];
		if( $type == 'specialists' ) {
			$get_users_args['specialists'] = true;
		} else if( $type == 'non_specialists' ) {
			$get_users_args['specialists'] = false;
		}
		if( strpos( $template, '%nid%' ) !== false ) {
			$get_users_args['search_in_nid'] = true;
		}

		$users = [];
		foreach( get_users( $get_users_args ) as $user ) {
			$text = $template;
			$text = str_replace( [
				'%display_name%',
				'%email%',
				'%phone%',
				'%nid%',
			], [
				$user->display_name,
				$user->user_email,
				User::get_phone( $user->ID ),
				User::get_nid( $user->ID ),
			], $text );
			$users[] = [
				'id'	=> $user->ID,
				'text'	=> $text,
			];
		}

		$this->result( 'success', $users );
	}
}