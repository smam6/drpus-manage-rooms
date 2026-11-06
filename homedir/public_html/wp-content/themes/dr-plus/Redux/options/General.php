<?php

defined( 'ABSPATH' ) || exit;

Redux::set_section( // General settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'General Settings', 'drplus' ),
		'id'			=> 'general-settings-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // use-outside-iran
				'id'		=> 'use-outside-iran',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Use outside Iran', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Disable', 'drplus' ) ),
				'on'		=> esc_html__( 'Enable', 'drplus' ),
				'off'		=> esc_html__( 'Disable', 'drplus' ),
				'desc'		=> esc_html__( 'By activating this option, phone numbers and other information will not be checked based on the country of Iran.', 'drplus' ) . '<br>' .
							   esc_html__( 'Also, sending SMS will be disabled.', 'drplus' ),
				'default'	=> false,
			],
			[ // m-icons
				'id'		=> 'm-icons',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Activate medicine icons', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'No', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> false,
			],
		),
	)
);

Redux::set_section( // Custom codes
	$opt_name,
	array(
		'title'			=> esc_html__( 'Custom codes', 'drplus' ),
		'id'			=> 'general-custom-codes-section',
		'subsection'	=> true,
		'fields'		=> array(
			[
				'id'		=> 'header_custom_code',
				'type'		=> 'ace_editor',
				'title'		=> __( 'Header', 'drplus' ),
				'desc'		=> esc_html__( 'The following code will add to the <head> tag.', 'drplus' ),
				'compiler'	=> true,
				'mode'		=> 'html',
			],
			[
				'id'		=> 'footer_custom_code',
				'type'		=> 'ace_editor',
				'title'		=> __( 'Footer', 'drplus' ),
				'desc'		=> esc_html__( 'The following code will be added to the footer before the closing </body> tag.', 'drplus' ),
				'compiler'	=> true,
				'mode'		=> 'html',
			],
		),
	)
);

Redux::set_section( // 404 settings
	$opt_name,
	array(
		'title'			=> esc_html__( '404 settings', 'drplus' ),
		'id'			=> 'general-404-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // 404_image
				'id'				=> '404_image',
				'type'		 		=> 'media',
				'title'				=> esc_html__( '404 Image', 'drplus' ),
				'compiler'	 		=> true,
				'url'				=> true,
				'preview_size'		=> 'full',
				'library_filter'	=> ['jpeg', 'gif', 'png', 'bmp', 'tiff', 'x-icon', 'svg', 'svg+xml', 'webp'],
				'default'			=> [
					'url'	=> DRPLUS_URI . "assets/images/404.svg",
				],
			],
			[ // 404_title
				'id'		=> '404_title',
				'type'		=> 'text',
				'title'		=> __( "404 Page title", 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( "Default: %s", 'drplus' ), esc_html__( "The desired page was not found.", 'drplus' ) ),
				'default'	=> esc_html__( "The desired page was not found.", 'drplus' ),
			],
			[ // 404_subtitle
				'id'		=> '404_subtitle',
				'type'		=> 'text',
				'title'		=> __( "404 Page subtitle", 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( "Default: %s", 'drplus' ), esc_html__( "This page may not exist or has been deleted.", 'drplus' ) ),
				'default'	=> esc_html__( "This page may not exist or has been deleted.", 'drplus' ),
			],
		),
	)
);