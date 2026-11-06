<?php
// remove payment form from #order_review and move under #customer_details

use DrPlus\Utils\UI;

remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
add_action( 'woocommerce_checkout_after_customer_details', 'drplus_woocommerce_checkout_payment' );
if( !function_exists( 'drplus_woocommerce_checkout_payment' ) ) {
	function drplus_woocommerce_checkout_payment( $is_book_order = false ) {
		if( !$is_book_order ) woocommerce_checkout_payment();
	}
}

if( !function_exists( 'drplus_wc_order_customer_address_icon' ) ) {
	function drplus_wc_order_customer_address_icon( $type ) {
		if( $type === 'billing' ) {
			$icon = 'drplus-icon-location';
		} else {
			$icon = 'drplus-icon-delivery';
		}
		?>
		<div class="order-address-icon-wrap">
			<i class="order-address-icon <?php echo $icon ?>"></i>
		</div>
		<?php
	}
}
add_action( 'woocommerce_order_details_after_customer_address', 'drplus_wc_order_customer_address_icon' );

if( !function_exists( 'drplus_wc_gateway_icons' ) ) {
	function drplus_wc_gateway_icons( $title, $gateway_id ) {
		$icons = [
			'bacs'		=> [
				'normal'	=> 'drplus-icon-bitcoin-card',
			],
			'cheque'	=> [
				'normal'	=> 'drplus-icon-cheque',
			],
			'cod'		=> [
				'normal'	=> 'drplus-icon-house',
			],
		];
		if( in_array( $gateway_id, array_keys( $icons ) ) ) {
			$title .= '<i class="payment_gateway-icon ' . $icons[$gateway_id]['normal'] . '"></i>';
		}

		return $title;
	}
}
add_filter( 'woocommerce_gateway_title', 'drplus_wc_gateway_icons', 10, 2 );

if( !function_exists( 'drplus_wc_order_get_formatted_billing_address' ) ) {
	function drplus_wc_order_get_formatted_billing_address( $address, $raw_address ) {
		if( is_admin() ) return $address;

		return WC()->countries->get_formatted_address( $raw_address, ' - ' );
	}
}
add_filter( 'woocommerce_order_get_formatted_billing_address', 'drplus_wc_order_get_formatted_billing_address', 10, 2 );

if( !function_exists( 'drplus_wc_order_get_formatted_shipping_address' ) ) {
	function drplus_wc_order_get_formatted_shipping_address( $address, $raw_address ) {
		if( is_admin() ) return $address;

		return WC()->countries->get_formatted_address( $raw_address, ' - ' );
	}
}
add_filter( 'woocommerce_order_get_formatted_shipping_address', 'drplus_wc_order_get_formatted_shipping_address', 10, 2 );

if( !function_exists( "drplus_wc_checkout_add_map_popup" ) ) {
	function drplus_wc_checkout_add_map_popup() {
		if( ( is_checkout() ) ) {
			if( is_order_received_page() ) {
				UI::map_popup();
			}
		}

		if( is_account_page() ) {
			UI::map_popup();
		}
	}
}
add_action( 'wp_footer', 'drplus_wc_checkout_add_map_popup' );