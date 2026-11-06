<?php

use DrPlus\Utils;
use DrPlus\Utils\Elementor;
use DrPlus\Utils\Sanitizers;

if( !defined( 'ABSPATH' ) ) exit;

$is_rtl = is_rtl();
$args = Utils::check_default( $args, [
	'icon'				=> '',
	'icon_has_bg'		=> true,
	'tag'				=> 'h2',
	'title'				=> '',
	'link'				=> [],
	'subtitle'			=> '',
	'nav_btns'			=> false,
	'next_arrow_icon'	=> $is_rtl ? 'drplus-icon-left' : 'drplus-icon-right',
	'prev_arrow_icon'	=> !$is_rtl ? 'drplus-icon-left' : 'drplus-icon-right',
	'classes'			=> [],
	'aria-label'		=> '',
], ['icon', 'link', 'prev_arrow_icon', 'next_arrow_icon'] );

// Sanitize
$custom_tag = Sanitizers::tag( $args['tag'] );
$icon = Sanitizers::icon( $args['icon'], 'section-title-icon' );
$icon_has_bg = Utils::to_bool( $args['icon_has_bg'] );
$title = wp_kses_post( $args['title'] );
$title = preg_replace( '/\{([^}]+)\}/', '<span>$1</span>', $title );
$subtitle = wp_kses_post( $args['subtitle'] );

if( !empty( $args['link'] ) && is_string( $args['link'] ) ) {
	$args['link'] = ['url' => $args['link']];
}

$classes = ['section-title-wrap'];
if( !empty( $args['classes'] ) ) {
	$classes = array_merge( $classes, $args['classes'] );
}
if( $args['nav_btns'] ) {
	$classes[] = 'section-title-with-arrows';
}
?>
<div class="<?php echo Utils::prepare_html_classes( $classes ) ?>">
	<div class="section-title"<?php echo $args['aria-label'] ? ' aria-label="' . $args['aria-label'] . '"' : '' ?>>
		<<?php echo tag_escape( $custom_tag ) ?> class="section-title-inner">
			<?php
			$icon_args = [
				'icon'			=> $args['icon'],
				'has_bg'		=> $icon_has_bg,
				'classes'		=> ['section-title-icon-wrap'],
				'icon_classes'	=> ['section-title-icon'],
			];
			get_template_part( "templates/components/template-components-simple_icon", null, $icon_args );
			?>
			<?php if( !empty( $args['link'] ) && !empty( !empty( $args['link']['url'] ) ) ) { ?>
				<a <?php echo Utils::get_html_attributes( Elementor::get_link_attributes( $args['link'] ) ) ?> class="section-title-title"><?php echo wp_kses_post( $title ) ?></a>
			<?php } else { ?>
				<span class="section-title-title"><?php echo wp_kses_post( $title ) ?></span>
			<?php } ?>
		</<?php echo tag_escape( $custom_tag ) ?>>
		<?php if( !empty( $subtitle ) ) { ?>
			<div class="section-title-subtitle">
				<?php echo wp_kses_post( $subtitle ) ?>
			</div>
		<?php } ?>
	</div>
	<?php
	if( $args['nav_btns'] ) {
		get_template_part( 'templates/components/template-components-slider_arrows', null, [
			'inline'	=> true,
			'next_icon'	=> $args['next_arrow_icon'],
			'prev_icon'	=> $args['prev_arrow_icon'],
		] );
	}
	?>
</div>