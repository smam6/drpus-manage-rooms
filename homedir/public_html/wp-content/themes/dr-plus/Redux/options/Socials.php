<?php
defined( 'ABSPATH' ) || exit;

Redux::set_section( // Socials
	$opt_name,
	array(
		'title'			=> esc_html__( 'Socials', 'drplus' ),
		'id'			=> 'socials-section',
		'subsection'	=> true,
		'fields'		=> array(
			[
				'id'	=> 'socials-notice',
				'type'	=> 'info',
				'desc'	=> __( "This section is designed to be displayed in the WordPress social media widget.", 'drplus' ),
				'style'	=> 'info',
				'icon'	=> 'el-icon-info-sign',
			],
			[ // socials
				'id'		=> 'socials',
				'type'			=> 'repeater',
				'title'			=> __( 'Social items', 'drplus' ),
				'compiler'		=> true,
				'group_values'	=> true,
				'init_empty'	=> true,
				'fields'		=> [
					[
						'id'	=> 'social_name',
						'type'	=> 'text',
						'title'	=> esc_html__( "Title", 'drplus' ),
					],
					[
						'id'			=> 'social_icon',
						'type'			=> 'icon_select',
						'title'			=> esc_html__( 'Icon', 'drplus' ),
						'compiler'		=> true,
						'default'		=> 'drplus-icon-instagram',
						'enqueue_frontend'	=> false,
						'stylesheet'	=> [
							[
								'url'		=> DRPLUS_URI . 'assets/css/iconly.min.css',
								'title'		=> __( 'Doctor plus icons', 'drplus' ),
								'prefix'	=> 'drplus-icon',
							],
						],
					],
					[ // social_link
						'id'		=> 'social_link',
						'type'		=> 'text',
						'title'		=> esc_html__( 'URL', 'drplus' ),
						'compiler'	=> true,
					],
				],
			],
		),
	)
);