<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
use DrPlus\Utils;
use DrPlus\Utils\Elementor;
use DrPlus\Utils\UtilsSpecialists;
use MJ\Whitebox\ElementorControls\Slider;

class SpecialistsOfflineVisits extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_specialist_offline_visits';
	}

	public function get_title() {
		return esc_html__( 'Specialists - visit at office (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-person';
	}

	public function get_categories() {
		return ['drplus', 'basic'];
	}

	public function get_keywords() {
		return ['counselor', 'doctor', 'advise', 'offline', 'visit', 'consultant', 'مشاور', 'دکتر', 'ویزیت', 'آنلاین', 'ثبت', 'پذیرش'];
	}

	protected function register_controls() {
		ElementorControls::specialists_query_control( $this, 'offline_visits', [
			'controls'	=> [
				'style'	=> [
					'default'	=> 'card-2'
				]
			]
		] );
		ElementorControls::display_settings( $this, [
			'controls'	=> [
				'desktop_slides_space'	=> [
					'default'	=> 16,
				],
				'desktop_cols'	=> [
					'default'	=> 5,
				],
				'desktop_gap'	=> [
					'default'	=> 16,
				],
				'tablet_slides_space'	=> [
					'default'	=> 16,
				],
				'tablet_cols'	=> [
					'default'	=> 2,
				],
				'tablet_gap'	=> [
					'default'	=> 16,
				],
				'mobile_slides_space'	=> [
					'default'	=> 16,
				],
				'mobile_cols'	=> [
					'default'	=> 1,
				],
				'mobile_gap'	=> [
					'default'	=> 16,
				],
			],
		] );
		Slider::options_controls( $this, [
			'controls'	=> [
				'show_arrows'		=> [
					'default'	=> 'no'
				],
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
		ElementorControls::specialists_seo_control( $this );
		ElementorControls::pagination_controls( $this, [
			'controls'	=> [
				'ppp'		=> [
					'label'	=> esc_html__( "Specialists per page", 'drplus' ),
				],
				'offset'	=> [
					'description'	=> esc_html__( 'The offset causes the first few results to be skipped and provides the number of specialists from that point onward.', 'drplus' ),
				],
			],
		] );
		ElementorControls::section_title_settings( $this, [
			'controls'	=> [
				'title'	=> [
					'default'	=> esc_html__( "in-person visit", 'drplus' )
				],
				'icon'	=> [
					'default'	=> [
						'value'		=> 'drplus-icon-heart-smile',
						'library'	=> 'drplus-icon',
					],
				],
				'link'	=> [
					'default'	=> [
						'url'	=> home_url( "/specialists?specialist-type=in-person" ),
					]
				],
			],
		] );
		$show_button_condition = [
			'button_show_button'	=> 'yes'
		];
		ElementorControls::button_settings( $this, [
			'section'	=> [
				'label'	=> esc_html__( 'Show all button', 'drplus' )
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
				],
				'text'	=> [
					'default'	=> esc_html__( 'Show all', 'drplus' ),
					'condition'	=> $show_button_condition,
				],
				'link'	=> [
					'condition'	=> $show_button_condition,
					'default'	=> [
						'url'	=> home_url( "/specialists?specialist-type=in-person" )
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
			],
		] );

		ElementorControls::section_title_styles( $this );

		// Card styles
		ElementorControls::specialists_card_style_control( $this, 'offline_visit' );

		ElementorControls::pagination_style_controls( $this );

		// Button styles
		ElementorControls::general_style_controls( $this, [ // button_wrap_
			'prefix'		=> 'button_wrap_',
			'base_selector'	=> '.specialist-read-more-btn',
			
			'section'	=> [
				'name'	=> 'button_wrap_',
				'label'	=> esc_html__( 'Show all button', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // button_icon_
			'prefix'		=> 'button_icon_',
			'base_selector'	=> '.specialist-read-more-btn',
			'selector'		=> '.button-icon',
			
			'section'	=> [
				'name'	=> 'button_icon_',
				'label'	=> esc_html__( 'Show all button icon', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // button_text_
			'prefix'		=> 'button_text_',
			'base_selector'	=> '.specialist-read-more-btn',
			'selector'		=> '.button-text',
			
			'section'	=> [
				'name'	=> 'button_text_',
				'label'	=> esc_html__( 'Show all button text', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'	=> 'slider_arrows_',
			'selector'	=> '.drplus-slider-nav-btn',
			
			'section'	=> [
				'name'		=> 'slider_arrows',
				'label'		=> esc_html__( 'Slider arrows style', 'mj-whitebox' ),
				'condition'	=> [
					'show_arrows'	=> 'yes'
				],
			],

			'mode'	=> 'icon',
		] );

		// Dark mode controls
		ElementorControls::dark_mode_toggle_controls( $this );

		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::section_title_styles( $this, false, true, true );

		// Card styles
		ElementorControls::specialists_card_style_control( $this, 'offline_visit', true );

		ElementorControls::pagination_style_controls( $this, false, true );

		ElementorControls::general_style_controls( $this, [ // dark_button_wrap_
			'prefix'			=> 'dark_button_wrap_',
			'base_selector' 	=> 'html[data-theme="dark"] {{WRAPPER}} .specialist-read-more-btn',

			'section' => [
				'name' 		=> 'dark_button_wrap_',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Show all button', 'drplus' ) ),
				'condition' => $dark_condition,
			],

			'excludes'	 => $dark_excludes,
			'hover_excludes'	 => $dark_excludes,
			'mode'		 => 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [ // dark_button_icon_
			'prefix' 		=> 'dark_button_icon_',
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .specialist-read-more-btn',
			'selector'		=> '.button-icon',

			'section' => [
				'name'		 => 'dark_button_icon_',
				'label' 	=> ElementorControls::dark_control_label( esc_html__( 'Show all button icon', 'drplus' ) ),
				'condition' => $dark_condition,
			],

			'excludes' => $dark_excludes,
			'hover_excludes'	 => $dark_excludes,
			'mode' => 'icon',
		] );

		ElementorControls::general_style_controls( $this, [ // dark_button_text_
			'prefix' 		=> 'dark_button_text_',
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .specialist-read-more-btn',
			'selector' 		=> '.button-text',

			'section' => [
				'name' 		=> 'dark_button_text_',
				'label' 	=> ElementorControls::dark_control_label( esc_html__( 'Show all button text', 'drplus' ) ),
				'condition' => $dark_condition,
			],

			'excludes' => $dark_excludes,
			'hover_excludes'	 => $dark_excludes,
			'mode' => 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'	=> 'dark_slider_arrows_',
			'selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-slider-nav-btn',
			
			'section'	=> [
				'name'		=> 'dark_slider_arrows',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Slider arrows style', 'mj-whitebox' ) ),
				'condition'	=> [
					'show_arrows'	=> 'yes',
					'enable_dark_mode' => 'yes'
				],
			],

			'excludes' => $dark_excludes,
			'hover_excludes'	 => $dark_excludes,
			'mode'	=> 'icon',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$settings['only_offline_visits'] = true;
		
		$display_attributes = Elementor::get_display_attributes( $settings );

		$attributes = [
			'class'	=> array_merge( [
				'drplus-slider-wrap',
				'specialists-slider-wrap',
			], $display_attributes['wrap_classes'] ),
			'data-settings'	=> $display_attributes['args'],
			'style'			=> $display_attributes['style'],
		];
		?>
		<div <?php echo Utils::get_html_attributes( $attributes ) ?>>
			<?php if( !empty( $settings['section_title_title'] ) ) { ?>
				<div class="drplus-slider-head specialists-slider-head">
					<?php
					get_template_part( "templates/components/template-components-section_title", null, [
						'icon'		=> $settings['section_title_icon'],
						'tag'		=> $settings['section_title_tag'],
						'title'		=> $settings['section_title_title'],
						'subtitle'	=> $settings['section_title_subtitle'],
						'link'		=> $settings['section_title_link'],
						'nav_btns'	=> Utils::to_bool( $settings['show_arrows'] ),
						'classes'	=> ['specialists-section-title'],
					] );
					if( Utils::to_bool( $settings['button_show_button'] ) ) {
						$button_args = Elementor::get_button_args( $settings );
						$button_args['prefix'] = 'button_';
						$button_args['button_classes'][] = 'specialist-read-more-btn';
						get_template_part( "templates/components/template-components-button", null, $button_args );
					}
					?>
				</div>
			<?php } ?>
			<?php UtilsSpecialists::list( $settings, 'offline_visits' ) ?>
		</div>
		<?php
	}
}