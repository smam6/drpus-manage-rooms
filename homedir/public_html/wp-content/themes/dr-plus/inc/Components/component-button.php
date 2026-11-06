<?php

namespace DrPlus\Components;

use DrPlus\Utils;

class Button {
	public static function view( array $args, bool $echo = true ) {
		$args = Utils::check_default( $args, [
			"transparent"	=> false,
			"type"			=> 'primary',
			"small"			=> false,
			"icon"			=> '',
			"text"			=> '',
			"link"			=> '',
			"title"			=> '',
			"new_tab"		=> false,
			"icon_align"	=> 'start',
			"style"			=> 'rounded',
			"align"			=> 'start',
			"fullwidth"		=> false,
			"classes"		=> [],
			"id"			=> '',
			"disabled"		=> false,
			"loading"		=> false,
			'popup'			=> '',
		], ['link', 'icon'] );

		if( !empty( $args['link'] ) && !is_array( $args['link'] ) ) {
			$args['link'] = [
				'url'			=> $args['link'],
				'is_external'	=> !empty( $args["new_tab"] ),
			];
		}

		if( !$echo ) {
			ob_start();
		}
		get_template_part( 'templates/components/template-components-button', null, $args );
		if( !$echo ) {
			return ob_get_clean();
		}
	}
}