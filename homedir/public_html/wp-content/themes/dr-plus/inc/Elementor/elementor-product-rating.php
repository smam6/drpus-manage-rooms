<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;

class ProductRating extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_product_rating';
	}

	public function get_title() {
		return esc_html__( 'Product Rating (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-rating';
	}

	public function get_categories() {
		return ['drplus'];
	}

	public function get_keywords() {
		return ['product', 'woocommerce', 'rating', 'rate', 'محصول', 'نظر', 'رای', 'ووکامرس'];
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Settings', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // show_number
			'show_number',
			[
				'label'			=> esc_html__( 'Show Number', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();

		ElementorControls::general_style_controls( $this, [ // product_rating_wrap_
			'prefix'		=> 'product_rating_wrap_',
			'selector'		=> '.product-head-rating',
			
			'section'	=> [
				'name'	=> 'product_rating_wrap_',
				'label'	=> esc_html__( 'General style', 'drplus' ),
			],

			'mode'	=> 'wrapper',
		] );
		ElementorControls::general_style_controls( $this, [ // product_rating_stars_
			'prefix'			=> 'product_rating_stars_',
			'selector'			=> '.drplus-icon-star',
			'hover_selector'	=> false,
			
			'section'	=> [
				'name'	=> 'product_rating_stars_',
				'label'	=> esc_html__( 'Stars style', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // product_rating_stars_active_
			'prefix'			=> 'product_rating_stars_active_',
			'selector'			=> '.drplus-icon-star-fill.active',
			'hover_selector'	=> false,
			
			'section'	=> [
				'name'	=> 'product_rating_stars_active_',
				'label'	=> esc_html__( 'Active Stars style', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // product_rating_number_
			'prefix'		=> 'product_rating_number_',
			'selector'		=> '.product-head-rating-value',
			
			'section'	=> [
				'name'	=> 'product_rating_number_',
				'label'	=> esc_html__( 'Rating number style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [ // product_rating_wrap_
			'prefix'		=> 'dark_product_rating_wrap_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .product-head-rating',
			
			'section'	=> [
				'name'		=> 'dark_product_rating_wrap_',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'General style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'		=> 'wrapper',
		] );
		ElementorControls::general_style_controls( $this, [ // product_rating_stars_
			'prefix'			=> 'dark_product_rating_stars_',
			'selector'			=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-icon-star',
			'hover_selector'	=> false,
			
			'section'	=> [
				'name'		=> 'dark_product_rating_stars_',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Stars style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'		=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // product_rating_stars_active_
			'prefix'			=> 'dark_product_rating_stars_active_',
			'selector'			=> 'html[data-theme="dark"] {{WRAPPER}} .drplus-icon-star-fill.active',
			'hover_selector'	=> false,
			
			'section'	=> [
				'name'		=> 'dark_product_rating_stars_active_',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Active Stars style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // product_rating_number_
			'prefix'		=> 'dark_product_rating_number_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .product-head-rating-value',
			
			'section'	=> [
				'name'		=> 'dark_product_rating_number_',
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Rating number style', 'drplus' ) ),
				'condition'	=> $dark_condition,
			],

			'excludes'	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode'		=> 'text',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		get_template_part( "templates/components/template-components-product-rating", null, [
			'show_number'	=> $settings['show_number'],
		] );
	}
}