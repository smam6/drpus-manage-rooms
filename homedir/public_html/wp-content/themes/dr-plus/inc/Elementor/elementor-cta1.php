<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
use DrPlus\Utils;

class CTA1 extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_cta1';
	}

	public function get_title() {
		return esc_html__( 'Call to Action 1 (Doctor Plus)', 'drplus' );
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

		$this->add_control( // icon
			'icon',
			[
				'label'		=> esc_html__( 'Icon', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::ICONS,
				'default'	=> [
					'value'		=> 'drplus-icon-calling',
					'library'	=> 'drplus-icon',
				],
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
				'default'		=> esc_html__( 'Lorem ipsum', 'drplus' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // link
			'link',
			[
				'label'			=> esc_html__( 'Link', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::URL,
				'label_block'	=> true,
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
				'description'	=> esc_html__( 'Preferred Size: 360px*200px', 'drplus' ),
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

	private function style_icon_stroke_controller( $selector, $prefix, $label, $is_dark = false ) {
		$section_args = [
			'label' => $label,
			'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
		];
		if( $is_dark ) {
			$section_arg['condition'] = [
				'enable_dark_mode'	=> 'yes',
			];
		}

		$this->start_controls_section(
			$prefix . 'section',
			$section_args
		);

		$this->add_control(
			$prefix . 'color',
			[
				'label' => esc_html__( 'Color', 'drplus' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ' . $selector => 'background-color: {{VALUE}}',
				],
			]
		);

		ElementorControls::border( $this, $prefix . 'border', $selector );

		ElementorControls::border_radius( $this, $prefix . 'border_radius', $selector );

		$this->add_control(
			$prefix . 'thickness',
			[
				'label' => esc_html__( 'Thickness', 'drplus' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 10,
					],
				],
				'selectors' => [
					'{{WRAPPER}} ' . $selector => 'inset: -{{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();
		$this->seo_controls();

		ElementorControls::general_style_controls( $this, [ // cta
			'prefix'		=> 'cta_',
			'selector'		=> '.drplus-cta1',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'cta_section',
				'label'	=> esc_html__( 'General Style', 'drplus' ),
			],

			'mode'	=> 'wrapper',
		] );

		ElementorControls::general_style_controls( $this, [ // cta_content_section
			'prefix'			=> 'cta_content_section_',
			'selector'			=> '.drplus-cta1-content-wrap',
			'hover_selector'	=> false,

			'section'	=> [
				'name'	=> 'cta_content_section',
				'label'	=> esc_html__( 'Content Section', 'drplus' ),
			],

			'excludes'	=> [
				'color',
				'typography',
				'icon_size',
				'text_shadow',
			],

			'mode'	=> 'wrapper',
		] );

		ElementorControls::general_style_controls( $this, [ // cta_img_section
			'prefix'			=> 'cta_img_section_',
			'selector'			=> '.drplus-cta1-image-wrap',
			'hover_selector'	=> false,
			
			'section'	=> [
				'name'	=> 'cta_img_section',
				'label'	=> esc_html__( 'Image Section', 'drplus' ),
			],

			'excludes'	=> [
				'color',
				'typography',
				'icon_size',
				'text_shadow',
				'background',
			],
			'controls'	=> [
				'img_background'	=> [
					'label'	=> esc_html__( 'Background', 'drplus' ),
					'type'	=> \Elementor\Controls_Manager::COLOR,
					'selectors'	=> [
						'{{WRAPPER}} .drplus-cta1-image-wrap' => 'background-color: {{VALUE}}',
						'{{WRAPPER}} .drplus-cta1-image-wrap::before' => '--img-bg-color: {{VALUE}}',
					],
				],
			],

			'mode'	=> 'wrapper',
		] );

		ElementorControls::general_style_controls( $this, [ // cta_icon
			'prefix'		=> 'cta_icon_',
			'selector'		=> '.drplus-cta1-btn',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'cta_icon_section',
				'label'	=> esc_html__( 'Icon', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );


		$this->style_icon_stroke_controller(  // cta_icon_stroke1
			'.drplus-cta1-btn::before',
			'cta_icon_stroke1_',
			sprintf( esc_html__( 'Icon Stroke %d', 'drplus' ), 1 )
		);

		$this->style_icon_stroke_controller(  // cta_icon_stroke2
			'.drplus-cta1-btn::after',
			'cta_icon_stroke2_',
			sprintf( esc_html__( 'Icon Stroke %d', 'drplus' ), 2 )
		);

		ElementorControls::general_style_controls( $this, [ // cta_img
			'prefix'		=> 'cta_img_',
			'selector'		=> '.drplus-cta1-img',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'cta_img',
				'label'	=> esc_html__( 'Image', 'drplus' ),
			],

			'mode'	=> 'img',
		] );

		ElementorControls::text_style_controls(
			$this,
			'.drplus-cta1-title',
			'cta_title_',
			esc_html__( 'Title', 'drplus' ),
			'{{WRAPPER}} .drplus-cta1-title'
		);

		ElementorControls::text_style_controls(
			$this,
			'.drplus-cta1-subtitle',
			'cta_subtitle_',
			esc_html__( 'Subtitle', 'drplus' ),
			'{{WRAPPER}} .drplus-cta1-subtitle'
		);

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [ // cta
			'prefix'		=> 'dark_cta_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-cta1',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_cta_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'General Style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'wrapper',
		] );

		ElementorControls::general_style_controls( $this, [ // cta_content_section
			'prefix'			=> 'dark_cta_content_section_',
			'selector'			=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-cta1-content-wrap',
			'hover_selector'	=> false,

			'section'	=> [
				'name'	=> 'dark_cta_content_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Content Section', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> [
				'color',
				'text_shadow',
			] + $dark_excludes,
			'hover_excludes'	=> [
				'color',
				'text_shadow',
			] + $dark_excludes,

			'mode'	=> 'wrapper',
		] );

		ElementorControls::general_style_controls( $this, [ // cta_img_section
			'prefix'			=> 'dark_cta_img_section_',
			'selector'			=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-cta1-image-wrap',
			'hover_selector'	=> false,
			
			'section'	=> [
				'name'	=> 'dark_cta_img_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Image Section', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> [
				'color',
				'text_shadow',
				'background',
			] + $dark_excludes,
			'hover_excludes'	=> [
				'color',
				'text_shadow',
				'background',
			] + $dark_excludes,
			'controls'	=> [
				'dark_img_background'	=> [
					'label'	=> esc_html__( 'Background', 'drplus' ),
					'type'	=> \Elementor\Controls_Manager::COLOR,
					'selectors'	=> [
						'html[data-theme="dark"] {{WRAPPER}} .drplus-cta1-image-wrap' => 'background-color: {{VALUE}}',
						'html[data-theme="dark"] {{WRAPPER}} .drplus-cta1-image-wrap::before' => '--img-bg-color: {{VALUE}}',
					],
				],
			],

			'mode'	=> 'wrapper',
		] );

		ElementorControls::general_style_controls( $this, [ // cta_icon
			'prefix'		=> 'dark_cta_icon_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-cta1-btn',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_cta_icon_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Icon', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'icon',
		] );


		$this->style_icon_stroke_controller(  // cta_icon_stroke1
			'html[data-theme="dark"] {{WRAPPER}} .drplus-cta1-btn::before',
			'dark_cta_icon_stroke1_',
			ElementorControls::dark_control_label( sprintf( esc_html__( 'Icon Stroke %d', 'drplus' ), 1 ) ),
			true
		);

		$this->style_icon_stroke_controller(  // cta_icon_stroke2
			'html[data-theme="dark"] {{WRAPPER}} .drplus-cta1-btn::after',
			'dark_cta_icon_stroke2_',
			ElementorControls::dark_control_label( sprintf( esc_html__( 'Icon Stroke %d', 'drplus' ), 2 ) ),
			true
		);

		ElementorControls::general_style_controls( $this, [ // cta_img
			'prefix'		=> 'dark_cta_img_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-cta1-img',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_cta_img',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Image', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'img',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_cta_title_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-cta1-title',
			
			'section'	=> [
				'name'	=> 'dark_cta_title',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Title', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_cta_subtitle_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-cta1-subtitle',
			
			'section'	=> [
				'name'	=> 'dark_cta_subtitle',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Subtitle', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'text',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		get_template_part( "templates/components/template-components-cta1", null, $settings );
	}
}