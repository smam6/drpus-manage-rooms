<?php
namespace DrPlus\AJAX;

use DrPlus\AJAX;
use DrPlus\Utils;
use DrPlus\Utils\Notifications as UtilsNotifications;

class Notifications extends AJAX {
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

	public function set_read() {
		$this->set_request_data();

		$notif_id = Utils::convert_chars( $this->data['id'], true, 'absint' );
		UtilsNotifications::add_user_read( $notif_id );

		$this->result( 'success', [
			'unreadCount'	=> UtilsNotifications::count_user_unread(),
		] );
	}
}