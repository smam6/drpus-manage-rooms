<?php
namespace DrPlus\Integrations;

class wp_rocket {
	public static function rejected_cookies( $cookies ) {
		if( !in_array( 'woocommerce_cart_hash', $cookies ) ) {
			$cookies[] = 'woocommerce_cart_hash';
		}
		if( !in_array( 'woocommerce_items_in_cart', $cookies ) ) {
			$cookies[] = 'woocommerce_items_in_cart';
		}

		return $cookies;
	}
}
// Deactivate WooCommerce Refresh Cart Fragments Cache
add_filter( 'rocket_cache_wc_empty_cart', '__return_false' );
add_filter( 'rocket_cache_reject_cookies', [wp_rocket::class, 'rejected_cookies'] );