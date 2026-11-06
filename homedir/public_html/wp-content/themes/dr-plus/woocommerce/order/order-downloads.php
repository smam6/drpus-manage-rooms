<?php
/**
 * Order Downloads.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-downloads.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.3.0
 */

use DrPlus\Components\Button;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="woocommerce-order-downloads">
	<?php if ( isset( $show_title ) ) : ?>
		<h2 class="woocommerce-order-downloads__title"><?php esc_html_e( 'Downloads', 'woocommerce' ); ?></h2>
	<?php endif; ?>

	<table class="woocommerce-table woocommerce-table--order-downloads shop_table shop_table_responsive order_details">
		<thead>
			<tr>
				<?php foreach ( wc_get_account_downloads_columns() as $column_id => $column_name ) : ?>
				<th class="<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
				<?php endforeach; ?>
			</tr>
		</thead>

		<?php foreach ( $downloads as $download ) : ?>
			<tr>
				<?php foreach ( wc_get_account_downloads_columns() as $column_id => $column_name ) : ?>
					<td class="<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
						<?php if( $column_id == 'download-remaining' || $column_id == 'download-expires' ) { ?>
							<span class="woocommerce_account_downloads_column_name"><?php echo esc_html( $column_name ); ?></span>
						<?php } ?>
						<?php
						if ( has_action( 'woocommerce_account_downloads_column_' . $column_id ) ) {
							do_action( 'woocommerce_account_downloads_column_' . $column_id, $download );
						} else {
							switch ( $column_id ) {
								case 'download-product':
									if ( $download['product_url'] ) {
										$_product = wc_get_product( $download['product_id'] );
										?>
										<a href="<?php echo esc_url( $download['product_url'] ); ?>" class="woocommerce-MyAccount-download-product-thumb" title="<?php echo esc_attr( $download['product_name'] ); ?>">
											<?php echo $_product->get_image() ?>
										</a>
										<a href="<?php echo esc_url( $download['product_url'] ); ?>" class="woocommerce-MyAccount-download-product-name" title="<?php echo esc_attr( $download['product_name'] ); ?>">
											<?php echo esc_html( $download['product_name'] ); ?>
										</a>
										<?php
									} else {
										?>
										<div class="woocommerce-MyAccount-download-product-thumb">
											<?php echo $_product->get_image() ?>
										</div>
										<div class="woocommerce-MyAccount-download-product-name">
											<?php echo esc_html( $download['product_name'] ); ?>
										</div>
										<?php
									}
									break;
								case 'download-file':
									Button::view( [
										'text' => esc_html__( 'Downlaod file', 'drplus' ),
										'link' => esc_url( $download['download_url'] ),
										'classes'	=> ['woocommerce-MyAccount-downloads-file', 'alt'],
										'type'		=> 'gray',
										'small'		=> true,
									] );
									break;
								case 'download-remaining':
									echo is_numeric( $download['downloads_remaining'] ) ? esc_html( $download['downloads_remaining'] ) : esc_html__( 'Infinite', 'woocommerce' );
									break;
								case 'download-expires':
									if ( ! empty( $download['access_expires'] ) ) {
										echo '<time datetime="' . esc_attr( date( 'Y-m-d', strtotime( $download['access_expires'] ) ) ) . '" title="' . esc_attr( strtotime( $download['access_expires'] ) ) . '">' . esc_html( date_i18n( 'l Y/m/d', strtotime( $download['access_expires'] ) ) ) . '</time>';
									} else {
										esc_html_e( 'Never', 'woocommerce' );
									}
									break;
							}
						}
						?>
					</td>
				<?php endforeach; ?>
			</tr>
		<?php endforeach; ?>
	</table>
</section>
