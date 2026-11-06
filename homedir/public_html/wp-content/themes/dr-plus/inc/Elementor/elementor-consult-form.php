<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;

class ConsultForm extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_consult_form';
	}

	public function get_title() {
		return esc_html__( 'Consult form (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-calendar';
	}

	public function get_categories() {
		return ['drplus', 'basic'];
	}

	public function get_keywords() {
		return ['book', 'consult', 'reserve', 'appointment', 'visit', 'form', 'نوبت', 'رزرو', 'ویزیت', 'پذیرش', 'فرم', "مشاوره"];
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			"settings_section",
			[
				'label'	=> esc_html__( 'General settings', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_responsive_control( // columns
			'columns',
			[
				'label'				=> esc_html__( 'Columns', 'drplus' ),
				'type'				=> \Elementor\Controls_Manager::NUMBER,
				'default'			=> 2,
				'tablet_default'	=> 2,
				'mobile_default'	=> 1,
				'min'				=> 1,
				'ai'				=> [
					'type'	=> 'text',
				],
				'dynamic'			=> [
					'active'	=> true,
				],
			]
		);

		$this->end_controls_section();
	}

	private function fields_settings( $field_key, $label ) {
		$prefix = "{$field_key}_";
		$this->start_controls_section( // content_section
			$prefix . "settings_section",
			[
				'label'	=> sprintf( esc_html__( '%s Settings', 'drplus' ), $label ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // status
			$prefix . 'status',
			[
				'label'			=> sprintf( esc_html__( 'Activate %s field', 'drplus' ), $label ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->add_control( // placeholder
			$prefix . 'placeholder',
			[
				'label'			=> sprintf( esc_html__( '%s field placeholder', 'drplus' ), $label ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> $label,
				'ai'			=> [
					'type'		=> 'text',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
				'condition'		=> [
					$prefix . 'status'	=> 'yes'
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();
		// If you want to change the fields list, also change them in template part file
		$fields = [
			'first_name'	=> __( 'Firstname', 'drplus' ),
			'last_name'		=> __( 'Lastname', 'drplus' ),
			'nid'			=> __( 'National ID', 'drplus' ),
			'phone'			=> __( 'Phone', 'drplus' ),
			'specialist'	=> __( "Specialist", 'drplus' ),
		];
		foreach( $fields as $field_key => $label ) {
			$this->fields_settings( $field_key, $label );
		}
		ElementorControls::button_settings( $this, [
			'excludes'	=> ['link', 'new_tab'],
			'controls'	=> [
				'text'	=> [
					'default'	=> esc_html__( 'Submit request', 'drplus' )
				],
				'align'	=> [
					'default'	=> 'end'
				],
			],
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'form_',
			'base_selector'	=> '.drplus-consult-form-widget',
			
			'section'	=> [
				'name'	=> 'form_styles',
				'label'	=> esc_html__( 'Form style', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'input_wrap_',
			'base_selector'	=> '.input-wrap',
			
			'section'	=> [
				'name'	=> 'input_wrap_styles',
				'label'	=> esc_html__( 'Input wrap style', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'input_',
			'base_selector'	=> '.input-wrap',
			'selector'		=> 'input',
			
			'section'	=> [
				'name'	=> 'input_styles',
				'label'	=> esc_html__( 'Input style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'select_',
			'base_selector'	=> '.input-wrap',
			'selector'		=> '.select2-container .select2-selection--single .select2-selection__rendered.select2-selection__rendered',
			
			'section'	=> [
				'name'	=> 'select_styles',
				'label'	=> esc_html__( 'Dropdown lists style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [ // button
			'prefix'	=> 'button_',
			'selector'	=> '.button',
			
			'section'	=> [
				'name'	=> 'button_section',
				'label'	=> esc_html__( 'Button', 'drplus' ),
			],

			'mode'	=> 'wrapper',
		] );
		ElementorControls::general_style_controls( $this, [ // button_text
			'prefix'		=> 'button_text_',
			'base_selector'	=> '.button',
			'selector'		=> '.button-text',
			
			'section'	=> [
				'name'	=> 'button_text_section',
				'label'	=> esc_html__( 'Button text', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // button_icon
			'prefix'		=> 'button_icon_',
			'base_selector'	=> '.button',
			'selector'		=> '.button-icon',
			
			'section'	=> [
				'name'	=> 'button_icon_section',
				'label'	=> esc_html__( 'Button icon', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_form_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-consult-form-widget',
			
			'section'	=> [
				'name'	=> 'dark_form_styles',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Form style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],
			
			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_input_wrap_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .input-wrap',
			
			'section'	=> [
				'name'	=> 'dark_input_wrap_styles',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Input wrap style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_input_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .input-wrap',
			'selector'		=> 'input',
			
			'section'	=> [
				'name'	=> 'dark_input_styles',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Input style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_select_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .input-wrap',
			'selector'		=> '.select2-container .select2-selection--single .select2-selection__rendered.select2-selection__rendered',
			
			'section'	=> [
				'name'	=> 'dark_select_styles',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Dropdown lists style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [ // button
			'prefix'	=> 'dark_button_',
			'selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .button',
			
			'section'	=> [
				'name'	=> 'dark_button_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Button', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'wrapper',
		] );
		ElementorControls::general_style_controls( $this, [ // button_text
			'prefix'		=> 'dark_button_text_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .button',
			'selector'		=> '.button-text',
			
			'section'	=> [
				'name'	=> 'dark_button_text_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Button text', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // button_icon
			'prefix'		=> 'dark_button_icon_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .button',
			'selector'		=> '.button-icon',
			
			'section'	=> [
				'name'	=> 'dark_button_icon_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Button icon', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'icon',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		get_template_part( "templates/components/template-components-consult-form", null, $settings );
	}
}