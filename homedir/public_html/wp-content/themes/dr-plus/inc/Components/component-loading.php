<?php

namespace DrPlus\Components;

use DrPlus\Utils;

class Loading {
	public static function view( $args ) {
		$args = Utils::check_default( $args, [
			'text'		=> '',
			'classes'	=> [],
		] );

		get_template_part( 'templates/components/template-components-loading', null, $args );
	}
}