<?php

use DrPlus\Utils;
use DrPlus\Utils\Options;
use DrPlus\Utils\SMS;

$separator = is_rtl() ? ' &rsaquo; ' : ' &lsaquo; ';

$styles = ['drplus-font-awesome', 'drplus', 'drplus-icons', 'drplus-auth'];
$active_fonts = Utils::get_active_fonts();
// Load active fonts
foreach( $active_fonts as $font ) {
	$styles[] = "drplus-font-{$font}";
}
$styles[] = 'drplus-custom';
$styles = apply_filters( 'drplus/auth/styles', $styles );

include( DRPLUS_DIR . "inc/Scripts.php" );

$body_classes = ['header_disabled', 'footer_disabled', 'login', 'auth'];

$redirect_url = '';
if( !empty( $_GET['redirect_to'] ) ) {
	$redirect_url = sanitize_url( urldecode( $_GET['redirect_to'] ) );
}

$options = Options::get_options( [
	'auth_title'			=> get_bloginfo( 'name', 'display' ) . $separator . __( "Log In", "drplus" ),
	'auth_email_required'	=> true,
	'auth_show_bg_pattern'	=> true,
	'auth_show_logo'		=> true,
	'auth_logo_link'		=> home_url(),
	'auth_logo_type'		=> 'img',
	'auth_logo_img'			=> DRPLUS_URI . "assets/images/logo.svg",
	'auth_logo_img_size'	=> [
		'width'		=> 92,
		'height'	=> 52,
	],
	'auth_redirect'			=> $redirect_url,
	'auth_redirect_force'	=> false,
	'auth_sms'				=> true,
	'auth_email'			=> true,
	'auth_show_back'		=> true,
	'auth_back_label'		=> __( "Return to home page", 'drplus' ),
	'auth_back_url'			=> home_url(),
	'auth_terms'			=> true,
	'auth_terms_text'		=> __( "Membership in the site constitutes agreement to the rules.", 'drplus' ),
	'auth_terms_url'		=> home_url( 'terms-conditions' ),

	'auth-background-pattern-type'	=> 'theme'
] );

if( !Utils::to_bool( $options['auth_show_bg_pattern'] ) ) {
	$body_classes[] = 'auth-remove-pattern';
}

$auth_sms = Utils::to_bool( $options['auth_sms'] );
$auth_email = Utils::to_bool( $options['auth_email'] );
$sms_settings = [];
if( $auth_sms ) {
	$sms_settings = SMS::get_settings();
}

// Get redirect URL
if( empty( $redirect_url ) ) {
	$base_url = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	if( Utils::to_bool( $options['auth_redirect_force'] ) && !empty( $options['auth_redirect'] ) ) {
		$redirect_url = $options['auth_redirect'];
	}
}
if( empty( $redirect_url ) ) {
	if( !empty( $_SERVER['HTTP_REFERER'] ) ) {
		$redirect_url = $_SERVER['HTTP_REFERER'];
	} else {
		$redirect_url = remove_query_arg( "login", $base_url );
	}
}
$redirect_url = apply_filters( "drplus/auth/redirect_url", $redirect_url );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
		<meta name='referrer' content='strict-origin-when-cross-origin'/>
		<title><?php echo $options['auth_title'] ?></title>
		<?php
		/**
		 * Enqueue scripts and styles for the login page.
		 *
		 * @since 3.1.0
		 */
		do_action( 'login_enqueue_scripts' );
		wp_print_styles( $styles );
		do_action( 'login_head' );
		?>
	</head>

	<body <?php body_class( $body_classes ); ?>>
		<?php do_action( 'drplus/auth/body' ) ?>
		<?php if( Utils::to_bool( $options['auth_show_bg_pattern'] ) ) { ?>
			<div class="auth-pattern pattern-type-<?php echo $options['auth-background-pattern-type'] ?>"></div>
			<div class="auth-pattern-overlay"></div>
		<?php } ?>
		<div class="auth-content">
			<?php if( Utils::to_bool( $options['auth_show_logo'] ) ) { ?>
				<?php if( !empty( $options['auth_logo_link'] ) ) { ?>
					<a href="<?php echo esc_url( $options['auth_logo_link'] ) ?>" class="auth-logo" title="<?php echo esc_attr( get_bloginfo( 'name' ) ) ?>">
				<?php } else { ?>
					<div class="auth-logo">
				<?php } ?>
						<?php echo Options::get_logo( [
							'type'			=> 'auth_logo_type',
							'text-type'		=> 'auth_logo_text_type',
							'text-custom'	=> 'auth_logo_text_custom',
							'img'			=> 'auth_logo_img',
							'img-size'		=> 'auth_logo_img_size',
						], $options );
						?>
				<?php if( !empty( $options['auth_logo_link'] ) ) { ?>
					</a>
				<?php } else { ?>
					</div>
				<?php } ?>
			<?php } ?>

			<div class="auth-form">
				<input type="hidden" name="redirect" id="auth-redirect" value="<?php echo esc_url( $redirect_url, ['http', 'https'] ) ?>">
				<?php do_action( 'drplus/auth/form/start' ) ?>

				<?php
				$default_args = [
					'auth_email_required'	=> $options['auth_email_required'],
					'auth_sms'				=> $auth_sms,
					'auth_email'			=> $auth_email,
					'sms'					=> $sms_settings,
					'auth_terms'			=> $options['auth_terms'],
					'auth_terms_text'		=> $options['auth_terms_text'],
					'auth_terms_url'		=> $options['auth_terms_url'],
					'active_section'		=> isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : '',
				];
				if( !$default_args['active_section'] ) {
					if( $auth_sms ) {
						$default_args['active_section'] = 'mobile';
					} else if( $auth_email ) {
						$default_args['active_section'] = 'login';
					}
				}

				if( $default_args['active_section'] != 'mobile' ) {
					if( !$auth_email ) {
						$default_args['active_section'] = 'mobile';
					}
				}

				if( $auth_sms ) {
					get_template_part( "templates/auth/template-auth-mobile", null, $default_args );
					if( !$auth_email && !$sms_settings['settings']['auth']['one_form'] ) {
						get_template_part( "templates/auth/template-auth-signup", null, $default_args );
					}
					get_template_part( "templates/auth/template-auth-otp", null, $default_args );
				}

				if( $auth_email ) {
					get_template_part( "templates/auth/template-auth-login", null, $default_args );
					get_template_part( "templates/auth/template-auth-signup", null, $default_args );
					get_template_part( "templates/auth/template-auth-lost_password", null, $default_args );
				}
				?>

				<?php do_action( 'drplus/auth/form/end' ) ?>
			</div>

			<div class="auth-footer">
				<?php do_action( 'drplus/auth/footer/start' ) ?>

				<?php if( Utils::to_bool( $options['auth_show_back'] ) ) { ?>
					<a href="<?php echo esc_url( $options['auth_back_url'], ['http', 'https'] ) ?>" class="outline auth-back"><?php echo esc_html( $options['auth_back_label'] ) ?></a>
				<?php } ?>

				<?php do_action( 'drplus/auth/footer/end' ) ?>
			</div>
		</div>

		<?php do_action( 'login_footer' ) ?>
	</body>
</html>