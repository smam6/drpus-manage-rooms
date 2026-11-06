<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
use DrPlus\Utils;

class Accordion extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_accordion';
	}

	public function get_title() {
		return esc_html__( 'Accordion (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-accordion';
	}

	public function get_categories() {
		return ['drplus', 'basic'];
	}

	public function get_keywords() {
		return ['accordion', 'list', 'item', 'faq', 'لیست', 'آیتم', 'آکاردئون', 'سوال'];
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Items', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // item_style
			"item_style",
			[
				'type'			=> \Elementor\Controls_Manager::SELECT,
				'label'			=> esc_html__( 'Style', 'drplus' ),
				'options'		=> [
					'style-1'	=> esc_html__( 'Style 1', 'drplus' ),
					'style-2'	=> esc_html__( 'Style 2', 'drplus' ),
				],
				'default'		=> 'style-1',
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control( // title
			'title',
			[
				'label'			=> esc_html__( 'Title', 'drplus' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'description'	=> esc_html__( 'HTML tags allowed', 'drplus' ),
				'default'		=> __( 'Lorem ipsum dolor sit amet', 'drplus' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$repeater->add_control( // text
			'text',
			[
				'label'			=> esc_html__( 'Content', 'drplus' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::WYSIWYG,
				'description'	=> esc_html__( 'HTML tags allowed', 'drplus' ),
				'default'		=> __( 'Accordion Content', 'drplus' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$repeater->add_control( // show_bg_icon
			'show_bg_icon',
			[
				'label'			=> esc_html__( 'Show icon in background of content', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> false,
			]
		);

		$repeater->add_control( // bg_icon
			'bg_icon',
			[
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Background Icon', 'drplus' ),
				'skin'			=> 'inline',
				'label_block'	=> false,
				'default'		=> [
					'value'		=> 'drplus-icon-dr-plus-1',
					'library'	=> 'drplus-icon',
				],
				'condition'		=> [
					'show_bg_icon'	=> 'yes'
				],
			]
		);

		$this->add_control( // items
			'items',
			[
				'label'			=> __( "Items", 'drplus' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::REPEATER,
				'fields'		=> $repeater->get_controls(),
				'default'		=> [
					[
						'title'	=> esc_html__( 'Accordion #1', 'drplus' ),
						'text'	=> esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'drplus' ),
					],
					[
						'title'	=> esc_html__( 'Accordion #2', 'drplus' ),
						'text'	=> esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'drplus' ),
					],
				],
				'title_field' => '{{{ title }}}',
			]
		);

		$this->add_control( // open_icon
			'open_icon',
			[
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Open icon', 'drplus' ),
				'skin'			=> 'inline',
				'label_block'	=> false,
				'separator'		=> 'before',
				'default'		=> [
					'value'		=> 'drplus-icon-bottom',
					'library'	=> 'drplus-icon'
				],
			]
		);

		$this->add_control( // close_icon
			'close_icon',
			[
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Close icon', 'drplus' ),
				'skin'			=> 'inline',
				'label_block'	=> false,
				'default'		=> [
					'value'		=> 'drplus-icon-top',
					'library'	=> 'drplus-icon'
				],
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

		$this->add_control( // faq_schema
			'faq_schema',
			[
				'label'			=> esc_html__( 'FAQ Schema', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'description'	=> esc_html__( 'Enabled this if the accordion is a FAQ. This feature can improve SEO of the page.', 'drplus' ),
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->end_controls_section();
	}

	private function dark_mode_toggle_controls() {
		$this->start_controls_section(
			'accordion_dark_mode_toggle',
			[
				'label'	=> esc_html__( 'Dark mode', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'enable_dark_mode',
			[
				'label'			=> esc_html__( 'Customize dark mode styles', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
				'description'	=> esc_html__( 'Enable to set separate colors for dark mode.', 'drplus' ),
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();
		$this->seo_controls();
		
		ElementorControls::general_style_controls( $this, [ // item_
			'prefix'		=> 'item_',
			'base_selector'	=> '.accordion-item',
			
			'section'	=> [
				'name'	=> 'item_section',
				'label'	=> esc_html__( 'Item style', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // active_item_
			'prefix'		=> 'active_item_',
			'base_selector'	=> '.accordion-item-active',
			
			'section'	=> [
				'name'	=> 'active_item_section',
				'label'	=> esc_html__( 'Active item style', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // title_
			'prefix'		=> 'title_',
			'base_selector'	=> '.accordion-item',
			'selector'		=> '.accordion-item-title',
			
			'section'	=> [
				'name'	=> 'title_section',
				'label'	=> esc_html__( 'Title style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // active_title_
			'prefix'		=> 'active_title_',
			'base_selector'	=> '.accordion-item-active',
			'selector'		=> '.accordion-item-title',
			
			'section'	=> [
				'name'	=> 'active_title_section',
				'label'	=> esc_html__( 'Active title style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // open_icon_
			'prefix'		=> 'open_icon_',
			'base_selector'	=> '.accordion-item',
			'selector'		=> '.accordion-item-icon-open',
			
			'section'	=> [
				'name'	=> 'open_icon_section',
				'label'	=> esc_html__( 'Open icon style', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // close_icon_
			'prefix'		=> 'close_icon_',
			'base_selector'	=> '.accordion-item',
			'selector'		=> '.accordion-item-icon-close',
			
			'section'	=> [
				'name'	=> 'close_icon_section',
				'label'	=> esc_html__( 'Close icon style', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // content_
			'prefix'		=> 'content_',
			'base_selector'	=> '.accordion-item',
			'selector'		=> '.accordion-content',
			
			'section'	=> [
				'name'	=> 'content_section',
				'label'	=> esc_html__( 'Content style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // active_content_
			'prefix'		=> 'active_content_',
			'base_selector'	=> '.accordion-item-active',
			'selector'		=> '.accordion-content',
			
			'section'	=> [
				'name'	=> 'active_content_section',
				'label'	=> esc_html__( 'Active content style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );

		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [ // dark_item_
			'prefix'		=> 'dark_item_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .accordion-item',
			
			'section'	=> [
				'name'		=> 'dark_item_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Item style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_active_item_
			'prefix'		=> 'dark_active_item_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .accordion-item-active',
			
			'section'	=> [
				'name'		=> 'dark_active_item_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Active item style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_title_
			'prefix'		=> 'dark_title_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .accordion-item',
			'selector'		=> '.accordion-item-title',
			
			'section'	=> [
				'name'		=> 'dark_title_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Title style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_active_title_
			'prefix'		=> 'dark_active_title_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .accordion-item-active',
			'selector'		=> '.accordion-item-title',
			
			'section'	=> [
				'name'		=> 'dark_active_title_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Active title style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_open_icon_
			'prefix'		=> 'dark_open_icon_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .accordion-item',
			'selector'		=> '.accordion-item-icon-open',
			
			'section'	=> [
				'name'		=> 'dark_open_icon_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Open icon style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'icon',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_close_icon_
			'prefix'		=> 'dark_close_icon_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .accordion-item',
			'selector'		=> '.accordion-item-icon-close',
			
			'section'	=> [
				'name'		=> 'dark_close_icon_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Close icon style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'icon',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_content_
			'prefix'		=> 'dark_content_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .accordion-item',
			'selector'		=> '.accordion-content',
			
			'section'	=> [
				'name'		=> 'dark_content_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Content style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_active_content_
			'prefix'		=> 'dark_active_content_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .accordion-item-active',
			'selector'		=> '.accordion-content',
			
			'section'	=> [
				'name'		=> 'dark_active_content_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Active content style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		get_template_part( "templates/components/template-components-accordion", null, $settings );
	}
}
