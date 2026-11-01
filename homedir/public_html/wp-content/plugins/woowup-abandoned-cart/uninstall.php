<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
global $wpdb;
wp_clear_scheduled_hook( 'woowup_abandonedcart_send_cart' );
delete_option('woowupApikey');
$abandonedCartRecoberyTable = $wpdb->prefix . WOOWUP_ABANDONEDCART_RECOBERY_TABLE;
$abandonedCartTable = $wpdb->prefix . WOOWUP_ABANDONEDCART_TABLE;
$wpdb->get_results( "DROP TABLE IF EXISTS {$abandonedCartRecoberyTable}");
$wpdb->get_results( "DROP TABLE IF EXISTS {$abandonedCartTable}");