<?php
/**
 * Product Loop Start
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/loop-start.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     3.3.0
 */

use DrPlus\Components\SectionTitle;
use DrPlus\Utils;
use DrPlus\Utils\Elementor;
use DrPlus\Utils\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$props = Product::get_loop_props();

$display_attributes = Elementor::get_display_attributes( $props );

$attributes = [
	'class'			=> [
		'drplus-slider-wrap',
		'drplus-products-slider',
		'columns-' . wc_get_loop_prop( 'columns' ),
		"products-{$props['style']}"
	],
	'data-settings'	=> $display_attributes['args'],
	'style'			=> $display_attributes['style'],
];
$attributes['class'] = array_merge( $attributes['class'], $display_attributes['wrap_classes'] );

$list_attributes = [
	'class'	=> array_merge( ["products", "wrapper"], $display_attributes['classes'] ),
];

if( empty( $props['remove_swiper_div'] ) ) {
	?>
	<div <?php echo Utils::get_html_attributes( $attributes ) ?>>
	<?php
	if( !empty( $props['section_title_title'] ) ) {
		echo '<div class="drplus-slider-head">';
			SectionTitle::view( [
				'icon'		=> $props['section_title_icon'],
				'title'		=> $props['section_title_title'],
				'tag'		=> $props['section_title_tag'],
				'link'		=> $props['section_title_link'],
				'nav_btns'	=> true,
			] );
		echo '</div>';
	}
}
?>
<ul <?php echo Utils::get_html_attributes( $list_attributes ) ?>>
