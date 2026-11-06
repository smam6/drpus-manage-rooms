<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
use DrPlus\Utils;
use DrPlus\Utils\Elementor;

class Search extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_search';
	}

	public function get_title() {
		return esc_html__( 'Search (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-search';
	}

	public function get_categories() {
		return ['drplus', 'basic'];
	}

	public function get_keywords() {
		return ['search', 'doctor', 'clinic', 'specialist', 'speciality', 'جستجو', 'دکتر', 'کلینیک', 'بیمارستان', 'تخصص', 'متخصص'];
	}

	private function search_settings_controls() {
		$this->start_controls_section( // content_section
			'search_settings_section',
			[
				'label'	=> esc_html__( 'Search settings', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$post_types = get_post_types( [
			'public'				=> true,
			'exclude_from_search'	=> false,
		], 'objects' );
		$post_types = wp_list_pluck( $post_types, 'label', 'name' );
		$post_types['specialist'] = esc_html__( "Specialists", 'drplus' );

		$this->add_control(
			'excludes',
			[
				'type' 			=> \Elementor\Controls_Manager::SELECT2,
				'label'			=> esc_html__( "Exclude post types", 'drplus' ),
				'label_block'	=> true,
				'multiple'		=> true,
				'default'		=> ['e-floating-buttons', 'page', 'attachment'],
				'options'		=> $post_types,
			]
		);

		$this->end_controls_section();
	}

	private function fields_settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Fields settings', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // search_field
			'search_field',
			[
				'label'			=> esc_html__( 'Show search field', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->add_control( // search_placeholder
			'search_placeholder',
			[
				'label'			=> esc_html__( 'Search placeholder', 'drplus' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( 'Search for doctor name, clinic, specialty and more', 'drplus' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
				'condition'		=> [
					'search_field'	=> 'yes'
				],
			]
		);

		$this->add_control( // city_field
			'city_field',
			[
				'label'			=> esc_html__( 'Show city field', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->add_control( // city_placeholder
			'city_placeholder',
			[
				'label'			=> esc_html__( 'City placeholder', 'drplus' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( 'All cities', 'drplus' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
				'condition'		=> [
					'city_field'	=> 'yes'
				],
			]
		);

		$this->end_controls_section();
	}

	private function input_style_controls( $is_dark = false ) {
		$wrap_selector = "{{WRAPPER}} .drplus-search-input, {{WRAPPER}} .drplus-search-select-wrap";
		$input_selector = "{{WRAPPER}} .drplus-search-input, {{WRAPPER}} .drplus-city";
		$placeholder_selector = "{{WRAPPER}} .drplus-search-input::placeholder, {{WRAPPER}} .drplus-city::placeholder";
		$prefix = 'input_';

		$section_args = [
			'label'	=> esc_html__( 'Inputs style', 'drplus' ),
			'tab'	=> \Elementor\Controls_Manager::TAB_STYLE,
		];

		if( $is_dark ) {
			$wrap_selector = 'html[data-theme="dark"] {{WRAPPER}} .drplus-search-input, html[data-theme="dark"] {{WRAPPER}} .drplus-search-select-wrap';
			$input_selector = 'html[data-theme="dark"] {{WRAPPER}} .drplus-search-input, html[data-theme="dark"] {{WRAPPER}} .drplus-city';
			$placeholder_selector = 'html[data-theme="dark"] {{WRAPPER}} .drplus-search-input::placeholder, html[data-theme="dark"] {{WRAPPER}} .drplus-city::placeholder';
			$prefix = 'dark_input_';
			$section_args['condition'] = [
				'enable_dark_mode' 	=> 'yes',
			];
			$section_args['label'] = ElementorControls::dark_control_label( esc_html__( 'Inputs style', 'drplus' ) );
		}

		$this->start_controls_section(
			"style_{$prefix}section",
			[
				'label'	=> esc_html__( 'Inputs style', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		if( !$is_dark ) {
			ElementorControls::margin( $this, "{$prefix}margin", $wrap_selector );
			ElementorControls::padding( $this, "{$prefix}padding", $input_selector );
			ElementorControls::typography( $this, "{$prefix}typography", $input_selector );
		}
		ElementorControls::background( $this, "{$prefix}background", $wrap_selector );
		ElementorControls::color( $this, "{$prefix}color", $input_selector );
		ElementorControls::color( $this, "{$prefix}placeholder_color", $placeholder_selector, [
			'label'	=> esc_html__( 'Placeholder color', 'drplus' ),
		] );
		if( !$is_dark ) {
			ElementorControls::border( $this, "{$prefix}border", $wrap_selector );
		}
		ElementorControls::border_radius( $this, "{$prefix}border_radius", $wrap_selector );
		ElementorControls::box_shadow( $this, "{$prefix}box_shadow", $wrap_selector );

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->search_settings_controls();
		$this->fields_settings_controls();
		ElementorControls::button_settings( $this, [
			'excludes'	=> [
				'link',
				'new_tab',
				'text',
				'align',
				'icon_align',
			],
			'controls'	=> [
				'show_button'	=> [
					'label'			=> esc_html__( 'Show search button', 'drplus' ),
					'type'			=> \Elementor\Controls_Manager::SWITCHER,
					'label_on'		=> esc_html__( 'Show', 'drplus' ),
					'label_off'		=> esc_html__( 'Hide', 'drplus' ),
					'return_value'	=> 'yes',
					'default'		=> 'yes',
				],
				'transparent'	=> [
					'condition'	=> [
						'button_show_button'	=> 'yes'
					],
				],
				'type'	=> [
					'condition'	=> [
						'button_show_button'	=> 'yes'
					],
				],
				'small'	=> [
					'condition'	=> [
						'button_show_button'	=> 'yes'
					],
				],
				'icon'	=> [
					'default'	=> [
						'value'		=> 'drplus-icon-search',
						'library'	=> 'drplus-icon',
					],
					'condition'	=> [
						'button_show_button'	=> 'yes'
					],
				],
				'style'	=> [
					'default'	=> 'rounded',
					'condition'	=> [
						'button_show_button'	=> 'yes'
					],
				],
				'fullwidth'	=> [
					'condition'	=> [
						'button_show_button'	=> 'yes'
					],
				],
			],
		] );

		$this->input_style_controls();
		ElementorControls::general_style_controls( $this, [ // button
			'prefix'		=> 'button_',
			'selector'		=> '.drplus-search-button',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'button_section',
				'label'	=> esc_html__( 'Button', 'drplus' ),
			],

			'mode'	=> 'wrapper',
		] );
		ElementorControls::general_style_controls( $this, [ // button_icon
			'prefix'			=> 'button_icon_',
			'base_selector'		=> '.drplus-search-button',
			'selector'			=> '.button-icon',
			'hover_selector'	=> '.drplus-search-button:hover .button-icon',
			
			'section'	=> [
				'name'	=> 'button_icon_section',
				'label'	=> esc_html__( 'Button icon', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		$this->input_style_controls( true );

		ElementorControls::general_style_controls( $this, [ // button
			'prefix' 	=> 'dark_button_',
			'selector' 	=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-search-button',
			
			'section' 	=> [
				'name' 	=> 'dark_button_section',
				'label' 	=> ElementorControls::dark_control_label( esc_html__( 'Button', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,
			'mode' 	=> 'wrapper',
		] );
		ElementorControls::general_style_controls( $this, [ // button_icon
			'prefix' 	=> 'dark_button_icon_',
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .drplus-search-button',
			'selector' 	=> 'html[data-theme="dark"] {{WRAPPER}} .button-icon',
			'hover_selector' 	=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-search-button:hover .button-icon',
			
			'section' 	=> [
				'name' 	=> 'dark_button_icon_section',
				'label' 	=> ElementorControls::dark_control_label( esc_html__( 'Button icon', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,
			'mode' 	=> 'icon',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		// Backward
		$excludes = !empty( $settings['excludes'] ) ? $settings['excludes'] : [];
		if( isset( $settings['search_hospitals'] ) && !Utils::to_bool( $settings['search_hospitals'] ) ) {
			$excludes[] = 'hospital';
		}
		if( DRPLUS_SINGLE_SPECIALIST_MODE ) $settings['search_specialist'] = false;
		if( isset( $settings['search_specialists'] ) && !Utils::to_bool( $settings['search_specialists'] ) ) {
			$excludes[] = 'specialist';
		}
		if( isset( $settings['search_specialities'] ) && !Utils::to_bool( $settings['search_specialities'] ) ) {
			$excludes[] = 'speciality';
		}

		$args = [
			'excludes'				=> $excludes,
			'search_field'			=> Utils::to_bool( $settings['search_field'] ),
			'city_field'			=> Utils::to_bool( $settings['city_field'] ),
			'search_placeholder'	=> esc_attr( $settings['search_placeholder'] ),
			'city_placeholder'		=> esc_attr( $settings['city_placeholder'] ),
			'button_show_button'	=> Utils::to_bool( $settings['button_show_button'] ),
		];
		if( $args['button_show_button'] ) {
			$args = array_merge( $args, Elementor::get_button_args( $settings ) );
		}
		
		get_template_part( "templates/components/template-components-search", null, $args );
	}
}