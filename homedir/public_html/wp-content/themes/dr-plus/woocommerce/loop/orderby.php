<?php
/**
 * Show options for ordering
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/orderby.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     9.7.0
 */

use DrPlus\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$primary_classes = ['content-area', 'site-content', 'row'];
$show_sidebar = is_active_sidebar( 'sidebar-shop' );
if( $show_sidebar ) {
	$primary_classes[] = 'content-area-with-sidebar';
}
?>
	<form id="sort-wrap" class="woocommerce-ordering archive-sort-form" method="get" action="">
		<?php
		get_template_part( "templates/components/template-components-select", null, [
			'wrap'	=> [
				'classes'	=> ['sort-wrap']
			],
			'label'		=> __( 'Sort:', 'drplus' ),
			'options'	=> $catalog_orderby_options,
			'value'		=> $orderby,
		] );
		?>
		<select name="orderby" class="orderby" aria-label="<?php esc_attr_e( 'Shop order', 'woocommerce' ); ?>">
			<?php foreach ( $catalog_orderby_options as $id => $name ) : ?>
				<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $orderby, $id ); ?>><?php echo esc_html( $name ); ?></option>
			<?php endforeach; ?>
		</select>
		<input type="hidden" name="paged" value="1" />
		<?php wc_query_string_form_fields( null, array( 'orderby', 'submit', 'paged', 'product-page' ) ); ?>
	</form>
</header>

<div id="primary" <?php echo Utils::prepare_html_classes( $primary_classes, true ) ?>>
	<?php
	do_action( 'drplus/wc/archive/start_primary' );
	?>
	<?php
	if( $show_sidebar ) {
		get_sidebar( 'shop' );
	}
	?>
	
	<div class="entry-container<?php echo $show_sidebar ? ' col-md-9 col-sm-12' : ' col-12' ?>">