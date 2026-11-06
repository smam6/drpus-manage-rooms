<?php
namespace DrPlus\AJAX;

use DrPlus\AJAX;
use DrPlus\Utils;
use DrPlus\Utils\Skyroom as UtilsSkyroom;

class Skyroom extends AJAX {
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

	public function get_room_link() {
		$this->set_request_data();

		$order_id = Utils::convert_chars( $this->data['order_id'], true, 'absint' );
		$user_id = Utils::convert_chars( $this->data['user_id'], true, 'absint' );

		$this->check_nonce( sprintf( 'drplus_booking_create_room_%s_%s', $order_id, $user_id ) );
		
		$max_try = 10;
		$video_link = "";
		for ($i=0; $i < $max_try; $i++) { 
			$video_link = UtilsSkyroom::get_room_link( $order_id, $user_id );
			if( !is_wp_error( $video_link ) ) break;
		}

		if( !empty( $video_link ) && !is_wp_error( $video_link ) ) {
			$this->result( 'success', [
				'link'	=> $video_link,
			] );
		} else {
			$this->result( 'error' );
		}
	}
}