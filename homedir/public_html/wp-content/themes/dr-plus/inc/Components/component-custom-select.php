<?php

namespace DrPlus\Components;

use DrPlus\Utils;

class CustomSelect {
	public static function view( $args ) {
		$args = Utils::check_default( $args, [
			'wrap'		=> [
				'class'		=> ['drplus-custom-select-wrap'],
				'classes'	=> [], // Custom classes
			],
			'select'	=> [
				'name'		=> '',
				'id'		=> wp_generate_uuid4(),
				'class'		=> ['drplus-custom-select'],
				'classes'	=> [], // Custom classes
			],
			'placeholder'	=> esc_html__( "Select", 'drplus' ),
			'options'		=> [],
			'value'			=> '',
		] );

		get_template_part( 'templates/components/template-components-custom-select', null, $args );
	}
}