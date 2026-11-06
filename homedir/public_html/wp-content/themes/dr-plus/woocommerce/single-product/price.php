<?php
/**
 * Single Product Price
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/price.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.0.0
 */

use DrPlus\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;
$wrap_classes = ['price'];

if( $product->get_type() === 'variable' ) {
	// return;
	$prices = $product->get_variation_prices( true );
	$wrap_classes[] = 'variation_price';
	if ( empty( $prices['price'] ) ) {
		$price = apply_filters( 'woocommerce_variable_empty_price_html', '', $product );
	} else {
		$min_price     = current( $prices['price'] );
		$max_price     = end( $prices['price'] );
		$min_reg_price = current( $prices['regular_price'] );
		$max_reg_price = end( $prices['regular_price'] );

		if ( $min_price !== $max_price ) {
			$price = wc_format_price_range( $min_price, $max_price );
			$wrap_classes[] = 'variation_price-range';
		} elseif ( $product->is_on_sale() && $min_reg_price === $max_reg_price ) {
			$price = wc_format_sale_price( wc_price( $max_reg_price ), wc_price( $min_price ) );
			$price .= '<span class="price-discount-percentage">%' . (100 - round( $min_price / $max_reg_price * 100 )) . '</span>';
			$wrap_classes[] = 'variation_price-discount';
		} else {
			$price = wc_price( $min_price );
		}

		$price = apply_filters( 'woocommerce_variable_price_html', $price . $product->get_price_suffix(), $product );
	}
} else {
	$wrap_classes[] = 'simple_price';
	if( '' === $product->get_price() ) {
		$price = apply_filters( 'woocommerce_empty_price_html', '', $product );
	} else if( $product->is_on_sale() ) {
		$reg_price = $product->get_regular_price();
		$sale_price = $product->get_sale_price();
		$price = wc_format_sale_price( wc_get_price_to_display( $product, array( 'price' => $reg_price ) ), wc_get_price_to_display( $product ) ) . $product->get_price_suffix();
		if( is_numeric( $sale_price ) && is_numeric( $reg_price ) ) {
			$price .= '<span class="price-discount-percentage">%' . Utils::calc_product_discount_percentage( $reg_price, $sale_price ) . '</span>';
		}
	} else {
		$price = wc_price( wc_get_price_to_display( $product ) ) . $product->get_price_suffix();
	}
}
$price = apply_filters( 'woocommerce_get_price_html', $price, $product );
if( $product->get_stock_status() === 'outofstock' || $price === '' ) {
	echo wc_get_stock_html( $product );
} else {
	?>
	<div class="product-head-info product-price-wrapper">
		<div class="product-head-info-value product-<?php echo esc_attr( $product->get_type() ) ?>-price <?php echo esc_attr( apply_filters( 'woocommerce_product_price_class', Utils::prepare_html_classes( $wrap_classes ) ) ); ?>">
			<?php echo apply_filters( 'woocommerce_get_price_html', $price, $product ) ?>
		</div>
	</div>
	<?php
}