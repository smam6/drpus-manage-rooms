<?php
use DrPlus\Utils;
use DrPlus\Utils\AdminUI;
use DrPlus\Utils\UtilsSpecialists;

$statuses = UtilsSpecialists::statuses( true );
?>
<div id="<?php echo self::$PREFIX ?>settings-new-specialist-request" class="<?php echo self::$PREFIX ?>settings-section">
	<div class="<?php echo self::$PREFIX ?>settings-section-head">
		<h3 class="<?php echo self::$PREFIX ?>section-title"><?php esc_html_e( 'New specialist request notification', 'drplus' ) ?></h3>
		<i class="drplus-icon-bottom"></i>
	</div>
	<div class="<?php echo self::$PREFIX ?>settings-section-body">
		<table class="form-table">
			<tr class="<?php echo self::$PREFIX ?>settings-status-row">
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-new-specialist-request-status"><?php esc_html_e( 'Status', 'drplus' ) ?></label>
				</th>

				<td>
					<?php
					$notif_status = Utils::to_bool( $settings['settings']['specialist_panel']['new_request']['enabled'] ?? false );
					AdminUI::switch( [
						'name'			=> self::$PREFIX . 'settings[specialist_panel][new_request][enabled]',
						'id'			=> self::$PREFIX . 'settings-new-specialist-request-status',
						'value'			=> '1',
						'active'		=> $notif_status,
						'label'			=> esc_html__( "Active Send SMS", 'drplus' ),
						'input_classes'	=> [self::$PREFIX . "status-switch"],
					] );
					?>
				</td>
			</tr>

			<tr class="<?php echo self::$PREFIX ?>settings-gateway-pattern-row">
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-new-specialist-request-pattern"><?php esc_html_e( 'Pattern code', 'drplus' ) ?></label>
				</th>
	
				<td>
					<input
						type="text"
						name="<?php echo self::$PREFIX ?>settings[specialist_panel][new_request][pattern]"
						id="<?php echo self::$PREFIX ?>settings-new-specialist-request-pattern"
						class="regular-text ltr <?php echo self::$PREFIX ?>settings-pattern"
						value="<?php echo esc_attr( $settings['settings']['specialist_panel']['new_request']['pattern'] ?? '' ) ?>"
					>
				</td>
			</tr>

			<tr>
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-new-specialist-request-message"><?php esc_html_e( 'Message variables', 'drplus' ) ?></label>
				</th>

				<td>
					<textarea
						name="<?php echo self::$PREFIX ?>settings[specialist_panel][new_request][message]"
						id="<?php echo self::$PREFIX ?>new-specialist-request-message"
						class="large-text ltr <?php echo self::$PREFIX ?>settings-message"
						rows="5"
					><?php echo esc_textarea( $settings['messages']['specialist_panel']['new_request'] ?? '' ) ?></textarea>
					<p class="description"><?php echo esc_html__( 'You can use these variables in the message', 'drplus' ) ?>:</p>
					<?php Utils::variables_html( [
						'user_fullname'		=> esc_html__( 'User full name', 'drplus' ),
						'requested_date'	=> esc_html__( 'Requested date', 'drplus' ),
						'domain'			=> esc_html__( "The domain name", 'drplus' ),
						'name'				=> esc_html__( "The website name", 'drplus' ),
					], true ) ?>
				</td>
			</tr>

			<tr>
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-new-specialist-request-recipients"><?php esc_html_e( 'Recipients\' phone numbers', 'drplus' ) ?></label>
				</th>

				<td>
					<textarea
						name="<?php echo self::$PREFIX ?>settings[specialist_panel][new_request][recipients]"
						id="<?php echo self::$PREFIX ?>new-specialist-request-recipients"
						class="large-text ltr <?php echo self::$PREFIX ?>settings-message"
						rows="5"
					><?php echo esc_textarea( implode( PHP_EOL, $settings['settings']['specialist_panel']['new_request']['recipients'] ?? [] ) ) ?></textarea>
					<p class="description"><?php echo esc_html__( 'Enter each phone number on one line.', 'drplus' ) ?></p>
					<p class="description"><?php echo esc_html__( 'Invalid phone numbers will be removed from the list.', 'drplus' ) ?></p>
				</td>
			</tr>
		</table>
	</div>
</div>

<?php foreach( $statuses as $status => $label ) { ?>
	<hr>
	<div id="<?php echo self::$PREFIX ?>settings-change-specialist-status-<?php echo $status ?>" class="<?php echo self::$PREFIX ?>settings-section">
		<div class="<?php echo self::$PREFIX ?>settings-section-head">
			<h3 class="<?php echo self::$PREFIX ?>section-title"><?php printf( esc_html__( 'Change specialist status to %s notification', 'drplus' ), $label ) ?></h3>
			<i class="drplus-icon-bottom"></i>
		</div>
		<div class="<?php echo self::$PREFIX ?>settings-section-body">
			<table class="form-table">
				<tr class="<?php echo self::$PREFIX ?>settings-status-row">
					<th>
						<label for="<?php echo self::$PREFIX ?>settings-change-specialist-status-status"><?php esc_html_e( 'Status', 'drplus' ) ?></label>
					</th>
	
					<td>
						<?php
						$notif_status = Utils::to_bool( $settings['settings']['specialist_panel']['change_status'][$status]['enabled'] ?? false );
						AdminUI::switch( [
							'name'			=> self::$PREFIX . "settings[specialist_panel][change_status][{$status}][enabled]",
							'id'			=> self::$PREFIX . "settings-change-specialist-status-status-{$status}",
							'value'			=> '1',
							'active'		=> $notif_status,
							'label'			=> esc_html__( "Active Send SMS", 'drplus' ),
							'input_classes'	=> [self::$PREFIX . "status-switch"],
						] );
						?>
					</td>
				</tr>
	
				<tr class="<?php echo self::$PREFIX ?>settings-gateway-pattern-row">
					<th>
						<label for="<?php echo self::$PREFIX ?>settings-change-specialist-status-pattern"><?php esc_html_e( 'Pattern code', 'drplus' ) ?></label>
					</th>
		
					<td>
						<input
							type="text"
							name="<?php echo self::$PREFIX ?>settings[specialist_panel][change_status][<?php echo $status ?>][pattern]"
							id="<?php echo self::$PREFIX ?>settings-change-specialist-status-pattern<?php echo $status ?>"
							class="regular-text ltr <?php echo self::$PREFIX ?>settings-pattern"
							value="<?php echo esc_attr( $settings['settings']['specialist_panel']['change_status'][$status]['pattern'] ?? '' ) ?>"
						>
					</td>
				</tr>
	
				<tr>
					<th>
						<label for="<?php echo self::$PREFIX ?>settings-change-specialist-status-message<?php echo $status ?>"><?php esc_html_e( 'Message variables', 'drplus' ) ?></label>
					</th>
	
					<td>
						<textarea
							name="<?php echo self::$PREFIX ?>settings[specialist_panel][change_status][<?php echo $status ?>][message]"
							id="<?php echo self::$PREFIX ?>change-specialist-status-message<?php echo $status ?>"
							class="large-text ltr <?php echo self::$PREFIX ?>settings-message"
							rows="5"
						><?php echo esc_textarea( $settings['messages']['specialist_panel']['change_status'][$status] ?? '' ) ?></textarea>
						<p class="description"><?php echo esc_html__( 'You can use these variables in the message', 'drplus' ) ?>:</p>
						<?php Utils::variables_html( [
							'user_fullname'		=> esc_html__( 'User full name', 'drplus' ),
							'new_status'		=> esc_html__( 'New status', 'drplus' ),
							'old_status'		=> esc_html__( 'Old status', 'drplus' ),
							'domain'			=> esc_html__( "The domain name", 'drplus' ),
							'name'				=> esc_html__( "The website name", 'drplus' ),
						], true ) ?>
					</td>
				</tr>
			</table>
		</div>
	</div>
<?php } ?>