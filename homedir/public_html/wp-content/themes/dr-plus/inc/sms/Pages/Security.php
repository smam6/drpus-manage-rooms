<?php

use DrPlus\Utils as DrPlusUtils;
use DrPlus\SMS\Utils;

?>
<div class="<?php echo self::$PREFIX ?>tab-content" id="<?php echo self::$PREFIX ?>security-content" style="display:none;">
	<table class="form-table">
		<tr>
			<th>
				<label for="<?php echo self::$PREFIX ?>security-hide-mobile"><?php esc_html_e( 'Hide user mobile number', 'drplus' ) ?></label>
			</th>

			<td>
				<select name="<?php echo self::$PREFIX ?>security[hide_mobile]" id="<?php echo self::$PREFIX ?>security-hide-mobile" class="regular-text">
					<?php foreach( Utils::hide_mobile_types() as $key => $label ) { ?>
						<option value="<?php echo esc_attr( $key ) ?>" <?php selected( $key, $settings['security']['hide_mobile'] ) ?>><?php echo esc_html( $label ) ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>

		<tr id="<?php echo self::$PREFIX ?>security-hide-mobile-custom-row" <?php echo $settings['security']['hide_mobile'] != 'custom' ? ' style="display:none"' : '' ?>>
			<th>
				<label for="<?php echo self::$PREFIX ?>security-hide-mobile-custom"><?php esc_html_e( 'Custom text', 'drplus' ) ?></label>
			</th>

			<td>
				<input type="text" name="<?php echo self::$PREFIX ?>security[hide_mobile_custom]" id="<?php echo self::$PREFIX ?>security-hide-mobile-custom" class="regular-text" value="<?php echo esc_attr( $settings['security']['hide_mobile_custom'] ) ?>">
				<?php DrPlusUtils::variables_html( Utils::security_variables() ) ?>
			</td>
		</tr>
	</table>
</div>