<?php
namespace DrPlus\Elementor;

use DrPlus\Utils;
use DrPlus\Utils\Elementor;
use DrPlus\ElementorControls;
use DrPlus\Utils\Hospital;
use DrPlus\Utils\UtilsSpecialists;

class SpecialistOffices extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_specialist_offices';
	}

	public function get_title() {
		return esc_html__( 'Specialist offices (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-google-maps';
	}

	public function get_categories() {
		return ['drplus', 'drplus_single_specialist'];
	}

	public function get_keywords() {
		return ['specialist', 'office', 'متخصص', 'مطب'];
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
				'default'		=> esc_html__( 'Clinic address of %s', 'drplus' ),
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
					'value'		=> 'drplus-icon-location-fill',
					'library'	=> 'drplus-icon'
				],
				'label_block'	=> false,
			],
		);

		$this->add_control(
			'show_office_image',
			[
				'label'			=> esc_html__( 'Show office image', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->add_control(
			'show_office_phones',
			[
				'label'			=> esc_html__( 'Show office phones', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->add_control(
			'show_office_address',
			[
				'label'			=> esc_html__( 'Show office address', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->add_control(
			'show_office_map_link',
			[
				'label'			=> esc_html__( 'Show office map link', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'container_',
			'selector'		=> '.specialist_offices',
			'section'	=> [
				'label'	=> esc_html__( 'Container', 'drplus'),
				'name'	=> 'container',
			],
			'mode'		=> 'wrap',
		] );

		ElementorControls::section_title_styles( $this, false, true );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'office_item_',
			'selector'		=> '.specialist_office',
			'section'	=> [
				'label'	=> esc_html__( 'Office container', 'drplus'),
				'name'	=> 'office_item',
			],
			'mode'		=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'office_item_image_',
			'selector'		=> '.specialist_office-img',
			'section'	=> [
				'label'	=> esc_html__( 'Office image', 'drplus'),
				'name'	=> 'office_item_image',
				'condition'	=> [
					'show_office_image'	=> 'yes'
				]
			],
			'mode'		=> 'img',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'office_item_name_',
			'selector'		=> '.specialist_office-name',
			'section'	=> [
				'label'	=> esc_html__( 'Office name', 'drplus'),
				'name'	=> 'office_item_name',
			],
			'mode'		=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'office_item_name_icon_',
			'selector'		=> '.specialist_office-link i',
			'section'	=> [
				'label'	=> esc_html__( 'Office name icon', 'drplus'),
				'name'	=> 'office_item_name_icon',
			],
			'mode'		=> 'icon',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'office_item_phone_',
			'selector'		=> '.specialist_office-phone',
			'section'	=> [
				'label'	=> esc_html__( 'Office phone', 'drplus'),
				'name'	=> 'office_item_phone',
				'condition'	=> [
					'show_office_phones'	=> 'yes'
				]
			],
			'mode'		=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'office_item_phone_icon_',
			'selector'		=> '.specialist_office-phone i',
			'section'	=> [
				'label'	=> esc_html__( 'Office phone icon', 'drplus'),
				'name'	=> 'office_item_phone_icon',
				'condition'	=> [
					'show_office_phones'	=> 'yes'
				]
			],
			'mode'		=> 'icon',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'office_item_address_',
			'selector'		=> '.specialist_office-address',
			'section'	=> [
				'label'	=> esc_html__( 'Office address', 'drplus'),
				'name'	=> 'office_item_address',
				'condition'	=> [
					'show_office_address'	=> 'yes'
				]
			],
			'mode'		=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'office_item_map_link_',
			'selector'		=> '.specialist_office-map',
			'section'	=> [
				'label'	=> esc_html__( 'Office map link', 'drplus'),
				'name'	=> 'office_item_map_link',
				'condition'	=> [
					'show_office_map_link'	=> 'yes'
				]
			],
			'mode'		=> 'text',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_container_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_offices',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Container', 'drplus') ),
				'name'	=> 'dark_container',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::section_title_styles( $this, false, true, true );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_office_item_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_office',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Office container', 'drplus') ),
				'name'	=> 'dark_office_item',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_office_item_image_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_office-img',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Office image', 'drplus') ),
				'name'	=> 'dark_office_item_image',
				'condition'	=> [
					'show_office_image'	=> 'yes'
				] + $dark_condition
			],
			'mode'		=> 'img',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_office_item_name_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_office-name',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Office name', 'drplus') ),
				'name'	=> 'dark_office_item_name',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_office_item_name_icon_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_office-link i',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Office name icon', 'drplus') ),
				'name'	=> 'dark_office_item_name_icon',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'icon',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_office_item_phone_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_office-phone',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Office phone', 'drplus') ),
				'name'	=> 'dark_office_item_phone',
				'condition'	=> [
					'show_office_phones'	=> 'yes'
				] + $dark_condition
			],
			'mode'		=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_office_item_phone_icon_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_office-phone i',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Office phone icon', 'drplus') ),
				'name'	=> 'dark_office_item_phone_icon',
				'condition'	=> [
					'show_office_phones'	=> 'yes'
				] + $dark_condition
			],
			'mode'		=> 'icon',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_office_item_address_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_office-address',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Office address', 'drplus') ),
				'name'	=> 'dark_office_item_address',
				'condition'	=> [
					'show_office_address'	=> 'yes'
				] + $dark_condition
			],
			'mode'		=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_office_item_map_link_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_office-map',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Office map link', 'drplus') ),
				'name'	=> 'dark_office_item_map_link',
				'condition'	=> [
					'show_office_map_link'	=> 'yes'
				] + $dark_condition
			],
			'mode'		=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		$specialist = UtilsSpecialists::get_specialist_from_post();
		if( empty( $specialist ) || empty( $specialist->offices ) ) return;

		$specialist_offices = [];
		$specialist_locations = get_the_terms( get_the_ID(), 'location' );
		foreach( $specialist->offices as $office ) {
			if( $office['type'] == 'consultation' ) continue;

			// Get office image
			if( $office['type'] == 'hospital' ) {
				$office['image'] = get_the_post_thumbnail_url( $office['id'] );
			} else if( !empty( $office['image'] ) ) {
				$office['image'] = wp_get_attachment_image_url( $office['image'] );
			}
			if( empty( $office['image'] ) ) {
				$office['image'] = DRPLUS_URI . 'assets/images/hospital-placeholder.webp';
			}

			if( $office['type'] == 'hospital' ) {
				$hospital_settings = Hospital::get_options( $office['id'] );
				$office['name'] = get_the_title( $office['id'] );
				$office['phone'] = $hospital_settings['phones'][0]['phone'] ?? "";
				$office_phone = $office['phone'];
				$office['address'] = $hospital_settings['address'];
				$office['map_url'] = $hospital_settings['map_address'];
				$office['province'] = $hospital_settings['province'];
				$office['city'] = $hospital_settings['city'];
			} else {
				$office_phone = explode( PHP_EOL, $office['phone'] );
				if( !empty( $office_phone ) ) $office_phone = Utils::convert_chars( $office_phone[0] );
				
				if( !empty( $office['province'] ) ) {
					foreach( $specialist_locations as $location ) {
						if( $location->term_id == $office['province'] ) {
							$office['province_id'] = $office['province'];
							$office['province'] = $location->name;
							break;
						}
					}
				}
				if( !empty( $office['city'] ) ) {
					foreach( $specialist_locations as $location ) {
						if( $location->term_id == $office['city'] ) {
							$office['city_id'] = $office['city'];
							$office['city'] = $location->name;
							break;
						}
					}
				}
			}
			$specialist_offices[] = $office;
		}

		if( empty( $specialist_offices ) ) return "";

		get_template_part( 'templates/specialists/single/template-specialists-single-offices', null, [
			'prefix'				=> 'specialist_',
			'specialist'			=> $specialist,
			'options'				=> [
				'single_specialist_sections_tag'	=> $settings['section_tag'],
				'section_title'						=> $settings['section_title'],
				'section_icon'						=> $settings['section_icon'],
				'show_office_image'					=> $settings['show_office_image'],
				'show_office_phones'				=> $settings['show_office_phones'],
				'show_office_address'				=> $settings['show_office_address'],
				'show_office_map_link'				=> $settings['show_office_map_link'],
			],
			'specialist_offices'	=> $specialist_offices,
		] );
	}
}