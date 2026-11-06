<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
use DrPlus\Utils;
use DrPlus\Utils\Options;
use DrPlus\Utils\UI;

class WishlistButton extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_wishlist_button';
	}

	public function get_title() {
		return esc_html__( 'Wishlist Button (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-heart-o';
	}

	public function get_categories() {
		return ['drplus'];
	}

	public function get_keywords() {
		return ['product', 'woocommerce', 'wishlist', 'محصول', 'علاقه مندی', 'ووکامرس'];
	}

	public function button_settings_controls() {
		$this->start_controls_section( // content_section
			'items_settings_section',
			[
				'label'	=> esc_html__( 'Settings', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'label',
			[
				'label'			=> esc_html__( 'Button text', 'drplus' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> '',
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // added_text
			'added_text',
			[
				'label'			=> esc_html__( 'Added to wishlist text', 'drplus' ),
				'description'	=> esc_html__( 'Show a message when a product is added to the wishlist', 'drplus' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( "Added to wishlist.", 'drplus' ),
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // removed_text
			'removed_text',
			[
				'label'			=> esc_html__( 'Removed from wishlist text', 'drplus' ),
				'description'	=> esc_html__( 'Show a message when a product is removed from the wishlist', 'drplus' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( "Removed from wishlist.", 'drplus' ),
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // login_text
			'login_text',
			[
				'label'			=> esc_html__( 'Login to account text', 'drplus' ),
				'description'	=> esc_html__( 'Show a message when user is not logged in to the account', 'drplus' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( "Login to your account", 'drplus' ),
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->button_settings_controls();
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'button_wrap_',
			'selector'	=> '.wishlist-button',

			'section'	=> [
				'name'	=> 'button_wrap',
				'label'	=> esc_html__( 'Button', 'drplus' ),
			],

			'mode'	=> 'wrapper',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'button_icon_',
			'base_selector'	=> '.wishlist-button',
			'selector'		=> 'i',
			'hover_type'	=> 'base',

			'section'	=> [
				'name'	=> 'button_icon',
				'label'	=> esc_html__( 'Button icon', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'button_text_',
			'base_selector'	=> '.wishlist-button',
			'selector'		=> '.wishlist-label',
			'hover_type'	=> 'base',

			'section'	=> [
				'name'	=> 'button_text',
				'label'	=> esc_html__( 'Button text', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'button_labels_',
			'hover_selector'	=> false,
			'selector'			=> '.wishlist-popover',

			'section'	=> [
				'name'	=> 'button_labels',
				'label'	=> esc_html__( 'Added/Removed Labels', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [ // button_wrap_
			'prefix' 	=> 'dark_button_wrap_',
			'selector' 	=> 'html[data-theme="dark"] {{WRAPPER}} .wishlist-button',
			
			'section' 	=> [
				'name' 			=> 'dark_button_wrap',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Button', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,
			'mode' 		=> 'wrapper',
		] );
		ElementorControls::general_style_controls( $this, [ // button_icon_
			'prefix' 		=> 'dark_button_icon_',
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .wishlist-button',
			'selector' 		=> 'i',
			
			'section' 	=> [
				'name' 			=> 'dark_button_icon',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Button icon', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,
			'mode' 		=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // button_text_
			'prefix' 		=> 'dark_button_text_',
			'base_selector' => 'html[data-theme="dark"] {{WRAPPER}} .wishlist-button',
			'selector' 		=> '.wishlist-label',
			
			'section' 	=> [
				'name' 			=> 'dark_button_text',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Button text', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,
			'mode' 		=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // button_labels_
			'prefix' 			=> 'dark_button_labels_',
			'hover_selector' 	=> false,
			'selector' 			=> 'html[data-theme="dark"] {{WRAPPER}} .wishlist-popover',
			
			'section' 	=> [
				'name' 			=> 'dark_button_labels',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Added/Removed Labels', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes' 	=> $dark_excludes,
			'mode' 		=> 'text',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$option = Options::get_options( [
			'wishlist'	=> true,
		] );
		if( !Utils::to_bool( $option['wishlist'] ) ) return;
		
		global $product;
		if( !empty( $product ) ) {
			UI::product_wishlist( $product->get_id(), [
				'label'			=> $settings['label'],
				'added_text'	=> $settings['added_text'],
				'removed_text'	=> $settings['removed_text'],
				'login_text'	=> $settings['login_text'],
			] );
		}
	}
}