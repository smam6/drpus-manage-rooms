<?php
defined( 'ABSPATH' ) || exit;

$menu_aligns = [];
if( is_rtl() ) {
	$menu_aligns = [
		'right'		=> esc_html__( "Right", 'drplus' ),
		'center'	=> esc_html__( "Center", 'drplus' ),
		'left'		=> esc_html__( "Left", 'drplus' ),
	];
} else {
	$menu_aligns = [
		'left'		=> esc_html__( "Left", 'drplus' ),
		'center'	=> esc_html__( "Center", 'drplus' ),
		'right'		=> esc_html__( "Right", 'drplus' ),
	];
}

Redux::set_section( // Header section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Header', 'drplus' ),
		'id'			=> 'header-general-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // show_header
				'id'		=> 'show_header',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Header status', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
			],
			[ // sticky_header
				'id'		=> 'sticky_header',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Header type', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Sticky', 'drplus' ) ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Sticky', 'drplus' ),
				'off'		=> esc_html__( 'Static', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['show_header','=',true]
				]
			],
			[ // show-header-mobile-menu
				'id'		=> 'show-header-mobile-menu',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Mobile menu status', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['show_header','=',true]
				]
			],
			[ // header_bg
				'id'			=> 'header_bg',
				'type'			=> 'color',
				'title'			=> __( 'Header background', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '#ffffff' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> '#ffffff',
				'required'	=> [
					['show_header','=',true]
				]
			],
			[ // header_bg_dark
				'id'			=> 'header_bg_dark',
				'type'			=> 'color',
				'title'			=> sprintf( __( '%s (Dark mode)', 'drplus' ), __( 'Header background', 'drplus' ) ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '#0F1115' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> '#0F1115',
				'required'	=> [
					['show_header','=',true],
					['color_mode','=', 'both'],
				]
			],
		),
	)
);

Redux::set_section( // Logo section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Logo', 'drplus' ),
		'id'			=> 'header-logo-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // show-logo
				'id'		=> 'show-logo',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show logo in the header', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['show_header','=',true]
				],
			],
			[ // logo-type
				'id'		=> 'logo-type',
				'type'		=> 'radio',
				'title'		=> esc_html__( 'Logo type', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Image', 'drplus' ) ),
				'data'		=> [
					'text'	=> esc_html__( 'Text', 'drplus' ),
					'img'	=> esc_html__( 'Image', 'drplus' ),
				],
				'default'	=> 'img',
				'required'	=> [
					['show-logo','=',true],
				]
			],
			[ // logo-text-type
				'id'		=> 'logo-text-type',
				'type'		=> 'radio',
				'title'		=> esc_html__( 'Text type', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Site title', 'drplus' ) ),
				'data'		=> [
					'title'		=> esc_html__( 'Site title', 'drplus' ),
					'custom'	=> esc_html__( 'Custom', 'drplus' ),
				],
				'default'	=> 'title',
				'required'	=> [
					['show-logo','=',true],
					['logo-type','=','text'],
				],
			],
			[ // logo-text-custom
				'id'		=> 'logo-text-custom',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Logo text', 'drplus' ),
				'required'	=> [
					['show-logo','=',true],
					['logo-type','=','text'],
					['logo-text-type','=','custom'],
				],
			],
			[ // logo-img
				'id'				=> 'logo-img',
				'type'		 		=> 'media',
				'title'				=> esc_html__( 'Logo image file', 'drplus' ),
				'compiler'	 		=> true,
				'url'				=> true,
				'preview_size'		=> 'full',
				'library_filter'	=> ['jpeg', 'gif', 'png', 'bmp', 'tiff', 'x-icon', 'svg', 'svg+xml', 'webp'],
				'default'			=> [
					'url'	=> DRPLUS_URI . "assets/images/logo.svg",
				],
				'required'			=> [
					['show-logo','=',true],
					['logo-type','=','img'],
				],
			],
			[ // logo-img-size
				'id'		=> 'logo-img-size',
				'type'		=> 'dimensions',
				'title'		=> esc_html__( 'Logo size', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), 'W: 91 & H: 51' ),
				'desc'		=> esc_html__( 'Leave empty for full size', 'drplus' ),
				'default'	=> [
					'width'		=> 91,
					'height'	=> 51
				],
				'required'	=> [
					['show-logo','=',true],
					['logo-type','=','img'],
				],
			],
			[ // logo-link
				'id'			=> 'logo-link',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Logo URL', 'drplus' ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), home_url() ),
				'default'		=> home_url(),
				'placeholder'	=> home_url(),
				'required'		=> [
					['show-logo','=',true],
				],
			],
			[ // header_logo_color
				'id'			=> 'header_logo_color',
				'type'			=> 'color',
				'title'			=> __( 'Logo text color', 'drplus' ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '#2e313c' ),
				'transparent'	=> false,
				'default'		=> '#2e313c',
				'required'		=> [
					['show-logo','=',true],
					['logo-type','=','text'],
				],
			],
			[ // header_logo_color_dark
				'id'			=> 'header_logo_color_dark',
				'type'			=> 'color',
				'title'			=> sprintf( __( '%s (Dark mode)', 'drplus' ), __( 'Logo text color', 'drplus' ) ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '#F1F1F1' ),
				'transparent'	=> false,
				'default'		=> '#F1F1F1',
				'required'		=> [
					['show-logo','=',true],
					['logo-type','=','text'],
					['color_mode','=', 'both'],
				],
			],
			[ // sticky_header_logo_color
				'id'			=> 'sticky_header_logo_color',
				'type'			=> 'color',
				'title'			=> __( 'Logo text color (sticky)', 'drplus' ),
				'compiler'		=> true,
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '#1dbab5' ),
				'transparent'	=> false,
				'default'		=> '#1dbab5',
				'required'		=> [
					['show-logo','=',true],
					['logo-type','=','text'],
					['sticky_header','=',true]
				],
			],
			[ // sticky_header_logo_color_dark
				'id'			=> 'sticky_header_logo_color_dark',
				'type'			=> 'color',
				'title'			=> __( 'Logo text color (sticky, Dark mode)', 'drplus' ),
				'compiler'		=> true,
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '#1dbab5' ),
				'transparent'	=> false,
				'default'		=> '#1dbab5',
				'required'		=> [
					['show-logo','=',true],
					['logo-type','=','text'],
					['sticky_header','=',true],
					['color_mode','=', 'both'],
				],
			],
		),
	)
);

Redux::set_section( // Menu section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Menu', 'drplus' ),
		'id'			=> 'header-menu-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // show-header-menu
				'id'		=> 'show-header-menu',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show main menu', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['show_header','=',true],
				]
			],
			[ // header-menu-align
				'id'		=> 'header-menu-align',
				'type'		=> 'button_set',
				'title'		=> esc_html__( 'Menu align', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), is_rtl() ? esc_html__( 'Right', 'drplus' ) : esc_html__( 'Left', 'drplus' ) ),
				'options'	=> $menu_aligns,
				'default'	=> is_rtl() ? 'right' : 'left',
				'required'	=> [
					['show-header-menu','=',true],
				],
			],
			[ // header_menu_color
				'id'			=> 'header_menu_color',
				'type'			=> 'color',
				'title'			=> __( 'Menu text color', 'drplus' ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '#383838' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> '#383838',
				'required'		=> [
					['show-header-menu','=',true],
				],
			],
			[ // header_menu_color_dark
				'id'			=> 'header_menu_color_dark',
				'type'			=> 'color',
				'title'			=> sprintf( __( '%s (Dark mode)', 'drplus' ), __( 'Menu text color', 'drplus' ) ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '#EDEDED' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> '#EDEDED',
				'required'		=> [
					['show-header-menu','=',true],
					['color_mode','=', 'both'],
				],
			],
			[ // header_menu_color_hover
				'id'			=> 'header_menu_color_hover',
				'type'			=> 'color',
				'title'			=> __( 'Menu text hover color', 'drplus' ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '#383838' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> '#383838',
				'required'		=> [
					['show-header-menu','=',true],
				],
			],
			[ // header_menu_color_hover_dark
				'id'			=> 'header_menu_color_hover_dark',
				'type'			=> 'color',
				'title'			=> sprintf( __( '%s (Dark mode)', 'drplus' ), __( 'Menu text hover color', 'drplus' ) ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '#EDEDED' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> '#EDEDED',
				'required'		=> [
					['show-header-menu','=',true],
					['color_mode','=', 'both'],
				],
			],
			[ // header_submenu_bg
				'id'			=> 'header_submenu_bg',
				'type'			=> 'color',
				'title'			=> __( 'Submenu background color', 'drplus' ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '#ffffff' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> '#ffffff',
				'required'		=> [
					['show-header-menu','=',true],
				],
			],
			[ // header_submenu_bg_dark
				'id'			=> 'header_submenu_bg_dark',
				'type'			=> 'color',
				'title'			=> sprintf( __( '%s (Dark mode)', 'drplus' ), __( 'Submenu background color', 'drplus' ) ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '#0F1115' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> '#0F1115',
				'required'		=> [
					['show-header-menu','=',true],
					['color_mode','=', 'both'],
				],
			],
			[ // header_submenu_color
				'id'			=> 'header_submenu_color',
				'type'			=> 'color',
				'title'			=> __( 'Submenu text color', 'drplus' ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '#114B5F' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> '#114B5F',
				'required'		=> [
					['show-header-menu','=',true],
				],
			],
			[ // header_submenu_color_dark
				'id'			=> 'header_submenu_color_dark',
				'type'			=> 'color',
				'title'			=> sprintf( __( '%s (Dark mode)', 'drplus' ), __( 'Submenu text color', 'drplus' ) ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '#B8D9E4' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> '#B8D9E4',
				'required'		=> [
					['show-header-menu','=',true],
					['color_mode','=', 'both'],
				],
			],
			[ // header_submenu_color_hover
				'id'			=> 'header_submenu_color_hover',
				'type'			=> 'color',
				'title'			=> __( 'Submenu text hover color', 'drplus' ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '#114B5F' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> '#114B5F',
				'required'		=> [
					['show-header-menu','=',true],
				],
			],
			[ // header_submenu_color_hover_dark
				'id'			=> 'header_submenu_color_hover_dark',
				'type'			=> 'color',
				'title'			=> sprintf( __( '%s (Dark mode)', 'drplus' ), __( 'Submenu text hover color', 'drplus' ) ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '#B8D9E4' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> '#B8D9E4',
				'required'		=> [
					['show-header-menu','=',true],
					['color_mode','=', 'both'],
				],
			],
			[ // header_submenu_subtitle_color
				'id'			=> 'header_submenu_subtitle_color',
				'type'			=> 'color',
				'title'			=> __( 'Submenu - subtitle text color', 'drplus' ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '#A6A6A6' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> '#A6A6A6',
				'required'		=> [
					['show-header-menu','=',true],
				],
			],
			[ // header_submenu_subtitle_color_dark
				'id'			=> 'header_submenu_subtitle_color_dark',
				'type'			=> 'color',
				'title'			=> sprintf( __( '%s (Dark mode)', 'drplus' ), __( 'Submenu - subtitle text color', 'drplus' ) ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '#9AB7C4' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> '#9AB7C4',
				'required'		=> [
					['show-header-menu','=',true],
					['color_mode','=', 'both'],
				],
			],
			[ // header_submenu_subtitle_color_hover
				'id'			=> 'header_submenu_subtitle_color_hover',
				'type'			=> 'color',
				'title'			=> __( 'Submenu - subtitle text hover color', 'drplus' ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '#A6A6A6' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> '#A6A6A6',
				'required'		=> [
					['show-header-menu','=',true],
				],
			],
			[ // header_submenu_subtitle_color_hover_dark
				'id'			=> 'header_submenu_subtitle_color_hover_dark',
				'type'			=> 'color',
				'title'			=> sprintf( __( '%s (Dark mode)', 'drplus' ), __( 'Submenu - subtitle text hover color', 'drplus' ) ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '#B8D9E4' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> '#B8D9E4',
				'required'		=> [
					['show-header-menu','=',true],
					['color_mode','=', 'both'],
				],
			],
			[ // header_submenu_bar_color_hover
				'id'			=> 'header_submenu_bar_color_hover',
				'type'			=> 'color',
				'title'			=> __( 'Submenu - bar hover color', 'drplus' ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '#159F9B' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> '#159F9B',
				'required'		=> [
					['show-header-menu','=',true],
				],
			],
			[ // header_submenu_bar_color_hover_dark
				'id'			=> 'header_submenu_bar_color_hover_dark',
				'type'			=> 'color',
				'title'			=> sprintf( __( '%s (Dark mode)', 'drplus' ), __( 'Submenu - bar hover color', 'drplus' ) ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '#1dbab5' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> '#1dbab5',
				'required'		=> [
					['show-header-menu','=',true],
					['color_mode','=', 'both'],
				],
			],
		),
	)
);

Redux::set_section( // Cart section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Cart button', 'drplus' ),
		'id'			=> 'header-cart-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // show-cart
				'id'		=> 'show-cart',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show cart', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['show_header','=',true]
				],
			],
			[ // cart-text
				'id'			=> 'cart-text',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Cart button text', 'drplus' ),
				'default'		=> '',
				'required'		=> [
					['show-cart','=',true],
				],
			],
			[ // cart-icon
				'id'			=> 'cart-icon',
				'type'			=> 'icon_select',
				'title'			=> esc_html__( 'Cart button icon', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), 'drplus-icon-shopping-cart' ),
				'default'		=> 'drplus-icon-shopping-cart',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> DRPLUS_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Doctor plus icons', 'drplus' ),
						'prefix'	=> 'drplus-icon',
					],
				],
				'required'		=> [
					['show-cart','=',true],
				],
			],
			[ // show-mini-cart
				'id'		=> 'show-mini-cart',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show mini cart', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['show_header','=',true],
					['show-cart','=',true],
				],
			],
			[ // show-cart-count
				'id'		=> 'show-cart-count',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show cart count', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['show_header','=',true],
					['show-cart','=',true],
				],
			],
		),
	)
);

Redux::set_section( // Account section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Account button', 'drplus' ),
		'id'			=> 'header-account-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // show-account-btn // guest
				'id'		=> 'show-account-btn',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show account button (guest)', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['show_header','=',true]
				]
			],
			[ // account-btn-text-type // guest
				'id'		=> 'account-btn-text-type',
				'type'		=> 'radio',
				'title'		=> esc_html__( 'Button text type (guest)', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( "Hide", 'drplus' ) ),
				'default'	=> 'none',
				'data'		=> [
					'custom_text'	=> esc_html__( "Custom text", 'drplus' ),
					'none'			=> esc_html__( "Hide", 'drplus' ),
				],
				'required'	=> [
					['show-account-btn','=',true],
				],
			],
			[ // account-btn-text // guest
				'id'			=> 'account-btn-text',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Button text (guest)', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), __( 'Account', 'drplus' ) ),
				'default'		=> __( 'Account', 'drplus' ),
				'placeholder'	=> __( 'Account', 'drplus' ),
				'required'		=> [
					['show-account-btn','=',true],
					['account-btn-text-type', '=', 'custom_text'],
				],
			],
			[ // account-btn-attachment-type // guest
				'id'		=> 'account-btn-attachment-type',
				'type'		=> 'radio',
				'title'		=> esc_html__( 'Button attachment type (guest)', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( "Icon", 'drplus' ) ),
				'default'	=> 'icon',
				'data'		=> [
					'icon'		=> esc_html__( "Icon", 'drplus' ),
					'none'		=> esc_html__( "Hide", 'drplus' ),
				],
				'required'	=> [
					['show-account-btn','=',true],
				],
			],
			[ // account-btn-icon // guest
				'id'			=> 'account-btn-icon',
				'type'			=> 'icon_select',
				'title'			=> esc_html__( 'Button icon (guest)', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), 'drplus-icon-user' ),
				'default'		=> 'drplus-icon-user',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> DRPLUS_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Doctor plus icons', 'drplus' ),
						'prefix'	=> 'drplus-icon',
					],
				],
				'required'		=> [
					['show-account-btn','=',true],
					['account-btn-attachment-type','=','icon']
				],
			],
			[ // guest-login-url // guest
				'id'			=> 'guest-login-url',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Button URL (Guest)', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), home_url( "?login=true" ) ),
				'default'		=> home_url( "?login=true" ),
				'placeholder'	=> home_url( "?login=true" ),
				'required'		=> [
					['show-account-btn','=',true],
				],
			],
			[ // account-btn-link-newtab // guest
				'id'		=> 'account-btn-link-newtab',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Open link in newtab (guest)', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'No', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> false,
				'required'	=> [
					['show-account-btn','=',true]
				]
			],

			[ // account-btn-guest-divider
				'id'	=> 'account-btn-guest-divider',
				'type'	=> 'divide',
				'required'	=> [
					['show-account-btn','=',true]
				]
			],

			[ // show-account-btn-user // user
				'id'		=> 'show-account-btn-user',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show account button (user)', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['show_header','=',true]
				]
			],
			[ // account-btn-user-text-type // user
				'id'		=> 'account-btn-user-text-type',
				'type'		=> 'radio',
				'title'		=> esc_html__( 'Button attachment type (user)', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( "User's display name", 'drplus' ) ),
				'default'	=> 'username',
				'data'		=> [
					'username'		=> esc_html__( "User's display name", 'drplus' ),
					'custom_text'	=> esc_html__( "Custom text", 'drplus' ),
					'none'			=> esc_html__( "Hide", 'drplus' ),
				],
				'required'	=> [
					['show-account-btn-user','=',true],
				],
			],
			[ // account-btn-user-text // user
				'id'			=> 'account-btn-user-text',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Button text (user)', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), __( 'Account', 'drplus' ) ),
				'default'		=> __( 'Account', 'drplus' ),
				'placeholder'	=> __( 'Account', 'drplus' ),
				'required'		=> [
					['show-account-btn-user','=',true],
					['account-btn-user-text-type', '=', 'custom_text'],
				],
			],
			[ // account-btn-user-attachment-type // user
				'id'		=> 'account-btn-user-attachment-type',
				'type'		=> 'radio',
				'title'		=> esc_html__( 'Button attachment type (user)', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( "Avatar", 'drplus' ) ),
				'default'	=> 'avatar',
				'data'		=> [
					'avatar'	=> esc_html__( "Avatar", 'drplus' ),
					'icon'		=> esc_html__( "Icon", 'drplus' ),
					'none'		=> esc_html__( "Hide", 'drplus' ),
				],
				'required'	=> [
					['show-account-btn-user','=',true],
				],
			],
			[ // account-btn-user-icon // user
				'id'			=> 'account-btn-user-icon',
				'type'			=> 'icon_select',
				'title'			=> esc_html__( 'Button icon (user)', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), 'drplus-icon-user' ),
				'default'		=> 'drplus-icon-user',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> DRPLUS_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Doctor plus icons', 'drplus' ),
						'prefix'	=> 'drplus-icon',
					],
				],
				'required'		=> [
					['show-account-btn-user','=',true],
					['account-btn-user-attachment-type','=','icon'],
				],
			],
			[ // account-btn-link // user
				'id'			=> 'account-btn-link',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Button URL (user)', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), home_url( 'my-account' ) ),
				'default'		=> home_url( 'my-account' ),
				'placeholder'	=> home_url( 'my-account' ),
				'required'		=> [
					['show-account-btn-user','=',true],
				],
			],
			[ // account-btn-user-link-newtab // user
				'id'		=> 'account-btn-user-link-newtab',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Open link in newtab (user)', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'No', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> false,
				'required'	=> [
					['show-account-btn-user','=',true]
				]
			],

			[ // account-btn-styles-divider
				'id'	=> 'account-btn-styles-divider',
				'type'	=> 'divide',
				'required'	=> [
					['show-account-btn','=',true]
				]
			],

			[ // account-btn-bg-color
				'id'			=> 'account-btn-bg-color',
				'type'			=> 'color',
				'title'			=> __( 'Button background', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '#ffffff' ),
				'compiler'		=> true,
				'transparent'	=> true,
				'default'		=> '#ffffff',
				'required'		=> [
					['show-account-btn','=',true],
				],
			],
			[ // account-btn-bg-color_dark
				'id'			=> 'account-btn-bg-color_dark',
				'type'			=> 'color',
				'title'			=> sprintf( __( '%s (Dark mode)', 'drplus' ), __( 'Button background', 'drplus' ) ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '#11161c' ),
				'compiler'		=> true,
				'transparent'	=> true,
				'default'		=> '#11161c',
				'required'		=> [
					['show-account-btn','=',true],
					['color_mode','=', 'both'],
				],
			],
			[ // account-btn-color
				'id'			=> 'account-btn-color',
				'type'			=> 'color',
				'title'			=> __( 'Button text & icon color', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '#1dbab5' ),
				'compiler'		=> true,
				'transparent'	=> true,
				'default'		=> '#1dbab5',
				'required'		=> [
					['show-account-btn','=',true],
				],
			],
			[ // account-btn-color_dark
				'id'			=> 'account-btn-color_dark',
				'type'			=> 'color',
				'title'			=> sprintf( __( '%s (Dark mode)', 'drplus' ), __( 'Button text & icon color', 'drplus' ) ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '#1dbab5' ),
				'compiler'		=> true,
				'transparent'	=> true,
				'default'		=> '#1dbab5',
				'required'		=> [
					['show-account-btn','=',true],
					['color_mode','=', 'both'],
				],
			],
			[ // account-btn-border
				'id'			=> 'account-btn-border',
				'type'			=> 'border',
				'title'			=> __( 'Button border', 'drplus' ),
				'compiler'		=> true,
				'default'		=> [
					'border-color'	=> '#1dbab5',
					'border-style'	=> 'solid',
					'border-width'	=> '1px',
				],
				'required'		=> [
					['show-account-btn','=',true],
				],
			],

			[ // account-btn-bg-color-hover
				'id'			=> 'account-btn-bg-color-hover',
				'type'			=> 'color',
				'title'			=> __( 'Button background (hover)', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '#ffffff' ),
				'compiler'		=> true,
				'transparent'	=> true,
				'default'		=> '#ffffff',
				'required'		=> [
					['show-account-btn','=',true],
				],
			],
			[ // account-btn-bg-color-hover_dark
				'id'			=> 'account-btn-bg-color-hover_dark',
				'type'			=> 'color',
				'title'			=> __( 'Button background (hover, Dark mode)', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '#1a2027' ),
				'compiler'		=> true,
				'transparent'	=> true,
				'default'		=> '#1a2027',
				'required'		=> [
					['show-account-btn','=',true],
					['color_mode','=', 'both'],
				],
			],
			[ // account-btn-color-hover
				'id'			=> 'account-btn-color-hover',
				'type'			=> 'color',
				'title'			=> __( 'Button text & icon color (hover)', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '#1dbab5' ),
				'compiler'		=> true,
				'transparent'	=> true,
				'default'		=> '#1dbab5',
				'required'		=> [
					['show-account-btn','=',true],
				],
			],
			[ // account-btn-color-hover_dark
				'id'			=> 'account-btn-color-hover_dark',
				'type'			=> 'color',
				'title'			=> __( 'Button text & icon color (hover, Dark mode)', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '#1dbab5' ),
				'compiler'		=> true,
				'transparent'	=> true,
				'default'		=> '#1dbab5',
				'required'		=> [
					['show-account-btn','=',true],
					['color_mode','=', 'both'],
				],
			],
			[ // account-btn-border-hover
				'id'			=> 'account-btn-border-hover',
				'type'			=> 'border',
				'title'			=> __( 'Button border (hover)', 'drplus' ),
				'compiler'		=> true,
				'default'		=> [
					'border-color'	=> '#1dbab5',
					'border-style'	=> 'solid',
					'border-width'	=> '1px',
				],
				'required'		=> [
					['show-account-btn','=',true],
				],
			],
		),
	)
);

Redux::set_section( // Account items section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Account items', 'drplus' ),
		'id'			=> 'header-account-items-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // show-account-btn-menu
				'id'		=> 'show-account-btn-menu',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show account menu', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['show-account-btn','=',true]
				]
			],
			[ // show-login-item
				'id'		=> 'show-login-item',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show login item', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['show-account-btn-menu','=',true]
				]
			],
			[ // login-text
				'id'			=> 'login-text',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Login text', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( "Login", 'drplus' ) ),
				'default'		=> esc_html__( "Login", 'drplus' ),
				'placeholder'	=> esc_html__( "Login", 'drplus' ),
				'required'		=> [
					['show-login-item','=',true],
				],
			],
			[ // login-icon
				'id'			=> 'login-icon',
				'type'			=> 'icon_select',
				'title'			=> esc_html__( 'Login icon', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), 'drplus-icon-login' ),
				'default'		=> 'drplus-icon-login',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> DRPLUS_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Doctor plus icons', 'drplus' ),
						'prefix'	=> 'drplus-icon',
					],
				],
				'required'		=> [
					['show-login-item','=',true],
				],
			],
			[ // show-signup-item
				'id'		=> 'show-signup-item',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show signup item', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['show-account-btn-menu','=',true]
				]
			],
			[ // signup-text
				'id'			=> 'signup-text',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Signup text', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( "Signup", 'drplus' ) ),
				'default'		=> esc_html__( "Signup", 'drplus' ),
				'placeholder'	=> esc_html__( "Signup", 'drplus' ),
				'required'		=> [
					['show-signup-item','=',true],
				],
			],
			[ // signup-icon
				'id'			=> 'signup-icon',
				'type'			=> 'icon_select',
				'title'			=> esc_html__( 'Signup icon', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), 'drplus-icon-user-add' ),
				'default'		=> 'drplus-icon-user-add',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> DRPLUS_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Doctor plus icons', 'drplus' ),
						'prefix'	=> 'drplus-icon',
					],
				],
				'required'		=> [
					['show-signup-item','=',true],
				],
			],
			[ // signup-link
				'id'			=> 'signup-link',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Signup URL', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), home_url( "?login=true" ) ),
				'default'		=> home_url( "?login=true" ),
				'placeholder'	=> home_url( "?login=true" ),
				'required'		=> [
					['show-signup-item','=',true],
				],
			],
			[
				'type'		=> 'content',
				'mode'		=> 'submessage',
				'content'	=> sprintf( __( 'To set items for logged in users, proceed through the <a href="%s">menus</a>', 'drplus' ), admin_url( 'nav-menus.php' ) ),
				'style'		=> 'info',
				'required'	=> [
					['show-account-btn-menu','=',true]
				]
			]
		),
	)
);

Redux::set_section( // Action button section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Action button', 'drplus' ),
		'id'			=> 'header-action-btn-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // show-header-action-btn
				'id'		=> 'show-header-action-btn',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show header action button', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['show_header','=',true]
				]
			],
			[ // header-action-btn-text
				'id'			=> 'header-action-btn-text',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Button text', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Request appointment', 'drplus' ) ),
				'default'		=> esc_html__( 'Request appointment', 'drplus' ),
				'placeholder'	=> esc_html__( 'Request appointment', 'drplus' ),
				'required'		=> [
					['show-header-action-btn','=',true],
				],
			],
			[ // header-action-btn-icon
				'id'			=> 'header-action-btn-icon',
				'type'			=> 'icon_select',
				'title'			=> esc_html__( 'Button icon', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), is_rtl() ? 'drplus-icon-arrow-up-left-square' : "drplus-icon-arrow-up-right-square" ),
				'default'		=> is_rtl() ? 'drplus-icon-arrow-up-left-square' : "drplus-icon-arrow-up-right-square",
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> DRPLUS_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Doctor plus icons', 'drplus' ),
						'prefix'	=> 'drplus-icon',
					],
				],
				'required'		=> [
					['show-header-action-btn','=',true],
				],
			],
			[ // header-action-btn-link
				'id'			=> 'header-action-btn-link',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Button URL', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), home_url( 'booking' ) ),
				'default'		=> home_url( 'booking' ),
				'placeholder'	=> home_url( 'booking' ),
				'required'		=> [
					['show-header-action-btn','=',true],
				],
			],
			[ // header-action-btn-link-guest
				'id'			=> 'header-action-btn-link-guest',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Button URL (Guest)', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), home_url( 'booking' ) ),
				'default'		=> home_url( 'booking' ),
				'placeholder'	=> home_url( 'booking' ),
				'required'		=> [
					['show-header-action-btn','=',true],
				],
			],
			[ // header-action-btn-link-newtab
				'id'		=> 'header-action-btn-link-newtab',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Open link in newtab', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'No', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> false,
				'required'	=> [
					['show-header-action-btn','=',true]
				]
			],
			[ // header-action-styles
				'id'	=> 'header-action-styles',
				'type'	=> 'divide',
				'required'	=> [
					['show-header-action-btn','=',true]
				]
			],
			[ // header-action-btn-bg-color
				'id'			=> 'header-action-btn-bg-color',
				'type'			=> 'color',
				'title'			=> __( 'Button background', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '#1dbab5' ),
				'compiler'		=> true,
				'transparent'	=> true,
				'default'		=> '#1dbab5',
				'color_alpha'	=> true,
				'required'		=> [
					['show-header-action-btn','=',true],
				],
			],
			[ // header-action-btn-bg-color_dark
				'id'			=> 'header-action-btn-bg-color_dark',
				'type'			=> 'color',
				'title'			=> sprintf( __( '%s (Dark mode)', 'drplus' ), __( 'Button background', 'drplus' ) ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '#1dbab5' ),
				'compiler'		=> true,
				'transparent'	=> true,
				'default'		=> '#1dbab5',
				'color_alpha'	=> true,
				'required'		=> [
					['show-header-action-btn','=',true],
					['color_mode','=', 'both'],
				],
			],
			[ // header-action-btn-color
				'id'			=> 'header-action-btn-color',
				'type'			=> 'color',
				'title'			=> __( 'Button color', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '#ffffff' ),
				'compiler'		=> true,
				'transparent'	=> true,
				'default'		=> '#ffffff',
				'required'		=> [
					['show-header-action-btn','=',true],
				],
			],
			[ // header-action-btn-color_dark
				'id'			=> 'header-action-btn-color_dark',
				'type'			=> 'color',
				'title'			=> sprintf( __( '%s (Dark mode)', 'drplus' ), __( 'Button color', 'drplus' ) ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '#ffffff' ),
				'compiler'		=> true,
				'transparent'	=> true,
				'default'		=> '#ffffff',
				'required'		=> [
					['show-header-action-btn','=',true],
					['color_mode','=', 'both'],
				],
			],
			[ // header-action-btn-border
				'id'			=> 'header-action-btn-border',
				'type'			=> 'border',
				'title'			=> __( 'Button border', 'drplus' ),
				'compiler'		=> true,
				'default'		=> [
					'border-color'	=> '#1dbab5',
					'border-style'	=> 'solid',
					'border-width'	=> '1px',
				],
				'required'		=> [
					['show-header-action-btn','=',true],
				],
			],

			[ // header-action-btn-bg-color-hover
				'id'			=> 'header-action-btn-bg-color-hover',
				'type'			=> 'color',
				'title'			=> __( 'Button background (hover)', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '#159F9B' ),
				'compiler'		=> true,
				'transparent'	=> true,
				'default'		=> '#159F9B',
				'color_alpha'	=> true,
				'required'		=> [
					['show-header-action-btn','=',true],
				],
			],
			[ // header-action-btn-bg-color-hover_dark
				'id'			=> 'header-action-btn-bg-color-hover_dark',
				'type'			=> 'color',
				'title'			=> __( 'Button background (hover, Dark mode)', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '#159F9B' ),
				'compiler'		=> true,
				'transparent'	=> true,
				'default'		=> '#159F9B',
				'color_alpha'	=> true,
				'required'		=> [
					['show-header-action-btn','=',true],
					['color_mode','=', 'both'],
				],
			],
			[ // header-action-btn-color-hover
				'id'			=> 'header-action-btn-color-hover',
				'type'			=> 'color',
				'title'			=> __( 'Button color (hover)', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '#ffffff' ),
				'compiler'		=> true,
				'transparent'	=> true,
				'default'		=> '#ffffff',
				'required'		=> [
					['show-header-action-btn','=',true],
				],
			],
			[ // header-action-btn-color-hover_dark
				'id'			=> 'header-action-btn-color-hover_dark',
				'type'			=> 'color',
				'title'			=> __( 'Button color (hover, Dark mode)', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '#ffffff' ),
				'compiler'		=> true,
				'transparent'	=> true,
				'default'		=> '#ffffff',
				'required'		=> [
					['show-header-action-btn','=',true],
					['color_mode','=', 'both'],
				],
			],
			[ // header-action-btn-border-hover
				'id'			=> 'header-action-btn-border-hover',
				'type'			=> 'border',
				'title'			=> __( 'Button border (hover)', 'drplus' ),
				'compiler'		=> true,
				'default'		=> [
					'border-color'	=> '#159F9B',
					'border-style'	=> 'solid',
					'border-width'	=> '1px',
				],
				'required'		=> [
					['show-header-action-btn','=',true],
				],
			],
		),
	)
);
