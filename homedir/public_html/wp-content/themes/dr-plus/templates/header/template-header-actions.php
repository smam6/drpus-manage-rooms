<?php

use DrPlus\Components\Button;
use DrPlus\Utils;
use DrPlus\Utils\Options;
use DrPlus\Utils\Sanitizers;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'mobile_mode'	=> false
]);

$home_url = home_url();

$options = Options::get_options( [
	'show-cart'				=> true,
	'cart-text'				=> '',
	'cart-icon'				=> 'drplus-icon-shopping-cart',
	'show-mini-cart'		=> true,
	'show-cart-count'		=> true,
	
	'show-account-btn'					=> true, // guest
	'account-btn-text-type'				=> 'none', // guest
	'account-btn-text'					=> __( 'Account', 'drplus' ), // guest
	'account-btn-attachment-type'		=> 'icon', // guest
	'account-btn-icon'					=> 'drplus-icon-user', // guest
	'account-btn-link-newtab'			=> false, // guest
	'account-btn-user-text-type'		=> 'username', // user
	'account-btn-user-text'				=> __( 'Account', 'drplus' ), // user
	'show-account-btn-user'				=> true, // user
	'account-btn-user-attachment-type'	=> 'avatar', // user
	'account-btn-user-icon'				=> 'drplus-icon-user', // user
	'account-btn-user-link-newtab'		=> false, // user

	'show-header-action-btn'		=> true,
	'header-action-btn-text'		=> esc_html__( 'Request appointment', 'drplus' ),
	'header-action-btn-icon'		=> is_rtl() ? 'drplus-icon-arrow-up-left-square' : "drplus-icon-arrow-up-right-square",
	'header-action-btn-link'		=> home_url( 'booking' ),
	'header-action-btn-link-guest'	=> home_url( "?login=true" ),
	'header-action-btn-link-newtab'	=> false,
] );

$is_user_logged_in = is_user_logged_in();

if( Utils::to_bool( $options['show-cart'] ) && Utils::is_wc_active() ) {
	get_template_part( "templates/header/template-header-action", 'mini_cart', [
		'cart-text'				=> $options['cart-text'],
		'cart-icon'				=> Sanitizers::icon( $options['cart-icon'], 'header-action-icon header-cart-icon' ),
		'show-mini-cart'		=> $options['show-mini-cart'],
		'show-cart-count'		=> $options['show-cart-count'],
		'mobile_mode'			=> $args['mobile_mode'],
		'minicart_align'		=> 'p-end'
	] );
}
if( ( !$is_user_logged_in && Utils::to_bool( $options['show-account-btn'] ) ) || ( $is_user_logged_in && Utils::to_bool( $options['show-account-btn-user'] ) ) ) {
	get_template_part( "templates/header/template-header-action", 'account_btn', [
		'call_mode'						=> 'template',
		'mobile_mode' 					=> $args['mobile_mode'],
		'account-btn-attachment-type'	=> !$is_user_logged_in ? $options['account-btn-attachment-type'] : $options['account-btn-user-attachment-type'],
		'account-btn-icon'				=> !$is_user_logged_in ? $options['account-btn-icon'] : $options['account-btn-user-icon'],
		'account-btn-link-newtab'		=> !$is_user_logged_in ? $options['account-btn-link-newtab'] : $options['account-btn-user-link-newtab'],
	] );
}
if( !$args['mobile_mode'] && Utils::to_bool( $options['show-header-action-btn'] ) ) {
	$action_btn_args = [
		'type'			=> 'primary',
		'text'			=> $options['header-action-btn-text'],
		'icon'			=> $options['header-action-btn-icon'],
		'icon_align'	=> 'end',
		'align'			=> 'end',
		'link'			=> is_user_logged_in() ? $options['header-action-btn-link'] : $options['header-action-btn-link-guest'],
		'new_tab'		=> $options['header-action-btn-link-newtab'],
		'classes'		=> ['header-action', 'header-header-action-btn'],
	];
	if( $action_btn_args['new_tab'] ) {
		$account_btn_args['atts'] = [
			'rel'	=> 'noopener noreferrer'
		];
	}
	Button::view( $action_btn_args );
}
?>