<?php
namespace DrPlus\Elementor;

use DrPlus\Utils;
use DrPlus\Utils\Elementor;
use DrPlus\ElementorControls;
use DrPlus\Utils\UtilsSpecialists;

class SpecialistServices extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_specialist_services';
	}

	public function get_title() {
		return esc_html__( 'Specialist services (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-archive';
	}

	public function get_categories() {
		return ['drplus', 'drplus_single_specialist'];
	}

	public function get_keywords() {
		return ['specialist', 'service', 'متخصص', 'خدمات', 'خدمت'];
	}

	public function show_in_panel() {
		if ( !Elementor::is_allowed_context( ['single-specialist', 'loop-item'] ) ) {
			return false;
		}

		return parent::show_in_panel();
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Settings', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // section_tag
			'section_tag',
			[
				'label'		=> esc_html__( 'Section tag', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'h3',
				'options'	=> Utils::custom_tags(),
			]
		);

		$this->add_control( // section_title
			'section_title',
			[
				'label'			=> esc_html__( 'Title', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( 'Specialized services of %s', 'drplus' ),
				'description'	=> esc_html__( 'Use %s to for specialist name', 'drplus' ),
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control(
			'section_icon',
			[
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Title Icon', 'drplus' ),
				'skin'			=> 'inline',
				'default'		=> [
					'value'		=> 'drplus-icon-mental-health',
					'library'	=> 'drplus-icon'
				],
				'label_block'	=> false,
			],
		);

		$this->add_control(
			'service_icon',
			[
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Title Icon', 'drplus' ),
				'skin'			=> 'inline',
				'default'		=> [
					'value'		=> 'drplus-icon-diamond',
					'library'	=> 'drplus-icon'
				],
				'label_block'	=> false,
			],
		);

		$this->add_control(
			'show_more_btn',
			[
				'label'			=> esc_html__( 'Show "show more button"', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->add_control(
			'show_more_btn_min_height',
			[
				'label'			=> esc_html__( 'Min height to show button (px)', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SLIDER,
				'size_units'	=> ['px'],
				'default'		=> [
					'size'	=> '300'
				],
				'range' => [
					'px' => [
						'max' => 1000,
					],
				],
				'condition'	=> [
					'show_more_btn'	=> 'yes'
				]
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		ElementorControls::grid_display_settings( $this );
		$this->settings_controls();
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'container_',
			'selector'		=> '.specialist_services',
			'section'	=> [
				'label'	=> esc_html__( 'Container', 'drplus'),
				'name'	=> 'container',
			],
			'mode'		=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'section_title_row_',
			'base_selector'		=> '.specialist_services > .section-title-wrap',
			'section'	=> [
				'label'	=> esc_html__( 'Section title row style', 'drplus'),
				'name'	=> 'section_title_row',
			],
			'mode'		=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'section_title_',
			'base_selector'		=> '.specialist_services > .section-title-wrap',
			'selector'			=> '.section-title-title',
			'section'	=> [
				'label'	=> esc_html__( 'Section title style', 'drplus'),
				'name'	=> 'section_title',
			],
			'mode'		=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'section_title_icon_',
			'base_selector'		=> '.specialist_services > .section-title-wrap',
			'selector'			=> '.section-title-icon',
			'section'	=> [
				'label'	=> esc_html__( 'Section title icon style', 'drplus'),
				'name'	=> 'section_title_icon',
			],
			'mode'		=> 'icon',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'service_item_',
			'base_selector'		=> '.specialist_service',
			'section'	=> [
				'label'	=> esc_html__( 'service item', 'drplus'),
				'name'	=> 'service_item',
			],
			'mode'		=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'service_item_title_',
			'base_selector'		=> '.specialist_service',
			'selector'			=> '.section-title-title',
			'section'	=> [
				'label'	=> esc_html__( 'service title', 'drplus'),
				'name'	=> 'service_item_title',
			],
			'mode'		=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'service_item_icon_',
			'base_selector'		=> '.specialist_service',
			'selector'			=> '.section-title-icon',
			'section'	=> [
				'label'	=> esc_html__( 'service icon', 'drplus'),
				'name'	=> 'service_item_icon',
			],
			'mode'		=> 'icon',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'service_item_desc_',
			'base_selector'		=> '.specialist_service',
			'selector'			=> '.specialist_service-desc',
			'section'	=> [
				'label'	=> esc_html__( 'service description', 'drplus'),
				'name'	=> 'service_item_desc',
			],
			'mode'		=> 'text',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_container_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_services',
			'section'	=> [
				'label'		=>ElementorControls::dark_control_label(  esc_html__( 'Container', 'drplus') ),
				'name'		=> 'dark_container',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_section_title_row_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_services > .section-title-wrap',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Section title row style', 'drplus') ),
				'name'	=> 'dark_section_title_row',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_section_title_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_services > .section-title-wrap',
			'selector'			=> '.section-title-title',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Section title style', 'drplus') ),
				'name'	=> 'dark_section_title',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_section_title_icon_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_services > .section-title-wrap',
			'selector'			=> '.section-title-icon',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Section title icon style', 'drplus') ),
				'name'	=> 'dark_section_title_icon',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'icon',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_service_item_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_service',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'service item', 'drplus') ),
				'name'	=> 'dark_service_item',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_service_item_title_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_service',
			'selector'			=> '.section-title-title',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'service title', 'drplus') ),
				'name'	=> 'dark_service_item_title',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_service_item_icon_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_service',
			'selector'			=> '.section-title-icon',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'service icon', 'drplus') ),
				'name'	=> 'dark_service_item_icon',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'icon',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_service_item_desc_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_service',
			'selector'			=> '.specialist_service-desc',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'service description', 'drplus') ),
				'name'	=> 'dark_service_item_desc',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		$specialist = UtilsSpecialists::get_specialist_from_post();
		if( empty( $specialist ) || empty( $specialist->meta['services'] ) ) return '';

		get_template_part( 'templates/specialists/single/template-specialists-single-services', null, [
			'prefix'				=> 'specialist_',
			'specialist'			=> $specialist,
			'options'				=> [
				'single_specialist_sections_tag'	=> $settings['section_tag'],
				'section_title'						=> $settings['section_title'],
				'section_icon'						=> $settings['section_icon'],
				'service_icon'						=> $settings['service_icon'],
				'show_more_btn'						=> $settings['show_more_btn'],
				'show_more_btn_min_height'			=> $settings['show_more_btn_min_height']['size'] ?? 300
			],
			'display_settings'		=> $settings,
		] );
	}
}