<?php

use DrPlus\Utils;

defined( 'ABSPATH' ) || exit;

$separator = is_rtl() ? ' &rsaquo; ' : ' &lsaquo; ';

Redux::set_section( // General
	$opt_name,
	array(
		'title'			=> esc_html__( 'General', 'drplus' ),
		'id'			=> 'auth-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // auth
				'id'		=> 'auth',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Authentication', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'desc'		=> esc_html__( 'If you want to use third-party authentication plugins(Like: Digits), disable this.', 'drplus' ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // auth_sms
				'id'		=> 'auth_sms',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Authentication by SMS', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'desc'		=> esc_html__( 'Enable or disable the Authentication by SMS.', 'drplus' ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
				'required'		=> [
					['auth','=',true],
				],
			],
			[ // auth_title
				'id'			=> 'auth_title',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Page title', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), get_bloginfo( 'name', 'display' ) . $separator . __( "Log In", "drplus" ) ),
				'default'		=> get_bloginfo( 'name', 'display' ) . $separator . __( "Log In", "drplus" ),
				'placeholder'	=> get_bloginfo( 'name', 'display' ) . $separator . __( "Log In", "drplus" ),
				'required'		=> [
					['auth','=',true],
				],
			],
			[ // auth_email_required
				'id'		=> 'auth_email_required',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Email requirement status', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Required', 'drplus' ) ),
				'on'		=> esc_html__( 'Required', 'drplus' ),
				'off'		=> esc_html__( 'Optional', 'drplus' ),
				'default'	=> true,
				'required'		=> [
					['auth','=',true],
				],
			],

			[ // divider
				'id'	=> 'auth_divider',
				'type'	=> 'divide',
			],

			[ // auth_show_bg_pattern
				'id'		=> 'auth_show_bg_pattern',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show background pattern', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['auth','=',true],
				],
			],
			[ // auth-background-pattern-type
				'id'		=> 'auth-background-pattern-type',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Auth Background pattern type', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Theme pattern image', 'drplus' ) ),
				'options'  => [
					'theme'			=> esc_html__( 'Theme pattern image', 'drplus' ),
					'custom'		=> esc_html__( 'Upload custom image', 'drplus' ),
				],
				'default'  => 'theme',
				'required'	=> [
					['auth_show_bg_pattern','=',true],
				]
			],
			[ // auth-background-pattern
				'id'				=> 'auth-background-pattern',
				'type'		 		=> 'media',
				'title'				=> esc_html__( 'Auth Background pattern', 'drplus' ),
				'compiler'	 		=> true,
				'url'				=> true,
				'preview_size'		=> 'full',
				'mode'				=> 'background-image',
				'library_filter'	=> ['jpeg', 'gif', 'png', 'bmp', 'tiff', 'x-icon', 'svg', 'svg+xml', 'webp'],
				'required'	=> [
					['auth-background-pattern-type','=','custom'],
				]
			],
			[ // auth_show_logo
				'id'		=> 'auth_show_logo',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show logo', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['auth','=',true],
				],
			],
			[ // auth_logo_type
				'id'		=> 'auth_logo_type',
				'type'		=> 'radio',
				'title'		=> esc_html__( 'Logo type', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Image', 'drplus' ) ),
				'data'		=> [
					'text'	=> esc_html__( 'Text', 'drplus' ),
					'img'	=> esc_html__( 'Image', 'drplus' ),
				],
				'default'	=> 'img',
				'required'	=> [
					['auth','=',true],
					['auth_show_logo','=',true],
				]
			],
			[ // auth_logo_text_type
				'id'		=> 'auth_logo_text_type',
				'type'		=> 'radio',
				'title'		=> esc_html__( 'Text type', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Site title', 'drplus' ) ),
				'data'		=> [
					'title'		=> esc_html__( 'Site title', 'drplus' ),
					'custom'	=> esc_html__( 'Custom', 'drplus' ),
				],
				'default'	=> 'title',
				'required'	=> [
					['auth','=',true],
					['auth_show_logo','=',true],
					['auth_logo_type','=','text'],
				],
			],
			[ // auth_logo_text_custom
				'id'		=> 'auth_logo_text_custom',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Logo text', 'drplus' ),
				'required'	=> [
					['auth','=',true],
					['auth_show_logo','=',true],
					['auth_logo_type','=','text'],
					['auth_logo_text_type','=','custom'],
				],
			],
			[ // auth_logo_img
				'id'				=> 'auth_logo_img',
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
					['auth','=',true],
					['auth_show_logo','=',true],
					['auth_logo_type','=','img'],
				],
			],
			[ // auth_logo_img_size
				'id'		=> 'auth_logo_img_size',
				'type'		=> 'dimensions',
				'title'		=> esc_html__( 'Logo size', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), 'W: 160 & H: 60' ),
				'desc'		=> esc_html__( 'Leave empty for full size', 'drplus' ),
				'default'	=> [
					'width'		=> 91,
					'height'	=> 51
				],
				'required'	=> [
					['auth','=',true],
					['auth_show_logo','=',true],
					['auth_logo_type','=','img'],
				],
			],
			[ // auth_logo_link
				'id'			=> 'auth_logo_link',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Logo URL', 'drplus' ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), home_url() ),
				'default'		=> home_url(),
				'placeholder'	=> home_url(),
				'required'		=> [
					['auth','=',true],
					['auth_show_logo','=',true],
				],
			],
			[ // auth_show_back
				'id'		=> 'auth_show_back',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show back link', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'desc'		=> esc_html__( 'Show back link in footer of the form', 'drplus' ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'		=> [
					['auth','=',true],
				],
			],
			[ // auth_back_label
				'id'			=> 'auth_back_label',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Back link text', 'drplus' ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), __( "Return to home page", 'drplus' ) ),
				'default'		=> __( "Return to home page", 'drplus' ),
				'placeholder'	=> __( "Return to home page", 'drplus' ),
				'required'		=> [
					['auth','=',true],
					['auth_show_back','=',true],
				],
			],
			[ // auth_back_url
				'id'			=> 'auth_back_url',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Back link', 'drplus' ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), home_url() ),
				'default'		=> home_url(),
				'placeholder'	=> home_url(),
				'required'		=> [
					['auth','=',true],
					['auth_show_back','=',true],
				],
			],
			[ // auth_terms
				'id'		=> 'auth_terms',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show terms link', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'desc'		=> esc_html__( 'Terms will show on signup steps', 'drplus' ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'		=> [
					['auth','=',true],
				],
			],
			[ // auth_terms_text
				'id'		=> 'auth_terms_text',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Terms text', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), __( "Membership in the site constitutes agreement to the rules.", 'drplus' ) ),
				'default'	=> __( "Membership in the site constitutes agreement to the rules.", 'drplus' ),
				'required'	=> [
					['auth','=',true],
					['auth_terms','=',true],
				],
			],
			[ // auth_terms_url
				'id'			=> 'auth_terms_url',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Terms link', 'drplus' ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), home_url( 'terms-conditions' ) ),
				'default'		=> home_url( 'terms-conditions' ),
				'placeholder'	=> home_url( 'terms-conditions' ),
				'required'		=> [
					['auth','=',true],
					['auth_terms','=',true],
				],
			],
		),
	)
);

Redux::set_section( // Email Auth
	$opt_name,
	array(
		'title'			=> esc_html__( 'Email/Username', 'drplus' ),
		'id'			=> 'auth-email-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // auth_email
				'id'		=> 'auth_email',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Authentication with email/username', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['auth','=',true],
				],
			],
		),
	)
);

Redux::set_section( // Lost password
	$opt_name,
	array(
		'title'			=> esc_html__( 'Lost password', 'drplus' ),
		'id'			=> 'auth-lost-password-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // lost-password-email-subject
				'id'		=> 'lost-password-email-subject',
				'type'		=> 'text',
				'title'		=> __( "Lost password email subject", 'drplus' ),
				'required'	=> [
					['auth','=',true],
					['auth_email','=',true],
				]
			],
			[ // lost-password-email-template
				'id'		=> 'lost-password-email-template',
				'type'		=> 'editor',
				'title'		=> __( "Lost password email template", 'drplus' ),
				'subtitle'	=> __( "The email structure does not support everything, and the email that is sent may differ from the content you have provided.<br>Use {password} to display the new password.", 'drplus' ),
				'required'	=> [
					['auth','=',true],
					['auth_email','=',true],
				]
			],
		)
	)
);

Redux::set_section( // Redirect
	$opt_name,
	array(
		'title'			=> esc_html__( 'Redirect', 'drplus' ),
		'id'			=> 'auth-redirect-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // auth_redirect
				'id'		=> 'auth_redirect',
				'type'		=> 'text',
				'title'		=> __( "Default redirect URL", 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), Utils::is_wc_active() ? home_url( 'my-account' ) : home_url() ),
				'required'	=> [
					['auth','=',true],
				]
			],
			[ // auth_redirect_force
				'id'		=> 'auth_redirect_force',
				'type'		=> 'switch',
				'title'		=> __( "Force Redirect", 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'desc'		=> __( "Force redirect after login", "drplus" ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'required'	=> [
					['auth','=',true],
				]
			],
		)
	)
);