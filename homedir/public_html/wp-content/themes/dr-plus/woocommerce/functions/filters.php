<?php

use DrPlus\Utils;
use DrPlus\Utils\Options;
use DrPlus\Utils\Sanitizers;
use DrPlus\Utils\UI;
use DrPlus\Utils\WC;

if( !function_exists( "drplus_wc_price_filter_additional_options" ) ) {
	function drplus_wc_price_filter_additional_options() {
		$options = [
			'instock'	=> esc_html__( 'Instock products', 'drplus' ),
			'onsale'	=> esc_html__( 'On-sale products', 'drplus' ),
		];

		?>
		<div class="drplus_filter_additional_options">
			<?php
			foreach( $options as $id => $label ) {
				UI::filter_radio( $label, $id, true );
			}
			?>
		</div>
		<?php
	}
}
add_action( 'woocommerce_widget_price_filter_end', 'drplus_wc_price_filter_additional_options' );

// Apply instock & onsale filter
if( !function_exists( "drplus_wc_custom_filters" ) ) {
	function drplus_wc_custom_filters( $query ) {
		if( !$query->is_post_type_archive( 'product' ) ) return;
	
		$meta_query = $query->get( 'meta_query' ) ?: array();

		// Filter products that are in stock
		if( !empty( $_GET['instock'] ) || ( isset( $query->query_vars['only_in_stocks'] ) && Utils::to_bool( $query->query_vars['only_in_stocks'] ) ) ) {
			$meta_query[] = array(
				'key'     => '_stock_status',
				'value'   => 'instock',
				'compare' => '='
			);
		}

		// Apply the modified meta query
		if( !empty( $meta_query ) ) {
			$query->set( 'meta_query', $meta_query );
		}

		// Filter products that are on sale
		if( !empty( $_GET['onsale'] ) ) {
			$post__in = $query->get( 'post__in' ) ?: [];
			$product_ids_on_sale = wc_get_product_ids_on_sale();
			$query->set( 'post__in', array_unique( array_merge( $post__in, $product_ids_on_sale ) ) );
		}
	}
}
if( !is_admin() ) {
	add_action( 'pre_get_posts', 'drplus_wc_custom_filters' );
}

// Change HTML for colors in filter widget
if( !function_exists( "drplus_wc_filter_term_html" ) ) {
	function drplus_wc_filter_term_html( $term_html, $term, $link ) {
		$taxonomy_id = wc_attribute_taxonomy_id_by_name( $term->taxonomy );
		$taxonomy_options = WC::get_attribute_settings( $taxonomy_id );

		if( !in_array( $taxonomy_options['display_type'], array_keys( WC::attr_display_types() ) ) ) return $term_html;

		$popover_html = '<div class="drplus-filter-color-text drplus-popover drplus-popover-center">' . $term->name . '</div>';
		if( $taxonomy_options['display_type'] == 'color' ) {
			if( $link ) {
				$term_html = '<a class="drplus_filter drplus-filter-color-wrap drplus-popover-wrap" rel="nofollow" href="' . esc_url( $link ) . '"><span class="drplus-filter-color" style="background-color: ' . esc_attr( WC::get_term_color( $term->term_id ) ) . '"></span>' . $popover_html . '</a>';
			} else { // Currently selected
				$term_html = '<div class="drplus_filter drplus-filter-color-wrap drplus-popover-wrap"><span class="drplus-filter-color" style="background-color: ' . esc_attr( WC::get_term_color( $term->term_id ) ) . '"></span>' . $popover_html . '</div>';
			}
		}

		return $term_html;
	}
}
add_filter( 'woocommerce_layered_nav_term_html', 'drplus_wc_filter_term_html', 10, 3 );

// Show variation options in single product
if( !function_exists( "drplus_wc_single_variation_options" ) ) {
	function drplus_wc_single_variation_options() {
		global $product;
		if( empty( $product ) ) return;
		if( $product->is_type( 'variable' ) ) {
			$attributes = $product->get_variation_attributes();
			?>
			<div class="product-head-variations">
				<?php
				foreach( $attributes as $attr_name => $options ) {
					$taxonomy_id = wc_attribute_taxonomy_id_by_name( $attr_name );
					$attr_settings = WC::get_attribute_settings( $taxonomy_id );
					wc_dropdown_variation_attribute_options( [
						'options'				=> $options,
						'attribute'				=> $attr_name,
						'product'				=> $product,
						'drplus_custom_display'	=> $attr_settings['display_type'],
						'drplus_attribute_icon'	=> $attr_settings['icon'],
					] );
				}
				?>
			</div>
			<?php
		}
	}
}
add_action( 'woocommerce_single_product_summary', 'drplus_wc_single_variation_options', 30 );

if( !function_exists( "drplus_wc_custom_variation_attribute_dropdown" ) ) {
	function drplus_wc_custom_variation_attribute_dropdown( $html, $args ) {
		if( !empty( $args['drplus_custom_display'] ) ) {
			$default_options = $args['options'];
			$options = [];
			$attribute = $args['attribute'];
			$product = $args['product'];
			$selected = '';
			if ( $product && taxonomy_exists( $attribute ) ) {
				// Get terms if this is a taxonomy - ordered. We need the names too.
				$terms = wc_get_product_terms(
					$product->get_id(),
					$attribute,
					array(
						'fields' => 'all',
					)
				);

				foreach( $terms as $term ) {
					if( in_array( $term->slug, $default_options, true ) ) {
						$options[$term->slug] = [
							'label'			=> esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute, $product ) ),
							'custom_value'	=> '',
							'selected'		=> sanitize_title( $args['selected'] ) == $term->slug
						];
						if( $args['drplus_custom_display'] == 'color' ) {
							$options[$term->slug]['custom_value'] = WC::get_term_color( $term->term_id );
						} else if( $args['drplus_custom_display'] == 'image' ) {
							$img_id = WC::get_term_img( $term->term_id );
							if( !$img_id ) {
								global $product;
								$img_id = $product->get_image_id();
								if( !$img_id ) {
									$img_id = get_option( 'woocommerce_placeholder_image', 0 );
								}
							}
							if( !wp_attachment_is_image( $img_id ) ) {
								$img_id = 0;
							}
							$options[$term->slug]['custom_value'] = $img_id;
						}
					}
				}
			} else {
				foreach( $default_options as $option ) {
					$selected = sanitize_title( $args['selected'] ) === $args['selected'] ? $args['selected'] == sanitize_title( $option ) : $args['selected'] == $option;
					$options[esc_attr( $option )] = [
						'label'		=> esc_html( apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute, $product ) ),
						'selected'	=> $selected
					];
				}
			}

			$icon = $args['drplus_attribute_icon'];
			if( !$icon ) {
				$icon = Options::get_options( [
					'wc-default-attribute-icon'	=> 'drplus-icon-diamond',
				] )['wc-default-attribute-icon'];
			}

			ob_start();
			?>
			<div class="product-head-variation product-head-variation-<?php echo $args['drplus_custom_display'] ?>-wrap" data-attr="<?php echo esc_attr( sanitize_title( $attribute ) ) ?>">
				<div class="product-head-variation-label">
					<?php echo Sanitizers::icon( $icon, 'product-head-variation-label-icon' ) ?>
					<span class="product-head-variation-label-text"><?php echo esc_html( wc_attribute_label( $args['attribute'] ) ) ?>:</span>
				</div>
				<div class="product-head-variation-items product-head-variation-<?php echo $args['drplus_custom_display'] ?>-items">
					<?php
					$classes = ['product-head-variation-item', "product-head-variation-{$args['drplus_custom_display']}", 'drplus-popover-wrap'];
					if( $args['drplus_custom_display'] != 'select' ) {
						foreach( $options as $value => $option ) {
							$item_classes = $classes;
							if( $option['selected'] ) {
								$item_classes[] = 'selected';
							}
							if( $args['drplus_custom_display'] == 'color' ) {
								?>
								<div class="<?php echo Utils::prepare_html_classes( $item_classes ) ?>" style="background:<?php echo $option['custom_value'] ?>" data-value="<?php echo esc_attr( $value ) ?>">
									<span class="drplus-filter-color-text drplus-popover drplus-popover-center"><?php echo esc_html( $option['label'] ) ?></span>
								</div>
								<?php
							} else if( $args['drplus_custom_display'] == 'image' ) {
								?>
								<div class="<?php echo Utils::prepare_html_classes( $item_classes ) ?>" data-value="<?php echo esc_attr( $value ) ?>">
									<?php echo wp_get_attachment_image( $option['custom_value'], [44, 44] ) ?>
									<span class="drplus-filter-image-text drplus-popover drplus-popover-center"><?php echo esc_html( $option['label'] ) ?></span>
								</div>
								<?php
							}
						}
					} else { // Select
						$dropdown_options = [];
						foreach( $options as $value => $option ) {
							$dropdown_options[$value] = $option['label'];
						}
						$item_classes = $classes;
						UI::dropdown( [
							'classes'	=> ['product-head-variation-select-wrap', 'product-attribute-dropdown'],
							'empty'		=> $args['show_option_none'],
							'current'	=> $selected,
							'options'	=> $dropdown_options,
							'attrs'		=> [
								'data-attr'	=> sanitize_title( $attribute ),
							],
						] );
					}
					?>
				</div>
			</div>
			<?php
			$html = ob_get_clean();
		}

		return $html;
	}
}
add_filter( 'woocommerce_dropdown_variation_attribute_options_html', 'drplus_wc_custom_variation_attribute_dropdown', 10, 2 );