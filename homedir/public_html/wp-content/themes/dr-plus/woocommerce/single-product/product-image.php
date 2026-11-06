<?php
/**
 * Single Product Image
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-image.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.5.0
 */

use Automattic\WooCommerce\Enums\ProductType;
use DrPlus\Utils\Options;
use DrPlus\Utils\Product;
use DrPlus\Utils\WC;

defined( 'ABSPATH' ) || exit;

// Note: `wc_get_gallery_image_html` was added in WC 3.3.2 and did not exist prior. This check protects against theme overrides being used on older versions of WC.
if ( ! function_exists( 'wc_get_gallery_image_html' ) ) {
	return;
}

$options = Options::get_options( [
	'wc-single-gallery-thumbnail-position'	=> 'end',
] );

global $product;

$columns           = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );
$post_thumbnail_id = $product->get_image_id();
$wrapper_classes   = apply_filters(
	'woocommerce_single_product_image_gallery_classes',
	array(
		'woocommerce-product-gallery',
		'woocommerce-product-gallery--' . ( $post_thumbnail_id ? 'with-images' : 'without-images' ),
		'woocommerce-product-gallery--columns-' . absint( $columns ),
		'images',
	)
);
$gallery_ids = Product::get_gallery_ids( $product );
?>
<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>" data-columns="<?php echo esc_attr( $columns ); ?>" style="opacity: 0; transition: opacity .25s ease-in-out;">
	<div class="woocommerce-product-gallery__wrapper product-thumb-slider-<?php echo esc_attr( $options['wc-single-gallery-thumbnail-position'] ) ?>">
		<div class="product-thumb-slider product-slider swiper">
			<div class="swiper-wrapper">
				<?php
				do_action( 'woocommerce_product_thumbnails' );
				?>
			</div>
		</div>

		<div class="product-main-slider product-slider swiper">
			<?php
			$badge = WC::get_product_badge( $product->get_id() );
			if( !empty( $badge ) ) {
				?>
				<div class="drplus-product-badge drplus-popover-wrap drplus-popover-start">
					<?php echo wp_get_attachment_image( $badge['image'], [24, 24] ) ?>
					<div class="drplus-popover"><?php echo esc_html( $badge['name'] ) ?></div>
				</div>
				<?php
			}
			?>
			<div class="swiper-wrapper">
				<?php
				if ( $gallery_ids ) {
					foreach( $gallery_ids as $img_id ) {
						echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', Product::slider_image_html( $img_id, true ), $img_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
					}
				} else {
					// Check for visible children with prices to determine if variation image swapping is possible.
					// Using get_visible_children() + get_price() is more efficient than get_available_variations()
					// as it uses cached IDs and synced price data rather than loading all variation objects.
					$wrapper_classname = $product->is_type( ProductType::VARIABLE ) && ! empty( $product->get_visible_children() ) && '' !== $product->get_price() ?
						'woocommerce-product-gallery__image woocommerce-product-gallery__image--placeholder' :
						'woocommerce-product-gallery__image--placeholder';
					$html              = sprintf( '<div class="%s">', esc_attr( $wrapper_classname ) );
					$html             .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'woocommerce' ) );
					$html             .= '</div>';
					echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, $post_thumbnail_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
				}
				?>
			</div>
			<?php
			get_template_part( 'templates/components/template-components-slider_arrows', null, [
				'inline'	=> true,
				'classes'	=> ['drplus-slider-nav-btn', 'drplus-product-image-arrow'],
			] );
			?>
		</div>

		<?php if ( $gallery_ids ) { ?>
			<div class="product-slider-popup-overlay"></div>
			<div class="product-slider-popup">
				<div class="product-slider-popup-main-slider product-slider product-main-slider swiper">
					<?php get_template_part( 'templates/components/template-components-slider_arrows' ); ?>
					<div class="swiper-wrapper">
						<?php
						foreach( $gallery_ids as $img_id ) {
							echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', Product::slider_image_html( $img_id, true ), $img_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
						}
						?>
					</div>
				</div>

				<div class="product-slider-popup-footer">
					<div class="product-slider-popup-thumb-slider product-slider product-thumb-slider swiper">
						<div class="swiper-wrapper">
							<?php
							foreach( $gallery_ids as $img_id ) {
								echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', Product::slider_image_html( $img_id ), $img_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
							}
							?>
						</div>
					</div>
				</div>
			</div>
		<?php } ?>
	</div>
</div>
