<?php

use DrPlus\Utils;
use DrPlus\Utils\Options;
use DrPlus\Utils\Sanitizers;
use MJ\Whitebox\Utils as WhiteboxUtils;

if( !defined( 'ABSPATH' ) ) exit;
if( !WhiteboxUtils::is_wc_active() ) return;

$args = Utils::check_default( $args, [
	'call_mode'				=> 'template', // template | elementor
	'cart-text'				=> '',
	'cart-icon'				=> '',
	'icon-align'			=> 'start',
	'show-mini-cart'		=> true,
	'show-cart-count'		=> true,
	'mobile_mode'			=> false,
	'minicart_align'		=> 'p-start'
] );
$cart_count = Utils::get_cart_count();

$options = Options::get_options( [
	'mini-cart-style'		=> 'style_1',
	'mini-cart-title'		=> esc_html__( "Your cart", 'drplus' ),
	'mini-cart-title-icon'	=> 'drplus-icon-bag-2',
] );
if( !empty( $options['mini-cart-title-icon'] ) ) $options['mini-cart-title-icon'] = Sanitizers::icon( $options['mini-cart-title-icon'], 'header-mini-cart-title-icon' );

$args['mini-cart-style'] = $options['mini-cart-style'];
$args['mini-cart-title'] = $options['mini-cart-title'];
$args['mini-cart-title-icon'] = $options['mini-cart-title-icon'];

$wrap_classes = ['header-cart-wrap', 'header-action', 'header-action-cart'];
if( $args['call_mode'] == 'template' ) {
	if( $args['mobile_mode'] ) {
		$wrap_classes = array_merge( $wrap_classes, ['hide-desktop', 'hide-tablet'] );
	} else {
		$wrap_classes[] = 'hide-mobile';
	}
}
if( $args['mobile_mode'] ) $wrap_classes[] = 'mobile-mode';
$wrap_classes[] = 'mini-cart-' . $args['mini-cart-style'];
?>
<div class="<?php echo Utils::prepare_html_classes( $wrap_classes ) ?>">
	<a href="<?php echo wc_get_cart_url() ?>" class="header-action-btn header-cart-btn">
		<?php if( Utils::to_bool( $args['show-cart-count'] ) ) { ?>
			<div class="header-cart-count-wrap">
				<span class="cart-count"><?php echo $cart_count === 0 ? "" : $cart_count ?></span>
			</div>
		<?php } ?>
		<?php
		if( $args['cart-icon'] && $args['icon-align'] == 'start' ) {
			echo $args['cart-icon'];
		}
		if( $args['cart-text'] ) {
			?>
			<span class="header-mini-cart-text">
				<?php echo $args['cart-text']; ?>
			</span> <?php
		}
		if( $args['cart-icon'] && $args['icon-align'] == 'end' ) {
			echo $args['cart-icon'];
		}
		?>
	</a>
	
	<?php if( Utils::to_bool( $args['show-mini-cart'] ) ) { ?>
		<div class="header-mini-cart-wrap header-popover <?php echo $args['minicart_align'] ?>">
			<?php if( $args['mini-cart-style'] == 'style_2' && !empty( $args['mini-cart-title'] ) ) { ?>
				<div class="header-mini-cart-title-wrap">
					<?php if( !empty( $args['mini-cart-title-icon'] ) ) {
						echo $args['mini-cart-title-icon'];
					} ?>
					<span class="header-mini-cart-title"><?php echo esc_html( $args['mini-cart-title'] ) ?></span>
				</div>
			<?php } ?>
			<div class="header-mini-cart-content">
				<?php woocommerce_mini_cart( [
					'mini-cart-style'		=> $args['mini-cart-style'],
				] ) ?>
			</div>
		</div>
	<?php } ?>
</div>