<?php

use DrPlus\Utils;
use DrPlus\Utils\UI;

$args = Utils::check_default( $args, [
	'index'	=> 0,
	'time'	=> [
		'from'		=> '',
		'to'		=> '',
		'status'	=> 1,
	],
], ['index'] );
?>
<div class="drplus-specialist-form-default-time-row drplus-specialist-form-time-row<?php echo !$args['time']['status'] ? " inactive" : '' ?>">
	<span class="drplus-specialist-form-time-index"><?php echo is_numeric( $args['index'] ) ? $args['index']+1 : $args['index'] ?></span>
	<div class="drplus-specialist-form-time-fields">
		<span class="drplus-specialist-form-time-separator"><?php esc_html_e( 'from', 'drplus' ) ?></span>
		<input type="time" class="drplus-specialist-form-time-input drplus-specialist-form-time-from" name="specialist_default_times[<?php echo $args['index'] ?>][from]" value="<?php echo $args['time']['from'] ?>" required>
		<span class="drplus-specialist-form-time-separator"><?php esc_html_e( 'to', 'drplus' ) ?></span>
		<input type="time" class="drplus-specialist-form-time-input drplus-specialist-form-time-to" name="specialist_default_times[<?php echo $args['index'] ?>][to]" value="<?php echo $args['time']['to'] ?>" required>
	</div>

	<div class="drplus-specialist-form-time-actions">
		<?php
		UI::switch( [
			'active'		=> Utils::to_bool( $args['time']['status'] ),
			'name'			=> "specialist_default_times[{$args['index']}][status]",
			'value'			=> '1',
			'input_classes'	=> ['drplus-specialist-form-time-status'],
		] );
		?>
		<i class="drplus-icon-trash drplus-specialist-form-time-remove" data-type="default"></i>
	</div>
</div>