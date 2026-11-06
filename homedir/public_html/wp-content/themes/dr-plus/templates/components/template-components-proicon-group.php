<?php

use DrPlus\Utils;
use DrPlus\Utils\Elementor;

$args = Utils::check_default( $args, [
	'items'	=> [],
] );
$display_attributes = Elementor::get_display_attributes( $args );

$attributes = [
	'class'	=> array_merge( [
		'drplus-slider-wrap',
		'drplus-proicon-slider',
	], $display_attributes['wrap_classes'] ),
	'data-settings'	=> $display_attributes['args'],
	'style'			=> $display_attributes['style'],
];
$wrapper_attributes = [
	'class'	=> array_merge( [
		'wrapper',
		'drplus-proicon-group',
	], $display_attributes['classes'] ),
];
?>
<div <?php echo Utils::get_html_attributes( $attributes ) ?>>
	<div <?php echo Utils::get_html_attributes( $wrapper_attributes ) ?>>
		<?php
		foreach( $args['items'] as $item ) {
			$item['is_slider'] = true;
			get_template_part( "templates/components/template-components-proicon", null, $item );
		}
		?>
	</div>
</div>