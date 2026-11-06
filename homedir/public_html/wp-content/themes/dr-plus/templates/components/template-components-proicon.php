<?php

use DrPlus\Utils;
use DrPlus\Utils\Elementor;
use DrPlus\Utils\Sanitizers;

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
	'hover_type'	=> 'fill',
	'is_slider'		=> false,
	'content_style'	=> 'style-1',
	'show_bg_icon'	=> false,
	'bg_icon'		=> [],
], ['icon', 'link', 'bg_icon'] );

if( is_string( $args['link'] ) ) $args['link'] = ['url' => $args['link']];

$args['icon_type'] = Utils::ensure_values_in_array( $args['icon_type'], ['image', 'icon'], 'image' );

$icon = '';
if( $args['icon_type'] == 'image' ) {
	$icon = !empty( $args['img']['id'] ) ? wp_get_attachment_image( $args['img']['id'], [52,52] ) : '<img src="' . $args['img']['url'] . '" alt="">';
} else {
	$icon = Sanitizers::icon( $args['icon'], 'proicon-icon' );
}

$bg_icon = '';
if( $args['show_bg_icon'] ) {
	$bg_icon = Sanitizers::icon( $args['bg_icon'] ?? [], 'proicon-bg-icon' );
}

$args['icon_align'] = Utils::ensure_values_in_array( $args['icon_align'], ['left', 'center', 'right'], 'center' );
$args['title'] = wp_kses_post( $args['title'] );
$args['tag'] = Sanitizers::tag( $args['tag'] );
$args['subtitle'] = wp_kses_post( $args['subtitle'] );
$args['subtitle'] = preg_replace( '/\{([^}]+)\}/', '<span>$1</span>', $args['subtitle'] );

$has_link = !empty( $args['link'] ) && !empty( $args['link']['url'] );

$icon_btn_classes = ['proicon-btn'];
if( $args['show_btn'] ) {
	if( $args['content_style'] == 'style-1' ) {
		$icon_btn_classes[] = is_rtl() ? 'drplus-icon-arrow-square-left' : 'drplus-icon-arrow-square-right';
	} else {
		$icon_btn_classes[] = 'drplus-icon-arrow-left-up';
		$icon_btn_classes[] = 'inner-shadow-style';
	}
}

$classes = ['proicon', "proicon-icon-{$args['icon_align']}", "proicon-hover-{$args['hover_type']}", "proicon-{$args['content_style']}"];
if( $args['is_slider'] ) {
	$classes[] = 'slider-slide';
}
?>
<?php if( $has_link ) { ?>
	<a <?php echo Utils::get_html_attributes( Elementor::get_link_attributes( $args['link'] ) ) ?> class="<?php echo Utils::prepare_html_classes( $classes ) ?>">
<?php } else { ?>
	<div class="<?php echo Utils::prepare_html_classes( $classes ) ?>">
<?php } ?>
	<div class="proicon-img-wrap"><?php echo $icon ?></div>
	<div class="proicon-texts">
		<<?php echo tag_escape( $args['tag'] ) ?> class="proicon-title"><?php echo $args['title'] ?></<?php echo tag_escape( $args['tag'] ) ?>>
		<div class="proicon-subtitle"><?php echo $args['subtitle'] ?></div>
	</div>

	<?php if( $args['show_btn'] ) { ?>
		<i class="<?php echo Utils::prepare_html_classes( $icon_btn_classes ) ?>"></i>
	<?php } ?>

	<?php if( !empty( $bg_icon ) ) echo $bg_icon; ?>
<?php if( $has_link ) { ?>
	</a>
<?php } else { ?>
	</div>
<?php } ?>