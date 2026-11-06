<?php
use DrPlus\Components\Button;
use DrPlus\Utils;

$section_classes = ['auth-section', 'auth-signup-section'];
if( $args['active_section'] == 'signup' ) {
	$section_classes[] = 'show';
}
if( !$args['auth_email'] && !$args['sms']['settings']['auth']['one_form'] ) {
	$section_classes[] = 'auth-signup-mobile';
}

?>
<section class="<?php echo Utils::prepare_html_classes( $section_classes ) ?>" data-section="signup" data-nonce="<?php echo wp_create_nonce( "drplus-auth-signup" ) ?>">
	<h4 class="auth-section-title"><?php esc_html_e( "Register", 'drplus' ) ?></h4>

	<?php if( $args['auth_email'] ) { ?>
		<div class="input-group auth-signup-username-group">
			<label for="auth-signup-username" class="input-label"><?php esc_html_e( 'Enter your username:', 'drplus' ) ?></label>
			<div class="input-wrap input-wrap-white">
				<input
					type="text"
					id="auth-signup-username"
					class="input-field input-ltr auth-input"
					autocomplete="username"
					placeholder="<?php esc_attr_e( 'Enter your username', 'drplus' ) ?>"
					<?php echo $args['active_section'] == 'signup' ? ' autofocus' : '' ?>
					autocapitalize="off"
					minlength="1"
					spellcheck="false"
					required
				>
			</div>
			<div class="input-error">
				<i class="drplus-icon-error"></i>
				<span class="input-error-text"></span>
			</div>
		</div>
	<?php } ?>

	<?php if( $args['auth_email'] ) { ?>
		<div class="input-group auth-signup-email-group">
			<label for="auth-signup-email" class="input-label"><?php esc_html_e( 'Enter your email:', 'drplus' ) ?></label>
			<div class="input-wrap input-wrap-white">
				<input
					type="email"
					id="auth-signup-email"
					class="input-field input-ltr auth-input"
					placeholder="<?php esc_attr_e( 'Enter your email', 'drplus' ) ?>"
					autocomplete="email"
					minlength="1"
					spellcheck="off"
					autocapitalize="off"
					<?php echo $args['auth_email_required'] ? 'required' : '' ?>
				>
			</div>
			<div class="input-error">
				<i class="drplus-icon-error"></i>
				<span class="input-error-text"></span>
			</div>
		</div>
	<?php } ?>

	<div class="input-group auth-signup-mobile-group">
		<label for="auth-signup-mobile" class="input-label"><?php esc_html_e( 'Enter your mobile number:', 'drplus' ) ?></label>
		<div class="input-wrap input-wrap-white">
			<input
				type="text"
				placeholder="09..."
				minlength="13"
				maxlength="13"
				id="auth-signup-mobile"
				class="input-field input-ltr auth-input drplus-phone-input"
				inputmode="tel"
				autocomplete="tel"
				autocapitalize="off"
				spellcheck="false"
			>
		</div>
		<div class="input-error">
			<i class="drplus-icon-error"></i>
			<span class="input-error-text"></span>
		</div>
	</div>

	<?php if( $args['auth_email'] ) { ?>
		<div class="input-group auth-signup-password-group">
			<label for="auth-signup-password" class="input-label"><?php esc_html_e( 'Enter your password:', 'drplus' ) ?></label>
			<div class="input-wrap input-wrap-white">
				<input
					type="password"
					id="auth-signup-password"
					class="input-field input-ltr auth-input"
					placeholder="<?php esc_attr_e( 'Enter your password', 'drplus' ) ?>"
					autocomplete="new-password"
					autocapitalize="off"
					minlength="1"
					spellcheck="false"
					required
				>
				<i class="password-icon show-password drplus-icon-eye"></i>
				<i class="password-icon hide-password drplus-icon-eye-slash"></i>
			</div>
			<div class="input-error">
				<i class="drplus-icon-error"></i>
				<span class="input-error-text"></span>
			</div>
		</div>
	<?php } ?>

	<div class="auth-section-error auth-signup-section-error"></div>

	<?php get_template_part( "templates/auth/template-auth-terms", null, [
		'auth_terms'		=> $args['auth_terms'],
		'auth_terms_text'	=> $args['auth_terms_text'],
		'auth_terms_url'	=> $args['auth_terms_url'],
	] ) ?>

	<div class="auth-section-actions">
		<?php
		Button::view( [
			'text'			=> __( "Register", 'drplus' ),
			'icon'			=> is_rtl() ? 'drplus-icon-arrow-up-left-square' : 'drplus-icon-arrow-up-right-square',
			'icon_align'	=> 'end',
			'fullwidth'		=> true,
			'small'			=> true,
			'disabled'		=> true,
			'loading'		=> true,
			'classes'		=> [$args['auth_email'] ? 'auth-signup-btn' : 'auth-send-otp', 'auth-section-submit-btn'],
			'id'			=> 'auth-signup-btn',
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