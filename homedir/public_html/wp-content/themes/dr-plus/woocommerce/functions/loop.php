<?php
use DrPlus\Utils;
use DrPlus\Utils\Options;
use DrPlus\Utils\Product;

remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );

if( !function_exists( "drplus_wc_pagination_args" ) ) {
	function drplus_wc_pagination_args( $args ) {
		$is_rtl = is_rtl();
		$args['prev_text'] = $is_rtl ? "<i class='drplus-icon-chevron-right-dot'></i>" : "<i class='drplus-icon-chevron-left-dot'></i>";
		$args['prev_text'] .= esc_html_x( "Previous", 'Pagination', 'drplus' );
		$args['next_text'] = esc_html_x( "Next", 'Pagination', 'drplus' );
		$args['next_text'] .= $is_rtl ? "<i class='drplus-icon-chevron-left-dot'></i>" : "<i class='drplus-icon-chevron-right-dot'></i>";
		return $args;
	}
}
add_filter( 'woocommerce_pagination_args', 'drplus_wc_pagination_args' );

// Change variable products price range
if( !function_exists( "drplus_wc_format_price_range" ) ) {
	function drplus_wc_format_price_range( $price_html, $from, $to ) {
		$price_html = '<div class="price-range-wrap">';
			$price_html .= '<div class="price-range price-range-from">';
				$price_html .= '<div class="price-range-label">' . esc_html_x( 'From', 'Price range', 'drplus' ) . '</div>';
				$price_html .= is_numeric( $from ) ? wc_price( $from ) : $from;
			$price_html .= '</div>';
			$price_html .= '<div class="price-range price-range-to">';
				$price_html .= '<div class="price-range-label">' . esc_html_x( 'To', 'Price range', 'drplus' ) . '</div>';
				$price_html .= is_numeric( $to ) ? wc_price( $to ) : $to;
			$price_html .= '</div>';
		$price_html .= '</div>';
		return $price_html;
	}
}
add_filter( 'woocommerce_format_price_range', 'drplus_wc_format_price_range', 10, 3 );

if( !function_exists( "drplus_wc_template_loop_product_link_open" ) ) {
	function drplus_wc_template_loop_product_link_open() {
		global $product;

		$link = apply_filters( 'woocommerce_loop_product_link', get_the_permalink(), $product );

		echo '<a href="' . esc_url( $link ) . '" class="woocommerce-LoopProduct-link woocommerce-loop-product__link" title="' . esc_attr( $product->get_name() ) . '">';
	}
}
add_action( 'woocommerce_before_shop_loop_item', 'drplus_wc_template_loop_product_link_open', 10 );

if( !function_exists( "drplus_wc_catalog_orderby" ) ) {
	function drplus_wc_catalog_orderby( $args ) {
		$args['menu_order']	= __( 'Default sorting', 'drplus' );
		$args['popularity']	= __( 'Popularity', 'drplus' );
		$args['rating']		= __( 'Average rating', 'drplus' );
		$args['date']		= __( 'Latest', 'drplus' );
		$args['price']		= __( 'Price: low to high', 'drplus' );
		$args['price-desc']	= __( 'Price: high to low', 'drplus' );
		return $args;
	}
}
add_filter( 'woocommerce_catalog_orderby', 'drplus_wc_catalog_orderby' );

/////////////////// Move out of stocks
if( !function_exists( "drplus_wc_move_out_of_stock_to_end_clauses" ) ) {
	function drplus_wc_move_out_of_stock_to_end_clauses( $args ) {
		global $wpdb;
		$args['join'] .= " LEFT JOIN {$wpdb->postmeta} AS bmt1 ON ({$wpdb->posts}.ID = bmt1.post_id AND bmt1.meta_key = '_stock_status')";

		$default_orderby = $args['orderby'];
		// Sort by stock status: 'instock' first, then 'outofstock', then NULL
		// Using FIELD() to prioritize 'instock' over other statuses
		$args['orderby'] = " FIELD(bmt1.meta_value, 'instock', 'onbackorder') DESC";
		if( $default_orderby ) {
			$args['orderby'] .= ", {$default_orderby}";
		}

		return $args;
	}
}

if( !function_exists( "drplus_wc_move_out_of_stock_to_end" ) ) {
	function drplus_wc_move_out_of_stock_to_end( $query ) {
		static $executed = false;
		if( !$executed ) {
			$options = Options::get_options( [
				'wc-move-out-of-stock-to-end'	=> false,
			] );
			if( !Utils::to_bool( $options['wc-move-out-of-stock-to-end'] ) ) return;

			if( isset( $_GET['instock'] ) && Utils::to_bool( $_GET['instock'] ) ) return;

			add_filter( 'posts_clauses', 'drplus_wc_move_out_of_stock_to_end_clauses' );
			$executed = true;
		}
	}
}
add_filter( 'woocommerce_product_query', 'drplus_wc_move_out_of_stock_to_end' );

if( !function_exists( "drplus_wc_move_out_of_stock_to_end_custom" ) ) {
	function drplus_wc_move_out_of_stock_to_end_custom( $pieces, $query ) {
		if( isset( $query->query_vars['move_out_of_stocks_to_end'] ) && Utils::to_bool( $query->query_vars['move_out_of_stocks_to_end'] ) ) {
			$pieces = drplus_wc_move_out_of_stock_to_end_clauses( $pieces );
		}

		return $pieces;
	}
}
add_filter( 'posts_clauses_request', 'drplus_wc_move_out_of_stock_to_end_custom', 10, 2 );

if( !function_exists( "drplus_wc_only_in_stocks" ) ) {
	function drplus_wc_only_in_stocks( $query ) {
		if( isset( $query->query_vars['only_in_stocks'] ) && Utils::to_bool( $query->query_vars['only_in_stocks'] ) ) {
			drplus_wc_custom_filters( $query );
		}
	}
}
add_action( 'pre_get_posts', 'drplus_wc_only_in_stocks' );

if( !function_exists( "drplus_wc_custom_orderby" ) ) {
	function drplus_wc_custom_orderby( $query, $query_vars ) {
		$custom_orders = ['price', 'popularity', 'rating', 'sales'];
		if( !empty( $query_vars['orderby'] ) && in_array( $query_vars['orderby'], $custom_orders ) ) {
			$orderby = $query_vars['orderby'];
			$order = $query_vars['order'] ?? 'ASC';
			if( $orderby == 'price' ) {
				$query['orderby'] = 'meta_value_num';
				$query['meta_key'] = '_price';
				$query['order'] = $order;
			} elseif( $orderby == 'popularity' ) {
				$query['orderby'] = 'meta_value_num';
				$query['meta_key'] = 'total_sales';
				$query['order'] = $order;
			} elseif( $orderby == 'rating' ) {
				$query['orderby'] = 'meta_value_num';
				$query['meta_key'] = '_wc_average_rating';
				$query['order'] = $order;
			} elseif( $orderby == 'sales' ) {
				$query['orderby'] = 'meta_value_num';
				$query['meta_key'] = 'total_sales';
				$query['order'] = $order;
			}
		}

		return $query;
	}
}
add_filter( 'woocommerce_product_data_store_cpt_get_products_query', 'drplus_wc_custom_orderby', 10, 2 );

if( !function_exists( "drplus_wc_taxonomy_archive_description_bottom" ) ) {
	function drplus_wc_taxonomy_archive_description_bottom() {
		woocommerce_taxonomy_archive_description();
	}
}
add_action( 'drplus/wc/archive/end_primary', 'drplus_wc_taxonomy_archive_description_bottom', 10 );

if( !function_exists( "drplus_wc_product_archive_description_bottom" ) ) {
	function drplus_wc_product_archive_description_bottom() {
		woocommerce_product_archive_description();
	}
}
add_action( 'drplus/wc/archive/end_primary', 'drplus_wc_product_archive_description_bottom', 10 );

if( !function_exists( "drplus_wc_loop_product_link_close" ) ) {
	function drplus_wc_loop_product_link_close() {
		$props = Product::get_loop_props();
		if( $props['style'] != 'style-2' ) {
			woocommerce_template_loop_product_link_close();
		}
	}
}
add_action( 'woocommerce_after_shop_loop_item', 'drplus_wc_loop_product_link_close', 5 );