<?php
namespace DrPlus\Shortcodes;

use DrPlus\Utils;
use DrPlus\Utils\Options;

if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( "\Drplus\Shortcodes\PostViews" ) ) {
	class PostViews {
		public static function view( $atts = [], $content = null, $tag = '' ) {
			$atts = array_change_key_case( (array)$atts, CASE_LOWER );
			$atts = shortcode_atts(
				[
					'post_id'	=> 0,
					'icon'		=> 'drplus-icon-eye',
					'before'	=> '',
					'after'		=> esc_html_x( "View", 'Post views', 'drplus' ),
					'wrap'		=> true,
				], $atts, $tag
			);

			// Sanitize
			$post_id = Utils::convert_chars( $atts['post_id'], true, 'absint' );
			if( $post_id === 0 ) {
				$post_id = get_the_ID();
			}
			$icon_classes = '';
			$icon_url = '';
			if( !empty( $atts['icon'] ) ) {
				if( filter_var( $atts['icon'], FILTER_VALIDATE_URL ) ) {
					$icon_url = esc_url( $atts['icon'], ['http', 'https'] );
				} else { // Icon class
					$icon_classes = explode( " ", Utils::convert_chars( $atts['icon'] ) );
					$icon_classes = implode( " ", array_filter( array_map( fn( $value ) => sanitize_html_class( $value ), $icon_classes ) ) );
				}
			}
			$before = wp_kses_post( $atts['before'] );
			$after = wp_kses_post( $atts['after'] );
			$wrap = Utils::to_bool( $atts['wrap'] );

			$views = drplus_get_post_views( $post_id );
			$options = Options::get_options( [
				'post_views_status'	=> true,
				'min_post_views'	=> 0,
			] );
			if( !$options['post_views_status'] || $views < $options['min_post_views'] ) {
				return '';
			}

			ob_start();
			?>
			<?php if( $wrap ) { ?>
				<div class="post-views">
			<?php } ?>
				<?php if( !empty( $icon_classes ) ) { ?>
					<i class="<?php echo esc_attr( $icon_classes ) ?> post-views-icon"></i>
				<?php } else if( $icon_url ) { ?>
					<img src="<?php echo $icon_url ?>" alt="" class="post-views-icon">
				<?php } ?>
				<div class="post-views-texts">
					<?php if( !empty( $before ) ) { ?>
						<span class="post-views-before"><?php echo $before ?></span>
					<?php } ?>
					<span class="post-views-count"><?php echo esc_html( $views ) ?></span>
					<?php if( !empty( $after ) ) { ?>
						<span class="post-views-after"><?php echo $after ?></span>
					<?php } ?>
				</div>
			<?php if( $wrap ) { ?>
				</div>
			<?php } ?>
			<?php
			return ob_get_clean();
		}
	}
	add_shortcode( 'drplus_post_views', [PostViews::class, 'view'] );
}