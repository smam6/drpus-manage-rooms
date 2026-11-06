<?php
namespace DrPlus\Elementor\Core;

use DrPlus\ElementorControls;
use Elementor\Controls_Manager;

class Container {
	public static function init( $element ) {
		$dark_condition = ElementorControls::dark_condition();
        ElementorControls::dark_mode_toggle_controls( $element );
		self::background( $element, $dark_condition );
		self::background_overlay( $element, $dark_condition );
		self::border( $element, $dark_condition );
	}

	public static function background( $element ,$dark_condition ) {
		$element->start_controls_section(
			'dark_section_background',
			[
				'label'		=> ElementorControls::dark_control_label( esc_html__( 'Background', 'elementor' ) ),
				'tab'		=> Controls_Manager::TAB_STYLE,
				'condition' => $dark_condition,
			]
		);

		$element->start_controls_tabs( 'dark_tabs_background' );

		/**
		 * Normal.
		 */
		$element->start_controls_tab(
			'dark_tab_background_normal',
			[
				'label' => esc_html__( 'Normal', 'elementor' ),
			]
		);

		$element->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'dark_background',
				'types' => [ 'classic', 'gradient', 'video', 'slideshow' ],
				'fields_options' => [
					'background' => [
						'frontend_available' => true,
					],
				],
				'selector'	=> 'html[data-theme="dark"] {{WRAPPER}}'
			]
		);

		$element->add_control(
			'dark_handle_slideshow_asset_loading',
			[
				'type' => Controls_Manager::HIDDEN,
				'assets' => [
					'styles' => [
						[
							'name' => 'e-swiper',
							'conditions' => [
								'terms' => [
									[
										'name' => 'dark_background_background',
										'operator' => '===',
										'value' => 'slideshow',
									],
								],
							],
						],
					],
					'scripts' => [
						[
							'name' => 'swiper',
							'conditions' => [
								'terms' => [
									[
										'name' => 'dark_background_background',
										'operator' => '===',
										'value' => 'slideshow',
									],
								],
							],
						],
					],
				],
			]
		);

		$element->end_controls_tab();

		/**
		 * Hover.
		 */
		$element->start_controls_tab(
			'dark_tab_background_hover',
			[
				'label' => esc_html__( 'Hover', 'elementor' ),
			]
		);

		$element->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'dark_background_hover',
				'selector' => 'html[data-theme="dark"] {{WRAPPER}}:hover',
			]
		);

		$element->end_controls_tab();

		$element->end_controls_tabs();

		$element->end_controls_section();
	}

	public static function background_overlay( $element, $dark_condition ) {
		$element->start_controls_section(
			'dark_section_background_overlay',
			[
				'label' => ElementorControls::dark_control_label( esc_html__( 'Background Overlay', 'elementor' ) ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' 	=> $dark_condition,
			]
		);

		$element->start_controls_tabs( 'dark_tabs_background_overlay' );

		/**
		 * Normal.
		 */
		$element->start_controls_tab(
			'dark_tab_background_overlay',
			[
				'label' => esc_html__( 'Normal', 'elementor' ),
			]
		);

		$background_overlay_selector = 'html[data-theme="dark"] {{WRAPPER}}::before, html[data-theme="dark"] {{WRAPPER}} > .elementor-background-video-container::before, html[data-theme="dark"] {{WRAPPER}} > .e-con-inner > .elementor-background-video-container::before, html[data-theme="dark"] {{WRAPPER}} > .elementor-background-slideshow::before, html[data-theme="dark"] {{WRAPPER}} > .e-con-inner > .elementor-background-slideshow::before, html[data-theme="dark"] {{WRAPPER}} > .elementor-motion-effects-container > .elementor-motion-effects-layer::before';

		$element->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'dark_background_overlay',
				'selector' => $background_overlay_selector,
				'fields_options' => [
					'background' => [
						'selectors' => [
							// Hack to set the `::before` content in order to render it only when there is a background overlay.
							$background_overlay_selector => '--background-overlay: \'\';',
						],
					],
				],
			]
		);

		$element->add_responsive_control(
			'dark_background_overlay_opacity',
			[
				'label' => esc_html__( 'Opacity', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => .5,
				],
				'range' => [
					'px' => [
						'max' => 1,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'html[data-theme="dark"] {{WRAPPER}}' => '--overlay-opacity: {{SIZE}};',
				],
				'condition' => [
					'background_overlay_background' => [ 'classic', 'gradient' ],
				],
			]
		);

		$element->add_group_control(
			\Elementor\Group_Control_Css_Filter::get_type(),
			[
				'name' => 'dark_css_filters',
				'selector' => 'html[data-theme="dark"] {{WRAPPER}}::before',
				'conditions' => [
					'relation' => 'or',
					'terms' => [
						[
							'name' => 'background_overlay_image[url]',
							'operator' => '!==',
							'value' => '',
						],
						[
							'name' => 'background_overlay_color',
							'operator' => '!==',
							'value' => '',
						],
					],
				],
			]
		);

		$element->add_control(
			'dark_overlay_blend_mode',
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
					'luminosity' => esc_html__( 'Luminosity', 'elementor' ),
				],
				'selectors' => [
					'html[data-theme="dark"] {{WRAPPER}}' => '--overlay-mix-blend-mode: {{VALUE}}',
				],
				'conditions' => [
					'relation' => 'or',
					'terms' => [
						[
							'name' => 'dark_background_overlay_image[url]',
							'operator' => '!==',
							'value' => '',
						],
						[
							'name' => 'dark_background_overlay_color',
							'operator' => '!==',
							'value' => '',
						],
					],
				],
			]
		);

		$element->end_controls_tab();

		/**
		 * Hover.
		 */
		$element->start_controls_tab(
			'dark_tab_background_overlay_hover',
			[
				'label' => esc_html__( 'Hover', 'elementor' ),
			]
		);

		$background_overlay_hover_selector = 'html[data-theme="dark"] {{WRAPPER}}:hover::before, html[data-theme="dark"] {{WRAPPER}}:hover > .elementor-background-video-container::before, html[data-theme="dark"] {{WRAPPER}}:hover > .e-con-inner > .elementor-background-video-container::before, html[data-theme="dark"] {{WRAPPER}} > .elementor-background-slideshow:hover::before, html[data-theme="dark"] {{WRAPPER}} > .e-con-inner > .elementor-background-slideshow:hover::before';

		$element->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'dark_background_overlay_hover',
				'selector' => $background_overlay_hover_selector,
				'fields_options' => [
					'background' => [
						'selectors' => [
							// Hack to set the `::before` content in order to render it only when there is a background overlay.
							$background_overlay_hover_selector => '--background-overlay: \'\';',
						],
					],
				],
			]
		);

		$element->add_responsive_control(
			'dark_background_overlay_hover_opacity',
			[
				'label' => esc_html__( 'Opacity', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => .5,
				],
				'range' => [
					'px' => [
						'max' => 1,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'html[data-theme="dark"] {{WRAPPER}}:hover' => '--overlay-opacity: {{SIZE}};',
				],
				'condition' => [
					'dark_background_overlay_hover_background' => [ 'classic', 'gradient' ],
				],
			]
		);

		$element->add_control(
			'dark_background_overlay_hover_transition',
			[
				'label' => esc_html__( 'Transition Duration', 'elementor' ) . ' (s)',
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 3,
						'step' => 0.1,
					],
				],
				'render_type' => 'ui',
				'separator' => 'before',
				'condition' => [
					'dark_background_overlay_hover_background' => [ 'classic', 'gradient' ],
				],
				'selectors' => [
					'html[data-theme="dark"] {{WRAPPER}}, html[data-theme="dark"] {{WRAPPER}}::before' => '--overlay-transition: {{SIZE}}s;',
				],
			]
		);

		$element->add_group_control(
			\Elementor\Group_Control_Css_Filter::get_type(),
			[
				'name' => 'dark_css_filters_hover',
				'selector' => 'html[data-theme="dark"] {{WRAPPER}}:hover::before',
			]
		);

		$element->end_controls_tab();

		$element->end_controls_tabs();

		$element->end_controls_section();
	}

	public static function border( $element, $dark_condition ) {
		$element->start_controls_section(
			'dark_section_border',
			[
				'label' => ElementorControls::dark_control_label( esc_html__( 'Border', 'elementor' ) ),
				'tab' 	=> Controls_Manager::TAB_STYLE,
				'condition'	=> $dark_condition
			]
		);

		$element->start_controls_tabs( 'dark_tabs_border' );

		/**
		 * Normal.
		 */
		$element->start_controls_tab(
			'dark_tab_border',
			[
				'label' => esc_html__( 'Normal', 'elementor' ),
			]
		);

		$element->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'dark_border',
				'selector' => 'html[data-theme="dark"] {{WRAPPER}}',
				'fields_options' => [
					'width' => [
						'selectors' => [
							'{{SELECTOR}}' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; --border-top-width: {{TOP}}{{UNIT}}; --border-right-width: {{RIGHT}}{{UNIT}}; --border-bottom-width: {{BOTTOM}}{{UNIT}}; --border-left-width: {{LEFT}}{{UNIT}};',
						],
					],
					'color' => [
						'selectors' => [
							'{{SELECTOR}}' => 'border-color: {{VALUE}}; --border-color: {{VALUE}};',
						],
					],
					'border' => [
						'selectors' => [
							'{{SELECTOR}}' => 'border-style: {{VALUE}}; --border-style: {{VALUE}};',
						],
					],
				],
			]
		);

		$element->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'dark_box_shadow',
				'selector'	=> 'html[data-theme="dark"] {{WRAPPER}}'
			]
		);

		$element->end_controls_tab();

		/**
		 * Hover.
		 */
		$element->start_controls_tab(
			'dark_tab_border_hover',
			[
				'label' => esc_html__( 'Hover', 'elementor' ),
			]
		);

		$element->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'dark_border_hover',
				'selector' => 'html[data-theme="dark"] {{WRAPPER}}:hover',
				'fields_options' => [
					'width' => [
						'selectors' => [
							'{{SELECTOR}}' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; --border-top-width: {{TOP}}{{UNIT}}; --border-right-width: {{RIGHT}}{{UNIT}}; --border-bottom-width: {{BOTTOM}}{{UNIT}}; --border-left-width: {{LEFT}}{{UNIT}};',
						],
					],
					'color' => [
						'selectors' => [
							'{{SELECTOR}}' => 'border-color: {{VALUE}}; --border-color: {{VALUE}};',
						],
					],
				],
			]
		);

		$element->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'dark_box_shadow_hover',
				'selector' => 'html[data-theme="dark"] {{WRAPPER}}:hover',
			]
		);

		$element->add_control(
			'dark_border_hover_transition',
			[
				'label' => esc_html__( 'Transition Duration', 'elementor' ) . ' (s)',
				'type' => Controls_Manager::SLIDER,
				'separator' => 'before',
				'default' => [
					'size' => 0.3,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 3,
						'step' => 0.1,
					],
				],
				'conditions' => [
					'relation' => 'or',
					'terms' => [
						[
							'name' => 'border_hover_border',
							'operator' => '!==',
							'value' => '',
						],
						[
							'name' => 'border_radius_hover[top]',
							'operator' => '!==',
							'value' => '',
						],
						[
							'name' => 'border_radius_hover[right]',
							'operator' => '!==',
							'value' => '',
						],
						[
							'name' => 'border_radius_hover[bottom]',
							'operator' => '!==',
							'value' => '',
						],
						[
							'name' => 'border_radius_hover[left]',
							'operator' => '!==',
							'value' => '',
						],
					],
				],
				'selectors' => [
					'html[data-theme="dark"] {{WRAPPER}}, html[data-theme="dark"] {{WRAPPER}}::before' => '--border-transition: {{SIZE}}s;',
				],
			]
		);

		$element->end_controls_tab();

		$element->end_controls_tabs();

		$element->end_controls_section();
	}
}
add_action( 'elementor/element/container/section_shape_divider/after_section_end', [Container::class, 'init'], 10, 2 );