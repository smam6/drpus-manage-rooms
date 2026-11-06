<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
use DrPlus\Utils;
use DrPlus\Utils\Archive;
use DrPlus\Utils\Sanitizers;

class Hospitals extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_hospitals';
	}

	public function get_title() {
		return esc_html__( 'Hospitals (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-apps';
	}

	public function get_categories() {
		return ['drplus', 'basic'];
	}

	public function get_keywords() {
		return ['hospital', 'clinic', 'office', 'doctor', 'specialist', 'بیمارستان', 'کلینیک', 'دفتر', 'نمایندگی', 'دکتر'];
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
				'label'		=> esc_html__( 'Title tag', 'drplus' ),
				'default'	=> 'h2',
				'options'	=> Utils::custom_tags()
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		ElementorControls::query_controls( $this, false, [
			'section'	=> [
				'label'	=> esc_html__( 'Hospitals', 'drplus' ),
			],

			'start_excludes'	=> ['post_type'],
			'start_controls'	=> [
				'query_type'	=> [
					'options'	=> [
						'latest'	=> esc_html__( 'Latest', 'drplus' )
					]
				],
			],

			'post_type'	=> 'hospital',
			'category'	=> 'hospital_category',
		] );
		ElementorControls::display_settings( $this, [
			'controls'	=> [
				'desktop_slides'	=> [
					'default'	=> 4,
				],
				'desktop_slides_space'	=> [
					'default'	=> 24,
				],
				'desktop_cols'	=> [
					'default'	=> 4,
				],
				'desktop_gap'	=> [
					'default'	=> 24,
				],
				'tablet_slides'	=> [
					'default'	=> 2,
				],
				'tablet_cols'	=> [
					'default'	=> 2,
				],
				'tablet_gap'	=> [
					'default'	=> 24,
				],
				'mobile_slides'	=> [
					'default'	=> 1,
				],
				'mobile_cols'	=> [
					'default'	=> 1,
				],
				'mobile_gap'	=> [
					'default'	=> 24,
				],
			],
		] );
		$this->seo_controls();
		ElementorControls::pagination_controls( $this );

		ElementorControls::general_style_controls( $this, [ // item_
			'prefix'		=> 'item_',
			'base_selector'	=> '.hospital a',

			'section'	=> [
				'name'		=> 'item_section',
				'label'		=> esc_html__( 'Item style', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // image_
			'prefix'		=> 'image_',
			'base_selector'	=> '.hospital a',
			'selector'		=> '.post-thumbnail img',

			'section'	=> [
				'name'		=> 'image_section',
				'label'		=> esc_html__( 'Image style', 'drplus' ),
			],

			'mode'	=> 'image',
		] );
		ElementorControls::general_style_controls( $this, [ // title_
			'prefix'		=> 'title_',
			'base_selector'	=> '.hospital a',
			'selector'		=> '.hospital-name',

			'section'	=> [
				'name'		=> 'title_section',
				'label'		=> esc_html__( 'Title style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // address_icon_
			'prefix'		=> 'address_icon_',
			'base_selector'	=> '.hospital a',
			'selector'		=> '.hospital-address-icon',

			'section'	=> [
				'name'		=> 'address_icon_section',
				'label'		=> esc_html__( 'Address icon style', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // address_
			'prefix'		=> 'address_',
			'base_selector'	=> '.hospital a',
			'selector'		=> '.hospital-address',

			'section'	=> [
				'name'		=> 'address_section',
				'label'		=> esc_html__( 'Address style', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::pagination_style_controls( $this );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();
		ElementorControls::general_style_controls( $this, [ // item_
			'prefix'		=> 'dark_item_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .hospital a',

			'section'	=> [
				'name'		=> 'dark_item_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Item style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // image_
			'prefix'		=> 'dark_image_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .hospital a',
			'selector'		=> '.post-thumbnail img',

			'section'	=> [
				'name'		=> 'dark_image_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Image style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'image',
		] );
		ElementorControls::general_style_controls( $this, [ // title_
			'prefix'		=> 'dark_title_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .hospital a',
			'selector'		=> '.hospital-name',

			'section'	=> [
				'name'		=> 'dark_title_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Title style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // address_icon_
			'prefix'		=> 'dark_address_icon_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .hospital a',
			'selector'		=> '.hospital-address-icon',

			'section'	=> [
				'name'		=> 'dark_address_icon_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Address icon style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // address_
			'prefix'		=> 'dark_address_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .hospital a',
			'selector'		=> '.hospital-address',

			'section'	=> [
				'name'		=> 'dark_address_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Address style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'icon',
		] );
		ElementorControls::pagination_style_controls( $this, false, true );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$settings['post_type'] = 'hospital';
		$settings['category'] = 'hospital_category';

		$settings['title_tag'] = Sanitizers::tag( $settings['title_tag'] );
		$settings['classes'] = ['hospitals-items-wrap'];
		$settings['list_classes'] = ['hospitals-items'];
		$settings['style'] = '';

		echo Archive::posts( $settings, "templates/hospitals/template-hospitals-single-item", [
			'wrap_classes'	=> ["list-hospitals", 'hospitals-items'],
		] );
	}
}