<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;

class ProductSubtitle extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_product_subtitle';
	}

	public function get_title() {
		return esc_html__( 'Product subtitle (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-post-title';
	}

	public function get_categories() {
		return ['drplus'];
	}

	public function get_keywords() {
		return ['product', 'woocommerce', 'subtitle', 'محصول', 'زیرعنوان', 'ووکامرس'];
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Settings', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // ps_before_text
			'ps_before_text',
			[
				'label'			=> esc_html__( 'Before text', 'drplus' ),
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

		$this->add_control( // ps_after_text
			'ps_after_text',
			[
				'label'			=> esc_html__( 'After text', 'drplus' ),
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

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();

		ElementorControls::general_style_controls( $this, [ // product_subtitle_wrap_
			'prefix'		=> 'product_subtitle_wrap_',
			'selector'		=> '.product-subtitle-wrap',
			
			'section'	=> [
				'name'	=> 'product_subtitle_wrap_',
				'label'	=> esc_html__( 'General style', 'drplus' ),
			],

			'mode'	=> 'wrapper',
		] );
		ElementorControls::general_style_controls( $this, [ // product_subtitle_before_text_
			'prefix'		=> 'product_subtitle_before_text_',
			'selector'		=> '.product-subtitle_before-text',
			
			'section'	=> [
				'name'	=> 'product_subtitle_before_text_',
				'label'	=> esc_html__( 'Before text style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // product_subtitle_
			'prefix'		=> 'product_subtitle_',
			'selector'		=> '.product-subtitle',
			
			'section'	=> [
				'name'	=> 'product_subtitle_',
				'label'	=> esc_html__( 'Subtitle style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // product_subtitle_after_text_
			'prefix'		=> 'product_subtitle_after_text_',
			'selector'		=> '.product-subtitle_after-text',
			
			'section'	=> [
				'name'	=> 'product_subtitle_after_text_',
				'label'	=> esc_html__( 'After text style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [ // product_subtitle_wrap_
			'prefix' 		=> 'dark_product_subtitle_wrap_',
			'selector' 		=> 'html[data-theme="dark"] {{WRAPPER}} .product-subtitle-wrap',
			
			'section' 	=> [
				'name' 			=> 'dark_product_subtitle_wrap_',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'General style', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode' 		=> 'wrapper',
		] );
		ElementorControls::general_style_controls( $this, [ // product_subtitle_before_text_
			'prefix' 		=> 'dark_product_subtitle_before_text_',
			'selector' 		=> 'html[data-theme="dark"] {{WRAPPER}} .product-subtitle_before-text',
			
			'section' 	=> [
				'name' 			=> 'dark_product_subtitle_before_text_',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Before text style', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode' 		=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // product_subtitle_
			'prefix' 		=> 'dark_product_subtitle_',
			'selector' 		=> 'html[data-theme="dark"] {{WRAPPER}} .product-subtitle',
			
			'section' 	=> [
				'name' 			=> 'dark_product_subtitle_',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Subtitle style', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode' 		=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // product_subtitle_after_text_
			'prefix' 		=> 'dark_product_subtitle_after_text_',
			'selector' 		=> 'html[data-theme="dark"] {{WRAPPER}} .product-subtitle_after-text',
			
			'section' 	=> [
				'name' 			=> 'dark_product_subtitle_after_text_',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'After text style', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode' 		=> 'text',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		get_template_part( "templates/components/template-components-product-subtitle", null, [
			'before_text'	=> $settings['ps_before_text'],
			'after_text'	=> $settings['ps_after_text'],
		] );
	}
}