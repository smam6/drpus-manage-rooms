<?php
namespace Sheyda\Wallet\AJAX;

use MJ\Whitebox\Utils\Sanitizers;
use Sheyda\Wallet\AJAX;
use SheydaWalletUtils as WalletUtils;

class ProcessTopUp extends AJAX {
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

	public function process_topup() {
		$this->set_request_data();

		$amount = Sanitizers::price( $this->data['amount'], true );

		// Prepare wc product
		$wallet_product_id = WalletUtils::get_wallet_product_id();
		WC()->cart->empty_cart();
		WC()->cart->add_to_cart(
			$wallet_product_id,
			1,
			0,
			[],
			[
				'is_wallet_topup'      => true,
				'wallet_topup_amount'  => $amount,
			]
		);
		WC()->session->set( '_topup_amount', $amount );
		
		$this->result( 'success', [
			'checkoutUrl' => wc_get_checkout_url()
		] );
	}
}
