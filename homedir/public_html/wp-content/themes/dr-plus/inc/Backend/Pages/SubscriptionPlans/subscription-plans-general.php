<?php
namespace DrPlus\Backend\Pages\SubscriptionPlans;

use DrPlus\Backend\Pages\SubscriptionPlansSettings;
use DrPlus\Utils;
use DrPlus\Utils\AdminUI;
use DrPlus\Utils\SubscriptionPlans;

class PlansGeneral extends SubscriptionPlansSettings {
	private static $PREFIX = "drplus_plans_";
	public static function view() {
		$settings = SubscriptionPlans::get_settings();
		$plans = SubscriptionPlans::get_plans( true );
		if( !empty( $plans ) ) {
			$plans = wp_list_pluck( $plans, 'title', 'id' );
		}
		$plans = array_merge( ["" => esc_html__( 'None', 'drplus' )], $plans );

		?>
		<form method="post" action="" class="<?php echo self::$PREFIX ?>section-wrap">
			<?php parent::create_nonce() ?>
			<table class="form-table <?php echo self::$PREFIX ?>general_wrap">
				<tr>
					<th>
						<label for="<?php echo self::$PREFIX ?>enable_plans"><?php esc_html_e( 'Enable subscription plans', 'drplus' ) ?></label>
					</th>
					<td>
						<?php AdminUI::switch( [
							'name'		=> self::$PREFIX . "enable_plans",
							'id'		=> self::$PREFIX . "enable_plans",
							'value'		=> 'true',
							'active'	=> $settings['enable'],
						] ); ?>
					</td>
				</tr>
				<tr>
					<th>
						<label for="<?php echo self::$PREFIX ?>special_plan"><?php esc_html_e( 'Special plan', 'drplus' ) ?></label>
					</th>
					<td>
						<?php echo AdminUI::select_with_label( [
							'name'				=> self::$PREFIX . 'special_plan',
							'id'				=> self::$PREFIX . 'special_plan',
							'options'			=> $plans,
							'value'				=> $settings['special_plan'],
							'select_classes'	=> ['drplus-select2'],
						] ) ?>
					</td>
				</tr>
				<tr>
					<th>
						<label for="<?php echo self::$PREFIX ?>expire_warning_days"><?php esc_html_e( 'Expire warning days', 'drplus' ) ?></label>
					</th>
					<td>
						<?php echo AdminUI::input_with_label( [
							'name'				=> self::$PREFIX . 'expire_warning_days',
							'id'				=> self::$PREFIX . 'expire_warning_days',
							'value'				=> $settings['expire_warning_days'],
							'type'				=> 'number',
							'input_classes'		=> ['regular-text']
						] ) ?>
					</td>
				</tr>
			</table>

			<button type="submit" id="<?php echo self::$PREFIX ?>submit"><?php esc_html_e( 'Save settings', 'drplus' ) ?></button>
		</form>
		<?php
	}

	public static function save() {
		$settings = [
			'enable'				=> Utils::convert_chars( $_POST[self::$PREFIX . 'enable_plans'] ?? false ),
			'special_plan'			=> Utils::convert_chars( $_POST[self::$PREFIX . 'special_plan'] ),
			'expire_warning_days'	=> Utils::convert_chars( $_POST[self::$PREFIX . 'expire_warning_days'], 'absint' ),
		];
		SubscriptionPlans::update_settings( $settings );
	}
}