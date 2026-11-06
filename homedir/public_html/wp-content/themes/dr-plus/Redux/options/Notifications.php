<?php
defined( 'ABSPATH' ) || exit;

Redux::set_section( // Notifications
	$opt_name,
	array(
		'title'			=> esc_html__( 'Notifications', 'drplus' ),
		'id'			=> 'notifications-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // notifications
				'id'		=> 'notifications',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Notifications status', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
		),
	)
);