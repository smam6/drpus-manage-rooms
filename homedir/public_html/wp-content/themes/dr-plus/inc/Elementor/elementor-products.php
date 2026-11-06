<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
use DrPlus\Utils\Archive;

class Products extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_products';
	}

	public function get_title() {
		return esc_html__( 'Products (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-products';
	}

	public function get_categories() {
		return ['drplus', 'basic'];
	}

	public function get_keywords() {
		return ['slider', 'slide', 'post', 'shop', 'product', 'محصول', 'مطلب', 'فروشگاه', "آرشیو", "بلاگ", 'اسلایدر', 'اسلاید'];
	}

	private function pagination_controls() {
		$this->start_controls_section( // content_section
			'pagination_section',
			[
				'label'		=> esc_html__( 'Pagination settings', 'drplus' ),
				'tab'		=> \Elementor\Controls_Manager::TAB_CONTENT,
				'condition'	=> [
					'query_type!'	=> ['by_id', 'current_query']
				],
			]
		);

		$this->add_control( // ppp
			'ppp',
			[
				'label'			=> esc_html__( 'Products count', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::NUMBER,
				'min'			=> 1,
				'default'		=> 10,
				'condition'		=> [
					'query_type!'	=> ['by_id', 'current_query']
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // offset
			'offset',
			[
				'label'			=> esc_html__( 'Offset', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::NUMBER,
				'min'			=> 0,
				'default'		=> 0,
				'condition'		=> [
					'query_type!'	=> ['by_id', 'current_query']
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // show_pagination
			'show_pagination',
			[
				'label'			=> esc_html__( 'Show pagination', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
				'condition'		=> [
					'query_type!'	=> ['by_id', 'current_query']
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		ElementorControls::section_title_settings( $this );
		ElementorControls::display_settings( $this );
		ElementorControls::query_controls( $this, true );
		$this->pagination_controls();

		ElementorControls::section_title_styles( $this );
		ElementorControls::general_style_controls( $this, [ // product
			'prefix'		=> 'product_',
			'base_selector'	=> '.product',
			
			'section'	=> [
				'name'	=> 'product_section',
				'label'	=> esc_html__( 'Product style', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [ // product_img
			'prefix'		=> 'product_img_',
			'base_selector'	=> '.product',
			'selector'		=> '.attachment-woocommerce_thumbnail',
			
			'section'	=> [
				'name'	=> 'product_img_section',
				'label'	=> esc_html__( 'Product image style', 'drplus' ),
			],

			'mode'	=> 'img',
		] );

		ElementorControls::text_style_controls( $this, '.woocommerce-loop-product__title', 'product_title_', esc_html__( "Product title", 'drplus' ), '{{WRAPPER}} .product:hover .woocommerce-loop-product__title' );
		ElementorControls::text_style_controls( $this, '.product-category', 'product_category_', esc_html__( "Product category", 'drplus' ), '{{WRAPPER}} .product:hover .product-category' );
		ElementorControls::text_style_controls( $this, '.price del bdi', 'product_sale_price_', esc_html__( "Product sale price", 'drplus' ), '{{WRAPPER}} .product:hover .price del bdi' );
		ElementorControls::text_style_controls( $this, '.price ins bdi, {{WRAPPER}} .price > .amount bdi', 'product_regular_price_', esc_html__( "Product regular price", 'drplus' ), '{{WRAPPER}} .product:hover .price ins bdi, {{WRAPPER}} .product:hover .price > .amount bdi' );
		ElementorControls::general_style_controls( $this, [ // product_price_currency_
			'prefix'		=> 'product_price_currency_',
			'base_selector'	=> '.product',
			'selector'		=> '.woocommerce-Price-currencySymbol',

			'section'	=> [
				'name'	=> 'product_price_currency_section',
				'label'	=> esc_html__( 'Product price currency style', 'drplus' ),
			],

			'mode'	=> 'svg'
		] );

		ElementorControls::general_style_controls( $this, [ // add_to_cart
			'prefix'		=> 'add_to_cart_',
			'base_selector'	=> '.product',
			'selector'		=> '.add_to_cart_button',
			'hover_type'	=> 'normal',

			'section'	=> [
				'name'	=> 'add_to_cart_section',
				'label'	=> esc_html__( 'Add to cart button style', 'drplus' ),
			],

			'mode'	=> 'icon'
		] );

		ElementorControls::pagination_style_controls( $this, true );

		// Dark mode controls
		ElementorControls::dark_mode_toggle_controls( $this );

		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::section_title_styles( $this, false, true, true );
		ElementorControls::general_style_controls( $this, [ // product
			'prefix'		=> 'dark_product_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .product',
			
			'section'	=> [
				'name'	=> 'dark_product_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Product style', 'drplus' ) ),
				'condition' => $dark_condition,
			],

			'excludes' => $dark_excludes,
			'hover_excludes' => $dark_excludes,
			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [ // product_img
			'prefix'		=> 'dark_product_img_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .product',
			'selector'		=> '.attachment-woocommerce_thumbnail',
			
			'section'	=> [
				'name'	=> 'dark_product_img_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Product image style', 'drplus' ) ),
				'condition' => $dark_condition,
			],

			'excludes' => $dark_excludes,
			'hover_excludes' => $dark_excludes,
			'mode'	=> 'img',
		] );

		ElementorControls::text_style_controls( $this, '.woocommerce-loop-product__title', 'product_title_', esc_html__( "Product title", 'drplus' ), 'html[data-theme="dark"] {{WRAPPER}} .product:hover .woocommerce-loop-product__title', true );
		ElementorControls::text_style_controls( $this, '.product-category', 'product_category_', esc_html__( "Product category", 'drplus' ), 'html[data-theme="dark"] {{WRAPPER}} .product:hover .product-category', true );
		ElementorControls::text_style_controls( $this, '.price del bdi', 'product_sale_price_', esc_html__( "Product sale price", 'drplus' ), 'html[data-theme="dark"] {{WRAPPER}} .product:hover .price del bdi', true );
		ElementorControls::text_style_controls( $this, '.price ins bdi, {{WRAPPER}} .price > .amount bdi', 'product_regular_price_', esc_html__( "Product regular price", 'drplus' ), 'html[data-theme="dark"] {{WRAPPER}} .product:hover .price ins bdi, {{WRAPPER}} .product:hover .price > .amount bdi', true );
		ElementorControls::general_style_controls( $this, [ // product_price_currency_
			'prefix'		=> 'dark_product_price_currency_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .product',
			'selector'		=> '.woocommerce-Price-currencySymbol',

			'section'	=> [
				'name'	=> 'dark_product_price_currency_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Product price currency style', 'drplus' ) ),
				'condition' => $dark_condition,
			],

			'excludes' => $dark_excludes,
			'hover_excludes' => $dark_excludes,
			'mode'	=> 'svg'
		] );

		ElementorControls::general_style_controls( $this, [ // add_to_cart
			'prefix'		=> 'dark_add_to_cart_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .product',
			'selector'		=> '.add_to_cart_button',
			'hover_type'	=> 'normal',

			'section'	=> [
				'name'	=> 'dark_add_to_cart_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Add to cart button style', 'drplus' ) ),
				'condition' => $dark_condition,
			],

			'excludes' => $dark_excludes,
			'hover_excludes' => $dark_excludes,
			'mode'	=> 'icon'
		] );

		ElementorControls::pagination_style_controls( $this, true, true );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		Archive::products( $settings );
	}
}