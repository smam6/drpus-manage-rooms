<?php

use DrPlus\Utils;
use DrPlus\Utils\Booking;
use DrPlus\Utils\Options;
use DrPlus\Utils\SubscriptionPlans;
use DrPlus\Utils\UtilsSpecialists;
use MJ\Whitebox\Utils as WhiteboxUtils;

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
	'name-tag'			=> 'h2',
	'short_bio-tag'		=> 'div',
	'verified-text'		=> '',
	'show_score'		=> true,
	'reserve_btn_icon'	=> ''
], ['reserve_btn_icon'] );

// Check for subscription plan
if( !SubscriptionPlans::is_specialist_plan_active( $specialist->user_id ) ) {
	$mode = 'view_only';
} else {
	if( $mode == 'online_visits' && !Utils::to_bool( $specialist->online_visit ) || $mode == 'offline_visits' && !Utils::to_bool( $specialist->offline_visit ) ) $mode = 'view_only';
}

if( $args['show_score'] ) {
	$comments = get_comments( [
		'fields'		=> 'ids',
		'post_id'		=> $specialist->post_id,
		'meta_key'		=> '_drplus_patient_review',
		'meta_value'	=> true,
		'status'		=> 'approve',
	] );
	$comments_count = count( $comments );
	if( $comments_count ) {
		$full_avg_score = Utils::get_post_avg( $specialist->post_id, false, $comments_count );
	} else {
		$full_avg_score = null;
	}
}

$page_link = UtilsSpecialists::get_page_link( $specialist );
?>

<div class="specialist-avatar-wrap">
	<a href="<?php echo $page_link ?>" title="<?php echo esc_attr( $specialist->display_name ) ?>">
		<?php if( $args['show_score'] && !is_null( $full_avg_score ) ) { ?>
			<div class="specialist-score-wrap">
				<span class="specialist-score"><?php echo $full_avg_score ?></span>
				<i class="drplus-icon-star-fill"></i>
			</div>
		<?php } ?>
		<?php echo get_avatar( $specialist->user_id, 120 ) ?>
	</a>
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
	'icon'			=> $args['reserve_btn_icon'],
	'text'			=> $button_text,
	'link'			=> $button_link,
	'type'			=> 'action',
	'icon_align'	=> 'start',
	'align'			=> 'start',
	'classes'		=> ['specialist-btn'],
	'fullwidth'		=> false,
	'small'			=> true,
] );
?>