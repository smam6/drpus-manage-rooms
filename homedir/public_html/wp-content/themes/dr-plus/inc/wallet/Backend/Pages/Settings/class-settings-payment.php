<?php
namespace Sheyda\Wallet\Backend;

use MJ\Whitebox\Utils\Formatters;
use MJ\Whitebox\Utils\Sanitizers;
use Sheyda\Wallet\AdminScripts;
use Sheyda\Wallet\Backend\Settings;
use Sheyda\Wallet\Utils\AdminUI;
use Sheyda\Wallet\Utils\Settings as UtilsSettings;
use SheydaWalletUtils as WalletUtils;

class PaymentSettings extends Settings {
	public static function view() {
		$prefix = parent::$PREFIX;

		$settings = UtilsSettings::get_settings( 'payment' );

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
								'label'		=> esc_html__( 'Allow Partial Payments using Wallet', 'sheyda_wallet' ),
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
							<label for="<?php echo $prefix ?>min_cart_value"><?php printf( esc_html__( 'Minimum cart value (%s)', 'sheyda_wallet' ), WalletUtils::get_price_symbol() ) ?></label>
						</th>
						<td>
							<?php
							AdminUI::input_with_label( [
								'label'			=> esc_html__( 'Minimum cart value to enable purchase with wallet', 'sheyda_wallet' ),
								'name'			=> $prefix . "min_cart_value",
								'id'			=> $prefix . "min_cart_value",
								'input_classes'	=> ['sheyda-wallet-price-input', 'ltr'],
								'value'			=> Formatters::price( $settings['min_cart_value'] )
							] );
							?>
						</td>
					</tr>
					<tr>
						<th>
							<label for="<?php echo $prefix ?>max_allowable_use"><?php printf( esc_html__( 'Maximum Allowable use (%s)', 'sheyda_wallet' ), WalletUtils::get_price_symbol() ) ?></label>
						</th>
						<td>
							<?php
							AdminUI::input_with_label( [
								'label'			=> esc_html__( 'Minimum value that user can purchase with wallet', 'sheyda_wallet' ),
								'name'			=> $prefix . "max_allowable_use",
								'id'			=> $prefix . "max_allowable_use",
								'input_classes'	=> ['sheyda-wallet-price-input', 'ltr'],
								'value'			=> Formatters::price( $settings['max_allowable_use'] )
							] );
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
			'enable'			=> WalletUtils::to_bool( $_POST[$prefix . "enable"] ?? false ),
			'min_cart_value'	=> Sanitizers::price( $_POST[$prefix . 'min_cart_value'] ?? 0 ),
			'max_allowable_use'	=> Sanitizers::price( $_POST[$prefix . 'max_allowable_use'] ?? 0 ),
		];

		UtilsSettings::save_settings( 'payment', $settings );
	}

	public static function enqueue() {
		AdminScripts::form_group();
		AdminScripts::switch();
	}
}