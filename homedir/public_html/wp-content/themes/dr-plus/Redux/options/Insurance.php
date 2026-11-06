<?php
defined( 'ABSPATH' ) || exit;

Redux::set_section( // Insurance
	$opt_name,
	array(
		'title'			=> esc_html__( 'Insurance', 'drplus' ),
		'id'			=> 'insurance-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // insurance
				'id'		=> 'insurance',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Insurance status', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
		),
	)
);