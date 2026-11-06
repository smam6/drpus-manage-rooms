<?php
defined( 'ABSPATH' ) || exit;

Redux::set_section( // Site theme settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'Site theme Setting', 'drplus' ),
		'id'			=> 'color-mode-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // color_mode
				'id'		=> 'color_mode',
				'type'		=> 'button_set',
				'title'		=> esc_html__( 'Site theme', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Light', 'drplus' ) ),
				'options'	=> [
					'light'	=> esc_html__( 'Light', 'drplus' ),
					'dark'	=> esc_html__( 'Dark', 'drplus' ),
					'both'	=> esc_html__( 'Both', 'drplus' ),
				],
				'default'	=> 'light',
			],
			[ // color_mode_auto_behavior
				'id'		=> 'color_mode_auto_behavior',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Auto mode preference', 'drplus' ),
				'subtitle'	=> esc_html__( 'Choose how the automatic mode should prioritize dark or light.', 'drplus' ),
				'options'	=> [
					'prefer_dark'	=> esc_html__( 'Prefer dark first', 'drplus' ),
					'prefer_light'	=> esc_html__( 'Prefer light first', 'drplus' ),
					'system'		=> esc_html__( 'Follow user system', 'drplus' ),
				],
				'default'	=> 'system',
				'required'	=> [
					['color_mode','=', 'both'],
				],
			],
			[ // color_mode_user_switch
				'id'		=> 'color_mode_user_switch',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Allow visitors to switch mode', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disable', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['color_mode','=', 'both'],
				],
			],
			[
				'id'	=> 'theme_style_divider_1',
				'type'	=> 'divide'
			],
			[
				'id'		=> 'page-width-padding-inline',
				'type'		=> 'select',
				'title'		=> esc_html__('Page minimum inline padding in desktop size', 'drplus'),
				'desc'		=> sprintf( __( "Default: %s", 'drplus' ), '72px' ),
				'options'	=> [
					'72'	=> '72px',
					'40'	=> '40px',
					'20'	=> '20px'
				],
				'default'	=> '72',
			],
			[
				'id'		=> 'page-width-padding-inline-mobile',
				'type'		=> 'select',
				'title'		=> esc_html__('Page minimum inline padding in Mobile size', 'drplus'),
				'desc'		=> sprintf( __( "Default: %s", 'drplus' ), '20px' ),
				'options'	=> [
					'72'	=> '72px',
					'40'	=> '40px',
					'20'	=> '20px'
				],
				'default'	=> '20',
			],
			[
				'id'	=> 'theme_style_divider_2',
				'type'	=> 'divide'
			],
		),
	)
);
Redux::set_section( // border radiuses
	$opt_name,
	array(
		'title'			=> esc_html__( 'Border radiuses', 'drplus' ),
		'id'			=> 'border-radiuses-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // global_border_radius_1
				'id'		=> 'global_border_radius_1',
				'type'		=> 'slider',
				'title'		=> esc_html__( 'Global border radius 1', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), '24px' ),
				'default'	=> 24,
				'min'		=> 0,
				'max'		=> 100,
				'step'		=> 1,
				'desc'		=> esc_html__( 'Used for content sections, etc.', 'drplus' ),
				'display_value' => 'text',
			],
			[ // global_border_radius_2
				'id'			=> 'global_border_radius_2',
				'type'			=> 'slider',
				'title'			=> esc_html__( 'Global border radius 2', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '16px' ),
				'default'		=> 16,
				'min'			=> 0,
				'max'			=> 100,
				'step'			=> 1,
				'desc'			=> esc_html__( 'Used for cards, modals, etc.', 'drplus' ),
				'display_value' => 'text',
			],
			[ // field_border_radius
				'id'			=> 'field_border_radius',
				'type'			=> 'slider',
				'title'			=> esc_html__( 'Field border radius', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '16px' ),
				'default'		=> 16,
				'min'			=> 0,
				'max'			=> 100,
				'step'			=> 1,
				'desc'			=> esc_html__( 'Used Fields', 'drplus' ),
				'display_value' => 'text',
			],
			[ // btn_normal_border_radius
				'id'		=> 'btn_normal_border_radius',
				'type'		=> 'slider',
				'title'		=> esc_html__( 'Button normal border radius', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), '16px' ),
				'default'	=> 16,
				'min'		=> 0,
				'max'		=> 100,
				'step'		=> 1,
				'display_value' => 'text',
			],
			[ // btn_small_border_radius
				'id'		=> 'btn_small_border_radius',
				'type'		=> 'slider',
				'title'		=> esc_html__( 'Button small border radius', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), '12px' ),
				'default'	=> 12,
				'min'		=> 0,
				'max'		=> 100,
				'step'		=> 1,
				'display_value' => 'text',
			],
		),
	)
);
Redux::set_section( // Button
	$opt_name,
	array(
		'title'			=> esc_html__( 'Buttons', 'drplus' ),
		'id'			=> 'button-styles-section',
		'subsection'	=> true,
		'fields'		=> array(
			[
				'id'		=> 'button-bg-style',
				'type'		=> 'select',
				'title'		=> esc_html__('Button Background style', 'drplus'),
				'desc'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Simple', 'drplus' ) ),
				'options'	=> [
					'simple'	=> esc_html__( 'Simple', 'drplus' ),
					'gradient'	=> esc_html__( 'With inner gradient', 'drplus' ),
				],
				'default'	=> 'simple',
			],
		),
	)
);