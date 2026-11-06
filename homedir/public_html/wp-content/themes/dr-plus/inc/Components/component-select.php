<?php

namespace DrPlus\Components;

use DrPlus\Utils;

class Select {
	public static function view( $args ) {
		$args = Utils::check_default( $args, [
			'wrap'		=> [
				'class'		=> ['drplus-select'],
				'classes'	=> [], // Custom classes
			],
			'label'		=> esc_html__( "Select", 'drplus' ),
			'options'	=> [],
			'value'		=> '',
			'linked'	=> false,
			'query_var'	=> '',
		] );

		get_template_part( 'templates/components/template-components-select', null, $args );
	}
}