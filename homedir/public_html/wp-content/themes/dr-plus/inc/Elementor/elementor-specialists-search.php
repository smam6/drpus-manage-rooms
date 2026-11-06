<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
use DrPlus\Utils\Elementor;

class SpecialistsSearch extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_specialists_search';
	}

	public function get_title() {
		return esc_html__( 'Specialists search (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-search-results';
	}

	public function get_categories() {
		return ['drplus', 'basic'];
	}

	public function get_keywords() {
		return ['counselor', 'doctor', 'advise', 'search', 'find', 'specialist', 'speciality', 'مشاور', 'دکتر', 'ویزیت', 'آنلاین', 'ثبت', 'پذیرش', 'جستجو', 'سرچ'];
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			'settings_controls_section',
			[
				'label'	=> esc_html__( 'Settings', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // search_icon
			'search_icon',
			[
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Icon', 'drplus' ),
				'skin'			=> 'inline',
				'label_block'	=> false,
				'default'	=> [
					'value'		=> 'drplus-icon-profile-tick',
					'library'	=> 'drplus-icon',
				],
			]
		);

		$this->add_control( // search_placeholder
			'search_placeholder',
			[
				'label'			=> esc_html__( 'Search placeholder', 'drplus' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> __( "Enter the doctor's name...", 'drplus' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // search_city
			'search_city',
			[
				'label'			=> esc_html__( 'Show select city', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
				'separator'		=> 'before',
			]
		);

		$this->add_control( // search_specialities
			'search_specialities',
			[
				'label'			=> esc_html__( 'Show specialities', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
				'separator'		=> 'before',
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();
		ElementorControls::button_settings( $this, [
			'controls'	=> [
				'text'	=> [
					'default'	=> esc_html__( 'Search Doctors', 'drplus' ),
				],
				'small'	=> [
					'default'	=> 'yes'
				],
				'icon_align'	=> [
					'default'	=> 'end',
				],
			],
			'excludes' => ['link'],
		] );

		ElementorControls::general_style_controls( $this, [ // wrap
			'prefix'	=> 'wrap_',
			'selector'	=> '.specialists-search',
			
			'section'	=> [
				'name'	=> 'wrap_section',
				'label'	=> esc_html__( 'Box', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // input_wrap_
			'prefix'		=> 'input_wrap_',
			'base_selector'	=> '.specialists-search',
			'selector'		=> '.specialists-search-input-wrap',
			
			'section'	=> [
				'name'	=> 'input_wrap_section',
				'label'	=> esc_html__( 'Search input wrap', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // input_icon_
			'prefix'		=> 'input_icon_',
			'base_selector'	=> '.specialists-search',
			'selector'		=> '.specialists-search-input-icon',
			
			'section'	=> [
				'name'	=> 'input_icon_section',
				'label'	=> esc_html__( 'Search input icon', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // select_city_wrap_
			'prefix'		=> 'select_city_wrap_',
			'base_selector'	=> '.specialists-search',
			'selector'		=> '.specialists-search-input-select-city',
			
			'section'	=> [
				'name'		=> 'select_city_wrap_section',
				'label'		=> esc_html__( 'Select city wrap', 'drplus' ),
				'condition'	=> [
					'search_city'	=> 'yes'
				]
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // select_city_icon_
			'prefix'		=> 'select_city_icon_',
			'base_selector'	=> '.specialists-search-input-wrap',
			'selector'		=> '.specialists-search-input-select-city-icon',
			
			'section'	=> [
				'name'		=> 'select_city_icon_section',
				'label'		=> esc_html__( 'Select city icon', 'drplus' ),
				'condition'	=> [
					'search_city'	=> 'yes'
				]
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // select_city_text_
			'prefix'		=> 'select_city_text_',
			'base_selector'	=> '.specialists-search-input-wrap',
			'selector'		=> '.specialists-search-input-select-city-icon',
			
			'section'	=> [
				'name'		=> 'select_city_text_section',
				'label'		=> esc_html__( 'Select city text', 'drplus' ),
				'condition'	=> [
					'search_city'	=> 'yes'
				]
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // select_city_arrow_
			'prefix'		=> 'select_city_arrow_',
			'base_selector'	=> '.specialists-search-input-wrap',
			'selector'		=> '.specialists-search-input-select-city-icon',
			
			'section'	=> [
				'name'		=> 'select_city_arrow_section',
				'label'		=> esc_html__( 'Select city arrow', 'drplus' ),
				'condition'	=> [
					'search_city'	=> 'yes'
				]
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // city_popup_wrap_
			'prefix'	=> 'city_popup_wrap_',
			'selector'	=> '.specialists-search-city-popup',
			
			'section'	=> [
				'name'		=> 'city_popup_wrap_section',
				'label'		=> esc_html__( 'City popup wrap', 'drplus' ),
				'condition'	=> [
					'search_city'	=> 'yes'
				]
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // city_popup_head_
			'prefix'		=> 'city_popup_head_',
			'base_selector'	=> '.specialists-search-city-popup',
			'selector'		=> '.specialists-search-city-popup-head',
			
			'section'	=> [
				'name'		=> 'city_popup_head_section',
				'label'		=> esc_html__( 'City popup head', 'drplus' ),
				'condition'	=> [
					'search_city'	=> 'yes'
				]
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // city_popup_title_
			'prefix'	=> 'city_popup_title_',
			'base_selector'	=> '.specialists-search-city-popup',
			'selector'		=> '.specialists-search-city-popup-title',
			
			'section'	=> [
				'name'		=> 'city_popup_title_section',
				'label'		=> esc_html__( 'City popup title', 'drplus' ),
				'condition'	=> [
					'search_city'	=> 'yes'
				]
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // city_popup_close_
			'prefix'	=> 'city_popup_close_',
			'base_selector'	=> '.specialists-search-city-popup',
			'selector'		=> '.specialists-search-city-popup-close',
			
			'section'	=> [
				'name'		=> 'city_popup_close_section',
				'label'		=> esc_html__( 'City popup close', 'drplus' ),
				'condition'	=> [
					'search_city'	=> 'yes'
				]
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // city_popup_search_wrap_
			'prefix'	=> 'city_popup_search_wrap_',
			'base_selector'	=> '.specialists-search-city-popup',
			'selector'		=> '.specialists-search-city-popup-search-wrap',
			
			'section'	=> [
				'name'		=> 'city_popup_search_wrap_section',
				'label'		=> esc_html__( 'City popup search wrap', 'drplus' ),
				'condition'	=> [
					'search_city'	=> 'yes'
				]
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // city_popup_search_field_
			'prefix'	=> 'city_popup_search_field_',
			'base_selector'	=> '.specialists-search-city-popup',
			'selector'		=> '.specialists-search-city-popup-field-wrap',
			
			'section'	=> [
				'name'		=> 'city_popup_search_field_section',
				'label'		=> esc_html__( 'City popup search input wrap', 'drplus' ),
				'condition'	=> [
					'search_city'	=> 'yes'
				]
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // city_popup_search_input_icon_
			'prefix'	=> 'city_popup_search_input_icon_',
			'base_selector'	=> '.specialists-search-city-popup',
			'selector'		=> '.specialists-search-city-popup-search-icon',
			
			'section'	=> [
				'name'		=> 'city_popup_search_input_icon_section',
				'label'		=> esc_html__( 'City popup search input icon', 'drplus' ),
				'condition'	=> [
					'search_city'	=> 'yes'
				]
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // city_popup_search_input_
			'prefix'	=> 'city_popup_search_input_',
			'base_selector'	=> '.specialists-search-city-popup',
			'selector'		=> '.specialists-search-city-popup-search',
			
			'section'	=> [
				'name'		=> 'city_popup_search_input_section',
				'label'		=> esc_html__( 'City popup search input', 'drplus' ),
				'condition'	=> [
					'search_city'	=> 'yes'
				]
			],

			'mode'	=> 'input',
		] );
		ElementorControls::general_style_controls( $this, [ // city_popup_all_cities_
			'prefix'	=> 'city_popup_all_cities_',
			'base_selector'	=> '.specialists-search-city-popup',
			'selector'		=> '.specialists-search-city-popup-all-cities',
			
			'section'	=> [
				'name'		=> 'city_popup_all_cities_section',
				'label'		=> esc_html__( 'City popup all cities button', 'drplus' ),
				'condition'	=> [
					'search_city'	=> 'yes'
				]
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // city_popup_results_
			'prefix'	=> 'city_popup_results_',
			'base_selector'	=> '.specialists-search-city-popup',
			'selector'		=> '.specialists-search-city-popup-results',
			
			'section'	=> [
				'name'		=> 'city_popup_results_section',
				'label'		=> esc_html__( 'City popup results wrap', 'drplus' ),
				'condition'	=> [
					'search_city'	=> 'yes'
				]
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // city_popup_result_
			'prefix'	=> 'city_popup_result_',
			'selector'	=> '.specialists-search-city-popup-result',
			
			'section'	=> [
				'name'		=> 'city_popup_result_section',
				'label'		=> esc_html__( 'City popup result item', 'drplus' ),
				'condition'	=> [
					'search_city'	=> 'yes'
				]
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // city_popup_result_city_
			'prefix'	=> 'city_popup_result_city_',
			'base_selector'	=> '.specialists-search-city-popup-result',
			'selector'		=> '.specialists-search-city-popup-result-city',
			
			'section'	=> [
				'name'		=> 'city_popup_result_city_section',
				'label'		=> esc_html__( 'City popup result item - City name', 'drplus' ),
				'condition'	=> [
					'search_city'	=> 'yes'
				]
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // city_popup_result_province_
			'prefix'	=> 'city_popup_result_province_',
			'base_selector'	=> '.specialists-search-city-popup-result',
			'selector'		=> '.specialists-search-city-popup-result-province',
			
			'section'	=> [
				'name'		=> 'city_popup_result_province_section',
				'label'		=> esc_html__( 'City popup result item - Province name', 'drplus' ),
				'condition'	=> [
					'search_city'	=> 'yes'
				]
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // city_popup_result_arrow_
			'prefix'	=> 'city_popup_result_arrow_',
			'base_selector'	=> '.specialists-search-city-popup-result',
			'selector'		=> '.specialists-search-city-popup-result-arrow',
			
			'section'	=> [
				'name'		=> 'city_popup_result_arrow_section',
				'label'		=> esc_html__( 'City popup result item arrow', 'drplus' ),
				'condition'	=> [
					'search_city'	=> 'yes'
				]
			],

			'mode'	=> 'icon',
		] );

		// Speciality
		ElementorControls::general_style_controls( $this, [ // speciality_item_
			'prefix'	=> 'speciality_item_',
			'base_selector'	=> '.specialists-search',
			'selector'		=> '.specialists-search-speciality',
			
			'section'	=> [
				'name'		=> 'speciality_item_section',
				'label'		=> esc_html__( 'Speciality item', 'drplus' ),
				'condition'	=> [
					'search_specialities'	=> 'yes'
				]
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // speciality_item_icon_wrap_
			'prefix'	=> 'speciality_item_icon_wrap_',
			'base_selector'	=> '.specialists-search-speciality',
			'selector'		=> '.specialists-search-speciality-icon-wrap',
			
			'section'	=> [
				'name'		=> 'speciality_item_icon_wrap_section',
				'label'		=> esc_html__( 'Speciality item icon wrap', 'drplus' ),
				'condition'	=> [
					'search_specialities'	=> 'yes'
				]
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // speciality_item_icon_
			'prefix'	=> 'speciality_item_icon_',
			'base_selector'	=> '.specialists-search-speciality',
			'selector'		=> '.specialists-search-speciality-icon-wrap',
			
			'section'	=> [
				'name'		=> 'speciality_item_icon_section',
				'label'		=> esc_html__( 'Speciality item icon', 'drplus' ),
				'condition'	=> [
					'search_specialities'	=> 'yes'
				]
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // speciality_item_checkbox_
			'prefix'	=> 'speciality_item_checkbox_',
			'base_selector'	=> '.specialists-search-speciality',
			'selector'		=> 'input::after',
			
			'section'	=> [
				'name'		=> 'speciality_item_checkbox_section',
				'label'		=> esc_html__( 'Speciality item checkbox', 'drplus' ),
				'condition'	=> [
					'search_specialities'	=> 'yes'
				]
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // speciality_item_checkbox_mark_
			'prefix'	=> 'speciality_item_checkbox_mark_',
			'base_selector'	=> '.specialists-search-speciality',
			'selector'		=> 'input::after',
			
			'section'	=> [
				'name'		=> 'speciality_item_checkbox_mark_section',
				'label'		=> esc_html__( 'Speciality item checkbox mark', 'drplus' ),
				'condition'	=> [
					'search_specialities'	=> 'yes'
				]
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // speciality_item_name_
			'prefix'	=> 'speciality_item_name_',
			'base_selector'	=> '.specialists-search-speciality',
			'selector'		=> '.specialists-search-speciality-name',
			
			'section'	=> [
				'name'		=> 'speciality_item_name_section',
				'label'		=> esc_html__( 'Speciality item name', 'drplus' ),
				'condition'	=> [
					'search_specialities'	=> 'yes'
				]
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // speciality_item_subtitle_
			'prefix'	=> 'speciality_item_subtitle_',
			'base_selector'	=> '.specialists-search-speciality',
			'selector'		=> '.specialists-search-speciality-subtitle',
			
			'section'	=> [
				'name'		=> 'speciality_item_subtitle_section',
				'label'		=> esc_html__( 'Speciality item subtitle', 'drplus' ),
				'condition'	=> [
					'search_specialities'	=> 'yes'
				]
			],

			'mode'	=> 'text',
		] );

		// Dark mode
		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();
		ElementorControls::general_style_controls( $this, [ // dark_wrap
			'prefix'	=> 'wrap_dark_',
			'selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialists-search',
			
			'section'	=> [
				'name'	=> 'dark_wrap_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Box', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_input_wrap_
			'prefix'		=> 'dark_input_wrap_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialists-search',
			'selector'		=> '.specialists-search-input-wrap',
			
			'section'	=> [
				'name'	=> 'dark_input_wrap_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Search input wrap', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_input_icon_
			'prefix'		=> 'dark_input_icon_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialists-search',
			'selector'		=> '.specialists-search-input-icon',
			
			'section'	=> [
				'name'	=> 'dark_input_icon_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Search input icon', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_select_city_wrap_
			'prefix'		=> 'dark_select_city_wrap_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialists-search',
			'selector'		=> '.specialists-search-input-select-city',
			
			'section'	=> [
				'name'		=> 'dark_select_city_wrap_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Select city wrap', 'drplus' ) ),
				'condition'	=> array_merge( [
					'search_city'	=> 'yes'
				], $dark_condition )
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_select_city_icon_
			'prefix'		=> 'dark_select_city_icon_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialists-search-input-wrap',
			'selector'		=> '.specialists-search-input-select-city-icon',
			
			'section'	=> [
				'name'		=> 'dark_select_city_icon_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Select city icon', 'drplus' ) ),
				'condition'	=> array_merge( [
					'search_city'	=> 'yes'
				], $dark_condition )
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_select_city_text_
			'prefix'		=> 'dark_select_city_text_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialists-search-input-wrap',
			'selector'		=> '.specialists-search-input-select-city-icon',
			
			'section'	=> [
				'name'		=> 'dark_select_city_text_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Select city text', 'drplus' ) ),
				'condition'	=> array_merge( [
					'search_city'	=> 'yes'
				], $dark_condition )
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_select_city_arrow_
			'prefix'		=> 'dark_select_city_arrow_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialists-search-input-wrap',
			'selector'		=> '.specialists-search-input-select-city-icon',
			
			'section'	=> [
				'name'		=> 'dark_select_city_arrow_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Select city arrow', 'drplus' ) ),
				'condition'	=> array_merge( [
					'search_city'	=> 'yes'
				], $dark_condition )
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_city_popup_wrap_
			'prefix'	=> 'dark_city_popup_wrap_',
			'selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialists-search-city-popup',
			
			'section'	=> [
				'name'		=> 'dark_city_popup_wrap_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'City popup wrap', 'drplus' ) ),
				'condition'	=> array_merge( [
					'search_city'	=> 'yes'
				], $dark_condition )
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_city_popup_head_
			'prefix'		=> 'dark_city_popup_head_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialists-search-city-popup',
			'selector'		=> '.specialists-search-city-popup-head',
			
			'section'	=> [
				'name'		=> 'dark_city_popup_head_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'City popup head', 'drplus' ) ),
				'condition'	=> array_merge( [
					'search_city'	=> 'yes'
				], $dark_condition )
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_city_popup_title_
			'prefix'	=> 'dark_city_popup_title_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialists-search-city-popup',
			'selector'		=> '.specialists-search-city-popup-title',
			
			'section'	=> [
				'name'		=> 'dark_city_popup_title_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'City popup title', 'drplus' ) ),
				'condition'	=> array_merge( [
					'search_city'	=> 'yes'
				], $dark_condition )
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_city_popup_close_
			'prefix'	=> 'dark_city_popup_close_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialists-search-city-popup',
			'selector'		=> '.specialists-search-city-popup-close',
			
			'section'	=> [
				'name'		=> 'dark_city_popup_close_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'City popup close', 'drplus' ) ),
				'condition'	=> array_merge( [
					'search_city'	=> 'yes'
				], $dark_condition )
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_city_popup_search_wrap_
			'prefix'	=> 'dark_city_popup_search_wrap_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialists-search-city-popup',
			'selector'		=> '.specialists-search-city-popup-search-wrap',
			
			'section'	=> [
				'name'		=> 'dark_city_popup_search_wrap_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'City popup search wrap', 'drplus' ) ),
				'condition'	=> array_merge( [
					'search_city'	=> 'yes'
				], $dark_condition )
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_city_popup_search_field_
			'prefix'	=> 'dark_city_popup_search_field_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialists-search-city-popup',
			'selector'		=> '.specialists-search-city-popup-field-wrap',
			
			'section'	=> [
				'name'		=> 'dark_city_popup_search_field_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'City popup search input wrap', 'drplus' ) ),
				'condition'	=> array_merge( [
					'search_city'	=> 'yes'
				], $dark_condition )
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_city_popup_search_input_icon_
			'prefix'	=> 'dark_city_popup_search_input_icon_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialists-search-city-popup',
			'selector'		=> '.specialists-search-city-popup-search-icon',
			
			'section'	=> [
				'name'		=> 'dark_city_popup_search_input_icon_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'City popup search input icon', 'drplus' ) ),
				'condition'	=> array_merge( [
					'search_city'	=> 'yes'
				], $dark_condition )
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_city_popup_search_input_
			'prefix'	=> 'dark_city_popup_search_input_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialists-search-city-popup',
			'selector'		=> '.specialists-search-city-popup-search',
			
			'section'	=> [
				'name'		=> 'dark_city_popup_search_input_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'City popup search input', 'drplus' ) ),
				'condition'	=> array_merge( [
					'search_city'	=> 'yes'
				], $dark_condition )
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'input',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_city_popup_all_cities_
			'prefix'	=> 'dark_city_popup_all_cities_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialists-search-city-popup',
			'selector'		=> '.specialists-search-city-popup-all-cities',
			
			'section'	=> [
				'name'		=> 'dark_city_popup_all_cities_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'City popup all cities button', 'drplus' ) ),
				'condition'	=> array_merge( [
					'search_city'	=> 'yes'
				], $dark_condition )
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_city_popup_results_
			'prefix'	=> 'dark_city_popup_results_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialists-search-city-popup',
			'selector'		=> '.specialists-search-city-popup-results',
			
			'section'	=> [
				'name'		=> 'dark_city_popup_results_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'City popup results wrap', 'drplus' ) ),
				'condition'	=> array_merge( [
					'search_city'	=> 'yes'
				], $dark_condition )
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_city_popup_result_
			'prefix'	=> 'dark_city_popup_result_',
			'selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialists-search-city-popup-result',
			
			'section'	=> [
				'name'		=> 'dark_city_popup_result_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'City popup result item', 'drplus' ) ),
				'condition'	=> array_merge( [
					'search_city'	=> 'yes'
				], $dark_condition )
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_city_popup_result_city_
			'prefix'	=> 'dark_city_popup_result_city_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialists-search-city-popup-result',
			'selector'		=> '.specialists-search-city-popup-result-city',
			
			'section'	=> [
				'name'		=> 'dark_city_popup_result_city_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'City popup result item - City name', 'drplus' ) ),
				'condition'	=> array_merge( [
					'search_city'	=> 'yes'
				], $dark_condition )
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_city_popup_result_province_
			'prefix'	=> 'dark_city_popup_result_province_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialists-search-city-popup-result',
			'selector'		=> '.specialists-search-city-popup-result-province',
			
			'section'	=> [
				'name'		=> 'dark_city_popup_result_province_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'City popup result item - Province name', 'drplus' ) ),
				'condition'	=> array_merge( [
					'search_city'	=> 'yes'
				], $dark_condition )
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_city_popup_result_arrow_
			'prefix'	=> 'dark_city_popup_result_arrow_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialists-search-city-popup-result',
			'selector'		=> '.specialists-search-city-popup-result-arrow',
			
			'section'	=> [
				'name'		=> 'dark_city_popup_result_arrow_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'City popup result item arrow', 'drplus' ) ),
				'condition'	=> array_merge( [
					'search_city'	=> 'yes'
				], $dark_condition )
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'icon',
		] );

		// Speciality
		ElementorControls::general_style_controls( $this, [ // dark_speciality_item_
			'prefix'	=> 'dark_speciality_item_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialists-search',
			'selector'		=> '.specialists-search-speciality',
			
			'section'	=> [
				'name'		=> 'dark_speciality_item_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Speciality item', 'drplus' ) ),
				'condition'	=> array_merge( [
					'search_specialities'	=> 'yes'
				], $dark_condition )
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_speciality_item_icon_wrap_
			'prefix'	=> 'dark_speciality_item_icon_wrap_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialists-search-speciality',
			'selector'		=> '.specialists-search-speciality-icon-wrap',
			
			'section'	=> [
				'name'		=> 'dark_speciality_item_icon_wrap_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Speciality item icon wrap', 'drplus' ) ),
				'condition'	=> array_merge( [
					'search_specialities'	=> 'yes'
				], $dark_condition )
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_speciality_item_icon_
			'prefix'	=> 'dark_speciality_item_icon_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialists-search-speciality',
			'selector'		=> '.specialists-search-speciality-icon-wrap',
			
			'section'	=> [
				'name'		=> 'dark_speciality_item_icon_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Speciality item icon', 'drplus' ) ),
				'condition'	=> array_merge( [
					'search_specialities'	=> 'yes'
				], $dark_condition )
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_speciality_item_checkbox_
			'prefix'	=> 'dark_speciality_item_checkbox_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialists-search-speciality',
			'selector'		=> 'input::after',
			
			'section'	=> [
				'name'		=> 'dark_speciality_item_checkbox_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Speciality item checkbox', 'drplus' ) ),
				'condition'	=> array_merge( [
					'search_specialities'	=> 'yes'
				], $dark_condition )
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_speciality_item_checkbox_mark_
			'prefix'	=> 'dark_speciality_item_checkbox_mark_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialists-search-speciality',
			'selector'		=> 'input::after',
			
			'section'	=> [
				'name'		=> 'dark_speciality_item_checkbox_mark_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Speciality item checkbox mark', 'drplus' ) ),
				'condition'	=> array_merge( [
					'search_specialities'	=> 'yes'
				], $dark_condition )
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_speciality_item_name_
			'prefix'	=> 'dark_speciality_item_name_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialists-search-speciality',
			'selector'		=> '.specialists-search-speciality-name',
			
			'section'	=> [
				'name'		=> 'dark_speciality_item_name_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Speciality item name', 'drplus' ) ),
				'condition'	=> array_merge( [
					'search_specialities'	=> 'yes'
				], $dark_condition )
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // dark_speciality_item_subtitle_
			'prefix'	=> 'dark_speciality_item_subtitle_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialists-search-speciality',
			'selector'		=> '.specialists-search-speciality-subtitle',
			
			'section'	=> [
				'name'		=> 'dark_speciality_item_subtitle_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Speciality item subtitle', 'drplus' ) ),
				'condition'	=> array_merge( [
					'search_specialities'	=> 'yes'
				], $dark_condition )
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'text',
		] );
	}

	protected function render() {
		get_template_part( "templates/components/template-components-specialists-search", null, $this->get_settings_for_display() );
	}
}