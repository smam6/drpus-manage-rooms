<?php
namespace DrPlus\Elementor\Core;

use DrPlus\ElementorControls;
use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;

class Divider {
	public static function init( $element ) {
		$dark_condition = ElementorControls::dark_condition();
        ElementorControls::dark_mode_toggle_controls( $element );
		self::divider_style( $element, $dark_condition );
		self::text_style( $element, $dark_condition );
		self::icon_style( $element, $dark_condition );
	}

	public static function divider_style( $element, $dark_condition ) {
		$element->start_controls_section(
			'dark_section_divider_style',
			[
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Divider', 'elementor' ) ),
				'tab'		=> Controls_Manager::TAB_STYLE,
				'condition' => [
					'style!' => 'none',
				]+$dark_condition,
			]
		);

		$element->add_control(
			'dark_color',
			[
				'label' => esc_html__( 'Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'global' => [
					'default' => Global_Colors::COLOR_SECONDARY,
				],
				'default' => '#000',
				'render_type' => 'template',
				'selectors' => [
					'html[data-theme="dark"] {{WRAPPER}}' => '--divider-color: {{VALUE}}',
				],
			]
		);

		$element->end_controls_section();
	}

	public static function text_style( $element, $dark_condition ) {
		$element->start_controls_section(
			'dark_section_text_style',
			[
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Text', 'elementor' ) ),
				'tab'		=> Controls_Manager::TAB_STYLE,
				'condition' => [
					'look' => 'line_text',
				]+$dark_condition,
			]
		);

		$element->add_control(
			'dark_text_color',
			[
				'label' => esc_html__( 'Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'global' => [
					'default' => Global_Colors::COLOR_SECONDARY,
				],
				'selectors' => [
					'html[data-theme="dark"] {{WRAPPER}} .elementor-divider__text' => 'color: {{VALUE}}',
				],
			]
		);

		$element->add_group_control(
			\Elementor\Group_Control_Text_Stroke::get_type(),
			[
				'name' => 'dark_text_stroke',
				'selector' => 'html[data-theme="dark"] {{WRAPPER}} .elementor-divider__text',
			]
		);

		$element->end_controls_section();
	}

	public static function icon_style( $element, $dark_condition ) {
		$element->start_controls_section(
			'dark_section_icon_style',
			[
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Icon', 'elementor' ) ),
				'tab'		=> Controls_Manager::TAB_STYLE,
				'condition' => [
					'look' => 'line_icon',
				]+$dark_condition,
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
					'default' => Global_Colors::COLOR_SECONDARY,
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
					'icon_view!' => 'default',
				],
				'selectors' => [
					'html[data-theme="dark"] {{WRAPPER}}.elementor-view-framed .elementor-icon' => 'background-color: {{VALUE}};',
					'html[data-theme="dark"] {{WRAPPER}}.elementor-view-stacked .elementor-icon' => 'color: {{VALUE}};',
					'html[data-theme="dark"] {{WRAPPER}}.elementor-view-stacked .elementor-icon svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$element->end_controls_section();
	}
}
add_action( 'elementor/element/divider/section_divider_style/after_section_end', [Divider::class, 'init'], 10, 2 );