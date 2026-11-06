<?php

use DrPlus\Utils;
use DrPlus\Utils\Elementor;
use DrPlus\Utils\Sanitizers;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'items'			=> [],
	'title_tag'		=> 'div',
	'subtitle_tag'	=> 'div',
] );
foreach( $args['items'] as $index => $item ) {
	$args['items'][$index] = Utils::check_default( $item, [
		'number'	=> '0',
		'title'		=> '',
		'subtitle'	=> '',
		'link'		=> [],
	] );
}

$display_attributes = Elementor::get_display_attributes( $args );

$attributes = [
	'class'	=> array_merge( [
		'drplus-slider-wrap',
		'drplus-statistics-slider',
	], $display_attributes['wrap_classes'] ),
	'data-settings'	=> $display_attributes['args'],
	'style'			=> $display_attributes['style'],
];

$wrapper_attributes = [
	'class'	=> array_merge( [
		'wrapper',
		'drplus-statistics',
	], $display_attributes['classes'] ),
];

$title_tag = Sanitizers::tag( $args['title_tag'] );
$subtitle_tag = Sanitizers::tag( $args['subtitle_tag'] );
?>
<div <?php echo Utils::get_html_attributes( $attributes ) ?>>
	<div <?php echo Utils::get_html_attributes( $wrapper_attributes ) ?>>
		<?php
		foreach( $args['items'] as $item ) {
			if( empty( $item['title'] ) ) continue;
			$has_link = !empty( $item['link'] ) && !empty( $item['link']['url'] );
			if( $has_link ) {
				?>
				<a <?php echo Utils::get_html_attributes( Elementor::get_link_attributes( $item['link'] ) ) ?> class="slider-slide statistic" title="<?php echo esc_attr( $item['title'] ) ?>">
			<?php } else { ?>
				<div class="slider-slide statistic">
			<?php } ?>
				<div class="statistic-number"><?php echo esc_html( $item['number'] ) ?></div>
				<div class="statistic-texts">
					<<?php echo tag_escape( $title_tag ) ?> class="statistic-title line-clamp line-clamp-1"><?php echo wp_kses_post( $item['title'] ) ?></<?php echo tag_escape( $title_tag ) ?>>
					<<?php echo tag_escape( $subtitle_tag ) ?> class="statistic-subtitle line-clamp line-clamp-1"><?php echo wp_kses_post( $item['subtitle'] ) ?></<?php echo tag_escape( $subtitle_tag ) ?>>
				</div>
			<?php if( $has_link ) { ?>
				</a>
			<?php } else { ?>
				</div>
			<?php } ?>
		<?php } ?>
	</div>
</div>