<?php
namespace DrPlus\AJAX;

use DrPlus\AJAX;
use DrPlus\Utils\Cache as UtilsCache;

class Cache extends AJAX {
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

	public function sync() {
		$this->set_request_data();
		
		UtilsCache::sync_all_sections();

		$this->result( 'success' );
	}
}