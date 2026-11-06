<?php
namespace DrPlus\AJAX;

use DrPlus\AJAX;
use DrPlus\Utils;
use DrPlus\Utils\User;

class GetUserData extends AJAX {
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

	public function get_user_data() {
		$this->set_request_data();

		$user_id = Utils::convert_chars( $this->data['user_id'] );

		$user = get_user_by( 'ID', $user_id );

		$meta_keys = ['mobile', 'nid', 'specialist_code', 'gender'];
		$user_data = [
			'first_name'	=> $user->first_name,
			'last_name'		=> $user->last_name,
			'email'			=> $user->user_email,
			'avatar'		=> User::get_avatar_id( $user_id ),
		];

		foreach ( $meta_keys as $key ) {
			$user_data[$key] = get_user_meta( $user_id, $key, true );
		}

		$user_data['avatar_url'] = get_avatar_url( $user_id );

		$this->result( 'success', $user_data );
	}
}