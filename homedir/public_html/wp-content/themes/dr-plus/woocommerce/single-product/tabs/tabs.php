<?php
/**
 * Single Product tabs
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/tabs/tabs.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.8.0
 */

use DrPlus\Utils\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filter tabs and allow third parties to add their own.
 *
 * Each tab is an array containing title, callback and priority.
 *
 * @see woocommerce_default_product_tabs()
 */
$product_tabs = apply_filters( 'woocommerce_product_tabs', array() );
global $product;

if ( ! empty( $product_tabs ) ) : ?>

	<div id="product-tabs" class="woocommerce-tabs content-area">
		<ul class="product-section product-tabs-head" role="tablist">
			<?php foreach ( $product_tabs as $key => $product_tab ) : ?>
				<li role="presentation" class="<?php echo esc_attr( $key ); ?>_tab product-tab-title<?php echo $key == 'description' ? ' active' : ''; ?>" id="tab-title-<?php echo esc_attr( $key ); ?>">
					<a href="<?php echo "#product-" . esc_attr( $key ); ?>" role="tab" aria-controls="<?php echo "product-" . esc_attr( $key ); ?>">
						<?php echo wp_kses_post( apply_filters( 'woocommerce_product_' . $key . '_tab_title', $product_tab['title'], $key ) ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<div class="product-tabs-content">
			<div class="product-tab-content">
				<?php foreach ( $product_tabs as $key => $product_tab ) : ?>
					<?php if( $key == 'reviews' ) continue; ?>
					<div class="product-section woocommerce-Tabs-panel woocommerce-Tabs-panel--<?php echo esc_attr( $key ); ?> panel entry-content product-tab-content" id="product-<?php echo esc_attr( $key ); ?>" role="tabpanel" aria-labelledby="tab-title-<?php echo esc_attr( $key ); ?>">
						<?php
						get_template_part( "templates/components/template-components-section_title", null, [
							'icon'			=> $product_tab['icon'] ?? "drplus-icon-diamond",
							'title'			=> esc_html( $product_tab['title'] ),
							'classes'		=> ['product-tab-content-title'],
						] );
						if ( isset( $product_tab['callback'] ) ) {
							call_user_func( $product_tab['callback'], $key, $product_tab );
						}
						if( $key == 'description' ) {
							do_action( 'drplus_product_after_description_tab' );
						}
						?>
					</div>
				<?php endforeach; ?>
				<?php do_action( 'woocommerce_product_after_tabs' ); ?>
			</div>
			<div class="products" id="product-mini-wrap">
				<div id="product-mini" class="product">
					<div class="product-inner woocommerce-loop-product__link">
						<div id="product-mini-image" class="product-thumbnail-wrap"><?php echo $product->get_image() ?></div>

						<div id="product-mini-texts">
							<h2 id="product-mini-title" class="woocommerce-loop-product__title line-clamp line-clamp-2"><?php echo $product->get_title() ?></h2>
							<p class="product-subtitle"><?php echo Product::get_subtitle( get_the_ID() ) ?></p>
						</div>

						<div class="product-mini-bottom">
							<?php echo woocommerce_template_single_price() ?>
						</div>
						
						<?php get_template_part( 'templates/components/template-components-button', null, [
							'type'		=> 'primary',
							'text'		=> esc_html__( 'Add to cart', 'drplus' ),
							'small'		=> true,
							'fullwidth'	=> true,
							'id'		=> 'product-mini-add-to-cart',
							'link'		=> [
								'url'	=> "#product-head-secondary"
							]
						] ) ?>
					</div>
				</div>
			</div>
		</div>
		<?php if( array_key_exists( 'reviews', $product_tabs ) ) {
			drplus_wc_single_comments();
		} ?>
	</div>

<?php endif; ?>