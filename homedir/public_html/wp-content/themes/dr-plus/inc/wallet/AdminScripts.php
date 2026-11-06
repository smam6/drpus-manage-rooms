<?php
namespace Sheyda\Wallet;

class AdminScripts {
	public static function tabs() {
		wp_enqueue_style( 'sheyda_wallet-tabs', SHEYDA_WALLET_URI . "assets/css/backend/components/tabs.min.css", [], SHEYDA_WALLET_VERSION );
	}

	public static function switch() {
		wp_enqueue_style( 'sheyda_wallet-switch', SHEYDA_WALLET_URI . "assets/css/backend/components/switch.min.css", [], SHEYDA_WALLET_VERSION );
		if( is_rtl() ) {
			wp_enqueue_style( 'sheyda_wallet-switch-rtl', SHEYDA_WALLET_URI . "assets/css/backend/components/switch.rtl.min.css", [], SHEYDA_WALLET_VERSION );
		}
	}

	public static function switch_select() {
		wp_enqueue_style( 'sheyda_wallet-switch-select', SHEYDA_WALLET_URI . "assets/css/backend/components/switch-select.min.css", [], SHEYDA_WALLET_VERSION );
	}

	public static function alert() {
		wp_enqueue_style( 'sheyda_wallet-alert', SHEYDA_WALLET_URI . "assets/css/backend/components/alert.min.css", [], SHEYDA_WALLET_VERSION );
	}

	public static function form_group() {
		wp_enqueue_style( 'sheyda_wallet-form-group', SHEYDA_WALLET_URI . "assets/css/backend/components/form_group.min.css", [], SHEYDA_WALLET_VERSION );
	}
}