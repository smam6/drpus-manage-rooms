<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
use DrPlus\Utils;

class Testimonials2 extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_testimonials2';
	}

	public function get_title() {
		return esc_html__( 'Testimonials 2 (Doctor Plus)', 'drplus' );
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

		$repeater->add_control( // position
			'position',
			[
				'label'			=> esc_html__( 'Position', 'drplus' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( 'Specialist', 'drplus' ),
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
						'position'	=> esc_html__( 'Specialist', 'drplus' ),
						'text'		=> esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'drplus' ),
					],
					[
						'img'		=> [
							'url'	=> DRPLUS_URI . 'assets/images/user.svg',
						],
						'name'		=> esc_html__( 'Name', 'drplus' ),
						'position'	=> esc_html__( 'Specialist', 'drplus' ),
						'text'		=> esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'drplus' ),
					],
					[
						'img'		=> [
							'url'	=> DRPLUS_URI . 'assets/images/user.svg',
						],
						'name'		=> esc_html__( 'Name', 'drplus' ),
						'position'	=> esc_html__( 'Specialist', 'drplus' ),
						'text'		=> esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'drplus' ),
					],
					[
						'img'		=> [
							'url'	=> DRPLUS_URI . 'assets/images/user.svg',
						],
						'name'		=> esc_html__( 'Name', 'drplus' ),
						'position'	=> esc_html__( 'Specialist', 'drplus' ),
						'text'		=> esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'drplus' ),
					],
				],
			]
		);
		$this->end_controls_section();
	}

	private function slider_settings_controls() {
		$this->start_controls_section( // content_section
			'slider_settings_section',
			[
				'label'	=> esc_html__( 'Slider', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		ElementorControls::autoplay_controls( $this );
		
		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->items_settings_controls();
		$this->slider_settings_controls();

		ElementorControls::general_style_controls( $this, [ // general_wrap
			'prefix'		=> 'general_wrap_',
			'selector'	=> '.testimonials2-wrap',
			
			'section'	=> [
				'name'	=> 'general_wrap',
				'label'	=> esc_html__( 'General Style', 'drplus' ),
			],

			'excludes'	=> [
				'background',
			],
			
			'controls'	=> [
				'custom_background'	=> [
					'type'		=> \Elementor\Controls_Manager::COLOR,
					'label'		=> esc_html__( 'Background color', 'drplus' ),
					'selectors'	=> [
						'{{WRAPPER}} .testimonials2-wrap'	=> 'background-color: {{VALUE}}; --ts2-bg-color: {{VALUE}};',
					],
				],
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // slider_main
			'prefix'		=> 'slider_main_',
			'selector'	=> '.testimonials2-slider-main',
			
			'section'	=> [
				'name'	=> 'slider_main',
				'label'	=> esc_html__( 'Main Slider Style', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // slider_thumb
			'prefix'		=> 'slider_thumb_',
			'selector'	=> '.testimonials2-slider-thumb',
			
			'section'	=> [
				'name'	=> 'slider_thumb',
				'label'	=> esc_html__( 'Thumb Slider Style', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // slider_thumb_active
			'prefix'		=> 'slider_thumb_active_',
			'selector'	=> '.testimonials2-slider-thumb.swiper-slide-active',
			
			'section'	=> [
				'name'	=> 'slider_thumb_active',
				'label'	=> esc_html__( 'Active Thumb Slider Style', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // avatar
			'prefix'		=> 'item_avatar_',
			'base_selector'	=> '.testimonials2-slider-thumb',
			'selector'		=> 'img.testimonials2-item-avatar',
			
			'section'	=> [
				'name'	=> 'item_avatar',
				'label'	=> esc_html__( 'Avatar', 'drplus' ),
			],

			'mode'	=> 'img',
		] );
		ElementorControls::general_style_controls( $this, [ // name
			'prefix'		=> 'item_name_',
			'base_selector'	=> '.testimonials2-slider-thumb',
			'selector'		=> '.testimonials2-item-name',
			
			'section'	=> [
				'name'	=> 'item_name',
				'label'	=> esc_html__( 'Name', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // active_name
			'prefix'			=> 'item_active_name_',
			'selector'			=> '.swiper-slide-active .testimonials2-item-name',
			'hover_selector'	=> false,
			
			'section'	=> [
				'name'	=> 'item_active_name',
				'label'	=> esc_html__( 'Active Slide Name', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // position
			'prefix'		=> 'item_position_',
			'base_selector'	=> '.testimonials2-slider-thumb',
			'selector'		=> '.testimonials2-item-position',
			
			'section'	=> [
				'name'	=> 'item_position',
				'label'	=> esc_html__( 'Position', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // active_position
			'prefix'			=> 'item_active_position_',
			'selector'			=> '.swiper-slide-active .testimonials2-item-position',
			'hover_selector'	=> false,
			
			'section'	=> [
				'name'	=> 'item_active_position',
				'label'	=> esc_html__( 'Active Slide Position', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // content
			'prefix'		=> 'item_content_',
			'base_selector'	=> '.testimonials2-slider-main',
			'selector'		=> '.testimonials2-item-content',
			
			'section'	=> [
				'name'	=> 'item_content',
				'label'	=> esc_html__( 'Content text', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // quote icon
			'prefix'		=> 'item_qute_icon_',
			'base_selector'	=> '.testimonials2-slider-main',
			'selector'		=> '.testimonials2-quote-icon',
			
			'section'	=> [
				'name'	=> 'item_qute_icon',
				'label'	=> esc_html__( 'Quote icon', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [ // general_wrap
			'prefix' 	=> 'dark_general_wrap_',
			'selector' 	=> 'html[data-theme="dark"] {{WRAPPER}} .testimonials2-wrap',
			
			'section' 	=> [
				'name' 			=> 'dark_general_wrap',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'General Style', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes'	=> [
				'background',
			] + $dark_excludes,
			'hover_excludes'	=> [
				'background',
			] + $dark_excludes,
			
			'controls'	=> [
				'dark_custom_background'	=> [
					'type'		=> \Elementor\Controls_Manager::COLOR,
					'label'		=> esc_html__( 'Background color', 'drplus' ),
					'selectors'	=> [
						'html[data-theme="dark"] {{WRAPPER}} .testimonials2-wrap'	=> 'background-color: {{VALUE}}; --ts2-bg-color: {{VALUE}};',
					],
				],
			],

			'mode' 	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // slider_main
			'prefix' 	=> 'dark_slider_main_',
			'selector' 	=> 'html[data-theme="dark"] {{WRAPPER}} .testimonials2-slider-main',
			
			'section' 	=> [
				'name' 			=> 'dark_slider_main',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Main Slider Style', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,
			'mode' 	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // slider_thumb
			'prefix' 	=> 'dark_slider_thumb_',
			'selector' 	=> 'html[data-theme="dark"] {{WRAPPER}} .testimonials2-slider-thumb',
			
			'section' 	=> [
				'name' 			=> 'dark_slider_thumb',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Thumb Slider Style', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,
			'mode' 		=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // slider_thumb_active
			'prefix' 	=> 'dark_slider_thumb_active_',
			'selector' 	=> 'html[data-theme="dark"] {{WRAPPER}} .testimonials2-slider-thumb.swiper-slide-active',
			
			'section' 	=> [
				'name' 			=> 'dark_slider_thumb_active',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Active Thumb Slider Style', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,
			'mode' 		=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // avatar
			'prefix' 		=> 'dark_item_avatar_',
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .testimonials2-slider-thumb',
			'selector' 		=> 'img.testimonials2-item-avatar',
			
			'section' 	=> [
				'name' 			=> 'dark_item_avatar',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Avatar', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,
			'mode' 		=> 'img',
		] );
		ElementorControls::general_style_controls( $this, [ // name
			'prefix' 		=> 'dark_item_name_',
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .testimonials2-slider-thumb',
			'selector' 		=> '.testimonials2-item-name',
			
			'section' 	=> [
				'name'		 	=> 'dark_item_name',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Name', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,
			'mode' 		=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // active_name
			'prefix' 			=> 'dark_item_active_name_',
			'selector' 			=> 'html[data-theme="dark"] {{WRAPPER}} .swiper-slide-active .testimonials2-item-name',
			'hover_selector' 	=> false,
			
			'section' 	=> [
				'name' 			=> 'dark_item_active_name',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Active Slide Name', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,
			'mode' 	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // position
			'prefix' 		=> 'dark_item_position_',
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .testimonials2-slider-thumb',
			'selector'	 	=> '.testimonials2-item-position',
			
			'section' 	=> [
				'name' 			=> 'dark_item_position',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Position', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,
			'mode' 		=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // active_position
			'prefix' 			=> 'dark_item_active_position_',
			'selector' 			=> 'html[data-theme="dark"] {{WRAPPER}} .swiper-slide-active .testimonials2-item-position',
			'hover_selector' 	=> false,
			
			'section' 	=> [
				'name' 			=> 'dark_item_active_position',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Active Slide Position', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,
			'mode' 		=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // content
			'prefix' 		=> 'dark_item_content_',
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .testimonials2-slider-main',
			'selector' 		=> '.testimonials2-item-content',
			
			'section' 	=> [
				'name' 			=> 'dark_item_content',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Content text', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,
			'mode' 		=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // quote icon
			'prefix' 		=> 'dark_item_qute_icon_',
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .testimonials2-slider-main',
			'selector' 		=> '.testimonials2-quote-icon',
			
			'section' 	=> [
				'name' 			=> 'dark_item_qute_icon',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Quote icon', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,
			'mode' 		=> 'icon',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$settings['autoplay'] = Utils::to_bool( $settings['autoplay'] ) ? $settings['autoplay_time'] : 0;
		
		get_template_part( 'templates/components/template-components-testimonials2', null, $settings );
	}
}