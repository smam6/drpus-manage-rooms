<?php

use DrPlus\Utils\UI;

global $product;

$aria_describedby = isset( $args['aria-describedby_text'] ) ? sprintf( 'aria-describedby="woocommerce_loop_add_to_cart_link_describedby_%s"', esc_attr( $product->get_id() ) ) : '';

$default_classes = ['button', 'button-secondary', 'small', 'add_to_cart_button'];
$default_classes = implode( " ", $default_classes );

$add_to_cart_html = '<span class="button-text">' . $product->add_to_cart_text() . '</span>';
$add_to_cart_html .= '<i class="button-icon add-to-cart-icon drplus-icon-bag-1" aria-hidden="true"></i>';
$add_to_cart_html .= UI::button_loading( false );
$add_to_cart_html .= '<i class="add-to-cart-tick drplus-icon-tick" aria-hidden="true"></i>';

echo apply_filters(
	'woocommerce_loop_add_to_cart_link', // WPCS: XSS ok.
	sprintf(
		'<a href="%s" %s data-quantity="%s" class="%s" %s>%s</a>',
		esc_url( $product->add_to_cart_url() ),
		$aria_describedby,
		esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
		esc_attr( isset( $args['class'] ) ? $args['class'] . " " . $default_classes : $default_classes ),
		isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
		$add_to_cart_html
	),
	$product,
	$args
);
?>
<?php if ( !empty( $args['aria-describedby_text'] ) ) : ?>
	<span id="woocommerce_loop_add_to_cart_link_describedby_<?php echo esc_attr( $product->get_id() ); ?>" class="screen-reader-text">
		<?php echo esc_html( $args['aria-describedby_text'] ); ?>
	</span>
<?php endif; ?>
