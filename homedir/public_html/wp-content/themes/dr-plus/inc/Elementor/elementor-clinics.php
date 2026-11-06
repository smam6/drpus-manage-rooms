<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
use DrPlus\Utils;

class Clinics extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_clinics';
	}

	public function get_title() {
		return esc_html__( 'Clinics (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	public function get_categories() {
		return ['drplus', 'basic'];
	}

	public function get_keywords() {
		return ['partner', 'clinic', 'doctor', 'دکتر', 'داروخانه', 'کلینیک', 'همکار'];
	}

	private function items_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Items', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control( // icon_type
			'icon_type',
			[
				'label'		=> esc_html__( 'Icon type', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::CHOOSE,
				'options'	=> [
					'image'	=> [
						'title'	=> esc_html__( 'Image', 'drplus' ),
						'icon'	=> 'eicon-image',
					],
					'icon'	=> [
						'title'	=> esc_html__( 'Icon', 'drplus' ),
						'icon'	=> 'eicon-posts-ticker',
					],
				],
				'default'	=> 'icon',
				'toggle'	=> false,
			]
		);

		$repeater->add_control( // img
			'img',
			[
				'label'			=> esc_html__( 'Image', 'drplus' ),
				'description'	=> esc_html__( 'Size: 40px*40px', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::MEDIA,
				'default'		=> [
					'url'	=> \Elementor\Utils::get_placeholder_image_src(),
				],
				'condition'		=> [
					'icon_type'	=> 'image'
				],
			]
		);

		$repeater->add_control( // icon
			'icon',
			[
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Icon', 'drplus' ),
				'skin'			=> 'inline',
				'label_block'	=> false,
				'default'	=> [
					'value'		=> 'drplus-icon-dr-plus-6',
					'library'	=> 'drplus-icon',
				],
				'condition'		=> [
					'icon_type'	=> 'icon'
				],
			]
		);

		$repeater->add_control( // title
			'title',
			[
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label'			=> esc_html__( 'Title', 'drplus' ),
				'label_block'	=> true,
				'default'		=> __( 'Title', 'drplus' ),
				'description'	=> esc_html__( 'HTML tags allowed', 'drplus' ),
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
				'label'		=> esc_html__( 'Link', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::URL,
				'default'	=> [
					'url'	=> '#'
				],
				'dynamic'	=> [
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
						'icon_type'	=> 'icon',
						'icon'		=> [
							'value'		=> 'drplus-icon-dr-plus-6',
							'library'	=> 'drplus-icon',
						],
						'title'		=> esc_html__( 'Lorem ipsum dollar', 'drplus' ),
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
				'label'	=> esc_html__( 'SEO settings', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // title_tag
			'title_tag',
			[
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'label'		=> esc_html__( 'Title item tag', 'drplus' ),
				'default'	=> 'div',
				'options'	=> Utils::custom_tags()
			]
		);

		$this->end_controls_section();
	}

	private function display_settings() {
		$this->start_controls_section( // content_section
			'display_section',
			[
				'label'	=> esc_html__( 'Display settings', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // desktop_cols
			'desktop_cols',
			[
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'label'		=> esc_html__( 'Desktop columns', 'drplus' ),
				'default'	=> 6,
				'min'		=> 1,
			]
		);

		$this->add_control( // tablet_cols
			'tablet_cols',
			[
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'label'		=> esc_html__( 'Tablet columns', 'drplus' ),
				'default'	=> 4,
				'min'		=> 1,
			]
		);

		$this->add_control( // mobile_cols
			'mobile_cols',
			[
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'label'		=> esc_html__( 'Mobile columns', 'drplus' ),
				'default'	=> 3,
				'min'		=> 1,
			]
		);

		$this->end_controls_section();
	}

	private function wrap_style_controls() {
		$this->start_controls_section( // content_section
			'general_section',
			[
				'label'	=> esc_html__( 'General styles', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control( // wrap_bg
			'wrap_bg',
			[
				'type'		=> \Elementor\Controls_Manager::COLOR,
				'label'		=> esc_html__( 'Background color', 'drplus' ),
				'selectors'	=> [
					'{{WRAPPER}} .clinics' => '--bg-color: {{VALUE}}',
				],
			]
		);

		$this->add_control( // item_border_color
			'item_border_color',
			[
				'type'		=> \Elementor\Controls_Manager::COLOR,
				'label'		=> esc_html__( 'Item border color', 'drplus' ),
				'selectors'	=> [
					'{{WRAPPER}} .clinics' => '--border-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();
	}
	
	private function wrap_dark_style_controls() {
		$this->start_controls_section( // dark_general_section
			'dark_general_section',
			[
				'label'	=> esc_html__( 'General styles', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_STYLE,
				'condition'	=> [
					'enable_dark_mode'	=> 'yes',
				],
			]
		);

		$this->add_control( // dark_wrap_bg
			'dark_wrap_bg',
			[
				'type'		=> \Elementor\Controls_Manager::COLOR,
				'label'		=> esc_html__( 'Background color', 'drplus' ),
				'selectors'	=> [
					'html[data-theme="dark"] {{WRAPPER}} .clinics' => '--bg-color: {{VALUE}}',
				],
			]
		);

		$this->add_control( // dark_item_border_color
			'dark_item_border_color',
			[
				'type'		=> \Elementor\Controls_Manager::COLOR,
				'label'		=> esc_html__( 'Item border color', 'drplus' ),
				'selectors'	=> [
					'html[data-theme="dark"] {{WRAPPER}} .clinics' => '--border-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->items_controls();
		$this->seo_controls();
		$this->display_settings();

		$this->wrap_style_controls();
		ElementorControls::general_style_controls( $this, [ // item_
			'prefix'		=> 'item_',
			'base_selector'	=> '.clinic',
			
			'section'	=> [
				'name'	=> 'item_section',
				'label'	=> esc_html__( 'Item style', 'drplus' ),
			],

			'excludes'	=> ['margin', 'border', 'border_radius'],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // item_icon_
			'prefix'		=> 'item_icon_',
			'base_selector'	=> '.clinic',
			'selector'		=> '.clinic-icon',
			
			'section'	=> [
				'name'	=> 'item_icon_section',
				'label'	=> esc_html__( 'Item icon style', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // item_image_
			'prefix'		=> 'item_image_',
			'base_selector'	=> '.clinic',
			'selector'		=> 'img.clinic-icon',
			
			'section'	=> [
				'name'	=> 'item_image_section',
				'label'	=> esc_html__( 'Item image style', 'drplus' ),
			],

			'mode'	=> 'image',
		] );
		ElementorControls::general_style_controls( $this, [ // item_title_
			'prefix'		=> 'item_title_',
			'base_selector'	=> '.clinic',
			'selector'		=> '.clinic-title',
			
			'section'	=> [
				'name'	=> 'item_title_section',
				'label'	=> esc_html__( 'Item title style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // item_popover_
			'prefix'		=> 'item_popover_',
			'base_selector'	=> '.clinic',
			'selector'		=> '.clinic-popover',
			
			'section'	=> [
				'name'	=> 'item_popover_section',
				'label'	=> esc_html__( 'Item popover style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // item_separator_
			'prefix'		=> 'item_separator_',
			'base_selector'	=> '.clinic',
			'selector'		=> '.clinic-separator',
			
			'section'	=> [
				'name'	=> 'item_separator_section',
				'label'	=> esc_html__( 'Item separator style', 'drplus' ),
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
						"{{WRAPPER}} .clinic-separator::before"	=> 'background-color: {{VALUE}};',
					],
				],
				'custom_border_radius'	=> [
					'_responsive'	=> true,
					'type'			=> \Elementor\Controls_Manager::DIMENSIONS,
					'label'			=> esc_html__( 'Border Radius', 'drplus' ),
					'size_units'	=> [ 'px', '%', 'em', 'rem', 'custom' ],
					'selectors'	=> [
						"{{WRAPPER}} .clinic-separator::before"	=> 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				],
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // item_separator_secondary_
			'prefix'		=> 'item_separator_secondary_',
			'base_selector'	=> '.clinic',
			'selector'		=> '.clinic-separator-white',
			
			'section'	=> [
				'name'	=> 'item_separator_secondary_section',
				'label'	=> esc_html__( 'Item separator(secondary) style', 'drplus' ),
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
						"{{WRAPPER}} .clinic-separator-white::before"	=> 'background-color: {{VALUE}};',
					],
				],
				'custom_border_radius'	=> [
					'_responsive'	=> true,
					'type'			=> \Elementor\Controls_Manager::DIMENSIONS,
					'label'			=> esc_html__( 'Border Radius', 'drplus' ),
					'size_units'	=> [ 'px', '%', 'em', 'rem', 'custom' ],
					'selectors'	=> [
						"{{WRAPPER}} .clinic-separator-white::before"	=> 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				],
			],

			'mode'	=> 'icon',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		
		$this->wrap_dark_style_controls();
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [ // dark_item_
			'prefix'		=> 'dark_item_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .clinic',
			
			'section'	=> [
				'name'	=> 'dark_item_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Item style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> array_merge( $dark_excludes, ['border'] ),

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_item_icon_
			'prefix'		=> 'dark_item_icon_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .clinic',
			'selector'		=> '.clinic-icon',
			
			'section'	=> [
				'name'	=> 'dark_item_icon_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Item icon style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_item_image_
			'prefix'		=> 'dark_item_image_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .clinic',
			'selector'		=> 'img.clinic-icon',
			
			'section'	=> [
				'name'	=> 'dark_item_image_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Item image style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'image',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_item_title_
			'prefix'		=> 'dark_item_title_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .clinic',
			'selector'		=> '.clinic-title',
			
			'section'	=> [
				'name'	=> 'dark_item_title_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Item title style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_item_popover_
			'prefix'		=> 'dark_item_popover_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .clinic',
			'selector'		=> '.clinic-popover',
			
			'section'	=> [
				'name'	=> 'dark_item_popover_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Item popover style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_item_separator_
			'prefix'		=> 'dark_item_separator_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .clinic',
			'selector'		=> '.clinic-separator',
			
			'section'	=> [
				'name'	=> 'dark_item_separator_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Item separator style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> array_merge( $dark_excludes, [
				'background',
				'border',
				'box_shadow',
			] ),
			'hover_excludes'	=> array_merge( $dark_excludes, [
				'background',
				'border',
				'box_shadow',
			] ),

			'controls'	=> [
				'dark_custom_background'	=> [
					'type'		=> \Elementor\Controls_Manager::COLOR,
					'label'		=> esc_html__( 'Background color', 'drplus' ),
					'selectors'	=> [
						'html[data-theme="dark"] {{WRAPPER}} .clinic-separator::before'	=> 'background-color: {{VALUE}};',
					],
				],
				'dark_custom_border_radius'	=> [
					'_responsive'	=> true,
					'type'			=> \Elementor\Controls_Manager::DIMENSIONS,
					'label'			=> esc_html__( 'Border Radius', 'drplus' ),
					'size_units'	=> [ 'px', '%', 'em', 'rem', 'custom' ],
					'selectors'	=> [
						'html[data-theme="dark"] {{WRAPPER}} .clinic-separator::before'	=> 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				],
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_item_separator_secondary_
			'prefix'		=> 'dark_item_separator_secondary_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .clinic',
			'selector'		=> '.clinic-separator-white',
			
			'section'	=> [
				'name'	=> 'dark_item_separator_secondary_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Item separator(secondary) style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> array_merge( $dark_excludes, [
				'background',
				'border',
				'box_shadow',
			] ),
			'hover_excludes'	=> array_merge( $dark_excludes, [
				'background',
				'border',
				'box_shadow',
			] ),

			'controls'	=> [
				'dark_custom_background'	=> [
					'type'		=> \Elementor\Controls_Manager::COLOR,
					'label'		=> esc_html__( 'Background color', 'drplus' ),
					'selectors'	=> [
						'html[data-theme="dark"] {{WRAPPER}} .clinic-separator-white::before'	=> 'background-color: {{VALUE}};',
					],
				],
				'dark_custom_border_radius'	=> [
					'_responsive'	=> true,
					'type'			=> \Elementor\Controls_Manager::DIMENSIONS,
					'label'			=> esc_html__( 'Border Radius', 'drplus' ),
					'size_units'	=> [ 'px', '%', 'em', 'rem', 'custom' ],
					'selectors'	=> [
						'html[data-theme="dark"] {{WRAPPER}} .clinic-separator-white::before'	=> 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				],
			],

			'mode'	=> 'icon',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		get_template_part( "templates/components/template-components-clinics", null, $settings );
	}
}