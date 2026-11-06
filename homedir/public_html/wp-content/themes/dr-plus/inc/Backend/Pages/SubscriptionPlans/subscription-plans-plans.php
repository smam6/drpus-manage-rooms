<?php
namespace DrPlus\Backend\Pages\SubscriptionPlans;

use DrPlus\Utils;
use DrPlus\Utils\AdminUI;
use DrPlus\Utils\Formatters;
use DrPlus\Backend\Pages\SubscriptionPlansSettings;
use DrPlus\Utils\Sanitizers;
use DrPlus\Utils\SubscriptionPlans;

class Plans extends SubscriptionPlansSettings {
	private static $PREFIX = "drplus_plans_";
	public static function view() {
		$woocommerce_currency = get_woocommerce_currency();
		if( in_array( $woocommerce_currency, ['IRR', 'IRT', 'IRHR', 'IRHT'] ) ) {
			$price_symbol = esc_html__( 'Toman', 'drplus' );
		} else {
			$price_symbol = get_woocommerce_currency_symbol();
		}

		$plans = SubscriptionPlans::get_plans();
		?>
		<form method="post" action="" class="<?php echo self::$PREFIX ?>section-wrap">
			<?php parent::create_nonce() ?>
			<div class="<?php echo self::$PREFIX ?>plans_wrap">
				<?php foreach( array_values( $plans ) as $index => $plan ) {
					$plan = Utils::check_default( $plan, [
						'id'				=> '',
						'enable'			=> true,
						'title'				=> '',
						'icon'				=> '',
						'subtitle'			=> '',
						'reg_price'			=> '',
						'sale_price'		=> '',
						'duration'			=> '',
						'duration_label'	=> '',
						'features'			=> [],
					] );
					$plan['index'] = $index;
					$plan['price_symbol'] = $price_symbol;
					self::plan_item( $plan );
				} ?>
			</div>

			<button type="button" id="<?php echo self::$PREFIX ?>add_plan_btn"><?php esc_html_e( 'Add Plan', 'drplus' ) ?></button>
			<button type="submit" id="<?php echo self::$PREFIX ?>submit"><?php esc_html_e( 'Save settings', 'drplus' ) ?></button>
		</form>
		<script type="text/html" id="tmpl-drplus-plan-item">
			<?php
			self::plan_item( [
				'index'			=> '{{{data.index}}}',
				'price_symbol'	=> $price_symbol,
			] );
			?>
		</script>
		<?php
		AdminUI::modal( [
			'id'				=> 'icon-picker-modal',
			'title'				=> __( 'Select plan icon', 'drplus' ),
			'classes'			=> ['icon-picker-modal'],
			'submit_btn_text'	=> esc_html__( "Select icon", 'drplus' ),
		] );
	}

	public static function plan_item( $args ) {
		$args = Utils::check_default( $args, [
			'index'				=> '',
			'id'				=> '',
			'enable'			=> true,
			'title'				=> '',
			'icon'				=> '',
			'subtitle'			=> '',
			'price_symbol'		=> '',
			'reg_price'			=> '',
			'sale_price'		=> '',
			'duration'			=> '',
			'duration_label'	=> '',
			'features'			=> [],
		] )
		?>
		<div class="<?php echo self::$PREFIX ?>plan_item">
			<div class="<?php echo self::$PREFIX ?>plan_item_head">
				<span class="<?php echo self::$PREFIX ?>plan_item_name"><?php echo $args['title'] ?></span>
				<i class="<?php echo self::$PREFIX ?>plan_item_remove drplus-icon-trash"></i>
				<i class="<?php echo self::$PREFIX ?>plan_item_arrow drplus-icon-top"></i>
			</div>
			<div class="<?php echo self::$PREFIX ?>plan_item_inner">
				<input type="hidden" name="<?php echo self::$PREFIX ?>item[<?php echo $args['index'] ?>][id]" value="<?php echo $args['id'] ?>">
				<?php
				AdminUI::switch( [
					'name'		=> self::$PREFIX . "item[{$args['index']}][enable]",
					'id'		=> self::$PREFIX . "item_{$args['index']}_enable",
					'value'		=> 'true',
					'active'	=> $args['enable'],
					'label'		=> esc_html__( 'Enable plan', 'drplus' )
				] );
				AdminUI::input_with_label( [
					'label'			=> esc_html__( 'Plan title', 'drplus' ),
					'type'			=> 'text',
					'value'			=> $args['title'],
					'id'			=> self::$PREFIX . "item_{$args['index']}_title",
					'name'			=> self::$PREFIX . "item[{$args['index']}][title]",
					'input_classes'	=> ['regular-text', self::$PREFIX . 'plan_title'],
					'required'		=> true
				] );
				?>
				<div class="<?php echo self::$PREFIX ?>plan_item_icon_row">
					<label for="<?php echo self::$PREFIX ?>plan_icon"><?php esc_html_e( 'Plan Icon', 'drplus' ) ?></label>
					<?php AdminUI::icon_picker( [
						'id'			=> self::$PREFIX . "item_{$args['index']}_icon",
						'name'			=> self::$PREFIX . "item[{$args['index']}][icon]",
						'icon'			=> $args['icon'],
						'modal_id'		=> 'icon-picker-modal',
					] ); ?>
				</div>
				<?php
				AdminUI::input_with_label( [
					'label'			=> esc_html__( 'Plan subtitle', 'drplus' ),
					'type'			=> 'text',
					'value'			=> $args['subtitle'],
					'id'			=> self::$PREFIX . "item_{$args['index']}_subtitle",
					'name'			=> self::$PREFIX . "item[{$args['index']}][subtitle]",
					'input_classes'	=> ['regular-text'],
					'required'		=> false
				] );
				AdminUI::input_with_label( [
					'label'			=> sprintf( esc_html__( 'Plan regular price (%s)', 'drplus' ), $args['price_symbol'] ),
					'type'			=> 'text',
					'value'			=> $args['reg_price'] == "" ? $args['reg_price'] : Formatters::price( $args['reg_price'] ),
					'id'			=> self::$PREFIX . "item_{$args['index']}_reg_price",
					'name'			=> self::$PREFIX . "item[{$args['index']}][reg_price]",
					'input_classes'	=> ['regular-text', 'ltr', 'drplus-price-input', 'drplus-numeric-input'],
					'inputmode'		=> 'numeric',
					'required'		=> true
				] );
				AdminUI::input_with_label( [
					'label'			=> sprintf( esc_html__( 'Plan sale price (%s)', 'drplus' ), $args['price_symbol'] ),
					'type'			=> 'text',
					'value'			=> $args['sale_price'] == "" ? $args['sale_price'] : Formatters::price( $args['sale_price'] ),
					'id'			=> self::$PREFIX . "item_{$args['index']}_sale_price",
					'name'			=> self::$PREFIX . "item[{$args['index']}][sale_price]",
					'input_classes'	=> ['regular-text', 'ltr', 'drplus-price-input', 'drplus-numeric-input'],
					'inputmode'		=> 'numeric',
					'required'		=> false
				] );
				AdminUI::input_with_label( [
					'label'			=> esc_html__( 'Plan duration (days)', 'drplus' ),
					'type'			=> 'text',
					'value'			=> $args['duration'],
					'id'			=> self::$PREFIX . "item_{$args['index']}_duration",
					'name'			=> self::$PREFIX . "item[{$args['index']}][duration]",
					'input_classes'	=> ['regular-text', 'ltr', 'drplus-numeric-input'],
					'inputmode'		=> 'numeric',
					'required'		=> true
				] );
				AdminUI::input_with_label( [
					'label'			=> esc_html__( 'Plan duration text', 'drplus' ),
					'type'			=> 'text',
					'value'			=> $args['duration_label'],
					'id'			=> self::$PREFIX . "item_{$args['index']}_duration_label",
					'name'			=> self::$PREFIX . "item[{$args['index']}][duration_label]",
					'input_classes'	=> ['regular-text'],
					'required'		=> true
				] );
				?>
				<p class="description"><?php esc_html_e( 'For example: Six-month subscription plan', 'drplus' ) ?></p>
				<?php
				AdminUI::input_with_label( [
					'label'			=> esc_html__( 'Plan Features', 'drplus' ),
					'textarea'		=> true,
					'value'			=> implode( PHP_EOL, $args['features'] ),
					'id'			=> self::$PREFIX . "item_{$args['index']}_features",
					'name'			=> self::$PREFIX . "item[{$args['index']}][features]",
					'input_classes'	=> ['regular-text'],
					'rows'			=> 3
				] );
				?>
				<p class="description"><?php esc_html_e( 'Write each feature on one line', 'drplus' ) ?></p>
			</div>
		</div>
		<?php
	}

	public static function save() {
		$plans = [];
		foreach( $_POST[self::$PREFIX . 'item'] as $plan_fields ) {
			foreach( $plan_fields as $key => $field ) {
				if( $key == 'id' && empty( $field ) ) {
					$plan_fields['id'] = uniqid( 'plan_' );
				} else if( $key == 'reg_price' || $key == 'sale_price' ) {
					$plan_fields[$key] = $field == "" ? "" : Sanitizers::price( $field );
				} else if( $key == 'features' ) {
					$features = explode( PHP_EOL, $field );
					$plan_fields[$key] = [];
					foreach( $features as $feature ) {
						$plan_fields[$key][] = Utils::convert_chars( $feature );
					}
				} else {
					$plan_fields[$key] = Utils::convert_chars( $field );
				}

			}
			$plans[] = $plan_fields;
		}

		SubscriptionPlans::update_plans( $plans );
	}
}