<?php
if( !function_exists( 'modify_wallet_paid_order_status' ) ) {
	function modify_wallet_paid_order_status( $status, $order ) {
		if( $status == 'processing' ) {
			return $status;
		}
		$is_booking = !empty( $order->get_meta( '_booking_data' ) );
		if( $is_booking ) {
			return 'processing';
		}
		return $status;
	}
}
add_filter( 'sheyda/wallet/order/new_status', 'modify_wallet_paid_order_status', 10, 2 );

function drplus_remove_checkout_validations() {
	// Remove phone validation for Persian WooCommerce
	if( class_exists( 'PW_Tools_General' ) ) {
		$instance_PW_Tools_General = new \PW_Tools_General;
		remove_action( 'woocommerce_after_checkout_validation', [ $instance_PW_Tools_General, 'validate_phone' ], 10 );
	}
}
add_action( 'drplus/wallet/topup/checkout/before_form', 'drplus_remove_checkout_validations' );