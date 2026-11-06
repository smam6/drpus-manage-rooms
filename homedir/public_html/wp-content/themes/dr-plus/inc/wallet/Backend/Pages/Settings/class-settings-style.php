<?php
namespace Sheyda\Wallet\Backend;

use SheydaWalletUtils as WalletUtils;
use Sheyda\Wallet\AdminScripts;
use Sheyda\Wallet\Backend\Settings;
use Sheyda\Wallet\PublicScripts;
use Sheyda\Wallet\Utils\Settings as UtilsSettings;

class StyleSettings extends Settings {
	public static function view() {
		$prefix = parent::$PREFIX;

		$settings = UtilsSettings::get_settings( 'style' );

		?>
		<form method="post" action="" class="<?php echo $prefix ?>section-wrap">
			<?php parent::create_nonce(); ?>
			<table class="form-table">
				<tbody>
					<tr>
						<th>
							<label for="<?php echo $prefix ?>bg-color"><?php esc_html_e( 'Background color', 'sheyda_wallet' ) ?></label>
						</th>
						<td>
							<input type="text" name="<?php echo $prefix ?>bg-color" id="<?php echo $prefix ?>bg-color" class="<?php echo $prefix ?>color-picker" value="<?php echo $settings['color-bg-color'] ?>" data-coloris>
						</td>
					</tr>

					<tr>
						<th>
							<label for="<?php echo $prefix ?>primary"><?php esc_html_e( 'Primary color', 'sheyda_wallet' ) ?></label>
						</th>
						<td>
							<input type="text" name="<?php echo $prefix ?>primary" id="<?php echo $prefix ?>primary" class="<?php echo $prefix ?>color-picker" value="<?php echo $settings['color-primary'] ?>" data-coloris>
						</td>
					</tr>

					<tr>
						<th>
							<label for="<?php echo $prefix ?>secondary"><?php esc_html_e( 'Secondary color', 'sheyda_wallet' ) ?></label>
						</th>
						<td>
							<input type="text" name="<?php echo $prefix ?>secondary" id="<?php echo $prefix ?>secondary" class="<?php echo $prefix ?>color-picker" value="<?php echo $settings['color-secondary'] ?>" data-coloris>
						</td>
					</tr>
					
					<tr>
						<th>
							<label for="<?php echo $prefix ?>red"><?php esc_html_e( 'Red color', 'sheyda_wallet' ) ?></label>
						</th>
						<td>
							<input type="text" name="<?php echo $prefix ?>red" id="<?php echo $prefix ?>red" class="<?php echo $prefix ?>color-picker" value="<?php echo $settings['color-red'] ?>" data-coloris>
						</td>
					</tr>

					<tr>
						<th>
							<label for="<?php echo $prefix ?>btn-text"><?php esc_html_e( 'Button text color', 'sheyda_wallet' ) ?></label>
						</th>
						<td>
							<input type="text" name="<?php echo $prefix ?>btn-text" id="<?php echo $prefix ?>btn-text" class="<?php echo $prefix ?>color-picker" value="<?php echo $settings['color-btn-text'] ?>" data-coloris>
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
			'color-bg-color'	=> !empty( $_POST[$prefix . 'bg-color'] ) ? sanitize_hex_color( $_POST[$prefix . 'bg-color'] ) : '',
			'color-primary'		=> !empty( $_POST[$prefix . 'primary'] ) ? sanitize_hex_color( $_POST[$prefix . 'primary'] ) : '',
			'color-secondary'	=> !empty( $_POST[$prefix . 'secondary'] ) ? sanitize_hex_color( $_POST[$prefix . 'secondary'] ) : '',
			'color-red'			=> !empty( $_POST[$prefix . 'red'] ) ? sanitize_hex_color( $_POST[$prefix . 'red'] ) : '',
			'color-btn-text'	=> !empty( $_POST[$prefix . 'btn-text'] ) ? sanitize_hex_color( $_POST[$prefix . 'btn-text'] ) : '',
		];

		UtilsSettings::save_settings( 'style', $settings );
	}

	public static function enqueue() {
		AdminScripts::form_group();
		PublicScripts::coloris();
		WalletUtils::enqueue_script( 'sheyda-wallet-style', SHEYDA_WALLET_URI . 'assets/js/backend/style', ['jquery'], SHEYDA_WALLET_VERSION );
	}
}