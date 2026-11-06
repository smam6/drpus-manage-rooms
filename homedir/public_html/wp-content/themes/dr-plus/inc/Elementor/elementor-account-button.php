<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
use DrPlus\Utils;

class AccountButton extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_account_button';
	}

	public function get_title() {
		return esc_html__( 'Account button (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-my-account';
	}

	public function get_categories() {
		return ['drplus'];
	}

	public function get_keywords() {
		return ['text', 'my account', 'woocommerce', 'wc', 'داشبورد', 'حساب کاربری', 'پروفایل', 'ووکامرس'];
	}

	private function guest_settings_controls() {
		$this->start_controls_section( // content_section
			'guest_settings_section',
			[
				'label'	=> esc_html__( 'Guest users settings', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // guest_text_type
			'guest_text_type',
			[
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'label'		=> esc_html__( 'Text type', 'drplus' ),
				'default'	=> 'none',
				'options'	=> [
					'custom_text'	=> __( 'Custom text', 'drplus' ),
					'none'			=> __( 'None', 'drplus' )
				],
			]
		);

		$this->add_control( // guest_button_text
			'guest_button_text',
			[
				'label'			=> esc_html__( 'Button text', 'drplus' ),
				'description'	=> esc_html__( 'HTML tags allowed', 'drplus' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( 'Account', 'drplus' ),
				'dynamic'		=> [
					'active'	=> true,
				],
				'condition'	=> [
					'guest_text_type'	=> 'custom_text',
				],
			]
		);

		$this->add_control( // guest_attachment_type
			'guest_attachment_type',
			[
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'label'		=> esc_html__( 'Attachment type', 'drplus' ),
				'default'	=> 'icon',
				'options'	=> [
					'icon'	=> __( 'Icon', 'drplus' ),
					'none'	=> __( 'None', 'drplus' )
				],
			]
		);

		$this->add_control( // guest_button_icon
			'guest_button_icon',
			[
				'label'		=> esc_html__( 'Icon', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::ICONS,
				'default'	=> [
					'value'		=> 'drplus-icon-user',
					'library'	=> 'drplus-icon',
				],
				'condition'	=> [
					'guest_attachment_type'	=> 'icon',
				],
			]
		);

		$this->add_control( // guest_attachment_align
			'guest_attachment_align',
			[
				'label'		=> esc_html__( 'Attachment Position', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::CHOOSE,
				'default'	=> 'end',
				'options'	=> [
					'start'	=> [
						'title'	=> esc_html__( 'Start', 'drplus' ),
						'icon'	=> 'eicon-h-align-left',
					],
					'end'	=> [
						'title'	=> esc_html__( 'End', 'drplus' ),
						'icon'	=> 'eicon-h-align-right',
					],
				],
				'condition'	=> [
					'guest_attachment_type!'	=> 'none',
				],
			],
		);

		$this->add_control( // guest_show_menu
			'guest_show_menu',
			[
				'label'			=> esc_html__( 'Show Menu', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->add_control( // guest_menu_align
			'guest_menu_align',
			[
				'label'		=> esc_html__( 'Menu Position', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::CHOOSE,
				'default'	=> 'end',
				'options'	=> [
					'p-start'	=> [
						'title'	=> esc_html__( 'Start', 'drplus' ),
						'icon'	=> 'eicon-h-align-left',
					],
					'p-center'	=> [
						'title'	=> esc_html__( 'Center', 'drplus' ),
						'icon'	=> 'eicon-h-align-center',
					],
					'p-end'	=> [
						'title'	=> esc_html__( 'End', 'drplus' ),
						'icon'	=> 'eicon-h-align-right',
					],
				],
				'condition'	=> [
					'guest_show_menu'	=> 'yes',
				],
			],
		);

		$this->add_control( // guest_btn_arrow
			'guest_btn_arrow',
			[
				'label'			=> esc_html__( 'Button arrow', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
			]
		);

		$this->end_controls_section();
	}

	private function users_settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Logged in users settings', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'text_type',
			[
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'label'		=> esc_html__( 'Text type', 'drplus' ),
				'default'	=> 'username',
				'options'	=> [
					'username'		=> __( 'User name', 'drplus' ),
					'custom_text'	=> __( 'Custom text', 'drplus' ),
					'none'			=> __( 'None', 'drplus' )
				],
			]
		);

		$this->add_control(
			'button_text',
			[
				'label'			=> esc_html__( 'Button text', 'drplus' ),
				'description'	=> esc_html__( 'HTML tags allowed', 'drplus' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( 'Account', 'drplus' ),
				'dynamic'		=> [
					'active'	=> true,
				],
				'condition'	=> [
					'text_type'	=> 'custom_text',
				],
			]
		);

		$this->add_control(
			'attachment_type',
			[
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'label'		=> esc_html__( 'Attachment type', 'drplus' ),
				'default'	=> 'avatar',
				'options'	=> [
					'avatar'	=> __( 'User avatar', 'drplus' ),
					'icon'		=> __( 'Icon', 'drplus' ),
					'none'		=> __( 'None', 'drplus' )
				],
			]
		);

		$this->add_control(
			'button_icon',
			[
				'label'		=> esc_html__( 'Icon', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::ICONS,
				'default'	=> [
					'value'		=> 'drplus-icon-user',
					'library'	=> 'drplus-icon',
				],
				'condition'	=> [
					'attachment_type'	=> 'icon',
				],
			]
		);

		$this->add_control(
			'attachment_align',
			[
				'label'		=> esc_html__( 'Attachment Position', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::CHOOSE,
				'default'	=> 'end',
				'options'	=> [
					'start'	=> [
						'title'	=> esc_html__( 'Start', 'drplus' ),
						'icon'	=> 'eicon-h-align-left',
					],
					'end'	=> [
						'title'	=> esc_html__( 'End', 'drplus' ),
						'icon'	=> 'eicon-h-align-right',
					],
				],
				'condition'	=> [
					'attachment_type!'	=> 'none',
				],
			],
		);

		$this->add_control(
			'show_menu',
			[
				'label'			=> esc_html__( 'Show Menu', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->add_control(
			'menu_align',
			[
				'label'		=> esc_html__( 'Menu Position', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::CHOOSE,
				'default'	=> 'end',
				'options'	=> [
					'p-start'	=> [
						'title'	=> esc_html__( 'Start', 'drplus' ),
						'icon'	=> 'eicon-h-align-left',
					],
					'p-center'	=> [
						'title'	=> esc_html__( 'Center', 'drplus' ),
						'icon'	=> 'eicon-h-align-center',
					],
					'p-end'	=> [
						'title'	=> esc_html__( 'End', 'drplus' ),
						'icon'	=> 'eicon-h-align-right',
					],
				],
				'condition'	=> [
					'show_menu'	=> 'yes',
				],
			],
		);

		$this->add_control(
			'show_user_name_in_menu',
			[
				'label'			=> esc_html__( 'Show user name in menu', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
				'condition'	=> [
					'show_menu'	=> 'yes',
				],
			]
		);

		$this->add_control(
			'show_user_email_in_menu',
			[
				'label'			=> esc_html__( 'Show user email in menu', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
				'condition'	=> [
					'show_menu'	=> 'yes',
				],
			]
		);

		$this->add_control(
			'show_signout_in_menu',
			[
				'label'			=> esc_html__( 'Show signout button in menu', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
				'condition'	=> [
					'show_menu'	=> 'yes',
				],
			]
		);

		$this->add_control(
			'show_notif_count',
			[
				'label'			=> esc_html__( 'Show notification count', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->add_control(
			'btn_arrow',
			[
				'label'			=> esc_html__( 'Button arrow', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
			]
		);

		$this->end_controls_section();
	}

	private function dark_mode_toggle_controls() {
		$this->start_controls_section(
			'dark_mode_toggle',
			[
				'label'	=> esc_html__( 'Dark mode', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'enable_dark_mode',
			[
				'label'			=> esc_html__( 'Customize dark mode styles', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
				'description'	=> esc_html__( 'Enable to set separate colors for dark mode.', 'drplus' ),
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->guest_settings_controls();
		$this->users_settings_controls();
		
		ElementorControls::general_style_controls( $this, [ // btn_wrap_
			'prefix'		=> 'btn_wrap_',
			'selector'		=> '.header-account-wrap .header-account',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'btn_wrap_',
				'label'	=> esc_html__( 'Button', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [ // btn_text_
			'prefix'			=> 'btn_text_',
			'base_selector'		=> '.header-account-wrap .header-account',
			'selector'			=> '.header-account-display_name',
			'hover_type'		=> 'base',
			
			'section'	=> [
				'name'	=> 'btn_text_',
				'label'	=> esc_html__( 'Button text', 'drplus' ),
				'conditions'	=> [
					'relation'	=> 'or',
					'terms'	=> [
						[
							'name'		=> 'text_type',
							'operator'	=> '!=',
							'value'		=> 'none',
						],
						[
							'name'		=> 'guest_text_type',
							'operator'	=> '!=',
							'value'		=> 'none',
						],
					],
				],
			],

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [ // btn_avatar_
			'prefix'			=> 'btn_avatar_',
			'base_selector'		=> '.header-account-wrap .header-account',
			'selector'			=> 'img',
			'hover_type'		=> 'base',
			
			'section'	=> [
				'name'	=> 'btn_avatar',
				'label'	=> esc_html__( 'User avatar', 'drplus' ),
				'condition'	=> [
					'attachment_type'	=> 'avatar',
				],
			],

			'mode'	=> 'image',
		] );

		ElementorControls::general_style_controls( $this, [ // btn_icon_
			'prefix'			=> 'btn_icon_',
			'base_selector'		=> '.header-account-wrap .header-account',
			'selector'			=> '.header-account-btn-icon',
			'hover_type'		=> 'base',
			
			'section'	=> [
				'name'	=> 'btn_icon',
				'label'	=> esc_html__( 'Button icon', 'drplus' ),
				'conditions'	=> [
					'relation'	=> 'or',
					'terms'	=> [
						[
							'name'		=> 'attachment_type',
							'operator'	=> '==',
							'value'		=> 'icon',
						],
						[
							'name'		=> 'guest_attachment_type',
							'operator'	=> '==',
							'value'		=> 'icon',
						],
					],
				],
			],

			'mode'	=> 'icon',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'btn_icon_arrow_',
			'base_selector'		=> '.header-account-wrap .header-account',
			'selector'			=> '.header-account-active-icon',
			'hover_type'		=> 'base',
			
			'section'	=> [
				'name'	=> 'btn_icon_arrow',
				'label'	=> esc_html__( 'Button arrow icon', 'drplus' ),
				'conditions'	=> [
					'relation'	=> 'or',
					'terms'	=> [
						[
							'name'		=> 'btn_arrow',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
						[
							'name'		=> 'guest_btn_arrow',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
					],
				],
			],

			'mode'	=> 'icon',
		] );

		ElementorControls::general_style_controls( $this, [ // btn_menu_wrap_
			'prefix'			=> 'btn_menu_wrap_',
			'base_selector'		=> '.header-account-wrap',
			'selector'			=> '.header-account-items',
			'hover_type'		=> 'normal',
			
			'section'	=> [
				'name'	=> 'btn_menu_wrap',
				'label'	=> esc_html__( 'Account menu', 'drplus' ),
				'conditions'	=> [
					'relation'	=> 'or',
					'terms'	=> [
						[
							'name'		=> 'show_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
						[
							'name'		=> 'guest_show_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
					],
				],
			],

			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [ // btn_menu_user_info_item
			'prefix'			=> 'btn_menu_user_info_item_',
			'base_selector'		=> '.header-account-wrap',
			'selector'			=> '.account-user-info',
			'hover_type'		=> 'normal',
			
			'section'	=> [
				'name'	=> 'btn_menu_user_info_item',
				'label'	=> esc_html__( 'User info item', 'drplus' ),
				'conditions'	=> [
					'relation'	=> 'or',
					'terms'	=> [
						[
							'name'		=> 'show_user_name_in_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
						[
							'name'		=> 'show_user_email_in_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
					],
				],
			],

			'controls'	=> [
				'btn_menu_user_info_item_bottom_line_display'	=> [
					'type'		=> \Elementor\Controls_Manager::SELECT,
					'label'		=> esc_html__( 'Show bottom line', 'drplus' ),
					'options'	=> [
						'block'		=> esc_html__( 'Show', 'drplus' ),
						'none'		=> esc_html__( 'Hide', 'drplus' ),
					],
					'selectors'	=> [
						"{{WRAPPER}} .account-item.account-user-info::after" => 'display: {{VALUE}} !important;',
					],
				],
				'btn_menu_user_info_item_bottom_line_color'	=> [
					'type'		=> \Elementor\Controls_Manager::COLOR,
					'label'		=> esc_html__( 'Bottom line color', 'drplus' ),
					'selectors'	=> [
						"{{WRAPPER}} .account-item.account-user-info::after" => 'background-color: {{VALUE}} !important;',
					],
				],
			],
			'hover_excludes' => [
				'btn_menu_user_info_item_bottom_line_display',
				'btn_menu_user_info_item_bottom_line_color'
			],

			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [ // btn_menu_user_name_text
			'prefix'			=> 'btn_menu_user_name_text_',
			'base_selector'		=> '.header-account-wrap',
			'selector'			=> '.account-user_name',
			'hover_type'		=> 'normal',
			
			'section'	=> [
				'name'	=> 'btn_menu_user_name_text_',
				'label'	=> esc_html__( 'User name text', 'drplus' ),
				'conditions'	=> [
					'relation'	=> 'and',
					'terms'	=> [
						[
							'name'		=> 'show_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
						[
							'name'		=> 'show_user_name_in_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
					],
				],
			],

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [ // btn_menu_user_email_text
			'prefix'			=> 'btn_menu_user_email_text_',
			'base_selector'		=> '.header-account-wrap',
			'selector'			=> '.account-user_email',
			'hover_type'		=> 'normal',
			
			'section'	=> [
				'name'	=> 'btn_menu_user_email_text_',
				'label'	=> esc_html__( 'User email text', 'drplus' ),
				'conditions'	=> [
					'relation'	=> 'and',
					'terms'	=> [
						[
							'name'		=> 'show_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
						[
							'name'		=> 'show_user_email_in_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
					],
				],
			],

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [ // btn_menu_signout_item
			'prefix'			=> 'btn_menu_signout_item_',
			'base_selector'		=> '.header-account-wrap',
			'selector'			=> '.account-user-signout',
			'hover_type'		=> 'normal',
			
			'section'	=> [
				'name'	=> 'btn_menu_signout_item',
				'label'	=> esc_html__( 'Signout item', 'drplus' ),
				'conditions'	=> [
					'relation'	=> 'and',
					'terms'	=> [
						[
							'name'		=> 'show_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
						[
							'name'		=> 'show_signout_in_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
					],
				],
			],

			'mode'	=> 'wrap',
		] );
		
		ElementorControls::general_style_controls( $this, [ // btn_menu_signout_text
			'prefix'			=> 'btn_menu_signout_text_',
			'base_selector'		=> '.header-account-wrap',
			'selector'			=> '.account-user-signout .account-item-link',
			'hover_type'		=> 'normal',
			
			'section'	=> [
				'name'	=> 'btn_menu_signout_text',
				'label'	=> esc_html__( 'Signout text', 'drplus' ),
				'conditions'	=> [
					'relation'	=> 'and',
					'terms'	=> [
						[
							'name'		=> 'show_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
						[
							'name'		=> 'show_signout_in_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
					],
				],
			],

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [ // btn_menu_item_
			'prefix'			=> 'btn_menu_item_',
			'base_selector'		=> '.header-account-wrap',
			'selector'			=> '.account-item-link',
			'hover_type'		=> 'normal',
			
			'section'	=> [
				'name'	=> 'btn_menu_item',
				'label'	=> esc_html__( 'Account menu item', 'drplus' ),
				'conditions'	=> [
					'relation'	=> 'or',
					'terms'	=> [
						[
							'name'		=> 'show_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
						[
							'name'		=> 'guest_show_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
					],
				],
			],

			'controls'	=> [
				'btn_menu_item_bottom_line_display'	=> [
					'type'		=> \Elementor\Controls_Manager::SELECT,
					'label'		=> esc_html__( 'Show bottom line', 'drplus' ),
					'options'	=> [
						'block'		=> esc_html__( 'Show', 'drplus' ),
						'none'		=> esc_html__( 'Hide', 'drplus' ),
					],
					'selectors'	=> [
						"{{WRAPPER}} .account-item:not(:last-child)::after"	=> 'display: {{VALUE}};',
					],
				],
				'btn_menu_item_bottom_line_color'	=> [
					'type'		=> \Elementor\Controls_Manager::COLOR,
					'label'		=> esc_html__( 'Bottom line color', 'drplus' ),
					'selectors'	=> [
						"{{WRAPPER}} .account-item:not(:last-child)::after"	=> 'background-color: {{VALUE}};',
					],
				],
			],
			'hover_excludes' => [
				'btn_menu_item_bottom_line_display',
				'btn_menu_item_bottom_line_color'
			],

			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [ // btn_menu_item_text_
			'prefix'			=> 'btn_menu_item_text_',
			'base_selector'		=> '.header-account-wrap .account-item-link',
			'selector'			=> '.account-item-label',
			'hover_type'		=> 'base',
			
			'section'	=> [
				'name'	=> 'btn_menu_item_text',
				'label'	=> esc_html__( 'Account menu item text', 'drplus' ),
				'conditions'	=> [
					'relation'	=> 'or',
					'terms'	=> [
						[
							'name'		=> 'show_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
						[
							'name'		=> 'guest_show_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
					],
				],
			],

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [ // btn_menu_item_icon_
			'prefix'			=> 'btn_menu_item_icon_',
			'base_selector'		=> '.header-account-wrap .account-item-link',
			'selector'			=> '.account-item-icon',
			'hover_type'		=> 'base',
			
			'section'	=> [
				'name'	=> 'btn_menu_item_icon',
				'label'	=> esc_html__( 'Account menu item icon', 'drplus' ),
				'conditions'	=> [
					'relation'	=> 'or',
					'terms'	=> [
						[
							'name'		=> 'show_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
						[
							'name'		=> 'guest_show_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
					],
				],
			],

			'mode'	=> 'icon',
		] );

		ElementorControls::general_style_controls( $this, [ // btn_menu_item_arrow_
			'prefix'			=> 'btn_menu_item_arrow_',
			'base_selector'		=> '.header-account-wrap .account-item-link',
			'selector'			=> '.account-item-hover-icon',
			'hover_type'		=> 'base',
			
			'section'	=> [
				'name'	=> 'btn_menu_item_arrow',
				'label'	=> esc_html__( 'Account menu item arrow', 'drplus' ),
				'conditions'	=> [
					'relation'	=> 'or',
					'terms'	=> [
						[
							'name'		=> 'show_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
						[
							'name'		=> 'guest_show_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
					],
				],
			],

			'mode'	=> 'icon',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );

		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [ // dark_btn_wrap_
			'prefix'		=> 'dark_btn_wrap_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .header-account-wrap .header-account',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'		=> 'dark_btn_wrap_',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Button', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [ // dark_btn_text_
			'prefix'			=> 'dark_btn_text_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .header-account-wrap .header-account',
			'selector'			=> '.header-account-display_name',
			'hover_type'		=> 'base',
			
			'section'	=> [
				'name'		=> 'dark_btn_text_',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Button text', 'drplus' ) ),
				'conditions'	=> [
					'relation'	=> 'or',
					'terms'	=> [
						[
							'name'		=> 'text_type',
							'operator'	=> '!=',
							'value'		=> 'none',
						],
						[
							'name'		=> 'guest_text_type',
							'operator'	=> '!=',
							'value'		=> 'none',
						],
					],
				],
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [ // dark_btn_avatar_
			'prefix'			=> 'dark_btn_avatar_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .header-account-wrap .header-account',
			'selector'			=> 'img',
			'hover_type'		=> 'base',
			
			'section'	=> [
				'name'		=> 'dark_btn_avatar',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'User avatar', 'drplus' ) ),
				'condition'	=> array_merge(
					[
						'attachment_type'	=> 'avatar',
					],
					$dark_condition
				),
			],

			'mode'				=> 'image',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [ // dark_btn_icon_
			'prefix'			=> 'dark_btn_icon_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .header-account-wrap .header-account',
			'selector'			=> '.header-account-btn-icon',
			'hover_type'		=> 'base',
			
			'section'	=> [
				'name'		=> 'dark_btn_icon',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Button icon', 'drplus' ) ),
				'conditions'	=> [
					'relation'	=> 'or',
					'terms'	=> [
						[
							'name'		=> 'attachment_type',
							'operator'	=> '==',
							'value'		=> 'icon',
						],
						[
							'name'		=> 'guest_attachment_type',
							'operator'	=> '==',
							'value'		=> 'icon',
						],
					],
				],
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'icon',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_btn_icon_arrow_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .header-account-wrap .header-account',
			'selector'			=> '.header-account-active-icon',
			'hover_type'		=> 'base',
			
			'section'	=> [
				'name'		=> 'dark_btn_icon_arrow',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Button arrow icon', 'drplus' ) ),
				'conditions'	=> [
					'relation'	=> 'or',
					'terms'	=> [
						[
							'name'		=> 'btn_arrow',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
						[
							'name'		=> 'guest_btn_arrow',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
					],
				],
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'icon',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [ // dark_btn_menu_wrap_
			'prefix'			=> 'dark_btn_menu_wrap_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .header-account-wrap',
			'selector'			=> '.header-account-items',
			'hover_type'		=> 'normal',
			
			'section'	=> [
				'name'		=> 'dark_btn_menu_wrap',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Account menu', 'drplus' ) ),
				'conditions'	=> [
					'relation'	=> 'or',
					'terms'	=> [
						[
							'name'		=> 'show_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
						[
							'name'		=> 'guest_show_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
					],
				],
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [ // dark_btn_menu_user_info_item
			'prefix'			=> 'dark_btn_menu_user_info_item_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .header-account-wrap',
			'selector'			=> '.account-user-info',
			'hover_type'		=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_btn_menu_user_info_item',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'User info item', 'drplus' ) ),
				'conditions'	=> [
					'relation'	=> 'and',
					'terms'	=> [
						[
							'relation'	=> 'or',
							'terms'	=> [
								[
									'name'		=> 'show_user_name_in_menu',
									'operator'	=> '==',
									'value'		=> 'yes',
								],
								[
									'name'		=> 'show_user_email_in_menu',
									'operator'	=> '==',
									'value'		=> 'yes',
								],
							],
						],
						[
							'name'		=> 'enable_dark_mode',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
					],
				],
			],

			'controls'	=> [
				'dark_btn_menu_user_info_item_bottom_line_display'	=> [
					'type'		=> \Elementor\Controls_Manager::SELECT,
					'label'		=> esc_html__( 'Show bottom line', 'drplus' ),
					'options'	=> [
						'block'		=> esc_html__( 'Show', 'drplus' ),
						'none'		=> esc_html__( 'Hide', 'drplus' ),
					],
					'selectors'	=> [
						'html[data-theme="dark"] {{WRAPPER}} .account-item.account-user-info::after' => 'display: {{VALUE}} !important;',
					],
				],
				'dark_btn_menu_user_info_item_bottom_line_color'	=> [
					'type'		=> \Elementor\Controls_Manager::COLOR,
					'label'		=> esc_html__( 'Bottom line color', 'drplus' ),
					'selectors'	=> [
						'html[data-theme="dark"] {{WRAPPER}} .account-item.account-user-info::after' => 'background-color: {{VALUE}} !important;',
					],
				],
			],
			'excludes'			=> $dark_excludes,
			'hover_excludes' => [
				'dark_btn_menu_user_info_item_bottom_line_display',
				'dark_btn_menu_user_info_item_bottom_line_color'
			]+$dark_excludes,

			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [ // dark_btn_menu_user_name_text
			'prefix'			=> 'dark_btn_menu_user_name_text_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .header-account-wrap',
			'selector'			=> '.account-user_name',
			'hover_type'		=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_btn_menu_user_name_text_',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'User name text', 'drplus' ) ),
				'conditions'	=> [
					'relation'	=> 'and',
					'terms'	=> [
						[
							'name'		=> 'show_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
						[
							'name'		=> 'show_user_name_in_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
						[
							'name'		=> 'enable_dark_mode',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
					],
				],
			],

			'mode'	=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [ // dark_btn_menu_user_email_text
			'prefix'			=> 'dark_btn_menu_user_email_text_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .header-account-wrap',
			'selector'			=> '.account-user_email',
			'hover_type'		=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_btn_menu_user_email_text_',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'User email text', 'drplus' ) ),
				'conditions'	=> [
					'relation'	=> 'and',
					'terms'	=> [
						[
							'name'		=> 'show_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
						[
							'name'		=> 'show_user_email_in_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
						[
							'name'		=> 'enable_dark_mode',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
					],
				],
			],

			'mode'	=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [ // dark_btn_menu_signout_item
			'prefix'			=> 'dark_btn_menu_signout_item_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .header-account-wrap',
			'selector'			=> '.account-user-signout',
			'hover_type'		=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_btn_menu_signout_item',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Signout item', 'drplus' ) ),
				'conditions'	=> [
					'relation'	=> 'and',
					'terms'	=> [
						[
							'name'		=> 'show_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
						[
							'name'		=> 'show_signout_in_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
						[
							'name'		=> 'enable_dark_mode',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
					],
				],
			],

			'mode'	=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		
		ElementorControls::general_style_controls( $this, [ // dark_btn_menu_signout_text
			'prefix'			=> 'dark_btn_menu_signout_text_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .header-account-wrap',
			'selector'			=> '.account-user-signout .account-item-link',
			'hover_type'		=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_btn_menu_signout_text',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Signout text', 'drplus' ) ),
				'conditions'	=> [
					'relation'	=> 'and',
					'terms'	=> [
						[
							'name'		=> 'show_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
						[
							'name'		=> 'show_signout_in_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
						[
							'name'		=> 'enable_dark_mode',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
						
					],
				],
			],

			'mode'	=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [ // dark_btn_menu_item_
			'prefix'			=> 'dark_btn_menu_item_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .header-account-wrap',
			'selector'			=> '.account-item-link',
			'hover_type'		=> 'normal',
			
			'section'	=> [
				'name'		=> 'dark_btn_menu_item',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Account menu item', 'drplus' ) ),
				'conditions'	=> [
					'relation'	=> 'or',
					'terms'	=> [
						[
							'name'		=> 'show_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
						[
							'name'		=> 'guest_show_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
					],
				],
				'condition'	=> $dark_condition,
			],

			'controls'	=> [
				'dark_btn_menu_item_bottom_line_display'	=> [
					'type'		=> \Elementor\Controls_Manager::SELECT,
					'label'		=> esc_html__( 'Show bottom line', 'drplus' ),
					'options'	=> [
						'block'		=> esc_html__( 'Show', 'drplus' ),
						'none'		=> esc_html__( 'Hide', 'drplus' ),
					],
					'selectors'	=> [
						'html[data-theme="dark"] {{WRAPPER}} .account-item:not(:last-child)::after'	=> 'display: {{VALUE}};',
					],
				],
				'dark_btn_menu_item_bottom_line_color'	=> [
					'type'		=> \Elementor\Controls_Manager::COLOR,
					'label'		=> esc_html__( 'Bottom line color', 'drplus' ),
					'selectors'	=> [
						'html[data-theme="dark"] {{WRAPPER}} .account-item:not(:last-child)::after'	=> 'background-color: {{VALUE}};',
					],
				],
			],

			'mode'				=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> [
				'dark_btn_menu_item_bottom_line_display',
				'dark_btn_menu_item_bottom_line_color'
			]+$dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [ // dark_btn_menu_item_text_
			'prefix'			=> 'dark_btn_menu_item_text_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .header-account-wrap .account-item-link',
			'selector'			=> '.account-item-label',
			'hover_type'		=> 'base',
			
			'section'	=> [
				'name'		=> 'dark_btn_menu_item_text',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Account menu item text', 'drplus' ) ),
				'conditions'	=> [
					'relation'	=> 'or',
					'terms'	=> [
						[
							'name'		=> 'show_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
						[
							'name'		=> 'guest_show_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
					],
				],
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [ // dark_btn_menu_item_icon_
			'prefix'			=> 'dark_btn_menu_item_icon_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .header-account-wrap .account-item-link',
			'selector'			=> '.account-item-icon',
			'hover_type'		=> 'base',
			
			'section'	=> [
				'name'		=> 'dark_btn_menu_item_icon',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Account menu item icon', 'drplus' ) ),
				'conditions'	=> [
					'relation'	=> 'or',
					'terms'	=> [
						[
							'name'		=> 'show_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
						[
							'name'		=> 'guest_show_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
					],
				],
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'icon',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [ // dark_btn_menu_item_arrow_
			'prefix'			=> 'dark_btn_menu_item_arrow_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .header-account-wrap .account-item-link',
			'selector'			=> '.account-item-hover-icon',
			'hover_type'		=> 'base',
			
			'section'	=> [
				'name'		=> 'dark_btn_menu_item_arrow',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Account menu item arrow', 'drplus' ) ),
				'conditions'	=> [
					'relation'	=> 'or',
					'terms'	=> [
						[
							'name'		=> 'show_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
						[
							'name'		=> 'guest_show_menu',
							'operator'	=> '==',
							'value'		=> 'yes',
						],
					],
				],
				'condition'	=> $dark_condition,
			],

			'mode'				=> 'icon',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
	}

	protected function render() {
		$user_logged_in = is_user_logged_in();
		$settings = $this->get_settings_for_display();

		$button_text = __( 'Account', 'drplus' );
		if( $user_logged_in ) {
			$button_text = $settings['button_text'] ?? $button_text;
		} else {
			$button_text = $settings['guest_button_text'] ?? $button_text;
		}
		
		$attachment_align = 'start';
		if( $user_logged_in ) {
			$attachment_align = $settings['attachment_align'] ?? $attachment_align;
		} else {
			$attachment_align = $settings['guest_attachment_align'] ?? $attachment_align;
		}

		$button_icon = 'drplus-icon-user';
		if( $user_logged_in ) {
			$button_icon = $settings['button_icon'] ?? $button_icon;
		} else {
			$button_icon = $settings['guest_button_icon'] ?? $button_icon;
		}

		$menu_align = 'p-start';
		if( $user_logged_in ) {
			$menu_align = $settings['menu_align'] ?? $menu_align;
		} else {
			$menu_align = $settings['guest_menu_align'] ?? $menu_align;
		}

		get_template_part( "templates/header/template-header-action", 'account_btn', [
			'call_mode'						=> 'elementor',
			'account-btn-text-type'			=> $user_logged_in ? $settings['text_type'] : $settings['guest_text_type'],
			'account-btn-text'				=> $button_text,
			'account-btn-attachment-type'	=> $user_logged_in ? $settings['attachment_type'] : $settings['guest_attachment_type'],
			'account-btn-attachment-align'	=> $attachment_align,
			'account-btn-icon'				=> $button_icon,
			'show-account-btn-menu'			=> $user_logged_in ? Utils::to_bool( $settings['show_menu'] ) : Utils::to_bool( $settings['guest_show_menu'] ),
			'account-btn-menu-align'		=> $menu_align,
			'show_user_name_in_menu'		=> Utils::to_bool( $settings['show_user_name_in_menu'] ),
			'show_user_email_in_menu'		=> Utils::to_bool( $settings['show_user_email_in_menu'] ),
			'show_signout_in_menu'			=> Utils::to_bool( $settings['show_signout_in_menu'] ),
			'show-notif-count'				=> $settings['show_notif_count'],
			'show_arrow'					=> $user_logged_in ? Utils::to_bool( $settings['btn_arrow'] ) : Utils::to_bool( $settings['guest_btn_arrow'] ),
		] );
	}
}
