<?php
namespace drplus\Elementor;

use drplus\ElementorControls;

class ProductFeaturedAttributes extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_product_featured_attributes';
	}

	public function get_title() {
		return esc_html__( 'Product Featured attributes (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	public function get_categories() {
		return ['drplus'];
	}

	public function get_keywords() {
		return ['product', 'woocommerce', 'single', 'attribute', 'featured', 'محصول', 'ویژگی', 'ووکامرس'];
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Settings', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // title
			'title',
			[
				'label'			=> esc_html__( 'Title', 'drplus' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( 'Product main features:', 'drplus' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // show_icon
			'show_icon',
			[
				'label'			=> esc_html__( 'Show icon', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->add_control( // icon
			'featured_icon',
			[
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Icon', 'drplus' ),
				'skin'			=> 'inline',
				'label_block'	=> false,
				'default'	=> [
					'value'		=> 'drplus-icon-tick',
					'library'	=> 'drplus-icon',
				],
				'condition'		=> [
					'show_icon'	=> 'yes',
				]
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();

		ElementorControls::general_style_controls( $this, [ // product_featured_wrap_
			'prefix'		=> 'product_featured_wrap_',
			'selector'		=> '.product-featured-attributes-wrap',
			
			'section'	=> [
				'name'	=> 'product_featured_wrap_',
				'label'	=> esc_html__( 'General style', 'drplus' ),
			],

			'mode'	=> 'wrapper',
		] );

		ElementorControls::general_style_controls( $this, [ // product_featured_label_
			'prefix'		=> 'product_featured_label_',
			'selector'		=> '.product-featured-attributes-label',
			
			'section'	=> [
				'name'	=> 'product_featured_label_',
				'label'	=> esc_html__( 'Title style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		
		ElementorControls::general_style_controls( $this, [ // product_featured_item_
			'prefix'		=> 'product_featured_item_',
			'selector'		=> '.product-featured-attribute',
			
			'section'	=> [
				'name'	=> 'product_featured_item_',
				'label'	=> esc_html__( 'Featured item style', 'drplus' ),
			],

			'mode'	=> 'wrapper',
		] );
		ElementorControls::general_style_controls( $this, [ // product_featured_item_icon_
			'prefix'		=> 'product_featured_item_icon_',
			'selector'		=> '.product-featured-attribute i',
			
			'section'	=> [
				'name'	=> 'product_featured_item_icon_',
				'label'	=> esc_html__( 'Featured icon style', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // product_featured_item_title_
			'prefix'		=> 'product_featured_item_title_',
			'selector'		=> '.product-featured-attribute-label',
			
			'section'	=> [
				'name'	=> 'product_featured_item_title_',
				'label'	=> esc_html__( 'Featured title style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // product_featured_item_value_
			'prefix'		=> 'product_featured_item_value_',
			'selector'		=> '.product-featured-attribute-option',
			
			'section'	=> [
				'name'	=> 'product_featured_item_value_',
				'label'	=> esc_html__( 'Featured value style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [ // product_featured_wrap_
			'prefix'		=> 'dark_product_featured_wrap_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .product-featured-attributes-wrap',
			
			'section'	=> [
				'name'		=> 'dark_product_featured_wrap_',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'General style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'		=> 'wrapper',
		] );

		ElementorControls::general_style_controls( $this, [ // product_featured_label_
			'prefix'		=> 'dark_product_featured_label_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .product-featured-attributes-label',
			
			'section'	=> [
				'name'		=> 'dark_product_featured_label_',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Title style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'		=> 'text',
		] );
		
		ElementorControls::general_style_controls( $this, [ // product_featured_item_
			'prefix'		=> 'dark_product_featured_item_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .product-featured-attribute',
			
			'section'	=> [
				'name'		=> 'dark_product_featured_item_',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Featured item style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'wrapper',
		] );
		ElementorControls::general_style_controls( $this, [ // product_featured_item_icon_
			'prefix'		=> 'dark_product_featured_item_icon_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .product-featured-attribute i',
			
			'section'	=> [
				'name'		=> 'dark_product_featured_item_icon_',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Featured icon style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'		=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // product_featured_item_title_
			'prefix'		=> 'dark_product_featured_item_title_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .product-featured-attribute-label',
			
			'section'	=> [
				'name'		=> 'dark_product_featured_item_title_',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Featured title style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'		=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // product_featured_item_value_
			'prefix'		=> 'dark_product_featured_item_value_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .product-featured-attribute-option',
			
			'section'	=> [
				'name'		=> 'dark_product_featured_item_value_',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Featured value style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'		=> 'text',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		drplus_wc_single_feature_attrs( [
			'title'			=> $settings['title'],
			'show_icon'		=> $settings['show_icon'],
			'icon'			=> $settings['featured_icon'],
		], true );
	}
}