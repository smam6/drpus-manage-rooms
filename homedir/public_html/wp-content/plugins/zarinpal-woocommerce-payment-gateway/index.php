<?php
/*
Plugin Name: افزونه پرداخت امن زرین‌پال برای ووکامرس (ZarinPal for WooCommerce)
Version: 5.1.1
Description: افزونه درگاه پرداخت امن زرین‌پال برای فروشگاه ساز ووکامرس
Plugin URI: https://zarinpal.com
Author: Masoud Amini, Armin Zahedi
Author URI: http://www.zarinpal.com/
Text Domain: zarinpal-woocommerce-payment-gateway
WC requires at least: 3.0
WC tested up to: 9.5
Requires at least: 5.8
Tested up to: 7.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
*/

if (!defined('ABSPATH')) {
    exit;
}

include_once("class-wc-gateway-zarinpal.php");

add_action('before_woocommerce_init', function () {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
    }
});

add_action('woocommerce_blocks_loaded', 'zarinpal_gateway_block_support');
function zarinpal_gateway_block_support() {
    require_once __DIR__ . '/includes/class-wc-zarinpal-gateway-blocks-support.php';
    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
            $payment_method_registry->register(new WC_Zarinpal_Gateway_Blocks_Support);
        }
    );
}
