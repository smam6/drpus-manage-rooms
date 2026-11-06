<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;

class StatisticsCard extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_statistics_card';
	}

	public function get_title() {
		return esc_html__( 'Statistics card (Doctor Plus)', 'drplus' );
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

		$repeater->add_control( // icon_type
			'icon_type',
			[
				'label'		=> esc_html__( 'Icon type', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::CHOOSE,
				'options'	=> [
					'image'	=> [
						'title'	=> esc_html__( 'Image', 'drplus' ),
						'icon'	=> 'eicon-image',
					],
					'icon'	=> [
						'title'	=> esc_html__( 'Icon', 'drplus' ),
						'icon'	=> 'eicon-posts-ticker',
					],
				],
				'default'	=> 'image',
				'toggle'	=> false,
			]
		);

		$repeater->add_control( // img
			'img',
			[
				'label'			=> esc_html__( 'Image', 'drplus' ),
				'description'	=> esc_html__( 'Size: 72px*72px', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::MEDIA,
				'default'		=> [
					'url'	=> \Elementor\Utils::get_placeholder_image_src(),
				],
				'condition'		=> [
					'icon_type'	=> 'image'
				],
			]
		);

		$repeater->add_control( // icon
			'icon',
			[
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Icon', 'drplus' ),
				'skin'			=> 'inline',
				'label_block'	=> false,
				'condition'		=> [
					'icon_type'	=> 'icon'
				],
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

		$this->add_control( // show_line_dots
			'show_line_dots',
			[
				'label'			=> esc_html__( 'Show line dots', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
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
						'text'		=> '20{%}',
						'subtitle'	=> esc_html__( 'Lorem ipsum', 'drplus' ),
					],
				],
				'title_field' => '{{{ text }}}',
			]
		);

		$this->end_controls_section();
	}

	private function card_hover_style() {
		$this->start_controls_section( // content_section
			'card_hover_style_section',
			[
				'label'	=> esc_html__( 'Card hover', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name'		=> 'card_hover_bg',
				'label'		=> esc_html__( "Card hover", 'drplus' ),
				'selector'	=> "{{WRAPPER}} .statistics-card-item-inner::after",
			]
		);

		$this->end_controls_section();
	}

	private function card_hover_dark_style() {
		$this->start_controls_section( // content_section
			'card_hover_dark_style_section',
			[
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Card hover', 'drplus' ) ),
				'tab'		=> \Elementor\Controls_Manager::TAB_STYLE,
				'condition'	=> [
					'enable_dark_mode' => 'yes'
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name'		=> 'card_hover_dark_bg',
				'label'		=> esc_html__( "Card hover", 'drplus' ),
				'selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .statistics-card-item-inner::after',
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->items_controls();
		ElementorControls::display_settings( $this, [
			'controls'	=> [
				'desktop_cols'	=> [
					'default'	=> 1,
				],
				'desktop_gap'	=> [
					'default'	=> 20,
				],
				'tablet_cols'	=> [
					'default'	=> 1,
				],
				'tablet_gap'	=> [
					'default'	=> 20,
				],
				'mobile_cols'	=> [
					'default'	=> 1,
				],
				'mobile_gap'	=> [
					'default'	=> 20,
				],
			],
		] );

		ElementorControls::general_style_controls( $this, [ // card_
			'prefix'		=> 'card_',
			'base_selector'	=> '.statistics-card-item',
			'selector'		=> '.statistics-card-item-inner',
			
			'section'	=> [
				'name'	=> 'card_section',
				'label'	=> esc_html__( 'Card style', 'drplus' ),
			],

			'controls'	=> [
				'line_dot_width'	=> [
					'type'		=> \Elementor\Controls_Manager::SLIDER,
					'label'			=> esc_html__( 'Line dot Width', 'drplus' ),
					'size_units'	=> [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
					'range' => [
						'px' => [
							'max' => 1000,
						],
					],
					'selectors'		=> [
						'{{WRAPPER}} .statistics-card-wrap'	=> '--wrap-line-width: {{SIZE}}{{UNIT}};',
					],
					'condition' => [
						'show_line_dots'	=> 'yes'
					]
				],
				'line_dot_color'	=> [
					'type'		=> \Elementor\Controls_Manager::COLOR,
					'label'			=> esc_html__( 'Line dot Color', 'drplus' ),
					'selectors'		=> [
						'{{WRAPPER}} .drplus-line-dot'	=> 'color: {{VALUE}};',
					],
					'condition' => [
						'show_line_dots'	=> 'yes'
					]
				],
			],

			'mode'	=> 'wrap',
		] );
		$this->card_hover_style();
		ElementorControls::general_style_controls( $this, [ // icon_wrap_
			'prefix'		=> 'icon_wrap_',
			'base_selector'	=> '.statistics-card-item',
			'selector'		=> '.statistics-card-item-icon-wrap',
			
			'section'	=> [
				'name'	=> 'icon_wrap_section',
				'label'	=> esc_html__( 'Icon wrap style', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // icon_
			'prefix'		=> 'icon_',
			'base_selector'	=> '.statistics-card-item',
			'selector'		=> '.statistics-card-item-icon',
			
			'section'	=> [
				'name'	=> 'icon_section',
				'label'	=> esc_html__( 'Icon style', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // image_
			'prefix'		=> 'image_',
			'base_selector'	=> '.statistics-card-item',
			'selector'		=> '.statistics-card-item-icon-wrap img',
			
			'section'	=> [
				'name'	=> 'image_section',
				'label'	=> esc_html__( 'Image style', 'drplus' ),
			],

			'mode'	=> 'img',
		] );
		ElementorControls::general_style_controls( $this, [ // text_wrap_
			'prefix'		=> 'text_wrap_',
			'base_selector'	=> '.statistics-card-item',
			'selector'		=> '.statistics-card-item-texts',
			
			'section'	=> [
				'name'	=> 'text_wrap_section',
				'label'	=> esc_html__( 'Texts wrap style', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // text_
			'prefix'		=> 'text_',
			'base_selector'	=> '.statistics-card-item',
			'selector'		=> '.statistics-card-item-text',
			
			'section'	=> [
				'name'	=> 'text_section',
				'label'	=> esc_html__( 'Text style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // text_symbol_
			'prefix'		=> 'text_symbol_',
			'base_selector'	=> '.statistics-card-item',
			'selector'		=> '.statistics-card-item-text span',
			
			'section'	=> [
				'name'	=> 'text_symbol_section',
				'label'	=> esc_html__( 'Text symbol style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // subtitle_
			'prefix'		=> 'subtitle_',
			'base_selector'	=> '.statistics-card-item',
			'selector'		=> '.statistics-card-item-subtitle',
			
			'section'	=> [
				'name'	=> 'subtitle_section',
				'label'	=> esc_html__( 'Subtitle style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // arrow_
			'prefix'		=> 'arrow_',
			'base_selector'	=> '.statistics-card-item',
			'selector'		=> '.statistics-card-item-arrow',
			
			'section'	=> [
				'name'	=> 'arrow_section',
				'label'	=> esc_html__( 'Arrow style', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();
		ElementorControls::general_style_controls( $this, [ // dark_card_
			'prefix'		=> 'dark_card_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .statistics-card-item',
			'selector'		=> '.statistics-card-item-inner',
			
			'section'	=> [
				'name'		=> 'dark_card_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Card style', 'drplus' ) ),
				'condition' => $dark_condition,
			],

			'controls'	=> [
				'dark_line_dot_color'	=> [
					'type'		=> \Elementor\Controls_Manager::COLOR,
					'label'			=> esc_html__( 'Line dot Color', 'drplus' ),
					'selectors'		=> [
						'html[data-theme="dark"] {{WRAPPER}} .drplus-line-dot'	=> 'color: {{VALUE}};',
					],
					'condition' => [
						'show_line_dots'	=> 'yes'
					]
				],
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,
			
			'mode'	=> 'wrap',
		] );
		$this->card_hover_dark_style();
		ElementorControls::general_style_controls( $this, [ // icon_wrap_dark_
			'prefix'		=> 'icon_wrap_dark_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .statistics-card-item',
			'selector'		=> '.statistics-card-item-icon-wrap',
			
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
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .statistics-card-item',
			'selector'		=> '.statistics-card-item-icon',
			
			'section'	=> [
				'name'		=> 'icon_dark_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Icon style', 'drplus' ) ),
				'condition' => $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // image_dark_
			'prefix'		=> 'image_dark_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .statistics-card-item',
			'selector'		=> '.statistics-card-item-icon-wrap img',
			
			'section'	=> [
				'name'		=> 'image_dark_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Image style', 'drplus' ) ),
				'condition' => $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'img',
		] );
		ElementorControls::general_style_controls( $this, [ // text_wrap_dark_
			'prefix'		=> 'text_wrap_dark_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .statistics-card-item',
			'selector'		=> '.statistics-card-item-texts',
			
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
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .statistics-card-item',
			'selector'		=> '.statistics-card-item-text',
			
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
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .statistics-card-item',
			'selector'		=> '.statistics-card-item-text span',
			
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
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .statistics-card-item',
			'selector'		=> '.statistics-card-item-subtitle',
			
			'section'	=> [
				'name'		=> 'subtitle_dark_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Subtitle style', 'drplus' ) ),
				'condition' => $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // arrow_dark_
			'prefix'		=> 'arrow_dark_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .statistics-card-item',
			'selector'		=> '.statistics-card-item-arrow',
			
			'section'	=> [
				'name'		=> 'arrow_dark_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Arrow style', 'drplus' ) ),
				'condition' => $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,

			'mode'	=> 'icon',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		get_template_part( "templates/components/template-components-statistics-card", null, $settings );
	}
}