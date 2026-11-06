<?php

use DrPlus\Utils;

defined( 'ABSPATH' ) || exit;

$fonts = Utils::fonts();
$weights = [
	'400'	=> __( 'Normal', 'drplus' ),
	'100'	=> __( 'Thin', 'drplus' ),
	'200'	=> __( 'ExtraLight', 'drplus' ),
	'300'	=> __( 'Light', 'drplus' ),
	'500'	=> __( 'Medium', 'drplus' ),
	'600'	=> __( 'SemiBold', 'drplus' ),
	'700'	=> __( 'Bold', 'drplus' ),
	'800'	=> __( 'ExtraBold', 'drplus' ),
	'900'	=> __( 'Black', 'drplus' ),
];

$fonts_fields = [];
$is_rtl = is_rtl();
foreach( $fonts as $font => $font_name ) {
	$fonts_fields[] = [
		'id'		=> "font_{$font}",
		'title'		=> $is_rtl ? $font_name['fa'] : $font_name['en'],
		'type'		=> 'switch',
		'on'		=> esc_html__( 'Enabled', 'drplus' ),
		'off'		=> esc_html__( 'Disabled', 'drplus' ),
		'default'	=> in_array( $font, Utils::default_active_fonts() ),
	];
}

Redux::set_section( // Active Fonts
	$opt_name,
	array(
		'title'			=> esc_html__( 'Active Fonts', 'drplus' ),
		'id'			=> 'fonts-typography-section',
		'subsection'	=> true,
		'fields'		=> $fonts_fields
	)
);

Redux::set_section( // Main
	$opt_name,
	array(
		'title'			=> esc_html__( 'Main', 'drplus' ),
		'id'			=> 'main-typography-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // main-typography
				'id'				=> 'main-typography',
				'type'				=> 'typography',
				'title'				=> esc_html__( 'Main font', 'drplus' ),
				'compiler'			=> true,
				'fonts'				=> $fonts,
				'font-size-unit'	=> 'px',
				'line-height-unit'	=> '%',
				'weights'			=> $weights,
				'subsets'			=> false,
				'color'				=> false,
				'preview'			=> false,
				'text-align'		=> false,
				'default'			=> [
					'font-family'	=> 'IRANYekanXFANum',
				],
			],
			[ // second-typography
				'id'				=> 'second-typography',
				'type'				=> 'typography',
				'title'				=> esc_html__( 'Second font', 'drplus' ),
				'compiler'			=> true,
				'fonts'				=> $fonts,
				'font-size-unit'	=> 'px',
				'line-height-unit'	=> '%',
				'weights'			=> $weights,
				'subsets'			=> false,
				'color'				=> false,
				'preview'			=> false,
				'text-align'		=> false,
			],
			[ // breadcrumb-typography
				'id'				=> 'breadcrumb-typography',
				'type'				=> 'typography',
				'title'				=> esc_html__( 'Breadcrumb', 'drplus' ),
				'compiler'			=> true,
				'fonts'				=> $fonts,
				'units'				=> 'rem',
				'line-height-unit'	=> '%',
				'weights'			=> $weights,
				'subsets'			=> false,
				'color'				=> false,
				'preview'			=> false,
				'text-align'		=> false,
			],
			[ // post-title-single-typography
				'id'				=> 'archive-title-typography',
				'type'				=> 'typography',
				'title'				=> esc_html__( 'Page/Archive title', 'drplus' ),
				'compiler'			=> true,
				'fonts'				=> $fonts,
				'units'				=> 'rem',
				'line-height-unit'	=> '%',
				'weights'			=> $weights,
				'subsets'			=> false,
				'color'				=> false,
				'preview'			=> false,
				'text-align'		=> false,
			],
			[ // post-title-typography
				'id'				=> 'post-title-typography',
				'type'				=> 'typography',
				'title'				=> esc_html__( 'Post title (single)', 'drplus' ),
				'compiler'			=> true,
				'fonts'				=> $fonts,
				'units'				=> 'rem',
				'line-height-unit'	=> '%',
				'weights'			=> $weights,
				'subsets'			=> false,
				'color'				=> false,
				'preview'			=> false,
				'text-align'		=> false,
			],
		),
	)
);

Redux::set_section( // Headings
	$opt_name,
	array(
		'title'			=> esc_html__( 'Headings', 'drplus' ),
		'id'			=> 'headings-typography-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // h1-typography
				'id'				=> 'h1-typography',
				'type'				=> 'typography',
				'title'				=> esc_html__( 'H1', 'drplus' ),
				'compiler'			=> true,
				'fonts'				=> $fonts,
				'font-size-unit'	=> 'rem',
				'line-height-unit'	=> '%',
				'weights'			=> $weights,
				'subsets'			=> false,
				'color'				=> false,
				'preview'			=> false,
				'text-align'		=> false,
			],
			[ // h2-typography
				'id'				=> 'h2-typography',
				'type'				=> 'typography',
				'title'				=> esc_html__( 'H2', 'drplus' ),
				'compiler'			=> true,
				'fonts'				=> $fonts,
				'font-size-unit'	=> 'rem',
				'line-height-unit'	=> '%',
				'weights'			=> $weights,
				'subsets'			=> false,
				'color'				=> false,
				'preview'			=> false,
				'text-align'		=> false,
			],
			[ // h3-typography
				'id'				=> 'h3-typography',
				'type'				=> 'typography',
				'title'				=> esc_html__( 'H3', 'drplus' ),
				'compiler'			=> true,
				'fonts'				=> $fonts,
				'font-size-unit'	=> 'rem',
				'line-height-unit'	=> '%',
				'weights'			=> $weights,
				'subsets'			=> false,
				'color'				=> false,
				'preview'			=> false,
				'text-align'		=> false,
			],
			[ // h4-typography
				'id'				=> 'h4-typography',
				'type'				=> 'typography',
				'title'				=> esc_html__( 'H4', 'drplus' ),
				'compiler'			=> true,
				'fonts'				=> $fonts,
				'font-size-unit'	=> 'rem',
				'line-height-unit'	=> '%',
				'weights'			=> $weights,
				'subsets'			=> false,
				'color'				=> false,
				'preview'			=> false,
				'text-align'		=> false,
			],
			[ // h5-typography
				'id'				=> 'h5-typography',
				'type'				=> 'typography',
				'title'				=> esc_html__( 'H5', 'drplus' ),
				'compiler'			=> true,
				'fonts'				=> $fonts,
				'font-size-unit'	=> 'rem',
				'line-height-unit'	=> '%',
				'weights'			=> $weights,
				'subsets'			=> false,
				'color'				=> false,
				'preview'			=> false,
				'text-align'		=> false,
			],
			[ // h6-typography
				'id'				=> 'h6-typography',
				'type'				=> 'typography',
				'title'				=> esc_html__( 'H6', 'drplus' ),
				'compiler'			=> true,
				'fonts'				=> $fonts,
				'font-size-unit'	=> 'rem',
				'line-height-unit'	=> '%',
				'weights'			=> $weights,
				'subsets'			=> false,
				'color'				=> false,
				'preview'			=> false,
				'text-align'		=> false,
			],
		),
	)
);

Redux::set_section( // Header
	$opt_name,
	array(
		'title'			=> esc_html__( 'Header', 'drplus' ),
		'id'			=> 'header-typography-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // header-logo-typography
				'id'				=> 'header-logo-typography',
				'type'				=> 'typography',
				'title'				=> esc_html__( 'Logo', 'drplus' ),
				'compiler'			=> true,
				'fonts'				=> $fonts,
				'units'				=> 'rem',
				'line-height-unit'	=> '%',
				'weights'			=> $weights,
				'subsets'			=> false,
				'color'				=> false,
				'preview'			=> false,
				'text-align'		=> false,
			],
			[ // header-menu-typography
				'id'				=> 'header-menu-typography',
				'type'				=> 'typography',
				'title'				=> esc_html__( 'Menu', 'drplus' ),
				'compiler'			=> true,
				'fonts'				=> $fonts,
				'units'				=> 'rem',
				'line-height-unit'	=> '%',
				'weights'			=> $weights,
				'subsets'			=> false,
				'color'				=> false,
				'preview'			=> false,
				'text-align'		=> false,
			],
		),
	)
);

Redux::set_section( // Forms
	$opt_name,
	array(
		'title'			=> esc_html__( 'Forms', 'drplus' ),
		'id'			=> 'forms-typography-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // forms-btn-typography
				'id'				=> 'forms-btn-typography',
				'type'				=> 'typography',
				'title'				=> esc_html__( 'Buttons', 'drplus' ),
				'compiler'			=> true,
				'fonts'				=> $fonts,
				'units'				=> 'rem',
				'line-height-unit'	=> '%',
				'weights'			=> $weights,
				'subsets'			=> false,
				'color'				=> false,
				'preview'			=> false,
				'text-align'		=> false,
			],
			[ // forms-input-typography
				'id'				=> 'forms-input-typography',
				'type'				=> 'typography',
				'title'				=> esc_html__( 'Inputs', 'drplus' ),
				'compiler'			=> true,
				'fonts'				=> $fonts,
				'units'				=> 'rem',
				'line-height-unit'	=> '%',
				'weights'			=> $weights,
				'subsets'			=> false,
				'color'				=> false,
				'preview'			=> false,
				'text-align'		=> false,
			],
		),
	)
);

Redux::set_section( // Shop
	$opt_name,
	array(
		'title'			=> esc_html__( 'Shop', 'drplus' ),
		'id'			=> 'shop-typography-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // shop-regular-price-typography
				'id'				=> 'shop-regular-price-typography',
				'type'				=> 'typography',
				'title'				=> esc_html__( 'Regular Price', 'drplus' ),
				'compiler'			=> true,
				'fonts'				=> $fonts,
				'units'				=> 'rem',
				'line-height-unit'	=> '%',
				'weights'			=> $weights,
				'subsets'			=> false,
				'color'				=> false,
				'preview'			=> false,
				'text-align'		=> false,
			],
			[ // shop-sale-price-typography
				'id'				=> 'shop-sale-price-typography',
				'type'				=> 'typography',
				'title'				=> esc_html__( 'Sale Price', 'drplus' ),
				'compiler'			=> true,
				'fonts'				=> $fonts,
				'units'				=> 'rem',
				'line-height-unit'	=> '%',
				'weights'			=> $weights,
				'subsets'			=> false,
				'color'				=> false,
				'preview'			=> false,
				'text-align'		=> false,
			],
		),
	)
);

Redux::set_section( // Footer
	$opt_name,
	array(
		'title'			=> esc_html__( 'Footer', 'drplus' ),
		'id'			=> 'footer-typography-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // footer-about-typography
				'id'				=> 'footer-about-typography',
				'type'				=> 'typography',
				'title'				=> esc_html__( 'About text', 'drplus' ),
				'compiler'			=> true,
				'fonts'				=> $fonts,
				'units'				=> 'rem',
				'line-height-unit'	=> '%',
				'weights'			=> $weights,
				'subsets'			=> false,
				'color'				=> false,
				'preview'			=> false,
				'text-align'		=> false,
			],
			[ // footer-heading-typography
				'id'				=> 'footer-heading-typography',
				'type'				=> 'typography',
				'title'				=> esc_html__( 'Footer headings', 'drplus' ),
				'compiler'			=> true,
				'fonts'				=> $fonts,
				'units'				=> 'rem',
				'line-height-unit'	=> '%',
				'weights'			=> $weights,
				'subsets'			=> false,
				'color'				=> false,
				'preview'			=> false,
				'text-align'		=> false,
				'required'			=> [
					['footer_show_menu','=',true],
				],
			],
			[ // footer-menu-typography
				'id'				=> 'footer-menu-typography',
				'type'				=> 'typography',
				'title'				=> esc_html__( 'Menu 1', 'drplus' ),
				'compiler'			=> true,
				'fonts'				=> $fonts,
				'units'				=> 'rem',
				'line-height-unit'	=> '%',
				'weights'			=> $weights,
				'subsets'			=> false,
				'color'				=> false,
				'preview'			=> false,
				'text-align'		=> false,
				'required'			=> [
					['footer_show_menu','=',true],
				],
			],
			[ // footer-copyright-typography
				'id'				=> 'footer-copyright-typography',
				'type'				=> 'typography',
				'title'				=> esc_html__( 'Copyright', 'drplus' ),
				'compiler'			=> true,
				'fonts'				=> $fonts,
				'units'				=> 'rem',
				'line-height-unit'	=> '%',
				'weights'			=> $weights,
				'subsets'			=> false,
				'color'				=> false,
				'preview'			=> false,
				'text-align'		=> false,
			],
		),
	)
);

Redux::set_section( // WP Dashboard
	$opt_name,
	array(
		'title'			=> esc_html__( 'WP Dashboard', 'drplus' ),
		'id'			=> 'wp-dashboard-typography-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // wp-dashboard-font-change
				'id'		=> "wp-dashboard-font-change",
				'title'		=> esc_html__( "Change dashboard font", 'drplus' ),
				'subtitle'	=> esc_html__( "If you have customized the WordPress dashboard or are using a theme for the dashboard, please disable this option.", 'drplus' ),
				'type'		=> 'switch',
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // wp-dashboard-font
				'id'				=> 'wp-dashboard-font',
				'type'				=> 'typography',
				'title'				=> esc_html__( 'Dashboard', 'drplus' ),
				'compiler'			=> true,
				'fonts'				=> $fonts,
				'units'				=> 'px',
				'line-height-unit'	=> '%',
				'font-style'		=> false,
				'font-weight'		=> false,
				'subsets'			=> false,
				'font-size'			=> false,
				'line-height'		=> false,
				'color'				=> false,
				'preview'			=> false,
				'text-align'		=> false,
				'default'			=> [
					'font-family'	=> 'IRANYekanX',
				],
				'required'			=> [
					[
						'wp-dashboard-font-change',
						'=',
						true
					],
				]
			],
		),
	)
);