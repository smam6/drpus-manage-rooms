<?php
/**
 * 04-version-check.php — run the shipped version-check.php inside the real site,
 * as the real administrator account from the production database.
 */
require __DIR__ . '/00-bootstrap.php';

$admin = get_user_by( 'login', 'aghazgaradmin' );
if ( ! $admin ) {
	$admins = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
	$admin  = $admins ? $admins[0] : null;
}
wp_set_current_user( $admin ? $admin->ID : 1 );
echo "user: ".wp_get_current_user()->user_login." (id {$admin->ID}) | manage_options: ".(current_user_can('manage_options')?'yes':'no')."\n";
echo str_repeat('=', 64)."\n";
include '/wordpress/wp-content/themes/dr-plus-child/inc/capacity/version-check.php';
