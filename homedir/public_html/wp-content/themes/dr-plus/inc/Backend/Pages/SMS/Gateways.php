<div class="<?php echo self::$PREFIX ?>tab-content" id="<?php echo self::$PREFIX ?>gateway-content">
	<h3 class="<?php echo self::$PREFIX ?>tab-title" id="<?php echo self::$PREFIX ?>gateways-title"><?php esc_html_e( 'Select the SMS sending gateway', 'drplus' ) ?></h3>

	<div id="<?php echo self::$PREFIX ?>gateways">
		<?php foreach( $gateways as $id => $gateway ) { ?>
			<label class="<?php echo self::$PREFIX ?>gateway<?php echo $settings['gateway'] == $id ? ' active' : '' ?>" data-id="<?php echo esc_attr( $id ) ?>">
				<input type="radio" name="<?php echo self::$PREFIX ?>gateway" value="<?php echo esc_attr( $id ) ?>" <?php checked( $settings['gateway'], $id ) ?>>
				<img src="<?php echo DRPLUS_URI . "assets/images/backend/sms/{$gateway['logo']}" ?>" alt="<?php echo esc_attr( $gateway['label'] ) ?>" class="<?php echo self::$PREFIX ?>gateway-logo">
				<div class="<?php echo self::$PREFIX ?>gateway-label"><?php echo esc_html( $gateway['label'] ) ?></div>
			</label>
		<?php } ?>
	</div>

	<?php foreach( $gateways as $id => $gateway ) { ?>
		<div class="<?php echo self::$PREFIX ?>gateway-fields <?php echo self::$PREFIX ?>gateway-<?php echo $id ?>-fields"<?php echo empty( $settings['gateway'] ) || $settings['gateway'] != $id ? ' style="display:none"' : '' ?>>
			<hr>
			<h3 class="<?php echo self::$PREFIX ?>gateway-fields-title"><?php printf( esc_html_x( '%s settings', 'SMS', 'drplus' ), $gateway['label'] ) ?></h3>
			<table class="form-table">
				<?php if( in_array( 'username', $gateway['fields'] ) ) { ?>
					<tr class="<?php echo self::$PREFIX ?>gateway-field-row">
						<th>
							<label for="<?php echo self::$PREFIX . $id ?>gateway-field-username"><?php esc_html_e( 'Username', 'drplus' ) ?></label>
						</th>
	
						<td>
							<input
								type="text"
								name="<?php echo self::$PREFIX . $id ?>[username]"
								id="<?php echo self::$PREFIX . $id ?>gateway-field-username"
								class="ltr regular-text <?php echo self::$PREFIX ?>gateway-field"
								value="<?php echo esc_attr( $settings[$id]['username'] ?? '' ) ?>"
							>
						</td>
					</tr>
				<?php } ?>

				<?php if( in_array( 'password', $gateway['fields'] ) ) { ?>
					<tr class="<?php echo self::$PREFIX ?>gateway-field-row">
						<th>
							<label for="<?php echo self::$PREFIX . $id ?>gateway-field-password"><?php esc_html_e( 'Password', 'drplus' ) ?></label>
						</th>
	
						<td>
							<input
								type="text"
								name="<?php echo self::$PREFIX . $id ?>[password]"
								id="<?php echo self::$PREFIX . $id ?>gateway-field-password"
								class="ltr regular-text <?php echo self::$PREFIX ?>gateway-field"
								value="<?php echo esc_attr( $settings[$id]['password'] ?? '' ) ?>"
							>
						</td>
					</tr>
				<?php } ?>

				<?php if( in_array( 'api_key', $gateway['fields'] ) ) { ?>
					<tr class="<?php echo self::$PREFIX ?>gateway-field-row">
						<th>
							<label for="<?php echo self::$PREFIX . $id ?>gateway-field-api_key"><?php esc_html_e( 'API Key', 'drplus' ) ?></label>
						</th>
	
						<td>
							<input
								type="text"
								name="<?php echo self::$PREFIX . $id ?>[api_key]"
								id="<?php echo self::$PREFIX . $id ?>gateway-field-api_key"
								class="ltr regular-text <?php echo self::$PREFIX ?>gateway-field"
								value="<?php echo esc_attr( $settings[$id]['api_key'] ?? '' ) ?>"
							>
						</td>
					</tr>
				<?php } ?>

				<?php if( in_array( 'from', $gateway['fields'] ) ) { ?>
					<tr class="<?php echo self::$PREFIX ?>gateway-field-row">
						<th>
							<label for="<?php echo self::$PREFIX . $id ?>gateway-field-from"><?php esc_html_e( 'Sender phone number', 'drplus' ) ?></label>
						</th>
	
						<td>
							<input type="text"
								name="<?php echo self::$PREFIX . $id ?>[from]"
								id="<?php echo self::$PREFIX . $id ?>gateway-field-from"
								class="ltr regular-text <?php echo self::$PREFIX ?>gateway-field"
								value="<?php echo esc_attr( $settings[$id]['from'] ?? '' ) ?>"
							>
						</td>
					</tr>
				<?php } ?>
			</table>
		</div>
	<?php } ?>
</div>