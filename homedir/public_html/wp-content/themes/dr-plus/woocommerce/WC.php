<?php

use DrPlus\Utils;
use DrPlus\Utils\Options;
use DrPlus\Utils\WC;

if( !function_exists( 'drplus_redirect_wc_endpoints' ) ) {
	function drplus_redirect_wc_endpoints() {
		$enable_wc_shop = WC::get_wc_shop_status();
		if( $enable_wc_shop ) return;

		// redirect myaccount shop related endpoints
		if( is_account_page() ) {
			$shop_endpoints = [
				'orders',
				'downloads',
				'wishlist'
			];
			$current_endpoint = WC()->query->get_current_endpoint();
			if( in_array( $current_endpoint, $shop_endpoints ) ) {
				wp_redirect( wc_get_account_endpoint_url( 'dashboard' ) );
				exit;
			}
		}

		// redirect shop and single product page to home url
		if( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() || is_product() ) {
			wp_redirect( home_url() );
			exit;
		}
	}
}
add_action( 'wp_loaded', 'drplus_redirect_wc_endpoints' );

add_filter( 'woocommerce_enqueue_styles', '__return_false' ); // Disable all wc styles

if( !function_exists( "drplus_wc_product_classes" ) ) {
	function drplus_wc_product_classes( $classes ) {
		$classes[] = 'slider-slide';
		return $classes;
	}
}
add_filter( 'woocommerce_post_class', 'drplus_wc_product_classes', 99 );

if( !function_exists( "drplus_wc_return_to_shop_text" ) ) {
	function drplus_wc_return_to_shop_text( $text ) {
		return Options::get_options( [
			'wc_return_to_shop_text'	=> __( 'Return to shop', 'drplus' )
		] )['wc_return_to_shop_text'];
	}
}
add_filter( 'woocommerce_return_to_shop_text', 'drplus_wc_return_to_shop_text' );

if( !function_exists( "drplus_wc_sku_status" ) ) {
	function drplus_wc_sku_status( $status ) {
		return Utils::to_bool( Options::get_options( ['sku_status' => true] )['sku_status'] );
	}
}
add_filter( 'wc_product_sku_enabled', 'drplus_wc_sku_status' );

if( !function_exists( "drplus_wc_modify_admin_access" ) ) {
	function drplus_wc_modify_admin_access( $prevent ) {
		if( $prevent ) {
			$additional_files = ['async-upload.php'];

			$file = basename( sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_FILENAME'] ) ) );
			if( in_array( $file, $additional_files ) ) {
				$prevent = false;
			}
		}

		return $prevent;
	}
}
add_filter( 'woocommerce_prevent_admin_access', 'drplus_wc_modify_admin_access', 99 );

include( DRPLUS_DIR . "woocommerce/functions/single.php" );
include( DRPLUS_DIR . "woocommerce/functions/loop.php" );
include( DRPLUS_DIR . "woocommerce/functions/myaccount.php" );
include( DRPLUS_DIR . "woocommerce/functions/checkout.php" );
include( DRPLUS_DIR . "woocommerce/functions/cart.php" );
include( DRPLUS_DIR . "woocommerce/functions/mini-cart.php" );
include( DRPLUS_DIR . "woocommerce/functions/filters.php" );