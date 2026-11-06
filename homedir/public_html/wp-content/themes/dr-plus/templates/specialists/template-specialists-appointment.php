<?php

use DrPlus\Utils;

$order_id = Utils::convert_chars( $_GET['order_id'], true, 'absint' );
$order = wc_get_order( $order_id );
if( !empty( $order ) ) {
	foreach( $order->get_meta_data() as $meta ) {
		if( $meta->key == '_booking_data' ) {
			$book_data = $meta->value;
			break;
		}
	}
}

if( empty( $order ) || empty( $book_data ) || $book_data['specialist_id'] != $args['specialist_id'] ) { ?>
	<div class="drplus-specialist-app-empty-order">
		<?php echo esc_html__( 'You have no access to this order.', 'drplus' ) ?>
	</div>
	<?php return; ?>
<?php }
get_template_part( "templates/booking/template-booking-step", 'receipt', [
	'book_data'			=> $book_data,
	'order'				=> $order,
	'show_back_btn'		=> true,
	'view_type'			=> 'specialist',
] );