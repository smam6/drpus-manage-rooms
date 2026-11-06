<?php
namespace DrPlus\SMS\Templates\Backend;

use DrPlus\Utils;

class Variable {
	public static function view( $args ) {
		$prefix = 'drplus_sms_';
		$args = Utils::check_default( $args, [
			'variable_mode'		=> true,
			'fields_name'		=> '',
			'gateway_variable'	=> '',
			'variable'			=> '',
			'options'			=> [],
		], ['gateway_variable'] );

		?>
		<div class="<?php echo $prefix ?>variable">
			<label class="<?php echo $prefix ?>variable-input-wrap <?php echo $prefix ?>settings-gateway-var-wrap"<?php echo $args['variable_mode'] ? '' : ' style="display:none"' ?>>
				<span class="<?php echo $prefix ?>variable-label"><?php esc_html_e( 'Gateway variable', 'drplus' ) ?>:</span>
				<input
					type="text"
					name="<?php echo $prefix . $args['fields_name'] ?>[gateway_vars][]"
					class="ltr <?php echo $prefix ?>settings-gateway-var"
					value="<?php echo $args['variable_mode'] ? esc_attr( $args['gateway_variable'] ) : '' ?>"
					placeholder="<?php esc_attr_e( "Gateway variable", 'drplus' ) ?>"
				>
			</label>

			<label class="<?php echo $prefix ?>variable-input-wrap <?php echo $prefix ?>settings-var-wrap">
				<span class="<?php echo $prefix ?>variable-label"><?php esc_html_e( 'Variable', 'drplus' ) ?>:</span>
				<select name="<?php echo $prefix . $args['fields_name'] ?>[vars][]" class="widefat <?php echo $prefix ?>settings-var">
					<?php foreach( $args['options'] as $option => $label ) { ?>
						<option value="<?php echo esc_attr( $option ) ?>" <?php selected( $args['variable'], "{" . $option . "}" ) ?>><?php echo esc_html( $label ) ?></option>
					<?php } ?>
				</select>
			</label>

			<button type="button" class="<?php echo $prefix ?>variable-remove"><i class="dashicons dashicons-trash"></i></button>
		</div>
		<?php
	}
}