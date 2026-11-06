<?php

use DrPlus\Utils;
use DrPlus\Utils\Elementor;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'items'			=> [],
	'title_tag'		=> 'div',
	'subtitle_tag'	=> 'div',
] );
$display_attributes = Elementor::get_display_attributes( $args );

$attributes = [
	'class'	=> array_merge( [
		'drplus-slider-wrap',
		'drplus-services-slider',
	], $display_attributes['wrap_classes'] ),
	'data-settings'	=> $display_attributes['args'],
	'style'			=> $display_attributes['style'],
];

$wrapper_attributes = [
	'class'	=> array_merge( [
		'wrapper',
		'drplus-services',
	], $display_attributes['classes'] ),
];
?>
<div <?php echo Utils::get_html_attributes( $attributes ) ?>>
	<div <?php echo Utils::get_html_attributes( $wrapper_attributes ) ?>>
		<?php
		foreach( $args['items'] as $item ) {
			$item['title_tag'] = $args['title_tag'];
			$item['subtitle_tag'] = $args['subtitle_tag'];
			get_template_part( "templates/components/template-components-service", null, $item );
		}
		?>
	</div>
</div>