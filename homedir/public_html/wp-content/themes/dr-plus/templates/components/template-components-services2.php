<?php

use DrPlus\Utils;
use DrPlus\Utils\Elementor;

if( !defined( 'ABSPATH' ) ) exit;
$is_rtl = is_rtl();

$args = Utils::check_default( $args, [
	'items'			=> [],
	'title_tag'		=> 'div',
	'subtitle_tag'	=> 'div',
	'next_arrow_icon'	=> $is_rtl ? 'drplus-icon-left' : 'drplus-icon-right',
	'prev_arrow_icon'	=> !$is_rtl ? 'drplus-icon-left' : 'drplus-icon-right',
], ['next_arrow_icon', 'prev_arrow_icon'] );

$display_attributes = Elementor::get_display_attributes( $args );

$attributes = [
	'class'	=> array_merge( [
		'drplus-slider-wrap',
		'drplus-services2-slider',
	], $display_attributes['wrap_classes'] ),
	'data-settings'	=> $display_attributes['args'],
	'style'			=> $display_attributes['style'],
];

$wrapper_attributes = [
	'class'	=> array_merge( [
		'wrapper',
		'drplus-services2',
	], $display_attributes['classes'] ),
];
?>
<div <?php echo Utils::get_html_attributes( $attributes ) ?>>
	<?php
	get_template_part( 'templates/components/template-components-slider_arrows', null, [
		'next_icon'	=> $args['next_arrow_icon'],
		'prev_icon'	=> $args['prev_arrow_icon'],
	] );
	?>
	<div <?php echo Utils::get_html_attributes( $wrapper_attributes ) ?>>
		<?php
		foreach( $args['items'] as $item ) {
			$item['title_tag'] = $args['title_tag'];
			$item['subtitle_tag'] = $args['subtitle_tag'];
			get_template_part( "templates/components/template-components-service2", null, $item );
		}
		?>
	</div>
</div>