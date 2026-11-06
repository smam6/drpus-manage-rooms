<?php

use DrPlus\Components\LineDot;
use DrPlus\Utils;
use DrPlus\Utils\Sanitizers;
use MJ\Whitebox\Utils\Elementor;

$args = Utils::check_default( $args, [
	'items'				=> [],
	'show_line_dots'	=> true,
] );
$item_default = [
	'icon_type'	=> 'image',
	'img'		=> [
		'id'	=> 0,
		'url'	=> ''
	],
	'icon'		=> [],
	'text'		=> '',
	'subtitle'	=> '',
	'link'		=> [],
];

$display_attrs = Elementor::get_display_attributes( $args );

$main_html_attrs = [
	'classes'		=> array_merge( ['drplus-slider-wrap', 'statistics-card', 'statistics-card-wrap'], $display_attrs['wrap_classes'] ),
	'data-settings'	=> $display_attrs['args'],
	'style'			=> $display_attrs['style'],
];
$wrap_html_attrs = [
	'classes'	=> array_merge( ['statistics-card-list', 'wrapper'], $display_attrs['classes'] ),
];
?>
<div <?php echo Utils::get_html_attributes( $main_html_attrs ) ?>>
	<div <?php echo Utils::get_html_attributes( $wrap_html_attrs ) ?>>
		<?php
		foreach( $args['items'] as $item ) {
			$tag = 'div';
			$item_html_attrs = [
				'classes'	=> ['slider-slide', 'statistics-card-item']
			];
			if( Elementor::has_link( $item['link'] ) ) {
				$tag = 'a';
				$item_html_attrs = array_merge( Elementor::get_link_attributes( $item['link'] ), $item_html_attrs );
			}
			?>
			<<?php echo "{$tag} " . Utils::get_html_attributes( $item_html_attrs ) ?>>
				<div class="statistics-card-item-inner">
					<div class="statistics-card-item-icon-wrap">
						<?php if( $item['icon_type'] == 'image' ) { ?>
							<?php echo $item['img']['id'] ? wp_get_attachment_image( $item['img']['id'], [56, 56] ) : '<img src="' . $item['img']['url'] . '" alt="">' ?>
						<?php } else { ?>
							<?php echo Sanitizers::icon( $item['icon'], 'statistics-card-item-icon' ) ?>
						<?php } ?>
					</div>
					<div class="statistics-card-item-texts">
						<div class="statistics-card-item-text line-clamp line-clamp-1">
							<?php echo preg_replace( '/\{([^}]+)\}/', '<span>$1</span>', wp_kses_post( $item['text'] ) ) ?>
							<?php if( $args['show_line_dots'] ) {
								LineDot::view();
							} ?>
						</div>
						<?php if( $item['subtitle'] ) { ?>
							<div class="statistics-card-item-subtitle line-clamp line-clamp-1"><?php echo wp_kses_post( $item['subtitle'] ) ?></div>
						<?php } ?>
					</div>
				</div>
				<i class="statistics-card-item-arrow drplus-icon-<?php echo is_rtl() ? 'left' : 'right' ?>" aria-hidden="true"></i>
			</<?php echo $tag ?>>
		<?php } ?>
	</div>
</div>