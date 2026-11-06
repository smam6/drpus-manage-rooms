<?php
namespace DrPlus\AJAX;

use DrPlus\AJAX;
use DrPlus\Utils;
use DrPlus\Utils\Booking;

class GetAvailableTimes extends AJAX {
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

	public function get_available_times() {
		$this->set_request_data();

		$date = Utils::convert_chars( $this->data['date'] );
		$specialist_id = Utils::convert_chars( $this->data['specialist'], true, 'absint' );
		$office_id = Utils::convert_chars( $this->data['office'] );
		$chunk = Utils::convert_chars( $this->data['chunk'], true, 'absint' );

		// check availability
		$time_slots = Booking::get_available_time_slots( $date, $specialist_id, $office_id, $chunk );

		$this->result( 'success', $time_slots );
	}
}