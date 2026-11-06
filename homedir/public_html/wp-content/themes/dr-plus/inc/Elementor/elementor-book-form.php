<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;

class BookForm extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_book_form';
	}

	public function get_title() {
		return esc_html__( 'Book form (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-calendar';
	}

	public function get_categories() {
		return ['drplus', 'basic'];
	}

	public function get_keywords() {
		return ['book', 'reserve', 'appointment', 'visit', 'form', 'نوبت', 'رزرو', 'ویزیت', 'پذیرش', 'فرم'];
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

		$this->add_control( // btn_divider
			'btn_divider',
			[
				'label'			=> esc_html__( 'Show divider top of the button', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> false,
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

		if( $field_key == 'foreign_customer' ) {
			$this->add_control( // style
				$prefix . 'input_style',
				[
					'label'			=> sprintf( esc_html__( 'Apply field style for %s field', 'drplus' ), $label ),
					'type'			=> \Elementor\Controls_Manager::SWITCHER,
					'label_on'		=> esc_html__( 'Yes', 'drplus' ),
					'label_off'		=> esc_html__( 'No', 'drplus' ),
					'return_value'	=> 'yes',
					'default'		=> 0,
					'condition'		=> [
						$prefix . 'status'	=> 'yes'
					],
				]
			);
			$placeholder = esc_html__( 'I am a foreign national and do not have a national ID number.', 'drplus' );
		} else {
			$placeholder = $label;
		}

		if( !in_array( $field_key, ['birthday', 'gender'] ) ) {
			$this->add_control( // placeholder
				$prefix . 'placeholder',
				[
					'label'			=> sprintf( esc_html__( '%s field placeholder', 'drplus' ), $label ),
					'label_block'	=> true,
					'type'			=> \Elementor\Controls_Manager::TEXT,
					'default'		=> $placeholder,
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
		}

		$this->end_controls_section();
	}

	private function dark_mode_toggle_controls() {
		$this->start_controls_section(
			'book_form_dark_mode_toggle',
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
		// If you want to change the fields list, also change them in template part file
		$fields = [
			'first_name'		=> __( 'Firstname', 'drplus' ),
			'last_name'			=> __( 'Lastname', 'drplus' ),
			'nid'				=> __( 'National ID', 'drplus' ),
			'foreign_customer'	=> __( 'Foreign Customer', 'drplus' ),
			'gender'			=> __( 'Gender', 'drplus' ),
			'birthday'			=> __( 'Year of birth', 'drplus' ),
			'phone'				=> __( 'Phone', 'drplus' ),
			'email'				=> __( 'Email', 'drplus' ),
			'specialist'		=> __( "Specialist", 'drplus' ),
		];
		foreach( $fields as $field_key => $label ) {
			$this->fields_settings( $field_key, $label );
		}
		ElementorControls::button_settings( $this, [
			'excludes'	=> ['link', 'new_tab'],
			'controls'	=> [
				'text'	=> [
					'default'	=> esc_html__( 'Submit appointment request', 'drplus' )
				],
				'icon'	=> [
					'default'	=> [
						'value'		=> is_rtl() ? 'drplus-icon-arrow-up-left-square' : 'drplus-icon-arrow-up-right-square',
						'library'	=> 'drplus-icon'
					]
				],
				'icon_align'	=> [
					'default'	=> 'end',
				],
				'align'	=> [
					'default'	=> 'end'
				],
			],
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'form_',
			'base_selector'	=> '.drplus-book-form-widget',
			
			'section'	=> [
				'name'	=> 'form_styles',
				'label'	=> esc_html__( 'Form style', 'drplus' ),
			],

			'controls'	=> [
				'divider'	=> [
					'type'		=> \Elementor\Controls_Manager::COLOR,
					'label'		=> esc_html__( 'Divider color', 'drplus' ),
					'selectors'	=> [
						"{{WRAPPER}} .drplus-book-form-widget-button-divider"	=> '--divider-color: {{VALUE}};',
					],
					'condition'	=> [
						'btn_divider'	=> 'yes'
					]
				]
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
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-book-form-widget',
			
			'section'	=> [
				'name'		=> 'dark_form_styles',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Form style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'controls'	=> [
				'dark_divider'	=> [
					'type'		=> \Elementor\Controls_Manager::COLOR,
					'label'		=> esc_html__( 'Divider color', 'drplus' ),
					'selectors'	=> [
						'html[data-theme="dark"] {{WRAPPER}} .drplus-book-form-widget-button-divider'	=> '--divider-color: {{VALUE}};',
					],
					'condition'	=> [
						'btn_divider'	=> 'yes'
					]
				]
			],

			'mode'				=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_input_wrap_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .input-wrap',
			
			'section'	=> [
				'name'		=> 'dark_input_wrap_styles',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Input wrap style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_input_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .input-wrap',
			'selector'		=> 'input',
			
			'section'	=> [
				'name'		=> 'dark_input_styles',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Input style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_select_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .input-wrap',
			'selector'		=> '.select2-container .select2-selection--single .select2-selection__rendered.select2-selection__rendered',
			
			'section'	=> [
				'name'		=> 'dark_select_styles',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Dropdown lists style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [ // button
			'prefix'	=> 'dark_button_',
			'selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .button',
			
			'section'	=> [
				'name'		=> 'dark_button_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Button', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'wrapper',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // button_text
			'prefix'		=> 'dark_button_text_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .button',
			'selector'		=> '.button-text',
			
			'section'	=> [
				'name'		=> 'dark_button_text_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Button text', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // button_icon
			'prefix'		=> 'dark_button_icon_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .button',
			'selector'		=> '.button-icon',
			
			'section'	=> [
				'name'		=> 'dark_button_icon_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Button icon', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'icon',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		get_template_part( "templates/components/template-components-book-form", null, $settings );
	}
}
