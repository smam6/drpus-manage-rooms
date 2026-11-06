<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
use DrPlus\Utils;
use DrPlus\Utils\Elementor;

class CTA2 extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_cta2';
	}

	public function get_title() {
		return esc_html__( 'Call to Action 2 (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-call-to-action';
	}

	public function get_categories() {
		return ['drplus', 'basic'];
	}

	public function get_keywords() {
		return ['text', 'call to action', 'cta', 'کال تو اکشن', 'اقدام به عمل'];
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Settings', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // title
			'title',
			[
				'label'			=> esc_html__( 'Title', 'drplus' ),
				'description'	=> esc_html__( 'HTML tags allowed', 'drplus' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( 'Lorem ipsum dollar', 'drplus' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // subtitle
			'subtitle',
			[
				'label'			=> esc_html__( 'Subtitle', 'drplus' ),
				'description'	=> esc_html__( 'HTML tags allowed', 'drplus' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> __( '<span>1,234</span>Lorem ipsum', 'drplus' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // image
			'image',
			[
				'label'			=> esc_html__( 'Choose Image', 'drplus' ),
				'description'	=> esc_html__( 'Preferred Size: 360px*190px', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::MEDIA,
				'separator'		=> 'before',
				'default'		=> [
					'url'		=> \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$this->end_controls_section();
	}

	private function seo_controls() {
		$this->start_controls_section( // content_section
			'seo_section',
			[
				'label'	=> esc_html__( 'SEO Settings', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // title_tag
			'title_tag',
			[
				'label'		=> esc_html__( 'Title tag', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'h4',
				'options'	=> Utils::custom_tags(),
			]
		);

		$this->add_control( // subtitle_tag
			'subtitle_tag',
			[
				'label'		=> esc_html__( 'Subtitle tag', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'div',
				'options'	=> Utils::custom_tags(),
			]
		);

		$this->end_controls_section();
	}

	private function general_style_controls() {
		$this->start_controls_section( // content_section
			'general_style_section',
			[
				'label'	=> esc_html__( 'General style', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		ElementorControls::margin( $this, 'margin', '{{WRAPPER}} .drplus-cta2' );
		ElementorControls::padding( $this, 'padding', '{{WRAPPER}} .drplus-cta2' );
		ElementorControls::background( $this, 'bg_background', '{{WRAPPER}} .drplus-cta2-bg' );
		ElementorControls::border( $this, 'bg_border', '{{WRAPPER}} .drplus-cta2-bg' );
		ElementorControls::border_radius( $this, 'bg_border_radius', '{{WRAPPER}} .drplus-cta2-bg' );

		$this->end_controls_section();
	}

	private function dark_general_style_controls() {
		$this->start_controls_section( // content_section
			'dark_general_style_section',
			[
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'General style', 'drplus' ) ),
				'tab'	=> \Elementor\Controls_Manager::TAB_STYLE,
				'condition'	=> [
					'enable_dark_mode'	=> 'yes',
				]
			]
		);

		ElementorControls::background( $this, 'dark_bg_background', 'html[data-theme="dark"] {{WRAPPER}} .drplus-cta2-bg' );
		ElementorControls::border( $this, 'dark_bg_border', 'html[data-theme="dark"] {{WRAPPER}} .drplus-cta2-bg' );

		$this->end_controls_section();
	}

	private function button_style_controls() {
		$selector = "{{WRAPPER}} .button";
		$hover_selector = "{$selector}:hover";

		$this->start_controls_section(
			'style_button_section',
			[
				'label'	=> esc_html__( 'Button style', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab( // Normal
			'tab_button_normal',
			[
				'label'	=> esc_html__( 'Normal', 'drplus' ),
			]
		);

		ElementorControls::margin( $this, 'button_margin', $selector );
		ElementorControls::padding( $this, 'button_padding', $selector );
		ElementorControls::typography( $this, 'button_typography', $selector );
		ElementorControls::color( $this, 'button_color', $selector );
		ElementorControls::background( $this, 'button_background', $selector );
		ElementorControls::border( $this, 'button_border', $selector );
		ElementorControls::border_radius( $this, 'button_border_radius', $selector );
		ElementorControls::box_shadow( $this, 'button_shadow', $selector );

		$this->end_controls_tab();

		$this->start_controls_tab( // Hover
			'tab_button_hover',
			[
				'label' => esc_html__( 'Hover', 'drplus' ),
			]
		);

		ElementorControls::margin( $this, 'button_hover_margin', $hover_selector );
		ElementorControls::padding( $this, 'button_hover_padding', $hover_selector );
		ElementorControls::typography( $this, 'button_hover_typography', $hover_selector );
		ElementorControls::color( $this, 'button_hover_color', $hover_selector );
		ElementorControls::background( $this, 'button_hover_background', $hover_selector );
		ElementorControls::border( $this, 'button_hover_border', $hover_selector );
		ElementorControls::border_radius( $this, 'button_hover_border_radius', $hover_selector );
		ElementorControls::box_shadow( $this, 'button_hover_shadow', $hover_selector );

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function button_text_style_controls( $label, $type ) {
		$selector = "{{WRAPPER}} .button-{$type}";
		$hover_selector = "{{WRAPPER}} .button:hover .button-{$type}";

		$this->start_controls_section(
			"style_button_{$type}_section",
			[
				'label'	=> sprintf( esc_html__( '%s style', 'drplus' ), $label ),
				'tab'	=> \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( "tabs_button_{$type}_style" );

		$this->start_controls_tab( // Normal
			"tab_button_{$type}_normal",
			[
				'label'	=> esc_html__( 'Normal', 'drplus' ),
			]
		);

		ElementorControls::margin( $this, "button_{$type}_margin", $selector );
		ElementorControls::padding( $this, "button_{$type}_padding", $selector );
		ElementorControls::typography( $this, "button_{$type}_typography", $selector );
		ElementorControls::color( $this, "button_{$type}_color", $selector );
		ElementorControls::background( $this, "button_{$type}_background", $selector );
		ElementorControls::border( $this, "button_{$type}_border", $selector );
		ElementorControls::border_radius( $this, "button_{$type}_border_radius", $selector );
		ElementorControls::box_shadow( $this, "button_{$type}_shadow", $selector );
		ElementorControls::text_shadow( $this, "button_{$type}_text_shadow", $selector );

		$this->end_controls_tab();

		$this->start_controls_tab( // Hover
			"tab_button_{$type}_hover",
			[
				'label' => esc_html__( 'Hover', 'drplus' ),
			]
		);

		ElementorControls::margin( $this, "button_{$type}_hover_margin", $hover_selector );
		ElementorControls::padding( $this, "button_{$type}_hover_padding", $hover_selector );
		ElementorControls::typography( $this, "button_{$type}_hover_typography", $hover_selector );
		ElementorControls::color( $this, "button_{$type}_hover_color", $hover_selector );
		ElementorControls::background( $this, "button_{$type}_hover_background", $hover_selector );
		ElementorControls::border( $this, "button_{$type}_hover_border", $hover_selector );
		ElementorControls::border_radius( $this, "button_{$type}_hover_border_radius", $hover_selector );
		ElementorControls::box_shadow( $this, "button_{$type}_hover_shadow", $hover_selector );
		ElementorControls::text_shadow( $this, "button_{$type}_hover_text_shadow", $hover_selector );

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();
		$this->seo_controls();
		ElementorControls::button_settings( $this, [
			'controls'	=> [
				'text'	=> [
					'default'	=> esc_html__( "Register a request", 'drplus' ),
				],
				'type'	=> [
					'default'	=> 'white',
				],
				'align'	=> [
					'default'	=> 'center',
				]
			],
		] );

		$this->general_style_controls();
		ElementorControls::text_style_controls( $this, '.drplus-cta2-title', 'title_', __( "Title", 'drplus' ), '{{WRAPPER}} .drplus-cta2' );
		ElementorControls::text_style_controls( $this, '.drplus-cta2-subtitle', 'subtitle_', __( "Subtitle", 'drplus' ), '{{WRAPPER}} .drplus-cta2' );
		ElementorControls::general_style_controls( $this, [ // image_
			'prefix'		=> 'image_',
			'base_selector'	=> '.drplus-cta2',
			'selector'		=> '.drplus-cta2-image',
			
			'section'	=> [
				'name'	=> 'image_section',
				'label'	=> esc_html__( 'Image Style', 'drplus' ),
			],

			'mode'	=> 'image',
		] );
		$this->button_style_controls();
		$this->button_text_style_controls( __( 'Button icon', 'drplus' ), 'icon' );
		$this->button_text_style_controls( __( 'Button text', 'drplus' ), 'text' );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();
		$this->dark_general_style_controls();
		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_title_',
			'selector'			=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-cta2-title',
			'hover_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-cta2',
			
			'section'	=> [
				'name'	=> 'dark_title',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Title', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_subtitle_',
			'selector'			=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-cta2-subtitle',
			'hover_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-cta2',
			
			'section'	=> [
				'name'	=> 'dark_subtitle',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Subtitle', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // image_
			'prefix'		=> 'dark_image_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-cta2',
			'selector'		=> '.drplus-cta2-image',
			
			'section'	=> [
				'name'	=> 'dark_image_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Image Style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'image',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_button_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}}',
			'selector'		=> '.button',
			
			'section'	=> [
				'name'	=> 'dark_button',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Button Style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_button_text_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}}',
			'selector'		=> '.button-text',
			
			'section'	=> [
				'name'	=> 'dark_button_text',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Button text Style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_button_icon_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}}',
			'selector'		=> '.button-icon',
			
			'section'	=> [
				'name'	=> 'dark_button_icon',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Button icon Style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'icon',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		get_template_part( "templates/components/template-components-cta2", null, $settings );
	}
}