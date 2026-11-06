<?php

use DrPlus\Utils;
use DrPlus\Utils\Booking;
use DrPlus\Utils\Options;
use DrPlus\Utils\SubscriptionPlans;
use DrPlus\Utils\UtilsSpecialists;

if( !defined( 'ABSPATH' ) ) exit;

if( empty( $args['specialist'] ) || empty( $args['mode'] ) ) return;

$specialist = $args['specialist'];
$mode = Utils::ensure_values_in_array( sanitize_text_field( $args['mode'] ), ['offline_visits', 'online_visits'] );
if( !$mode ) return;
$options = Options::get_options( [
	'offline_reserve_time_text'	=> esc_html__( 'Book an appointment', 'drplus' ),
	'online_reserve_time_text'	=> esc_html__( 'Request Consultation', 'drplus' ),
	'view_specialist_btn_text'	=> esc_html__( 'View Specialist', 'drplus' ),
] );

$args = Utils::check_default( $args, [
	'name-tag'		=> 'h2',
	'short_bio-tag'	=> 'div',
	'verified-text'	=> '',
] );

// Check for subscription plan
if( !SubscriptionPlans::is_specialist_plan_active( $specialist->user_id ) ) {
	$mode = 'view_only';
} else {
	if( $mode == 'online_visits' && !Utils::to_bool( $specialist->online_visit ) || $mode == 'offline_visits' && !Utils::to_bool( $specialist->offline_visit ) ) $mode = 'view_only';
}

$page_link = UtilsSpecialists::get_page_link( $specialist );
?>

<?php if( false && $mode == 'offline_visits' && Utils::to_bool( $specialist->offline_visit ) ) { ?>
	<div class="specialist-book-info specialist-book-info-time drplus-popover-wrap drplus-popover-start">
		<i class="drplus-icon-time-circle"></i>
		<div class="specialist-book-info-text drplus-popover">40 دقیقه - زمان انتظار</div>
	</div>

	<div class="specialist-book-info specialist-book-info-availability drplus-popover-wrap drplus-popover-end">
		<i class="drplus-icon-calendar"></i>
		<div class="specialist-book-info-text drplus-popover">یکشنبه-28مرداد - 15:30 </div>
	</div>
<?php } ?>

<div class="specialist-avatar-wrap">
	<a href="<?php echo $page_link ?>" title="<?php echo esc_attr( $specialist->display_name ) ?>"><?php echo get_avatar( $specialist->user_id ) ?></a>
</div>
<div class="specialist-name-wrap">
	<<?php echo tag_escape( $args['name-tag'] ) ?> class="specialist-name line-clamp line-clamp-1">
		<a href="<?php echo $page_link ?>" title="<?php echo esc_attr( $specialist->display_name ) ?>"><?php echo esc_html( $specialist->display_name ) ?></a>
	</<?php echo tag_escape( $args['name-tag'] ) ?>>

	<?php if( Utils::to_bool( $specialist->subtitle ) ) { ?>
		<<?php echo tag_escape( $args['short_bio-tag'] ) ?> class="specialist-short_bio line-clamp line-clamp-1">
			<a href="<?php echo $page_link ?>" title="<?php echo esc_attr( $specialist->display_name ) ?>"><?php echo esc_html( $specialist->subtitle ) ?></a>
		</<?php echo tag_escape( $args['short_bio-tag'] ) ?>>
	<?php } ?>

	<?php if( Utils::to_bool( $specialist->is_verified ) && !empty( $args['verified-text'] ) ) { ?>
		<div class="specialist-is-verified">
			<i class="drplus-icon-verify-fill"></i>
			<span class="specialist-is-verified-text"><?php echo esc_html( $args['verified-text'] ) ?></span>
		</div>
	<?php } ?>
</div>
<div class="specialist-meta-wrap">
	<?php get_template_part( "templates/specialists/template-specialists-meta", $mode, [
		'specialist'	=> $specialist,
		'page_link'		=> $page_link,
	] ); ?>
</div>
<?php
if( Utils::is_wc_active() ) {
	if( Booking::is_booking_active() && $mode != 'view_only' ) {
		$button_link = Booking::get_booking_page_url( 'time' );
		$button_link = add_query_arg( [
			'sid' => $specialist->id,
			'drplus_save_recent'	=> $specialist->id,
		], $button_link );
		$button_text = $mode == 'offline_visits' ? $options['offline_reserve_time_text'] : $options['online_reserve_time_text'];
	} else {
		$button_text = $options['view_specialist_btn_text'];
		$button_link = $page_link;
	}
} else {
	$button_text = $options['view_specialist_btn_text'];
	$button_link = $page_link;
}
get_template_part( "templates/components/template-components-button", null, [
	'icon'			=> is_rtl() ? 'drplus-icon-arrow-up-left-square' : 'drplus-icon-arrow-up-right-square',
	'text'			=> $button_text,
	'link'			=> $button_link,
	'icon_align'	=> 'end',
	'align'			=> 'center',
	'classes'		=> ['specialist-btn'],
	'fullwidth'		=> true,
	'small'			=> true,
] );