<?php

use DrPlus\Components\Button;
use DrPlus\Model\Wishlist;
use DrPlus\Utils;
use DrPlus\Utils\Options;
use DrPlus\Utils\User;
use DrPlus\Utils\Wishlist as UtilsWishlist;

if( !defined( 'ABSPATH' ) ) exit;

$search_text = sanitize_text_field( get_query_var( 'search' ) );
$products = [];

$options = Options::get_options( [
	'wishlist_ppp'	=> 6,
] );
$ppp = Utils::absint_pro( $options['wishlist_ppp'], 1 );
$current_page = !empty( $_GET['wishlist-page'] ) ? Utils::convert_chars( $_GET['wishlist-page'], true, 'absint' ) : 1;
$current_page = $current_page < 1 ? 1 : $current_page;
$offset = ( $current_page - 1 ) * $ppp;
$remove_id = Utils::convert_chars( $_GET['remove'] ?? 0, true, 'absint' );

if( !empty( $remove_id ) ) {
	UtilsWishlist::remove_from_wishlist( $remove_id );
}

if( !$search_text ) {
	$products = User::get_wishlist_products( get_current_user_id(), 'products', [
		'limit'		=> $ppp,
		'offset'	=> $offset,
		'paginate'	=> true,
	] );
} else {
	$wishlist_table = (new Wishlist)->getTable();
	global $wpdb;
	$query = $wpdb->prepare( "SELECT `{$wpdb->posts}`.`ID` FROM `{$wpdb->posts}` INNER JOIN `{$wishlist_table}` ON `{$wpdb->posts}`.`ID`=`{$wishlist_table}`.`product_id` WHERE `{$wishlist_table}`.`user_id`=%d AND `{$wpdb->posts}`.`post_title` LIKE %s", [
		get_current_user_id(),
		'%' . $wpdb->esc_like( $search_text ) . '%',
	] );
	$product_ids = $wpdb->get_col( $query );
	if( !empty( $product_ids ) ) {
		$products = wc_get_products( [
			'include'	=> $product_ids,
			'limit'		=> $ppp,
			'offset'	=> $offset,
			'paginate'	=> true,
		] );
	}
}
?>

<?php if( $products ) { ?>
	<div id="wishlist-content">
		<form action="" method="get" id="wishlist-search">
			<label class="screen-reader-text"><?php esc_html_e( 'Search for:', 'drplus' ) ?></label>
			<input type="search" name="search" class="search-field" placeholder="<?php echo esc_attr_x( 'Search in wishlist', 'placeholder', 'drplus' ) ?>" value="<?php echo esc_attr( $search_text ) ?>" title="<?php echo esc_attr_x( 'Search for:', 'label', 'drplus' ) ?>" />
			<button type="submit" class="button" title="<?php echo esc_attr_e( "Search", 'drplus' ) ?>"><i class="drplus-icon-search-2"></i></button>
		</form>

		<div id="wishlist-items">
			<?php
			foreach( $products->products as $product ) {
				$product_link = get_permalink( $product->get_id() );
				?>
				<div <?php wc_product_class( 'wishlist-item', $product ) ?>>
					<a href="<?php echo esc_url( $product_link ) ?>" class="wishlist-item-image" title="<?php echo esc_html( $product->get_name() ) ?>">
						<?php echo $product->get_image() ?>
					</a>

					<div class="wishlist-item-texts">
						<h3 class="wishlist-item-title">
							<a href="<?php echo esc_url( $product_link ) ?>" class="wishlist-item-link" title="<?php echo esc_html( $product->get_name() ) ?>"><?php echo esc_html( $product->get_name() ) ?></a>
						</h3>
						<div class="wishlist-item-price">
							<?php echo $product->get_price_html() ?>
						</div>
					</div>

					<div class="wishlist-item-buttons">
						<?php
						Button::view( [
							'link'		=> $product_link,
							'icon'		=> 'drplus-icon-eye',
							'small'		=> true,
							'type'		=> 'gray',
							'title'		=> __( 'View', 'drplus' ),
							'popup'		=> __( 'View', 'drplus' ),
							'classes'	=> ["wishlist-btn-view"],
						] );

						Button::view( [
							'link'		=> add_query_arg( "remove", $product->get_id() ),
							'icon'		=> 'drplus-icon-close',
							'type'		=> 'gray',
							'small'		=> true,
							'title'		=> __( 'Remove', 'drplus' ),
							'popup'		=> __( 'Remove', 'drplus' ),
							'classes'	=> ["wishlist-btn-remove"],
						] );
						?>
					</div>
				</div>
			<?php } ?>
		</div>
		<?php
		if( $products->max_num_pages > 1 ) {
			get_template_part( 'templates/archives/template-archives-pagination', 'custom', [
				'query'				=> $products,
				'paged'				=> $current_page,
				'query_arg_name'	=> 'wishlist-page',
			] );
		}
		?>
	</div>
<?php } else { ?>
	<div class="empty-page">
		<i class="empty-page-icon empty-cart-icon drplus-icon-heart"></i>
		<div class='empty-page-text'>
			<?php esc_html_e( Options::get_options( [
				'wc_empty_wishlist_text'	=> esc_html__( 'There are no products in wishlist.', 'drplus' )
			] )['wc_empty_wishlist_text'], 'woocommerce' ) ?>
		</div>
		<?php
		get_template_part( 'templates/components/template-components-button', null, [
			'text'	=> apply_filters( 'woocommerce_return_to_shop_text', __( 'Return to Shop', 'drplus' ) ),
			'link'	=> apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ),
			'align'	=> 'center',
			'small'	=> true
		] );
		?>
	</div>
<?php } ?>