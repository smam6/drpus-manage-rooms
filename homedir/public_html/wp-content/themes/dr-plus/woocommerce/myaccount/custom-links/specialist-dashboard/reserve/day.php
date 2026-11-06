<?php

use DrPlus\Components\Button;
use DrPlus\Utils;
use DrPlus\Utils\UI;

$args = Utils::check_default( $args, [
	'index'	=> 0,
	'day'	=> [
		'day'			=> 0, // Day index
		'day_name'		=> '',
		'use_default'	=> true,
		'times'			=> [],
		'status'		=> true,
	],
], ['index'] );
?>
<div class="drplus-specialist-form-day<?php echo !$args['day']['status'] ? ' inactive' : '' ?>" data-day-index="<?php echo esc_attr( $args['day']['day'] ) ?>">
	<input type="hidden" name="specialist_days[<?php echo esc_attr( $args['day']['day'] ) ?>][day_index]" value="<?php echo esc_attr( $args['day']['day'] ) ?>">
	<div class="drplus-specialist-form-day-head">
		<?php
		UI::switch( [
			'active'		=> Utils::to_bool( $args['day']['status'] ),
			'name'			=> "specialist_days[{$args['day']['day']}][status]",
			'value'			=> '1',
			'input_classes'	=> ['drplus-specialist-form-day-status'],
		] );
		?>
		<div class="drplus-specialist-form-day-name"><?php echo esc_html( $args['day']['day_name'] ) ?></div>
		<label class="checkbox-wrap">
			<input type="checkbox" name="specialist_days[<?php echo esc_attr( $args['day']['day'] ) ?>][default_time]" class="drplus-specialist-form-day-default" value="true" <?php checked( true, $args['day']['use_default'] ) ?>>
			<?php esc_html_e( 'Use Default Times', 'drplus' ) ?>
		</label>
	</div>

	<div class="drplus-specialist-form-day-times-wrap"<?php echo $args['day']['use_default'] ? ' style="display: none"' : '' ?>>
		<div class="drplus-specialist-form-times drplus-specialist-form-day-times">
			<?php
			if( !$args['day']['use_default'] ) {
				foreach( $args['day']['times'] as $index => $time ) {
					get_template_part( "woocommerce/myaccount/custom-links/specialist-dashboard/reserve/custom_time_row", null, [
						'index'		=> $index,
						'day_index'	=> $args['day']['day'],
						'from'		=> $time->from,
						'to'		=> $time->to,
					] );
				}
			}
			?>
		</div>
		<?php
		Button::view( [
			'type'			=> 'action',
			'text'			=> __( 'Add Time', 'drplus' ),
			'icon'			=> 'drplus-icon-add-square',
			'icon_align'	=> 'end',
			'align'			=> 'end',
			'small'			=> true,
			'classes'		=> ['drplus-specialist-form-times-new'],
			'atts'			=> [
				'type'		=> 'button',
				'data-type'	=> 'custom',
			],
		] );
		?>
	</div>
</div>