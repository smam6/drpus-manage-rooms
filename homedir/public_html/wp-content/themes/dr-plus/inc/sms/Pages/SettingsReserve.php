<?php

use DrPlus\SMS\Templates\Backend\Variable;
use DrPlus\Utils;
use DrPlus\Utils\AdminUI;
use DrPlus\Utils\SMS;

$reserve_variables = SMS::reserve_variables();
?>

<div id="<?php echo self::$PREFIX ?>settings-notif-specialist-after-book-container" class="<?php echo self::$PREFIX ?>settings-section">
	<div class="<?php echo self::$PREFIX ?>settings-section-head">
		<h3 class="<?php echo self::$PREFIX ?>section-title"><?php esc_html_e( 'SMS notification to specialist after appointment booked', 'drplus' ) ?></h3>
		<i class="drplus-icon-bottom"></i>
	</div>
	<div class="<?php echo self::$PREFIX ?>settings-section-body">
		<table class="form-table">
			<tr class="<?php echo self::$PREFIX ?>settings-status-row">
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-notif-specialist-after-book-status"><?php esc_html_e( 'Status', 'drplus' ) ?></label>
				</th>

				<td>
					<?php
					$login_status = Utils::to_bool( $settings['settings']['reserve_notification']['specialist']['book']['enabled'] ?? false );
					AdminUI::switch( [
						'name'			=> self::$PREFIX . 'settings[reserve_notification][specialist][book][enabled]',
						'id'			=> self::$PREFIX . 'settings-notif-specialist-after-book-status',
						'value'			=> '1',
						'active'		=> $login_status,
						'label'			=> esc_html__( "Active Send SMS", 'drplus' ),
						'input_classes'	=> [self::$PREFIX . "status-switch"],
					] );
					?>
				</td>
			</tr>

			<tr class="<?php echo self::$PREFIX ?>settings-gateway-pattern-row">
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-notif-specialist-after-book-pattern"><?php esc_html_e( 'Pattern code', 'drplus' ) ?></label>
				</th>
	
				<td>
					<input
						type="text"
						name="<?php echo self::$PREFIX ?>settings[reserve_notification][specialist][book][pattern]"
						id="<?php echo self::$PREFIX ?>settings-notif-specialist-after-book-pattern"
						class="regular-text ltr <?php echo self::$PREFIX ?>settings-pattern"
						value="<?php echo esc_attr( $settings['settings']['reserve_notification']['specialist']['book']['pattern'] ?? '' ) ?>"
					>
				</td>
			</tr>

			<tr>
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-notif-specialist-after-book-message"><?php esc_html_e( 'Message variables', 'drplus' ) ?></label>
				</th>

				<td>
					<div class="<?php echo self::$PREFIX ?>variables">
						<script type="text/html" class="<?php echo self::$PREFIX ?>variable-template" id="tmpl-<?php echo self::$PREFIX ?>reserve_notification-specialist-book-template">
							<?php
							Variable::view( [
								'fields_name'	=> 'settings[reserve_notification][specialist][book][message]',
								'options'		=> $reserve_variables
							] );
							?>
						</script>
						<?php
						if( !empty( $settings['messages']['reserve_notification']['specialist']['book'] ) ) {
							$variable_mode = !empty( $settings['gateway'] ) && !empty( $gateways[$settings['gateway']] ) && !empty( $gateways[$settings['gateway']]['variable_mode'] );
							foreach( SMS::convert_string_to_variables( $settings['messages']['reserve_notification']['specialist']['book'] ) as $gateway_variable => $variable ) {
								Variable::view( [
									'fields_name'		=> 'settings[reserve_notification][specialist][book][message]',
									'variable_mode'		=> $variable_mode,
									'gateway_variable'	=> $gateway_variable,
									'variable'			=> $variable,
									'options'			=> $reserve_variables
								] );
							}
						}
						?>

						<button type="button" class="<?php echo self::$PREFIX ?>button <?php echo self::$PREFIX ?>variable-add"><?php esc_html_e( 'Add variable', 'drplus' ) ?></button>
					</div>
				</td>
			</tr>
		</table>
	</div>
</div>

<hr>
<div id="<?php echo self::$PREFIX ?>settings-notif-patient-after-book-container" class="<?php echo self::$PREFIX ?>settings-section">
	<div class="<?php echo self::$PREFIX ?>settings-section-head">
		<h3 class="<?php echo self::$PREFIX ?>section-title"><?php esc_html_e( 'SMS notification to patient after appointment booked', 'drplus' ) ?></h3>
		<i class="drplus-icon-bottom"></i>
	</div>
	<div class="<?php echo self::$PREFIX ?>settings-section-body">
		<table class="form-table">
			<tr class="<?php echo self::$PREFIX ?>settings-status-row">
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-notif-patient-after-book-status"><?php esc_html_e( 'Status', 'drplus' ) ?></label>
				</th>
	
				<td>
					<?php
					$login_status = Utils::to_bool( $settings['settings']['reserve_notification']['patient']['book']['enabled'] ?? false );
					AdminUI::switch( [
						'name'			=> self::$PREFIX . 'settings[reserve_notification][patient][book][enabled]',
						'id'			=> self::$PREFIX . 'settings-notif-patient-after-book-status',
						'value'			=> '1',
						'active'		=> $login_status,
						'label'			=> esc_html__( "Active Send SMS", 'drplus' ),
						'input_classes'	=> [self::$PREFIX . "status-switch"],
					] );
					?>
				</td>
			</tr>

			<tr class="<?php echo self::$PREFIX ?>settings-gateway-pattern-row">
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-notif-patient-after-book-pattern"><?php esc_html_e( 'Pattern code', 'drplus' ) ?></label>
				</th>
	
				<td>
					<input
						type="text"
						name="<?php echo self::$PREFIX ?>settings[reserve_notification][patient][book][pattern]"
						id="<?php echo self::$PREFIX ?>settings-notif-patient-after-book-pattern"
						class="regular-text ltr <?php echo self::$PREFIX ?>settings-pattern"
						value="<?php echo esc_attr( $settings['settings']['reserve_notification']['patient']['book']['pattern'] ?? '' ) ?>"
					>
				</td>
			</tr>
	
			<tr>
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-notif-patient-after-book-message"><?php esc_html_e( 'Message variables', 'drplus' ) ?></label>
				</th>
	
				<td>
					<div class="<?php echo self::$PREFIX ?>variables">
						<script type="text/html" class="<?php echo self::$PREFIX ?>variable-template" id="tmpl-<?php echo self::$PREFIX ?>reserve_notification-patient-book-template">
							<?php
							Variable::view( [
								'fields_name'	=> 'settings[reserve_notification][patient][book][message]',
								'options'		=> $reserve_variables
							] );
							?>
						</script>
						<?php
						if( !empty( $settings['messages']['reserve_notification']['patient']['book'] ) ) {
							$variable_mode = !empty( $settings['gateway'] ) && !empty( $gateways[$settings['gateway']] ) && !empty( $gateways[$settings['gateway']]['variable_mode'] );
							foreach( SMS::convert_string_to_variables( $settings['messages']['reserve_notification']['patient']['book'] ) as $gateway_variable => $variable ) {
								Variable::view( [
									'fields_name'		=> 'settings[reserve_notification][patient][book][message]',
									'variable_mode'		=> $variable_mode,
									'gateway_variable'	=> $gateway_variable,
									'variable'			=> $variable,
									'options'			=> $reserve_variables
								] );
							}
						}
						?>

						<button type="button" class="<?php echo self::$PREFIX ?>button <?php echo self::$PREFIX ?>variable-add"><?php esc_html_e( 'Add variable', 'drplus' ) ?></button>
					</div>
				</td>
			</tr>
		</table>
	</div>
</div>

<hr>
<div id="<?php echo self::$PREFIX ?>settings-notif-specialist-after-book-canceled-container" class="<?php echo self::$PREFIX ?>settings-section">
	<div class="<?php echo self::$PREFIX ?>settings-section-head">
		<h3 class="<?php echo self::$PREFIX ?>section-title"><?php esc_html_e( 'SMS notification to specialist after appointment canceled', 'drplus' ) ?></h3>
		<i class="drplus-icon-bottom"></i>
	</div>
	<div class="<?php echo self::$PREFIX ?>settings-section-body">
		<table class="form-table">
			<tr class="<?php echo self::$PREFIX ?>settings-status-row">
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-notif-specialist-after-book-status"><?php esc_html_e( 'Status', 'drplus' ) ?></label>
				</th>

				<td>
					<?php
					$login_status = Utils::to_bool( $settings['settings']['reserve_notification']['specialist']['book_canceled']['enabled'] ?? false );
					AdminUI::switch( [
						'name'			=> self::$PREFIX . 'settings[reserve_notification][specialist][book_canceled][enabled]',
						'id'			=> self::$PREFIX . 'settings-notif-specialist-after-book-canceled-status',
						'value'			=> '1',
						'active'		=> $login_status,
						'label'			=> esc_html__( "Active Send SMS", 'drplus' ),
						'input_classes'	=> [self::$PREFIX . "status-switch"],
					] );
					?>
				</td>
			</tr>

			<tr class="<?php echo self::$PREFIX ?>settings-gateway-pattern-row">
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-notif-specialist-after-book-pattern"><?php esc_html_e( 'Pattern code', 'drplus' ) ?></label>
				</th>
	
				<td>
					<input
						type="text"
						name="<?php echo self::$PREFIX ?>settings[reserve_notification][specialist][book_canceled][pattern]"
						id="<?php echo self::$PREFIX ?>settings-notif-specialist-after-book-canceled-pattern"
						class="regular-text ltr <?php echo self::$PREFIX ?>settings-pattern"
						value="<?php echo esc_attr( $settings['settings']['reserve_notification']['specialist']['book_canceled']['pattern'] ?? '' ) ?>"
					>
				</td>
			</tr>

			<tr>
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-notif-specialist-after-book-canceled-message"><?php esc_html_e( 'Message variables', 'drplus' ) ?></label>
				</th>

				<td>
					<div class="<?php echo self::$PREFIX ?>variables">
						<script type="text/html" class="<?php echo self::$PREFIX ?>variable-template" id="tmpl-<?php echo self::$PREFIX ?>reserve_notification-specialist-book_canceled-template">
							<?php
							Variable::view( [
								'fields_name'	=> 'settings[reserve_notification][specialist][book_canceled][message]',
								'options'		=> $reserve_variables
							] );
							?>
						</script>
						<?php
						if( !empty( $settings['messages']['reserve_notification']['specialist']['book_canceled'] ) ) {
							$variable_mode = !empty( $settings['gateway'] ) && !empty( $gateways[$settings['gateway']] ) && !empty( $gateways[$settings['gateway']]['variable_mode'] );
							foreach( SMS::convert_string_to_variables( $settings['messages']['reserve_notification']['specialist']['book_canceled'] ) as $gateway_variable => $variable ) {
								Variable::view( [
									'fields_name'		=> 'settings[reserve_notification][specialist][book_canceled][message]',
									'variable_mode'		=> $variable_mode,
									'gateway_variable'	=> $gateway_variable,
									'variable'			=> $variable,
									'options'			=> $reserve_variables
								] );
							}
						}
						?>

						<button type="button" class="<?php echo self::$PREFIX ?>button <?php echo self::$PREFIX ?>variable-add"><?php esc_html_e( 'Add variable', 'drplus' ) ?></button>
					</div>
				</td>
			</tr>
		</table>
	</div>
</div>

<hr>
<div id="<?php echo self::$PREFIX ?>settings-notif-patient-after-book-canceled-container" class="<?php echo self::$PREFIX ?>settings-section">
	<div class="<?php echo self::$PREFIX ?>settings-section-head">
		<h3 class="<?php echo self::$PREFIX ?>section-title"><?php esc_html_e( 'SMS notification to patient after appointment canceled', 'drplus' ) ?></h3>
		<i class="drplus-icon-bottom"></i>
	</div>
	<div class="<?php echo self::$PREFIX ?>settings-section-body">
		<table class="form-table">
			<tr class="<?php echo self::$PREFIX ?>settings-status-row">
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-notif-patient-after-book-status"><?php esc_html_e( 'Status', 'drplus' ) ?></label>
				</th>

				<td>
					<?php
					$login_status = Utils::to_bool( $settings['settings']['reserve_notification']['patient']['book_canceled']['enabled'] ?? false );
					AdminUI::switch( [
						'name'			=> self::$PREFIX . 'settings[reserve_notification][patient][book_canceled][enabled]',
						'id'			=> self::$PREFIX . 'settings-notif-patient-after-book-canceled-status',
						'value'			=> '1',
						'active'		=> $login_status,
						'label'			=> esc_html__( "Active Send SMS", 'drplus' ),
						'input_classes'	=> [self::$PREFIX . "status-switch"],
					] );
					?>
				</td>
			</tr>

			<tr class="<?php echo self::$PREFIX ?>settings-gateway-pattern-row">
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-notif-patient-after-book-pattern"><?php esc_html_e( 'Pattern code', 'drplus' ) ?></label>
				</th>
	
				<td>
					<input
						type="text"
						name="<?php echo self::$PREFIX ?>settings[reserve_notification][patient][book_canceled][pattern]"
						id="<?php echo self::$PREFIX ?>settings-notif-patient-after-book-canceled-pattern"
						class="regular-text ltr <?php echo self::$PREFIX ?>settings-pattern"
						value="<?php echo esc_attr( $settings['settings']['reserve_notification']['patient']['book_canceled']['pattern'] ?? '' ) ?>"
					>
				</td>
			</tr>

			<tr>
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-notif-patient-after-book-canceled-message"><?php esc_html_e( 'Message variables', 'drplus' ) ?></label>
				</th>

				<td>
					<div class="<?php echo self::$PREFIX ?>variables">
						<script type="text/html" class="<?php echo self::$PREFIX ?>variable-template" id="tmpl-<?php echo self::$PREFIX ?>reserve_notification-patient-book_canceled-template">
							<?php
							Variable::view( [
								'fields_name'	=> 'settings[reserve_notification][patient][book_canceled][message]',
								'options'		=> $reserve_variables
							] );
							?>
						</script>
						<?php
						if( !empty( $settings['messages']['reserve_notification']['patient']['book_canceled'] ) ) {
							$variable_mode = !empty( $settings['gateway'] ) && !empty( $gateways[$settings['gateway']] ) && !empty( $gateways[$settings['gateway']]['variable_mode'] );
							foreach( SMS::convert_string_to_variables( $settings['messages']['reserve_notification']['patient']['book_canceled'] ) as $gateway_variable => $variable ) {
								Variable::view( [
									'fields_name'		=> 'settings[reserve_notification][patient][book_canceled][message]',
									'variable_mode'		=> $variable_mode,
									'gateway_variable'	=> $gateway_variable,
									'variable'			=> $variable,
									'options'			=> $reserve_variables
								] );
							}
						}
						?>

						<button type="button" class="<?php echo self::$PREFIX ?>button <?php echo self::$PREFIX ?>variable-add"><?php esc_html_e( 'Add variable', 'drplus' ) ?></button>
					</div>
				</td>
			</tr>
		</table>
	</div>
</div>

<hr>
<div id="<?php echo self::$PREFIX ?>settings-specialist-reminder-notif-container" class="<?php echo self::$PREFIX ?>settings-section">
	<div class="<?php echo self::$PREFIX ?>settings-section-head">
		<h3 class="<?php echo self::$PREFIX ?>section-title"><?php esc_html_e( 'SMS reminder to specialist before the appointment', 'drplus' ) ?></h3>
		<i class="drplus-icon-bottom"></i>
	</div>
	<div class="<?php echo self::$PREFIX ?>settings-section-body">
		<table class="form-table">
			<tr class="<?php echo self::$PREFIX ?>settings-status-row">
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-specialist-reminder-notif-status"><?php esc_html_e( 'Status', 'drplus' ) ?></label>
				</th>
	
				<td>
					<?php
					$login_status = Utils::to_bool( $settings['settings']['reserve_notification']['specialist']['reminder']['enabled'] ?? false );
					AdminUI::switch( [
						'name'			=> self::$PREFIX . 'settings[reserve_notification][specialist][reminder][enabled]',
						'id'			=> self::$PREFIX . 'settings-specialist-reminder-notif-status',
						'value'			=> '1',
						'active'		=> $login_status,
						'label'			=> esc_html__( "Active Send SMS", 'drplus' ),
						'input_classes'	=> [self::$PREFIX . "status-switch"],
					] );
					?>
				</td>
			</tr>

			<tr>
				<th>
					<span><?php esc_html_e( 'List of messages', 'drplus' ) ?></span>
				</th>
			</tr>

			<tr>
				<td colspan="2" class="<?php echo self::$PREFIX ?>settings-repeater-wrap <?php echo self::$PREFIX ?>settings-reminder-repeater-wrap" data-type="specialist">
					<script type="text/html" class="<?php echo self::$PREFIX ?>variable-template" id="tmpl-<?php echo self::$PREFIX ?>reserve_notification-specialist-reminder-template">
						<?php
						Variable::view( [
							'fields_name'	=> 'settings[reserve_notification][specialist][reminder][{{{data.index}}}][message]',
							'options'		=> $reserve_variables
						] );
						?>
					</script>
					<?php
					if( empty( $settings['settings']['reserve_notification']['specialist']['reminder'] ) || count( $settings['settings']['reserve_notification']['specialist']['reminder'] ) === 1 ) {
						echo get_template_part( 'templates/backend/sms/ReminderItem', null, [
							'show_remove_btn'	=> false,
							'status'			=> $settings['settings']['reserve_notification']['specialist']['reminder']['enabled'] ?? false,
						] );
					} else {
						$reminder_index = 1;
						foreach( $settings['settings']['reserve_notification']['specialist']['reminder'] as $reminder_id => $reminder ) {
							if( !is_array( $reminder ) ) continue;
							$message = $settings['messages']['reserve_notification']['specialist']['reminder'][$reminder_id] ?? '';
							echo get_template_part( 'templates/backend/sms/ReminderItem', null, [
								'type'				=> 'specialist',
								'index'				=> $reminder_index,
								'id'				=> $reminder_id,
								'message'			=> $message,
								'timing'			=> $reminder['timing'],
								'pattern'			=> $reminder['pattern'],
								'status'			=> $reminder['enabled'] ?? false,
								'show_remove_btn'	=> $reminder_index != 1,
							] );
							$reminder_index++;
						}
					}
					?>
				</td>
			</tr>

			<tr>
				<td></td>
				<td colspan="2">
					<div class="<?php echo self::$PREFIX ?>settings-repeater-add">
						<span><?php esc_html_e( 'Add new Reminder', 'drplus' ) ?></span>
						<i class="drplus-icon-add-square"></i>
					</div>
				</td>
			</tr>
		</table>
	</div>
</div>
<hr>
<div id="<?php echo self::$PREFIX ?>settings-patient-reminder-notif-container" class="<?php echo self::$PREFIX ?>settings-section">
	<div class="<?php echo self::$PREFIX ?>settings-section-head">
		<h3 class="<?php echo self::$PREFIX ?>section-title"><?php esc_html_e( 'SMS reminder to patient before the appointment', 'drplus' ) ?></h3>
		<i class="drplus-icon-bottom"></i>
	</div>
	<div class="<?php echo self::$PREFIX ?>settings-section-body">
		<table class="form-table">
			<tr class="<?php echo self::$PREFIX ?>settings-status-row">
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-patient-reminder-notif-status"><?php esc_html_e( 'Status', 'drplus' ) ?></label>
				</th>
	
				<td>
					<?php
					$login_status = Utils::to_bool( $settings['settings']['reserve_notification']['patient']['reminder']['enabled'] ?? false );
					AdminUI::switch( [
						'name'			=> self::$PREFIX . 'settings[reserve_notification][patient][reminder][enabled]',
						'id'			=> self::$PREFIX . 'settings-patient-reminder-notif-status',
						'value'			=> '1',
						'active'		=> $login_status,
						'label'			=> esc_html__( "Active Send SMS", 'drplus' ),
						'input_classes'	=> [self::$PREFIX . "status-switch"],
					] );
					?>
				</td>
			</tr>

			<tr>
				<th>
					<span><?php esc_html_e( 'List of messages', 'drplus' ) ?></span>
				</th>
			</tr>

			<tr>
				<td colspan="2" class="<?php echo self::$PREFIX ?>settings-repeater-wrap <?php echo self::$PREFIX ?>settings-reminder-repeater-wrap" data-type="patient">
					<script type="text/html" class="<?php echo self::$PREFIX ?>variable-template" id="tmpl-<?php echo self::$PREFIX ?>reserve_notification-patient-reminder-template">
						<?php
						Variable::view( [
							'fields_name'	=> 'settings[reserve_notification][patient][reminder][{{{data.index}}}][message]',
							'options'		=> $reserve_variables
						] );
						?>
					</script>
					<?php
					if( empty( $settings['settings']['reserve_notification']['patient']['reminder'] ) || count( $settings['settings']['reserve_notification']['patient']['reminder'] ) === 1 ) {
						echo get_template_part( 'templates/backend/sms/ReminderItem', null, [
							'show_remove_btn'	=> false,
							'status'			=> $settings['settings']['reserve_notification']['patient']['reminder']['enabled'] ?? false,
							'type'				=> 'patient'
						] );
					} else {
						$reminder_index = 1;
						foreach( $settings['settings']['reserve_notification']['patient']['reminder'] as $reminder_id => $reminder ) {
							if( !is_array( $reminder ) ) continue;
							$message = $settings['messages']['reserve_notification']['patient']['reminder'][$reminder_id] ?? '';
							echo get_template_part( 'templates/backend/sms/ReminderItem', null, [
								'type'				=> 'patient',
								'index'				=> $reminder_index,
								'id'				=> $reminder_id,
								'message'			=> $message,
								'timing'			=> $reminder['timing'],
								'pattern'			=> $reminder['pattern'],
								'status'			=> $reminder['enabled'] ?? false,
								'show_remove_btn'	=> $reminder_index != 1,
							] );
							$reminder_index++;
						}
					}
					?>
				</td>
			</tr>

			<tr>
				<td></td>
				<td colspan="2">
					<div class="<?php echo self::$PREFIX ?>settings-repeater-add">
						<span><?php esc_html_e( 'Add new Reminder', 'drplus' ) ?></span>
						<i class="drplus-icon-add-square"></i>
					</div>
				</td>
			</tr>
		</table>
	</div>
</div>
<script type="text/html" id="tmpl-drplus-reminder-item">
		<?php
		get_template_part( "templates/backend/sms/ReminderItem", null, [
			'type'				=> '{{data.type}}',
			'index'				=> '{{data.index}}',
			'id'				=> "",
			'message'			=> '',
			'timing'			=> '-1',
			'pattern'			=> "",
			'status'			=> true,
			'show_remove_btn'	=> true,
		] );
		?>
</script>