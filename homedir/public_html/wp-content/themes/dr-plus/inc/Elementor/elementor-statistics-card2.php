<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
use MJ\Whitebox\ElementorControls as WhiteboxElementorControls;
use MJ\Whitebox\ElementorControls\Slider;

class StatisticsCard2 extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_statistics_card2';
	}

	public function get_title() {
		return esc_html__( 'Statistics card 2 (Doctor Plus)', 'drplus' );
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

		$repeater->add_control( // icon
			'icon',
			[
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Icon', 'drplus' ),
				'skin'			=> 'inline',
				'label_block'	=> false,
			]
		);

		$repeater->add_control( // text
			'text',
			[
				'type'		=> \Elementor\Controls_Manager::TEXT,
				'label'		=> esc_html__( 'Number', 'drplus' ),
				'description'	=> esc_html__( "To color a portion of text, enclose the text in { and }. Example: {percentage}", 'drplus' ),
				'label_block'	=> true,
				'default'	=> '20{%}',
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'	=> [
					'active'	=> true,
				],
			]
		);

		$repeater->add_control( // subtitle
			'subtitle',
			[
				'type'		=> \Elementor\Controls_Manager::TEXT,
				'label'		=> esc_html__( 'Subtitle', 'drplus' ),
				'default'	=> esc_html__( "Lorem", 'drplus' ),
				'label_block'	=> true,
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'	=> [
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
						'icon'		=> [
							'library'	=> 'drplus-icon',
							'value'		=> 'drplus-icon-medal-star'
						],
						'text'		=> sprintf( '{+450} %s', esc_html__( 'Lorem ipsum', 'drplus' ) ),
						'subtitle'	=> esc_html__( 'Lorem ipsum', 'drplus' ),
					],
				],
				'title_field' => '{{{ text }}}',
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->items_controls();
		WhiteboxElementorControls::display_settings( $this, [
			'controls'	=> [
				'desktop_slider'	=> [
					'default'	=> 'no',
				],
				'desktop_cols'	=> [
					'default'	=> 4
				],
				'desktop_column_gap'	=> [
					'default'	=> 80,
				],
				'tablet_slider'	=> [
					'default'	=> 'yes',
				],
				'tablet_slides'	=> [
					'default'	=> 2,
				],
				'tablet_slides_space'	=> [
					'default'	=> 24,
				],
				'mobile_slider'	=> [
					'default'	=> 'yes',
				],
				'mobile_slides'	=> [
					'default'	=> 1,
				],
				'mobile_slides_space'	=> [
					'default'	=> 24,
				],
			],
		] );
		Slider::options_controls( $this, [
			'controls'	=> [
				'prev_arrow_icon' => [
					'_position'		=> 20,
					'label'			=> esc_html__( 'Prev arrow icon', 'drplus' ),
					'type'			=> \Elementor\Controls_Manager::ICONS,
					'skin'			=> 'inline',
					'label_block'	=> false,
					'default'		=> [
						'library'	=> 'drplus-icon',
						'value'		=> !is_rtl() ? 'drplus-icon-left' : 'drplus-icon-right'
					],
					'condition'		=> [
						'show_arrows'	=> 'yes'
					]
				],
				'next_arrow_icon' => [
					'_position'		=> 20,
					'label'			=> esc_html__( 'Next arrow icon', 'drplus' ),
					'type'			=> \Elementor\Controls_Manager::ICONS,
					'skin'			=> 'inline',
					'label_block'	=> false,
					'default'		=> [
						'library'	=> 'drplus-icon',
						'value'		=> is_rtl() ? 'drplus-icon-left' : 'drplus-icon-right'
					],
					'condition'		=> [
						'show_arrows'	=> 'yes'
					]
				]
			],
			'excludes'	=> ['show_dots']
		], true );

		ElementorControls::general_style_controls( $this, [ // card_
			'prefix'		=> 'card_',
			'selector'		=> '.statistics-card2',
			
			'section'	=> [
				'name'	=> 'card_section',
				'label'	=> esc_html__( 'Card style', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // icon_wrap_
			'prefix'		=> 'icon_wrap_',
			'base_selector'	=> '.statistics-card2-item',
			'selector'		=> '.statistics-card2-item-icon-wrap',
			
			'section'	=> [
				'name'	=> 'icon_wrap_section',
				'label'	=> esc_html__( 'Icon wrap style', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // icon_
			'prefix'		=> 'icon_',
			'base_selector'	=> '.statistics-card2-item',
			'selector'		=> '.statistics-card2-item-icon',
			
			'section'	=> [
				'name'	=> 'icon_section',
				'label'	=> esc_html__( 'Icon style', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // text_wrap_
			'prefix'		=> 'text_wrap_',
			'base_selector'	=> '.statistics-card2-item',
			'selector'		=> '.statistics-card2-item-texts',
			
			'section'	=> [
				'name'	=> 'text_wrap_section',
				'label'	=> esc_html__( 'Texts wrap style', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // text_
			'prefix'		=> 'text_',
			'base_selector'	=> '.statistics-card2-item',
			'selector'		=> '.statistics-card2-item-text',
			
			'section'	=> [
				'name'	=> 'text_section',
				'label'	=> esc_html__( 'Text style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // text_symbol_
			'prefix'		=> 'text_symbol_',
			'base_selector'	=> '.statistics-card2-item',
			'selector'		=> '.statistics-card2-item-text span',
			
			'section'	=> [
				'name'	=> 'text_symbol_section',
				'label'	=> esc_html__( 'Text symbol style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // subtitle_
			'prefix'		=> 'subtitle_',
			'base_selector'	=> '.statistics-card2-item',
			'selector'		=> '.statistics-card2-item-subtitle',
			
			'section'	=> [
				'name'	=> 'subtitle_section',
				'label'	=> esc_html__( 'Subtitle style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [ // slider_arrow
			'prefix' 	=> 'slider_arrow_',
			'selector' 	=> '.drplus-slider-nav-btn',
			
			'section' 	=> [
				'name' 			=> 'slider_arrow',
				'label' 		=> esc_html__( 'Slider arrows style', 'drplus' ),
			],

			'mode' 	=> 'icon',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();
		ElementorControls::general_style_controls( $this, [ // dark_card_
			'prefix'		=> 'dark_card_',
			'selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .statistics-card2',
			
			'section'	=> [
				'name'		=> 'dark_card_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Card style', 'drplus' ) ),
				'condition' => $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,
			
			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // icon_wrap_dark_
			'prefix'		=> 'icon_wrap_dark_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .statistics-card2-item',
			'selector'		=> '.statistics-card2-item-icon-wrap',
			
			'section'	=> [
				'name'		=> 'icon_wrap_dark_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Icon wrap style', 'drplus' ) ),
				'condition' => $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // icon_dark_
			'prefix'		=> 'icon_dark_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .statistics-card2-item',
			'selector'		=> '.statistics-card2-item-icon',
			
			'section'	=> [
				'name'		=> 'icon_dark_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Icon style', 'drplus' ) ),
				'condition' => $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // text_wrap_dark_
			'prefix'		=> 'text_wrap_dark_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .statistics-card2-item',
			'selector'		=> '.statistics-card2-item-texts',
			
			'section'	=> [
				'name'		=> 'text_wrap_dark_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Texts wrap style', 'drplus' ) ),
				'condition' => $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // text_dark_
			'prefix'		=> 'text_dark_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .statistics-card2-item',
			'selector'		=> '.statistics-card2-item-text',
			
			'section'	=> [
				'name'		=> 'text_dark_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Text style', 'drplus' ) ),
				'condition' => $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // text_symbol_dark_
			'prefix'		=> 'text_symbol_dark_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .statistics-card2-item',
			'selector'		=> '.statistics-card2-item-text span',
			
			'section'	=> [
				'name'		=> 'text_symbol_dark_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Text symbol style', 'drplus' ) ),
				'condition' => $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // subtitle_dark_
			'prefix'		=> 'subtitle_dark_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .statistics-card2-item',
			'selector'		=> '.statistics-card2-item-subtitle',
			
			'section'	=> [
				'name'		=> 'subtitle_dark_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Subtitle style', 'drplus' ) ),
				'condition' => $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [ // slider_arrow
			'prefix' 	=> 'dark_slider_arrow_',
			'selector' 	=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-slider-nav-btn',
			
			'section' 	=> [
				'name' 			=> 'dark_slider_arrow',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Slider arrows style', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,
			'mode' 	=> 'icon',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		get_template_part( "templates/components/template-components-statistics-card2", null, $settings );
	}
}