<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
use DrPlus\Utils;

class Services extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_services';
	}

	public function get_title() {
		return esc_html__( 'Services (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-form-vertical';
	}

	public function get_categories() {
		return ['drplus', 'basic'];
	}

	public function get_keywords() {
		return ['service', 'icon', 'button', 'link', 'سرویس', 'خدمات', 'دکمه', 'آیکون', 'لینک'];
	}

	private function items_controls() {
		$this->start_controls_section( // content_section
			'items_section',
			[
				'label'	=> esc_html__( 'Items', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control( // icon
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

		$repeater->add_control( // title
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

		$repeater->add_control( // subtitle
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

		$repeater->add_control( // link
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

		$this->add_control( // items
			'items',
			[
				'label'		=> esc_html__( 'Items', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::REPEATER,
				'fields'	=> $repeater->get_controls(),
				'default'	=> [
					[
						'icon'		=> [
							'value'		=> 'drplus-icon-calling',
							'library'	=> 'drplus-icon',
						],
						'title'		=> esc_html__( 'Lorem ipsum dollar', 'drplus' ),
						'subtitle'	=> esc_html__( 'Lorem ipsum', 'drplus' ),
					],
				],
				'title_field' => '{{{ title }}}',
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
				'default'	=> 'div',
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

	protected function register_controls() {
		$this->items_controls();
		$this->seo_controls();
		ElementorControls::display_settings( $this, [
			'controls'	=> [
				'desktop_cols'	=> [
					'default'	=> 4
				],
				'desktop_gap'	=> [
					'default'	=> 80,
				],
				'tablet_slider'	=> [
					'default'	=> 'yes',
				],
				'tablet_slides'	=> [
					'default'	=> 2,
				],
				'tablet_slides_space'	=> [
					'default'	=> 24,
				],
				'mobile_slider'	=> [
					'default'	=> 'yes',
				],
				'mobile_slides'	=> [
					'default'	=> 1,
				],
				'mobile_slides_space'	=> [
					'default'	=> 24,
				],
			],
		] );

		ElementorControls::general_style_controls( $this, [ // item
			'prefix'		=> 'item_',
			'selector'		=> '.drplus-service',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'item_section',
				'label'	=> esc_html__( 'Service item', 'drplus' ),
			],

			'mode'	=> 'wrapper',
		] );

		ElementorControls::general_style_controls( $this, [ // item_icon
			'prefix'		=> 'item_icon_',
			'base_selector'	=> '.drplus-service',
			'selector'		=> '.drplus-service-icon',
			
			'section'	=> [
				'name'	=> 'item_icon_section',
				'label'	=> esc_html__( 'Service item icon', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );

		ElementorControls::text_style_controls(
			$this,
			'.drplus-service-title',
			'service_title_',
			esc_html__( 'Service item title', 'drplus' ),
			'{{WRAPPER}} .drplus-service:hover .drplus-service-title'
		);

		ElementorControls::text_style_controls(
			$this,
			'.drplus-service-subtitle',
			'service_subtitle_',
			esc_html__( 'Service item subtitle', 'drplus' ),
			'{{WRAPPER}} .drplus-service:hover .drplus-service-subtitle'
		);

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [ // item
			'prefix' 	=> 'dark_item_',
			'selector' 	=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-service',
			
			'section' 	=> [
				'name' 	=> 'dark_item_section',
				'label' 	=> ElementorControls::dark_control_label( esc_html__( 'Service item', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,
			'mode' 	=> 'wrapper',
		] );

		ElementorControls::general_style_controls( $this, [ // item_icon
			'prefix' 	=> 'dark_item_icon_',
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .drplus-service',
			'selector' 	=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-service-icon',
			
			'section' 	=> [
				'name' 	=> 'dark_item_icon_section',
				'label' 	=> ElementorControls::dark_control_label( esc_html__( 'Service item icon', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,
			'mode' 	=> 'icon',
		] );

		ElementorControls::general_style_controls( $this, [ // service_title_
			'prefix' 			=> 'dark_service_title_',
			'selector' 			=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-service-title',
			'hover_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-service:hover .drplus-service-title',
			
			'section' 	=> [
				'name' 			=> 'dark_service_title_section',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Service item title', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,
			'mode' 		=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [ // service_subtitle_
			'prefix' 			=> 'dark_service_subtitle_',
			'selector' 			=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-service-subtitle',
			'hover_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-service:hover .drplus-service-subtitle',
			
			'section' 	=> [
				'name' 			=> 'dark_service_subtitle_section',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Service item subtitle', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,
			'mode' 		=> 'text',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		get_template_part( "templates/components/template-components-services", null, $settings );
	}
}