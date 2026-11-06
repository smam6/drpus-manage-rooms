<?php
/**
 * My Account navigation
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/navigation.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

use DrPlus\Utils;
use DrPlus\Utils\Formatters;
use DrPlus\Utils\Notifications;
use DrPlus\Utils\Options;
use DrPlus\Utils\User;
use DrPlus\Utils\WC;
use Sheyda\Wallet\Utils\Settings as WalletSettings;
use SheydaWalletUtils as WalletUtils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$options = Options::get_options( [
	'show-notif-count'			=> true,
	'show_avatar_in_myaccount'	=> true,
	'use-outside-iran'			=> false,
] );
if( Utils::to_bool( $options['show-notif-count'] ) ) {
	$notif_count = Notifications::count_user_unread();
}
$nav_icons = WC::my_account_menu_link_icons();
$user = wp_get_current_user();
$user_phone = User::get_phone( $user->ID );

do_action( 'woocommerce_before_account_navigation' );
?>

<div class="myaccount-sidebar-wrap">
	<div class="myaccount-sidebar-header">
		<?php if( Utils::to_bool( $options['show_avatar_in_myaccount'] ) ) { ?>
			<div class="myaccount-user-avatar">
				<?php echo get_avatar( $user->ID, 64 ) ?>
			</div>
		<?php } ?>
		<div class="myaccount-user-name">
			<?php echo esc_html( $user->display_name ) ?>
		</div>
		<?php if( !empty( $user_phone ) ) { ?>
			<div class="myaccount-user-phone">
				<?php echo esc_html( Utils::to_bool( $options['use-outside-iran'] ) ? $user_phone : Formatters::phone( $user_phone ) ) ?>
			</div>
		<?php } ?>
		<?php if( WalletSettings::get_settings()['enable'] ) { ?>
			<?php
			$user_balance = WalletUtils::get_user_balance();
			if( !empty( $user_balance ) && isset( $user_balance->balance ) && isset( $user_balance->locked ) ) {
				?>
				<div class="myaccount-user-wallet-balance">
					<span class="myaccount-user-wallet-balance-label"><?php esc_html_e( 'Your balance:', 'drplus' ) ?></span>
					<span class="myaccount-user-wallet-balance-value"><?php printf( '%s %s', Formatters::price( absint( intval( $user_balance->balance ) - intval( $user_balance->locked ) ) ), get_woocommerce_currency_symbol() ) ?></span>
				</div>
				<?php
			}
		}
		?>
		<div class="myaccount-sidebar-header-bottom">
			<img src="<?php echo DRPLUS_URI . "/assets/images/science-group-icons.svg" ?>" alt="">
		</div>
	</div>
	<div class="myaccount-sidebar-body">
		<nav class="woocommerce-MyAccount-navigation" aria-label="<?php esc_html_e( 'Account pages', 'woocommerce' ); ?>">
			<ul>
				<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
					<li class="<?php echo wc_get_account_menu_item_classes( $endpoint ); ?>">
						<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>" <?php echo wc_is_current_account_menu_item( $endpoint ) ? 'aria-current="page"' : ''; ?> title="<?php echo esc_html( $label ); ?>">
							<?php if( !empty( $nav_icons[ $endpoint ] ) ) { ?>
								<i class="drplus-icon-<?php echo esc_attr( $nav_icons[ $endpoint ] ); ?>"></i>
							<?php } ?>
							<span class="woocommerce-MyAccount-navigation-link-text">
								<?php echo esc_html( $label ); ?>
								<?php if( Utils::to_bool( $options['show-notif-count'] ) && $endpoint == 'notifications' && $notif_count > 0 ) { ?>
									<div class="account-notif-count-wrap">
										<span class="account-notif-count"><?php echo esc_html( $notif_count ) ?></span>
									</div>
								<?php } ?>
							</span>
							<i class="woocommerce-MyAccount-navigation-active-icon drplus-icon-square-arrow-<?php echo is_rtl() ? 'right' : 'left' ?>"></i>
						</a>
					</li>
				<?php endforeach; ?>
				<li class="woocommerce-MyAccount-navigation-expand">
					<a href="#">
						<i class="drplus-icon-double-arrow-<?php echo is_rtl() ? 'left' : 'right' ?>"></i>
						<span class="woocommerce-MyAccount-navigation-expand-minimize">
							<?php esc_html_e( 'Minimize', 'drplus' ) ?>
						</span>
						<span class="woocommerce-MyAccount-navigation-expand-maximize">
							<?php esc_html_e( 'Maximize', 'drplus' ) ?>
						</span>
					</a>
				</li>
			</ul>
		</nav>
	</div>
</div>
<div class="myaccount-sidebar-mobile-expand">
	<i class="drplus-icon-double-arrow-<?php echo is_rtl() ? 'left' : 'right' ?>"></i>
</div>


<?php do_action( 'woocommerce_after_account_navigation' ); ?>
