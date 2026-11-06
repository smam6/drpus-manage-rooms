<?php
defined( 'ABSPATH' ) || exit;

Redux::set_section( // SMS general settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'SMS general settings', 'drplus' ),
		'id'			=> 'sms-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // sms
				'id'		=> 'sms',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'SMS', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'desc'		=> esc_html__( 'Enable or disable the SMS service.', 'drplus' ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[
				'id'	=> 'sms_info-notice',
				'type'	=> 'info',
				'desc'	=> sprintf( __( "To configure the SMS, Please go to <a href='%s'>SMS Settings</a>", 'drplus' ), admin_url( "admin.php?page=drplus-sms" ) ),
				'style'	=> 'info',
				'icon'	=> 'el-icon-info-sign',
			]
		),
	)
);