<?php

use DrPlus\Components\Button;
use DrPlus\Utils;
use DrPlus\Utils\Booking;
use DrPlus\Utils\SubscriptionPlans;
use DrPlus\Utils\UI;
use DrPlus\Utils\UtilsSpecialists;

extract($args);
?>
<section class="<?php echo $prefix ?>bio <?php echo $prefix ?>section" role="region" aria-label="<?php printf( __( 'Information of %s', 'drplus' ), $specialist->display_name ) ?>">
	<div class="<?php echo $prefix ?>avatar-wrap">
		<img src="<?php echo $avatar_url ?>" class="<?php echo $prefix ?>avatar" alt="<?php echo esc_attr( $specialist->display_name ) ?>" role="presentation" itemprop="image">
	</div>
	<<?php echo tag_escape( $options['single_specialist_title_tag'] ) ?> class="<?php echo $prefix ?>name" itemprop="name"><?php echo esc_html( $specialist->display_name ) ?></<?php echo tag_escape( $options['single_specialist_title_tag'] ) ?>>
	<?php if( $specialist->status != 'active' ) { ?>
		<p class="description" style='font-style:italic;font-size:14px;color:#a6a6a6;text-align:center'><?php printf( esc_html__( '(This page is only visible to the administrator because specialist status is "%s")', 'drplus' ), UtilsSpecialists::statuses( true )[$specialist->status] ) ?></p>
	<?php } ?>
	<?php
	if( Utils::to_bool( $options['single_specialist_show_patients_review_stat'] ) && comments_open() ) {
		get_template_part( 'templates/specialists/single/template-specialists-single-score', null, [
			'prefix'			=> $prefix,
			'comments_count'	=> $comments_count,
			'show_score_text'	=> true,
		] );
	}
	?>
	<?php if( $options['onboard-info-field-subtitle-enabled'] && !empty( $specialist->subtitle ) ) { ?>
		<<?php echo $options['single_specialist_subtitle_tag'] ?> class="<?php echo $prefix ?>subtitle"><?php echo esc_html( $specialist->subtitle ) ?></<?php echo $options['single_specialist_subtitle_tag'] ?>>
	<?php } ?>
	<?php
	if( Utils::to_bool( $options['single_specialist_show_specialist_code'] ) && $options['onboard-info-field-specialist-code-enabled'] ) {
		$specialist_code = UtilsSpecialists::get_specialist_code( $specialist->user_id );
		?>
		<div class="<?php echo $prefix ?>code-wrap">
			<?php if( $specialist->is_verified ) { ?>
				<i class="<?php echo $prefix ?>verified-icon drplus-icon-verify-fill"></i>
				<span class="screen-reader-text"><?php esc_html_e( 'Verified specialist', 'drplus' ) ?></span>
			<?php } ?>
			<span class="<?php echo $prefix ?>code"><?php printf( '%s: %s', $options['specialist-code-label'], !empty( $specialist_code ) ? $specialist_code : esc_html__( 'Unknown', 'drplus' ) ) ?></span>
		</div>									
	<?php } ?>
	<?php if( Utils::is_wc_active() && Booking::is_booking_active() && Utils::to_bool( $options['single_specialist_show_reserve_btn'] ) ) { ?>
		<div class="<?php echo $prefix ?>booking">
			<?php if( ( SubscriptionPlans::is_specialist_plan_active( $specialist->user_id ) && ( Utils::to_bool( $specialist->offline_visit ) || Utils::to_bool( $specialist->online_visit ) ) ) ) { ?>
				<?php if( Utils::to_bool( $specialist->offline_visit ) ) {
					Button::view( [
						'icon'			=> is_rtl() ? 'drplus-icon-arrow-up-left-square' : 'drplus-icon-arrow-up-right-square',
						'text'			=> $options['offline_reserve_time_text'],
						'title'			=> $options['offline_reserve_time_text'],
						'align'			=> 'center',
						'icon_align'	=> 'end',
						'fullwidth'		=> true,
						'id'			=> $prefix . "booking-btn",
						'link'			=> Booking::get_booking_page_url( 'time?sid=' . $specialist->id ),
						'atts'			=> [
							'aria_label'	=> esc_html__( 'Book an appointment with ' . $specialist->display_name, 'drplus' )
						]
					] );
				} ?>
				<?php if( Utils::to_bool( $specialist->online_visit ) ) {
					Button::view( [
						'icon'			=> is_rtl() ? 'drplus-icon-arrow-up-left-square' : 'drplus-icon-arrow-up-right-square',
						'text'			=> $options['online_reserve_time_text'],
						'title'			=> $options['online_reserve_time_text'],
						'align'			=> 'center',
						'icon_align'	=> 'end',
						'fullwidth'		=> true,
						'type'			=> 'bordered',
						'id'			=> $prefix . "consultation-btn",
						'link'			=> add_query_arg( ['sid' => $specialist->id, 'consultation' => 1], Booking::get_booking_page_url( 'time' ) ),
						'atts'			=> [
							'aria_label'	=> esc_html__( 'Book an appointment for online consultation with ' . $specialist->display_name, 'drplus' )
						]
					] );
				} ?>									
			<?php } else { ?>
				<p class="<?php echo $prefix ?>not-available-reserve"><?php echo str_replace( '{name}', $specialist->display_name, $options['single_specialist_not_available_reserve_text'] ) ?></p>
			<?php } ?>
		</div>							
	<?php } ?>
</section>