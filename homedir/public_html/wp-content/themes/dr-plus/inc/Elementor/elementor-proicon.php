<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
use DrPlus\Utils;

class ProIcon extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_pro_icon';
	}

	public function get_title() {
		return esc_html__( 'Pro Icon (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-toggle';
	}

	public function get_categories() {
		return ['drplus', 'basic'];
	}

	public function get_keywords() {
		return ['icon', 'image', 'آیکون', 'آیکن', 'تصویر', 'عکس'];
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Settings', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // content_style
			"content_style",
			[
				'type'			=> \Elementor\Controls_Manager::SELECT,
				'label'			=> esc_html__( 'Style', 'drplus' ),
				'options'		=> [
					'style-1'	=> esc_html__( 'Style 1', 'drplus' ),
					'style-2'	=> esc_html__( 'Style 2', 'drplus' ),
				],
				'default'		=> 'style-1',
			]
		);

		$this->add_control( // icon_type
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
				'default'	=> 'icon',
				'toggle'	=> false,
			]
		);

		$this->add_control( // img
			'img',
			[
				'label'			=> esc_html__( 'Image', 'drplus' ),
				'description'	=> esc_html__( 'Size: 40px*40px', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::MEDIA,
				'default'		=> [
					'url'	=> \Elementor\Utils::get_placeholder_image_src(),
				],
				'condition'		=> [
					'icon_type'	=> 'image'
				],
			]
		);

		$this->add_control( // icon
			'icon',
			[
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Icon', 'drplus' ),
				'skin'			=> 'inline',
				'label_block'	=> false,
				'default'	=> [
					'value'		=> 'drplus-icon-grid-fill',
					'library'	=> 'drplus-icon',
				],
				'condition'		=> [
					'icon_type'	=> 'icon'
				],
			]
		);

		$this->add_control( // icon_align
			'icon_align',
			[
				'label'		=> esc_html__( 'Icon align', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::CHOOSE,
				'options'	=> [
					'top'	=> [
						'title'	=> esc_html__( 'Top', 'drplus' ),
						'icon'	=> 'eicon-justify-start-v',
					],
					'center'	=> [
						'title'	=> esc_html__( 'Center', 'drplus' ),
						'icon'	=> 'eicon-justify-center-v',
					],
					'bottom'	=> [
						'title'	=> esc_html__( 'Bottom', 'drplus' ),
						'icon'	=> 'eicon-justify-end-v',
					],
				],
				'default'	=> 'center',
				'toggle'	=> false,
				'condition'	=> [
					'icon_type'	=> 'icon'
				]
			]
		);

		$this->add_control( // title
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

		$this->add_control( // tag
			'tag',
			[
				'type'			=> \Elementor\Controls_Manager::SELECT,
				'label'			=> esc_html__( 'Title tag', 'drplus' ),
				'label_block'	=> true,
				'default'		=> 'div',
				'options'		=> Utils::custom_tags(),
			]
		);

		$this->add_control( // subtitle
			'subtitle',
			[
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label'			=> esc_html__( 'Subtitle', 'drplus' ),
				'label_block'	=> true,
				'default'		=> __( 'Subtitle', 'drplus' ),
				'description'	=> esc_html__( "To color a portion of text, enclose the text in { and }. Example: {percentage}", 'drplus' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // link
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

		$this->add_control( // show_btn
			'show_btn',
			[
				'label'			=> esc_html__( 'Show button', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->add_control( // hover_type
			'hover_type',
			[
				'label'		=> esc_html__( 'Hover type', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'fill',
				'options'	=> [
					'fill'		=> esc_html_x( 'Fill', 'Proicon hover type','drplus' ),
					'bordered'	=> esc_html_x( 'Bordered', 'Proicon hover type','drplus' ),
				],
			]
		);

		$this->add_control( // show_bg_icon
			'show_bg_icon',
			[
				'label'		=> esc_html__( 'Show icon in background', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> false,
			]
		);

		$this->add_control( // bg_icon
			'bg_icon',
			[
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Background Icon', 'drplus' ),
				'skin'			=> 'inline',
				'label_block'	=> false,
				'default'	=> [
					'value'		=> 'drplus-icon-dr-plus-1',
					'library'	=> 'drplus-icon',
				],
				'condition'		=> [
					'show_bg_icon'	=> 'yes'
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();

		ElementorControls::general_style_controls( $this, [ // wrap
			'prefix'		=> 'wrap_',
			'selector'		=> '.proicon',
			
			'section'	=> [
				'name'	=> 'wrap_section',
				'label'	=> esc_html__( 'Wrap style', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // icon_wrap
			'prefix'		=> 'icon_wrap_',
			'base_selector'	=> '.proicon',
			'selector'		=> '.proicon-img-wrap',
			
			'section'	=> [
				'name'	=> 'icon_wrap_section',
				'label'	=> esc_html__( 'Icon wrap style', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [ // image
			'prefix'		=> 'image_',
			'base_selector'	=> '.proicon',
			'selector'		=> '.proicon-img-wrap img',
			
			'section'	=> [
				'name'	=> 'image_section',
				'label'	=> esc_html__( 'Image style', 'drplus' ),
				'condition'	=> [
					'icon_type'	=> 'image'
				],
			],

			'mode'	=> 'img',
		] );

		ElementorControls::general_style_controls( $this, [ // icon
			'prefix'		=> 'icon_',
			'base_selector'	=> '.proicon',
			'selector'		=> '.proicon-icon',
			
			'section'	=> [
				'name'	=> 'icon_section',
				'label'	=> esc_html__( 'Icon style', 'drplus' ),
				'condition'	=> [
					'icon_type'	=> 'icon'
				],
			],

			'mode'	=> 'icon',
		] );

		ElementorControls::text_style_controls( $this, '.proicon-title', 'title_', __( "Title style", 'drplus' ), "{{WRAPPER}} .proicon:hover .proicon-title" );
		ElementorControls::text_style_controls( $this, '.proicon-subtitle', 'subtitle_', __( "Subtitle style", 'drplus' ), "{{WRAPPER}} .proicon:hover .proicon-subtitle" );

		ElementorControls::general_style_controls( $this, [ // button
			'prefix'		=> 'button_',
			'base_selector'	=> '.proicon',
			'selector'		=> '.proicon-btn',
			
			'section'	=> [
				'name'	=> 'button_section',
				'label'	=> esc_html__( 'Button style', 'drplus' ),
				'condition'	=> [
					'show_btn'	=> 'yes'
				],
			],

			'mode'	=> 'icon',
		] );

		ElementorControls::general_style_controls( $this, [ // bg_icon
			'prefix'		=> 'bg_icon_',
			'base_selector'	=> '.proicon',
			'selector'		=> '.proicon-bg-icon',
			
			'section'	=> [
				'name'	=> 'bg_icon_section',
				'label'	=> esc_html__( 'Background icon style', 'drplus' ),
				'condition'	=> [
					'show_bg_icon'	=> 'yes'
				],
			],

			'mode'	=> 'icon',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [ // wrap
			'prefix' 	=> 'dark_wrap_',
			'selector' 	=> 'html[data-theme="dark"] {{WRAPPER}} .proicon',
			
			'section' 	=> [
				'name' 			=> 'dark_wrap_section',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Wrap style', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' => $dark_excludes,
			'mode' 		=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // icon_wrap
			'prefix' 		=> 'dark_icon_wrap_',
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .proicon',
			'selector' 		=> '.proicon-img-wrap',
			
			'section' 	=> [
				'name' 			=> 'dark_icon_wrap_section',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Icon wrap style', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' => $dark_excludes,
			'mode' 		=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [ // image
			'prefix' 		=> 'dark_image_',
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .proicon',
			'selector'	 	=> '.proicon-img-wrap img',
			
			'section' 	=> [
				'name' 			=> 'dark_image_section',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Image style', 'drplus' ) ),
				'condition' 	=> array_merge( [ 'icon_type' => 'image' ], $dark_condition ),
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' => $dark_excludes,
			'mode' 		=> 'img',
		] );

		ElementorControls::general_style_controls( $this, [ // icon
			'prefix' 		=> 'dark_icon_',
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .proicon',
			'selector' 		=> '.proicon-icon',
			
			'section' 	=> [
				'name' 			=> 'dark_icon_section',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Icon style', 'drplus' ) ),
				'condition' 	=> array_merge( [ 'icon_type' => 'icon' ], $dark_condition ),
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' => $dark_excludes,
			'mode' 		=> 'icon',
		] );

		ElementorControls::general_style_controls( $this, [ // button
			'prefix' 		=> 'dark_button_',
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .proicon',
			'selector' 		=> '.proicon-btn',
			
			'section' 	=> [
				'name' 			=> 'dark_button_section',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Button style', 'drplus' ) ),
				'condition' 	=> array_merge( [ 'show_btn' => 'yes' ], $dark_condition ),
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' => $dark_excludes,
			'mode' 		=> 'icon',
		] );

		ElementorControls::general_style_controls( $this, [ // title_
			'prefix' 		=> 'dark_title_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .proicon',
			'selector' 		=> '.proicon-title',
			
			'section' 	=> [
				'name' 			=> 'dark_title_section',
				'label' 		=> ElementorControls::dark_control_label( __( "Title style", 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' => $dark_excludes,
			'mode' 		=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [ // subtitle_
			'prefix' 		=> 'dark_subtitle_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .proicon',
			'selector'	 	=> '.proicon-subtitle',
			
			'section' 	=> [
				'name' 			=> 'dark_subtitle_section',
				'label' 		=> ElementorControls::dark_control_label( __( "Subtitle style", 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' => $dark_excludes,
			'mode'	 	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [ // dark_bg_icon
			'prefix'		=> 'dark_bg_icon_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .proicon',
			'selector'		=> '.proicon-bg-icon',
			
			'section'	=> [
				'name'	=> 'dark_bg_icon_section',
				'label'	=> ElementorControls::dark_control_label( __( 'Background icon style', 'drplus' ) ),
				'condition'	=> [
					'show_bg_icon'	=> 'yes'
				]+$dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' => $dark_excludes,
			'mode'		=> 'icon',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		get_template_part( "templates/components/template-components-proicon", null, $settings );
	}
}