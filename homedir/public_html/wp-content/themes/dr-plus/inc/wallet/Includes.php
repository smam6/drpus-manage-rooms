<?php
namespace Sheyda\Wallet;

use SheydaWalletUtils as WalletUtils;
use Sheyda\Wallet\Utils\Settings;

class Includes {
	public static function main() {
		include( SHEYDA_WALLET_DIR . "WalletUtils.php" );
		include( SHEYDA_WALLET_DIR . "Utils/utils-settings.php" );
		include( SHEYDA_WALLET_DIR . "Utils/utils-wc.php" );

		if( is_admin() ) {
			include( SHEYDA_WALLET_DIR . "Utils/utils-admin-ui.php" );
		}

		if( wp_doing_ajax() ) {
			include( SHEYDA_WALLET_DIR . "AJAX.php" );
		}

		include( SHEYDA_WALLET_DIR . "Woocommerce/ProcessTopUp.php" );

		self::components();

		include( SHEYDA_WALLET_DIR . "MenuItem.php" );
	}

	public static function components() {
		include( SHEYDA_WALLET_DIR . "Components/component-pagination-custom.php" );
	}

	public static function models() {
		include( SHEYDA_WALLET_DIR . "Models/Ledger.php" );
		include( SHEYDA_WALLET_DIR . "Models/Balances.php" );
		include( SHEYDA_WALLET_DIR . "Models/Withdrawals.php" );
		include( SHEYDA_WALLET_DIR . "Models/Locks.php" );
	}

	public static function backend() {
		include( SHEYDA_WALLET_DIR . "Backend/Pages/Wallets.php" );
	}

	public static function public_scripts() {
		include( SHEYDA_WALLET_DIR . "PublicScripts.php" );
	}

	public static function admin_scripts() {
		include( SHEYDA_WALLET_DIR . "AdminScripts.php" );
	}

	public static function woocommerce() {
		if( !WalletUtils::is_wc_active() ) return;
		if( !Settings::get_settings()['enable'] ) return;
		include( SHEYDA_WALLET_DIR . "Woocommerce/woocommerce.php" );
		include( SHEYDA_WALLET_DIR . "Woocommerce/PartialPayment.php" );
	}
}
Includes::main();
Includes::models();
Includes::public_scripts();
Includes::woocommerce();
if( is_admin() ) {
	Includes::admin_scripts();
	Includes::backend();

}