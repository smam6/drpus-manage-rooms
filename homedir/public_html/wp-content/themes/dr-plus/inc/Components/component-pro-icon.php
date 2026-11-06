<?php

namespace DrPlus\Components;

use DrPlus\Utils;

class ProIcon {
	public static function view( $args ) {
		$args = Utils::check_default( $args, [
			'icon_type'		=> 'image',
			'img'			=> [],
			'icon'			=> [],
			'icon_align'	=> 'center',
			'title'			=> '',
			'tag'			=> 'div',
			'subtitle'		=> '',
			'link'			=> [],
			'show_btn'		=> true,
			'is_slider'		=> false,
		], ['icon', 'link'] );

		get_template_part( 'templates/components/template-components-proicon', null, $args );
	}
}