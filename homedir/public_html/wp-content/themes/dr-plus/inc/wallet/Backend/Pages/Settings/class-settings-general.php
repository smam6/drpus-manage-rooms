<?php
namespace Sheyda\Wallet\Backend;

use SheydaWalletUtils as WalletUtils;
use Sheyda\Wallet\AdminScripts;
use Sheyda\Wallet\Backend\Settings;
use Sheyda\Wallet\PublicScripts;
use Sheyda\Wallet\Utils\AdminUI;
use Sheyda\Wallet\Utils\Settings as UtilsSettings;

class GeneralSettings extends Settings {
	public static function view() {
		$prefix = parent::$PREFIX;

		// Get wc orders
		$order_statuses = wc_get_order_statuses();

		$settings = UtilsSettings::get_settings( 'general' );

		?>
		<form method="post" action="" class="<?php echo $prefix ?>section-wrap">
			<?php parent::create_nonce(); ?>
			<table class="form-table">
				<tbody>
					<tr>
						<th>
							<label for="<?php echo $prefix ?>enable"><?php esc_html_e( 'Enable/Disable', 'sheyda_wallet' ) ?></label>
						</th>
						<td>
							<?php
							AdminUI::switch( [
								'label'		=> esc_html__( 'Enable wallet', 'sheyda_wallet' ),
								'name'		=> $prefix . "enable",
								'id'		=> $prefix . "enable",
								'value'		=> 1,
								'active'	=> WalletUtils::to_bool( $settings['enable'] )
							] );
							?>
						</td>
					</tr>
					<tr>
						<th>
							<label for="<?php echo $prefix ?>enable_refund"><?php esc_html_e( 'Enable refund', 'sheyda_wallet' ) ?></label>
						</th>
						<td>
							<?php
							AdminUI::switch( [
								'label'		=> esc_html__( 'Transfer the refund amount to the user’s wallet automatically when an order is refunded.', 'sheyda_wallet' ),
								'name'		=> $prefix . "enable_refund",
								'id'		=> $prefix . "enable_refund",
								'value'		=> 1,
								'active'	=> WalletUtils::to_bool( $settings['enable_refund'] )
							] );
							?>
						</td>
					</tr>
					<tr>
						<th>
							<label for="<?php echo $prefix ?>"><?php esc_html_e( 'Order Status for Wallet Payment Gateway', 'sheyda_wallet' ) ?></label>
						</th>
						<td>
							<?php
							AdminUI::select_with_label( [
								'label'		=> esc_html__( 'Order status', 'sheyda_wallet' ),
								'name'		=> $prefix . "wc_purchase_order_status",
								'id'		=> $prefix . "wc_purchase_order_status",
								'classes'	=> ['sheyda-wallet-select2'],
								'options'	=> $order_statuses,
								'value'		=> $settings['wc_purchase_order_status']
							] )
							?>
						</td>
					</tr>
					<tr>
						<th>
							<label for="<?php echo $prefix ?>"><?php esc_html_e( 'Wallet item title in my account page', 'sheyda_wallet' ) ?></label>
						</th>
						<td>
							<?php
							AdminUI::input_with_label( [
								'label'		=> esc_html__( 'Title', 'sheyda_wallet' ),
								'name'		=> $prefix . "myaccount_item_title",
								'id'		=> $prefix . "myaccount_item_title",
								'value'		=> $settings['myaccount_item_title'],
							] )
							?>
						</td>
					</tr>
					<tr>
						<th>
							<label for="<?php echo $prefix ?>"><?php esc_html_e( 'Wallet item position in my account page', 'sheyda_wallet' ) ?></label>
						</th>
						<td>
							<?php
							AdminUI::input_with_label( [
								'label'		=> esc_html__( 'Position', 'sheyda_wallet' ),
								'name'		=> $prefix . "myaccount_item_position",
								'id'		=> $prefix . "myaccount_item_position",
								'value'		=> $settings['myaccount_item_position'],
								'type'		=> 'number'
							] )
							?>
						</td>
					</tr>
				</tbody>
			</table>

			<button type="submit" id="<?php echo $prefix ?>submit"><?php esc_html_e( 'Save changes', 'sheyda_wallet' ) ?></button>
		</form>
		<?php
	}

	public static function save() {
		$prefix = parent::$PREFIX;
		$settings = [
			'enable'						=> WalletUtils::to_bool( $_POST[$prefix . "enable"] ?? false ),
			'enable_refund'					=> WalletUtils::to_bool( $_POST[$prefix . "enable_refund"] ?? false ),
			'wc_purchase_order_status'		=> WalletUtils::convert_chars( $_POST[$prefix . "wc_purchase_order_status"] ),
			'myaccount_item_position'		=> WalletUtils::convert_chars( $_POST[$prefix . "myaccount_item_position"], 'absint' ),
			'myaccount_item_title'			=> WalletUtils::convert_chars( $_POST[$prefix . "myaccount_item_title"] ),
		];

		UtilsSettings::save_settings( 'general', $settings );
	}

	public static function enqueue() {
		AdminScripts::form_group();
		AdminScripts::switch();
		PublicScripts::select2();
	}
}