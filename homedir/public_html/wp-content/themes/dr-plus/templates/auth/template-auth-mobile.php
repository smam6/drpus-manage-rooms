<?php
use DrPlus\Components\Button;

$title = esc_html__( "Login / Signup", 'drplus' );
if( !$args['auth_email'] && !$args['sms']['settings']['auth']['one_form'] ) {
	$title = esc_html__( "Login", 'drplus' );
}
?>
<section class="auth-section auth-mobile-section<?php echo $args['active_section'] == 'mobile' ? ' show' : '' ?>" data-section="mobile" data-nonce="<?php echo wp_create_nonce( "drplus-auth-mobile" ) ?>">
	<h4 class="auth-section-title"><?php echo $title ?></h4>

	<div class="input-group auth-mobile-group">
		<label for="auth-mobile-input" class="input-label"><?php esc_html_e( 'Enter your mobile number:', 'drplus' ) ?></label>
		<div class="input-wrap input-wrap-white">
			<input
				type="text"
				placeholder="09..."
				minlength="13"
				maxlength="13"
				id="auth-mobile-input"
				class="input-field input-ltr auth-input drplus-phone-input"
				inputmode="tel"
				autocomplete="tel"
				<?php echo $args['active_section'] == 'mobile' ? ' autofocus' : '' ?>
				autocapitalize="off"
				spellcheck="false"
			>
		</div>
		<div class="input-error">
			<i class="drplus-icon-error"></i>
			<span class="input-error-text"></span>
		</div>
	</div>

	<div class="auth-section-actions">
		<?php
		Button::view( [
			'text'			=> __( "Next", 'drplus' ),
			'icon'			=> is_rtl() ? 'drplus-icon-arrow-up-left-square' : 'drplus-icon-arrow-up-right-square',
			'icon_align'	=> 'end',
			'fullwidth'		=> true,
			'small'			=> true,
			'disabled'		=> true,
			'loading'		=> true,
			'classes'		=> ['auth-send-otp', 'auth-section-submit-btn'],
			'id'			=> 'auth-mobile-submit',
		] );
		if( $args['auth_email'] ) {
			Button::view( [
				'type'			=> 'bordered',
				'text'			=> __( "Using email", 'drplus' ),
				'icon'			=> is_rtl() ? 'drplus-icon-left' : 'drplus-icon-right',
				'icon_align'	=> 'end',
				'fullwidth'		=> true,
				'small'			=> true,
				'classes'		=> ['auth-switch-login-btn'],
			] );
		}
		if( !$args['auth_email'] && !$args['sms']['settings']['auth']['one_form'] ) {
			Button::view( [
				'type'			=> 'bordered',
				'text'			=> __( "Register", 'drplus' ),
				'icon'			=> is_rtl() ? 'drplus-icon-left' : 'drplus-icon-right',
				'icon_align'	=> 'end',
				'fullwidth'		=> true,
				'small'			=> true,
				'classes'		=> ['auth-switch-signup-btn', 'signup-btn'],
			] );
		}
		?>
	</div>
</section>