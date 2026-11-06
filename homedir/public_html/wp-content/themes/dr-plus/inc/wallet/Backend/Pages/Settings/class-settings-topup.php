<?php
namespace Sheyda\Wallet\Backend;

use Sheyda\Wallet\AdminScripts;
use Sheyda\Wallet\Backend\Settings;
use Sheyda\Wallet\Utils\AdminUI;
use Sheyda\Wallet\Utils\Settings as UtilsSettings;
use SheydaWalletUtils as WalletUtils;

class TopUpSettings extends Settings {
	public static function view() {
		$prefix = parent::$PREFIX;
		$settings = UtilsSettings::get_settings( 'topup' );
		

		?>
		<form method="post" action="" class="<?php echo $prefix ?>section-wrap">
			<?php parent::create_nonce(); ?>
			<table class="form-table">
				<tbody>
					<tr>
						<th>
							<label for="<?php echo $prefix ?>enable_topup"><?php esc_html_e( 'Enable Top-up', 'sheyda_wallet' ) ?></label>
						</th>
						<td>
							<?php
							AdminUI::switch( [
								'label'		=> esc_html__( 'Allow Users to Deposit Funds', 'sheyda_wallet' ),
								'name'		=> $prefix . "enable",
								'id'		=> $prefix . "enable_topup_request",
								'value'		=> 1,
								'active'	=> WalletUtils::to_bool( $settings['enable'] )
							] );
							?>
						</td>
					</tr>
					<tr>
						<th>
							<label for="<?php echo $prefix ?>predefined_amounts"><?php esc_html_e( 'Predefined amounts', 'sheyda_wallet' ) ?></label>
						</th>
						<td>
							<?php
							AdminUI::input_with_label( [
								'label'			=> sprintf( esc_html__( 'Predefined amounts (%s)', 'sheyda_wallet' ), WalletUtils::get_price_symbol() ),
								'name'			=> $prefix . "predefined_amounts",
								'id'			=> $prefix . "predefined_amounts",
								'description'	=> esc_html__( 'Enter each amount in one line', 'sheyda_wallet' ),
								'textarea'		=> true,
								'value'			=> !empty( $settings['predefined_amounts'] ) ? implode( PHP_EOL, $settings['predefined_amounts'] ) : '',
								'rows'			=> 4,
							] );
							?>
						</td>
					</tr>
					<tr>
						<th>
							<label for="<?php echo $prefix ?>enable_topup_checkout_template"><?php esc_html_e( 'Enable wallet template for checkout', 'sheyda_wallet' ) ?></label>
						</th>
						<td>
							<?php
							AdminUI::switch( [
								'label'		=> esc_html__( 'Replace checkout template for topup process', 'sheyda_wallet' ),
								'name'		=> $prefix . "enable_topup_checkout_template",
								'id'		=> $prefix . "enable_topup_checkout_template",
								'value'		=> 1,
								'active'	=> WalletUtils::to_bool( $settings['enable_topup_checkout_template'] )
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
			'enable'							=> WalletUtils::to_bool( $_POST[$prefix . "enable"] ?? false ),
			'predefined_amounts'				=> WalletUtils::remove_empty_indexes( explode( PHP_EOL, WalletUtils::convert_chars( $_POST[$prefix . "predefined_amounts"], 'sanitize_textarea_field' ) ) ),
			'enable_topup_checkout_template'	=> WalletUtils::to_bool( $_POST[$prefix . "enable_topup_checkout_template"] ?? false ),
		];

		UtilsSettings::save_settings( 'topup', $settings );
	}

	public static function enqueue() {
		AdminScripts::switch();
		AdminScripts::form_group();
	}
}