<?php
namespace DrPlus\AJAX;

use DrPlus\AJAX;
use DrPlus\Utils;
use DrPlus\Utils\Booking;
use DrPlus\Utils\SubscriptionPlans;

class ProcessBuyPlan extends AJAX {
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

	public function process_buy_plan() {
		$this->set_request_data();

		$plan_id = Utils::convert_chars( $this->data['plan_id'], true );

		$all_plans = SubscriptionPlans::get_plans( true );
		$selected_plan = null;
		foreach( $all_plans as $plan ) {
			if( $plan['id'] == $plan_id ) {
				$selected_plan = $plan;
				break;
			}
		}

		if( empty( $selected_plan ) ) {
			$this->result( 'error', [
				'message'	=> esc_html__( 'Selected plan is not available', 'drplus' ),
				'code'		=> 'not_available'
			] );
		}

		// Check if plan is free
		if( ( empty( $selected_plan['sale_price'] ) && $selected_plan['sale_price'] !== "" ) || empty( $selected_plan['reg_price'] ) ) {
			// --- Plan is free ---
			// Add plan for user and return success
		}

		// --- So Plan isn't free ---
		// Prepare wc product
		$booking_product_id = Booking::get_booking_product_id(); // Use book product for pay plan price
		WC()->cart->empty_cart();
		WC()->cart->add_to_cart( $booking_product_id, 1, 0, [], ['is_plan' => true, 'plan_data' => $selected_plan] );
		WC()->session->set( 'plan_data', $selected_plan );
		WC()->session->__unset( 'book_data' ); // Clear book data to prevent conflict
		
		$this->result( 'success', [
			'checkoutUrl' => wc_get_checkout_url()
		] );
	}
}
