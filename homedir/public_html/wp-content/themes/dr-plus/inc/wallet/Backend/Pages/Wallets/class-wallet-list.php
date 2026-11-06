<?php
namespace Sheyda\Wallet\Backend;

class WalletsList {
	public static function view( $wallets_table ) {
		?>
		<div id="sheyda-wallet-header">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Wallets', 'sheyda_wallet' ) ?></h1>
			<hr class="wp-header-end">
		</div>
		<form method="GET" id="posts-filter">
			<input type="hidden" name="page" value="sheyda-wallet">
			<?php
			$wallets_table->prepare_items();
			$wallets_table->views();
			$wallets_table->search_box( __( 'Search user', 'sheyda_wallet' ), 'search_user' );
			$wallets_table->display();
			?>
		</form>
		<?php
	}
}