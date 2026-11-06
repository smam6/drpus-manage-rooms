<?php
namespace DrPlus\AJAX;

use DrPlus\AJAX;
use DrPlus\Utils;
use DrPlus\Utils\Wishlist as UtilsWishlist;

class Wishlist extends AJAX {
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

	public function toggle() {
		$this->set_request_data();

		$product_id = Utils::convert_chars( $this->data['product_id'], true, 'absint' );
		$this->check_nonce( "wishlist-toggle-{$product_id}" );
		
		if( UtilsWishlist::is_in_wishlist( $product_id ) ) {
			$res = UtilsWishlist::remove_from_wishlist( $product_id );
			$icon_class = 'drplus-icon-heart';
			$status = 'remove';
		} else {
			$res = UtilsWishlist::add_to_wishlist( $product_id );
			$icon_class = 'drplus-icon-heart-bold';
			$status = 'added';
		}

		if( !empty( $res ) ) {
			$this->result( 'success', [
				'icon_class'	=> $icon_class,
				'status'		=> $status,
			] );
		} else {
			if( $status == 'add' ) {
				$this->result( 'error', __( 'There was an error while adding product to wishlist.', 'drplus' ) );
			} else {
				$this->result( 'error', __( 'There was an error while removing product from wishlist.', 'drplus' ) );
			}
		}
	}
}