<?php
namespace DrPlus\Elementor;

use DrPlus\Utils;
use DrPlus\Utils\Elementor;
use DrPlus\ElementorControls;
use DrPlus\Utils\SpecialistInsurancesRel;
use DrPlus\Utils\UtilsSpecialists;

class SpecialistInsurances extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_specialist_insurances';
	}

	public function get_title() {
		return esc_html__( 'Specialist insurances (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-post-excerpt';
	}

	public function get_categories() {
		return ['drplus', 'drplus_single_specialist'];
	}

	public function get_keywords() {
		return ['specialist', 'insurances', 'متخصص', 'بیمه'];
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
				'default'		=> esc_html__( 'Covered insurances', 'drplus' ),
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

		$this->end_controls_section();
	}

	protected function register_controls() {
		ElementorControls::grid_display_settings( $this );
		$this->settings_controls();
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'container_',
			'selector'		=> '.specialist_insurances',
			'section'	=> [
				'label'	=> esc_html__( 'Container', 'drplus'),
				'name'	=> 'container',
			],
			'mode'		=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'section_title_row_',
			'selector'		=> '.specialist_insurances > .section-title-wrap',
			'section'	=> [
				'label'	=> esc_html__( 'Section title row style', 'drplus'),
				'name'	=> 'section_title_row',
			],
			'mode'		=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'section_title_',
			'base_selector'		=> '.specialist_insurances > .section-title-wrap',
			'selector'			=> '.section-title-title',
			'section'	=> [
				'label'	=> esc_html__( 'Section title style', 'drplus'),
				'name'	=> 'section_title',
			],
			'mode'		=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'section_title_icon_',
			'base_selector'		=> '.specialist_insurances > .section-title-wrap',
			'selector'			=> '.section-title-icon',
			'section'	=> [
				'label'	=> esc_html__( 'Section title icon style', 'drplus'),
				'name'	=> 'section_title_icon',
			],
			'mode'		=> 'icon',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'insurance_item_',
			'base_selector'		=> '.specialist_insurance',
			'section'	=> [
				'label'	=> esc_html__( 'Insurance item', 'drplus'),
				'name'	=> 'insurance_item',
			],
			'mode'		=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'insurance_item_title_',
			'base_selector'		=> '.specialist_insurance',
			'selector'			=> '.specialist_insurance-name',
			'section'	=> [
				'label'	=> esc_html__( 'Insurance title', 'drplus'),
				'name'	=> 'insurance_item_title',
			],
			'mode'		=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'insurance_item_icon_',
			'base_selector'		=> '.specialist_insurance',
			'selector'			=> '.specialist_insurance-icon',
			'section'	=> [
				'label'	=> esc_html__( 'Insurance icon', 'drplus'),
				'name'	=> 'insurance_item_icon',
			],
			'mode'		=> 'icon',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_container_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_insurance',
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
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_insurance > .section-title-wrap',
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
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_insurance > .section-title-wrap',
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
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_insurance > .section-title-wrap',
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
			'prefix'			=> 'dark_insurance_item_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_insurance',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Insurance item', 'drplus') ),
				'name'	=> 'dark_insurance_item',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_insurance_item_title_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_insurance',
			'selector'			=> '.specialist_insurance-name',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Insurance title', 'drplus') ),
				'name'	=> 'dark_insurance_item_title',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_insurance_item_icon_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_insurance',
			'selector'			=> '.specialist_insurance-icon',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Insurance icon', 'drplus') ),
				'name'	=> 'dark_insurance_item_icon',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'icon',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		$specialist = UtilsSpecialists::get_specialist_from_post();
		if( empty( $specialist ) || $specialist->insurances->isEmpty() ) {
			return '';
		}

		$specialist_insurances = SpecialistInsurancesRel::get_user_insurances( $specialist->user_id );
		$insurances = [];
		foreach( $specialist_insurances as $insurance ) {
			$insurance_term = get_term( $insurance->insurance_id, 'insurance' );
			if( empty( $insurance_term ) ) continue;
			$insurances[] = [
				'name' => $insurance_term->name,
				'icon'	=> get_term_meta( $insurance->insurance_id, 'icon', true ),
			];
		}

		if( empty( $insurances ) ) {
			return '';
		}

		get_template_part( 'templates/specialists/single/template-specialists-single-insurances', null, [
			'prefix'				=> 'specialist_',
			'specialist'			=> $specialist,
			'insurances'			=> $insurances,
			'options'				=> [
				'single_specialist_sections_tag'	=> $settings['section_tag'],
				'section_title'						=> $settings['section_title'],
				'section_icon'						=> $settings['section_icon'],
			],
			'display_settings'		=> $settings,
		] );
	}
}