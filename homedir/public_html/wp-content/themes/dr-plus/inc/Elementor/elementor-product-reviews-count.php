<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;

class ProductReviewsCount extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_product_reviews_count';
	}

	public function get_title() {
		return esc_html__( 'Product Reviews Count (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-comments';
	}

	public function get_categories() {
		return ['drplus'];
	}

	public function get_keywords() {
		return ['product', 'woocommerce', 'reviews', 'comments', 'محصول', 'نظر', 'دیدگاه', 'ووکامرس'];
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Settings', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // comment_text
			'comment_text',
			[
				'label'			=> esc_html__( 'Comment text', 'drplus' ),
				'description'	=> esc_html__( 'HTML tags allowed', 'drplus' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( 'Comment', 'drplus' ),
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
			'review_icon',
			[
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Icon', 'drplus' ),
				'skin'			=> 'inline',
				'label_block'	=> false,
				'default'	=> [
					'value'		=> 'drplus-icon-chat',
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

		ElementorControls::general_style_controls( $this, [ // product_reviews_count_wrap_
			'prefix'		=> 'product_reviews_count_wrap_',
			'selector'		=> '.product-head-comments',
			
			'section'	=> [
				'name'	=> 'product_reviews_count_wrap_',
				'label'	=> esc_html__( 'General style', 'drplus' ),
			],

			'mode'	=> 'wrapper',
		] );
		ElementorControls::general_style_controls( $this, [ // product_reviews_count_icon_
			'prefix'		=> 'product_reviews_count_icon_',
			'selector'		=> '.product-head-comments-icon',
			
			'section'	=> [
				'name'	=> 'product_reviews_count_icon_',
				'label'	=> esc_html__( 'Icon style', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // product_reviews_count_number_
			'prefix'		=> 'product_reviews_count_number_',
			'selector'		=> '.product-head-review-value',
			
			'section'	=> [
				'name'	=> 'product_reviews_count_number_',
				'label'	=> esc_html__( 'Reviews Count style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // product_reviews_count_text_
			'prefix'		=> 'product_reviews_count_text_',
			'selector'		=> '.product-head-comments-label',
			
			'section'	=> [
				'name'	=> 'product_reviews_count_text_',
				'label'	=> esc_html__( 'Reviews text style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [ // product_reviews_count_wrap_
			'prefix' 		=> 'dark_product_reviews_count_wrap_',
			'selector' 		=> 'html[data-theme="dark"] {{WRAPPER}} .product-head-comments',
			
			'section' 	=> [
				'name' 			=> 'dark_product_reviews_count_wrap_',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'General style', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode' 		=> 'wrapper',
		] );
		ElementorControls::general_style_controls( $this, [ // product_reviews_count_icon_
			'prefix' 		=> 'dark_product_reviews_count_icon_',
			'selector' 		=> 'html[data-theme="dark"] {{WRAPPER}} .product-head-comments-icon',
			
			'section' 	=> [
				'name' 			=> 'dark_product_reviews_count_icon_',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Icon style', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode' 	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // product_reviews_count_number_
			'prefix' 		=> 'dark_product_reviews_count_number_',
			'selector' 		=> 'html[data-theme="dark"] {{WRAPPER}} .product-head-review-value',
			
			'section' 	=> [
				'name' 			=> 'dark_product_reviews_count_number_',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Reviews Count style', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode' 		=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // product_reviews_count_text_
			'prefix' 		=> 'dark_product_reviews_count_text_',
			'selector' 		=> 'html[data-theme="dark"] {{WRAPPER}} .product-head-comments-label',
			
			'section' 	=> [
				'name' 			=> 'dark_product_reviews_count_text_',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Reviews text style', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode' 		=> 'text',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		get_template_part( "templates/components/template-components-product-reviews-count", null, [
			'comment_text'	=> $settings['comment_text'],
			'show_icon'		=> $settings['show_icon'],
			'review_icon'	=> $settings['review_icon'],
		] );
	}
}