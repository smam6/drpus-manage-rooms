<?php
/**
 * Displayed when no products are found matching the current query
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/no-products-found.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.8.0
 */

use DrPlus\Utils\Options;

defined( 'ABSPATH' ) || exit;

$options = Options::get_options( [
	'wc_empty_shop_text'	=> __( 'No product was found.', 'drplus' )
] );
?>
</header>
<div class="woocommerce-no-products-found woocommerce-page-content empty-page">
	<i class="empty-page-icon empty-shop-icon drplus-icon-shopping-cart"></i>
	<p class="empty-page-text empty-shop-text"><?php echo esc_html( $options['wc_empty_shop_text'] ) ?></p>
</div>
