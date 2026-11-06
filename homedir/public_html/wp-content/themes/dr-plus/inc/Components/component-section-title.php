<?php

namespace DrPlus\Components;

use DrPlus\Utils;

class SectionTitle {
	public static function view( array $args, bool $echo = true ) {
		$args = Utils::check_default( $args, [
			'icon'			=> '',
			'icon_has_bg'	=> true,
			'tag'			=> 'h2',
			'title'			=> '',
			'link'			=> [],
			'nav_btns'		=> false,
			'classes'		=> [],
			'aria-label'	=> '',
		], ['icon', 'link'] );

		if( !$echo ) {
			ob_start();
		}
		get_template_part( 'templates/components/template-components-section_title', null, $args );
		if( !$echo ) {
			return ob_get_clean();
		}
	}
}