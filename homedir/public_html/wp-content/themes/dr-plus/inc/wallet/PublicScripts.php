<?php
namespace Sheyda\Wallet;

use SheydaWalletUtils as WalletUtils;

class PublicScripts {
	public static function main() {
		wp_enqueue_style( 'sheyda-wallet-icons', SHEYDA_WALLET_URI . "assets/css/iconly.min.css", [], SHEYDA_WALLET_VERSION );

		WalletUtils::enqueue_script( 'sheyda-wallet-utils', SHEYDA_WALLET_URI . "assets/js/utils.js" );
		WalletUtils::enqueue_script( 'sheyda-wallet', SHEYDA_WALLET_URI . "assets/js/wallet.js" );

		WalletUtils::wallet_vars_localize();
	}

	public static function select2() {
		wp_enqueue_style( 'sheyda-wallet-select2', SHEYDA_WALLET_URI . "assets/libs/select2/select2.min.css", [], SHEYDA_WALLET_VERSION );
		wp_enqueue_script( 'sheyda-wallet-select2', SHEYDA_WALLET_URI . "assets/libs/select2/select2.min.js", ['jquery'], SHEYDA_WALLET_VERSION, true );
	}

	public static function coloris() {
		wp_enqueue_style( 'sheyda-wallet-coloris', SHEYDA_WALLET_URI . "assets/libs/coloris/coloris.min.css", [], SHEYDA_WALLET_VERSION );
		wp_enqueue_script( 'sheyda-wallet-coloris', SHEYDA_WALLET_URI . "assets/libs/coloris/coloris.min.js", ['jquery'], SHEYDA_WALLET_VERSION, true );
	}
}
add_action( 'admin_enqueue_scripts', [PublicScripts::class, 'main'] );
add_action( 'wp_enqueue_scripts', [PublicScripts::class, 'main'] );