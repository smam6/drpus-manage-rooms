<?php

use DrPlus\Utils;
use DrPlus\Utils\Elementor;
use DrPlus\Utils\Sanitizers;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'title'			=> '',
	'subtitle'		=> '',
	'image'			=> [],
	'title_tag'		=> 'h4',
	'subtitle_tag'	=> 'div',
] );
$args = Elementor::check_button_defaults( $args );

$title_tag = Sanitizers::tag( $args['title_tag'] );
$subtitle_tag = Sanitizers::tag( $args['subtitle_tag'] );

$args['prefix'] = 'button_';
?>
<div class="drplus-cta2">
	<div class="drplus-cta2-bg"></div>
	<div class="drplus-cta2-content">
		<<?php echo tag_escape( $title_tag ) ?> class="drplus-cta2-title"><?php echo wp_kses_post( $args['title'] ) ?></<?php echo tag_escape( $title_tag ) ?>>
		<<?php echo tag_escape( $subtitle_tag ) ?> class="drplus-cta2-subtitle"><?php echo wp_kses_post( $args['subtitle'] ) ?></<?php echo tag_escape( $subtitle_tag ) ?>>
		
		<?php echo !empty( $args['image']['id'] ) ? wp_get_attachment_image( $args['image']['id'], 'full', false, ['class' => 'drplus-cta2-image'] ) : '<img src="' . esc_url( $args['image']['url'] ) . '" alt="" class="drplus-cta2-image">' ?>

		<?php get_template_part( "templates/components/template-components-button", null, $args ) ?>
	</div>
</div>