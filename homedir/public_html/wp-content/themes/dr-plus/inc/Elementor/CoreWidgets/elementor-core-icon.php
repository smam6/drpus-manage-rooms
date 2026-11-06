<?php
namespace DrPlus\Elementor\Core;

use DrPlus\ElementorControls;
use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;

class Icon {
	public static function init( $element ) {
		$dark_condition = ElementorControls::dark_condition();
        ElementorControls::dark_mode_toggle_controls( $element );
		self::style( $element, $dark_condition );
	}

	public static function style( $element, $dark_condition ) {
		$element->start_controls_section(
			'dark_section_style_icon',
			[
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Icon', 'elementor' ) ),
				'tab'		=> Controls_Manager::TAB_STYLE,
				'condition' => $dark_condition,
			]
		);

		$element->start_controls_tabs( 'dark_icon_colors' );

		$element->start_controls_tab(
			'dark_icon_colors_normal',
			[
				'label' => esc_html__( 'Normal', 'elementor' ),
			]
		);

		$element->add_control(
			'dark_primary_color',
			[
				'label' => esc_html__( 'Primary Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'html[data-theme="dark"] {{WRAPPER}}.elementor-view-stacked .elementor-icon' => 'background-color: {{VALUE}};',
					'html[data-theme="dark"] {{WRAPPER}}.elementor-view-framed .elementor-icon, html[data-theme="dark"] {{WRAPPER}}.elementor-view-default .elementor-icon' => 'color: {{VALUE}}; border-color: {{VALUE}};',
					'html[data-theme="dark"] {{WRAPPER}}.elementor-view-framed .elementor-icon, html[data-theme="dark"] {{WRAPPER}}.elementor-view-default .elementor-icon svg' => 'fill: {{VALUE}};',
				],
				'global' => [
					'default' => Global_Colors::COLOR_PRIMARY,
				],
			]
		);

		$element->add_control(
			'dark_secondary_color',
			[
				'label' => esc_html__( 'Secondary Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'condition' => [
					'view!' => 'default',
				],
				'selectors' => [
					'html[data-theme="dark"] {{WRAPPER}}.elementor-view-framed .elementor-icon' => 'background-color: {{VALUE}};',
					'html[data-theme="dark"] {{WRAPPER}}.elementor-view-stacked .elementor-icon' => 'color: {{VALUE}};',
					'html[data-theme="dark"] {{WRAPPER}}.elementor-view-stacked .elementor-icon svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$element->end_controls_tab();

		$element->start_controls_tab(
			'dark_icon_colors_hover',
			[
				'label' => esc_html__( 'Hover', 'elementor' ),
			]
		);

		$element->add_control(
			'dark_hover_primary_color',
			[
				'label' => esc_html__( 'Primary Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'html[data-theme="dark"] {{WRAPPER}}.elementor-view-stacked .elementor-icon:hover' => 'background-color: {{VALUE}};',
					'html[data-theme="dark"] {{WRAPPER}}.elementor-view-framed .elementor-icon:hover, html[data-theme="dark"] {{WRAPPER}}.elementor-view-default .elementor-icon:hover' => 'color: {{VALUE}}; border-color: {{VALUE}};',
					'html[data-theme="dark"] {{WRAPPER}}.elementor-view-framed .elementor-icon:hover, html[data-theme="dark"] {{WRAPPER}}.elementor-view-default .elementor-icon:hover svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$element->add_control(
			'dark_hover_secondary_color',
			[
				'label' => esc_html__( 'Secondary Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'condition' => [
					'view!' => 'default',
				],
				'selectors' => [
					'html[data-theme="dark"] {{WRAPPER}}.elementor-view-framed .elementor-icon:hover' => 'background-color: {{VALUE}};',
					'html[data-theme="dark"] {{WRAPPER}}.elementor-view-stacked .elementor-icon:hover' => 'color: {{VALUE}};',
					'html[data-theme="dark"] {{WRAPPER}}.elementor-view-stacked .elementor-icon:hover svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$element->end_controls_tab();

		$element->end_controls_tabs();

		$element->end_controls_section();
		
	}
}
add_action( 'elementor/element/icon/section_style_icon/after_section_end', [Icon::class, 'init'], 10, 2 );