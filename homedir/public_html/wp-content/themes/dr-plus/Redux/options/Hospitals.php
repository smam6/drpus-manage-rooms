<?php

use DrPlus\Utils;

defined( 'ABSPATH' ) || exit;

Redux::set_section( // Archives settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'Archives settings', 'drplus' ),
		'id'			=> 'hospitals-archive-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // archive_hospital_breadcrumb
				'id'		=> 'archive_hospital_breadcrumb',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show breadcrumb', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // archive_hospital_show_title
				'id'		=> 'archive_hospital_show_title',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show archive title', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // archive_hospital_title_icon
				'id'			=> 'archive_hospital_title_icon',
				'type'			=> 'icon_select',
				'title'			=> esc_html__( 'Archive title icon', 'drplus' ),
				'compiler'		=> true,
				'default'		=> 'drplus-icon-stethoscope',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> DRPLUS_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Doctor plus icons', 'drplus' ),
						'prefix'	=> 'drplus-icon',
					],
				],
				'required'		=> [
					['archive_hospital_show_title','=',true]
				]
			],
			[ // archive_hospital_show_sidebar
				'id'		=> 'archive_hospital_show_sidebar',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show archive sidebar', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // archive_hospital_sidebar
				'id'		=> 'archive_hospital_sidebar',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Archive sidebar', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Blog sidebar', 'drplus' ) ),
				'data'		=> 'sidebars',
				'default'	=> 'archive_hospital',
				'required'	=> [
					['archive_hospital_show_sidebar','=',true],
				]
			],
			[ // archive_hospital_desktop_cols
				'id'		=> 'archive_hospital_desktop_cols',
				'type'		=> 'spinner',
				'title'		=> esc_html__( "Desktop columns", 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '3' ),
				'min'		=> 1,
				'max'		=> 6,
				'default'	=> 3,
			],
			[ // archive_hospital_desktop_gap
				'id'		=> 'archive_hospital_desktop_gap',
				'type'		=> 'spinner',
				'title'		=> esc_html__( "Desktop gap (px)", 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '24' ),
				'min'		=> 1,
				'max'		=> 64,
				'default'	=> 24,
			],
			[ // archive_hospital_tablet_cols
				'id'		=> 'archive_hospital_tablet_cols',
				'type'		=> 'spinner',
				'title'		=> esc_html__( "Tablet columns", 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '2' ),
				'min'		=> 1,
				'max'		=> 6,
				'default'	=> 2,
			],
			[ // archive_hospital_tablet_gap
				'id'		=> 'archive_hospital_tablet_gap',
				'type'		=> 'spinner',
				'title'		=> esc_html__( "Tablet gap (px)", 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '16' ),
				'min'		=> 1,
				'max'		=> 64,
				'default'	=> 16,
			],
			[ // archive_hospital_mobile_cols
				'id'		=> 'archive_hospital_mobile_cols',
				'type'		=> 'spinner',
				'title'		=> esc_html__( "Mobile columns", 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '1' ),
				'min'		=> 1,
				'max'		=> 6,
				'default'	=> 1,
			],
			[ // archive_hospital_mobile_gap
				'id'		=> 'archive_hospital_mobile_gap',
				'type'		=> 'spinner',
				'title'		=> esc_html__( "Mobile gap (px)", 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '16' ),
				'min'		=> 1,
				'max'		=> 64,
				'default'	=> 16,
			],
		),
	)
);

Redux::set_section( // Archive items settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'Archive items settings', 'drplus' ),
		'id'			=> 'hospitals-archive-items-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // archive_hospital_title_tag
				'id'		=> 'archive_hospital_title_tag',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Archive item title tag', 'drplus' ), 
				'subtitle'	=> sprintf( esc_html__( "Default: %s", 'drplus' ), __( "H2", 'drplus' ) ),
				'options'	=> Utils::custom_tags(),
				'default'	=> 'h2',
			],
			[ // archive_hospital_show_subtitle
				'id'		=> 'archive_hospital_show_subtitle',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show archive item subtitle', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
			],
			[ // archive_hospital_show_address
				'id'		=> 'archive_hospital_show_address',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show archive item address', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
			],
			[ // archive_hospital_show_read_more
				'id'		=> 'archive_hospital_show_read_more',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show read more button', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
			],
			[ // archive_hospital_read_more_text
				'id'		=> 'archive_hospital_read_more_text',
				'type'		=> 'text',
				'title'		=> __( 'View button text', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'View details', 'drplus' ) ),
				'compiler'	=> true,
				'default'	=> __( 'View details', 'drplus' ),
				'required'	=> [
					['archive_hospital_show_read_more','=',true]
				],
			],
			[ // archive_hospital_read_more_icon
				'id'			=> 'archive_hospital_read_more_icon',
				'type'			=> 'icon_select',
				'title'			=> esc_html__( 'Read more icon', 'drplus' ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), is_rtl() ? 'drplus-icon-arrow-up-left-square' : 'drplus-icon-arrow-up-right-square' ),
				'compiler'		=> true,
				'default'		=> is_rtl() ? 'drplus-icon-arrow-up-left-square' : 'drplus-icon-arrow-up-right-square',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> DRPLUS_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Doctor plus icons', 'drplus' ),
						'prefix'	=> 'drplus-icon',
					],
				],
				'required'		=> [
					['archive_hospital_show_read_more','=',true]
				],
			],
		)
	)
);

Redux::set_section( // Single hospital settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'Single hospital settings', 'drplus' ),
		'id'			=> 'hospitals-single-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // single_hospital_use_content_style
				'id'			=> 'single_hospital_use_content_style',
				'type'			=> 'switch',
				'title'			=> esc_html__( 'Use default styles for content', 'drplus' ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Disable', 'drplus' ) ),
				'description'	=> __( 'Check the checkbox to use default styles for content(background, border and others). If you want just show the content, uncheck this', 'drplus' ),
				'on'			=> esc_html__( 'Enable', 'drplus' ),
				'off'			=> esc_html__( 'Disable', 'drplus' ),
				'default'		=> false,
			],
			[ // single_hospital_show_breadcrumb
				'id'		=> 'single_hospital_show_breadcrumb',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show breadcrumb', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
			],
			[ // single_hospital_show_head
				'id'		=> 'single_hospital_show_head',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show hospital header', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
			],
			[ // single_hospital_head_icon
				'id'		=> 'single_hospital_head_icon',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show head icon', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['single_hospital_show_head','=',true],
				]
			],
			[ // single_hospital_head_title
				'id'		=> 'single_hospital_head_title',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show head title', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['single_hospital_show_head','=',true],
				]
			],
			[ // single_hospital_head_subtitle
				'id'		=> 'single_hospital_head_subtitle',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show head subtitle', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['single_hospital_show_head','=',true],
				]
			],
			[ // single_hospital_head_address
				'id'		=> 'single_hospital_head_address',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show head address', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['single_hospital_show_head','=',true],
				]
			],
			[ // single_hospital_show_gallery
				'id'		=> 'single_hospital_show_gallery',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show gallery', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
			],
			[ // single_hospital_show_map
				'id'		=> 'single_hospital_show_map',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show map', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
			],
			[ // single_hospital_show_specialists
				'id'		=> 'single_hospital_show_specialists',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show specialists', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
			],
		)
	)
);