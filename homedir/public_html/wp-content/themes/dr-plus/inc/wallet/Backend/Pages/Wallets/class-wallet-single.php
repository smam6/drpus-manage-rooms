<?php
namespace Sheyda\Wallet\Backend;

use MJ\Whitebox\Utils\Formatters;
use Sheyda\Wallet\Backend\Pages\ListTables\Ledgers;
use SheydaWalletUtils as WalletUtils;

class WalletSingle {
	public static function view() {
		$user_id = WalletUtils::convert_chars( $_GET['user'], true, 'absint' );
		$user = get_user_by( 'id', $user_id );
		$user_wallet = WalletUtils::get_user_balance( $user_id );
		?>
		<div id="sheyda-wallet-header">
			<h1 class="wp-heading-inline"><?php echo esc_html( $user->display_name ) ?></h1>
			<hr class="wp-header-end">
		</div>
		<p><?php printf( __( "Total balance: %s", 'sheyda_wallet' ), Formatters::price( $user_wallet->balance, true ) ) ?></p>
		<p><?php printf( __( "Locked: %s", 'sheyda_wallet' ), Formatters::price( $user_wallet->locked, true ) ) ?></p>
		<p><?php printf( __( "Available balance: %s", 'sheyda_wallet' ), Formatters::price( WalletUtils::get_user_balance_amount( $user_id ), true ) ) ?></p>
		<hr>
		<?php
		include( SHEYDA_WALLET_DIR . "Backend/Pages/ListTables/Ledgers.php" );
		$table = new Ledgers;
		$table->prepare_items();
		$table->views();
		$table->display();
	}
}