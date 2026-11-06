<?php
/**
 * Related Products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/related.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     10.3.0
 */

use DrPlus\Components\SectionTitle;
use DrPlus\Utils;
use DrPlus\Utils\Elementor;
use DrPlus\Utils\Options;
use DrPlus\Utils\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$options = Options::get_options( [
	'wc-single-end-products-show'		=> true,
	'wc-single-end-products-title_tag'	=> 'h3',
	'wc-single-end-products-ppp'		=> 8,
	'wc-single-end-products-type'		=> 'related',
] );
if( !Utils::to_bool( $options['wc-single-end-products-show'] ) ) return;
if( $options['wc-single-end-products-type'] == 'latests' ) {
	$related_products = wc_get_products( [
		'limit'		=> $options['wc-single-end-products-ppp'],
		'orderby'	=> 'date',
    	'order'		=> 'DESC',
	] );
}

if ( $related_products ) {
	/**
	 * Ensure all images of related products are lazy loaded by increasing the
	 * current media count to WordPress's lazy loading threshold if needed.
	 * Because wp_increase_content_media_count() is a private function, we
	 * check for its existence before use.
	 */
	if ( function_exists( 'wp_increase_content_media_count' ) ) {
		$content_media_count = wp_increase_content_media_count( 0 );
		if ( $content_media_count < wp_omit_loading_attr_threshold() ) {
			wp_increase_content_media_count( wp_omit_loading_attr_threshold() - $content_media_count );
		}
	}
	$props = [
		'default_wc_products_style'	=> 'style-1',
		'desktop_slider'			=> true,
		'desktop_slides_type'		=> 'count',
		'desktop_slides'			=> 4,
		'desktop_slides_space'		=> 24,
		'tablet_slider'				=> true,
		'tablet_slides_type'		=> 'auto',
		'tablet_slides_space'		=> 24,
		'mobile_slider'				=> true,
		'mobile_slides_type'		=> 'auto',
		'mobile_slides_space'		=> 24,
	];
	$props = Utils::check_default( $props, Product::get_default_props() );

	$display_attributes = Elementor::get_display_attributes( $props );
	$attributes = [
		'class'			=> [
			'drplus-slider-wrap',
			'drplus-products-slider',
			'related_inner',
			'products-' . $options['default_wc_products_style'],
		],
		'data-settings'	=> $display_attributes['args'],
		'style'			=> $display_attributes['style'],
	];
	$attributes['class'] = array_merge( $attributes['class'], $display_attributes['wrap_classes'] );
?>

	<section class="related">
		<div <?php echo Utils::get_html_attributes( $attributes ) ?>>

			<?php
			$heading = apply_filters( 'woocommerce_product_related_products_heading', __( 'Related products', 'woocommerce' ) );

			$shop_page = get_permalink( wc_get_page_id( 'shop' ) );
			if ( $heading ) {
				echo '<div class="drplus-slider-head">';
					SectionTitle::view( [
						'icon'			=> DRPLUS_URI . "/assets/images/diamond.svg",
						'icon_has_bg'	=> false,
						'title'			=> esc_html( $heading ),
						'nav_btns'		=> true,
						'classes'		=> ['related-product-title'],
						'tag'			=> $options['wc-single-end-products-title_tag'],
					] );
				echo '</div>';
			}
			?>
			
			<?php
			$props['remove_swiper_div'] = true;
			wc_set_loop_prop( 'drplus_loop_props', $props );
			woocommerce_product_loop_start();
			?>

				<?php foreach ( $related_products as $related_product ) : ?>

						<?php
						$post_object = get_post( $related_product->get_id() );

						setup_postdata( $GLOBALS['post'] = $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found

						wc_get_template_part( 'content', 'product' );
						?>

				<?php endforeach; ?>

			<?php woocommerce_product_loop_end(); ?>

			<?php
			get_template_part( "templates/components/button", null, [
				'type'		=> 'action',
				'small'		=> true,
				'text'		=> esc_html__( "View all", 'drplus' ),
				'link'		=> $shop_page,
				'align'		=> 'center',
				'id'		=> 'related-products-view-mobile',
				'classes'	=> ['hide-desktop-1024'],
			] );
			?>
		</div>
	</section>
	<?php
}

wp_reset_postdata();
