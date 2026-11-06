<?php
use DrPlus\Components\Button;
?>
<section class="auth-section auth-lost_password-section<?php echo $args['active_section'] == 'lost_password' ? ' show' : '' ?>" data-section="lost_password" data-nonce="<?php echo wp_create_nonce( "drplus-auth-lost_password" ) ?>">
	<h4 class="auth-section-title"><?php esc_html_e( "Forgot password", 'drplus' ) ?></h4>

	<div class="input-group auth-lost_password-group">
		<label for="auth-lost_password-input" class="input-label"><?php esc_html_e( 'Enter your email or username or phone number:', 'drplus' ) ?></label>
		<div class="input-wrap input-wrap-white">
			<input
				type="text"
				id="auth-lost_password-input"
				class="input-field input-ltr auth-input"
				tabindex="2"
				placeholder="<?php esc_attr_e( 'Enter your email or username or phone number', 'drplus' ) ?>"
				<?php echo $args['active_section'] == 'lost_password' ? ' autofocus' : '' ?>
				autocomplete="username"
				autocapitalize="off"
				minlength="1"
				spellcheck="false"
			>
		</div>
		<div class="input-error">
			<i class="drplus-icon-error"></i>
			<span class="input-error-text"></span>
		</div>
	</div>

	<div class="auth-section-notice auth-lost_password-section-notice"></div>

	<div class="auth-section-actions">
		<?php
		Button::view( [
			'text'			=> __( "Send new password", 'drplus' ),
			'icon'			=> is_rtl() ? 'drplus-icon-arrow-up-left-square' : 'drplus-icon-arrow-up-right-square',
			'icon_align'	=> 'end',
			'fullwidth'		=> true,
			'small'			=> true,
			'disabled'		=> true,
			'loading'		=> true,
			'classes'		=> ['auth-lost_password-btn', 'auth-section-submit-btn'],
			'id'			=> 'auth-lost_password-btn',
		] );
		Button::view( [
			'type'			=> 'bordered',
			'text'			=> __( "Back to login", 'drplus' ),
			'icon'			=> is_rtl() ? 'drplus-icon-left' : 'drplus-icon-right',
			'icon_align'	=> 'end',
			'fullwidth'		=> true,
			'small'			=> true,
			'classes'		=> ['auth-switch-login'],
		] );
		?>
	</div>
</section>