<?php

use DrPlusUtilsChat as Chat;

defined( 'ABSPATH' ) || exit;

Redux::set_section( // General settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'General settings', 'drplus' ),
		'id'			=> 'chat-general-section',
		'subsection'	=> true,
		'fields'		=> array(
			[
				'id'		=> 'chat-enable-voice-record',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Enable Voice recording', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enable', 'drplus' ) ),
				'on'		=> esc_html__( 'Enable', 'drplus' ),
				'off'		=> esc_html__( 'Disable', 'drplus' ),
				'default'	=> true,
			],
			[
				'id'		=> 'chat-enable-send-file',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Enable Send file', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enable', 'drplus' ) ),
				'on'		=> esc_html__( 'Enable', 'drplus' ),
				'off'		=> esc_html__( 'Disable', 'drplus' ),
				'default'	=> true,
			],
			[
				'id'		=> 'chat-allowed-file-types',
				'type'		=> 'checkbox',
				'title'		=> esc_html__( 'Allowed file types', 'drplus' ),
				'default'	=> '1',
				'options' 	=> Chat::allowed_file_types(),
				'default' 	=> array_map( fn() => 1, Chat::allowed_file_types() ),
				'required'		=> [
					['chat-enable-send-file','=',true],
				],
			],
			[
				'id'		=> 'chat-file-upload-max-size',
				'type'		=> 'spinner',
				'title'		=> __( 'File upload max size (MB)', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), '100' ),
				'default'	=> '100',
				'min'		=> '1',
				'max'		=> intval( ini_get('upload_max_filesize') ),
				'required'	=> [
					['chat-enable-send-file','=',true],
				]
			],
			[
				'id'		=> 'chat-reason-as-first-message',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Send reason for visit as first message', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enable', 'drplus' ) ),
				'on'		=> esc_html__( 'Enable', 'drplus' ),
				'off'		=> esc_html__( 'Disable', 'drplus' ),
				'default'	=> true,
			],
		),
	)
);

Redux::set_section( // Chat page
	$opt_name,
	array(
		'title'			=> esc_html__( 'Chat page', 'drplus' ),
		'id'			=> 'chat-page-section',
		'subsection'	=> true,
		'fields'		=> array(
			[
				'id'		=> 'chat-page-fullscreen',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show chat page in fullscreen', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
			],
			[
				'id'		=> 'chat-page-get-messages-with-ajax',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Get new messages with ajax', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
			],
			[
				'id'		=> 'chat-page-get-message-ajax-interval',
				'type'		=> 'spinner',
				'title'		=> __( 'Check new message interval (seconds)', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), '5' ),
				'default'	=> '5',
				'min'		=> '1',
				'max'		=> '10000',
				'required'	=> [
					['chat-page-get-messages-with-ajax','=',true],
				]
			],
			[
				'id'		=> 'chat-page-background-type',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Chat background type', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Predefined', 'drplus' ) ),
				'options'  => [
					'predefined'	=> esc_html__( 'Select from predefined backgrounds', 'drplus' ),
					'custom'		=> esc_html__( 'Upload custom image', 'drplus' ),
					'none'			=> esc_html__( 'None', 'drplus' ),
				],
				'default'  => 'predefined',
			],
			[
				'id'		=> 'chat-page-predefined-background',
				'type'		=> 'image_select',
				'title'		=> __( 'Chat background', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Background 1', 'drplus' ) ),
				'default' 	=> 'chat-bg-1',
				'mode'		=> 'background-image',
				'options'	=> [
					'chat-bg-1'	=> [
						'alt'	=> esc_html__( "Background 1", 'drplus' ),
						'img'	=> DRPLUS_URI . "assets/images/chat-bg-1.jpg",
						'title'	=> esc_html__( "Background 1", 'drplus' ),
					],
					'chat-bg-2'	=> [
						'alt'	=> esc_html__( "Background 2", 'drplus' ),
						'img'	=> DRPLUS_URI . "assets/images/chat-bg-2.jpg",
						'title'	=> esc_html__( "Background 2", 'drplus' ),
					],
					'chat-bg-3'	=> [
						'alt'	=> esc_html__( "Background 3", 'drplus' ),
						'img'	=> DRPLUS_URI . "assets/images/chat-bg-3.jpg",
						'title'	=> esc_html__( "Background 3", 'drplus' ),
					],
					'chat-bg-4'	=> [
						'alt'	=> esc_html__( "Background 4", 'drplus' ),
						'img'	=> DRPLUS_URI . "assets/images/chat-bg-4.jpg",
						'title'	=> esc_html__( "Background 4", 'drplus' ),
					],
					'chat-bg-5'	=> [
						'alt'	=> esc_html__( "Background 5", 'drplus' ),
						'img'	=> DRPLUS_URI . "assets/images/chat-bg-5.jpg",
						'title'	=> esc_html__( "Background 5", 'drplus' ),
					],
				],
				'required'	=> [
					['chat-page-background-type','=','predefined'],
				]
			],
			[
				'id'				=> 'chat-page-custom-background',
				'type'		 		=> 'media',
				'title'				=> esc_html__( 'Upload chat background', 'drplus' ),
				'compiler'	 		=> true,
				'url'				=> true,
				'preview_size'		=> 'full',
				'mode'				=> 'background-image',
				'library_filter'	=> ['jpeg', 'gif', 'png', 'bmp', 'tiff', 'x-icon', 'svg', 'svg+xml', 'webp'],
				'required'	=> [
					['chat-page-background-type','=','custom'],
				]
			],
		),
	)
);