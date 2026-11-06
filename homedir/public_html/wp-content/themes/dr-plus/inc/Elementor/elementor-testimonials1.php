<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
use DrPlus\Utils;
use DrPlus\Utils\Elementor;

class Testimonials1 extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_testimonials1';
	}

	public function get_title() {
		return esc_html__( 'Testimonials 1 (Doctor Plus)', 'drplus' );
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

		$repeater = new \Elementor\Repeater();

		$repeater->add_control( // img
			'img',
			[
				'label'			=> esc_html__( 'Default image', 'drplus' ),
				'description'	=> esc_html__( 'Size: 48px*48px', 'drplus' ),
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

		$repeater->add_control( // date
			'date',
			[
				'label'			=> esc_html__( 'Date', 'drplus' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> '',
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$repeater->add_control( // specialist
			'specialist',
			[
				'label'			=> esc_html__( 'Specialist name', 'drplus' ),
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

		$repeater->add_control( // specialist_link
			'specialist_link',
			[
				'label'		=> esc_html__( 'Specialist link', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::URL,
				'default'	=> [
					'url'	=> '#'
				],
				'dynamic'	=> [
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
						'img'			=> [
							'url'	=> DRPLUS_URI . 'assets/images/user.svg',
						],
						'name'				=> esc_html__( 'Name', 'drplus' ),
						'score'				=> 4.5,
						'date'				=> '28 مرداد 1403',
						'specialist'		=> esc_html__( "Specialist name", 'drplus' ),
						'specialist_link'	=> [
							'url'	=> '#',
						],
						'text'				=> esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'drplus' ),
					],
					[
						'img'			=> [
							'url'	=> DRPLUS_URI . 'assets/images/user.svg',
						],
						'name'				=> esc_html__( 'Name', 'drplus' ),
						'score'				=> 4.5,
						'date'				=> '28 مرداد 1403',
						'specialist'		=> esc_html__( "Specialist name", 'drplus' ),
						'specialist_link'	=> [
							'url'	=> '#',
						],
						'text'				=> esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'drplus' ),
					],
					[
						'img'			=> [
							'url'	=> DRPLUS_URI . 'assets/images/user.svg',
						],
						'name'				=> esc_html__( 'Name', 'drplus' ),
						'score'				=> 4.5,
						'date'				=> '28 مرداد 1403',
						'specialist'		=> esc_html__( "Specialist name", 'drplus' ),
						'specialist_link'	=> [
							'url'	=> '#',
						],
						'text'				=> esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'drplus' ),
					],
					[
						'img'			=> [
							'url'	=> DRPLUS_URI . 'assets/images/user.svg',
						],
						'name'				=> esc_html__( 'Name', 'drplus' ),
						'score'				=> 4.5,
						'date'				=> '28 مرداد 1403',
						'specialist'		=> esc_html__( "Specialist name", 'drplus' ),
						'specialist_link'	=> [
							'url'	=> '#',
						],
						'text'				=> esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'drplus' ),
					],
				],
			]
		);

		$this->add_control( // visit_text
			'visit_text',
			[
				'label'			=> esc_html__( 'Visit text', 'drplus' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( 'Visit', 'drplus' ),
				'separator'		=> 'before',
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
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
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->items_settings_controls();
		ElementorControls::section_title_settings( $this );
		ElementorControls::slider_settings_controls( $this );

		ElementorControls::general_style_controls( $this, [ // slider_wrap
			'prefix'		=> 'slider_wrap_',
			'selector'	=> '.testimonials1-slider-wrap',
			
			'section'	=> [
				'name'	=> 'slider_wrap',
				'label'	=> esc_html__( 'General Style', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::section_title_styles( $this );
		ElementorControls::general_style_controls( $this, [ // slider
			'prefix'		=> 'slider_',
			'selector'	=> '.testimonials1-wrapper',
			
			'section'	=> [
				'name'	=> 'slider',
				'label'	=> esc_html__( 'Slider Style', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // avatar
			'prefix'		=> 'item_avatar_',
			'base_selector'	=> '.testimonials1-item',
			'selector'		=> '.testimonials1-item-avatar',
			
			'section'	=> [
				'name'	=> 'item_avatar',
				'label'	=> esc_html__( 'Avatar', 'drplus' ),
			],

			'mode'	=> 'img',
		] );
		ElementorControls::general_style_controls( $this, [ // name
			'prefix'		=> 'item_name_',
			'base_selector'	=> '.testimonials1-item',
			'selector'		=> '.testimonials1-item-name',
			
			'section'	=> [
				'name'	=> 'item_name',
				'label'	=> esc_html__( 'Name', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // date
			'prefix'		=> 'item_date_',
			'base_selector'	=> '.testimonials1-item',
			'selector'		=> '.testimonials1-item-date',
			
			'section'	=> [
				'name'	=> 'item_date',
				'label'	=> esc_html__( 'Date', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // score icon
			'prefix'		=> 'item_score_icon_',
			'base_selector'	=> '.testimonials1-item',
			'selector'		=> '.testimonials1-item-score-icon',
			
			'section'	=> [
				'name'	=> 'item_score_icon',
				'label'	=> esc_html__( 'Score icon', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // score text
			'prefix'		=> 'item_score_',
			'base_selector'	=> '.testimonials1-item',
			'selector'		=> '.testimonials1-item-score',
			
			'section'	=> [
				'name'	=> 'item_score',
				'label'	=> esc_html__( 'Score text', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // content
			'prefix'		=> 'item_content_',
			'base_selector'	=> '.testimonials1-item',
			'selector'		=> '.testimonials1-item-content p',
			
			'section'	=> [
				'name'	=> 'item_content',
				'label'	=> esc_html__( 'Content text', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // visit text
			'prefix'		=> 'item_visit_',
			'base_selector'	=> '.testimonials1-item',
			'selector'		=> '.testimonials1-item-visit-text',
			
			'section'	=> [
				'name'	=> 'item_visit',
				'label'	=> esc_html__( 'Visit text', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // specialist name
			'prefix'		=> 'item_specialist_',
			'base_selector'	=> '.testimonials1-item',
			'selector'		=> '.testimonials1-item-specialist',
			
			'section'	=> [
				'name'	=> 'item_specialist',
				'label'	=> esc_html__( 'Specialist Name', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [ // slider_wrap
			'prefix' 	=> 'dark_slider_wrap_',
			'selector' 	=> 'html[data-theme="dark"] {{WRAPPER}} .testimonials1-slider-wrap',
			
			'section' 	=> [
				'name' 			=> 'dark_slider_wrap',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'General Style', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,
			'mode' 		=> 'wrap',
		] );
		ElementorControls::section_title_styles( $this, false, true, true );
		ElementorControls::general_style_controls( $this, [ // slider
			'prefix' 	=> 'dark_slider_',
			'selector' 	=> 'html[data-theme="dark"] {{WRAPPER}} .testimonials1-wrapper',
			
			'section' 	=> [
				'name' 			=> 'dark_slider',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Slider Style', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,
			'mode' 		=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // avatar
			'prefix' 		=> 'dark_item_avatar_',
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .testimonials1-item',
			'selector' 		=> '.testimonials1-item-avatar',
			
			'section' 	=> [
				'name'		 	=> 'dark_item_avatar',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Avatar', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,
			'mode' 		=> 'img',
		] );
		ElementorControls::general_style_controls( $this, [ // name
			'prefix' 		=> 'dark_item_name_',
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .testimonials1-item',
			'selector' 		=> '.testimonials1-item-name',
			
			'section' 	=> [
				'name' 			=> 'dark_item_name',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Name', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,
			'mode'	 	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // date
			'prefix' 		=> 'dark_item_date_',
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .testimonials1-item',
			'selector' 		=> '.testimonials1-item-date',
			
			'section' 	=> [
				'name' 			=> 'dark_item_date',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Date', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,
			'mode' 		=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // score icon
			'prefix' 		=> 'dark_item_score_icon_',
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .testimonials1-item',
			'selector' 		=> '.testimonials1-item-score-icon',
			
			'section' 	=> [
				'name' 			=> 'dark_item_score_icon',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Score icon', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,
			'mode' 		=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // score text
			'prefix' 		=> 'dark_item_score_',
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .testimonials1-item',
			'selector' 		=> '.testimonials1-item-score',
			
			'section' 	=> [
				'name' 			=> 'dark_item_score',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Score text', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,
			'mode' 		=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // content
			'prefix' 		=> 'dark_item_content_',
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .testimonials1-item',
			'selector' 		=> '.testimonials1-item-content p',
			
			'section' 	=> [
				'name' 			=> 'dark_item_content',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Content text', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,
			'mode' 		=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // visit text
			'prefix' 		=> 'dark_item_visit_',
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .testimonials1-item',
			'selector' 		=> '.testimonials1-item-visit-text',
			
			'section' 	=> [
				'name' 			=> 'dark_item_visit',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Visit text', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,
			'mode' 		=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // specialist name
			'prefix' 		=> 'dark_item_specialist_',
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .testimonials1-item',
			'selector' 		=> '.testimonials1-item-specialist',
			
			'section' 	=> [
				'name' 			=> 'dark_item_specialist',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Specialist Name', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	 => $dark_excludes,
			'mode' 		=> 'text',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		get_template_part( 'templates/components/template-components-testimonials1', null, $settings );
	}
}