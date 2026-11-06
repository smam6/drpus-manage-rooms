<?php
namespace MJ\Whitebox\ElementorControls;

use MJ\Whitebox\ElementorControls;
use MJ\Whitebox\Utils;

class Slider extends ElementorControls {
	public static $default_next_arrow_icon = [];
	public static $default_prev_arrow_icon = [];

	/**
	 * Add slider settings controls to an Elementor widget.
	 *
	 * Defines responsive slide settings for desktop, tablet, and mobile,
	 * including slide count, type (auto/count), and spacing. Supports
	 * custom exclusions and additional arguments.
	 *
	 * @param object $object Elementor widget instance to add controls to.
	 * @param array[string|mixed] $args Optional. Additional arguments like 'section', 'excludes', and custom 'controls'.
	 *
	 * @return void
	 */
	public static function settings_controls( $object, $args = [] ) {
		$default_controls = [
			'desktop_slides_type' => [
				'label'		=> esc_html__( "Desktop slides type", 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'count',
				'options'	=> [
					'count'	=> __( 'Count', 'mj-whitebox' ),
					'auto'	=> __( 'Auto', 'mj-whitebox' ),
				],
			],
			'desktop_slides' => [
				'label'		=> esc_html__( "Desktop visible slides", 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'default'	=> 4,
				'condition'	=> [
					'desktop_slides_type'	=> 'count',
				]
			],
			'desktop_slides_space' => [
				'label'		=> esc_html__( "Desktop slides space", 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 0,
				'default'	=> 24,
			],
			'tablet_slides_type' => [
				'label'		=> esc_html__( "Tablet slides type", 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'auto',
				'options'	=> [
					'count'	=> __( 'Count', 'mj-whitebox' ),
					'auto'	=> __( 'Auto', 'mj-whitebox' ),
				],
			],
			'tablet_slides' => [
				'label'		=> esc_html__( "Tablet visible slides", 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'default'	=> 4,
				'condition'	=> [
					'tablet_slides_type'	=> 'count',
				]
			],
			'tablet_slides_space' => [
				'label'		=> esc_html__( "Tablet slides space", 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 0,
				'default'	=> 16,
			],
			'mobile_slides_type' => [
				'label'		=> esc_html__( "Mobile slides type", 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'auto',
				'options'	=> [
					'count'	=> __( 'Count', 'mj-whitebox' ),
					'auto'	=> __( 'Auto', 'mj-whitebox' ),
				],
			],
			'mobile_slides' => [
				'label'		=> esc_html__( "Mobile visible slides", 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'default'	=> 4,
				'condition'	=> [
					'mobile_slides_type'	=> 'count',
				]
			],
			'mobile_slides_space' => [
				'label'		=> esc_html__( "Mobile slides space", 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 0,
				'default'	=> 16,
			],
			'autoplay'	=> [
				'label'			=> esc_html__( 'Autoplay', 'mj-whitebox' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'mj-whitebox' ),
				'label_off'		=> esc_html__( 'No', 'mj-whitebox' ),
				'separator'		=> 'before',
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			],
			'autoplay_time' => [
				'label'			=> esc_html__( 'Autoplay time (s)', 'mj-whitebox' ),
				'description'	=> esc_html__( 'seconds', 'mj-whitebox' ),
				'type'			=> \Elementor\Controls_Manager::NUMBER,
				'min'			=> 1,
				'default'		=> 10,
				'condition'		=> [
					'autoplay'	=> 'yes'
				],
			],
			'show_arrows' => [
				'label'			=> esc_html__( 'Show arrows', 'mj-whitebox' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'mj-whitebox' ),
				'label_off'		=> esc_html__( 'No', 'mj-whitebox' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
				'separator'		=> 'before',
			],
			'next_arrow_icon'	=> [
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Next arrow icon', 'mj-whitebox' ),
				'skin'			=> 'inline',
				'label_block'	=> false,
				'default'		=> static::$default_next_arrow_icon,
				'condition'		=> [
					'show_arrows'	=> 'yes'
				],
			],
			'prev_arrow_icon'	=> [
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Previous arrow icon', 'mj-whitebox' ),
				'skin'			=> 'inline',
				'label_block'	=> false,
				'default'		=> static::$default_prev_arrow_icon,
				'condition'		=> [
					'show_arrows'	=> 'yes'
				],
			],
			'loop' => [
				'label'			=> esc_html__( 'Loop', 'mj-whitebox' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'mj-whitebox' ),
				'label_off'		=> esc_html__( 'No', 'mj-whitebox' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
				'separator'		=> 'before',
			],
			'show_dots'	=> [
				'label'			=> esc_html__( 'Show dots', 'mj-whitebox' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'mj-whitebox' ),
				'label_off'		=> esc_html__( 'No', 'mj-whitebox' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
				'separator'		=> 'before',
			],
		];
		$args = Utils::check_default( $args, [
			'section'	=> [
				'name'	=> 'slider_settings_section',
				'label'	=> esc_html__( 'Slider settings', 'mj-whitebox' ),
			],
			'excludes'	=> [],
			'only'		=> [],
			'controls'	=> $default_controls
		] );
		
		$section_args = [
			'label'	=> $args['section']['label'],
			'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
		];
		if( !empty( $args['section']['condition'] ) ) {
			$section_args['condition'] = $args['section']['condition'];
		}
		if( !empty( $args['section']['conditions'] ) ) {
			$section_args['conditions'] = $args['section']['conditions'];
		}
		$object->start_controls_section(
			$args['section']['name'],
			$section_args
		);

		parent::_add_controls( $object, $default_controls, '', $args );

		$object->end_controls_section();
	}

	/**
	 * Register options controls for slider.
	 * Don't use this function with "slider_settings_controls"
	 *
	 * @param object $object  Elementor widget/section object.
	 * @param array  $args    Optional. Arguments for controls. Default empty array.
	 * @param bool   $add_display_conditions_to_section Optional. Whether to add display conditions. Default false.
	 *
	 * @return void
	 */
	public static function options_controls( $object, $args = [], $add_display_conditions_to_section = false ) {
		$args = Utils::check_default( $args, [
			'section'	=> [
				'name'			=> 'slider_options_controls',
			],
			'excludes'	=> [],
			'controls'	=> [
				'autoplay'	=> [
					'separator'	=> 'default'
				]
			],
		] );
		$default_excludes = [
			'desktop_slides_type',
			'desktop_slides',
			'desktop_slides_space',
			'tablet_slides_type',
			'tablet_slides',
			'tablet_slides_space',
			'mobile_slides_type',
			'mobile_slides',
			'mobile_slides_space',
		];

		foreach( $default_excludes as $control_name ) {
			if( !isset( $args['controls'][$control_name] ) && !in_array( $control_name, $args['excludes'] ) ) {
				$args['excludes'][] = $control_name;
			}
		}

		if( $add_display_conditions_to_section ) {
			$args['section']['conditions'] = [
				'relation'	=> 'or',
				'terms'	=> [
					[
						'name'		=> 'desktop_slider',
						'operator'	=> '==',
						'value'		=> 'yes'
					],
					[
						'name'		=> 'tablet_slider',
						'operator'	=> '==',
						'value'		=> 'yes'
					],
					[
						'name'		=> 'mobile_slider',
						'operator'	=> '==',
						'value'		=> 'yes'
					],
				],
			];
		}
		self::settings_controls( $object, $args );
	}

	/**
	 * Adds autoplay controls to an Elementor widget.
	 *
	 * This function provides two controls:
	 * 1. A switcher to enable or disable autoplay.
	 * 2. A number input to set the autoplay interval in seconds, visible only if autoplay is enabled.
	 *
	 * @param \Elementor\Widget_Base $object The Elementor widget instance to which the controls will be added.
	 * 
	 * @return void
	 */
	public static function autoplay_controls( $object, $args = [] ) {
		$default_controls = [
			'autoplay'	=> [
				'label'			=> esc_html__( 'Autoplay', 'mj-whitebox' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'mj-whitebox' ),
				'label_off'		=> esc_html__( 'No', 'mj-whitebox' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			],
			'autoplay_time'	=> [
				'label'			=> esc_html__( 'Autoplay time (s)', 'mj-whitebox' ),
				'description'	=> esc_html__( 'seconds', 'mj-whitebox' ),
				'type'			=> \Elementor\Controls_Manager::NUMBER,
				'min'			=> 1,
				'default'		=> 10,
				'condition'		=> [
					'autoplay'	=> 'yes'
				],
			],
		];
		$args = Utils::check_default( $args, [
			'excludes'	=> [],
			'controls'	=> $default_controls,
		] );
		
		parent::_add_controls( $object, $default_controls, '', $args );
	}

	/**
	 * Adds style controls for slider navigation arrows in an Elementor widget.
	 *
	 * This function allows customization of arrow appearance, including color, size, and other styles.
	 *
	 * @param \Elementor\Widget_Base $object The Elementor widget instance to which the controls will be added.
	 * @param string $arrows_btn_selector Something like: .bijan-slider-nav-btn
	 * @param array $args Optional. Configuration array to customize prefix, selector, section settings, and mode.
	 * 
	 * @return void
	 */

	public static function arrows_style_controls( $object, $arrows_btn_selector, $args = [] ) {
		$args = Utils::check_default( $args, [
			'prefix'	=> 'slider_arrows_',
			'selector'	=> $arrows_btn_selector,
			
			'section'	=> [
				'name'		=> 'slider_arrows',
				'label'		=> esc_html__( 'Slider arrows style', 'mj-whitebox' ),
				'condition'	=> [
					'show_arrows'	=> 'yes'
				],
			],

			'mode'	=> 'icon',
		] );

		parent::general_style_controls( $object, $args );
	}

	/**
	 * Adds style controls for slider pagination dots in an Elementor widget.
	 *
	 * This function allows customization of arrow appearance, including color, size, and other styles.
	 *
	 * @param \Elementor\Widget_Base $object The Elementor widget instance to which the controls will be added.
	 * @param string $dot_selector Something like: .bijan-slider-nav-btn
	 * @param array $args Optional. Configuration array to customize prefix, selector, section settings, and mode.
	 * 
	 * @return void
	 */

	public static function dots_style_controls( $object, $dot_selector, $args = [] ) {
		$args = Utils::check_default( $args, [
			'prefix'	=> 'slider_dots_',
			'selector'	=> $dot_selector,
			
			'section'	=> [
				'name'		=> 'slider_dots',
				'label'		=> esc_html__( 'Slider dots wrap style', 'mj-whitebox' ),
				'condition'	=> [
					'show_dots'	=> 'yes'
				],
			],

			'mode'	=> 'wrap',
		] );

		parent::general_style_controls( $object, $args );
	}
}