<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
use MJ\Whitebox\ElementorControls\Slider;
use MJ\Whitebox\Utils;

class SpecialistSlider extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_specialist_slider';
	}

	public function get_title() {
		return esc_html__( 'Specialist slider (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-testimonial';
	}

	public function get_categories() {
		return ['drplus', 'basic'];
	}

	public function get_keywords() {
		return ['slider', 'specialist', 'carousel', 'متخصص', 'اسلایدر'];
	}

	private function general_settings_controls() {
		$this->start_controls_section( // content_section
			'general_settings_section',
			[
				'label'	=> esc_html__( 'Settings', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // title
			'title',
			[
				'label'			=> esc_html__( 'Title', 'drplus' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( 'Popular Doctors', 'drplus' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // title_tag
			'title_tag',
			[
				'label'			=> esc_html__( 'Title tag', 'drplus' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::SELECT,
				'default'		=> 'h2',
				'options'		=> Utils::custom_tags()
			]
		);

		$this->add_responsive_control( // item_info_fade_in_duration
			'item_info_fade_in_duration',
			[
				'label'			=> esc_html__( 'Item info fade in duration (s)', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SLIDER,
				'size_units'	=> [],
				'selectors'		=> [
					'{{WRAPPER}} .specialist-slider-item-info'	=> '--transition-duration: {{SIZE}}s;',
				],
				'default'		=> [
					'size'	=> '.5'
				]
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control( // img
			'img',
			[
				'label'			=> esc_html__( 'image', 'drplus' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::MEDIA,
				'default'		=> [
					'url'		=> DRPLUS_URI . 'assets/images/user.svg',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$repeater->add_control( // name
			'name',
			[
				'label'			=> esc_html__( 'Name', 'drplus' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( 'Specialist name', 'drplus' ),
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
				'label'			=> esc_html__( 'Subtitle', 'drplus' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( 'Specialist subtitle', 'drplus' ),
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
				'dynamic'	=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // items
			'items',
			[
				'label'			=> __( "Items", 'drplus' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::REPEATER,
				'fields'		=> $repeater->get_controls(),
				'title_field'	=> '{{{ name }}}',
				'default'		=> [
					[
						'img'		=> [
							'url'	=> DRPLUS_URI . 'assets/images/user.svg',
						],
						'name'		=> esc_html__( 'Specialist  name', 'drplus' ),
						'subtitle'	=> esc_html__( 'Specialist subtitle', 'drplus' ),
						'link'		=>[
							'url'	=> '#'
						]
					],
				],
			]
		);
		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->general_settings_controls();
		Slider::options_controls( $this, [
			'excludes'	=> ['show_dots'],
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
				]
			]
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'slider_wrap_',
			'selector'	=> '.specialist-slider-wrap',
			
			'section'	=> [
				'name'	=> 'slider_wrap',
				'label'	=> esc_html__( 'General Style', 'drplus' ),
			],

			'controls'	=> [
				'background'	=> [
					'selector'	=> "{{WRAPPER}} .specialist-slider-wrap::before",
				],
				'max_width' => [
					'type'	=> \Elementor\Controls_Manager::SLIDER,
					'label'	=> esc_html__( 'Width', 'drplus' ),
					'size_units'	=> [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
					'selectors'	=> [
						"{{WRAPPER}} .specialist-slider-wrap" => 'max-width: {{SIZE}}{{UNIT}}'
					],
					'range' => [
						'px' => [
							'max' => 1000,
						],
					],
					'_responsive'	=> 1
				]
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'slider_title_',
			'selector'	=> '.specialist-slider-title',
			
			'section'	=> [
				'name'	=> 'slider_title',
				'label'	=> esc_html__( 'Slider title', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'slider_item_',
			'selector'	=> '.specialist-slider-item-inner',
			
			'section'	=> [
				'name'	=> 'slider_item',
				'label'	=> esc_html__( 'Slider item', 'drplus' ),
			],

			'excludes'	=> ['background'],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'slider_img_',
			'selector'	=> '.specialist-slider-item-avatar',
			
			'section'	=> [
				'name'	=> 'slider_img',
				'label'	=> esc_html__( 'Slider item image', 'drplus' ),
			],

			'controls'	=> [
				'custom_width' => [
					'type'	=> \Elementor\Controls_Manager::SLIDER,
					'label'	=> esc_html__( 'Width', 'drplus' ),
					'size_units'	=> [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
					'selectors'	=> [
						"{{WRAPPER}} .specialist-slider-item-avatar" => 'width: {{SIZE}}{{UNIT}}'
					],
					'range' => [
						'px' => [
							'max' => 1000,
						],
					],
					'_responsive'	=> 1
				],
			],

			'mode'	=> 'img',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'slider_info_',
			'selector'	=> '.specialist-slider-item-info',
			
			'section'	=> [
				'name'	=> 'slider_info',
				'label'	=> esc_html__( 'Slider item info container', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'slider_name_',
			'selector'	=> '.specialist-slider-item-name',
			
			'section'	=> [
				'name'	=> 'slider_name',
				'label'	=> esc_html__( 'Slider item name', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'slider_subtitle_',
			'selector'	=> '.specialist-slider-item-subtitle',
			
			'section'	=> [
				'name'	=> 'slider_subtitle',
				'label'	=> esc_html__( 'Slider item subtitle', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'slider_arrow_wrap_',
			'selector'	=> '.specialist-slider-arrows-wrap',
			
			'section'	=> [
				'name'		=> 'slider_arrow_wrap',
				'label'		=> esc_html__( 'Arrows container', 'drplus' ),
				'condition'	=> [
					'show_arrows'	=> 'yes'
				]
			],

			'controls'	=> [
				'custom_width' => [
					'type'	=> \Elementor\Controls_Manager::SLIDER,
					'label'	=> esc_html__( 'Width', 'drplus' ),
					'size_units'	=> [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
					'selectors'	=> [
						"{{WRAPPER}} .specialist-slider-arrows-wrap" => 'width: {{SIZE}}{{UNIT}}'
					],
					'range' => [
						'px' => [
							'max' => 1000,
						],
					],
					'_responsive'	=> 1
				],
				'custom_height' => [
					'type'	=> \Elementor\Controls_Manager::SLIDER,
					'label'	=> esc_html__( 'Height', 'drplus' ),
					'size_units'	=> [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
					'selectors'	=> [
						"{{WRAPPER}} .specialist-slider-arrows-wrap" => 'height: {{SIZE}}{{UNIT}}'
					],
					'range' => [
						'px' => [
							'max' => 1000,
						],
					],
					'_responsive'	=> 1
				],
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'slider_arrows_',
			'selector'	=> '.drplus-slider-nav-btn',
			
			'section'	=> [
				'name'	=> 'slider_arrows',
				'label'	=> esc_html__( 'Slider arrows', 'drplus' ),
				'condition'	=> [
					'show_arrows'	=> 'yes'
				]
			],

			'mode'	=> 'icon',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_slider_wrap_',
			'selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialist-slider-wrap',
			
			'section'	=> [
				'name'	=> 'dark_slider_wrap',
				'label'	=> esc_html__( 'General Style', 'drplus' ),
				'condition' 	=> $dark_condition,
			],

			'controls'	=> [
				'dark_background'	=> [
					'selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialist-slider-wrap::before',
				],
				'dark_max_width' => [
					'type'	=> \Elementor\Controls_Manager::SLIDER,
					'label'	=> esc_html__( 'Width', 'drplus' ),
					'size_units'	=> [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
					'selectors'	=> [
						'html[data-theme="dark"] {{WRAPPER}} .specialist-slider-wrap' => 'max-width: {{SIZE}}{{UNIT}}'
					],
					'range' => [
						'px' => [
							'max' => 1000,
						],
					],
					'_responsive'	=> 1
				]
			],

			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_slider_title_',
			'selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialist-slider-title',
			
			'section'	=> [
				'name'	=> 'dark_slider_title',
				'label'	=> esc_html__( 'Slider title', 'drplus' ),
				'condition' 	=> $dark_condition,
			],

			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_slider_item_',
			'selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialist-slider-item-inner',
			
			'section'	=> [
				'name'	=> 'dark_slider_item',
				'label'	=> esc_html__( 'Slider item', 'drplus' ),
				'condition' 	=> $dark_condition,
			],

			'excludes'			=> ['background']+$dark_excludes,
			'hover_excludes'	=> ['background']+$dark_excludes,

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_slider_img_',
			'selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialist-slider-item-avatar',
			
			'section'	=> [
				'name'	=> 'dark_slider_img',
				'label'	=> esc_html__( 'Slider item image', 'drplus' ),
				'condition' 	=> $dark_condition,
			],

			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'controls'	=> [
				'dark_custom_width' => [
					'type'	=> \Elementor\Controls_Manager::SLIDER,
					'label'	=> esc_html__( 'Width', 'drplus' ),
					'size_units'	=> [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
					'selectors'	=> [
						'html[data-theme="dark"] {{WRAPPER}} .specialist-slider-item-avatar' => 'width: {{SIZE}}{{UNIT}}'
					],
					'range' => [
						'px' => [
							'max' => 1000,
						],
					],
					'_responsive'	=> 1
				],
			],

			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'img',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_slider_info_',
			'selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialist-slider-item-info',
			
			'section'	=> [
				'name'	=> 'dark_slider_info',
				'label'	=> esc_html__( 'Slider item info container', 'drplus' ),
				'condition' 	=> $dark_condition,
			],

			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_slider_name_',
			'selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialist-slider-item-name',
			
			'section'	=> [
				'name'	=> 'dark_slider_name',
				'label'	=> esc_html__( 'Slider item name', 'drplus' ),
				'condition' 	=> $dark_condition,
			],

			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_slider_subtitle_',
			'selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialist-slider-item-subtitle',
			
			'section'	=> [
				'name'	=> 'dark_slider_subtitle',
				'label'	=> esc_html__( 'Slider item subtitle', 'drplus' ),
				'condition' 	=> $dark_condition,
			],

			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_slider_arrow_wrap_',
			'selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .specialist-slider-arrows-wrap',
			
			'section'	=> [
				'name'		=> 'dark_slider_arrow_wrap',
				'label'		=> esc_html__( 'Arrows container', 'drplus' ),
				'condition' 	=> $dark_condition,
				'condition'	=> [
					'show_arrows'	=> 'yes'
				]
			],

			'controls'	=> [
				'dark_custom_width' => [
					'type'	=> \Elementor\Controls_Manager::SLIDER,
					'label'	=> esc_html__( 'Width', 'drplus' ),
					'size_units'	=> [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
					'selectors'	=> [
						'html[data-theme="dark"] {{WRAPPER}} .specialist-slider-arrows-wrap' => 'width: {{SIZE}}{{UNIT}}'
					],
					'range' => [
						'px' => [
							'max' => 1000,
						],
					],
					'_responsive'	=> 1
				],
				'dark_custom_height' => [
					'type'	=> \Elementor\Controls_Manager::SLIDER,
					'label'	=> esc_html__( 'Height', 'drplus' ),
					'size_units'	=> [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
					'selectors'	=> [
						'html[data-theme="dark"] {{WRAPPER}} .specialist-slider-arrows-wrap' => 'height: {{SIZE}}{{UNIT}}'
					],
					'range' => [
						'px' => [
							'max' => 1000,
						],
					],
					'_responsive'	=> 1
				],
			],

			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_slider_arrows_',
			'selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-slider-nav-btn',
			
			'section'	=> [
				'name'	=> 'dark_slider_arrows',
				'label'	=> esc_html__( 'Slider arrows', 'drplus' ),
				'condition' 	=> ['show_arrows'	=> 'yes']+$dark_condition,
			],

			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			
			'mode'	=> 'icon',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		get_template_part( 'templates/components/template-components-specialist-slider', null, $settings );
	}
}