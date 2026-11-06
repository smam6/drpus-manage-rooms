<?php

use DrPlus\Utils;
use DrPlus\Utils\Elementor;
use DrPlus\Utils\Sanitizers;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'icon'			=> '',
	'title'			=> '',
	'subtitle'		=> '',
	'link'			=> '',
	'image'			=> '',
	'title_tag'		=> 'h4',
	'subtitle_tag'	=> 'div',
], ['icon', 'image', 'link'] );

$icon = Sanitizers::icon( $args['icon'], 'drplus-cta1-icon' );
$title = wp_kses_post( $args['title'] );
$subtitle = wp_kses_post( $args['subtitle'] );
$image = Utils::convert_chars( !empty( $args['image']['url'] ) ? $args['image']['url'] : $args['image']['id'] );
if( !empty( $image ) ) {
	$image = is_numeric( $image ) ? wp_get_attachment_image( $image, 'medium_large', false, [ 'class' => 'drplus-cta1-img', 'alt' => esc_attr( $title ) ] ) : '<img src="' . esc_url( $image ) . '" alt="' . esc_attr( $title ) . '" class="drplus-cta1-img">';
}
$title_tag = Sanitizers::tag( $args['title_tag'] );
$subtitle_tag = Sanitizers::tag( $args['subtitle_tag'] );

$has_link = !empty( $args['link'] ) && !empty( $args['link']['url'] );
?>
<section class="drplus-cta1">
	<div class="drplus-cta1-content-wrap">
		<?php if( !empty( $icon ) ) : ?>
			<?php if( $has_link ) { ?>
				<a <?php echo Utils::get_html_attributes( Elementor::get_link_attributes( $args['link'] ) ) ?> class="drplus-cta1-btn" role="button"><?php echo $icon ?></a>
			<?php } else { ?>
				<div class="drplus-cta1-btn"><?php echo $icon; ?></div>
			<?php } ?>
		<?php endif; ?>
		<div class="drplus-cta1-content">
			<?php if( !empty( $title ) ) : ?>
				<?php if( $has_link ) { ?>
					<a <?php echo Utils::get_html_attributes( Elementor::get_link_attributes( $args['link'] ) ) ?>>
				<?php } ?>
					<<?php echo tag_escape( $title_tag ) ?> class="drplus-cta1-title"><?php echo esc_html( $title ); ?></<?php echo tag_escape( $title_tag ) ?>>
				<?php if( $has_link ) { ?>
					</a>
				<?php } ?>
			<?php endif; ?>
			<?php if( !empty( $subtitle ) ) : ?>
				<<?php echo tag_escape( $subtitle_tag ) ?> class="drplus-cta1-subtitle"><?php echo esc_html( $subtitle ); ?></<?php echo tag_escape( $subtitle_tag ) ?>>
			<?php endif; ?>
		</div>
	</div>
	<div class="drplus-cta1-image-wrap">
		<?php if( !empty( $image ) ) {
			echo $image;
		} ?>
	</div>
</section>