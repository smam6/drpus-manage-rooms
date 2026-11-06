<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
use DrPlus\Utils;
use MJ\Whitebox\ElementorControls\Slider;

class Testimonials3 extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_testimonials3';
	}

	public function get_title() {
		return esc_html__( 'Testimonials 3 (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-testimonial';
	}

	public function get_categories() {
		return ['drplus', 'basic'];
	}

	public function get_keywords() {
		return ['testimonials', 'نظرات مشتریان', 'نظر', 'مشتری'];
	}

	private function items_settings_controls() {
		$this->start_controls_section( // content_section
			'items_settings_section',
			[
				'label'	=> esc_html__( 'Items', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // show_score_stars
			'show_score_stars',
			[
				'label'			=> esc_html__( 'Show score stars', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->add_control( // show_score_number
			'show_score_number',
			[
				'label'			=> esc_html__( 'Show score number', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->add_control( // score icon
			'score_icon',
			[
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Score icon', 'drplus' ),
				'skin'			=> 'inline',
				'label_block'	=> false,
				'default'		=> [
					'value'		=> 'drplus-icon-star-fill',
					'library'	=> 'drplus-icon'
				],
				'condition'		=> [
					'show_score_stars'	=> 'yes'
				]
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control( // img
			'img',
			[
				'label'			=> esc_html__( 'Default image', 'drplus' ),
				'description'	=> esc_html__( 'Size: 96px*96px', 'drplus' ),
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
				'default'		=> esc_html__( 'Name', 'drplus' ),
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
				'default'		=> esc_html__( 'Customer', 'drplus' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$repeater->add_control( // text
			'text',
			[
				'label'			=> esc_html__( 'Text', 'drplus' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::WYSIWYG,
				'default'		=> esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'drplus' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$repeater->add_control( // score
			'score',
			[
				'label'			=> esc_html__( 'Score', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::NUMBER,
				'default'		=> '4.5',
				'step'			=> '0.1',
				'min'			=> 1,
				'max'			=> 5,
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
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
						'name'		=> esc_html__( 'Name', 'drplus' ),
						'subtitle'	=> esc_html__( 'Customer', 'drplus' ),
						'text'		=> esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'drplus' ),
						'score'		=> 4
					],
					[
						'img'		=> [
							'url'	=> DRPLUS_URI . 'assets/images/user.svg',
						],
						'name'		=> esc_html__( 'Name', 'drplus' ),
						'subtitle'	=> esc_html__( 'Customer', 'drplus' ),
						'text'		=> esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'drplus' ),
						'score'		=> 4
					],
					[
						'img'		=> [
							'url'	=> DRPLUS_URI . 'assets/images/user.svg',
						],
						'name'		=> esc_html__( 'Name', 'drplus' ),
						'subtitle'	=> esc_html__( 'Customer', 'drplus' ),
						'text'		=> esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'drplus' ),
						'score'		=> 4
					],
					[
						'img'		=> [
							'url'	=> DRPLUS_URI . 'assets/images/user.svg',
						],
						'name'		=> esc_html__( 'Name', 'drplus' ),
						'subtitle'	=> esc_html__( 'Customer', 'drplus' ),
						'text'		=> esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'drplus' ),
						'score'		=> 4
					],
				],
			]
		);
		$this->end_controls_section();
	}

	protected function register_controls() {
		ElementorControls::section_title_settings( $this );
		$this->items_settings_controls();
		ElementorControls::display_settings( $this, [
			'controls'	=> [
				'desktop_cols'	=> [
					'default'	=> 3,
				],
				'tablet_cols'	=> [
					'default'	=> 1,
				],
				'mobile_cols'	=> [
					'default'	=> 1,
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
		ElementorControls::pagination_controls( $this, [
			'controls'	=> [
				'ppp'				=> [
					'default'	=> 12
				],
				'show_pagination'	=> [
					'default'	=> 'yes'
				]
			],
		] );

		ElementorControls::section_title_styles( $this, true, true );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'wrap_',
			'base_selector'	=> '.drplus-testimonials3',
			
			'section'	=> [
				'name'	=> 'wrap_section',
				'label'	=> esc_html__( 'Container', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'item_',
			'base_selector'	=> '.testimonials3-item',
			
			'section'	=> [
				'name'	=> 'item_section',
				'label'	=> esc_html__( 'Item', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'item_img_',
			'base_selector'	=> '.testimonials3-item .testimonials3-item-avatar',
			
			'section'	=> [
				'name'	=> 'item_img',
				'label'	=> esc_html__( 'Avatar', 'drplus' ),
			],

			'mode'	=> 'img',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'item_name_',
			'base_selector'	=> '.testimonials3-item-name',
			
			'section'	=> [
				'name'	=> 'item_name',
				'label'	=> esc_html__( 'Name', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'item_subtitle_',
			'base_selector'	=> '.testimonials3-item-subtitle',
			
			'section'	=> [
				'name'	=> 'item_subtitle',
				'label'	=> esc_html__( 'Subtitle', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'item_score_stars_',
			'base_selector'	=> '.testimonials3-item-score-icon',
			
			'section'	=> [
				'name'	=> 'item_score_stars',
				'label'	=> esc_html__( 'Star icon', 'drplus' ),
				'condition'	=> [
					'show_score_stars'	=> 'yes'
				]
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'item_score_numbers_',
			'base_selector'	=> '.testimonials3-item-score-number',
			
			'section'	=> [
				'name'	=> 'item_score_numbers',
				'label'	=> esc_html__( 'Score number', 'drplus' ),
				'condition'	=> [
					'show_score_number'	=> 'yes'
				]
			],

			'controls'	=> [
				'item_score_numbers_line'	=> [
					'type'		=> \Elementor\Controls_Manager::COLOR,
					'label'		=> esc_html__( 'Line color', 'drplus' ),
					'selectors'	=> [
						"{{WRAPPER}} .testimonials3-item-score-stars:not(:last-child)::after"	=> 'background-color: {{VALUE}};',
					],
					'_position'	=> 20
				],
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'item_content_',
			'base_selector'	=> '.testimonials3-item-content',
			
			'section'	=> [
				'name'	=> 'item_content',
				'label'	=> esc_html__( 'Text', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'item_quote_',
			'base_selector'	=> '.testimonials3-item-quote-icon',
			
			'section'	=> [
				'name'	=> 'item_quote',
				'label'	=> esc_html__( 'Quote icon', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::section_title_styles( $this, true, true, true );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_wrap_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-testimonials3',
			
			'section'	=> [
				'name'		=> 'dark_wrap_section',
				'label'		=> esc_html__( 'Container', 'drplus' ),
				'condition' => $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_item_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .testimonials3-item',
			
			'section'	=> [
				'name'		=> 'dark_item_section',
				'label'		=> esc_html__( 'Item', 'drplus' ),
				'condition' => $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_item_img_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .testimonials3-item .testimonials3-item-avatar',
			
			'section'	=> [
				'name'		=> 'dark_item_img',
				'label'		=> esc_html__( 'Avatar', 'drplus' ),
				'condition' => $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,

			'mode'	=> 'img',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_item_name_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .testimonials3-item-name',
			
			'section'	=> [
				'name'		=> 'dark_item_name',
				'label'		=> esc_html__( 'Name', 'drplus' ),
				'condition' => $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_item_subtitle_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .testimonials3-item-subtitle',
			
			'section'	=> [
				'name'		=> 'dark_item_subtitle',
				'label'		=> esc_html__( 'Subtitle', 'drplus' ),
				'condition' => $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_item_score_stars_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .testimonials3-item-score-icon',
			
			'section'	=> [
				'name'	=> 'dark_item_score_stars',
				'label'	=> esc_html__( 'Star icon', 'drplus' ),
				'condition'	=> [
					'show_score_stars'	=> 'yes'
				] + $dark_condition
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_item_score_numbers_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .testimonials3-item-score-number',
			
			'section'	=> [
				'name'	=> 'dark_item_score_numbers',
				'label'	=> esc_html__( 'Score number', 'drplus' ),
				'condition'	=> [
					'show_score_number'	=> 'yes'
				] + $dark_condition
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,

			'controls'	=> [
				'dark_item_score_numbers_line'	=> [
					'type'		=> \Elementor\Controls_Manager::COLOR,
					'label'		=> esc_html__( 'Line color', 'drplus' ),
					'options'	=> [
						'block'		=> esc_html__( 'Show', 'drplus' ),
						'none'		=> esc_html__( 'Hide', 'drplus' ),
					],
					'selectors'	=> [
						'html[data-theme="dark"] {{WRAPPER}} .testimonials3-item-score-stars:not(:last-child)::after'	=> 'background-color: {{VALUE}};',
					],
					'_position'	=> 20
				],
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_item_content_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .testimonials3-item-content',
			
			'section'	=> [
				'name'		=> 'dark_item_content',
				'label'		=> esc_html__( 'Text', 'drplus' ),
				'condition' => $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_item_quote_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .testimonials3-item-quote-icon',
			
			'section'	=> [
				'name'		=> 'dark_item_quote',
				'label'		=> esc_html__( 'Quote icon', 'drplus' ),
				'condition' => $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,

			'mode'	=> 'icon',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		get_template_part( 'templates/components/template-components-testimonials3', null, $settings );
	}
}