<?php
namespace DrPlus\Utils;

use DrPlus\Utils;

if( !Utils::is_wc_active() ) return;

class Product extends Utils {
	public static function get_gallery_ids( $product ) {
		$post_thumbnail_id = $product->get_image_id();
		$attachment_ids = $product->get_gallery_image_ids();
		$new_images = [$post_thumbnail_id];

		if( $product->is_type( 'variable' ) ) {
			$variations_with_image = $product->get_available_variations( 'image' );
			// Add variation images
			foreach( $variations_with_image as $variation ) {
				$new_images[] = $variation->get_image_id();
			}
			// Add variations gallery
			foreach( $variations_with_image as $variation ) {
				$new_images = array_merge( $new_images, $variation->get_gallery_image_ids() );
			}
		}

		$attachment_ids = array_unique( array_merge( $new_images, $attachment_ids ) );
		$attachment_ids = array_filter( $attachment_ids );

		return $attachment_ids;
	}

	public static function slider_image_html( $attachment_id, $main_image = false ) {
		if( empty( $attachment_id ) ) return '';
		$gallery_thumbnail = wc_get_image_size( 'gallery_thumbnail' );
		$thumbnail_size    = apply_filters( 'woocommerce_gallery_thumbnail_size', array( $gallery_thumbnail['width'], $gallery_thumbnail['height'] ) );
		$image_size        = apply_filters( 'woocommerce_gallery_image_size', $main_image ? 'woocommerce_single' : $thumbnail_size );
		$full_size         = apply_filters( 'woocommerce_gallery_full_size', apply_filters( 'woocommerce_product_thumbnails_large_size', 'full' ) );
		$full_src          = wp_get_attachment_image_src( $attachment_id, $full_size );
		if( empty( $full_src ) ) return '';
		$image             = wp_get_attachment_image(
			$attachment_id,
			$image_size,
			false,
			apply_filters(
				'woocommerce_gallery_image_html_attachment_image_params',
				array(
					'title'                   => _wp_specialchars( get_post_field( 'post_title', $attachment_id ), ENT_QUOTES, 'UTF-8', true ),
					'data-caption'            => _wp_specialchars( get_post_field( 'post_excerpt', $attachment_id ), ENT_QUOTES, 'UTF-8', true ),
					'data-src'                => esc_url( $full_src[0] ),
					'data-large_image'        => esc_url( $full_src[0] ),
					'data-large_image_width'  => esc_attr( $full_src[1] ),
					'data-large_image_height' => esc_attr( $full_src[2] ),
					'class'                   => esc_attr( $main_image ? 'wp-post-image' : '' ),
				),
				$attachment_id,
				$image_size,
				$main_image
			)
		);

		return '<div class="woocommerce-product-gallery__image swiper-slide" data-id="' . esc_attr( $attachment_id ) . '">' . $image . '</div>';
	}

	public static function get_default_props() {
		$settings = Options::get_options( [
			'default_wc_products_style'	=> 'style-1',
		] );

		$desktop_columns = absint( wc_get_loop_prop( 'columns' ) );
		if( $desktop_columns === 0 ) {
			$desktop_columns = 4;
		}

		return [
			'style'					=> "{$settings['default_wc_products_style']}", // To support string

			'desktop_slider'		=> false,
			'desktop_slides_type'	=> 'auto',
			'desktop_slides'		=> $desktop_columns,
			'desktop_slides_space'	=> 0,
			'desktop_cols'			=> $desktop_columns,
			'desktop_gap'			=> 16,
			
			'tablet_slider'			=> false,
			'tablet_slides_type'	=> 'auto',
			'tablet_slides'			=> 4,
			'tablet_slides_space'	=> 0,
			'tablet_cols'			=> 2,
			'tablet_gap'			=> 16,

			'mobile_slider'			=> false,
			'mobile_slides_type'	=> 'auto',
			'mobile_slides'			=> 4,
			'mobile_slides_space'	=> 0,
			'mobile_cols'			=> 1,
			'mobile_gap'			=> 16,
		];
	}

	public static function get_loop_props() {
		$props = wc_get_loop_prop( 'drplus_loop_props' );
		if( !is_array( $props ) ) $props = [];

		if( !empty( $_GET['products-style'] ) ) {
			$style = parent::convert_chars( $_GET['products-style'] );
			$props['style'] = $style;
		}

		$default_props = self::get_default_props();

		$props = parent::check_default( $props, $default_props );

		wc_set_loop_prop( 'drplus_loop_props', $props );

		return $props;
	}

	public static function get_featured_attributes( int $product_id ) {
		static $featured_attrs = null;
		if( $featured_attrs === null ) {
			$featured_attrs = get_post_meta( $product_id, '_drplus_featured_attrs', true );
			if( !is_array( $featured_attrs ) ) $featured_attrs = [];
			$updated = false;

			if( !empty( $featured_attrs ) ) {
				$product = wc_get_product( $product_id );
				$product_attrs = $product->get_attributes();
				if( !empty( $product_attrs ) ) {
					$new_featured_attrs = array_intersect( $featured_attrs, array_map( fn( $attr ) => $attr->get_name(), $product_attrs ) );
					if( $featured_attrs != $new_featured_attrs ) {
						$featured_attrs = $new_featured_attrs;
						$updated = true;
					}
				} else {
					$featured_attrs = [];
					$updated = true;
				}
			}

			if( $updated ) {
				self::update_featured_attributes( $product_id, $featured_attrs );
			}
		}

		return $featured_attrs;
	}

	public static function update_featured_attributes( int $product_id, array $attributes ) {
		update_post_meta( $product_id, '_drplus_featured_attrs', $attributes );
	}

	public static function get_subtitle( int $product_id ) {
		return get_post_meta( $product_id, '_drplus_subtitle', true );
	}

	public static function save_subtitle( int $product_id, string $subtitle ) {
		update_post_meta( $product_id, '_drplus_subtitle', sanitize_textarea_field( $subtitle ) );
	}

	public static function get_services( $product_id ) {
		$services = get_the_terms( $product_id, 'product-service' );
		if( !empty( $services ) ) {
			foreach( $services as $id => $service ) {
				$services[$id]->icon = get_term_meta( $service->term_id, 'service_icon', true );
			}
		}
		return $services;
	}
}