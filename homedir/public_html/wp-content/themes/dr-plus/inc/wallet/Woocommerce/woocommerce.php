<?php
namespace Sheyda\Wallet;

use SheydaWalletUtils as WalletUtils;
use Sheyda\Wallet\Utils\Settings;
use Sheyda\Wallet\Woocommerce\WCWallet;

class Woocommerce {
	public static function add_wallet_item( array $items ) {
		$settings = Settings::get_settings( 'general' );

		$items['sheyda-wallet'] = $settings['myaccount_item_title'];

		$position = $settings['myaccount_item_position'];

		WalletUtils::reposition_array_element( $items, 'sheyda-wallet', $position-1 );

		return $items;
	}

	public static function wallet_page_content() {
		wp_localize_script( 'sheyda-wallet-wc', 'walletWC', [
			'i18n'	=>  [
				'maxWithdrawalError' => esc_html__( 'Your requested amount is more than your wallet balance', 'sheyda_wallet' ),
				'minWithdrawalError' => esc_html__( 'Your requested amount is less than minimum withdrawal request amount', 'sheyda_wallet' ),
			]
		] );
		include_once( SHEYDA_WALLET_DIR . "Woocommerce/MyAccount/class-wc-wallet.php" );
		WCWallet::view();
	}

	public static function add_rewrite_endpoint() {
		add_rewrite_endpoint( 'sheyda-wallet', EP_PAGES );
	}

	private static function enqueue_custom_style() {
		$custom_style_file = Settings::get_style_file( 'path' );
		if( file_exists( $custom_style_file ) ) {
			$version_option_name = "sheyda_wallet_custom_style_version" . Settings::get_lang_suffix();
			wp_enqueue_style( 'sheyda-wallet-wc-custom', Settings::get_style_file( 'uri' ), [], get_option( $version_option_name, SHEYDA_WALLET_VERSION ) );
		}
	}

	public static function enqueue_scripts() {
		if( is_account_page() ) {
			if( !empty( $_GET['section'] ) && $_GET['section'] == 'financial' ) {
				wp_enqueue_script( 'wp-util' );
			}

			wp_enqueue_style( 'sheyda-wallet', SHEYDA_WALLET_URI . "assets/css/wallet.min.css", [], SHEYDA_WALLET_VERSION );
			wp_enqueue_style( 'sheyda-wallet-wc', SHEYDA_WALLET_URI . "assets/css/wc/my-account/wc-wallet.min.css", [], SHEYDA_WALLET_VERSION );
			if( SHEYDA_WALLET_DEV ) {
				wp_enqueue_script( 'sheyda-wallet-wc', SHEYDA_WALLET_URI . "assets/js/wc/my-account/wc-wallet.js", ['jquery'], SHEYDA_WALLET_VERSION, true );
			} else {
				wp_enqueue_script( 'sheyda-wallet-wc', SHEYDA_WALLET_URI . "assets/js/wc/my-account/wc-wallet.min.js", ['jquery'], SHEYDA_WALLET_VERSION, true );
			}

			self::enqueue_custom_style();

			include_once( SHEYDA_WALLET_DIR . "Woocommerce/MyAccount/class-wc-wallet.php" );
			WCWallet::enqueue();
		} else if( is_checkout() ) {
			wp_enqueue_style( 'sheyda-wallet', SHEYDA_WALLET_URI . "assets/css/wallet.min.css", [], SHEYDA_WALLET_VERSION );
			wp_enqueue_style( 'sheyda-wallet-wc-checkout', SHEYDA_WALLET_URI . "assets/css/wc/checkout.min.css", [], SHEYDA_WALLET_VERSION );
			self::enqueue_custom_style();
		}
	}

	public static function is_purchasable( $is_purchasable, $product ) {
		if( $product->get_id() == WalletUtils::get_wallet_product_id() ) {
			return true;
		}
		return $is_purchasable;
	}

	public static function hide_wallet_product( $query ) {
		static $executed = false;
		if( !$executed ) {
			if( $query->get( 'post_type' ) == 'product' ) {
				$not_in = $query->get( 'post__not_in' );
				if( !is_array( $not_in ) ) {
					$not_in = [];
				}
				$not_in[] = WalletUtils::get_wallet_product_id();

				$query->set( 'post__not_in', $not_in );

				$executed = true;
			}
		}
	}

	public static function process_financial_form() {
		include_once( SHEYDA_WALLET_DIR . "Woocommerce/MyAccount/class-wc-wallet.php" );
		WCWallet::save( 'financial' );
	}

	public static function process_withdrawal_form() {
		include_once( SHEYDA_WALLET_DIR . "Woocommerce/MyAccount/class-wc-wallet.php" );
		WCWallet::save( 'withdrawal' );
	}

	public static function add_refund_to_wallet( int $refund_id, array $args ) {
		$settings = Settings::get_settings( 'general' );
		if( !$settings['enable_refund'] ) return;

		$refund = new \WC_Order_Refund( $refund_id );
		$order = wc_get_order( $args['order_id'] );
		if( !$order || !$refund ) return;

		$refunded_amount = WalletUtils::convert_iranian_currency( $refund->get_amount( 'edit' ), $refund->get_currency(), get_woocommerce_currency() );
		if( is_wp_error( $refunded_amount ) ) {
			$logger = WalletUtils::build_logger();
			$logger->error( $refunded_amount->get_error_message(), [
				'function'	=> 'Woocommerce\add_refund_to_wallet'
			] );
			return;
		}

		$customer_id = $order->get_customer_id();
		if( $refunded_amount && $customer_id ) {
			// Create a wallet refund record for user
			$order_id = $order->get_id();
			$meta = [
				'order_id'	=> $order,
			];
			$reason = $refund->get_reason();
			$meta['description'] = sprintf( esc_html__( 'Refund of order #%s', 'sheyda-wallet' ), $order_id );
			if( $reason ) $meta['description'] .= ' - ' . $reason;
			WalletUtils::add_user_refund_record( $refunded_amount, $customer_id, 0, $order_id, $meta );
		}
	}

	public static function override_checkout_template_for_topup( $template, $template_name, $template_path ) {
		if( $template_name === 'checkout/form-checkout.php' ) {
			if( WalletUtils::cart_has_wallet_topup() ) {
				$settings = Settings::get_settings( 'topup' );
				if( $settings['enable_topup_checkout_template'] ) {
					return SHEYDA_WALLET_DIR . "Woocommerce/templates/topup-form-checkout.php";
				}
			}
		}
		return $template;
	}
}
add_filter( 'woocommerce_account_menu_items', [Woocommerce::class, 'add_wallet_item'], 10 );
add_action( "woocommerce_account_sheyda-wallet_endpoint", [Woocommerce::class, 'wallet_page_content'], 10 );
add_action( "init", [Woocommerce::class, 'add_rewrite_endpoint'] );
add_action( 'wp_enqueue_scripts', [Woocommerce::class, 'enqueue_scripts'] );
add_filter( 'woocommerce_is_purchasable', [Woocommerce::class, 'is_purchasable'], 10, 2 );
add_action( 'pre_get_posts', [Woocommerce::class, 'hide_wallet_product'] );

add_action( 'admin_post_sheyda_wallet_financial_form', [Woocommerce::class, 'process_financial_form'] );
add_action( 'admin_post_sheyda_wallet_withdrawal_form', [Woocommerce::class, 'process_withdrawal_form'] );

add_action( 'woocommerce_refund_created', [Woocommerce::class, 'add_refund_to_wallet'], 10, 2 );

add_filter( 'woocommerce_locate_template', [Woocommerce::class, 'override_checkout_template_for_topup'], 10, 3 );
