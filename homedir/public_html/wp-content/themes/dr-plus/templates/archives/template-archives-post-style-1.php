<?php

use DrPlus\Utils;
use DrPlus\Utils\Sanitizers;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'title_tag'			=> 'h2',
	'show_time'			=> true,
	'time_type'			=> 'date', // date | difference
	'show_read_more'	=> true,
	'read_more_text'	=> __( "Read more", 'drplus' ),
	'read_more_icon'	=> is_rtl() ? 'drplus-icon-arrow-left' : 'drplus-icon-arrow-right',
], ['read_more_icon'] );
$title_tag = Sanitizers::tag( $args['title_tag'] );

$classes = [get_post_type(), 'slider-slide'];

$time = '';
if( $args['show_time'] ) {
	if( $args['time_type'] == 'difference' ) {
		$time = sprintf( esc_html__( '%s ago', 'drplus' ), human_time_diff( get_the_date( "U" ), Utils::convert_chars( date_i18n( "U" ) ) ) );
	} else {
		$time = get_the_date();
	}
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( Utils::prepare_html_classes( $classes ) ); ?>>
	<a href="<?php echo get_permalink() ?>" title="<?php echo get_the_title() ?>">
		<?php drplus_post_thumbnail( null, null, false ) ?>

		<<?php echo tag_escape( $title_tag ) ?> class="post-title line-clamp line-clamp-2"><?php echo drplus_get_post_title() ?></<?php echo tag_escape( $title_tag ) ?>>

		<?php if( $args['show_time'] || $args['show_read_more'] ) { ?>
			<div class="post-footer">
				<?php if( $args['show_time'] ) { ?>
					<time datetime="<?php echo get_the_date( 'Y-m-d H:i:s' ) ?>" class="post-time"><?php echo $time ?></time>
				<?php } ?>
				<?php if( $args['show_read_more'] ) { ?>
					<div class="read-more-btn"><span class="read-more-btn-text"><?php echo esc_html( $args['read_more_text'] ) ?></span><?php echo Sanitizers::icon( $args['read_more_icon'], 'read-more-btn-icon' ) ?></div>
				<?php } ?>
			</div>
		<?php } ?>
	</a>
</article>