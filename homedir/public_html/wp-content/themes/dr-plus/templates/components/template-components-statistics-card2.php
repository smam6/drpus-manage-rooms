<?php

use DrPlus\Utils;
use DrPlus\Utils\Sanitizers;
use MJ\Whitebox\Utils\Elementor;

$is_rtl = is_rtl();
$args = Utils::check_default( $args, [
	'items'				=> [],
	'next_arrow_icon'	=> $is_rtl ? 'drplus-icon-left' : 'drplus-icon-right',
	'prev_arrow_icon'	=> !$is_rtl ? 'drplus-icon-left' : 'drplus-icon-right',
], ['next_arrow_icon', 'prev_arrow_icon'] );
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
	'classes'		=> array_merge( ['drplus-slider-wrap', 'statistics-card2', 'statistics-card2-wrap'], $display_attrs['wrap_classes'] ),
	'data-settings'	=> $display_attrs['args'],
	'style'			=> $display_attrs['style'],
];
$wrap_html_attrs = [
	'classes'	=> array_merge( ['statistics-card2-list', 'wrapper'], $display_attrs['classes'] ),
];
?>
<div <?php echo Utils::get_html_attributes( $main_html_attrs ) ?>>
	<?php
	if( $args['show_arrows'] ) {
		get_template_part( 'templates/components/template-components-slider_arrows', null, [
			'next_icon'	=> $args['next_arrow_icon'],
			'prev_icon'	=> $args['prev_arrow_icon'],
		] );
	}
	?>
	<div <?php echo Utils::get_html_attributes( $wrap_html_attrs ) ?>>
		<?php
		foreach( $args['items'] as $item ) {
			$tag = 'div';
			$item_html_attrs = [
				'classes'	=> ['slider-slide', 'statistics-card2-item']
			];
			if( Elementor::has_link( $item['link'] ) ) {
				$tag = 'a';
				$item_html_attrs = array_merge( Elementor::get_link_attributes( $item['link'] ), $item_html_attrs );
			}
			?>
			<<?php echo "{$tag} " . Utils::get_html_attributes( $item_html_attrs ) ?>>
				<div class="statistics-card2-item-icon-wrap inner-shadow-style">
					<?php echo Sanitizers::icon( $item['icon'], 'statistics-card2-item-icon' ) ?>
				</div>
				<div class="statistics-card2-item-texts">
					<div class="statistics-card2-item-text line-clamp line-clamp-1">
						<?php echo preg_replace( '/\{([^}]+)\}/', '<span>$1</span>', wp_kses_post( $item['text'] ) ) ?>
					</div>
					<?php if( $item['subtitle'] ) { ?>
						<div class="statistics-card2-item-subtitle line-clamp line-clamp-1"><?php echo wp_kses_post( $item['subtitle'] ) ?></div>
					<?php } ?>
				</div>
			</<?php echo $tag ?>>
		<?php } ?>
	</div>
</div>