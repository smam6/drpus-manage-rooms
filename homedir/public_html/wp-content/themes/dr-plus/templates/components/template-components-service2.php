<?php

use DrPlus\Components\LineDot;
use DrPlus\Utils;
use DrPlus\Utils\Sanitizers;
use MJ\Whitebox\Utils\Elementor;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'icon'			=> '',
	'title'			=> '',
	'subtitle'		=> '',
	'link'			=> [],
	'item_type'		=> 'content',
	'title_tag'		=> 'div',
	'subtitle_tag'	=> 'div',
], ['icon'] );

$item_classes = ['drplus-service2', 'slider-slide'];

if( $args['item_type'] == 'content' ) {
	$icon = Sanitizers::icon( $args['icon'], 'drplus-service2-icon inner-shadow-style' );
	$title = wp_kses_post( $args['title'] );
	$subtitle = wp_kses_post( $args['subtitle'] );
	$title_tag = Sanitizers::tag( $args['title_tag'] );
	$subtitle_tag = Sanitizers::tag( $args['subtitle_tag'] );
	
	
	$has_link = !empty( $args['link'] ) && !empty( $args['link']['url'] );
} else {
	$has_link = false;
	$item_classes[] = 'slide-is-divider';
}
?>

<?php if( $has_link ) { ?>
	<a <?php echo Utils::get_html_attributes( Elementor::get_link_attributes( $args['link'] ) ) ?> class="<?php echo Utils::prepare_html_classes( $item_classes ) ?>">
<?php } else { ?>
	<div class="<?php echo Utils::prepare_html_classes( $item_classes ) ?>">
<?php } ?>
<?php if( $args['item_type'] == 'content' ) { ?>
	<?php echo $icon ?>
	<div class="drplus-service2-texts">
		<<?php echo $title_tag ?> class="drplus-service2-title"><?php echo $title ?></<?php echo $title_tag ?>>
		<<?php echo $subtitle_tag ?> class="drplus-service2-subtitle"><?php echo $subtitle ?></<?php echo $subtitle_tag ?>>
	</div>
<?php } else {
	LineDot::view( [
		'width'	=> '70%',
	] );
} ?>
<?php if( $has_link ) { ?>
	</a>
<?php } else { ?>
	</div>
<?php } ?>