<?php
namespace DrPlus\Shortcodes;

use DrPlus\Utils;

if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( "\Drplus\Shortcodes\Booking" ) ) {
	class Booking {
		public static function view() {
			if( !Utils::is_wc_active() ) {
				if( is_user_logged_in() && current_user_can( 'administrator' ) ) {
					return sprintf( __( 'The Doctor Plus appointment booking system is based on WooCommerce. <a href="%s" target="_blank">Install and activate WooCommerce from the WordPress dashboard.</a>', 'drplus' ), admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ) );
				}
			}
			ob_start();
			get_template_part( 'templates/booking/template-booking-base', null );
			return ob_get_clean();

		}
	}
}
add_shortcode( 'drplus_booking', [Booking::class, 'view'] );