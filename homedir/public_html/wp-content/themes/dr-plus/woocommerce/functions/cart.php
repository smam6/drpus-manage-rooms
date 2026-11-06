<?php
use DrPlus\Utils;
use DrPlus\Utils\Options;

if( !function_exists( "drplus_get_cart_item_regular_price" ) ) {
	function drplus_get_cart_item_regular_price( $product ) {
		$price = $product->get_regular_price();
		if ( WC()->cart->display_prices_including_tax() ) {
			$product_price = wc_get_price_including_tax( $product, [
				'price'	=> $price
			] );
		} else {
			$product_price = wc_get_price_excluding_tax( $product, [
				'price'	=> $price
			] );
		}
		return apply_filters( 'woocommerce_cart_product_price', wc_price( $product_price ), $product );
	}
}

if( !function_exists( 'drplus_wc_add_to_cart_fragments' ) ) {
	function drplus_wc_add_to_cart_fragments( $fragments ) {
		ob_start();
		?>
		<div class="header-mini-cart-content">
			<?php woocommerce_mini_cart() ?>
		</div>
		<?php
		$fragments['.header-mini-cart-content'] = ob_get_clean();

		$fragments['.cart-count'] = '<span class="cart-count">' . Utils::get_cart_count() . '</span>';

		return $fragments;
	}
}
add_filter( 'woocommerce_add_to_cart_fragments', 'drplus_wc_add_to_cart_fragments' );

remove_action( 'woocommerce_cart_is_empty', 'wc_empty_cart_message', 10 ); // Remove default WooCommerce message
if( !function_exists( "drplus_wc_empty_cart_message" ) ) {
	function drplus_wc_empty_cart_message() {
		$text = Options::get_options( [
			'wc_empty_cart_text'	=> __( 'Your cart is empty!', 'drplus' ),
		] )['wc_empty_cart_text'];

		?>
		<div class="empty-page">
			<i class="empty-page-icon empty-orders-icon drplus-icon-bag"></i>
			<p class="empty-page-text empty-orders-text"><?php echo esc_html( $text ) ?></p>
		</div>
		<?php
	}
}
add_action( 'woocommerce_cart_is_empty', 'drplus_wc_empty_cart_message' );