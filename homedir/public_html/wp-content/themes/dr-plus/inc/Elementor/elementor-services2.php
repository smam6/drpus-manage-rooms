<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
use DrPlus\Utils;
use MJ\Whitebox\ElementorControls as WhiteboxElementorControls;
use MJ\Whitebox\ElementorControls\Slider;

class Services2 extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_services2';
	}

	public function get_title() {
		return esc_html__( 'Services2 (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-form-vertical';
	}

	public function get_categories() {
		return ['drplus', 'basic'];
	}

	public function get_keywords() {
		return ['services', 'slider', 'text', 'title', 'subtitle', 'خدمات', 'اسلایدر', 'عنوان', 'زیرعنوان'];
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

		$repeater->add_control( // item_type
			'item_type',
			[
				'label'			=> esc_html__( 'Item type', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SELECT,
				'options'		=> [
					'content'	=> esc_html__( 'Content', 'drplus' ),
					'divider'	=> esc_html__( 'Divider', 'drplus' ),
				],
				'default'		=> 'content'
			]
		);

		$repeater->add_control( // icon
			'icon',
			[
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Icon', 'drplus' ),
				'skin'			=> 'inline',
				'label_block'	=> false,
				'condition'		=> [
					'item_type'	=> 'content'
				]
			]
		);

		$repeater->add_control( // title
			'title',
			[
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label'			=> esc_html__( 'Title', 'drplus' ),
				'label_block'	=> true,
				'default'		=> esc_html__( "Lorem ipsum dollar", 'drplus' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'	=> [
					'active'	=> true,
				],
				'condition'		=> [
					'item_type'	=> 'content'
				]
			]
		);

		$repeater->add_control( // subtitle
			'subtitle',
			[
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label'			=> esc_html__( 'Subtitle', 'drplus' ),
				'default'		=> esc_html__( "Lorem ipsum dollar", 'drplus' ),
				'label_block'	=> true,
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'	=> [
					'active'	=> true,
				],
				'condition'		=> [
					'item_type'	=> 'content'
				]
			]
		);

		$repeater->add_control( // link
			'link',
			[
				'label'		=> esc_html__( 'Link', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::URL,
				'default'	=> [
					'url'	=> '#'
				],
				'dynamic'	=> [
					'active'	=> true,
				],
				'condition'		=> [
					'item_type'	=> 'content'
				]
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
						'number'	=> '20',
						'text'		=> esc_html__( 'Lorem ipsum dollar', 'drplus' ),
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

		WhiteboxElementorControls::display_settings( $this, [
			'controls'	=> [
				'desktop_slider'	=> [
					'default'	=> 'no',
				],
				'desktop_cols'	=> [
					'default'	=> 4
				],
				'desktop_column_gap'	=> [
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
		Slider::options_controls( $this, [
			'controls'	=> [
				'prev_arrow_icon' => [
					'_position'		=> 20,
					'label'			=> esc_html__( 'Prev arrow icon', 'drplus' ),
					'type'			=> \Elementor\Controls_Manager::ICONS,
					'skin'			=> 'inline',
					'label_block'	=> false,
					'default'		=> [
						'library'	=> 'drplus-icon',
						'value'		=> !is_rtl() ? 'drplus-icon-left' : 'drplus-icon-right'
					],
					'condition'		=> [
						'show_arrows'	=> 'yes'
					]
				],
				'next_arrow_icon' => [
					'_position'		=> 20,
					'label'			=> esc_html__( 'Next arrow icon', 'drplus' ),
					'type'			=> \Elementor\Controls_Manager::ICONS,
					'skin'			=> 'inline',
					'label_block'	=> false,
					'default'		=> [
						'library'	=> 'drplus-icon',
						'value'		=> is_rtl() ? 'drplus-icon-left' : 'drplus-icon-right'
					],
					'condition'		=> [
						'show_arrows'	=> 'yes'
					]
				]
			],
			'excludes'	=> ['show_dots']
		], true );

		ElementorControls::general_style_controls( $this, [ // item
			'prefix'			=> 'item_',
			'selector'			=> '.drplus-service2',
			'hover_selector'	=> '.drplus-service2:not(.slide-is-divider):hover',
			'hover_type'		=> 'normal',
			
			'section'	=> [
				'name'	=> 'item_section',
				'label'	=> esc_html__( 'Service item', 'drplus' ),
			],

			'mode'	=> 'wrapper',
		] );

		ElementorControls::general_style_controls( $this, [ // item_icon
			'prefix'		=> 'item_icon_',
			'base_selector'	=> '.drplus-service2',
			'selector'		=> '.drplus-service2-icon',
			
			'section'	=> [
				'name'	=> 'item_icon_section',
				'label'	=> esc_html__( 'Service item icon', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );

		ElementorControls::text_style_controls(
			$this,
			'.drplus-service2-title',
			'service_title_',
			esc_html__( 'Service item title', 'drplus' ),
			'{{WRAPPER}} .drplus-service2:hover .drplus-service2-title'
		);

		ElementorControls::text_style_controls(
			$this,
			'.drplus-service2-subtitle',
			'service_subtitle_',
			esc_html__( 'Service item subtitle', 'drplus' ),
			'{{WRAPPER}} .drplus-service2:hover .drplus-service2-subtitle'
		);

		ElementorControls::general_style_controls( $this, [ // slider_arrow
			'prefix' 	=> 'slider_arrow_',
			'selector' 	=> '{{WRAPPER}} .drplus-slider-nav-btn',
			
			'section' 	=> [
				'name' 	=> 'slider_arrow',
				'label' 	=> esc_html__( 'Slider arrows style', 'drplus' ),
			],

			'mode' 	=> 'icon',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [ // item
			'prefix' 	=> 'dark_item_',
			'selector' 	=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-service2',
			
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
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .drplus-service2',
			'selector' 	=> '.drplus-service2-icon',
			
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
			'selector' 			=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-service2-title',
			'hover_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-service2:hover .drplus-service2-title',
			
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
			'selector' 			=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-service2-subtitle',
			'hover_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-service2:hover .drplus-service2-subtitle',
			
			'section' 	=> [
				'name' 			=> 'dark_service_subtitle_section',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Service item subtitle', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,
			'mode' 		=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [ // slider_arrow
			'prefix' 	=> 'dark_slider_arrow_',
			'selector' 	=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-slider-nav-btn',
			
			'section' 	=> [
				'name' 			=> 'dark_slider_arrow',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Slider arrows style', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,
			'mode' 	=> 'icon',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		get_template_part( "templates/components/template-components-services2", null, $settings );
	}
}