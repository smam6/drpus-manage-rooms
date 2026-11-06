<?php

use DrPlus\Utils;
use DrPlus\Utils\Options;
use DrPlus\Utils\Sanitizers;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'comment_text'	=> esc_html__( 'Comment', 'drplus' ),
	'show_icon'		=> true,
	'review_icon'	=> 'drplus-icon-chat',
], ['review_icon'] );

if( post_type_supports( 'product', 'comments' ) && wc_review_ratings_enabled() ) {
	$options = Options::get_options( [
		'wc-single-show-comments'	=> true,
	] );
	if( !Utils::to_bool( $options['wc-single-show-comments'] ) ) return;
	
	global $product;
	if( empty( $product ) ) return;
	$review_count = $product->get_review_count();
	?>
	<div class="product-head-meta product-head-comments">
		<a href="#reviews" class="post-meta-value">
			<?php if( $args['show_icon'] && !empty( $args['review_icon'] ) ) {
				echo Sanitizers::icon( $args['review_icon'], 'product-head-comments-icon' );
			} ?>
			<span class="product-head-comments-label"><?php echo esc_html( $args['comment_text'] ) ?></span>
			<span class="product-head-meta-value product-head-review-value"><?php echo $review_count ?></span>
		</a>
	</div>
	<?php
}