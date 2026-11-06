<?php

use DrPlus\Utils;
use DrPlus\Utils\Archive;
use DrPlus\Utils\Elementor;
use DrPlus\Utils\Product;
use DrPlus\Utils\Search;
use DrPlus\Utils\UtilsSpecialists;

$post_type = $args['post_type'];

$display_settings = [
	'desktop_slider'	=> false,
	'desktop_cols'		=> 3,
	'desktop_gap'		=> 16,

	'tablet_slider'	=> false,
	'tablet_cols'	=> 2,
	'tablet_gap'	=> 16,

	'mobile_slider'	=> false,
	'mobile_cols'	=> 1,
	'mobile_gap'	=> 16,
];
$display_attributes = Elementor::get_display_attributes( $display_settings );

$div_attrs = [
	'class'	=> array_merge( [
		"{$post_type}-wrap",
		'search-section',
	], $display_attributes['wrap_classes'] ),
	'data-settings'	=> $display_attributes['args'],
	'style'			=> $display_attributes['style'],
];
if( $post_type == 'specialist' ) {
	$div_attrs['class'][] = 'specialists';
} else if( $post_type == 'speciality' ) {
	$div_attrs['class'][] = 'specialities';
} else if( $post_type == 'hospital' ) {
	$div_attrs['class'][] = 'hospitals';
} else if( $post_type == 'product' ) {
	$props = Product::get_loop_props();
	$props = Utils::check_default( $display_settings, $props ); // Set props values from display_settings
	$props['remove_swiper_div'] = true;
	wc_set_loop_prop( 'drplus_loop_props', $props );

	$div_attrs['class'][] = 'drplus-products-slider';
	$div_attrs['class'][] = 'columns-' . wc_get_loop_prop( 'columns' );
	$div_attrs['class'][] = "products-{$props['style']}";
} else { // Other post type but not product
	$div_attrs['class'][] = 'list-posts';
	$div_attrs['class'][] = "posts-{$args['options']['archive_posts_style']}";
}
$div_attrs['class'] = apply_filters( 'drplus/search/results/section_classes', $div_attrs['class'], $post_type );

$wrapper_attrs = [
	'class'	=> array_merge( [
		'wrapper',
	], $display_attributes['classes'] ),
];

// To get icon and labels of post type
$categorized_results = Search::get_categorized_results();

if( !empty( $categorized_results ) ) {
	echo "<div " . Utils::get_html_attributes( $div_attrs ) . ">";
		if( $post_type == 'specialist' ) {
			get_template_part( "templates/search/templates-search-section-head", null, [
				'show_more'	=> false,
				'icon'		=> $categorized_results['specialist']['icon'],
				'label'		=> $categorized_results['specialist']['label'],
				'count'		=> $categorized_results['specialist']['count'],
				'post_type'	=> $post_type,
			] );
		} else {
			get_template_part( "templates/search/templates-search-section-head", null, [
				'show_more'	=> false,
				'icon'		=> $categorized_results[$post_type]['icon'],
				'label'		=> $categorized_results[$post_type]['label'],
				'count'		=> $categorized_results[$post_type]['count'],
			] );
		}
			if( $post_type != 'product' ) {
				echo "<div " . Utils::get_html_attributes( $wrapper_attrs ) . ">";
			}
			if( $post_type == 'specialist' ) {
				$upp = get_option( 'posts_per_page', 8 );
				UtilsSpecialists::list_html( [
					'specialists'	=> $categorized_results['specialist']['results'],
					'settings'		=> $display_settings,
					'remove_wrap'	=> true,
				] );
	
				get_template_part( "templates/archives/template-archives-pagination", 'custom', [
					'max_num_pages'		=> ceil( $categorized_results['specialist']['count'] / $upp ),
					'query_arg_name'	=> 'paged',
					'paged'				=> Archive::get_paged(),
				] );
			} else {
				if( have_posts() ) {
					if( $post_type == 'product' ) {
						woocommerce_product_loop_start();
					}
					while( have_posts() ) {
						the_post();
						if( $post_type == 'product' ) {
							$GLOBALS['product'] = wc_get_product();
							/**
							 * Hook: woocommerce_shop_loop.
							 */
							do_action( 'woocommerce_shop_loop' );
							wc_get_template_part( 'content', 'product' );
						} else {
							$custom_rendered = apply_filters( "drplus/search/results/{$post_type}", false, $post );
							if( !$custom_rendered ) {
								get_template_part( "templates/archives/template-archives-post", $args['options']['archive_posts_style'], [
									'title_tag'			=> $args['options']['search_post_title_tag'],
									'time_type'			=> $args['options']['search_post_time_type'],
									'show_read_more'	=> $args['options']['search_post_show_read_more'],
									'read_more_text'	=> $args['options']['search_post_read_more_text'],
									'read_more_icon'	=> $args['options']['search_post_read_more_icon'],
								] );
							}
						}
					}
					if( $post_type == 'product' ) {
						woocommerce_product_loop_end();
					}
	
					get_template_part( "templates/archives/template-archives-pagination" );
				}
			}
		if( $post_type != 'product' ) {
			echo "</div>";
		}
	echo "</div>";
} else {
	get_template_part( "templates/search/templates-search-empty" );
}