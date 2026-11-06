<?php
namespace Sheyda\Wallet\Backend;

use Sheyda\Wallet\Backend\Pages\ListTables\Ledgers;

class WalletTransactions {
	public static function view() {
		?>
		<div id="sheyda-wallet-header">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Wallet transactions', 'sheyda_wallet' ) ?></h1>
			<hr class="wp-header-end">
		</div>
		<?php
		include_once( SHEYDA_WALLET_DIR . "Backend/Pages/ListTables/Ledgers.php" );
		$table = new Ledgers;
		$table->show_all = true;
		$table->prepare_items();
		?>
		<form method="GET" id="posts-filter">
			<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ?? '' ); ?>">
			<?php if( !empty( $_REQUEST['section'] ) ) { ?>
				<input type="hidden" name="section" value="<?php echo esc_attr( $_REQUEST['section'] ); ?>">
			<?php } ?>
			<?php
			$table->views();
			$table->search_box( __( 'Search user', 'sheyda_wallet' ), 'search_user' );
			$table->display();
			?>
		</form>
		<?php
	}
}
