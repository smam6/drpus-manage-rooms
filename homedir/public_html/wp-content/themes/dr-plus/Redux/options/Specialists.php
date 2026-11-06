<?php

use DrPlus\Utils;

defined( 'ABSPATH' ) || exit;

$separator = is_rtl() ? ' &rsaquo; ' : ' &lsaquo; ';
$max_upload_size_bytes = Utils::get_max_upload_size();

Redux::set_section( // Specialists settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'Specialists settings', 'drplus' ),
		'id'			=> 'specialists-general-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // single_specialist_mode
				'id'		=> 'single_specialist_mode',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Single-Specialist mode', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Disable', 'drplus' ) ),
				'on'		=> esc_html__( 'Enable', 'drplus' ),
				'off'		=> esc_html__( 'Disable', 'drplus' ),
				'desc'		=> esc_html__( 'By enabling Single-Specialist mode, all features and sections related to Multi-Expert functionality will be disabled.', 'drplus' ) . '<br>' . sprintf( __( 'After Change this option, go to <a href="%s" target="_blank">permalink options</a> and save the settings.', 'drplus' ), admin_url( "options-permalink.php" ) ),
				'default'	=> false,
			],
			[ // single_specialist_id
				'id'			=> 'single_specialist_id',
				'type'			=> 'select',
				'title'			=> esc_html__( 'Specialist', 'drplus' ),
				'subtitle'		=> esc_html__( 'Select the specialist', 'drplus' ),
				'placeholder'	=> esc_html__( 'Search and select', 'drplus' ),
				'data'		=> 'posts',
				'args'     => [
					'post_type' => ['specialist'],
					'post_status' => 'publish',
				],
				'ajax'     => true,
				'required'	=> [
					['single_specialist_mode','=',true]
				]
			],
			[ // specialist_identity_max_upload_size
				'id'		=> 'specialist_identity_max_upload_size',
				'type'		=> 'spinner',
				'title'		=> esc_html__( "Max upload size for identity files (MB)", 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), Utils::convert_bytes_to_mb( $max_upload_size_bytes ) ),
				'min'		=> 1,
				'max'		=> Utils::convert_bytes_to_mb( $max_upload_size_bytes, false ),
				'default'	=> Utils::convert_bytes_to_mb( $max_upload_size_bytes, false ),
			],
		),
	)
);

Redux::set_section( // Onboard settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'Onboard settings', 'drplus' ),
		'id'			=> 'specialists-onboard-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // specialist_onboard
				'id'		=> 'specialist_onboard',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Onboarding', 'drplus' ),
				'desc'		=> esc_html__( 'Allow users to submit requests for specialist profiles', 'drplus' ) . "<br>" . sprintf( __( 'After enabling this option, go to <a href="%s" target="_blank">permalink options</a> and save the settings.', 'drplus' ), admin_url( "options-permalink.php" ) ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['single_specialist_mode', '!=', true],
				]
			],
			[ // onboard_page_title
				'id'			=> 'onboard_page_title',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Page title', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), get_bloginfo( 'name', 'display' ) . $separator . __( "Onboarding", "drplus" ) ),
				'default'		=> get_bloginfo( 'name', 'display' ) . $separator . __( "Onboarding", "drplus" ),
				'placeholder'	=> get_bloginfo( 'name', 'display' ) . $separator . __( "Onboarding", "drplus" ),
				'required'		=> [
					['specialist_onboard','=',true],
					['single_specialist_mode', '!=', true],
				],
			],
			[ // onboard_title
				'id'			=> 'onboard_title',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Form title', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), __( "Onboarding", "drplus" ) ),
				'default'		=> __( "Onboarding", "drplus" ),
				'placeholder'	=> __( "Onboarding", "drplus" ),
				'required'		=> [
					['specialist_onboard','=',true],
					['single_specialist_mode', '!=', true],
				],
			],
			[ // onboard_show_bg_pattern
				'id'		=> 'onboard_show_bg_pattern',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show background pattern', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['specialist_onboard','=',true],
					['single_specialist_mode', '!=', true],
				],
			],
			[ // onboard_show_logo
				'id'		=> 'onboard_show_logo',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show logo', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['specialist_onboard','=',true],
					['single_specialist_mode', '!=', true],
				],
			],
			[ // onboard_logo_type
				'id'		=> 'onboard_logo_type',
				'type'		=> 'radio',
				'title'		=> esc_html__( 'Logo type', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Image', 'drplus' ) ),
				'data'		=> [
					'text'	=> esc_html__( 'Text', 'drplus' ),
					'img'	=> esc_html__( 'Image', 'drplus' ),
				],
				'default'	=> 'img',
				'required'	=> [
					['specialist_onboard','=',true],
					['single_specialist_mode', '!=', true],
					['onboard_show_logo','=',true],
				]
			],
			[ // onboard_logo_text_type
				'id'		=> 'onboard_logo_text_type',
				'type'		=> 'radio',
				'title'		=> esc_html__( 'Text type', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Site title', 'drplus' ) ),
				'data'		=> [
					'title'		=> esc_html__( 'Site title', 'drplus' ),
					'custom'	=> esc_html__( 'Custom', 'drplus' ),
				],
				'default'	=> 'title',
				'required'	=> [
					['specialist_onboard','=',true],
					['single_specialist_mode', '!=', true],
					['onboard_show_logo','=',true],
					['onboard_logo_type','=','text'],
				],
			],
			[ // onboard_logo_text_custom
				'id'		=> 'onboard_logo_text_custom',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Logo text', 'drplus' ),
				'required'	=> [
					['specialist_onboard','=',true],
					['single_specialist_mode', '!=', true],
					['onboard_show_logo','=',true],
					['onboard_logo_type','=','text'],
					['onboard_logo_text_type','=','custom'],
				],
			],
			[ // onboard_logo_img
				'id'				=> 'onboard_logo_img',
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
					['specialist_onboard','=',true],
					['single_specialist_mode', '!=', true],
					['onboard_show_logo','=',true],
					['onboard_logo_type','=','img'],
				],
			],
			[ // onboard_logo_img_size
				'id'		=> 'onboard_logo_img_size',
				'type'		=> 'dimensions',
				'title'		=> esc_html__( 'Logo size', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), 'W: 160 & H: 60' ),
				'desc'		=> esc_html__( 'Leave empty for full size', 'drplus' ),
				'default'	=> [
					'width'		=> 91,
					'height'	=> 51
				],
				'required'	=> [
					['specialist_onboard','=',true],
					['single_specialist_mode', '!=', true],
					['onboard_show_logo','=',true],
					['onboard_logo_type','=','img'],
				],
			],
			[ // onboard_logo_link
				'id'			=> 'onboard_logo_link',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Logo URL', 'drplus' ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), home_url() ),
				'default'		=> home_url(),
				'placeholder'	=> home_url(),
				'required'		=> [
					['specialist_onboard','=',true],
					['single_specialist_mode', '!=', true],
					['onboard_show_logo','=',true],
				],
			],
			[ // onboard_show_back
				'id'		=> 'onboard_show_back',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show back link', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'desc'		=> esc_html__( 'Show back link in footer of the form', 'drplus' ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'		=> [
					['specialist_onboard','=',true],
					['single_specialist_mode', '!=', true],
				],
			],
			[ // onboard_back_label
				'id'			=> 'onboard_back_label',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Back link text', 'drplus' ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), __( "Return to home page", 'drplus' ) ),
				'default'		=> __( "Return to home page", 'drplus' ),
				'placeholder'	=> __( "Return to home page", 'drplus' ),
				'required'		=> [
					['specialist_onboard','=',true],
					['single_specialist_mode', '!=', true],
					['onboard_show_back','=',true],
				],
			],
			[ // onboard_back_url
				'id'			=> 'onboard_back_url',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Back link', 'drplus' ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), home_url() ),
				'default'		=> home_url(),
				'placeholder'	=> home_url(),
				'required'		=> [
					['specialist_onboard','=',true],
					['single_specialist_mode', '!=', true],
					['onboard_show_back','=',true],
				],
			],
			
			[
				'id'   =>'onboard_divider_1',
				'type' => 'divide'
			],

			[ // onboard-info-field-name-enabled
				'id'		=> 'onboard-info-field-name-enabled',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Enable name field', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
			],
			[ // onboard-info-field-name-required
				'id'		=> 'onboard-info-field-name-required',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Is name field Required?', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'		=> [
					['onboard-info-field-name-enabled','=',true]
				]
			],
			[ // onboard-info-field-subtitle-enabled
				'id'		=> 'onboard-info-field-subtitle-enabled',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Enable subtitle field', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
			],
			[ // onboard-info-field-subtitle-required
				'id'		=> 'onboard-info-field-subtitle-required',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Is subtitle field Required?', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'		=> [
					['onboard-info-field-subtitle-enabled','=',true]
				]
			],
			[ // onboard-info-field-email-enabled
				'id'		=> 'onboard-info-field-email-enabled',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Enable email field', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
			],
			[ // onboard-info-field-email-required
				'id'		=> 'onboard-info-field-email-required',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Is email field Required?', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'		=> [
					['onboard-info-field-email-enabled','=',true]
				]
			],
			[ // onboard-info-field-birthday-enabled
				'id'		=> 'onboard-info-field-birthday-enabled',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Enable birthday field', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
			],
			[ // onboard-info-field-birthday-required
				'id'		=> 'onboard-info-field-birthday-required',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Is birthday field Required?', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'		=> [
					['onboard-info-field-birthday-enabled','=',true]
				]
			],
			[ // onboard-info-field-nid-enabled
				'id'		=> 'onboard-info-field-nid-enabled',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Enable national ID field', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
			],
			[ // onboard-info-field-nid-required
				'id'		=> 'onboard-info-field-nid-required',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Is national ID field Required?', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'		=> [
					['onboard-info-field-nid-enabled','=',true]
				]
			],
			[ // onboard-info-field-specialist-code-enabled
				'id'		=> 'onboard-info-field-specialist-code-enabled',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Enable medical ID field', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
			],
			[ // onboard-info-field-specialist-code-required
				'id'		=> 'onboard-info-field-specialist-code-required',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Is medical ID field Required?', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'		=> [
					['onboard-info-field-specialist-code-enabled','=',true]
				]
			],
			[ // onboard-info-field-phone-enabled
				'id'		=> 'onboard-info-field-phone-enabled',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Enable phone field', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
			],
			[ // onboard-info-field-phone-required
				'id'		=> 'onboard-info-field-phone-required',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Is phone field Required?', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'		=> [
					['onboard-info-field-phone-enabled','=',true]
				]
			],
			[ // onboard-info-field-gender-enabled
				'id'		=> 'onboard-info-field-gender-enabled',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Enable gender field', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
			],
			[ // onboard-info-field-gender-required
				'id'		=> 'onboard-info-field-gender-required',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Is gender field Required?', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'		=> [
					['onboard-info-field-gender-enabled','=',true]
				]
			],
			[ // onboard-info-field-bio-enabled
				'id'		=> 'onboard-info-field-bio-enabled',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Enable biography field', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
			],
			[ // onboard-info-field-bio-required
				'id'		=> 'onboard-info-field-bio-required',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Is biography field Required?', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'No', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> false,
				'required'		=> [
					['onboard-info-field-bio-enabled','=',true]
				]
			],
		)
	)
);

Redux::set_section( // Specialists single settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'Single specialist settings', 'drplus' ),
		'id'			=> 'specialists-single-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // single_specialist_show_breadcrumb
				'id'		=> 'single_specialist_show_breadcrumb',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show breadcrumb', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // single_specialist_show_reviews
				'id'		=> 'single_specialist_show_reviews',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show reviews', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // single_specialist_show_reviews_stars
				'id'		=> 'single_specialist_show_reviews_stars',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show reviews stars', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // single_specialist_show_specialist_code
				'id'		=> 'single_specialist_show_specialist_code',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show specialist code', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // single_specialist_show_reserve_btn
				'id'		=> 'single_specialist_show_reserve_btn',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show reserve button', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // single_specialist_show_services
				'id'		=> 'single_specialist_show_services',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show services', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // single_specialist_show_insurances
				'id'		=> 'single_specialist_show_insurances',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show insurances', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['insurance','=',true]
				]
			],
			[ // single_specialist_show_offices
				'id'		=> 'single_specialist_show_offices',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show offices', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // single_specialist_show_patients_review_stat
				'id'		=> 'single_specialist_show_patients_review_stat',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show patient satisfaction stat', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // single_specialist_show_online_consultation_stat
				'id'		=> 'single_specialist_show_online_consultation_stat',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show online consultation stat', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // single_specialist_show_visits_count_stat
				'id'		=> 'single_specialist_show_visits_count_stat',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show visits count stat', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // single_specialist_show_articles_stat
				'id'		=> 'single_specialist_show_articles_stat',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show number of articles stat', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // single_specialist_show_introduction
				'id'		=> 'single_specialist_show_introduction',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show introduction', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[
				'id'		=> 'single_specialist_limit_introduction_height',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Limit introduction height and show "show more" button', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'No', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> false,
				'required'	=> [
					['single_specialist_show_introduction', '=', true]
				],
			],
			[
				'id'		=> 'single_specialist_introduction_limited_height',
				'type'		=> 'dimensions',
				'title'		=> esc_html__( 'Introduction limited height', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), '400px' ),
				'mode'		=> [
					'width'		=> false,
					'height'	=> true
				],
				'width'		=> false,
				'units'    => array('em','px','%', 'vh'),
				'units_extended'	=> true,
				'default'		=> [
					'height'	=> '500'
				],
				'required'	=> [
					['single_specialist_limit_introduction_height', '=', true],
					['single_specialist_show_introduction', '=', true]
				]
			],
			[ // single_specialist_show_certificates
				'id'		=> 'single_specialist_show_certificates',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show certificates', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // single_specialist_show_certificate_image
				'id'		=> 'single_specialist_show_certificate_image',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show certificate image by click on it', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['single_specialist_show_certificates','=',true]
				]
			],
			[ // single_specialist_show_certificates_verified
				'id'		=> 'single_specialist_show_certificates_verified',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show certificates verified text', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['single_specialist_show_certificates','=',true]
				]
			],
			[ // single_specialist_certificates_verified_text
				'id'			=> 'single_specialist_certificates_verified_text',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Verified certificates text', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), sprintf( esc_html__( "All of {name}'s credentials have been verified by %s", 'drplus' ), get_bloginfo( 'name' ) ) ),
				'default'		=> sprintf( esc_html__( "All of {name}'s credentials have been verified by %s", 'drplus' ), get_bloginfo( 'name' ) ),
				'placeholder'	=> sprintf( esc_html__( "All of {name}'s credentials have been verified by %s", 'drplus' ), get_bloginfo( 'name' ) ),
			],
			[ // single_specialist_show_faqs
				'id'		=> 'single_specialist_show_faqs',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show FAQs', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // single_specialist_show_related_specialists
				'id'		=> 'single_specialist_show_related_specialists',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show related specialists', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // specialist-code-label
				'id'			=> 'specialist-code-label',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Specialist code label', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Medical system number', 'drplus' ) ),
				'default'		=> esc_html__( 'Medical system number', 'drplus' ),
				'placeholder'	=> esc_html__( 'Medical system number', 'drplus' ),
			],
			[ // single_specialist_not_available_reserve_text
				'id'			=> 'single_specialist_not_available_reserve_text',
				'type'			=> 'text',
				'title'			=> esc_html__( 'notice text when reserve is not available', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Online appointment booking is not yet available for {name}', 'drplus' ) ),
				'desc'			=> esc_html__( 'You can use \'{name}\' for show specialist name', 'drplus' ),
				'default'		=> esc_html__( 'Online appointment booking is not yet available for {name}', 'drplus' ),
				'placeholder'	=> esc_html__( 'Online appointment booking is not yet available for {name}', 'drplus' ),
			],
			[ // single_specialist_related_specialists_verified_text
				'id'			=> 'archive_specialist_verified_text',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Verified text', 'drplus' ),
				'desc'			=> esc_html__( 'If you leave it empty, the verified badge will not be shown.', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), sprintf( esc_html__( 'Verified by %s', 'drplus' ), get_bloginfo( 'name' ) ) ),
				'default'		=> sprintf( esc_html__( 'Verified by %s', 'drplus' ), get_bloginfo( 'name' ) ),
				'placeholder'	=> sprintf( esc_html__( 'Verified by %s', 'drplus' ), get_bloginfo( 'name' ) ),
			],
		),
	)
);

Redux::set_section( // Specialists archive settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'Specialists archive settings', 'drplus' ),
		'id'			=> 'specialists-archive-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // archive_specialist_breadcrumb
				'id'		=> 'archive_specialist_breadcrumb',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show breadcrumb', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // archive_specialist_show_title
				'id'		=> 'archive_specialist_show_title',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show archive title', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // archive_specialist_title_icon
				'id'			=> 'archive_specialist_title_icon',
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
					['archive_specialist_show_title','=',true]
				]
			],
			[ // archive_specialist_show_sidebar
				'id'		=> 'archive_specialist_show_sidebar',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show archive sidebar', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // archive_specialist_sidebar
				'id'		=> 'archive_specialist_sidebar',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Archive sidebar', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Specialist archive sidebar', 'drplus' ) ),
				'data'		=> 'sidebars',
				'default'	=> 'archive_specialist',
				'required'	=> [
					['archive_specialist_show_sidebar','=',true],
				]
			],
			[ // archive_specialist_desktop_cols
				'id'		=> 'archive_specialist_desktop_cols',
				'type'		=> 'spinner',
				'title'		=> esc_html__( "Desktop columns", 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '4' ),
				'min'		=> 1,
				'max'		=> 6,
				'default'	=> 4,
			],
			[ // archive_specialist_desktop_gap
				'id'		=> 'archive_specialist_desktop_gap',
				'type'		=> 'spinner',
				'title'		=> esc_html__( "Desktop gap (px)", 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '24' ),
				'min'		=> 1,
				'max'		=> 64,
				'default'	=> 16,
			],
			[ // archive_specialist_tablet_cols
				'id'		=> 'archive_specialist_tablet_cols',
				'type'		=> 'spinner',
				'title'		=> esc_html__( "Tablet columns", 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '2' ),
				'min'		=> 1,
				'max'		=> 6,
				'default'	=> 2,
			],
			[ // archive_specialist_tablet_gap
				'id'		=> 'archive_specialist_tablet_gap',
				'type'		=> 'spinner',
				'title'		=> esc_html__( "Tablet gap (px)", 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '24' ),
				'min'		=> 1,
				'max'		=> 64,
				'default'	=> 16,
			],
			[ // archive_specialist_mobile_cols
				'id'		=> 'archive_specialist_mobile_cols',
				'type'		=> 'spinner',
				'title'		=> esc_html__( "Mobile columns", 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '1' ),
				'min'		=> 1,
				'max'		=> 6,
				'default'	=> 1,
			],
			[ // archive_specialist_mobile_gap
				'id'		=> 'archive_specialist_mobile_gap',
				'type'		=> 'spinner',
				'title'		=> esc_html__( "Mobile gap (px)", 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '24' ),
				'min'		=> 1,
				'max'		=> 64,
				'default'	=> 16,
			],
			[ // archive_specialist_card_type
				'id'		=> 'archive_specialist_card_type',
				'type'		=> 'image_select',
				'title'		=> esc_html__( "Specialists display type", 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( "Card 2", 'drplus' ) ),
				'options'	=> [
					'card-1'	=> [
						'alt'	=> esc_html__( "Card 1", 'drplus' ),
						'img'	=> DRPLUS_URI . "assets/images/backend/specialist-card-1.jpg",
						'title'	=> esc_html__( "Card 1", 'drplus' ),
					],
					'card-2'	=> [
						'alt'	=> esc_html__( "Card 2", 'drplus' ),
						'img'	=> DRPLUS_URI . "assets/images/backend/specialist-card-2.jpg",
						'title'	=> esc_html__( "Card 2", 'drplus' ),
					],
					'list'	=> [
						'alt'	=> esc_html__( "List", 'drplus' ),
						'img'	=> DRPLUS_URI . "assets/images/backend/specialist-list.jpg",
						'title'	=> esc_html__( "List", 'drplus' ),
					],
				],
				'default'	=> 'card-2',
			],
			[ // archive_specialist_verified_text
				'id'			=> 'archive_specialist_verified_text',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Verified text', 'drplus' ),
				'desc'			=> esc_html__( 'If you leave it empty, the verified badge will not be shown.', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), sprintf( esc_html__( 'Verified by %s', 'drplus' ), get_bloginfo( 'name' ) ) ),
				'default'		=> sprintf( esc_html__( 'Verified by %s', 'drplus' ), get_bloginfo( 'name' ) ),
				'placeholder'	=> sprintf( esc_html__( 'Verified by %s', 'drplus' ), get_bloginfo( 'name' ) ),
			],
		),
	)
);