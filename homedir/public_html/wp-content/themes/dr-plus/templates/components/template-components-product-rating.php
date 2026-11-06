<?php

use DrPlus\Utils;
use DrPlus\Utils\Options;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( [
	'show_number'	=> true,
], $args );

if( post_type_supports( 'product', 'comments' ) && wc_review_ratings_enabled() ) {
	$options = Options::get_options( [
		'wc-single-show-stars'		=> true,
	] );
	if( !Utils::to_bool( $options['wc-single-show-stars'] ) ) return;

	global $product;
	if( empty( $product ) ) return;
	$average = absint( $product->get_average_rating() );
	?>
	<div class="product-head-meta product-head-rating">
		<div class="product-head-stars">
			<?php for( $index = 1; $index <= 5; $index++ ) { ?>
				<i class="drplus-icon-star<?php echo $average !== 0 && $index <= $average ? '-fill active' : '' ?>"></i>
			<?php } ?>
		</div>
		<?php if( $args['show_number'] ) { ?>
			<span class="product-head-meta-value product-head-rating-value"><?php echo $average ?></span>
		<?php } ?>
	</div>
	<?php
}