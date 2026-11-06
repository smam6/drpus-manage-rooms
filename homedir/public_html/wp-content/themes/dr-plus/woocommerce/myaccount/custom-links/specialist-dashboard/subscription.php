<?php

use DrPlus\Components\Button;
use DrPlus\Components\Loading;
use DrPlus\Utils;
use DrPlus\Utils\Formatters;
use DrPlus\Utils\SubscriptionPlans;

$show_save_button = false;
$show_form = false;

// Get subscription plan
$plans = SubscriptionPlans::get_plans( true );
$settings = SubscriptionPlans::get_settings();
$special_plan = $settings['special_plan'];
$nonce = wp_create_nonce( 'drplus_subscription_plan' );

$woocommerce_currency = get_woocommerce_currency();
if( in_array( $woocommerce_currency, ['IRR', 'IRT', 'IRHR', 'IRHT'] ) ) {
	$price_symbol = esc_html__( 'Toman', 'drplus' );
} else {
	$price_symbol = get_woocommerce_currency_symbol();
}

?>
<div class="drplus-subscription-plans-wrap">
	<?php if( !empty( $plans ) ) { ?>
		<?php foreach( $plans as $plan ) { ?>
			<div class="drplus_subscription_plan_wrap<?php echo $settings['special_plan'] == $plan['id'] ? ' special_plan' : "" ?>">
				<?php if( $settings['special_plan'] == $plan['id'] ) { ?>
					<i class="drplus-icon-verify-fill drplus_subscription_plan_special_icon"></i>
				<?php } ?>
				<input type="hidden" class="drplus_subscription_plan_id" value="<?php echo $plan['id'] ?>">
				<input type="hidden" class="drplus_subscription_plan_nonce" value="<?php echo $nonce ?>">
				<i class="drplus_subscription_plan_icon <?php echo esc_html( $plan['icon'] ) ?>"></i>
				<span class="drplus_subscription_plan_title"><?php echo esc_html( $plan['title'] ) ?></span>
				<span class="drplus_subscription_plan_subtitle"><?php echo esc_html( $plan['subtitle'] ) ?></span>
				<?php if( !empty( $plan['features'] ) ) { ?>
					<ul class="drplus_subscription_plan_features">
						<?php foreach( $plan['features'] as $feature ) { ?>
							<li class="drplus_subscription_plan_feature">
								<i class="drplus-icon-tick drplus_subscription_plan_feature_icon"></i>
								<span class="drplus_subscription_plan_feature_title"><?php echo esc_html( $feature ) ?></span>
							</li>
						<?php } ?>
					</ul>
				<?php } ?>
				<div class="drplus_subscription_plan_price_wrap">
					<?php if( empty( $plan['reg_price'] ) ) { ?>
						<span class="drplus_subscription_plan_price"><?php esc_html_e( 'Free', 'drplus' ) ?></span>
					<?php } else { ?>
						<?php if( !empty( $plan['sale_price'] ) ) { ?>
							<span class="drplus_subscription_plan_reg_price">
								<del>
									<?php echo Formatters::price( $plan['reg_price'] ) ?>
									<span class="drplus_subscription_plan_price_symbol"><?php echo $price_symbol ?></span>
								</del>
							</span>
						<?php } ?>
						<div class="drplus_subscription_plan_price_inner">
							<span class="drplus_subscription_plan_price">
								<?php echo esc_html( $plan['sale_price'] == '' ? Formatters::price( $plan['reg_price'] ) : Formatters::price( $plan['sale_price'] ) ) ?>
								<span class="drplus_subscription_plan_price_symbol"><?php echo $price_symbol ?></span>
							</span>
							<span class="drplus_subscription_plan_label"><?php echo esc_html( $plan['duration_label'] ) ?></span>
						</div>
					<?php } ?>
				</div>
				<div class="drplus_subscription_plan_buy_btn-wrap">
					<?php Button::view( [
						'text'		=> esc_html__( 'Buy plan', 'drplus' ),
						'fullwidth'	=> true,
						'small'		=> true,
						'type'		=> $settings['special_plan'] == $plan['id'] ? 'white' : 'primary',
						'loading'	=> true,
						'classes'	=> ['drplus_subscription_plan_buy_btn'],
						'atts'		=> [
							'type'	=> 'button'
						]
					] ) ?>
				</div>
			</div>
		<?php } ?>
	<?php } else { ?>
		<div class="empty-page">
			<i class="drplus-icon-archive-book"></i>
			<p class="empty-page-text"><?php esc_html_e( 'No subscription plans found.', 'drplus' ) ?></p>
		</div>
	<?php } ?>
</div>