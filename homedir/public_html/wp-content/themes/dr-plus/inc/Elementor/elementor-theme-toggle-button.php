<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
use DrPlus\Utils\Elementor;

class ThemeToggleButton extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_theme_toggle_button';
	}

	public function get_title() {
		return esc_html__( 'Theme toggle Button (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-button';
	}

	public function get_categories() {
		return ['drplus'];
	}

	public function get_keywords() {
		return ['button', 'theme', 'dark', 'light', 'style', 'دکمه', 'باتن', 'تم', 'دارک', 'لایت', ];
	}

	public function general_settings() {
		$this->start_controls_section( // general_settings_section
			'general_settings_section',
			[
				'label'	=> esc_html__( 'General', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // button_style
			"button_style",
			[
				'type'			=> \Elementor\Controls_Manager::SELECT,
				'label'			=> esc_html__( 'Style', 'drplus' ),
				'options'		=> [
					'style-1'	=> sprintf( esc_html__( 'Style %d', 'drplus' ), 1 ),
					'style-2'	=> sprintf( esc_html__( 'Style %d', 'drplus' ), 2 ),
				],
				'default'		=> 'style-1',
			]
		);

		$this->end_controls_section();
	}

	private function style_controls( $is_dark = false ) {
		$condition = [];
		$prefix = "";
		$class_prefix = "";
		if( $is_dark ) {
			$condition = ElementorControls::dark_condition();
			$prefix = 'dark_';
			$class_prefix = 'html[data-theme="dark"] ';
		}
		$this->start_controls_section(
			$prefix . 'theme_toggle_button_style',
			[
				'label'		=> ElementorControls::maybe_dark_label( esc_html__( 'Style', 'drplus' ), $is_dark ),
				'tab'		=> \Elementor\Controls_Manager::TAB_STYLE,
				'condition'	=> $condition
			]
		);

		ElementorControls::color( $this, $prefix . 'theme_toggle_button_background', '{{WRAPPER}} .drplus_theme_toggle_button', [
			'selectors'		=> [
				$class_prefix . '{{WRAPPER}} .drplus_theme_toggle_button'	=> '--background: {{VALUE}};',
			],
			'label'			=> esc_html__( 'Background', 'drplus' )
		] );
		ElementorControls::color( $this, $prefix . 'theme_toggle_button_color', '{{WRAPPER}} .drplus_theme_toggle_button', [
			'selectors'		=> [
				$class_prefix . '{{WRAPPER}} .drplus_theme_toggle_button'	=> '--icon_color: {{VALUE}};',
			],
		] );
		ElementorControls::border( $this, $prefix . 'theme_toggle_button_border', $class_prefix . '{{WRAPPER}} .drplus_theme_toggle_button' );

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->general_settings();
		$this->style_controls();
		ElementorControls::dark_mode_toggle_controls( $this );
		$this->style_controls( true );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		get_template_part( "templates/components/template-components-theme-toggle-button", null, $settings );
	}
}
