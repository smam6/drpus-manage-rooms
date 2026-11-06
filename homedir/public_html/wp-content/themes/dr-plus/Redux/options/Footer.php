<?php

defined( 'ABSPATH' ) || exit;

Redux::set_section( // Footer section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Footer', 'drplus' ),
		'id'			=> 'footer-general-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // show_footer
				'id'		=> 'show_footer',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Footer status', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), __( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
			],
			[ // footer_show_back_to_top
				'id'		=> 'footer_show_back_to_top',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Back to top button', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), __( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['show_footer','=',true]
				]
			],
			[ // footer_back_to_top_icon
				'id'			=> 'footer_back_to_top_icon',
				'type'			=> 'icon_select',
				'title'			=> esc_html__( 'Back to top icon', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), 'drplus-icon-arrow-up' ),
				'default'		=> 'drplus-icon-arrow-up',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> DRPLUS_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Doctor plus icons', 'drplus' ),
						'prefix'	=> 'drplus-icon',
					],
				],
				'required'		=> [
					['footer_show_back_to_top','=',true],
				],
			],
			[ // footer_back_to_top_bg
				'id'			=> 'footer_back_to_top_bg',
				'type'			=> 'color',
				'title'			=> __( 'Back to top button background', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '#1dbab5' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> '#1dbab5',
				'required'		=> [
					['footer_show_back_to_top','=',true],
				],
			],
			[ // footer_back_to_top_bg_dark
				'id'			=> 'footer_back_to_top_bg_dark',
				'type'			=> 'color',
				'title'			=> sprintf( __( '%s (Dark mode)', 'drplus' ), __( 'Back to top button background', 'drplus' ) ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '#1dbab5' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> '#1dbab5',
				'required'		=> [
					['footer_show_back_to_top','=',true],
					['color_mode','=', 'both'],
				],
			],
			[ // footer_back_to_top_color
				'id'			=> 'footer_back_to_top_color',
				'type'			=> 'color',
				'title'			=> __( 'Back to top icon color', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '#ffffff' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> '#ffffff',
				'required'		=> [
					['footer_show_back_to_top','=',true],
				],
			],
			[ // footer_back_to_top_color_dark
				'id'			=> 'footer_back_to_top_color_dark',
				'type'			=> 'color',
				'title'			=> sprintf( __( '%s (Dark mode)', 'drplus' ), __( 'Back to top icon color', 'drplus' ) ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '#ffffff' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> '#ffffff',
				'required'		=> [
					['footer_show_back_to_top','=',true],
					['color_mode','=', 'both'],
				],
			],
			[ // footer_bg
				'id'			=> 'footer_bg',
				'type'			=> 'background',
				'title'			=> __( 'Footer background', 'drplus' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> [
					'background-color'	=> '#114b5f'
				],
				'required'	=> [
					['show_footer','=',true]
				]
			],
			[ // footer_bg_dark
				'id'			=> 'footer_bg_dark',
				'type'			=> 'background',
				'title'			=> sprintf( __( '%s (Dark mode)', 'drplus' ), __( 'Footer background', 'drplus' ) ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> [
					'background-color'	=> '#0c1a22'
				],
				'required'	=> [
					['show_footer','=',true],
					['color_mode','=', 'both'],
				]
			],
			[ // footer_color
				'id'			=> 'footer_color',
				'type'			=> 'color',
				'title'			=> __( 'Footer text color', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '#ffffff' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> '#ffffff',
				'required'		=> [
					['show_footer','=',true],
				],
			],
			[ // footer_color_dark
				'id'			=> 'footer_color_dark',
				'type'			=> 'color',
				'title'			=> sprintf( __( '%s (Dark mode)', 'drplus' ), __( 'Footer text color', 'drplus' ) ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '#e6e6e6' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> '#e6e6e6',
				'required'		=> [
					['show_footer','=',true],
					['color_mode','=', 'both'],
				],
			],
		),
	)
);

Redux::set_section( // About section
	$opt_name,
	array(
		'title'			=> esc_html__( 'About', 'drplus' ),
		'id'			=> 'footer-about-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // footer_show_about_us
				'id'		=> 'footer_show_about_us',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show about us', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), __( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['show_footer','=',true],
				]
			],
			[ // footer_about_title
				'id'		=> 'footer_about_title',
				'type'		=> 'text',
				'title'		=> esc_html__( 'About us title', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), __( 'About Us', 'drplus' ) ),
				'default'	=> __( 'About Us', 'drplus' ),
				'required'	=> [
					['footer_show_about_us','=',true],
				],
			],
			[ // footer_about_text
				'id'		=> 'footer_about_text',
				'type'		=> 'editor',
				'title'		=> esc_html__( "About text", 'drplus' ),
				'subtitle'	=> sprintf( __( "Default:<br>%s", 'drplus' ), __( "Experience fast and convenient medical services with Doctor Plus digital health services. Online appointments, telephone consultations, and use of clinic and medical center services are among our services. Also, for a better experience using our services on your mobile phone, you can download and install our software for Android phones.", 'drplus' ) ),
				'default'	=> __( "Experience fast and convenient medical services with Doctor Plus digital health services. Online appointments, telephone consultations, and use of clinic and medical center services are among our services. Also, for a better experience using our services on your mobile phone, you can download and install our software for Android phones.", 'drplus' ),
				'args'		=> [
					'media_buttons'	=> false,
				],
				'required'	=> [
					['footer_show_about_us','=',true],
				]
			],
		),
	)
);

Redux::set_section( // Menu section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Menu', 'drplus' ),
		'id'			=> 'footer-menu-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // footer_show_menu
				'id'		=> 'footer_show_menu',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show menu', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), __( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['show_footer','=',true]
				]
			],
			[ // footer_menu_title
				'id'		=> 'footer_menu_title',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Menu title', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), __( 'Useful Links', 'drplus' ) ),
				'default'	=> __( 'Useful Links', 'drplus' ),
				'required'		=> [
					['show_footer','=',true],
					['footer_show_menu','=',true]
				],
			]
		),
	)
);

Redux::set_section( // Contact section section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Contact section', 'drplus' ),
		'id'			=> 'footer-contact-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // footer_show_contact_info
				'id'		=> 'footer_show_contact_info',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show contact section', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), __( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['show_footer','=',true]
				]
			],
			[ // footer_contact_info_title
				'id'		=> 'footer_contact_info_title',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Contact section title', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), __( 'Contact Methods', 'drplus' ) ),
				'default'	=> __( 'Contact Methods', 'drplus' ),
				'required'	=> [
					['footer_show_contact_info','=',true],
				],
			],
			[ // footer_contact_info_text
				'id'		=> 'footer_contact_info_text',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Contact section description', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), __( 'Service is available seven days a week from 9:00 AM to 12:00 PM.', 'drplus' ) ),
				'default'	=> __( 'Service is available seven days a week from 9:00 AM to 12:00 PM.', 'drplus' ),
				'required'	=> [
					['footer_show_contact_info','=',true],
				],
			],
			[ // footer_contact_info
				'id'			=> 'footer_contact_info',
				'type'			=> 'repeater',
				'title'			=> esc_html__( 'Contact info', 'drplus' ),
				'subtitle'		=> esc_html__( 'Example: Phone numbers, email address, etc.', 'drplus' ),
				'compiler'		=> true,
				'group_values'	=> true,
				'init_empty'	=> true,
				'fields'		=> [
					[ // footer_contact_icons
						'id'			=> 'footer_contact_icons',
						'type'			=> 'icon_select',
						'title'			=> esc_html__( 'Icon', 'drplus' ),
						'compiler'		=> true,
						'default'		=> 'drplus-icon-calling',
						'enqueue_frontend'	=> false,
						'stylesheet'	=> [
							[
								'url'		=> DRPLUS_URI . 'assets/css/iconly.min.css',
								'title'		=> __( 'Doctor plus icons', 'drplus' ),
								'prefix'	=> 'drplus-icon',
							],
						],
					],
					[ // footer_contact_items
						'id'	=> 'footer_contact_items',
						'type'	=> 'text',
						'title'	=> esc_html__( "Text", 'drplus' ),
					],
					[ // footer_contact_types
						'id'		=> 'footer_contact_types',
						'type'		=> 'button_set',
						'title'		=> esc_html__( 'Item type', 'drplus' ),
						'options'	=> [
							'phone'		=> __( "Phone", 'drplus' ),
							'email'		=> __( "Email", 'drplus' ),
							'address'	=> __( "Address", 'drplus' ),
							'other'		=> __( "Other", 'drplus' ),
						],
						'default'	=> 'phone',
					],
					[ // footer_contact_links
						'id'		=> 'footer_contact_links',
						'type'		=> 'text',
						'title'		=> esc_html__( 'URL', 'drplus' ),
						'compiler'	=> true,
						'required'	=> [
							['footer_contact_types','=',['address', 'other']],
						],
					],
				],
				'required'		=> [
					['footer_show_contact_info','=',true],
				],
			],
		),
	)
);

Redux::set_section( // Organizations Logo section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Organizations Logo', 'drplus' ),
		'id'			=> 'footer-org-logos-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // footer_show_org_logos
				'id'		=> 'footer_show_org_logos',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Organizations Logo', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), __( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
			],
			[ // footer_orgs_logo_bg
				'id'			=> 'footer_orgs_logo_bg',
				'type'			=> 'background',
				'title'			=> __( 'Section background', 'drplus' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> [
					'background-color'	=> '#0f3e4f'
				],
				'required'	=> [
					['show_footer','=',true],
					['footer_show_org_logos','=',true],
				]
			],
			[ // footer_orgs_logo_bg_dark
				'id'			=> 'footer_orgs_logo_bg_dark',
				'type'			=> 'background',
				'title'			=> sprintf( __( '%s (Dark mode)', 'drplus' ), __( 'Section background', 'drplus' ) ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> [
					'background-color'	=> '#0d2c39'
				],
				'required'	=> [
					['show_footer','=',true],
					['footer_show_org_logos','=',true],
					['color_mode','=', 'both'],
				]
			],
			[ // footer_orgs_logo_items
				'id'			=> 'footer_orgs_logo_items',
				'type'			=> 'repeater',
				'title'			=> esc_html__( 'Organizations logo', 'drplus' ),
				'subtitle'		=> esc_html__( 'Example: enamad and etc.', 'drplus' ),
				'group_values'	=> true,
				'init_empty'	=> true,
				'fields'		=> [
					[
						'id'	=> 'org_logos_title',
						'type'	=> 'text',
						'title'	=> esc_html__( 'Title', 'drplus' )
					],
					[
						'id'	=> 'org_logos',
						'type'	=> 'ace_editor',
						'title'	=> esc_html__( 'Script', 'drplus' ),
					]
				],
				'required'	=> [
					['show_footer','=',true],
					['footer_show_org_logos','=',true],
				]
			],
		),
	)
);

Redux::set_section( // Socials section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Socials', 'drplus' ),
		'id'			=> 'footer-social-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // footer_show_social_info
				'id'		=> 'footer_show_social_info',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show social section', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), __( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['show_footer','=',true]
				]
			],
			[ // footer_social_bg
				'id'			=> 'footer_social_bg',
				'type'			=> 'background',
				'title'			=> __( 'Section background', 'drplus' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> [
					'background-color'	=> '#0f3e4f'
				],
				'required'	=> [
					['show_footer','=',true],
					['footer_show_social_info','=',true],
				]
			],
			[ // footer_social_bg_dark
				'id'			=> 'footer_social_bg_dark',
				'type'			=> 'background',
				'title'			=> sprintf( __( '%s (Dark mode)', 'drplus' ), __( 'Section background', 'drplus' ) ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> [
					'background-color'	=> '#0d2c39'
				],
				'required'	=> [
					['show_footer','=',true],
					['footer_show_social_info','=',true],
					['color_mode','=', 'both'],
				]
			],
			[ // footer_social_title
				'id'		=> 'footer_social_title',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Social section title', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), __( 'Together with the Doctor Plus platform...', 'drplus' ) ),
				'default'	=> __( 'Together with the Doctor Plus platform...', 'drplus' ),
				'required'	=> [
					['footer_show_social_info','=',true],
				],
			],
			[ // footer_social_info
				'id'			=> 'footer_social_info',
				'type'			=> 'repeater',
				'title'			=> esc_html__( 'Socials', 'drplus' ),
				'compiler'		=> true,
				'group_values'	=> true,
				'init_empty'	=> true,
				'fields'		=> [
					[ // footer_social_icons
						'id'			=> 'footer_social_icons',
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
					[ // footer_social_items
						'id'		=> 'footer_social_items',
						'type'		=> 'text',
						'title'		=> esc_html__( 'URL', 'drplus' ),
						'compiler'	=> true,
					],
				],
				'required'		=> [
					['footer_show_social_info','=',true],
				],
			],
			[ // footer_social_show_bottom_logo
				'id'		=> 'footer_social_show_bottom_logo',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show logo ', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), __( 'Show', 'drplus' ) ),
				'desc'		=> esc_html__( "Show logo under the social icons", 'drplus' ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['show_footer','=',true]
				]
			],
			[ // footer_social_bottom_logo_type
				'id'		=> 'footer_social_bottom_logo_type',
				'type'		=> 'radio',
				'title'		=> esc_html__( 'Logo type', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Image', 'drplus' ) ),
				'data'		=> [
					'text'	=> esc_html__( 'Text', 'drplus' ),
					'img'	=> esc_html__( 'Image', 'drplus' ),
				],
				'default'	=> 'img',
				'required'	=> [
					['footer_social_show_bottom_logo','=',true],
				]
			],
			[ // footer_social_bottom_logo_text_type
				'id'		=> 'footer_social_bottom_logo_text_type',
				'type'		=> 'radio',
				'title'		=> esc_html__( 'Text type', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Site title', 'drplus' ) ),
				'data'		=> [
					'title'		=> esc_html__( 'Site title', 'drplus' ),
					'custom'	=> esc_html__( 'Custom', 'drplus' ),
				],
				'default'	=> 'title',
				'required'	=> [
					['footer_social_show_bottom_logo','=',true],
					['footer_social_bottom_logo_type','=','text'],
				],
			],
			[ // footer_social_bottom_logo_text_custom
				'id'		=> 'footer_social_bottom_logo_text_custom',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Logo text', 'drplus' ),
				'required'	=> [
					['footer_social_show_bottom_logo','=',true],
					['footer_social_bottom_logo_type','=','text'],
					['footer_social_bottom_logo_text_type','=','custom'],
				],
			],
			[ // footer_social_bottom_logo
				'id'				=> 'footer_social_bottom_logo',
				'type'		 		=> 'media',
				'title'				=> esc_html__( 'Logo image file', 'drplus' ),
				'compiler'	 		=> true,
				'url'				=> true,
				'preview_size'		=> 'full',
				'library_filter'	=> ['jpeg', 'gif', 'png', 'bmp', 'tiff', 'x-icon', 'svg', 'svg+xml', 'webp'],
				'default'			=> [
					'url'	=> DRPLUS_URI . "assets/images/footer-logo.svg",
				],
				'required'			=> [
					['footer_social_show_bottom_logo','=',true],
					['footer_social_bottom_logo_type','=','img'],
				],
			],
			[ // footer_social_bottom_logo_size
				'id'		=> 'footer_social_bottom_logo_size',
				'type'		=> 'dimensions',
				'title'		=> esc_html__( 'Logo size', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), 'W: 91 & H: 51' ),
				'desc'		=> esc_html__( 'Leave empty for full size', 'drplus' ),
				'default'	=> [
					'width'		=> 140,
					'height'	=> 32
				],
				'required'	=> [
					['footer_social_show_bottom_logo','=',true],
					['footer_social_bottom_logo_type','=','img'],
				],
			],
			[ // footer_social_bottom_logo_link
				'id'			=> 'footer_social_bottom_logo_link',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Logo URL', 'drplus' ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), home_url() ),
				'default'		=> home_url(),
				'placeholder'	=> home_url(),
				'required'		=> [
					['footer_social_show_bottom_logo','=',true],
				],
			],
			[ // footer_logo_color
				'id'			=> 'footer_logo_color',
				'type'			=> 'color',
				'title'			=> __( 'Logo text color', 'drplus' ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '#1dbab5' ),
				'transparent'	=> false,
				'default'		=> '#1dbab5',
				'required'		=> [
					['footer_social_show_bottom_logo','=',true],
					['footer_social_bottom_logo_type','=','text'],
				],
			],
			[ // footer_logo_color_dark
				'id'			=> 'footer_logo_color_dark',
				'type'			=> 'color',
				'title'			=> sprintf( __( '%s (Dark mode)', 'drplus' ), __( 'Logo text color', 'drplus' ) ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '#1dbab5' ),
				'transparent'	=> false,
				'default'		=> '#1dbab5',
				'required'		=> [
					['footer_social_show_bottom_logo','=',true],
					['footer_social_bottom_logo_type','=','text'],
					['color_mode','=', 'both'],
				],
			],
		),
	)
);

Redux::set_section( // Copyright
	$opt_name,
	array(
		'title'			=> esc_html__( 'Copyright', 'drplus' ),
		'id'			=> 'footer-copyright-section',
		'subsection'	=> true,
		'fields'		=> array(
			[
				'id'		=> 'footer_copyright',
				'type'		=> 'text',
				'title'		=> __( 'Copyright text', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default:<br>%s", 'drplus' ), __( "All rights of this website belong to Doctor plus store.", 'drplus' ) ),
				'default'	=> __( "All rights of this website belong to Doctor plus store.", 'drplus' ),
			],
		),
	)
);
