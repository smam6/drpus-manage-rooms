<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
use DrPlus\Utils;

class Menu extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_menu';
	}

	public function get_title() {
		return esc_html__( 'Menu (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-menu-bar';
	}

	public function get_categories() {
		return ['drplus', 'basic'];
	}

	public function get_keywords() {
		return ['text', 'menu', 'منو'];
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Settings', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$menus = wp_get_nav_menus();

		$options = [];

		foreach( $menus as $menu ) {
			$options[$menu->slug] = $menu->name;
		}

		$this->add_control( // menu
			"menu",
			[
				'type'			=> \Elementor\Controls_Manager::SELECT,
				'label'			=> esc_html__( 'Menu', 'drplus' ),
				'options'		=> $options,
				'default'		=> !empty( $options ) ? array_keys( $options )[0] : '',
				'description' => sprintf(
					/* translators: 1: Link opening tag, 2: Link closing tag. */
					esc_html__( 'Go to the %1$sMenus screen%2$s to manage your menus.', 'elementor-pro' ),
					sprintf( '<a href="%s" target="_blank">', admin_url( 'nav-menus.php' ) ),
					'</a>'
				),
			]
		);

		$this->add_control( // menu_style
			"menu_style",
			[
				'type'			=> \Elementor\Controls_Manager::SELECT,
				'label'			=> esc_html__( 'Menu Style', 'drplus' ),
				'options'		=> [
					'style-1'	=> esc_html__( 'Style 1', 'drplus' ),
					'style-2'	=> esc_html__( 'Style 2', 'drplus' ),
				],
				'default'		=> 'style-1',
			]
		);

		$this->add_control(
			'menu-align',
			[
				'label'		=> esc_html__( 'Menu Position', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::CHOOSE,
				'default'	=> is_rtl() ? 'right' : 'left',
				'options'	=> [
					'right'	=> [
						'title'	=> esc_html__( 'Right', 'drplus' ),
						'icon'	=> 'eicon-h-align-left',
					],
					'center'	=> [
						'title'	=> esc_html__( 'Center', 'drplus' ),
						'icon'	=> 'eicon-h-align-center',
					],
					'left'	=> [
						'title'	=> esc_html__( 'Left', 'drplus' ),
						'icon'	=> 'eicon-h-align-right',
					],
				],
			],
		);

		$this->add_control(
			'menu-direction',
			[
				'label'		=> esc_html__( 'Menu direction', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::CHOOSE,
				'default'	=> 'row-direction',
				'options'	=> [
					'col-direction'	=> [
						'title'	=> esc_html__( 'Column', 'drplus' ),
						'icon'	=> 'eicon-arrow-down',
					],
					'col-reverse-direction'	=> [
						'title'	=> esc_html__( 'Column reverse', 'drplus' ),
						'icon'	=> 'eicon-arrow-up',
					],
					'row-direction'	=> [
						'title'	=> esc_html__( 'Row', 'drplus' ),
						'icon'	=> 'eicon-arrow-right',
					],
					'row-reverse-direction'	=> [
						'title'	=> esc_html__( 'Row reverse', 'drplus' ),
						'icon'	=> 'eicon-arrow-left',
					],
				],
			],
		);

		$this->add_control( // show_icons
			'show_icons',
			[
				'label'			=> esc_html__( 'Show icons', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->add_control( // show_subtitles
			'show_subtitles',
			[
				'label'			=> esc_html__( 'Show subtitles', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->add_control( // show_top_arrow
			'show_top_arrow',
			[
				'label'			=> esc_html__( 'Show top arrow', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
				'condition'		=> [
					'menu_style' => 'style-2'
				]
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'menu_item_',
			'selector'		=> '{{WRAPPER}} .drplus-menu-wrap .menu > .menu-item, {{WRAPPER}} .drplus-menu-wrap .menu>ul>li',
			'hover_selector' => '{{WRAPPER}} .drplus-menu-wrap .menu > .menu-item:hover, {{WRAPPER}} .drplus-menu-wrap .menu>ul>li:hover',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'menu_item',
				'label'	=> esc_html__( 'Menu item', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'menu_item_active_',
			'selector'		=> '{{WRAPPER}} .drplus-menu-wrap .menu > .menu-item.current-menu-item',
			'hover_selector' => '{{WRAPPER}} .drplus-menu-wrap .menu > .menu-item.current-menu-item:hover',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'menu_item_active',
				'label'	=> esc_html__( 'Active menu item', 'drplus' ),
			],


			'controls'	=> [
				'arrow_color'	=> [
					'label'	=> esc_html__( 'Top arrow color', 'drplus' ),
					'type'	=> \Elementor\Controls_Manager::COLOR,
					'selectors'	=> [
						'{{WRAPPER}} .drplus-menu-wrap .menu > .menu-item > a, {{WRAPPER}} .drplus-menu-wrap .menu>ul>li>a' => '--arrow-color: {{VALUE}}',
					],
					'condition'	=> [
						'menu_style' => 'style-2',
						'show_top_arrow' => 'yes'
					]
				],
			],

			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'menu_item_title_',
			'selector'		=> '{{WRAPPER}} .drplus-menu-wrap .menu > .menu-item > a .menu-item-title, {{WRAPPER}} .drplus-menu-wrap .menu > ul > li > a .menu-item-title',
			'hover_selector' => '{{WRAPPER}} .drplus-menu-wrap .menu > .menu-item:hover > a .menu-item-title, {{WRAPPER}} .drplus-menu-wrap .menu > ul > li:hover > a .menu-item-title',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'menu_item_title',
				'label'	=> esc_html__( 'Menu item title', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'menu_item_title_active_',
			'selector'		=> '{{WRAPPER}} .drplus-menu-wrap .menu > .menu-item.current-menu-item > a .menu-item-title',
			'hover_selector' => '{{WRAPPER}} .drplus-menu-wrap .menu > .menu-item.current-menu-item:hover > a .menu-item-title',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'menu_item_title_active',
				'label'	=> esc_html__( 'Active menu item title', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'menu_item_icon_',
			'selector'		=> '{{WRAPPER}} .drplus-menu-wrap .menu > .menu-item > a .drplus-simple-icon-wrap, {{WRAPPER}} .drplus-menu-wrap .menu > ul > li > a .drplus-simple-icon-wrap',
			'hover_selector' => '{{WRAPPER}} .drplus-menu-wrap .menu > .menu-item:hover > a .drplus-simple-icon-wrap, {{WRAPPER}} .drplus-menu-wrap .menu > ul > li:hover > a .drplus-simple-icon-wrap',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'menu_item_icon',
				'label'	=> esc_html__( 'Menu item icon', 'drplus' ),
			],

			'excludes'	=> [
				'background',
			],
			'hover_excludes'	=> [
				'background',
				'icon_background'
			],
			'controls'	=> [
				'icon_background'	=> [
					'label'	=> esc_html__( 'Background color', 'drplus' ),
					'type'	=> \Elementor\Controls_Manager::COLOR,
					'selectors'	=> [
						'{{WRAPPER}} .drplus-menu-wrap .menu > .menu-item > a .drplus-simple-icon-wrap::before, {{WRAPPER}} .drplus-menu-wrap .menu > ul > li > a .drplus-simple-icon-wrap::before' => 'background-color: {{VALUE}}',
					],
					'condition'	=> [
						'menu_style' => 'style-1'
					]
				],
			],

			'mode'	=> 'icon',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'menu_item_icon_active_',
			'selector'		=> '{{WRAPPER}} .drplus-menu-wrap .menu > .menu-item.current-menu-item > a .drplus-simple-icon-wrap',
			'hover_selector' => '{{WRAPPER}} .drplus-menu-wrap .menu > .menu-item.current-menu-item:hover > a .drplus-simple-icon-wrap',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'menu_item_icon_active',
				'label'	=> esc_html__( 'Active menu item icon', 'drplus' ),
			],

			'excludes'	=> [
				'background',
			],
			'hover_excludes'	=> [
				'background',
				'icon_background'
			],
			'controls'	=> [
				'icon_background'	=> [
					'label'	=> esc_html__( 'Background color', 'drplus' ),
					'type'	=> \Elementor\Controls_Manager::COLOR,
					'selectors'	=> [
						'{{WRAPPER}} .drplus-menu-wrap .menu > .menu-item.current-menu-item > a .drplus-simple-icon-wrap::before' => 'background-color: {{VALUE}}',
					],
					'condition'	=> [
						'menu_style' => 'style-1'
					]
				],
			],

			'mode'	=> 'icon',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'submenu_wrap_',
			'selector'		=> '{{WRAPPER}} .drplus-menu-wrap ul.sub-menu, {{WRAPPER}} .drplus-menu-wrap ul.children',
			'hover_selector' => false,
			
			'section'	=> [
				'name'	=> 'submenu_wrap',
				'label'	=> esc_html__( 'Sub menu container', 'drplus' ),
			],

			'mode'	=> 'wrapper',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'submenu_item_',
			'selector'		=> '{{WRAPPER}} .drplus-menu-wrap ul.sub-menu .menu-item, {{WRAPPER}} .drplus-menu-wrap ul.children > li a',
			'hover_selector' => '{{WRAPPER}} .drplus-menu-wrap ul.sub-menu .menu-item:hover, {{WRAPPER}} .drplus-menu-wrap ul.children li:hover a',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'submenu_item',
				'label'	=> esc_html__( 'Sub Menu item', 'drplus' ),
			],

			'hover_excludes'	=> [
				'submenu_item_hover_bar'
			],

			'controls'	=> [
				'submenu_item_hover_bar'	=> [
					'_position'	=> 5,
					'label'	=> esc_html__( 'Hover bar color', 'drplus' ),
					'type'	=> \Elementor\Controls_Manager::COLOR,
					'selectors'	=> [
						'{{WRAPPER}} .drplus-menu-wrap ul.sub-menu .menu-item a::after, {{WRAPPER}} .drplus-menu-wrap ul.sub-menu:is(.children) li a::after, {{WRAPPER}} .drplus-menu-wrap ul.children .menu-item a::after, {{WRAPPER}} .drplus-menu-wrap ul.children:is(.children) li a::after' => 'background: {{VALUE}}',
					],
					'condition'	=> [
						'menu_style' => 'style-1'
					]
				],
			],

			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'submenu_item_title_',
			'selector'		=> '{{WRAPPER}} .drplus-menu-wrap ul.sub-menu .menu-item-title, {{WRAPPER}} .drplus-menu-wrap ul.children .menu-item-title',
			'hover_selector' => '{{WRAPPER}} .drplus-menu-wrap ul.sub-menu .menu-item:hover .menu-item-title, {{WRAPPER}} .drplus-menu-wrap ul.children li:hover .menu-item-title',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'submenu_item_title',
				'label'	=> esc_html__( 'Sub Menu item title', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'submenu_item_subtitle_',
			'selector'		=> '{{WRAPPER}} .drplus-menu-wrap ul.sub-menu .menu-item-subtitle, {{WRAPPER}} .drplus-menu-wrap ul.children .menu-item-subtitle',
			'hover_selector' => '{{WRAPPER}} .drplus-menu-wrap ul.sub-menu .menu-item:hover .menu-item-subtitle, {{WRAPPER}} .drplus-menu-wrap ul.children li:hover .menu-item-subtitle',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'submenu_item_subtitle',
				'label'	=> esc_html__( 'Sub Menu item subtitle', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'submenu_item_icon_',
			'selector'		=> '{{WRAPPER}} .drplus-menu-wrap ul.sub-menu .menu-item .drplus-simple-icon-wrap, {{WRAPPER}} .drplus-menu-wrap ul.children .drplus-simple-icon-wrap',
			'hover_selector' => '{{WRAPPER}} .drplus-menu-wrap ul.sub-menu .menu-item .drplus-simple-icon-wrap, {{WRAPPER}} .drplus-menu-wrap ul.children li:hover .drplus-simple-icon-wrap',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'submenu_item_icon',
				'label'	=> esc_html__( 'Sub Menu item icon', 'drplus' ),
			],

			'excludes'	=> [
				'background',
			],
			'hover_excludes'	=> [
				'background',
				'icon_background'
			],
			'controls'	=> [
				'icon_background'	=> [
					'label'	=> esc_html__( 'Background color', 'drplus' ),
					'type'	=> \Elementor\Controls_Manager::COLOR,
					'selectors'	=> [
						'{{WRAPPER}} .drplus-menu-wrap ul.sub-menu .drplus-simple-icon-wrap::before, {{WRAPPER}} .drplus-menu-wrap ul.children .drplus-simple-icon-wrap::before' => 'background-color: {{VALUE}}',
					],
					'condition'	=> [
						'menu_style' => 'style-1'
					]
				],
			],

			'mode'	=> 'icon',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_menu_item_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap .menu > .menu-item,html[data-theme="dark"] {{WRAPPER}}  .drplus-menu-wrap .menu>ul>li',
			'hover_selector' => 'html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap .menu > .menu-item:hover,html[data-theme="dark"] {{WRAPPER}}  .drplus-menu-wrap .menu>ul>li:hover',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_menu_item',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Menu item', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_menu_item_active_',
			'selector'		=> '{{WRAPPER}} .drplus-menu-wrap .menu > .menu-item.current-menu-item',
			'hover_selector' => '{{WRAPPER}} .drplus-menu-wrap .menu > .menu-item.current-menu-item:hover',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'		=> 'dark_menu_item_active',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Active menu item', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],


			'controls'	=> [
				'arrow_color'	=> [
					'label'	=> esc_html__( 'Top arrow color', 'drplus' ),
					'type'	=> \Elementor\Controls_Manager::COLOR,
					'selectors'	=> [
						'{{WRAPPER}} .drplus-menu-wrap .menu > .menu-item > a, {{WRAPPER}} .drplus-menu-wrap .menu>ul>li>a' => '--arrow-color: {{VALUE}}',
					],
					'condition'	=> [
						'menu_style' => 'style-2',
						'show_top_arrow' => 'yes'
					]
				],
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_menu_item_title_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap .menu > .menu-item > a .menu-item-title,html[data-theme="dark"] {{WRAPPER}}  .drplus-menu-wrap .menu > ul > li > a .menu-item-title',
			'hover_selector' => 'html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap .menu > .menu-item:hover > a .menu-item-title,html[data-theme="dark"] {{WRAPPER}}  .drplus-menu-wrap .menu > ul > li:hover > a .menu-item-title',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_menu_item_title',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Menu item title', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_menu_item_title_active_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap .menu > .menu-item.current-menu-item > a .menu-item-title',
			'hover_selector' => 'html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap .menu > .menu-item.current-menu-item:hover > a .menu-item-title',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_menu_item_title_active',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Active menu item title', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_menu_item_icon_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap .menu > .menu-item > a .drplus-simple-icon-wrap,html[data-theme="dark"] {{WRAPPER}}  .drplus-menu-wrap .menu > ul > li > a .drplus-simple-icon-wrap',
			'hover_selector' => 'html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap .menu > .menu-item:hover > a .drplus-simple-icon-wrap,html[data-theme="dark"] {{WRAPPER}}  .drplus-menu-wrap .menu > ul > li:hover > a .drplus-simple-icon-wrap',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_menu_item_icon',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Menu item icon', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> [
				'background',
			] + $dark_excludes,
			'hover_excludes'	=> [
				'background',
				'icon_background'
			] + $dark_excludes,
			'controls'	=> [
				'dark_icon_background'	=> [
					'label'	=> esc_html__( 'Background color', 'drplus' ),
					'type'	=> \Elementor\Controls_Manager::COLOR,
					'selectors'	=> [
						'html[data-theme="dark"] {{WRAPPER}}  .drplus-menu-wrap .menu > .menu-item > a .drplus-simple-icon-wrap::before,html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap .menu > ul > li > a .drplus-simple-icon-wrap::before' => 'background-color: {{VALUE}}',
					],
					'condition'	=> [
						'menu_style' => 'style-1'
					]
				],
			],

			'mode'	=> 'icon',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_menu_item_icon_active_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap .menu > .menu-item.current-menu-item > a .drplus-simple-icon-wrap',
			'hover_selector' => 'html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap .menu > .menu-item.current-menu-item:hover > a .drplus-simple-icon-wrap',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'		=> 'dark_menu_item_icon_active',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Active menu item icon', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> [
				'background',
			] + $dark_excludes,
			'hover_excludes'	=> [
				'background',
				'icon_background'
			] + $dark_excludes,
			'controls'	=> [
				'dark_icon_background'	=> [
					'label'	=> esc_html__( 'Background color', 'drplus' ),
					'type'	=> \Elementor\Controls_Manager::COLOR,
					'selectors'	=> [
						'html[data-theme="dark"] {{WRAPPER}}  .drplus-menu-wrap .menu > .menu-item.current-menu-item > a .drplus-simple-icon-wrap::before' => 'background-color: {{VALUE}}',
					],
					'condition'	=> [
						'menu_style' => 'style-1'
					]
				],
			],

			'mode'	=> 'icon',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_submenu_wrap_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap ul.sub-menu, .drplus-menu-wrap ul.children',
			'hover_selector' => false,
			
			'section'	=> [
				'name'	=> 'dark_submenu_wrap',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Sub menu container', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'wrapper',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_submenu_item_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap ul.sub-menu .menu-item,html[data-theme="dark"] {{WRAPPER}}  .drplus-menu-wrap ul.children > li',
			'hover_selector' => 'html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap ul.sub-menu .menu-item:hover,html[data-theme="dark"] {{WRAPPER}}  .drplus-menu-wrap ul.children li:hover',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_submenu_item',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Sub Menu item', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> [
				'submenu_item_hover_bar'
			] + $dark_excludes,

			'controls'	=> [
				'dark_submenu_item_hover_bar'	=> [
					'_position'	=> 5,
					'label'	=> esc_html__( 'Hover bar color', 'drplus' ),
					'type'	=> \Elementor\Controls_Manager::COLOR,
					'selectors'	=> [
						'html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap ul.sub-menu .menu-item a::after, html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap ul.sub-menu:is(.children) li a::after, html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap ul.children .menu-item a::after, html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap ul.children:is(.children) li a::after' => 'background: {{VALUE}}',
					],
					'condition'	=> [
						'menu_style' => 'style-1'
					]
				],
			],

			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_submenu_item_title_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap ul.sub-menu .menu-item-title, html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap ul.children .menu-item-title',
			'hover_selector' => 'html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap ul.sub-menu .menu-item:hover .menu-item-title, html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap ul.children li:hover .menu-item-title',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_submenu_item_title',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Sub Menu item title', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_submenu_item_subtitle_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap ul.sub-menu .menu-item-subtitle, html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap ul.children .menu-item-subtitle',
			'hover_selector' => 'html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap ul.sub-menu .menu-item:hover .menu-item-subtitle, html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap ul.children li:hover .menu-item-subtitle',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_submenu_item_subtitle',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Sub Menu item subtitle', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_submenu_item_icon_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap ul.sub-menu .menu-item .drplus-simple-icon-wrap, html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap ul.children .drplus-simple-icon-wrap',
			'hover_selector' => 'html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap ul.sub-menu .menu-item .drplus-simple-icon-wrap, html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap ul.children li:hover .drplus-simple-icon-wrap',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_submenu_item_icon',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Sub Menu item icon', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> [
				'background',
			] + $dark_excludes,
			'hover_excludes'	=> [
				'background',
				'icon_background'
			] + $dark_excludes,
			'controls'	=> [
				'dark_icon_background'	=> [
					'label'	=> esc_html__( 'Background color', 'drplus' ),
					'type'	=> \Elementor\Controls_Manager::COLOR,
					'selectors'	=> [
						'html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap ul.sub-menu .drplus-simple-icon-wrap::before, html[data-theme="dark"] {{WRAPPER}} .drplus-menu-wrap ul.children .drplus-simple-icon-wrap::before' => 'background-color: {{VALUE}}',
					],
					'condition'	=> [
						'menu_style' => 'style-1'
					]
				],
			],

			'mode'	=> 'icon',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$settings['menu_style'] = $settings['menu_style'] ?? 'style-1';
		$show_top_arrow = Utils::to_bool( $settings['show_top_arrow'] ) && $settings['menu_style'] == 'style-2';
		$wrap_classes = ['drplus-menu-wrap', $settings['menu-align'], $settings['menu-direction'], 'menu-' . $settings['menu_style']];

		if( $show_top_arrow && $settings['menu-direction'] != 'col-direction' && $settings['menu-direction'] != 'col-reverse-direction' ) {
			$wrap_classes[] = 'menu-item-has-top-arrow';
		}

		?>
		<nav class="<?php echo Utils::prepare_html_classes( $wrap_classes ) ?>">
			<?php
			wp_nav_menu( [
				'menu'				=> $settings['menu'],
				'container_class'	=> $settings['menu'],
				'show_icons'		=> Utils::to_bool( $settings['show_icons'] ),
				'show_subtitles'	=> Utils::to_bool( $settings['show_subtitles'] ),
				'menu_style'		=> $settings['menu_style'] ?? 'style-1'
			] );
			?>
		</nav>
		<?php
	}
}