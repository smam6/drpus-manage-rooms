<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
use DrPlus\Utils;

class SimpleIcon extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_simple_icon';
	}

	public function get_title() {
		return esc_html__( 'Simple icon (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-favorite';
	}

	public function get_categories() {
		return ['drplus', 'basic'];
	}

	public function get_keywords() {
		return ['icon', 'image', 'آیکون', 'آیکن', 'تصویر', 'عکس'];
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Settings', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // icon
			'icon',
			[
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Icon', 'drplus' ),
				'skin'			=> 'inline',
				'label_block'	=> false,
				'default'	=> [
					'value'		=> 'drplus-icon-diamond',
					'library'	=> 'drplus-icon',
				],
			]
		);

		$this->add_control( // link
			'link',
			[
				'label'		=> esc_html__( 'Link', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::URL,
				'dynamic'	=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // has_bg
			'has_bg',
			[
				'label'			=> esc_html__( 'Show background', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();

		ElementorControls::general_style_controls( $this, [ // icon_wrap
			'prefix'		=> 'icon_wrap_',
			'base_selector'	=> '.drplus-simple-icon-wrap',
			
			'section'	=> [
				'name'	=> 'icon_wrap_section',
				'label'	=> esc_html__( 'Icon wrap style', 'drplus' ),
			],

			'excludes'	=> [
				'background',
				'border',
				'border_radius',
				'box_shadow',
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // icon
			'prefix'		=> 'icon_',
			'base_selector'	=> '.drplus-simple-icon-wrap',
			'selector'		=> '.drplus-simple-icon',
			
			'section'	=> [
				'name'	=> 'icon_section',
				'label'	=> esc_html__( 'Icon style', 'drplus' ),
			],

			'excludes'	=> [
				'margin',
				'padding',
				'background',
				'border',
				'border_radius',
				'box_shadow',
			],

			'controls'	=> [
				'custom_background'	=> [
					'type'		=> \Elementor\Controls_Manager::COLOR,
					'label'		=> esc_html__( 'Background color', 'drplus' ),
					'selectors'	=> [
						"{{WRAPPER}} .drplus-simple-icon-wrap::before"	=> 'background-color: {{VALUE}};',
					],
					'condition'	=> [
						'has_bg'	=> 'yes'
					],
				],
				'custom_border_radius'	=> [
					'_responsive'	=> true,
					'type'			=> \Elementor\Controls_Manager::DIMENSIONS,
					'label'			=> esc_html__( 'Border Radius', 'drplus' ),
					'size_units'	=> [ 'px', '%', 'em', 'rem', 'custom' ],
					'selectors'	=> [
						"{{WRAPPER}} .drplus-simple-icon-wrap::before"	=> 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'condition'	=> [
						'has_bg'	=> 'yes'
					],
				],
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [ // icon
			'prefix' 		=> 'dark_icon_',
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .drplus-simple-icon-wrap',
			'selector' 		=> '.drplus-simple-icon',
			
			'section' 	=> [
				'name' 			=> 'dark_icon_section',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Icon style', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'controls'	=> [
				'dark_custom_background'	=> [
					'type'		=> \Elementor\Controls_Manager::COLOR,
					'label'		=> esc_html__( 'Background color', 'drplus' ),
					'selectors'	=> [
						'html[data-theme="dark"] {{WRAPPER}} .drplus-simple-icon-wrap::before'	=> 'background-color: {{VALUE}};',
					],
					'condition'	=> [
						'has_bg'	=> 'yes'
					],
				],
				'dark_custom_border_radius'	=> [
					'_responsive'	=> true,
					'type'			=> \Elementor\Controls_Manager::DIMENSIONS,
					'label'			=> esc_html__( 'Border Radius', 'drplus' ),
					'size_units'	=> [ 'px', '%', 'em', 'rem', 'custom' ],
					'selectors'	=> [
						'html[data-theme="dark"] {{WRAPPER}} .drplus-simple-icon-wrap::before'	=> 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'condition'	=> [
						'has_bg'	=> 'yes'
					],
				],
			],

			'excludes' 	=> [
				'background',
				'border',
				'box_shadow',
			] + $dark_excludes,

			'hover_excludes' 	=> [
				'background',
				'border',
				'box_shadow',
			] + $dark_excludes,

			'mode' 	=> 'icon',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		get_template_part( "templates/components/template-components-simple_icon", null, $settings );
	}
}