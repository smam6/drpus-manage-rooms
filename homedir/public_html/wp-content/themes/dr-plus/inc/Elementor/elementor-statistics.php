<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
use DrPlus\Utils;

class Statistics extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_statistics';
	}

	public function get_title() {
		return esc_html__( 'Statistics (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-number-field';
	}

	public function get_categories() {
		return ['drplus', 'basic'];
	}

	public function get_keywords() {
		return ['statistic', 'number', 'text', 'title', 'subtitle', 'آمار', 'عدد', 'عنوان', 'زیرعنوان'];
	}

	private function items_controls() {
		$this->start_controls_section( // content_section
			'items_section',
			[
				'label'	=> esc_html__( 'Items', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control( // number
			'number',
			[
				'type'		=> \Elementor\Controls_Manager::TEXT,
				'label'		=> esc_html__( 'Number', 'drplus' ),
				'default'	=> '20',
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'	=> [
					'active'	=> true,
				],
			]
		);

		$repeater->add_control( // title
			'title',
			[
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label'			=> esc_html__( 'Title', 'drplus' ),
				'label_block'	=> true,
				'default'		=> __( 'Title', 'drplus' ),
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

		$repeater->add_control( // subtitle
			'subtitle',
			[
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label'			=> esc_html__( 'Subtitle', 'drplus' ),
				'label_block'	=> true,
				'default'		=> __( 'Subtitle', 'drplus' ),
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

		$repeater->add_control( // link
			'link',
			[
				'label'		=> esc_html__( 'Link', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::URL,
				'default'	=> [
					'url'	=> '#'
				],
				'dynamic'	=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // items
			'items',
			[
				'label'		=> esc_html__( 'Items', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::REPEATER,
				'fields'	=> $repeater->get_controls(),
				'default'	=> [
					[
						'number'	=> '20',
						'title'		=> esc_html__( 'Lorem ipsum dollar', 'drplus' ),
						'subtitle'	=> esc_html__( 'Lorem ipsum', 'drplus' ),
					],
				],
				'title_field' => '{{{ title }}}',
			]
		);

		$this->end_controls_section();
	}

	private function seo_controls() {
		$this->start_controls_section( // content_section
			'seo_section',
			[
				'label'	=> esc_html__( 'SEO Settings', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // title_tag
			'title_tag',
			[
				'label'		=> esc_html__( 'Title tag', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'div',
				'options'	=> Utils::custom_tags(),
			]
		);

		$this->add_control( // subtitle_tag
			'subtitle_tag',
			[
				'label'		=> esc_html__( 'Subtitle tag', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'div',
				'options'	=> Utils::custom_tags(),
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->items_controls();
		$this->seo_controls();
		ElementorControls::display_settings( $this, [
			'controls'	=> [
				'desktop_cols'	=> [
					'default'	=> 3,
				],
				'desktop_gap'	=> [
					'default'	=> 32,
				],
				'tablet_slider'	=> [
					'default'	=> 'no'
				],
				'tablet_cols'	=> [
					'default'	=> 1,
				],
				'tablet_gap'	=> [
					'default'	=> 12,
				],
				'mobile_slider'	=> [
					'default'	=> 'no'
				],
				'mobile_cols'	=> [
					'default'	=> 1,
				],
				'mobile_gap'	=> [
					'default'	=> 12,
				],
			],
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'item_',
			'base_selector'	=> '.statistic',
			
			'section'	=> [
				'name'	=> 'item_section',
				'label'	=> esc_html__( 'Item style', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'number_',
			'base_selector'	=> '.statistic',
			'selector'		=> '.statistic-number',
			
			'section'	=> [
				'name'	=> 'number_section',
				'label'	=> esc_html__( 'Number style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'title_',
			'base_selector'	=> '.statistic',
			'selector'		=> '.statistic-title',
			
			'section'	=> [
				'name'	=> 'title_section',
				'label'	=> esc_html__( 'Title style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'subtitle_',
			'base_selector'	=> '.statistic',
			'selector'		=> '.statistic-subtitle',
			
			'section'	=> [
				'name'	=> 'subtitle_section',
				'label'	=> esc_html__( 'Subtitle style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [ // item_
			'prefix' 		=> 'dark_item_',
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .statistic',
			
			'section' 	=> [
				'name' 			=> 'dark_item_section',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Item style', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,
			'mode' 		=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // number_
			'prefix' 		=> 'dark_number_',
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .statistic',
			'selector'	 	=> '.statistic-number',
			
			'section' 	=> [
				'name' 			=> 'dark_number_section',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Number style', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,
			'mode' 		=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // title_
			'prefix' 		=> 'dark_title_',
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .statistic',
			'selector' 		=> '.statistic-title',
			
			'section' 	=> [
				'name' 		=> 'dark_title_section',
				'label' 	=> ElementorControls::dark_control_label( esc_html__( 'Title style', 'drplus' ) ),
				'condition' => $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,
			'mode' 		=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // subtitle_
			'prefix' 		=> 'dark_subtitle_',
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .statistic',
			'selector' 		=> '.statistic-subtitle',
			
			'section' 	=> [
				'name' 		=> 'dark_subtitle_section',
				'label' 	=> ElementorControls::dark_control_label( esc_html__( 'Subtitle style', 'drplus' ) ),
				'condition' => $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,
			'mode' 		=> 'text',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		get_template_part( "templates/components/template-components-statistics", null, $settings );
	}
}