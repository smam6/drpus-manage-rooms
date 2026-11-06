<?php
use DrPlus\Components\Button;
use DrPlus\Utils;

?>
<section class="auth-section auth-otp-section" data-section="otp" data-nonce="<?php echo wp_create_nonce( "drplus-auth-otp" ) ?>">
	<h4 class="auth-section-title"><?php esc_html_e( "Enter the verification code", 'drplus' ) ?></h4>

	<div class="input-group otp-fields-group">
		<label class="input-label"><?php _e( 'Enter the verification code sent to number <span class="auth-otp-number"></span>', 'drplus' ) ?></label>
		<div class="otp-fields">
			<?php for( $index = 0; $index <= 3; $index++ ) { ?>
				<div class="input-wrap input-wrap-white">
					<input
					type="number"
					min="0"
					max="9"
					minlength="1"
					maxlength="1"
					id="auth-otp-input-<?php echo $index ?>"
					class="input-field input-ltr auth-otp-input"
					tabindex="<?php echo $index + 1 ?>"
					autocomplete="one-time-code"
				>
				</div>
			<?php } ?>
		</div>
		<div class="input-error">
			<i class="drplus-icon-error"></i>
			<span class="input-error-text"></span>
		</div>
	</div>

	<div class="otp-timer"><?php echo Utils::second_to_string( $args['sms']['settings']['auth']['login']['otp_timer'] ) ?></div>
	<div class="otp-timer-resend auth-send-otp auth-link outline" tabindex="6"><?php esc_html_e( 'Resend code', 'drplus' ) ?></div>

	<?php get_template_part( "templates/auth/template-auth-terms", null, [
		'auth_terms'		=> $args['auth_terms'],
		'auth_terms_text'	=> $args['auth_terms_text'],
		'auth_terms_url'	=> $args['auth_terms_url'],
	] ) ?>

	<div class="auth-section-actions">
		<?php
		Button::view( [
			'text'			=> __( "Verify", 'drplus' ),
			'icon'			=> is_rtl() ? 'drplus-icon-arrow-up-left-square' : 'drplus-icon-arrow-up-right-square',
			'icon_align'	=> 'end',
			'fullwidth'		=> true,
			'small'			=> true,
			'disabled'		=> true,
			'loading'		=> true,
			'classes'		=> ['auth-verify-otp-btn', 'auth-section-submit-btn'],
			'id'			=> 'auth-verify-otp-btn',
		] );
		Button::view( [
			'type'			=> 'bordered',
			'text'			=> __( "Change mobile", 'drplus' ),
			'icon'			=> is_rtl() ? 'drplus-icon-left' : 'drplus-icon-right',
			'icon_align'	=> 'end',
			'fullwidth'		=> true,
			'small'			=> true,
			'classes'		=> ['auth-change-mobile'],
		] );
		?>
	</div>
</section>