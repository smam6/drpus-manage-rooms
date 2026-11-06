<?php
namespace DrPlus\Elementor\Core;

use DrPlus\ElementorControls;
use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;

class TextEditor {
	public static function init( $element ) {
		$dark_condition = ElementorControls::dark_condition();
        ElementorControls::dark_mode_toggle_controls( $element );
		self::text( $element, $dark_condition );
		self::drop_cap( $element, $dark_condition );
	}

	public static function text( $element, $dark_condition ) {
		$element->start_controls_section(
			'dark_section_style',
			[
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Text Editor', 'elementor' ) ),
				'tab'		=> Controls_Manager::TAB_STYLE,
				'condition' => $dark_condition,
			]
		);

		$element->add_group_control(
			\Elementor\Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'dark_text_shadow',
				'selector' => 'html[data-theme="dark"] {{WRAPPER}}',
			]
		);

		$element->add_control(
			'dark_separator',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$element->start_controls_tabs( 'dark_link_colors' );

		$element->start_controls_tab(
			'dark_colors_normal',
			[
				'label' => esc_html__( 'Normal', 'elementor' ),
			]
		);

		$element->add_control(
			'dark_text_color',
			[
				'label' => esc_html__( 'Text Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'html[data-theme="dark"] {{WRAPPER}}' => 'color: {{VALUE}};',
				],
				'global' => [
					'default' => Global_Colors::COLOR_TEXT,
				],
			]
		);

		$element->add_control(
			'dark_link_color',
			[
				'label' => esc_html__( 'Link Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'html[data-theme="dark"] {{WRAPPER}} a' => 'color: {{VALUE}};',
				],
			]
		);

		$element->end_controls_tab();

		$element->start_controls_tab(
			'dark_colors_hover',
			[
				'label' => esc_html__( 'Hover', 'elementor' ),
			]
		);

		$element->add_control(
			'dark_link_hover_color',
			[
				'label' => esc_html__( 'Link Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'html[data-theme="dark"] {{WRAPPER}} a:hover, html[data-theme="dark"] {{WRAPPER}} a:focus' => 'color: {{VALUE}};',
				],
			]
		);

		$element->add_control(
			'dark_link_hover_color_transition_duration',
			[
				'label' => esc_html__( 'Transition Duration', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 's', 'ms', 'custom' ],
				'default' => [
					'unit' => 's',
				],
				'selectors' => [
					'html[data-theme="dark"] {{WRAPPER}} a' => 'transition-duration: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$element->end_controls_tab();

		$element->end_controls_tabs();

		$element->end_controls_section();
	}

	public static function drop_cap( $element, $dark_condition ) {
		$element->start_controls_section(
			'dark_section_drop_cap',
			[
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Drop Cap', 'elementor' ) ),
				'tab'		=> Controls_Manager::TAB_STYLE,
				'condition'	=> [
					'drop_cap' => 'yes',
				]+$dark_condition,
			]
		);

		$element->add_control(
			'dark_drop_cap_view',
			[
				'label' => esc_html__( 'View', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'default' => esc_html__( 'Default', 'elementor' ),
					'stacked' => esc_html__( 'Stacked', 'elementor' ),
					'framed' => esc_html__( 'Framed', 'elementor' ),
				],
				'default' => 'default',
				'prefix_class' => 'elementor-drop-cap-view-',
			]
		);

		$element->add_control(
			'dark_drop_cap_primary_color',
			[
				'label' => esc_html__( 'Primary Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'html[data-theme="dark"] {{WRAPPER}}.elementor-drop-cap-view-stacked .elementor-drop-cap' => 'background-color: {{VALUE}};',
					'html[data-theme="dark"] {{WRAPPER}}.elementor-drop-cap-view-framed .elementor-drop-cap, {{WRAPPER}}.elementor-drop-cap-view-default .elementor-drop-cap' => 'color: {{VALUE}}; border-color: {{VALUE}};',
				],
				'global' => [
					'default' => Global_Colors::COLOR_PRIMARY,
				],
			]
		);

		$element->add_control(
			'dark_drop_cap_secondary_color',
			[
				'label' => esc_html__( 'Secondary Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'html[data-theme="dark"] {{WRAPPER}}.elementor-drop-cap-view-framed .elementor-drop-cap' => 'background-color: {{VALUE}};',
					'html[data-theme="dark"] {{WRAPPER}}.elementor-drop-cap-view-stacked .elementor-drop-cap' => 'color: {{VALUE}};',
				],
				'condition' => [
					'drop_cap_view!' => 'default',
				],
			]
		);

		$element->add_group_control(
			\Elementor\Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'dark_drop_cap_shadow',
				'selector' => 'html[data-theme="dark"] {{WRAPPER}} .elementor-drop-cap',
			]
		);

		$element->end_controls_section();
	}
}
add_action( 'elementor/element/text-editor/section_style/after_section_end', [TextEditor::class, 'init'], 10, 2 );