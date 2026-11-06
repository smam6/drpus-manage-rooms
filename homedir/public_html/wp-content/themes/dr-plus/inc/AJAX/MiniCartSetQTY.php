<?php
namespace DrPlus\AJAX;

use DrPlus\AJAX;
use DrPlus\Utils;

class MiniCartSetQTY extends AJAX {
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

	public function update() {
		$this->set_request_data();
		$this->check_requires( ['item_qty'], true, false );
		$cart_item_key = Utils::convert_chars( $this->data['item_key'] );
		
		$this->check_nonce( "update_mini_cart-{$cart_item_key}" );

		$qty = Utils::convert_chars( $this->data['item_qty'], true, 'absint' );
		
		WC()->cart->set_quantity( $cart_item_key, $qty );
		\WC_AJAX::get_refreshed_fragments();
		die;
	}
}