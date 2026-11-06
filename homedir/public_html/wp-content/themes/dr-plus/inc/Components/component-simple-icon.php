<?php

namespace DrPlus\Components;

use DrPlus\Utils;

class SimpleIcon {
	public static function view( array $args, bool $echo = true ) {
		$args = Utils::check_default( $args, [
			'icon'			=> [],
			'has_bg'		=> true,
			'link'			=> [],
			'classes'		=> [],
			'icon_classes'	=> [],
		], ['icon'] );

		if( !$echo ) {
			ob_start();
		}
		get_template_part( 'templates/components/template-components-simple_icon', null, $args );
		if( !$echo ) {
			return ob_get_clean();
		}
	}
}