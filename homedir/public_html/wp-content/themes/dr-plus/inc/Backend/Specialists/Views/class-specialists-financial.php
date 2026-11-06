<?php

namespace DrPlus\Backend\Specialists;

use DrPlus\Utils;
use DrPlus\Utils\AdminUI;
use DrPlus\Utils\Formatters;
use DrPlus\Utils\Options;

class SpecialistFinancial extends SpecialistView {
	public static function view() {
		$specialist = parent::$specialist;
		$user_data = $specialist->meta;

		$use_outside_iran = Utils::to_bool( Options::get_options( ['use-outside-iran' => false] )['use-outside-iran'] );

		$card_input_args = [
			'label'			=> esc_html__( 'Card number', 'drplus' ),
			'type'			=> 'text',
			'value'			=> !empty( $user_data['card_number'] ) ? Formatters::card_number( $user_data['card_number'] ) : "",
			'id'			=> parent::$PREFIX . "card_number",
			'name'			=> parent::$PREFIX . "meta[card_number]",
			'input_classes'	=> ['regular-text', 'ltr'],
			'inputmode'		=> 'numeric',
		];
		if( !$use_outside_iran ) {
			$card_input_args['minlength'] = 19;
			$card_input_args['maxlength'] = 19;
			$card_input_args['input_classes'][] = 'drplus-card-number-input';
		} else {
			$card_input_args['input_classes'][] = 'drplus-numeric-input';
		}
		AdminUI::input_with_label( $card_input_args );
		$shaba_input_args = [
			'label'			=> esc_html__( 'SHABA number', 'drplus' ),
			'type'			=> 'text',
			'value'			=> !empty( $user_data['shaba_number'] ) ? Formatters::shaba_number( $user_data['shaba_number'] ) : "",
			'id'			=> parent::$PREFIX . "shaba_number",
			'name'			=> parent::$PREFIX . "meta[shaba_number]",
			'input_classes'	=> ['regular-text', 'ltr'],
			'inputmode'		=> 'numeric',
		];
		if( !$use_outside_iran ) {
			$shaba_input_args['minlength'] = 32;
			$shaba_input_args['maxlength'] = 32;
			$shaba_input_args['placeholder'] = 'IR...';
			$shaba_input_args['input_classes'][] = 'drplus-shaba-number-input';
		} else {
			$shaba_input_args['input_classes'][] = 'drplus-numeric-input';
		}
		AdminUI::input_with_label( $shaba_input_args );
	}
}