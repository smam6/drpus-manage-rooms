<?php
use DrPlus\Utils\WC;

global $product;

if ( empty( $product ) || ! $product instanceof \WC_Product ) {
	return;
}
?>
<li <?php wc_product_class( '', $product ); ?>>
	<?php
	/**
	 * Hook: woocommerce_before_shop_loop_item.
	 *
	 * @hooked woocommerce_template_loop_product_link_open - 10
	 */

	do_action( 'woocommerce_before_shop_loop_item' );

	/**
	 * Hook: woocommerce_before_shop_loop_item_title.
	 *
	 * @hooked woocommerce_show_product_loop_sale_flash - 10
	 * @hooked woocommerce_template_loop_product_thumbnail - 10
	 */
	do_action( 'woocommerce_before_shop_loop_item_title' );
	
	?>
	<div class="product-thumbnail-wrap">
		<?php echo woocommerce_get_product_thumbnail() ?>
	</div>
	<?php

	/**
	 * Hook: woocommerce_shop_loop_item_title.
	 *
	 * @hooked woocommerce_template_loop_product_title - 10
	 */
	do_action( 'woocommerce_shop_loop_item_title' );

	/**
	 * Hook: woocommerce_after_shop_loop_item_title.
	 *
	 * @hooked woocommerce_template_loop_rating - 5
	 * @hooked woocommerce_template_loop_price - 10
	 */
	do_action( 'woocommerce_after_shop_loop_item_title' );
	$category_ids = (array) $product->get_category_ids();
	if ( ! empty( $category_ids ) ) {
		$category = get_term( $category_ids[0], 'product_cat' );
		if ( $category && ! is_wp_error( $category ) ) {
			echo '<div class="product-category">' . esc_html( $category->name ) . '</div>';
		}
	}
	woocommerce_template_loop_price();

	/**
	 * Hook: woocommerce_after_shop_loop_item.
	 *
	 * @hooked woocommerce_template_loop_product_link_close - 5
	 * @hooked woocommerce_template_loop_add_to_cart - 10
	 */
	do_action( 'woocommerce_after_shop_loop_item' );
	$badge = WC::get_product_badge( $product->get_id() );
	if( !empty( $badge ) ) {
		?>
		<div class="drplus-product-badge drplus-popover-wrap drplus-popover-start">
			<?php echo wp_get_attachment_image( $badge['image'], [24, 24] ) ?>
			<div class="drplus-product-badge-text drplus-popover"><?php echo esc_html( $badge['name'] ) ?></div>
		</div>
		<?php
	}
	?>
</li>
