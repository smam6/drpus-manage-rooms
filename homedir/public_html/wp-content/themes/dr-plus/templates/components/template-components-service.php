<?php

use DrPlus\Utils;
use DrPlus\Utils\Elementor;
use DrPlus\Utils\Sanitizers;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'icon'			=> '',
	'title'			=> '',
	'subtitle'		=> '',
	'link'			=> [],
	'title_tag'		=> 'div',
	'subtitle_tag'	=> 'div',
], ['icon'] );

$icon = Sanitizers::icon( $args['icon'], 'drplus-service-icon' );
$title = wp_kses_post( $args['title'] );
$subtitle = wp_kses_post( $args['subtitle'] );
$title_tag = Sanitizers::tag( $args['title_tag'] );
$subtitle_tag = Sanitizers::tag( $args['subtitle_tag'] );

$item_classes = ['drplus-service', 'slider-slide'];

$has_link = !empty( $args['link'] ) && !empty( $args['link']['url'] );
?>

<?php if( $has_link ) { ?>
	<a <?php echo Utils::get_html_attributes( Elementor::get_link_attributes( $args['link'] ) ) ?> class="<?php echo Utils::prepare_html_classes( $item_classes ) ?>">
<?php } else { ?>
	<div class="<?php echo Utils::prepare_html_classes( $item_classes ) ?>">
<?php } ?>
	<?php echo $icon ?>
	<div class="drplus-service-texts">
		<<?php echo $title_tag ?> class="drplus-service-title"><?php echo $title ?></<?php echo $title_tag ?>>
		<<?php echo $subtitle_tag ?> class="drplus-service-subtitle"><?php echo $subtitle ?></<?php echo $subtitle_tag ?>>
	</div>
<?php if( $has_link ) { ?>
	</a>
<?php } else { ?>
	</div>
<?php } ?>