<?php

use DrPlus\Utils\Options;
use Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils;

remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_button_view_cart', 10 );
remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_proceed_to_checkout', 20 );

if( !function_exists( "drplus_wc_mini_cart_checkout_btn" ) ) {
	function drplus_wc_mini_cart_checkout_btn() {
		$options = Options::get_options( [
			'wc_checkout_text'		=> __( 'Submit order', 'drplus' ),
			'wc_view_cart_text'		=> __( 'View cart', 'woocommerce' ),
			'mini-cart-style'		=> 'style_1',
		] );

		$wp_button_class = wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '';

		if( CartCheckoutUtils::has_cart_page() && $options['mini-cart-style'] == 'style_2' ) {
			echo '<a href="' . esc_url( wc_get_cart_url() ) . '" class="button fullwidth view_cart small wc-forward' . esc_attr( $wp_button_class ) . '">' . $options['wc_view_cart_text'] . '</a>';
		}

		if( $options['mini-cart-style'] == 'style_2' ) {
			$wp_button_class .= ' button-secondary';
		}

		echo '<a href="' . esc_url( wc_get_checkout_url() ) . '" class="button fullwidth checkout small wc-forward' . esc_attr( $wp_button_class ) . '">' . esc_html( $options['wc_checkout_text'] ) . '</a>';
	}
}
add_action( 'woocommerce_widget_shopping_cart_buttons', 'drplus_wc_mini_cart_checkout_btn' );

if( !function_exists( 'drplus_woocommerce_widget_cart_item_quantity' ) ) {
	function drplus_woocommerce_widget_cart_item_quantity( $quantity ) {
		return "";
	}
}
add_filter( 'woocommerce_widget_cart_item_quantity', 'drplus_woocommerce_widget_cart_item_quantity', 10, 1 );

if( !function_exists( 'drplus_woocommerce_widget_cart_item_footer' ) ) {
	function drplus_woocommerce_widget_cart_item_footer( $cart_item, $product_price, $cart_item_key, $_product ) {
		?>
		<div class="drplus_mini-cart-item-quantity-wrap">
			<div class="drplus_mini-cart-item-quantity" data-nonce="<?php echo wp_create_nonce( "update_mini_cart-{$cart_item_key}" ) ?>" data-key="<?php echo esc_attr( $cart_item_key ) ?>">
				<?php
				if ( $_product->is_sold_individually() ) {
					$min_quantity = 1;
					$max_quantity = 1;
				} else {
					$min_quantity = 0;
					$max_quantity = $_product->get_max_purchase_quantity();
				}
				$product_quantity = woocommerce_quantity_input(
					array(
						'input_name'   => "cart[{$cart_item_key}][qty]",
						'input_value'  => $cart_item['quantity'],
						'max_value'    => $max_quantity,
						'min_value'    => $min_quantity,
						'product_name' => $_product->get_name(),
					),
					$_product,
					false
				);
				echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // PHPCS: XSS ok.
				wp_nonce_field( "update_mini_cart-{$cart_item_key}", "update_mini_cart-{$cart_item_key}_value" );
				?>
			</div>
			<div class="drplus_mini-cart-item-price product-price-wrapper">
				<div class="product-simple-price price simple_price">
					<?php echo $product_price ?>
				</div>
			</div>
		</div>
		<?php
	}
}
add_action( 'drplus_after_mini_cart_item_content', 'drplus_woocommerce_widget_cart_item_footer', 10, 4 );