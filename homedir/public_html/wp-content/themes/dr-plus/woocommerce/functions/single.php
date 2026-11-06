<?php

use DrPlus\Utils;
use DrPlus\Utils\Options;
use DrPlus\Utils\Product;
use DrPlus\Utils\Sanitizers;
use DrPlus\Utils\UI;

remove_action( 'woocommerce_review_before_comment_meta', 'woocommerce_review_display_rating', 10 );
add_action( 'drplus_wc_comment_stars', 'woocommerce_review_display_rating', 10 );

if( !function_exists( "drplus_wc_star_rating_html" ) ) {
	function drplus_wc_star_rating_html( $html, $rating, $count ) {
		$count = $count === 0 || $count > 5 ? 5 : $count;
		return UI::stars( absint( $rating ), $count, false, '', false );
	}
}
add_filter( 'woocommerce_get_star_rating_html', 'drplus_wc_star_rating_html', 10, 3 );

remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );

if( !function_exists( "drplus_wc_single_after_title_start" ) ) {
	function drplus_wc_single_after_title_start() {
		$options = Options::get_options( [
			'wc-single-show-stars'		=> true,
			'wc-single-show-comments'	=> true,
		] );
		if( Utils::to_bool( $options['wc-single-show-stars'] ) || Utils::to_bool( $options['wc-single-show-comments'] ) ) {
			echo '<div id="product-head-after-title">';
		}
	}
}
add_action( 'woocommerce_single_product_summary', 'drplus_wc_single_after_title_start', 6 );

if( !function_exists( "drplus_wc_single_head_rating" ) ) {
	function drplus_wc_single_head_rating() {
		get_template_part( "templates/components/template-components-product-rating", null, [
			'show_number'	=> true,
		] );
	}
}
add_action( 'woocommerce_single_product_summary', 'drplus_wc_single_head_rating', 7 );

if( !function_exists( 'drplus_wc_review_gravatar_size' ) ) {
	function drplus_wc_review_gravatar_size( $size ) {
		return 75;
	}
}
add_filter( 'woocommerce_review_gravatar_size', 'drplus_wc_review_gravatar_size' );

remove_action( 'woocommerce_review_before_comment_meta', 'woocommerce_review_display_rating', 10 );

if( !function_exists( "drplus_wc_single_head_comments" ) ) {
	function drplus_wc_single_head_comments() {
		get_template_part( "templates/components/template-components-product-reviews-count" );
	}
}
add_action( 'woocommerce_single_product_summary', 'drplus_wc_single_head_comments', 8 );

if( !function_exists( "drplus_wc_single_head_add_to_wishlist" ) ) {
	function drplus_wc_single_head_add_to_wishlist() {
		$option = Options::get_options( [
			'wishlist'	=> true,
		] );
		if( !Utils::to_bool( $option['wishlist'] ) ) return;
		global $product;
		UI::product_wishlist( $product->get_id(), [
			'additional_classes'	=> ['product-head-meta'],
			'label'					=> '',
		] );
	}
}
add_action( 'drplus_after_product_title', 'drplus_wc_single_head_add_to_wishlist' );

if( !function_exists( "drplus_wc_single_after_title_end" ) ) {
	function drplus_wc_single_after_title_end() {
		$options = Options::get_options( [
			'wc-single-show-stars'		=> true,
			'wc-single-show-comments'	=> true,
		] );
		if( Utils::to_bool( $options['wc-single-show-stars'] ) || Utils::to_bool( $options['wc-single-show-comments'] ) ) {
			echo "</div>"; // product-head-after-title
		}
	}
}
add_action( 'woocommerce_single_product_summary', 'drplus_wc_single_after_title_end', 19 );

if( !function_exists( "drplus_wc_single_feature_attrs" ) ) {
	function drplus_wc_single_feature_attrs( $args = [], bool $force_show = false ) {
		if( !is_array( $args ) ) $args = [];

		if( !$force_show ) {
			$options = Options::get_options( [
				'wc-single-show-featured-attrs'	=> true,
			] );
			if( !Utils::to_bool( $options['wc-single-show-featured-attrs'] ) ) return;
		}

		$args = Utils::check_default( $args, [
			'title'							=> esc_html__( 'Product main features:', 'drplus' ),
			'show_icon'						=> true,
			'icon'							=> 'drplus-icon-tick',
		], ['icon'] );
		global $product;
		if( empty( $product ) ) return;
		$featured_attrs = Product::get_featured_attributes( $product->get_id() );
		if( !empty( $featured_attrs ) ) {
			$product_attrs = $product->get_attributes();
			$product_attrs = [];
			foreach( $product->get_attributes() as $attr ) {
				$product_attrs[$attr->get_name()] = $attr;
			}
			?>
			<div class="product-featured-attributes-wrap">
				<?php if( !empty( $args['title'] ) ) { ?>
					<div class="product-featured-attributes-label"><?php echo esc_html( $args['title'] ) ?></div>
				<?php } ?>
				<div class="product-featured-attributes">
					<?php
					foreach( $featured_attrs as $index => $attr_name ) {
						$attr = $product_attrs[$attr_name];
						$label = $attr_name;
						$option = $attr->get_options()[0];
						if( $attr->is_taxonomy() ) {
							$label = wc_attribute_label( $attr_name );
							$option = get_term( $option, $attr_name );
							if( !is_wp_error( $option ) ) {
								$option = $option->name;
							} else {
								continue;
							}
						}
						if( $index % 2 === 0 ) {
							?>
							<div class="product-featured-attribute-row">
						<?php } ?>
							<div class="product-featured-attribute">
								<?php if( $args['show_icon'] && !empty( $args['icon'] ) ) {
									echo Sanitizers::icon( $args['icon'] );
								} ?>
								<span class="product-featured-attribute-label"><?php echo esc_html( $label ) ?>:</span>
								<div class="product-featured-attribute-option"><?php echo esc_html( $option ) ?></div>
							</div>
						<?php if( $index % 2 !== 0 || array_key_last( $featured_attrs ) == $index ) { ?>
							</div>
						<?php } ?>
					<?php } ?>
				</div>
			</div>
			<?php
		}
	}
}
add_action( 'woocommerce_single_product_summary', 'drplus_wc_single_feature_attrs', 20 );

add_action( 'woocommerce_product_meta_end', 'woocommerce_template_single_price', 100 );

if( !function_exists( "drplus_wc_stock_html" ) ) {
	function drplus_wc_stock_html( $html, $product ) {
		if( !$html ) {
			if( $product->get_price() && $product->get_stock_status() !== 'outofstock' ) {
				$html = '<div class="stock instock">' . esc_html__( "In stock", 'woocommerce' ) . '</div>';
			} else {
				$html = '<div class="stock outofstock">' . esc_html__( "Out of stock", 'woocommerce' ) . '</div>';
			}
		}
		return $html;
	}
}
add_filter( 'woocommerce_get_stock_html', 'drplus_wc_stock_html', 10, 2 );

if( !function_exists( 'drplus_wc_single_add_to_cart_text' ) ) {
	function drplus_wc_single_add_to_cart_text( $text ) {
		$options = Options::get_options( [
			'wc_add_to_cart_single_text'	=> __( 'Add to cart', 'drplus' )
		] );
		return $options['wc_add_to_cart_single_text'];
	}
}
add_filter( 'woocommerce_product_single_add_to_cart_text', 'drplus_wc_single_add_to_cart_text' );

add_filter( 'woocommerce_product_description_heading', '__return_false' );
add_filter( 'woocommerce_product_additional_information_heading', '__return_false' );

if( !function_exists( 'drplus_wc_product_footer' ) ) {
	function drplus_wc_product_footer() {
	  global $product;
	  ?>
	  <div id="post-terms-wrap">
		<?php echo wc_get_product_category_list( $product->get_id(), ( is_rtl() ? "، " : ', ' ), '<div id="post-categories" class="post-terms posted_in"><span class="post-term-title">' . _n( 'Category:', 'Categories:', count( $product->get_category_ids() ), 'woocommerce' ) . '</span>', '</div>' ); ?>
  
		<?php echo wc_get_product_tag_list( $product->get_id(), '', '<div id="post-tags" class="post-terms tagged_as"><span class="post-term-title">' . _n( 'Tag:', 'Tags:', count( $product->get_tag_ids() ), 'woocommerce' ) . '</span>', '</div>' ); ?>
	  </div>
	  <?php
	}
}
add_action( 'drplus_product_after_description_tab', 'drplus_wc_product_footer' );

if( !function_exists( "drplus_modify_product_tabs" ) ) {
	function drplus_modify_product_tabs( $tabs ) {
		if( !empty( $tabs['description'] ) ) {
			$tabs['description']['title'] = esc_html__( 'Product introduction', 'drplus' );
			$tabs['description']['icon'] = 'drplus-icon-document';
		}
		if( !empty( $tabs['additional_information'] ) ) {
			$tabs['additional_information']['title'] = esc_html__( 'Technical specifications', 'drplus' );
			$tabs['additional_information']['icon'] = 'drplus-icon-task';
		}
		if( !empty( $tabs['reviews'] ) ) {
			$tabs['reviews']['icon'] = 'drplus-icon-chat-fill';
		}
		return $tabs;
	}
}
add_filter( 'woocommerce_product_tabs', 'drplus_modify_product_tabs' );

if( !function_exists( "drplus_wc_single_comments" ) ) {
	function drplus_wc_single_comments() {
		if ( ! comments_open() ) return;
		?>
		<div id="product-reviews" class="product-reviews" aria-labelledby="tab-title-reviews">
			<?php
			get_template_part( "templates/components/template-components-section_title", null, [
				'icon'			=> "drplus-icon-chat-fill",
				'title'			=> esc_html__( 'Reviews', 'drplus' ),
				'classes'		=> ['product-tab-content-title'],
				'tag'			=> 'h3'
			] );
			comments_template();
			?>
		</div>
		<?php
	}
}

if( !function_exists( "drplus_wc_related_products_args" ) ) {
	function drplus_wc_related_products_args( $args ) {
		$args['posts_per_page'] = 8;
		$args['columns'] = 5;
		return $args;
	}
}
add_filter( 'woocommerce_output_related_products_args', 'drplus_wc_related_products_args' );

if( !function_exists( "drplus_wc_variation_price_html" ) ) {
	function drplus_wc_variation_price_html( $price, $product ) {
		if( $product->is_type( 'variation' ) ) {
			if( $product->is_on_sale() ) {
				$reg_price = $product->get_regular_price();
				$sale_price = $product->get_sale_price();
				$price = wc_format_sale_price( wc_get_price_to_display( $product, array( 'price' => $reg_price ) ), wc_get_price_to_display( $product ) ) . $product->get_price_suffix();
				if( is_numeric( $sale_price ) && is_numeric( $reg_price ) ) {
					$price .= '<span class="price-discount-percentage">%' . Utils::calc_product_discount_percentage( $reg_price, $sale_price ) . '</span>';
				}
			}
		}
		return $price;
	}
}
add_filter( 'woocommerce_get_price_html', 'drplus_wc_variation_price_html', 10, 2 );

if( !function_exists( "drplus_wc_comment_info" ) ) {
	function drplus_wc_comment_info( $comment ) {
		if( '0' !== $comment->comment_approved ) {
			?>
			<div class="meta comment-meta">
				<span class="review-date-label"><?php esc_html_e( 'Date', 'drplus' ) ?>:</span>
				<time class="review-date woocommerce-review__published-date" datetime="<?php echo esc_attr( get_comment_date( 'c' ) ); ?>"><?php echo esc_html( get_comment_date( wc_date_format() ) ); ?></time>
			</div>
			<?php
		}
	}
}
add_action( 'drplus/wc/comments/comment_info', 'drplus_wc_comment_info', 10, 1 );

if( !function_exists( "drplus_wc_related_products_args" ) ) {
	function drplus_wc_related_products_args( $args ) {
		$options = Options::get_options( [
			'wc-single-end-products-show'	=> true,
			'wc-single-end-products-type'	=> 'related',
			'wc-single-end-products-ppp'	=> 8,
		] );
		if( !Utils::to_bool( $options['wc-single-end-products-show'] ) || $options['wc-single-end-products-type'] != 'related' ) return [];

		$args['posts_per_page'] = $options['wc-single-end-products-ppp'];
		$args['columns'] = $args['posts_per_page'];

		return $args;
	}
}
add_filter( 'woocommerce_output_related_products_args', 'drplus_wc_related_products_args' );

if( !function_exists( "drplus_wc_related_products" ) ) {
	// Sometimes the related products has been cached. So we slice the array by this function
	function drplus_wc_related_products( $products ) {
		$options = Options::get_options( [
			'wc-single-end-products-show'	=> true,
			'wc-single-end-products-type'	=> 'related',
			'wc-single-end-products-ppp'	=> 8,
		] );
		if( !Utils::to_bool( $options['wc-single-end-products-show'] ) || $options['wc-single-end-products-type'] != 'related' ) return [];

		$products = array_slice( $products, 0, $options['wc-single-end-products-ppp'] );

		return $products;
	}
}
add_filter( 'woocommerce_related_products', 'drplus_wc_related_products' );

if( !function_exists( "drplus_wc_change_related_products_title" ) ) {
	function drplus_wc_change_related_products_title( $title ) {
		$options = Options::get_options( [
			'wc-single-end-products-show'	=> true,
			'wc-single-end-products-title'	=> esc_html__( 'Related products', 'drplus' ),
		] );
		return Utils::to_bool( $options['wc-single-end-products-show'] ) ? esc_html( $options['wc-single-end-products-title'] ) : '';
	}
}
add_filter( 'woocommerce_product_related_products_heading', 'drplus_wc_change_related_products_title' );