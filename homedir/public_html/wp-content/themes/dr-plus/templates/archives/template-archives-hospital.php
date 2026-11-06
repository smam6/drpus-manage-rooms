<?php

use DrPlus\Components\Button;
use DrPlus\Utils;
use DrPlus\Utils\Hospital;
use DrPlus\Utils\Sanitizers;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'archive_hospital_title_tag'		=> 'h2',
	'archive_hospital_show_subtitle'	=> true,
	'archive_hospital_show_address'		=> true,
	'archive_hospital_show_read_more'	=> true,
	'archive_hospital_read_more_text'	=> __( "View details", 'drplus' ),
	'archive_hospital_read_more_icon'	=> is_rtl() ? 'drplus-icon-arrow-up-left-square' : 'drplus-icon-arrow-up-right-square',
], ['archive_hospital_read_more_icon'] );
$title_tag = Sanitizers::tag( $args['archive_hospital_title_tag'] );

$classes = [get_post_type(), 'slider-slide'];

$hospital = Hospital::get_options( get_the_ID() );
$has_thumb = !post_password_required() && has_post_thumbnail();
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( Utils::prepare_html_classes( $classes ) ); ?>>
	<a href="<?php echo get_permalink() ?>" title="<?php echo get_the_title() ?>">
		<?php
		if( $has_thumb ) {
			drplus_post_thumbnail( null, null, false );
		} else {
			$options = Hospital::get_options( get_the_ID() );
			if( !empty( $options['gallery'] ) ) {
				?>
				<figure class="post-thumbnail">
					<a href="<?php echo $post_link ?>" aria-hidden="true"><?php echo wp_get_attachment_image( $options['gallery'][0], 'post-thumbnail' ) ?></a>
				</figure>
				<?php
			}
		}
		?>

		<<?php echo tag_escape( $title_tag ) ?> class="post-title line-clamp line-clamp-2"><?php echo drplus_get_post_title() ?></<?php echo tag_escape( $title_tag ) ?>>

		<?php if( $args['archive_hospital_show_subtitle'] && $hospital['subtitle'] ) { ?>
			<div class="hospital-subtitle line-clamp line-clamp-2"><?php echo esc_html( $hospital['subtitle'] ) ?></div>
		<?php } ?>

		<?php if( $args['archive_hospital_show_address'] && $hospital['address'] ) { ?>
			<div class="hospital-address-wrap">
				<i class="hospital-address-icon drplus-icon-location-fill"></i>
				<address class="hospital-address line-clamp line-clamp-1"><?php echo esc_html( $hospital['address'] ) ?></address>
			</div>
		<?php } ?>

		<?php
		if( $args['archive_hospital_show_read_more'] ) {
			Button::view( [
				'text'			=> $args['archive_hospital_read_more_text'],
				'icon'			=> $args['archive_hospital_read_more_icon'],
				'icon_align'	=> 'end',
				'fullwidth'		=> true,
				'small'			=> true,
			] );
		}
		?>
	</a>
</article>