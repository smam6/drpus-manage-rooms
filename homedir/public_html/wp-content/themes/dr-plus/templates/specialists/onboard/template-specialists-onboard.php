<?php

use DrPlus\Components\Button;
use DrPlus\PublicScripts;
use DrPlus\Utils;
use DrPlus\Utils\Onboard;
use DrPlus\Utils\Options;

$separator = is_rtl() ? ' &rsaquo; ' : ' &lsaquo; ';

$styles = ['drplus-pdp', 'drplus-select2', 'drplus-dropzone', 'drplus-font-awesome', 'drplus', 'drplus-icons', 'drplus-onboard', 'drplus-onboard-rtl'];
$active_fonts = Utils::get_active_fonts();
// Load active fonts
foreach( $active_fonts as $font ) {
	$styles[] = "drplus-font-{$font}";
}
$styles[] = 'drplus-custom';
$styles = apply_filters( 'drplus/onboard/styles', $styles );

$scripts = ['wp-util', 'drplus-pd', 'drplus-pdp', 'drplus-select2', 'drplus-swapy', 'drplus-dropzone', 'drplus-utils', 'drplus', 'drplus-front', 'drplus-onboard'];
$scripts = apply_filters( 'drplus/onboard/scripts', $scripts );

include( DRPLUS_DIR . "inc/Scripts.php" );
PublicScripts::pdp();
PublicScripts::select2();
PublicScripts::swapy();
PublicScripts::dropzone();

$body_classes = ['header_disabled', 'footer_disabled', 'onboard'];

$options = Options::get_options( [
	'onboard_page_title'		=> get_bloginfo( 'name', 'display' ) . $separator . __( "Onboarding", "drplus" ),
	'onboard_title'				=> __( "Onboarding", "drplus" ),
	'onboard_show_bg_pattern'	=> true,
	'onboard_show_logo'			=> true,
	'onboard_logo_link'			=> home_url(),
	'onboard_logo_type'			=> 'img',
	'onboard_logo_img'			=> DRPLUS_URI . "assets/images/logo.svg",
	'onboard_logo_img_size'		=> [
		'width'		=> 92,
		'height'	=> 52,
	],
	'onboard_show_back'			=> true,
	'onboard_back_label'		=> __( "Return to home page", 'drplus' ),
	'onboard_back_url'			=> home_url(),
] );

if( !Utils::to_bool( $options['onboard_show_bg_pattern'] ) ) {
	$body_classes[] = 'onboard-remove-pattern';
}
$specialist = $args['specialist'];

$steps = Onboard::steps();
$step = '';
if( $specialist->status == 'rejected' ) {
	$steps['rejected'] = [
		'title'			=> __( "Your request has been rejected.", 'drplus' ),
		'description'	=> __( "The reason for the rejection of your request is written below:", 'drplus' ),
	];
	$step = 'rejected';
}
if( $specialist->status == 'pending' ) {
	$step = 'done';
}

// Check the current step
if( $step != 'rejected' && $step != 'done' ) {
	if( empty( $_GET['step'] ) || !in_array( Utils::convert_chars( $_GET['step'] ), array_keys( $steps ) ) ) {
		$step = Onboard::get_user_step();
	} else {
		$step = Utils::convert_chars( $_GET['step'] );
	}
	$prev_step = Onboard::get_prev_step( $step );
}

$body_classes[] = "onboard-{$step}";

if( $step == 'personal' ) {
	wp_enqueue_media();
	$styles = array_merge( $styles, [
		'media-views',
		'imgareaselect',
	] );
	$scripts = array_merge( $scripts, [
		'media-editor',
		'media-audiovideo',
		'mce-view',
		'image-edit',
	] );
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
		<meta name='referrer' content='strict-origin-when-cross-origin'/>
		<title><?php echo $options['onboard_page_title'] ?></title>
		<?php
		/**
		 * Enqueue scripts and styles for the login page.
		 *
		 * @since 3.1.0
		 */
		do_action( 'drplus/onboard/enqueue_scripts' );
		wp_print_styles( $styles );
		do_action( 'drplus/onboard/head' );
		?>
	</head>

	<body <?php body_class( $body_classes ); ?>>
		<?php do_action( 'drplus/onboard/body/start' ) ?>
		<?php if( Utils::to_bool( $options['onboard_show_bg_pattern'] ) ) { ?>
			<div class="onboard-pattern"></div>
			<div class="onboard-pattern-overlay"></div>
		<?php } ?>

		<div class="onboard-content">
			<?php if( Utils::to_bool( $options['onboard_show_logo'] ) ) { ?>
				<?php if( !empty( $options['onboard_logo_link'] ) ) { ?>
					<a href="<?php echo esc_url( $options['onboard_logo_link'] ) ?>" class="onboard-logo" title="<?php echo esc_attr( get_bloginfo( 'name' ) ) ?>">
				<?php } else { ?>
					<div class="onboard-logo">
				<?php } ?>
						<?php echo Options::get_logo( [
							'type'			=> 'onboard_logo_type',
							'text-type'		=> 'onboard_logo_text_type',
							'text-custom'	=> 'onboard_logo_text_custom',
							'img'			=> 'onboard_logo_img',
							'img-size'		=> 'onboard_logo_img_size',
						], $options );
						?>
				<?php if( !empty( $options['onboard_logo_link'] ) ) { ?>
					</a>
				<?php } else { ?>
					</div>
				<?php } ?>
			<?php } ?>

			<?php do_action( 'drplus/onboard/before_form' ) ?>
			<form method="post" action="" class="onboard-form">
				<?php wp_nonce_field( 'drplus_specialist-save', 'nonce' ) ?>
				<input type="hidden" name="step" value="<?php echo esc_attr( $step ) ?>">
				<input type="hidden" name="is_onboard" value="true">
				<?php do_action( 'drplus/onboard/form/start' ) ?>
				<h2 class="onboard-form-title"><?php echo esc_html( $options['onboard_title'] ) ?></h2>

				<section class="onboard-form-head">
					<h2 class="onboard-section-title"><?php echo esc_html( $steps[$step]['title'] ) ?></h2>
					<p class="onboard-section-description"><?php echo esc_html( $steps[$step]['description'] ) ?></p>
				</section>

				<section class="onboard-form-fields">
					<?php
					get_template_part( "templates/specialists/onboard/template-specialists-onboard-{$step}", null, [
						'specialist'	=> $specialist,
					] );
					?>
				</section>

				<section class="onboard-form-actions">
					<?php
					if( $specialist->status != 'rejected' ) {
						if( $step != 'done' ) {
							if( $prev_step ) {
								Button::view( [
									'text'			=> __( "Back", 'drplus' ),
									'type'			=> 'bordered',
									'icon'			=> 'drplus-icon-left',
									'icon_align'	=> is_rtl() ? 'end' : 'start',
									'fullwidth'		=> true,
									'small'			=> true,
									'classes'		=> ['onboard-back-btn'],
									'link'			=> add_query_arg( 'step', $prev_step ),
								] );
							}
							Button::view( [
								'text'			=> __( "Next", 'drplus' ),
								'icon'			=> 'drplus-icon-right',
								'icon_align'	=> is_rtl() ? 'start' : 'end',
								'fullwidth'		=> true,
								'small'			=> true,
								'classes'		=> ['onboard-next-btn'],
								'id'			=> 'onboard-submit',
							] );
						} else {
							Button::view( [
								'text'			=> __( "Back to my account", 'drplus' ),
								'icon'			=> is_rtl() ? 'drplus-icon-arrow-up-left-square' : 'drplus-icon-arrow-up-arrow-up-right-square',
								'icon_align'	=> 'end',
								'fullwidth'		=> true,
								'small'			=> true,
								'link'			=> get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ),
							] );
						}
					} else {
						Button::view( [
							'text'			=> __( "Edit information", 'drplus' ),
							'icon'			=> 'drplus-icon-edit',
							'icon_align'	=> is_rtl() ? 'start' : 'end',
							'fullwidth'		=> true,
							'small'			=> true,
							'classes'		=> ['onboard-next-btn'],
							'id'			=> 'onboard-submit',
						] );
					}
					?>
				</section>
				
				<?php do_action( 'drplus/onboard/form/end' ) ?>
			</form>
			<?php do_action( 'drplus/onboard/after_form' ) ?>

			<div class="onboard-footer">
				<?php do_action( 'drplus/onboard/footer/start' ) ?>

				<?php if( Utils::to_bool( $options['onboard_show_back'] ) ) { ?>
					<a href="<?php echo esc_url( $options['onboard_back_url'], ['http', 'https'] ) ?>" class="outline onboard-back"><?php echo esc_html( $options['onboard_back_label'] ) ?></a>
				<?php } ?>

				<?php do_action( 'drplus/onboard/footer/end' ) ?>
			</div>
		</div>

		<?php
		wp_print_scripts( $scripts );
		if( $step == 'personal' ) {
			wp_print_media_templates();
		}
		do_action( 'drplus/onboard/body/end' );
		?>
	</body>
</html>