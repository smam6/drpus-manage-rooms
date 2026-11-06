<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
class Breadcrumb extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_breadcrumb';
	}

	public function get_title() {
		return esc_html__( 'Breadcrumb (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-navigation-horizontal';
	}

	public function get_categories() {
		return ['drplus', 'basic'];
	}

	public function get_keywords() {
		return ['breadcrumb', 'مسیریابی'];
	}

	protected function register_controls() {
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'container_',
			'selector'		=> '.breadcrumb-wrap',
			'section'	=> [
				'label'	=> esc_html__( 'Container', 'drplus'),
				'name'	=> 'container',
			],
			'mode'		=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'item_',
			'selector'		=> '.breadcrumb-item',
			'section'	=> [
				'label'	=> esc_html__( 'Item', 'drplus'),
				'name'	=> 'item',
			],
			'mode'		=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'active_item_',
			'selector'		=> '.breadcrumb-item.breadcrumb-item-active',
			'section'	=> [
				'label'	=> esc_html__( 'Active Item', 'drplus'),
				'name'	=> 'active_item',
			],
			'mode'		=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'separator_',
			'selector'		=> '.breadcrumb-separator',
			'section'	=> [
				'label'	=> esc_html__( 'Separator icon', 'drplus'),
				'name'	=> 'separator',
			],
			'mode'		=> 'icon',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );

		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark container_',
			'selector'		=> 'html[data-theme="dark"] .breadcrumb-wrap',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Container', 'drplus') ),
				'name'	=> 'dark container',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark item_',
			'selector'		=> 'html[data-theme="dark"] .breadcrumb-item',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Item', 'drplus') ),
				'name'	=> 'dark item',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark active_item_',
			'selector'		=> 'html[data-theme="dark"] .breadcrumb-item.breadcrumb-item-active',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Active Item', 'drplus') ),
				'name'	=> 'dark active_item',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark separator_',
			'selector'		=> 'html[data-theme="dark"] .breadcrumb-separator',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Separator icon', 'drplus') ),
				'name'	=> 'dark separator',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'icon',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
	}

	protected function render() {	
		drplus_breadcrumb();
	}
}