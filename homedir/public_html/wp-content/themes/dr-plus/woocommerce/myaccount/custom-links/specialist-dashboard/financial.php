<?php

use DrPlus\Utils;
use DrPlus\Utils\Formatters;
use DrPlus\Utils\Options;
use DrPlus\Utils\UI;

if( !defined( 'ABSPATH' ) ) exit;

$use_outside_iran = Utils::to_bool( Options::get_options( ['use-outside-iran' => false] )['use-outside-iran'] );
?>
<div class="drplus-specialist-form-body drplus-specialist-form-financial">
	<?php
	$card_input_args = [
		'label'			=> esc_html__( 'Card number', 'drplus' ),
		'type'			=> 'text',
		'value'			=> Formatters::card_number( $specialist->meta['card_number'] ?? "" ),
		'id'			=> "specialist_card_number",
		'name'			=> "specialist_meta[card_number]",
		'input_classes'	=> ['input-ltr'],
		'inputmode'		=> 'numeric',
		'required'		=> 'required',
	];
	if( !$use_outside_iran ) {
		$card_input_args['minlength'] = 19;
		$card_input_args['maxlength'] = 19;
		$card_input_args['input_classes'][] = 'drplus-card-number-input';
	} else {
		$card_input_args['input_classes'][] = 'drplus-numeric-input';
	}
	UI::input_with_label( $card_input_args );

	$shaba_input_args = [
		'label'			=> esc_html__( 'SHABA number', 'drplus' ),
		'type'			=> 'text',
		'value'			=> Formatters::shaba_number( $specialist->meta['shaba_number'] ?? "" ),
		'id'			=> "specialist_shaba_number",
		'name'			=> "specialist_meta[shaba_number]",
		'input_classes'	=> ['input-ltr'],
		'inputmode'		=> 'numeric',
		'required'		=> 'required',
	];
	if( !$use_outside_iran ) {
		$shaba_input_args['minlength'] = 32;
		$shaba_input_args['maxlength'] = 32;
		$shaba_input_args['placeholder'] = 'IR...';
		$shaba_input_args['input_classes'][] = 'drplus-shaba-number-input';
	} else {
		$shaba_input_args['input_classes'][] = 'drplus-numeric-input';
	}
	UI::input_with_label( $shaba_input_args );
	?>
</div>