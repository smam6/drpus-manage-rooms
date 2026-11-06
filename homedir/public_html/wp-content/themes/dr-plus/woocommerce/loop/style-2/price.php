<?php

use DrPlus\Utils;
use Automattic\WooCommerce\Enums\ProductType;

global $product;

if( $product->get_stock_status() !== 'outofstock' && ( $product->is_type( ProductType::VARIABLE ) || $product->get_regular_price() !== "" ) ) {
	if( $product->is_type( ProductType::VARIABLE ) ) {
		echo '<div class="price-wrap"><div class="price">' . $product->get_price_html() . '</div></div>';
	} else {
		$regular_price = wc_get_price_to_display( $product, [
			'price'	=> $product->get_regular_price()
		] );
		if( $product->is_on_sale() ) {
			$sale_price = $product->get_sale_price();
			$sale_price = wc_get_price_to_display( $product, ['price' => $sale_price] );
			$sale_price_formatted = wc_format_sale_price( $regular_price, $sale_price );
			?>
			<div class="price-discount-percentage">%<?php echo Utils::calc_product_discount_percentage( $regular_price, $sale_price ) ?></div>
			<?php
		}
		?>
		<div class="price-wrap">
			<?php
			if( $product->is_on_sale() ) {
				?>
				<div class="sale-price-wrap">
					<span class="sale-price price"><?php echo $sale_price_formatted ?></span>
				</div>
				<?php
			} else {
				echo '<span class="regular-price price">' . wc_price( $product->get_price() ) . '</span>';
			}
			?>
		</div>
		<?php
	}
} else {
	echo wc_get_stock_html( $product );
}