<?php

use DrPlus\Utils;
use DrPlus\Utils\Auth;
use DrPlus\Utils\Options;

if( !function_exists( "drplus_auth_screen" ) ) {
	function drplus_auth_screen() {
		if( empty( $_GET['login'] ) || !Utils::to_bool( $_GET['login'] ) || is_admin() || is_user_logged_in() ) return;

		$options = Options::get_options( [
			'auth'			=> true,
			'auth_sms'		=> true,
			'auth_email'	=> true,
		] );
		if( !Utils::to_bool( $options['auth'] ) || ( !Utils::to_bool( $options['auth_sms'] ) && !Utils::to_bool( $options['auth_email'] ) ) ) return;

		// Redirect to https login if forced to use SSL
		if (force_ssl_admin() && !is_ssl()) {
			if( strpos( $_SERVER['REQUEST_URI'], 'http' ) === 0 ) {
				wp_redirect( set_url_scheme( $_SERVER['REQUEST_URI'], 'https' ) );
				exit();
			} else {
				wp_redirect( 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
				exit();
			}
		}

		Utils::maybe_define( 'DRPLUS_LOGIN', true );

		get_template_part( "templates/auth/templates-auth" );
		die;
	}
}
add_action( 'wp_loaded', 'drplus_auth_screen', 1 );

if( !function_exists( "drplus_wp_robots_auth" ) ) {
	function drplus_wp_robots_auth( array $robots ) {
		if( Auth::is_login() ) {
			$robots['noarchive'] = true;
		}
		return $robots;
	}
}
add_filter( 'wp_robots', 'drplus_wp_robots_auth' );