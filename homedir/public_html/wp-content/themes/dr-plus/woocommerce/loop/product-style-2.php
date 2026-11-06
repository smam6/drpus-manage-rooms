<?php

use DrPlus\Components\Button;
use DrPlus\Utils\WC;

global $product;

if ( empty( $product ) || ! $product instanceof \WC_Product ) {
	return;
}

$score = $product->get_reviews_allowed() ? $product->get_average_rating() : 0;
?>
<li <?php wc_product_class( '', $product ); ?>>
	<?php
	/**
	 * Hook: woocommerce_before_shop_loop_item.
	 *
	 * @hooked woocommerce_template_loop_product_link_open - 10
	 */

	do_action( 'woocommerce_before_shop_loop_item' );
	?>
	<?php if( $score ) { ?>
		<div class="product-score">
			<span class="product-score-avg"><?php echo $score ?></span>
			<i class="product-score-icon drplus-icon-star-fill" aria-hidden="true"></i>
		</div>
	<?php } ?>

	<div class="product-thumbnail-wrap">
		<?php echo woocommerce_get_product_thumbnail() ?>
	</div>
	<?php

	/**
	 * Hook: woocommerce_before_shop_loop_item_title.
	 *
	 * @hooked woocommerce_show_product_loop_sale_flash - 10
	 * @hooked woocommerce_template_loop_product_thumbnail - 10
	 */
	do_action( 'woocommerce_before_shop_loop_item_title' );

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

	?>
	<div class="product-data-box">
		<?php
		$badge = WC::get_product_badge( $product->get_id() );
		if( !empty( $badge ) ) {
			?>
			<div class="drplus-product-badge">
				<?php echo wp_get_attachment_image( $badge['image'], [24, 24] ) ?>
				<div class="drplus-product-badge-text"><?php echo esc_html( $badge['name'] ) ?></div>
			</div>
			<?php
		}
		?>

		<?php woocommerce_template_loop_price() ?>
	</div>
	</a>
	<div class="product-buttons">
		<?php
		Button::view( [
			'text'			=> esc_html__( "View details", 'drplus' ),
			'link'			=> apply_filters( 'woocommerce_loop_product_link', get_the_permalink(), $product ),
			'small'			=> true,
			'fullwidth'		=> true,
			'transparent'	=> true,
			'classes'		=> ['product-link'],
			'icon'			=> is_rtl() ? 'drplus-icon-arrow-left' : 'drplus-icon-arrow-right',
			'icon_align'	=> 'end',
		] );
		/**
		 * Hook: woocommerce_after_shop_loop_item.
		 *
		 * @hooked woocommerce_template_loop_product_link_close - 5
		 * @hooked woocommerce_template_loop_add_to_cart - 10
		 */
		do_action( 'woocommerce_after_shop_loop_item' );
		?>
	</div>
</li>
