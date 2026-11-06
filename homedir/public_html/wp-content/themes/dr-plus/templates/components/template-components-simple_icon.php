<?php

use DrPlus\Utils;
use DrPlus\Utils\Elementor;
use DrPlus\Utils\Sanitizers;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'icon'			=> [],
	'has_bg'		=> true,
	'link'			=> [],
	'classes'		=> [],
	'icon_classes'	=> [],
], ['icon'] );

$wrap_classes = ['drplus-simple-icon-wrap'];
if( $args['has_bg'] ) {
	$wrap_classes[] = 'icon-has-bg';
}
$wrap_classes = array_merge( $wrap_classes, $args['classes'] );

$icon_classes = array_merge( ['drplus-simple-icon'], $args['icon_classes'] );

$icon = Sanitizers::icon( $args['icon'], Utils::prepare_html_classes( $icon_classes ) );

$has_link = !empty( $args['link'] ) && !empty( $args['link']['url'] );
?>
<?php if( $has_link ) { ?>
	<a <?php echo Utils::get_html_attributes( Elementor::get_link_attributes( $args['link'] ) ) ?> class="<?php echo Utils::prepare_html_classes( $wrap_classes ) ?>">
<?php } else { ?>
	<span class="<?php echo Utils::prepare_html_classes( $wrap_classes ) ?>">
<?php } ?>
<?php echo $icon ?>
<?php if( $has_link ) { ?>
	</a>
<?php } else { ?>
	</span>	
<?php } ?>