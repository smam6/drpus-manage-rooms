<?php

use DrPlus\Utils;

$args = Utils::check_default( $args, [
	'index'		=> 0,
	'day_index'	=> 0,
	'from'		=> '',
	'to'		=> '',
], ['index', 'day_index'] );
?>
<div class="drplus-specialist-form-time-row drplus-specialist-form-time-custom-row">
	<span class="drplus-specialist-form-time-index"><?php echo is_numeric( $args['index'] ) ? $args['index']+1 : $args['index'] ?></span>
	<div class="drplus-specialist-form-time-fields">
		<span class="drplus-specialist-form-time-separator"><?php esc_html_e( 'from', 'drplus' ) ?></span>
		<input type="time" class="drplus-specialist-form-time-input drplus-specialist-form-time-from" name="specialist_days[<?php echo $args['day_index'] ?>][times][<?php echo $args['index'] ?>][from]" value="<?php echo $args['from'] ?>" required>
		<span class="drplus-specialist-form-time-separator"><?php esc_html_e( 'to', 'drplus' ) ?></span>
		<input type="time" class="drplus-specialist-form-time-input drplus-specialist-form-time-to" name="specialist_days[<?php echo $args['day_index'] ?>][times][<?php echo $args['index'] ?>][to]" value="<?php echo $args['to'] ?>" required>
	</div>
	<div class="drplus-specialist-form-time-actions">
		<i class="drplus-icon-trash drplus-specialist-form-time-remove" data-type="custom"></i>
	</div>
</div>