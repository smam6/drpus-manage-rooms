<?php
namespace DrPlus\Elementor\Core;

use DrPlus\ElementorControls;
use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;

class Heading {
	public static function init( $element ) {
		$dark_condition = ElementorControls::dark_condition();
        ElementorControls::dark_mode_toggle_controls( $element );
		self::style( $element, $dark_condition );
	}

	public static function style( $element, $dark_condition ) {
		$element->start_controls_section(
			'dark_section_title_style',
			[
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Heading', 'elementor' ) ),
				'tab'		=> Controls_Manager::TAB_STYLE,
				'condition' => $dark_condition,
			]
		);

		$element->add_group_control(
			\Elementor\Group_Control_Text_Stroke::get_type(),
			[
				'name' => 'dark_text_stroke',
				'selector' => 'html[data-theme="dark"] {{WRAPPER}} .elementor-heading-title',
			]
		);

		$element->add_group_control(
			\Elementor\Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'dark_text_shadow',
				'selector' => 'html[data-theme="dark"] {{WRAPPER}} .elementor-heading-title',
			]
		);

		$element->add_control(
			'dark_blend_mode',
			[
				'label' => esc_html__( 'Blend Mode', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'' => esc_html__( 'Normal', 'elementor' ),
					'multiply' => esc_html__( 'Multiply', 'elementor' ),
					'screen' => esc_html__( 'Screen', 'elementor' ),
					'overlay' => esc_html__( 'Overlay', 'elementor' ),
					'darken' => esc_html__( 'Darken', 'elementor' ),
					'lighten' => esc_html__( 'Lighten', 'elementor' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'elementor' ),
					'saturation' => esc_html__( 'Saturation', 'elementor' ),
					'color' => esc_html__( 'Color', 'elementor' ),
					'difference' => esc_html__( 'Difference', 'elementor' ),
					'exclusion' => esc_html__( 'Exclusion', 'elementor' ),
					'hue' => esc_html__( 'Hue', 'elementor' ),
					'luminosity' => esc_html__( 'Luminosity', 'elementor' ),
				],
				'selectors' => [
					'html[data-theme="dark"] {{WRAPPER}} .elementor-heading-title' => 'mix-blend-mode: {{VALUE}}',
				],
			]
		);

		$element->add_control(
			'dark_separator',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$element->start_controls_tabs( 'dark_title_colors' );

		$element->start_controls_tab(
			'dark_title_colors_normal',
			[
				'label' => esc_html__( 'Normal', 'elementor' ),
			]
		);

		$element->add_control(
			'dark_title_color',
			[
				'label' => esc_html__( 'Text Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'global' => [
					'default' => Global_Colors::COLOR_PRIMARY,
				],
				'selectors' => [
					'html[data-theme="dark"] {{WRAPPER}} .elementor-heading-title' => 'color: {{VALUE}};',
				],
			]
		);

		$element->end_controls_tab();

		$element->start_controls_tab(
			'dark_title_colors_hover',
			[
				'label' => esc_html__( 'Hover', 'elementor' ),
			]
		);

		$element->add_control(
			'dark_title_hover_color',
			[
				'label' => esc_html__( 'Link Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'html[data-theme="dark"] {{WRAPPER}} .elementor-heading-title a:hover, {{WRAPPER}} .elementor-heading-title a:focus' => 'color: {{VALUE}};',
				],
			]
		);

		$element->add_control(
			'dark_title_hover_color_transition_duration',
			[
				'label' => esc_html__( 'Transition Duration', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 's', 'ms', 'custom' ],
				'default' => [
					'unit' => 's',
				],
				'selectors' => [
					'html[data-theme="dark"] {{WRAPPER}} .elementor-heading-title a' => 'transition-duration: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$element->end_controls_tab();

		$element->end_controls_tabs();

		$element->end_controls_section();
	}
}
add_action( 'elementor/element/heading/section_title_style/after_section_end', [Heading::class, 'init'], 10, 2 );