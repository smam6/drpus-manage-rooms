<?php

use DrPlus\SMS\Templates\Backend\Variable;
use DrPlus\Utils as DrPlusUtils;
use DrPlus\Utils\AdminUI;
use DrPlus\SMS\Utils;

$auth_available_variables = Utils::auth_variables();
$lost_password_variables = Utils::auth_variables( [
	'password'	=> esc_html__( 'Generated password', 'drplus' )
], ['otp', 'end_time'] );
Utils::reposition_array_element( $lost_password_variables, 'password', 0 );
?>

<div id="<?php echo self::$PREFIX ?>settings-login-container" class="<?php echo self::$PREFIX ?>settings-section">
	<div class="<?php echo self::$PREFIX ?>settings-section-head">
		<h3 class="<?php echo self::$PREFIX ?>section-title" id="<?php echo self::$PREFIX ?>settings-login-title"><?php esc_html_e( 'Login settings', 'drplus' ) ?></h3>
		<i class="drplus-icon-bottom"></i>
	</div>
	<div class="<?php echo self::$PREFIX ?>settings-section-body">
		<table class="form-table">
			<tr class="<?php echo self::$PREFIX ?>settings-status-row">
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-auth-login-status"><?php esc_html_e( 'Status', 'drplus' ) ?></label>
				</th>
	
				<td>
					<?php
					$login_status = DrPlusUtils::to_bool( $settings['settings']['auth']['login']['enabled'] ?? true );
					AdminUI::switch( [
						'name'			=> self::$PREFIX . 'settings[auth][login][enabled]',
						'id'			=> self::$PREFIX . 'settings-auth-login-status',
						'value'			=> '1',
						'active'		=> $login_status,
						'label'			=> esc_html__( "Active login with mobile", 'drplus' ),
						'input_classes'	=> [self::$PREFIX . "status-switch"],
					] );
					?>
				</td>
			</tr>
	
			<tr class="<?php echo self::$PREFIX ?>settings-gateway-pattern-row">
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-auth-login-pattern"><?php esc_html_e( 'Pattern code', 'drplus' ) ?></label>
				</th>
	
				<td>
					<input
						type="text"
						name="<?php echo self::$PREFIX ?>settings[auth][login][pattern]"
						id="<?php echo self::$PREFIX ?>settings-auth-login-pattern"
						class="regular-text ltr <?php echo self::$PREFIX ?>settings-pattern"
						value="<?php echo esc_attr( $settings['settings']['auth']['login']['pattern'] ?? '' ) ?>"
					>
				</td>
			</tr>
	
			<tr>
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-auth-login"><?php esc_html_e( 'Login OTP variables', 'drplus' ) ?></label>
				</th>
	
				<td>
					<div class="<?php echo self::$PREFIX ?>variables">
						<script type="text/html" class="<?php echo self::$PREFIX ?>variable-template" id="tmpl-<?php echo self::$PREFIX ?>login-template">
							<?php
							Variable::view( [
								'fields_name'	=> 'settings[auth][login][message]',
								'options'		=> $auth_available_variables
							] );
							?>
						</script>
						<?php
						if( !empty( $settings['messages']['auth']['login'] ) ) {
							$variable_mode = !empty( $settings['gateway'] ) && !empty( $gateways[$settings['gateway']] ) && !empty( $gateways[$settings['gateway']]['variable_mode'] );
							foreach( Utils::convert_string_to_variables( $settings['messages']['auth']['login'] ) as $gateway_variable => $variable ) {
								Variable::view( [
									'fields_name'		=> 'settings[auth][login][message]',
									'variable_mode'		=> $variable_mode,
									'gateway_variable'	=> $gateway_variable,
									'variable'			=> $variable,
									'options'			=> $auth_available_variables
								] );
							}
						}
						?>

						<button type="button" class="<?php echo self::$PREFIX ?>button <?php echo self::$PREFIX ?>variable-add"><?php esc_html_e( 'Add variable', 'drplus' ) ?></button>
					</div>
				</td>
			</tr>
	
			<tr>
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-auth-login-otp_timer"><?php esc_html_e( 'OTP time', 'drplus' ) ?></label>
				</th>
	
				<td>
					<input
						type="number"
						min="30"
						name="<?php echo self::$PREFIX ?>settings[auth][login][otp_timer]"
						id="<?php echo self::$PREFIX ?>settings-auth-login-otp_timer"
						class="small-text ltr <?php echo self::$PREFIX ?>settings-otp_timer"
						value="<?php echo esc_attr( $settings['settings']['auth']['login']['otp_timer'] ?? 60 ) ?>"
					>
					<p class="description"><?php esc_html_e( 'Time in seconds that the OTP is valid.', 'drplus' ) ?></p>
				</td>
			</tr>
		</table>
	</div>
</div>	

<hr>
<div id="<?php echo self::$PREFIX ?>settings-register-container" class="<?php echo self::$PREFIX ?>settings-section">
	<div class="<?php echo self::$PREFIX ?>settings-section-head">
		<h3 class="<?php echo self::$PREFIX ?>section-title" id="<?php echo self::$PREFIX ?>settings-register-title"><?php esc_html_e( 'Register settings', 'drplus' ) ?></h3>
		<i class="drplus-icon-bottom"></i>
	</div>
	<div class="<?php echo self::$PREFIX ?>settings-section-body">
		<table class="form-table">
			<tr class="<?php echo self::$PREFIX ?>settings-status-row">
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-auth-register-status"><?php esc_html_e( 'Status', 'drplus' ) ?></label>
				</th>
	
				<td>
					<?php
					$register_status = DrPlusUtils::to_bool( $settings['settings']['auth']['register']['enabled'] ?? true );
					AdminUI::switch( [
						'name'			=> self::$PREFIX . 'settings[auth][register][enabled]',
						'id'			=> self::$PREFIX . 'settings-auth-register-status',
						'value'			=> '1',
						'active'		=> $register_status,
						'label'			=> esc_html__( "Active register with mobile", 'drplus' ),
						'input_classes'	=> [self::$PREFIX . "status-switch"],
					] );
					?>
				</td>
			</tr>
	
			<tr class="<?php echo self::$PREFIX ?>settings-gateway-pattern-row">
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-auth-register-pattern"><?php esc_html_e( 'Pattern code', 'drplus' ) ?></label>
				</th>
	
				<td>
					<input
						type="text"
						name="<?php echo self::$PREFIX ?>settings[auth][register][pattern]"
						id="<?php echo self::$PREFIX ?>settings-auth-register-pattern"
						class="regular-text ltr <?php echo self::$PREFIX ?>settings-pattern"
						value="<?php echo esc_attr( $settings['settings']['auth']['register']['pattern'] ?? '' ) ?>"
					>
				</td>
			</tr>
	
			<tr>
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-auth-register"><?php esc_html_e( 'Register OTP variables', 'drplus' ) ?></label>
				</th>
	
				<td>
					<div class="<?php echo self::$PREFIX ?>variables">
						<script type="text/html" class="<?php echo self::$PREFIX ?>variable-template" id="tmpl-<?php echo self::$PREFIX ?>register-template">
							<?php
							Variable::view( [
								'fields_name'	=> 'settings[auth][register][message]',
								'options'		=> $auth_available_variables
							] );
							?>
						</script>
						<?php
						if( !empty( $settings['messages']['auth']['register'] ) ) {
							$variable_mode = !empty( $settings['gateway'] ) && !empty( $gateways[$settings['gateway']] ) && !empty( $gateways[$settings['gateway']]['variable_mode'] );
							foreach( Utils::convert_string_to_variables( $settings['messages']['auth']['register'] ) as $gateway_variable => $variable ) {
								Variable::view( [
									'fields_name'		=> 'settings[auth][register][message]',
									'variable_mode'		=> $variable_mode,
									'gateway_variable'	=> $gateway_variable,
									'variable'			=> $variable,
									'options'			=> $auth_available_variables
								] );
							}
						}
						?>

						<button type="button" class="<?php echo self::$PREFIX ?>button <?php echo self::$PREFIX ?>variable-add"><?php esc_html_e( 'Add variable', 'drplus' ) ?></button>
					</div>
				</td>
			</tr>
	
			<tr>
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-auth-register-otp_timer"><?php esc_html_e( 'OTP time', 'drplus' ) ?></label>
				</th>
	
				<td>
					<input
						type="number"
						min="30"
						name="<?php echo self::$PREFIX ?>settings[auth][register][otp_timer]"
						id="<?php echo self::$PREFIX ?>settings-auth-register-otp_timer"
						class="small-text ltr <?php echo self::$PREFIX ?>settings-otp_timer"
						value="<?php echo esc_attr( $settings['settings']['auth']['register']['otp_timer'] ?? 60 ) ?>"
					>
					<p class="description"><?php esc_html_e( 'Time in seconds that the OTP is valid.', 'drplus' ) ?></p>
				</td>
			</tr>
	
			<tr>
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-auth-one_form"><?php esc_html_e( 'One form mode', 'drplus' ) ?></label>
				</th>
	
				<td id="<?php echo self::$PREFIX ?>settings-auth-one_form-wrap">
					<?php
					AdminUI::switch( [
						'name'		=> self::$PREFIX . 'settings[auth][one_form]',
						'id'		=> self::$PREFIX . 'settings-auth-one_form',
						'value'		=> '1',
						'active'	=> DrPlusUtils::to_bool( $settings['settings']['auth']['one_form'] ?? true ),
						'label'		=> esc_html__( "One form for login and register.", 'drplus' ),
						'disabled'	=> !$login_status || !$register_status
					] );
					?>
					<p class="description"><?php esc_html_e( 'If you check one form mode, Login and register will used by a single form but you can change the settings for login or register.', 'drplus' ) ?></p>
				</td>
			</tr>
		</table>
	</div>
</div>

<hr>
<div id="<?php echo self::$PREFIX ?>settings-lost_password-container" class="<?php echo self::$PREFIX ?>settings-section">
	<div class="<?php echo self::$PREFIX ?>settings-section-head">
		<h3 class="<?php echo self::$PREFIX ?>section-title" id="<?php echo self::$PREFIX ?>settings-lost_password-title"><?php esc_html_e( 'Lost password settings', 'drplus' ) ?></h3>
		<i class="drplus-icon-bottom"></i>
	</div>
	<div class="<?php echo self::$PREFIX ?>settings-section-body">
		<table class="form-table">
			<tr class="<?php echo self::$PREFIX ?>settings-status-row">
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-auth-lost_password-status"><?php esc_html_e( 'Status', 'drplus' ) ?></label>
				</th>
	
				<td>
					<?php
					AdminUI::switch( [
						'name'			=> self::$PREFIX . 'settings[auth][lost_password][enabled]',
						'id'			=> self::$PREFIX . 'settings-auth-lost_password-status',
						'value'			=> '1',
						'active'		=> DrPlusUtils::to_bool( $settings['settings']['auth']['lost_password']['enabled'] ?? true ),
						'label'			=> esc_html__( "Active lost password with mobile", 'drplus' ),
						'input_classes'	=> [self::$PREFIX . "status-switch"],
					] );
					?>
				</td>
			</tr>
	
			<tr class="<?php echo self::$PREFIX ?>settings-gateway-pattern-row">
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-auth-lost_password-pattern"><?php esc_html_e( 'Pattern code', 'drplus' ) ?></label>
				</th>
	
				<td>
					<input
						type="text"
						name="<?php echo self::$PREFIX ?>settings[auth][lost_password][pattern]"
						id="<?php echo self::$PREFIX ?>settings-auth-lost_password-pattern"
						class="regular-text ltr <?php echo self::$PREFIX ?>settings-pattern"
						value="<?php echo esc_attr( $settings['settings']['auth']['lost_password']['pattern'] ?? '' ) ?>"
					>
				</td>
			</tr>
	
			<tr>
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-auth-lost_password"><?php esc_html_e( 'New password variables', 'drplus' ) ?></label>
				</th>
	
				<td>
					<div class="<?php echo self::$PREFIX ?>variables">
						<script type="text/html" class="<?php echo self::$PREFIX ?>variable-template" id="tmpl-<?php echo self::$PREFIX ?>lost_password-template">
							<?php
							Variable::view( [
								'fields_name'	=> 'settings[auth][lost_password][message]',
								'options'		=> $lost_password_variables
							] );
							?>
						</script>
						<?php
						if( !empty( $settings['messages']['auth']['lost_password'] ) ) {
							$variable_mode = !empty( $settings['gateway'] ) && !empty( $gateways[$settings['gateway']] ) && !empty( $gateways[$settings['gateway']]['variable_mode'] );
							foreach( Utils::convert_string_to_variables( $settings['messages']['auth']['lost_password'] ) as $gateway_variable => $variable ) {
								Variable::view( [
									'fields_name'		=> 'settings[auth][lost_password][message]',
									'variable_mode'		=> $variable_mode,
									'gateway_variable'	=> $gateway_variable,
									'variable'			=> $variable,
									'options'			=> $lost_password_variables
								] );
							}
						}
						?>

						<button type="button" class="<?php echo self::$PREFIX ?>button <?php echo self::$PREFIX ?>variable-add"><?php esc_html_e( 'Add variable', 'drplus' ) ?></button>
					</div>
					<p class="description"><?php esc_html_e( 'A new password will automatically be generated and set for the user.', 'drplus' ) ?></p>
				</td>
			</tr>
		</table>
	</div>
</div>