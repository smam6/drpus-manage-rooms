<?php
namespace drplus\Elementor;

use drplus\ElementorControls;

class ProductServices extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_product_services';
	}

	public function get_title() {
		return esc_html__( 'Product services (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-checkbox';
	}

	public function get_categories() {
		return ['drplus'];
	}

	public function get_keywords() {
		return ['product', 'woocommerce', 'single', 'service', 'محصول', 'سرویس', 'ووکامرس'];
	}

	protected function register_controls() {
		ElementorControls::general_style_controls( $this, [ // product_services_wrap_
			'prefix'		=> 'product_services_wrap_',
			'selector'		=> '.product-services-wrap',
			
			'section'	=> [
				'name'	=> 'product_services_wrap_',
				'label'	=> esc_html__( 'General style', 'drplus' ),
			],

			'mode'	=> 'wrapper',
		] );
		ElementorControls::general_style_controls( $this, [ // product_service_item_
			'prefix'		=> 'product_service_item_',
			'selector'		=> '.product-service-item',
			
			'section'	=> [
				'name'	=> 'product_service_item_',
				'label'	=> esc_html__( 'Service item style', 'drplus' ),
			],

			'mode'	=> 'wrapper',
		] );
		ElementorControls::general_style_controls( $this, [ // product_service_icon_
			'prefix'		=> 'product_service_icon_',
			'selector'		=> '.product-service-icon',
			
			'section'	=> [
				'name'	=> 'product_service_icon_',
				'label'	=> esc_html__( 'Service icon style', 'drplus' ),
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // product_service_name_
			'prefix'		=> 'product_service_name_',
			'selector'		=> '.product-service-name',
			
			'section'	=> [
				'name'	=> 'product_service_name_',
				'label'	=> esc_html__( 'Service name style', 'drplus' ),
			],

			'mode'	=> 'text',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [ // product_services_wrap_
			'prefix' 		=> 'dark_product_services_wrap_',
			'selector' 		=> 'html[data-theme="dark"] {{WRAPPER}} .product-services-wrap',
			
			'section' 	=> [
				'name' 			=> 'dark_product_services_wrap_',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'General style', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode' 		=> 'wrapper',
		] );
		ElementorControls::general_style_controls( $this, [ // product_service_item_
			'prefix' 		=> 'dark_product_service_item_',
			'selector' 		=> 'html[data-theme="dark"] {{WRAPPER}} .product-service-item',
			
			'section' 	=> [
				'name' 	=> 'dark_product_service_item_',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Service item style', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode' 		=> 'wrapper',
		] );
		ElementorControls::general_style_controls( $this, [ // product_service_icon_
			'prefix' 		=> 'dark_product_service_icon_',
			'selector' 		=> 'html[data-theme="dark"] {{WRAPPER}} .product-service-icon',
			
			'section' 	=> [
				'name' 			=> 'dark_product_service_icon_',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Service icon style', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode' 		=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // product_service_name_
			'prefix' 		=> 'dark_product_service_name_',
			'selector' 		=> 'html[data-theme="dark"] {{WRAPPER}} .product-service-name',
			
			'section' 	=> [
				'name' 			=> 'dark_product_service_name_',
				'label' 		=> ElementorControls::dark_control_label( esc_html__( 'Service name style', 'drplus' ) ),
				'condition' 	=> $dark_condition,
			],

			'excludes' 	=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
			'mode' 		=> 'text',
		] );
	}

	protected function render() {		
		get_template_part( 'templates/components/template-components-product-services' );
	}
}