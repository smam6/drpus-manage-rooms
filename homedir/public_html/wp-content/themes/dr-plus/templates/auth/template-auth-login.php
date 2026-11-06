<?php
use DrPlus\Components\Button;
?>
<section class="auth-section auth-login-section<?php echo $args['active_section'] == 'login' ? ' show' : '' ?>" data-section="login" data-nonce="<?php echo wp_create_nonce( "drplus-auth-login" ) ?>">
	<h4 class="auth-section-title"><?php esc_html_e( "Login", 'drplus' ) ?></h4>

	<div class="input-group auth-login-username-group">
		<label for="auth-login-username" class="input-label"><?php esc_html_e( 'Enter your email or username:', 'drplus' ) ?></label>
		<div class="input-wrap input-wrap-white">
			<input
				type="text"
				id="auth-login-username"
				class="input-field input-ltr auth-input"
				tabindex="2"
				autocomplete="username"
				placeholder="<?php esc_attr_e( 'Enter your email or username', 'drplus' ) ?>"
				<?php echo $args['active_section'] == 'login' ? ' autofocus' : '' ?>
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

	<div class="input-group auth-login-password-group">
		<label for="auth-login-password" class="input-label"><?php esc_html_e( 'Enter your password:', 'drplus' ) ?></label>
		<div class="input-wrap input-wrap-white">
			<input
				type="password"
				id="auth-login-password"
				class="input-field input-ltr auth-input"
				tabindex="3"
				placeholder="<?php esc_attr_e( 'Enter your password', 'drplus' ) ?>"
				autocomplete="current-password"
				autocapitalize="off"
				minlength="1"
				spellcheck="false"
			>
			<i class="password-icon show-password drplus-icon-eye"></i>
			<i class="password-icon hide-password drplus-icon-eye-slash"></i>
		</div>
		<div class="input-error">
			<i class="drplus-icon-error"></i>
			<span class="input-error-text"></span>
		</div>
	</div>

	<label id="rememberme-label" class="checkbox-wrap">
		<input type="checkbox" id="auth-login-rememberme" value="forever" tabindex="4" checked>
		<?php esc_html_e( 'Remember me', 'drplus' ) ?>
	</label>

	<div class="auth-login-section-links">
		<div class="lost-password-link auth-link" tabindex="5"><?php esc_html_e( "Lost your password?", 'drplus' ) ?></div>
		<div class="signup-btn auth-link" tabindex="6"><?php _e( "Don't have an account? <span>Signup now</span>", 'drplus' ) ?></div>
	</div>

	<div class="auth-section-error auth-login-section-error"></div>

	<div class="auth-section-actions">
		<?php
		Button::view( [
			'text'			=> __( "Login", 'drplus' ),
			'icon'			=> is_rtl() ? 'drplus-icon-arrow-up-left-square' : 'drplus-icon-arrow-up-right-square',
			'icon_align'	=> 'end',
			'fullwidth'		=> true,
			'small'			=> true,
			'disabled'		=> true,
			'loading'		=> true,
			'classes'		=> ['auth-login-btn', 'auth-section-submit-btn'],
			'id'			=> 'auth-login-btn',
		] );
		if( $args['auth_sms'] ) {
			Button::view( [
				'type'			=> 'bordered',
				'text'			=> __( "Using mobile", 'drplus' ),
				'icon'			=> is_rtl() ? 'drplus-icon-left' : 'drplus-icon-right',
				'icon_align'	=> 'end',
				'fullwidth'		=> true,
				'small'			=> true,
				'classes'		=> ['auth-switch-mobile-btn'],
			] );
		}
		?>
	</div>
</section>