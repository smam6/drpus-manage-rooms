<?php

use DrPlus\Components\Button;
use DrPlus\Model\Booking as ModelBooking;
use DrPlus\Utils;
use DrPlus\Utils\Booking;
use DrPlus\Utils\Date;
use DrPlus\Utils\Formatters;
use DrPlus\Utils\Sanitizers;
use DrPlus\Utils\SubscriptionPlans;
use DrPlus\Utils\WC;

if( !defined( 'ABSPATH' ) ) exit;

// Subscription Plan
$show_quick_access_items = true;
$subs_plans = SubscriptionPlans::get_settings();
if( $subs_plans['enable'] ) {
	// Get specialist plan expire date
	$user_subs_plan = SubscriptionPlans::get_specialist_plan( $specialist->user_id );

	$plan_section_classes = ['drplus-specialist-subscription-section'];

	if( $user_subs_plan['plan_expired'] ) {
		$plan_section_classes[] = 'subscription-expired';
	}
	if( $user_subs_plan['plan_expire_warning'] ) {
		$plan_section_classes[] = 'subscription-expiring-warning';
	}

	$show_quick_access_items = apply_filters( 'drplus/specialist/dashboard/show_quick_access', !$user_subs_plan['plan_expired'], $user_subs_plan, $specialist->user_id );
}

$today_day_index = date('w'); // 0 (Sunday) to 6 (Saturday)
$today_date = date('Y-m-d');
if( $today_day_index == 6 ) {
    $last_saturday = $today_date;
} else {
    $last_saturday = date('Y-m-d', strtotime('last Saturday'));
}
if( $today_day_index == 5 ) {
    $next_friday = $today_date;
} else {
    $next_friday = date('Y-m-d', strtotime('next Friday'));
}

$where = [
	['`date`', '>=', $last_saturday],
	['`date`', '<=', $next_friday]
];
$week_app_count = Booking::get_specialist_appointments_count( $specialist->id, "", "", $where );
$today_app_count = Booking::get_specialist_appointments_count( $specialist->id, "", $today_date );

$month_income = ModelBooking::query()->select( 'specialist_income' )->where( 'specialist_id', $specialist->id )->whereIn( 'order_status', ['completed', 'processing'] )->whereBetween( '`date`', [Date::first_day_of_jalali_month(), Date::last_day_of_jalali_month()] )->get()->pluck( 'specialist_income' );
?>
<h2 class="drplus-myaccount-page-title"><?php esc_html_e( 'Dashboard', 'drplus' ) ?></h2>

<?php if( $subs_plans['enable'] ) { ?>
	<section <?php echo Utils::prepare_html_classes( $plan_section_classes, true ) ?>>
		<div class="drplus-specialist-subscription-content">
			<div class="drplus-specialist-subscription-inner">
			<?php if( $user_subs_plan['plan_expired'] ) { ?>
				<?php if( !empty( $user_subs_plan['title'] ) ) { ?>
					<div class="drplus-specialist-subscription-name-wrap">
						<span class="drplus-specialist-subscription-name"><?php echo $user_subs_plan['title'] ?></span>
						<span class="drplus-specialist-subscription-status expired-plan"><?php esc_html_e( 'Expired', 'drplus' ) ?></span>
					</div>
					<span class="drplus-specialist-subscription-notice expired-warning"><?php esc_html_e( 'Your subscription has expired. To access your panel, please purchase a subscription plan.', 'drplus' ) ?></span>
				<?php } else { ?>
					<span class="drplus-specialist-subscription-notice expired-warning"><?php esc_html_e( 'You do not have any active subscriptions. To access your panel, please purchase a subscription plan.', 'drplus' ) ?></span>
				<?php } ?>
			<?php } else {
				?>
				<div class="drplus-specialist-subscription-name-wrap">
					<span class="drplus-specialist-subscription-name"><?php echo $user_subs_plan['title'] ?></span>
					<span class="drplus-specialist-subscription-status active-plan"><?php esc_html_e( 'Active', 'drplus' ) ?></span>
				</div>
				<?php if( $user_subs_plan['plan_expire_warning'] ) { ?>
					<span class="drplus-specialist-subscription-notice expired-warning"><?php printf( __( 'Your subscription plan is about to expire in <strong>%s</strong> days. Please renew it to continue accessing your panel.', 'drplus' ), $user_subs_plan['remaining_days'] ) ?></span>
				<?php } else { ?>
					<span class="drplus-specialist-subscription-notice"><?php printf( esc_html__( 'Remaining Days: %s', 'drplus' ), $user_subs_plan['remaining_days'] ) ?></span>
				<?php }
			} ?>
			</div>
			<?php Button::view( [
				'text'	=> esc_html__( 'View Subscription Plans', 'drplus' ),
				'type'	=> $user_subs_plan['plan_expired'] || $user_subs_plan['plan_expire_warning'] ? 'secondary' : 'white',
				'align'	=> 'end',
				'small'	=> true,
				'link'	=> wc_get_account_endpoint_url( 'specialist-dashboard/subscription' ),
			] ); ?>
		</div>
	</section>
<?php } ?>

<section class="drplus-specialist-statistics">
	<div class="drplus-specialist-statistic">
		<div class="drplus-specialist-statistic-texts">
			<div class="drplus-specialist-statistic-title"><?php esc_html_e( 'Patients of this week', 'drplus' ) ?></div>
			<div class="drplus-specialist-statistic-value"><?php echo number_format_i18n( $week_app_count ) ?></div>
		</div>
		<i class="drplus-specialist-statistic-icon drplus-icon-doctor-profile"></i>
	</div>

	<div class="drplus-specialist-statistic">
		<div class="drplus-specialist-statistic-texts">
			<div class="drplus-specialist-statistic-title"><?php esc_html_e( "This month's income", 'drplus' ) ?></div>
			<div class="drplus-specialist-statistic-value"><?php printf( esc_html__( '%s Toman', 'drplus' ), Formatters::price( array_sum( $month_income ) ) ) ?></div>
		</div>
		<i class="drplus-specialist-statistic-icon drplus-icon-income"></i>
	</div>

	<div class="drplus-specialist-statistic">
		<div class="drplus-specialist-statistic-texts">
			<div class="drplus-specialist-statistic-title"><?php esc_html_e( "Today's appointments", 'drplus' ) ?></div>
			<div class="drplus-specialist-statistic-value"><?php echo number_format_i18n( $today_app_count ) ?></div>
		</div>
		<i class="drplus-specialist-statistic-icon drplus-icon-counter"></i>
	</div>
</section>

<?php if( $show_quick_access_items ) { ?>
	<section class="drplus-specialist-quick-access-section">
		<h3 class="drplus-myaccount-page-title"><?php esc_html_e( 'Quick access', 'drplus' ) ?></h3>
		<div class="drplus-specialist-quick-access-items">
			<?php foreach( WC::specialist_profile_sections() as $qa_key => $qa ) { ?>
				<a href="<?php echo esc_url( wc_get_endpoint_url( "specialist-dashboard/{$qa_key}" ) ) ?>" class="drplus-specialist-quick-access">
					<?php echo Sanitizers::icon( 'drplus-icon-' . $qa['icon'], 'drplus-specialist-quick-access-icon' ) ?>
					<div class="drplus-specialist-quick-access-label"><?php echo esc_html( $qa['label'] ) ?></div>
				</a>
			<?php } ?>
		</div>
	</section>
<?php } ?>