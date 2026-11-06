<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
use DrPlus\Utils\Archive;

class Products2 extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_products_2';
	}

	public function get_title() {
		return esc_html__( 'Products style 2 (Doctor Plus)', 'drplus' );
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
		ElementorControls::display_settings( $this );
		ElementorControls::query_controls( $this, true );
		$this->pagination_controls();

		ElementorControls::general_style_controls( $this, [ // product_
			'prefix'		=> 'product_',
			'base_selector'	=> '.product',
			
			'section'	=> [
				'name'	=> 'product_section',
				'label'	=> esc_html__( 'Product style', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // product_thumbnail_wrap_
			'prefix'		=> 'product_thumbnail_wrap_',
			'base_selector'	=> '.product-thumbnail-wrap',
			
			'section'	=> [
				'name'	=> 'product_thumbnail_wrap_section',
				'label'	=> esc_html__( 'Product thumbnail wrap style', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // product_img_
			'prefix'		=> 'product_img_',
			'base_selector'	=> '.product',
			'selector'		=> '.attachment-woocommerce_thumbnail',
			
			'section'	=> [
				'name'	=> 'product_img_section',
				'label'	=> esc_html__( 'Product image style', 'drplus' ),
			],

			'mode'	=> 'img',
		] );
		ElementorControls::general_style_controls( $this, [ // product_title_
			'prefix'		=> 'product_title_',
			'base_selector'	=> '.product',
			'selector'		=> '.woocommerce-loop-product__title',
			
			'section'	=> [
				'name'	=> 'product_title_section',
				'label'	=> esc_html__( 'Product title', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // product_data_box_
			'prefix'		=> 'product_data_box_',
			'base_selector'	=> '.product',
			'selector'		=> '.product-data-box',
			
			'section'	=> [
				'name'	=> 'product_data_box_section',
				'label'	=> esc_html__( 'Product data box', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // product_badge_wrap_
			'prefix'		=> 'product_badge_wrap_',
			'base_selector'	=> '.product',
			'selector'		=> '.drplus-product-badge',
			
			'section'	=> [
				'name'	=> 'product_badge_wrap_section',
				'label'	=> esc_html__( 'Product badge wrap', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // product_badge_img_
			'prefix'		=> 'product_badge_img_',
			'base_selector'	=> '.product',
			'selector'		=> '.drplus-product-badge img',
			
			'section'	=> [
				'name'	=> 'product_badge_img_section',
				'label'	=> esc_html__( 'Product badge image', 'drplus' ),
			],

			'mode'	=> 'img',
		] );
		ElementorControls::general_style_controls( $this, [ // product_badge_text_
			'prefix'		=> 'product_badge_text_',
			'base_selector'	=> '.product',
			'selector'		=> '.drplus-product-badge-text',
			
			'section'	=> [
				'name'	=> 'product_badge_text_section',
				'label'	=> esc_html__( 'Product badge text', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // discount_percentage_
			'prefix'		=> 'discount_percentage_',
			'base_selector'	=> '.product',
			'selector'		=> '.price-discount-percentage',
			
			'section'	=> [
				'name'	=> 'discount_percentage_section',
				'label'	=> esc_html__( 'Discount percentage', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // product_score_wrap_
			'prefix'		=> 'product_score_wrap_',
			'base_selector'	=> '.product',
			'selector'		=> '.product-score',
			
			'section'	=> [
				'name'	=> 'product_score_wrap_section',
				'label'	=> esc_html__( 'Score wrap', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // product_score_text_
			'prefix'		=> 'product_score_text_',
			'base_selector'	=> '.product',
			'selector'		=> '.product-score-avg',
			
			'section'	=> [
				'name'	=> 'product_score_text_section',
				'label'	=> esc_html__( 'Score text', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // product_score_icon_
			'prefix'		=> 'product_score_icon_',
			'base_selector'	=> '.product',
			'selector'		=> '.product-score-icon',
			
			'section'	=> [
				'name'	=> 'product_score_icon_section',
				'label'	=> esc_html__( 'Score icon', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // product_link_btn_
			'prefix'		=> 'product_link_btn_',
			'base_selector'	=> '.product',
			'selector'		=> '.product-link',
			
			'section'	=> [
				'name'	=> 'product_link_btn_section',
				'label'	=> esc_html__( 'Read more button', 'drplus' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // product_link_btn_text_
			'prefix'		=> 'product_link_btn_text_',
			'base_selector'	=> '.product',
			'selector'		=> '.product-link .button-text',
			
			'section'	=> [
				'name'	=> 'product_link_btn_text_section',
				'label'	=> esc_html__( 'Read more button text', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // product_link_btn_icon_
			'prefix'		=> 'product_link_btn_icon_',
			'base_selector'	=> '.product',
			'selector'		=> '.product-link .button-icon',
			
			'section'	=> [
				'name'	=> 'product_link_btn_icon_section',
				'label'	=> esc_html__( 'Read more button icon', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );

		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();
		
		ElementorControls::general_style_controls( $this, [ // dark_product_
			'prefix'		=> 'dark_product_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .product',
			
			'section'	=> [
				'name'	=> 'dark_product_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Product style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'	=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_product_thumbnail_wrap_
			'prefix'		=> 'dark_product_thumbnail_wrap_',
			'base_selector'	=> 'html[data-theme="dark"] {{WRAPPER}} .product-thumbnail-wrap',
			
			'section'	=> [
				'name'	=> 'dark_product_thumbnail_wrap_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Product thumbnail wrap style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'	=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_product_img_
			'prefix'		=> 'dark_product_img_',
			'base_selector'	=> '.product',
			'selector'		=> '.attachment-woocommerce_thumbnail',
			
			'section'	=> [
				'name'	=> 'dark_product_img_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Product image style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'	=> 'img',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_product_title_
			'prefix'		=> 'dark_product_title_',
			'base_selector'	=> '.product',
			'selector'		=> '.woocommerce-loop-product__title',
			
			'section'	=> [
				'name'	=> 'dark_product_title_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Product title', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'	=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_product_data_box_
			'prefix'		=> 'dark_product_data_box_',
			'base_selector'	=> '.product',
			'selector'		=> '.product-data-box',
			
			'section'	=> [
				'name'	=> 'dark_product_data_box_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Product data box', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'	=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_product_badge_wrap_
			'prefix'		=> 'dark_product_badge_wrap_',
			'base_selector'	=> '.product',
			'selector'		=> '.drplus-product-badge',
			
			'section'	=> [
				'name'	=> 'dark_product_badge_wrap_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Product badge wrap', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'	=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_product_badge_img_
			'prefix'		=> 'dark_product_badge_img_',
			'base_selector'	=> '.product',
			'selector'		=> '.drplus-product-badge img',
			
			'section'	=> [
				'name'	=> 'dark_product_badge_img_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Product badge image', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'	=> 'img',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_product_badge_text_
			'prefix'		=> 'dark_product_badge_text_',
			'base_selector'	=> '.product',
			'selector'		=> '.drplus-product-badge-text',
			
			'section'	=> [
				'name'	=> 'dark_product_badge_text_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Product badge text', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'	=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_discount_percentage_
			'prefix'		=> 'dark_discount_percentage_',
			'base_selector'	=> '.product',
			'selector'		=> '.price-discount-percentage',
			
			'section'	=> [
				'name'	=> 'dark_discount_percentage_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Discount percentage', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'	=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_product_score_wrap_
			'prefix'		=> 'dark_product_score_wrap_',
			'base_selector'	=> '.product',
			'selector'		=> '.product-score',
			
			'section'	=> [
				'name'	=> 'dark_product_score_wrap_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Score wrap', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'	=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_product_score_text_
			'prefix'		=> 'dark_product_score_text_',
			'base_selector'	=> '.product',
			'selector'		=> '.product-score-avg',
			
			'section'	=> [
				'name'	=> 'dark_product_score_text_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Score text', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'	=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_product_score_icon_
			'prefix'		=> 'dark_product_score_icon_',
			'base_selector'	=> '.product',
			'selector'		=> '.product-score-icon',
			
			'section'	=> [
				'name'	=> 'dark_product_score_icon_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Score icon', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'	=> 'icon',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_product_link_btn_
			'prefix'		=> 'dark_product_link_btn_',
			'base_selector'	=> '.product',
			'selector'		=> '.product-link',
			
			'section'	=> [
				'name'	=> 'dark_product_link_btn_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Read more button', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'	=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_product_link_btn_text_
			'prefix'		=> 'dark_product_link_btn_text_',
			'base_selector'	=> '.product',
			'selector'		=> '.product-link .button-text',
			
			'section'	=> [
				'name'	=> 'dark_product_link_btn_text_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Read more button text', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'	=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [ // dark_product_link_btn_icon_
			'prefix'		=> 'dark_product_link_btn_icon_',
			'base_selector'	=> '.product',
			'selector'		=> '.product-link .button-icon',
			
			'section'	=> [
				'name'	=> 'dark_product_link_btn_icon_section',
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Read more button icon', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'mode'	=> 'icon',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$settings['style'] = 'style-2';
		Archive::products( $settings );
	}
}