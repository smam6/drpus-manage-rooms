<?php

namespace DrPlus\Components;

use DrPlus\Utils;

class LineDot {
	public static function view( array $args = [], bool $echo = true ) {
		$args = Utils::check_default( $args, [
			'direction'			=> 'end', // start - end
			'width'				=> '', // With unit
			'hover_width'		=> '', // With unit - empty for no change
			'classes'			=> [],
		] );

		$inline_styles = [];
		if( !empty( $args['width'] ) ) {
			$inline_styles['--line-width'] = $args['width'];
		}
		if( !empty( $args['hover_width'] ) ) {
			$inline_styles['--line-hover-width'] = $args['hover_width'];
		}

		$classes = $args['classes'];
		$classes[] = 'drplus-line-dot';
		$classes[] = 'direction-' . $args['direction'];

		if( !$echo ) {
			ob_start();
		}
		?>
		<span class="<?php echo Utils::prepare_html_classes( $classes ) ?>" <?php echo !empty( $inline_styles ) ? Utils::get_html_attributes( ['style' => $inline_styles] ) : "" ?>></span>
		<?php
		if( !$echo ) {
			return ob_get_clean();
		}
	}
}