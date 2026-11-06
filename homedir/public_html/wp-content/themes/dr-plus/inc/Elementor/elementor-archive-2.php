<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
use DrPlus\Utils\Archive as UtilsArchive;
use MJ\Whitebox\ElementorControls\Slider;

class Archive2 extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_archive_2';
	}

	public function get_title() {
		return esc_html__( 'Archive 2 (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-gallery-justified';
	}

	public function get_categories() {
		return ['drplus', 'basic'];
	}

	public function get_keywords() {
		return ['blog', 'post', 'پست', 'نوشته', 'بلاگ', 'وبلاگ'];
	}

	protected function register_controls() {
		ElementorControls::query_controls( $this );

		ElementorControls::section_title_settings( $this );

		$show_button_condition = [
			'button_show_button'	=> 'yes',
			'section_title_title!'	=> '',
		];
		ElementorControls::button_settings( $this, [
			'section'	=> [
				'label'		=> esc_html__( 'Show all button', 'drplus' ),
				'condition'	=> [
					'section_title_title!'	=> '',
				],
			],
			'controls'	=> [
				'show_button'	=> [
					'label'			=> esc_html__( "Show button", 'drplus' ),
					'type'			=> \Elementor\Controls_Manager::SWITCHER,
					'label_on'		=> esc_html__( 'Yes', 'drplus' ),
					'label_off'		=> esc_html__( 'No', 'drplus' ),
					'return_value'	=> 'yes',
					'default'		=> 'yes',
					'_position'		=> 0,
					'condition'		=> [
						'section_title_title!'	=> '',
					],
				],
				'text'	=> [
					'default'	=> esc_html__( 'Show all', 'drplus' ),
					'condition'	=> $show_button_condition,
				],
				'link'	=> [
					'condition'	=> $show_button_condition,
					'default'	=> [
						'url'	=> home_url( "/blog" )
					]
				],
				'new_tab'	=> [
					'condition'	=> $show_button_condition,
				],
				'transparent'	=> [
					'condition'	=> $show_button_condition,
				],
				'type'	=> [
					'condition'	=> array_merge( $show_button_condition, ['button_transparent!' => 'yes'] ),
					'default'	=> 'gray',
				],
				'small'	=> [
					'condition'	=> $show_button_condition,
					'default'	=> 'yes'
				],
				'icon'	=> [
					'condition'	=> $show_button_condition,
					'default'	=> [
						'value'		=> is_rtl() ? 'drplus-icon-arrow-square-left' : 'drplus-icon-arrow-square-right',
						'library'	=> 'drplus-icon',
					],
				],
				'icon_align'	=> [
					'condition'	=> $show_button_condition,
					'default'	=> 'end',
				],
				'style'	=> [
					'condition'	=> $show_button_condition,
				],
				'align'	=> [
					'condition'	=> $show_button_condition,
					'default'	=> 'end'
				],
				'fullwidth'	=> [
					'condition'	=> $show_button_condition,
				],
			],
		] );
		ElementorControls::display_settings( $this, [
			'controls'	=> [
				'desktop_cols'	=> [
					'default'	=> 3,
				],
				'desktop_gap'	=> [
					'default'	=> 20
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

		ElementorControls::section_title_styles( $this, true );
		// Button styles
		ElementorControls::general_style_controls( $this, [ // button_wrap_
			'prefix'		=> 'button_wrap_',
			'base_selector'	=> '.posts-more-btn',
			
			'section'	=> [
				'name'	=> 'button_wrap_',
				'label'	=> esc_html__( 'Show all button', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // button_icon_
			'prefix'		=> 'button_icon_',
			'base_selector'	=> '.posts-more-btn',
			'selector'		=> '.button-icon',
			
			'section'	=> [
				'name'	=> 'button_icon_',
				'label'	=> esc_html__( 'Show all button icon', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // button_text_
			'prefix'		=> 'button_text_',
			'base_selector'	=> '.posts-more-btn',
			'selector'		=> '.button-text',
			
			'section'	=> [
				'name'	=> 'button_text_',
				'label'	=> esc_html__( 'Show all button text', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // post
			'prefix'		=> 'post_',
			'base_selector'	=> 'article > a',
			
			'section'	=> [
				'name'	=> 'post_section',
				'label'	=> esc_html__( 'Post style', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [ // post_img
			'prefix'		=> 'post_img_',
			'base_selector'	=> 'article > a',
			'selector'		=> '.post-thumbnail img',
			
			'section'	=> [
				'name'	=> 'post_img_section',
				'label'	=> esc_html__( 'Post image style', 'drplus' ),
			],

			'mode'	=> 'image',
		] );

		ElementorControls::general_style_controls( $this, [ // post_category_
			'prefix'		=> 'post_category_',
			'base_selector'	=> 'article > a',
			'selector'		=> '.post-category',
			
			'section'	=> [
				'name'	=> 'post_category_section',
				'label'	=> esc_html__( 'Post category', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // post_time_
			'prefix'		=> 'post_time_',
			'base_selector'	=> 'article > a',
			'selector'		=> '.post-time',
			
			'section'	=> [
				'name'	=> 'post_time_section',
				'label'	=> esc_html__( 'Post time', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // post_title_
			'prefix'		=> 'post_title_',
			'base_selector'	=> 'article > a',
			'selector'		=> '.post-title',
			
			'section'	=> [
				'name'	=> 'post_title_section',
				'label'	=> esc_html__( 'Post title', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // read_more_wrap
			'prefix'		=> 'read_more_wrap_',
			'base_selector'	=> 'article > a',
			'selector'		=> '.read-more-btn',
			
			'section'	=> [
				'name'	=> 'read_more_wrap_section',
				'label'	=> esc_html__( 'Read more button style', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // read_more_text
			'prefix'		=> 'read_more_text_',
			'base_selector'	=> 'article > a',
			'selector'		=> '.read-more-btn-text',
			
			'section'	=> [
				'name'	=> 'read_more_text_section',
				'label'	=> esc_html__( 'Read more button text style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // read_more_icon
			'prefix'		=> 'read_more_icon_',
			'base_selector'	=> 'article > a',
			'selector'		=> '.read-more-btn-icon',
			
			'section'	=> [
				'name'	=> 'read_more_icon_section',
				'label'	=> esc_html__( 'Read more button icon style', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );

		ElementorControls::pagination_style_controls( $this );

		ElementorControls::dark_mode_toggle_controls( $this );

		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::section_title_styles( $this, true, true, true );
		ElementorControls::general_style_controls( $this, [ // dark_button_wrap_
			'prefix'		=> 'dark_button_wrap_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .posts-more-btn',
			
			'section'	=> [
				'name'		=> 'dark_button_wrap_',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Show all button', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_button_icon_
			'prefix'		=> 'dark_button_icon_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .posts-more-btn',
			'selector'		=> '.button-icon',
			
			'section'	=> [
				'name'		=> 'dark_button_icon_',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Show all button icon', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'icon',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_button_text_
			'prefix'		=> 'dark_button_text_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .posts-more-btn',
			'selector'		=> '.button-text',
			
			'section'	=> [
				'name'		=> 'dark_button_text_',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Show all button text', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_post
			'prefix'		=> 'dark_post_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} article > a',
			
			'section'	=> [
				'name'		=> 'dark_post_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Post style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_post_img
			'prefix'		=> 'dark_post_img_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} article > a',
			'selector'		=> '.post-thumbnail img',
			
			'section'	=> [
				'name'		=> 'dark_post_img_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Post image style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'image',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_post_category
			'prefix'		=> 'dark_post_category_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} article',
			'selector'		=> '.post-category',
			'hover_selector'=> 'html[data-theme="dark"] {{WRAPPER}} article:hover .post-category',
			
			'section'	=> [
				'name'		=> 'dark_post_category_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Post category', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_post_time
			'prefix'		=> 'dark_post_time_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} article',
			'selector'		=> '.post-time',
			'hover_selector'=> 'html[data-theme="dark"] {{WRAPPER}} article:hover .post-time',
			
			'section'	=> [
				'name'		=> 'dark_post_time_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Post time', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_post_title
			'prefix'		=> 'dark_post_title_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} article',
			'selector'		=> '.post-title',
			'hover_selector'=> 'html[data-theme="dark"] {{WRAPPER}} article:hover .post-title',
			
			'section'	=> [
				'name'		=> 'dark_post_title_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Post title', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_read_more_wrap
			'prefix'		=> 'dark_read_more_wrap_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} article > a',
			'selector'		=> '.read-more-btn',
			
			'section'	=> [
				'name'		=> 'dark_read_more_wrap_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Read more button style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_read_more_text
			'prefix'		=> 'dark_read_more_text_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} article > a',
			'selector'		=> '.read-more-btn-text',
			
			'section'	=> [
				'name'		=> 'dark_read_more_text_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Read more button text style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_read_more_icon
			'prefix'		=> 'dark_read_more_icon_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} article > a',
			'selector'		=> '.read-more-btn-icon',
			
			'section'	=> [
				'name'		=> 'dark_read_more_icon_section',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Read more button icon style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'icon',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::pagination_style_controls( $this, false, true );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$settings['style'] = 'style-2';
		echo UtilsArchive::posts( $settings );
	}
}
