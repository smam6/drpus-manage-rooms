<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
use DrPlus\Utils\Options;
use DrPlus\Utils\Sanitizers;

class MiniCart extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_minicart';
	}

	public function get_title() {
		return esc_html__( 'Mini cart (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-woo-cart';
	}

	public function get_categories() {
		return ['drplus'];
	}

	public function get_keywords() {
		return ['text', 'mini cart', 'woocommerce', 'wc', 'سبد خرید', 'ووکامرس', 'کارت', 'سفارش'];
	}

	private function settings_controls() {
		$options = Options::get_options( [
			'empty-mini-cart-text'	=> esc_html__( 'The cart is empty.', 'drplus' ),
			'cart-icon'				=> 'drplus-icon-shopping-cart',
		] );
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Settings', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // cart_text
			'cart_text',
			[
				'label'			=> esc_html__( 'Cart button text', 'drplus' ),
				'description'	=> esc_html__( 'HTML tags allowed', 'drplus' ),
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

		$this->add_control( // icon
			'cart_icon',
			[
				'label'		=> esc_html__( 'Cart button icon', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::ICONS,
				'default'	=> [
					'value'		=> $options['cart-icon'],
					'library'	=> 'drplus-icon',
				],
			]
		);

		$this->add_control(
			'icon_align',
			[
				'label'		=> esc_html__( 'Icon Position', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::CHOOSE,
				'default'	=> 'start',
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
			],
		);

		$this->add_control(
			'minicart_align',
			[
				'label'		=> esc_html__( 'Mini cart Position', 'drplus' ),
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
			],
		);

		$this->add_control(
			'mobile_mode',
			[
				'label'			=> esc_html__( 'open mini cart on click or tap', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();
		
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'cart_btn_',
			'selector'		=> 'a.header-cart-btn',
			'hover_selector' => 'a.header-cart-btn:hover',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'cart_btn',
				'label'	=> esc_html__( 'Cart button', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'cart_btn_text_',
			'selector'		=> 'a.header-cart-btn .header-mini-cart-text',
			'hover_selector' => 'a.header-cart-btn:hover .header-mini-cart-text',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'cart_btn_text',
				'label'	=> esc_html__( 'Cart button text', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'cart_btn_icon_',
			'selector'		=> 'a.header-cart-btn .header-cart-icon',
			'hover_selector' => 'a.header-cart-btn:hover .header-cart-icon',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'cart_btn_icon',
				'label'	=> esc_html__( 'Cart button icon', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'cart_count_',
			'selector'		=> 'a.header-cart-btn .header-cart-count-wrap',
			'hover_selector' => 'a.header-cart-btn:hover .header-cart-count-wrap',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'cart_count',
				'label'	=> esc_html__( 'Cart count', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'mini_cart_title_wrap_',
			'selector'		=> '.header-cart-wrap .header-mini-cart-title-wrap',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'mini_cart_title_wrap',
				'label'	=> esc_html__( 'Mini cart title wrap (style 2)', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'mini_cart_title_',
			'selector'		=> '.header-cart-wrap .header-mini-cart-title',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'mini_cart_title',
				'label'	=> esc_html__( 'Mini cart title (style 2)', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'mini_cart_title_icon_',
			'selector'		=> '.header-cart-wrap .header-mini-cart-title-icon',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'mini_cart_title_icon',
				'label'	=> esc_html__( 'Mini cart title icon (style 2)', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'cart_content_wrap_',
			'selector'		=> '.header-cart-wrap .header-mini-cart-wrap',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'cart_content_wrap',
				'label'	=> esc_html__( 'Mini cart wrap', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'cart_empty_text_',
			'selector'		=> '.header-mini-cart-wrap .woocommerce-mini-cart__empty-message',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'cart_empty_text',
				'label'	=> esc_html__( 'Empty cart text', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'cart_item_wrap_',
			'selector'			=> '.header-mini-cart-wrap .woocommerce-mini-cart-item',
			'hover_selector'	=> '.header-mini-cart-wrap .woocommerce-mini-cart-item:hover',
			'hover_type'		=> 'normal',
			
			'section'	=> [
				'name'	=> 'cart_item_wrap',
				'label'	=> esc_html__( 'Mini cart item', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'cart_item_image_',
			'selector'			=> '.header-mini-cart-wrap .drplus_mini-cart-item-product-image-wrap',
			'hover_selector'	=> '.header-mini-cart-wrap .woocommerce-mini-cart-item:hover .drplus_mini-cart-item-product-image-wrap',
			'hover_type'		=> 'normal',
			
			'section'	=> [
				'name'	=> 'cart_item_image',
				'label'	=> esc_html__( 'Mini cart product image', 'drplus' ),
			],

			'mode'	=> 'image',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'cart_item_name_',
			'selector'			=> '.header-mini-cart-wrap .drplus_mini-cart-item-product-name-wrap',
			'hover_selector'	=> '.header-mini-cart-wrap .woocommerce-mini-cart-item:hover .drplus_mini-cart-item-product-name-wrap',
			'hover_type'		=> 'normal',
			
			'section'	=> [
				'name'	=> 'cart_item_name',
				'label'	=> esc_html__( 'Mini cart product name', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'cart_item_remove_icon_',
			'selector'			=> '.header-mini-cart-wrap .remove_from_cart_button',
			'hover_selector'	=> '.header-mini-cart-wrap .remove_from_cart_button:hover',
			'hover_type'		=> 'normal',
			
			'section'	=> [
				'name'	=> 'cart_item_remove_icon',
				'label'	=> esc_html__( 'Mini cart product remove icon', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'cart_item_price_',
			'selector'			=> '.header-mini-cart-wrap .product-price-wrapper .price ins, {{WRAPPER}} .header-mini-cart-wrap .product-price-wrapper .price>.woocommerce-Price-amount, {{WRAPPER}} .header-mini-cart-wrap .product-price-wrapper .price .price-range bdi',
			'hover_selector'	=> false,
			
			'section'	=> [
				'name'	=> 'cart_item_price',
				'label'	=> esc_html__( 'Mini cart product price', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'cart_item_reg_price_',
			'selector'			=> '.header-mini-cart-wrap .drplus_mini-cart-item-price .price.simple_price del .woocommerce-Price-amount',
			'hover_selector'	=> false,
			
			'section'	=> [
				'name'	=> 'cart_item_reg_price',
				'label'	=> esc_html__( 'Mini cart product regular price', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'cart_item_sale_percentage_',
			'selector'			=> '.header-mini-cart-wrap .price-discount-percentage',
			'hover_selector'	=> false,
			
			'section'	=> [
				'name'	=> 'cart_item_sale_percentage',
				'label'	=> esc_html__( 'Mini cart product sale percentage', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'cart_view_btn_',
			'selector'			=> '.header-mini-cart-wrap .woocommerce-mini-cart__buttons a.view_cart',
			
			'section'	=> [
				'name'	=> 'cart_view_btn',
				'label'	=> esc_html__( 'View cart button (style 2)', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'cart_order_btn_',
			'selector'			=> '.header-mini-cart-wrap .woocommerce-mini-cart__buttons a.checkout',
			
			'section'	=> [
				'name'	=> 'cart_order_btn',
				'label'	=> esc_html__( 'Checkout button', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'cart_total_price_title_',
			'selector'			=> '.header-mini-cart-wrap .woocommerce-mini-cart__total strong',
			'hover_selector'	=> false,
			
			'section'	=> [
				'name'	=> 'cart_total_price_title',
				'label'	=> esc_html__( 'Mini cart total price title', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'cart_total_price_amount_',
			'selector'			=> '.header-mini-cart-wrap .woocommerce-mini-cart__total .amount',
			'hover_selector'	=> false,
			
			'section'	=> [
				'name'	=> 'cart_total_price_amount',
				'label'	=> esc_html__( 'Mini cart total price amount', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_cart_btn_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} a.header-cart-btn',
			'hover_selector' => 'html[data-theme="dark"] {{WRAPPER}} a.header-cart-btn:hover',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_cart_btn',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Cart button', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_cart_btn_text_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} a.header-cart-btn .header-mini-cart-text',
			'hover_selector' => 'html[data-theme="dark"] {{WRAPPER}} a.header-cart-btn:hover .header-mini-cart-text',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_cart_btn_text',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Cart button text', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_cart_btn_icon_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} a.header-cart-btn .header-cart-icon',
			'hover_selector' => 'html[data-theme="dark"] {{WRAPPER}} a.header-cart-btn:hover .header-cart-icon',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_cart_btn_icon',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Cart button icon', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'icon',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_cart_count_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} a.header-cart-btn .header-cart-count-wrap',
			'hover_selector' => 'html[data-theme="dark"] {{WRAPPER}} a.header-cart-btn:hover .header-cart-count-wrap',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_cart_count',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Cart count', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_mini_cart_title_wrap_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .header-cart-wrap .header-mini-cart-title-wrap',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_mini_cart_title_wrap',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Mini cart title wrap (style 2)', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_mini_cart_title_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .header-cart-wrap .header-mini-cart-title',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_mini_cart_title',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Mini cart title (style 2)', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_mini_cart_title_icon_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .header-cart-wrap .header-mini-cart-title-icon',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_mini_cart_title_icon',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Mini cart title icon (style 2)', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'icon',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_cart_content_wrap_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .header-cart-wrap .header-mini-cart-wrap',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_cart_content_wrap',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Mini cart wrap', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_cart_empty_text_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .header-mini-cart-wrap .woocommerce-mini-cart__empty-message',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_cart_empty_text',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Empty cart text', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_cart_item_wrap_',
			'selector'			=> 'html[data-theme="dark"] {{WRAPPER}} .header-mini-cart-wrap .woocommerce-mini-cart-item',
			'hover_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .header-mini-cart-wrap .woocommerce-mini-cart-item:hover',
			'hover_type'		=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_cart_item_wrap',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Mini cart item', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_cart_item_image_',
			'selector'			=> 'html[data-theme="dark"] {{WRAPPER}} .header-mini-cart-wrap .drplus_mini-cart-item-product-image-wrap',
			'hover_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .header-mini-cart-wrap .woocommerce-mini-cart-item:hover .drplus_mini-cart-item-product-image-wrap',
			'hover_type'		=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_cart_item_image',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Mini cart product image', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'image',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_cart_item_name_',
			'selector'			=> 'html[data-theme="dark"] {{WRAPPER}} .header-mini-cart-wrap .drplus_mini-cart-item-product-name-wrap',
			'hover_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .header-mini-cart-wrap .woocommerce-mini-cart-item:hover .drplus_mini-cart-item-product-name-wrap',
			'hover_type'		=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_cart_item_name',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Mini cart product name', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_cart_item_remove_icon_',
			'selector'			=> 'html[data-theme="dark"] {{WRAPPER}} .header-mini-cart-wrap .remove_from_cart_button',
			'hover_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .header-mini-cart-wrap .remove_from_cart_button:hover',
			'hover_type'		=> 'normal',
			
			'section'	=> [
				'name'	=> 'dark_cart_item_remove_icon',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Mini cart product remove icon', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_cart_item_price_',
			'selector'			=> 'html[data-theme="dark"] {{WRAPPER}} .header-mini-cart-wrap .product-price-wrapper .price ins, {{WRAPPER}} .header-mini-cart-wrap .product-price-wrapper .price>.woocommerce-Price-amount, {{WRAPPER}} .header-mini-cart-wrap .product-price-wrapper .price .price-range bdi',
			'hover_selector'	=> false,
			
			'section'	=> [
				'name'	=> 'dark_cart_item_price',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Mini cart product price', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_cart_item_reg_price_',
			'selector'			=> 'html[data-theme="dark"] {{WRAPPER}} .header-mini-cart-wrap .drplus_mini-cart-item-price .price.simple_price del .woocommerce-Price-amount',
			'hover_selector'	=> false,
			
			'section'	=> [
				'name'	=> 'dark_cart_item_reg_price',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Mini cart product regular price', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_cart_item_sale_percentage_',
			'selector'			=> 'html[data-theme="dark"] {{WRAPPER}} .header-mini-cart-wrap .price-discount-percentage',
			'hover_selector'	=> false,
			
			'section'	=> [
				'name'	=> 'dark_cart_item_sale_percentage',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Mini cart product sale percentage', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_cart_view_btn_',
			'selector'			=> 'html[data-theme="dark"] {{WRAPPER}} .header-mini-cart-wrap .woocommerce-mini-cart__buttons a.view_cart',
			
			'section'	=> [
				'name'		=> 'dark_cart_view_btn',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'View cart button (style 2)', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_cart_order_btn_',
			'selector'			=> 'html[data-theme="dark"] {{WRAPPER}} .header-mini-cart-wrap .woocommerce-mini-cart__buttons a',
			
			'section'	=> [
				'name'	=> 'dark_cart_order_btn',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Mini cart submit order button', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_cart_total_price_title_',
			'selector'			=> 'html[data-theme="dark"] {{WRAPPER}} .header-mini-cart-wrap .woocommerce-mini-cart__total strong',
			'hover_selector'	=> false,
			
			'section'	=> [
				'name'	=> 'dark_cart_total_price_title',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Mini cart total price title', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_cart_total_price_amount_',
			'selector'			=> 'html[data-theme="dark"] {{WRAPPER}} .header-mini-cart-wrap .woocommerce-mini-cart__total .amount',
			'hover_selector'	=> false,
			
			'section'	=> [
				'name'	=> 'dark_cart_total_price_amount',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Mini cart total price amount', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'text',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		get_template_part( "templates/header/template-header-action", 'mini_cart', [
			'cart-icon'				=> Sanitizers::icon( $settings['cart_icon'], 'header-action-icon header-cart-icon' ),
			'cart-text'				=> $settings['cart_text'],
			'icon-align'			=> $settings['icon_align'],
			'call_mode'				=> 'elementor',
			'minicart_align'		=> $settings['minicart_align'],
			'mobile_mode'			=> $settings['mobile_mode']
		] );
	}
}