<?php
namespace DrPlus\Elementor;

use DrPlus\Utils;
use DrPlus\Utils\Elementor;
use DrPlus\ElementorControls;
use DrPlus\Utils\Booking;
use DrPlus\Utils\SubscriptionPlans;
use DrPlus\Utils\UtilsSpecialists;

class SpecialistBookButton extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_specialist_book_button';
	}

	public function get_title() {
		return esc_html__( 'Specialist Book Button (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-calendar';
	}

	public function get_categories() {
		return ['drplus', 'drplus_single_specialist'];
	}

	public function get_keywords() {
		return ['specialist', 'book', 'reserve', 'نوبت', 'رزرو'];
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

		$this->add_control( // show_score_text
			'button_text',
			[
				'label'			=> esc_html__( 'Text', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( 'Book an appointment', 'drplus' ),
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

		$this->add_control(
			'link_type',
			[
				'label'		=> esc_html__( 'Visit Link type', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'all',
				'options'	=> [
					'all'			=> esc_html__( 'All visits', 'drplus' ),
					'in_person'		=> esc_html__( 'In-person visits', 'drplus' ),
					'consultation'	=> esc_html__( 'Consultation visits', 'drplus' ),
				],
			],
		);

		$this->add_control(
			'button_icon',
			[
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Icon', 'drplus' ),
				'skin'			=> 'inline',
				'default'		=> [
					'value'		=> is_rtl() ? 'drplus-icon-arrow-up-left-square' : 'drplus-icon-arrow-up-right-square',
					'library'	=> 'drplus-icon'
				],
				'label_block'	=> false,
			],
		);

		$this->add_control(
			'button_icon_align',
			[
				'label'		=> esc_html__( 'Icon Position', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::CHOOSE,
				'default'	=> 'start',
				'options'	=> [
					'start'	=> [
						'title'	=> esc_html__( 'Start', 'drplus' ),
						'icon'	=> 'eicon-h-align-left',
					],
					'end'	=> [
						'title'	=> esc_html__( 'End', 'drplus' ),
						'icon'	=> 'eicon-h-align-right',
					],
				],
				'condition'	=> [
					'button_icon[value]!'	=> '',
				],
			],
		);

		$this->add_control(
			'button_new_tab',
			[
				'label'			=> esc_html__( "Open in new tab", 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
			],
		);

		$this->add_control(
			'button_transparent',
			[
				'label'			=> esc_html__( 'Transparent button', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
			]
		);
		$this->add_control(
			'button_type',
			[
				'label'		=> esc_html__( 'Button type', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'primary',
				'options'	=> Utils::button_types(),
				'condition'	=> [
					'button_transparent!'	=> 'yes'
				]
			]
		);
		$this->add_control(
			'button_small',
			[
				'label'			=> esc_html__( 'Small button', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
			]
		);

		$this->add_control(
			'button_fullwidth',
			[
				'label'			=> esc_html__( 'Fullwidth', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
			],
		);

		$this->add_control(
			'button_align',
			[
				'label'		=> esc_html__( 'Alignment', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::CHOOSE,
				'options'	=> [
					'start'		=> [
						'title'	=> esc_html__( 'Start', 'drplus' ),
						'icon'	=> 'eicon-text-align-left',
					],
					'center'	=> [
						'title'	=> esc_html__( 'Center', 'drplus' ),
						'icon'	=> 'eicon-text-align-center',
					],
					'end'		=> [
						'title'	=> esc_html__( 'End', 'drplus' ),
						'icon'	=> 'eicon-text-align-right',
					],
				],
				'default'	=> 'start',
				'toggle'	=> true,
				'condition'	=> [
					'button_fullwidth!'	=> 'yes'
				],
			],
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'button_style_',
			'selector'		=> '.button',
			'section'	=> [
				'label'	=> esc_html__( 'Button style', 'drplus'),
				'name'	=> 'button_style',
			],
			'mode'		=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'button_text_',
			'selector'		=> '.button-text',
			'section'	=> [
				'label'	=> esc_html__( 'Button text', 'drplus'),
				'name'	=> 'button_text',
			],
			'mode'		=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'button_icon_',
			'selector'		=> '.button-icon',
			'section'	=> [
				'label'	=> esc_html__( 'Button icon', 'drplus'),
				'name'	=> 'button_icon',
			],
			'mode'		=> 'icon',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_button_style_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .button',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Button style', 'drplus') ),
				'name'	=> 'dark_button_style',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_button_text_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .button-text',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Button text', 'drplus') ),
				'name'	=> 'dark_button_text',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_button_icon_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .button-icon',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Button icon', 'drplus') ),
				'name'	=> 'dark_button_icon',
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
		if( empty( $specialist ) || empty( $specialist->id ) ) return;
		$is_elementor_editor = \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode();
		if( !SubscriptionPlans::is_specialist_plan_active( $specialist->user_id ) && !$is_elementor_editor ) return;

		if( !$is_elementor_editor ) {
			if(
				( $settings['link_type'] == 'all' && !Utils::to_bool( $specialist->offline_visit ) && !Utils::to_bool( $specialist->online_visit ) ) ||
				( $settings['link_type'] == 'in_person' && !Utils::to_bool( $specialist->offline_visit ) ) ||
				( $settings['link_type'] == 'consultation' && !Utils::to_bool( $specialist->online_visit ) )
			) return;
		}

		$link = Booking::get_booking_page_url( 'time' );
		if( $settings['link_type'] == 'all' ) {
			$link = add_query_arg( ['sid' => $specialist->id], $link );
		} else if( $settings['link_type'] == 'in_person' ) {
			$link = $link = add_query_arg( ['sid' => $specialist->id, 'in_person_visit' => 1], $link );
		} else if(  $settings['link_type'] == 'consultation') {
			$link = $link = add_query_arg( ['sid' => $specialist->id, 'consultation' => 1], $link );
		}

		$settings['button_link'] = [
			'url'	=> $link,
		];
		get_template_part( "templates/components/template-components-button", null, Elementor::get_button_args( $settings, '' ) );
	}
}