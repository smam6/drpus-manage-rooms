<?php
/**
 * Product attributes
 *
 * Used by list_attributes() in the products class.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-attributes.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! $product_attributes ) {
	return;
}
$max_attr_to_show_first = 4;
$index = 0;
?>
<table class="woocommerce-product-attributes shop_attributes" aria-label="<?php esc_attr_e( 'Product Details', 'woocommerce' ); ?>">
	<?php foreach ( $product_attributes as $product_attribute_key => $product_attribute ) : ?>
		<?php $index++; ?>
		<tr class="woocommerce-product-attributes-item woocommerce-product-attributes-item--<?php echo esc_attr( $product_attribute_key ); ?><?php echo $index > $max_attr_to_show_first ? ' product-attr-extra-row' : '' ?>">
			<th class="woocommerce-product-attributes-item__label" scope="row">
				<i class="drplus-icon-diamond"></i>
				<?php echo wp_kses_post( $product_attribute['label'] ); ?>
			</th>
			<td class="woocommerce-product-attributes-item__value">
				<?php
				$attr_key = $product_attribute_key;
				// remove attribute_ from key
				if( substr( $attr_key, 0, 10 ) == 'attribute_' ) {
					$attr_key = substr( $product_attribute_key, 10 );
				}
				if( !empty( $attributes[ $attr_key ] ) ) {
					$attribute = $attributes[ $attr_key ];
					$values = [];
					if ( $attribute->is_taxonomy() ) {
						$attribute_taxonomy = $attribute->get_taxonomy_object();
						$attribute_values   = wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'all' ) );
			
						foreach ( $attribute_values as $attribute_value ) {
							$value_name = esc_html( $attribute_value->name );
			
							if ( $attribute_taxonomy->attribute_public ) {
								$values[] = '<a href="' . esc_url( get_term_link( $attribute_value->term_id, $attribute->get_name() ) ) . '" rel="tag">' . $value_name . '</a>';
							} else {
								$values[] = $value_name;
							}
						}
					} else {
						$values = $attribute->get_options();
			
						foreach ( $values as &$value ) {
							$value = make_clickable( esc_html( $value ) );
						}
					}
				} else {
					$values = [$product_attribute['value']];
				}
				if( count( $values ) > 1 ) {
					foreach( $values as $attr_value ) { ?>
						<div class="product-attr-value multiple-value"><?php echo esc_html( $attr_value ); ?></div>
					<?php }
				} else { ?>
					<div class="product-attr-value multiple-value"><?php echo esc_html( $values[0] ); ?></div>
				<?php }
				?>
			</td>
		</tr>
	<?php endforeach; ?>
</table>
<?php if( $index > $max_attr_to_show_first ) {
	get_template_part( "templates/components/template-components-button", null, [
		'icon'			=> 'drplus-icon-bottom',
		'text'			=> '<span class="more">' . esc_html__( 'More', 'drplus' ) . '</span><span class="less">' . esc_html__( 'Less', 'drplus' ) . '</span>',
		'icon_align'	=> 'end',
		'align'			=> 'start',
		'small'			=> true,
		'transparent'	=> true,
		'classes'		=> ['product-show-more-attr-link'],
	] );
} ?>