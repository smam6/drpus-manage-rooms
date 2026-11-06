<?php

use DrPlus\Utils;
use DrPlus\Utils\Product;

if( !defined( 'ABSPATH' ) ) exit;

global $product;
if( empty( $product ) || empty( $product_id = $product->get_id() ) ) return;

$subtitle = Product::get_subtitle( $product_id );

?>
<div class="product-subtitle-wrap">
	<?php if( !empty( $args['before_text'] ) ) { ?>
		<span class="product-subtitle_before-text"><?php echo esc_html( $args['before_text'] ) ?></span>
	<?php } ?>
	<span class="product-subtitle"><?php echo esc_html( $subtitle ) ?></span>
	<?php if( !empty( $args['before_text'] ) ) { ?>
		<span class="product-subtitle_after-text"><?php echo esc_html( $args['after_text'] ) ?></span>
	<?php } ?>
</div>