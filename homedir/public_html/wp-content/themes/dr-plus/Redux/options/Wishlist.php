<?php
defined( 'ABSPATH' ) || exit;

Redux::set_section( // Wishlist
	$opt_name,
	array(
		'title'			=> esc_html__( 'Wishlist', 'drplus' ),
		'id'			=> 'wishlist-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // wishlist
				'id'		=> 'wishlist',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Wishlist status', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // wishlist_ppp
				'id'		=> 'wishlist_ppp',
				'type'		=> 'spinner',
				'title'		=> esc_html__( "Products per page", 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '6' ),
				'min'		=> 1,
				'max'		=> 100,
				'default'	=> 6,
				'required'	=> [
					['wishlist','=',true]
				]
			],
		),
	)
);