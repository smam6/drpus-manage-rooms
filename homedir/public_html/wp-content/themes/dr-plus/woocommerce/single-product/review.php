<?php
/**
 * Review Comments Template
 *
 * Closing li is left out on purpose!.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/review.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">

	<div id="comment-<?php comment_ID(); ?>" class="comment_container comment-body">

		<div class="comment-header-info comment-author vcard">
			<div class="comment-author-avatar">
				<?php
				/**
				 * The woocommerce_review_before hook
				 *
				 * @hooked woocommerce_review_display_gravatar - 10
				 */
				do_action( 'woocommerce_review_before', $comment );
				?>
			</div>
			<div class="comment-author-name">
				<?php
				/**
				 * The woocommerce_review_before_comment_meta hook.
				 *
				 * @hooked woocommerce_review_display_rating - 10 (removed by theme)
				 */
				do_action( 'woocommerce_review_before_comment_meta', $comment );
				?>
				<?php
				/**
				 * The woocommerce_review_meta hook.
				 *
				 * @hooked woocommerce_review_display_meta - 10
				 */
				do_action( 'woocommerce_review_meta', $comment );
				?>
			</div>
			<?php
			/**
			 * The drplus/wc/comments/comment_info hook.
			 *
			 * @hooked drplus_wc_comment_info - 10
			 */
			do_action( 'drplus/wc/comments/comment_info', $comment );
			?>
			<div class="reply">
				<?php
				/**
				 * The drplus_wc_comment_stars hook
				 * 
				 * @hooked woocommerce_review_display_rating - 10
				 */
				do_action( 'drplus_wc_comment_stars', $comment );
				/**
				 * The comment_reply_link_args hook
				 *
				 */
				comment_reply_link( array_merge( $args, array(
					'add_below' => 'div-comment',
					'depth'     => $depth,
					'max_depth' => $args['max_depth'],
				) ) );
				?>
			</div>
		</div>

		<?php
		do_action( 'woocommerce_review_before_comment_text', $comment );
		?>
		<div class="comment-text-wrap">
			<?php
			/**
			 * The woocommerce_review_comment_text hook
			 *
			 * @hooked woocommerce_review_display_comment_text - 10
			 */
			do_action( 'woocommerce_review_comment_text', $comment );
			?>
		</div>
		
		<?php
		do_action( 'woocommerce_review_after_comment_text', $comment );
		?>
	</div>
