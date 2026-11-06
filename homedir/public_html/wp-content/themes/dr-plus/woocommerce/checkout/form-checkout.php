<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_book_order = false;
$is_plan_order = false;
$is_topup_order = false;
foreach( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
	if( !empty( $cart_item['is_booking'] ) ) {
		$book_data = $cart_item['book_data'];
		$is_book_order = true;
		break;
	} else if( !empty( $cart_item['is_plan'] ) ) {
		$plan_data = $cart_item['plan_data'];
		$is_plan_order = true;
		break;
	} else if( !empty( $cart_item['is_wallet_topup'] ) ) {
		$is_topup_order = true;
		break;
	}
}

if( $is_book_order ) {
	?>
	<div class="drplus-booking-checkout">
	<?php

	do_action( 'drplus/booking/checkout/before_form', $book_data );
} else if( $is_plan_order ) {
	?>
	<div class="drplus-plan-checkout">
	<?php

	do_action( 'drplus/plan/checkout/before_form', $plan_data );
} else if( $is_topup_order ) {
	?>
	<div class="drplus-wallet-topup-checkout">
	<?php
	do_action( 'drplus/wallet/topup/checkout/before_form' );
}

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

	<?php if ( $checkout_fields = $checkout->get_checkout_fields() ) : ?>

		<?php if( $is_book_order || $is_plan_order || $is_topup_order ) { ?>
			<div id="wc_customer_details_wrapper" style="display: none">
		<?php } ?>

			<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

			<div class="col2-set" id="customer_details">
				<?php if( isset( $checkout_fields['billing'] ) ) { ?>
					<div class="col-1">
						<?php do_action( 'woocommerce_checkout_billing' ); ?>
					</div>
				<?php } ?>
	
				<?php if( isset( $checkout_fields['shipping'] ) ) { ?>
					<div class="col-2">
						<?php do_action( 'woocommerce_checkout_shipping' ); ?>
					</div>
				<?php } ?>
			</div>

			<?php do_action( 'woocommerce_checkout_after_customer_details', $is_book_order ); ?>

		<?php if( $is_book_order || $is_plan_order || $is_topup_order ) { ?>
			</div>
		<?php } ?>

	<?php endif; ?>
	
	<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
	
	<div id="order_review-wrap">
		<h3 id="order_review_heading"><?php $is_book_order || $is_plan_order || $is_topup_order ? esc_html_e( 'Invoice', 'drplus' ) : esc_html_e( 'Your order', 'woocommerce' ); ?></h3>
		
		<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

		<div id="order_review" class="woocommerce-checkout-review-order">
			<?php do_action( 'woocommerce_checkout_order_review' ); ?>
			<?php if( $is_book_order || $is_plan_order || $is_topup_order ) {
				woocommerce_checkout_payment();
			} ?>
		</div>
	</div>
	<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>

<?php
if( $is_book_order || $is_plan_order || $is_topup_order ) {
	?>
	</div>
	<?php
}
