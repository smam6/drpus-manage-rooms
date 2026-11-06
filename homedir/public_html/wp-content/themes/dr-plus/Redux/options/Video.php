<?php

defined( 'ABSPATH' ) || exit;

Redux::set_section( // Skyroom settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'Skyroom settings', 'drplus' ),
		'id'			=> 'video-general-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // skyroom-api
				'id'			=> 'skyroom-api',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Skyroom API', 'drplus' ),
				'subtitle'		=> esc_html__( 'Get the API key from Skyroom support.', 'drplus' ),
				'placeholder'	=> 'apikey-...',
				'required'		=> [
					['enable-booking','=',true],
				],
			],
			[ // skyroom-op-login-first
				'id'		=> 'skyroom-op-login-first',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'The operator must enter first', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'		=> [
					['enable-booking','=',true]
				]
			],
			[ // skyroom-room-title
				'id'			=> 'skyroom-room-title',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Room title', 'drplus' ),
				'subtitle'		=> esc_html__( 'Consultation with {specialist_name}', 'drplus' ),
				'desc'			=> esc_html__( '{specialist_name} : Specialist name', 'drplus' ),
				'default'		=> esc_html__( 'Consultation with {specialist_name}', 'drplus' ),
				'required'		=> [
					['enable-booking','=',true],
				],
			],
			[ // skyroom-add-time
				'id'			=> 'skyroom-add-time',
				'type'			=> 'slider',
				'title'			=> esc_html__( 'Additional time duration (Minute)', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '5' ),
				'default'		=> 5,
				"min"       	=> 0,
				"step"      	=> 1,
				"max"       	=> 15,
				'display_value'	=> 'text',
				'placeholder'	=> '5',
				'desc'			=> esc_html__( 'You can allocate additional time for each consultation session.', 'drplus' ),
				'required'		=> [
					['enable-booking','=',true],
				],
			],
			[ // skyroom-patient-role
				'id'		=> 'skyroom-patient-role',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Patient role in room', 'drplus' ),
				'subtitle'	=> esc_html__( 'Choose a role from list', 'drplus' ),
				'options'	=> [
					'user'	=> esc_html__( 'User', 'drplus' ),
					'op'	=> esc_html__( 'Operator', 'drplus' ),
				],
				'default'	=> 'user',
				'required'	=> [
					['enable-booking','=',true]
				]
			],
		)
	)
);

Redux::set_section( // Texts settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'Texts settings', 'drplus' ),
		'id'			=> 'video-texts-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // video-enter-btn-text
				'id'			=> 'video-enter-btn-text',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Enter video call button text', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Enter the video call', 'drplus' ) ),
				'default'		=> esc_html__( 'Enter the video call', 'drplus' ),
				'required'		=> [
					['enable-booking','=',true],
				],
			],
			[ // video-enter-btn-icon
				'id'			=> 'video-enter-btn-icon',
				'type'			=> 'icon_select',
				'title'			=> esc_html__( 'Button icon', 'drplus' ),
				'compiler'		=> true,
				'default'		=> 'drplus-icon-video',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> DRPLUS_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Doctor plus icons', 'drplus' ),
						'prefix'	=> 'drplus-icon',
					],
				],
			],
			[ // video-not-started-text
				'id'			=> 'video-specialist-not-started-text',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Not started session message for specialist', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'The session will start on {start_time} with {patient_name}', 'drplus' ) ),
				'desc'			=> esc_html__( '{patient_name} : Patient name', 'drplus' ) . '<br>' .
								   esc_html__( '{start_time} : Start time of the session', 'drplus' ) . '<br>' .
								   esc_html__( '{end_time} : End time of the session', 'drplus' )
									,
				'default'		=> esc_html__( 'The session will start on {start_time} with {specialist_name}', 'drplus' ),
				'required'		=> [
					['enable-booking','=',true],
				],
			],
			[ // video-not-started-text
				'id'			=> 'video-visitor-not-started-text',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Not started session message for visitor', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'The session will start on {start_time} with {specialist_name}', 'drplus' ) ),
				'desc'			=> esc_html__( '{specialist_name} : Specialist name', 'drplus' ) . '<br>' .
								   esc_html__( '{start_time} : Start time of the session', 'drplus' ) . '<br>' .
								   esc_html__( '{end_time} : End time of the session', 'drplus' )
									,
				'default'		=> esc_html__( 'The session will start on {start_time} with {specialist_name}', 'drplus' ),
				'required'		=> [
					['enable-booking','=',true],
				],
			],
			[ // video-ended-text
				'id'			=> 'video-specialist-ended-text',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Ended session message for specialist', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'This session ended on {end_time}', 'drplus' ) ),
				'desc'			=> esc_html__( '{patient_name} : Patient name', 'drplus' ) . '<br>' .
								   esc_html__( '{start_time} : Start time of the session', 'drplus' ) . '<br>' .
								   esc_html__( '{end_time} : End time of the session', 'drplus' )
									,
				'default'		=> esc_html__( 'This session ended on {end_time}', 'drplus' ),
				'required'		=> [
					['enable-booking','=',true],
				],
			],
			[ // video-ended-text
				'id'			=> 'video-patient-ended-text',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Ended session message for visitor', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'This session ended on {end_time}', 'drplus' ) ),
				'desc'			=> esc_html__( '{specialist_name} : Specialist name', 'drplus' ) . '<br>' .
								   esc_html__( '{start_time} : Start time of the session', 'drplus' ) . '<br>' .
								   esc_html__( '{end_time} : End time of the session', 'drplus' )
									,
				'default'		=> esc_html__( 'This session ended on {end_time}', 'drplus' ),
				'required'		=> [
					['enable-booking','=',true],
				],
			],
		)
	)
);