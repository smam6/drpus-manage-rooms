<?php
namespace Sheyda\Wallet\Backend;

class WalletSettings {
	public static function view() {
		?>
		<div id="sheyda-wallet-header">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Wallet settings', 'sheyda_wallet' ) ?></h1>
			<hr class="wp-header-end">
		</div>
		<?php
	}
}