<?php
namespace MJ\Whitebox;

use MJ\Whitebox\Utils\Elementor;

if( !defined( 'ABSPATH' ) ) exit;

if( isset( $_GET['elementor_updater'] ) ) return;

class ElementorControls {
	protected static function prepare_args( $args, $default_controls ) {
		if( !empty( $args['only'] ) ) {
			foreach( array_keys( $default_controls ) as $default_control ) {
				if( !in_array( $default_control, $args['only'] ) ) {
					$args['excludes'][] = $default_control;
					if( isset( $args['hover_excludes'] ) ) {
						$args['hover_excludes'][] = $default_control;
					}
				}
			}
		}

		foreach( array_keys( $default_controls ) as $index => $control_name ) {
			if( !in_array( $control_name, $args['excludes'] ) ) {
				Utils::reposition_array_element( $args['controls'], $control_name, $index );
			}
		}

		// Reposition controls
		foreach( $args['controls'] as $control_name => $control_args ) {
			if( isset( $control_args['_position'] ) ) {
				Utils::reposition_array_element( $args['controls'], $control_name, $control_args['_position'] );
			}
		}

		return $args;
	}

	/**
	 * Helper function: Add controls to an Elementor widget, handling exclusions and responsive settings.
	 *
	 * Repositions controls based on default order or custom positions, 
	 * then adds them using add_control or add_responsive_control.
	 *
	 * @param object $object Elementor widget instance.
	 * @param array $default_controls Default controls to add.
	 * @param string $prefix Optional prefix for control names.
	 * @param array $args Optional arguments like 'excludes' and custom 'controls'.
	 *
	 * @return void
	 */
	protected static function _add_controls( $object, $default_controls, $prefix, $args = [] ) {
		$args = Utils::check_default( $args, [
			'excludes'	=> [],
			'only'		=> [],
			'controls'	=> $default_controls
		] );

		$args = self::prepare_args( $args, $default_controls );

		foreach( $args['controls'] as $control_name => $control_args ) {
			if( !in_array( $control_name, $args['excludes'] ) ) {
				if( empty( $control_args['_responsive'] ) ) {
					$object->add_control(
						$prefix . $control_name,
						$control_args
					);
				} else {
					$object->add_responsive_control(
						$prefix . $control_name,
						$control_args
					);
				}
			}
		}
	}

	/**
	 * Adds a general style controls section to an Elementor widget.
	 *
	 * This function allows you to add a set of reusable style controls (margin, padding, color, background,
	 * typography, text_align, icon_size, border, border_radius, box_shadow, text_shadow, etc.) to an Elementor
	 * widget with support for normal and hover states. It also supports custom modes such as svg, icon, text, wrapper, image, and input.
	 *
	 * @param \Elementor\Widget_Base $object The Elementor widget instance to add controls to.
	 * @param array $args An array of arguments to customize the controls:
	 *     - prefix (string)             : Prefix for control names.
	 *     - base_selector (string)      : Base CSS selector for the controls.
	 *     - selector (string)           : Optional additional selector appended to base_selector.
	 *     - hover_selector (string|false): Optional hover selector. Set false to disable hover controls.
	 *     - hover_type (string)         : 'base' to use base_selector for hover, 'normal' to use selector.
	 *     - section (array)             : Array with 'name' and 'label' for the controls section.
	 *     - tabs (array)                : Array defining Normal and Hover tab labels.
	 *     - excludes (array)            : Controls to exclude from Normal tab.
	 *     - hover_excludes (array)      : Controls to exclude from Hover tab.
	 *     - controls (array)            : Custom control arguments for each style control.
	 *     - mode (string)               : Optional mode for special behavior ('svg', 'icon', 'text', 'wrapper', 'img', 'input').
	 *
	 * Example usage:
	 *
	 * self::general_style_controls( $object, [
	 *     'prefix'        => 'card_',
	 *     'base_selector' => '.specialist-card',
	 *     'section'       => [
	 *         'name'  => 'card_',
	 *         'label' => esc_html__( 'Specialist card', 'mj-whitebox' ),
	 *     ],
	 *     'mode' => 'wrap',
	 * ] );
	 *
	 * self::general_style_controls( $object, [
	 *     'prefix'        => 'image_',
	 *     'base_selector' => '.specialist-card',
	 *     'selector'      => '.specialist-avatar-wrap img',
	 *     'section'       => [
	 *         'name'  => 'image_',
	 *         'label' => esc_html__( 'Specialist image', 'mj-whitebox' ),
	 *     ],
	 *     'mode' => 'image',
	 * ] );
	 *
	 * self::general_style_controls( $object, [
	 *     'prefix'        => 'name_',
	 *     'base_selector' => '.specialist-card',
	 *     'selector'      => '.specialist-name',
	 *     'section'       => [
	 *         'name'  => 'name_',
	 *         'label' => esc_html__( 'Specialist name', 'mj-whitebox' ),
	 *     ],
	 *     'mode' => 'text',
	 * ] );
	 *
	 * @return void
	 */
	public static function general_style_controls( $object, array $args ) {
		// You can add other args for each control when you set $args['controls']
		// If selector of each control is not set, it will use $args['base_selector'] . " " . $args['selector']
		$default_control_args = [
			'selector'			=> '',
			'hover_selector'	=> '',
		];
		$default_controls = [
			'margin'		=> $default_control_args,
			'padding'		=> $default_control_args,
			'background'	=> $default_control_args,
			'color'			=> $default_control_args,
			'typography'	=> $default_control_args,
			'text_align'	=> $default_control_args,
			'icon_size'		=> $default_control_args,
			'border'		=> $default_control_args,
			'border_radius'	=> $default_control_args,
			'box_shadow'	=> $default_control_args,
			'text_shadow'	=> $default_control_args,
		];

		$args = Utils::check_default( $args, [
			'prefix'			=> '', // Required
			'base_selector'		=> '',
			'selector'			=> '',
			'hover_selector'	=> '', // Set this to false to disable hover
			'hover_type'		=> 'base', // base: The hover selector will set to base. normal: The hover selector will set to selector
			
			'section'	=> [ // Required
				'name'	=> '',
				'label'	=> '',
			],
			'tabs'		=> [
				'normal'	=> [
					'label'	=> esc_html__( 'Normal', 'mj-whitebox' ),
				],
				'hover'	=> [
					'label'	=> esc_html__( 'Hover', 'mj-whitebox' ),
				],
			],

			'excludes'			=> [],
			'hover_excludes'	=> [],
			'only'				=> [],
			'controls'			=> $default_controls,

			'mode'	=> '', // svg, icon, wrapper(wrap), text, img(image)
		], ['hover_selector']);

		if( empty( $args['base_selector'] ) && !empty( $args['selector'] ) ) {
			$args['base_selector'] = $args['selector'];
			$args['selector'] = '';
		}

		$base_selector = Elementor::get_wrapper_selector( $args['base_selector'] );
		$selector = "{$base_selector} {$args['selector']}";
		$hover_selector = '';
		$prefix = $args['prefix'];
		if( isset( $args['hover_selector'] ) ) {
			if( $args['hover_selector'] !== false && $args['hover_selector'] === '' ) {
				if( $args['hover_type'] == 'base' ) {
					$hover_selector = "{$base_selector}:hover {$args['selector']}";
				} else {
					$hover_selector = "{$selector}:hover";
				}
			} else {
				$hover_selector = $args['hover_selector'];
			}
			if( !empty( $hover_selector ) ) {
				$hover_selector = Elementor::get_wrapper_selector( $hover_selector );
			}
		}

		// Set custom modes
		if( $args['mode'] == 'svg' ) {
			$selector = "{$selector} > svg path";
			$hover_selector = "{$hover_selector} > svg path";
			$args['excludes'] = array_unique( array_merge( $args['excludes'], ['padding', 'margin', 'background', 'typography', 'icon_size', 'border', 'border_radius', 'box_shadow', 'text_shadow']) );
			$args['hover_excludes'] = array_unique( array_merge( $args['hover_excludes'], ['padding', 'margin', 'background', 'typography', 'icon_size', 'border', 'border_radius', 'box_shadow', 'text_shadow']) );
		} else if( $args['mode'] == 'icon' ) {
			$args['excludes'] = array_unique( array_merge( $args['excludes'], ['typography']) );
			$args['hover_excludes'] = array_unique( array_merge( $args['hover_excludes'], ['typography']) );
		} else if( $args['mode'] == 'text' ) {
			$args['excludes'] = array_unique( array_merge( $args['excludes'], ['icon_size']) );
			$args['hover_excludes'] = array_unique( array_merge( $args['hover_excludes'], ['icon_size']) );
		} else if( $args['mode'] == 'wrapper' || $args['mode'] == 'wrap' ) {
			$args['excludes'] = array_unique( array_merge( $args['excludes'], ['color', 'typography', 'icon_size', 'text_shadow']) );
			$args['hover_excludes'] = array_unique( array_merge( $args['hover_excludes'], ['color', 'typography', 'icon_size', 'text_shadow']) );
		} else if( $args['mode'] == 'img' || $args['mode'] == 'image' ) {
			$args['excludes'] = array_unique( array_merge( $args['excludes'], ['color', 'typography', 'icon_size', 'text_shadow']) );
			$args['hover_excludes'] = array_unique( array_merge( $args['hover_excludes'], ['color', 'typography', 'icon_size', 'text_shadow']) );
			if( !isset( $args['controls']['css_filters'] ) ) {
				$args['controls']['css_filters'] = $default_control_args;
			} else {
				$args['controls']['css_filters'] = Utils::check_default( $args['controls']['css_filters'], $default_control_args );
			}
			if( !isset( $args['controls']['width'] ) ) {
				$args['controls']['width'] = $default_control_args;
			} else {
				$args['controls']['width'] = Utils::check_default( $args['controls']['width'], $default_control_args );
			}
			if( !isset( $args['controls']['height'] ) ) {
				$args['controls']['height'] = $default_control_args;
			} else {
				$args['controls']['height'] = Utils::check_default( $args['controls']['height'], $default_control_args );
			}
		} else if( $args['mode'] == 'input' ) {
			$args['excludes'] = array_unique( array_merge( $args['excludes'], ['icon_size', 'text_shadow']) );
			$args['hover_excludes'] = array_unique( array_merge( $args['hover_excludes'], ['icon_size', 'text_shadow']) );
			if( !isset( $args['controls']['placeholder_color'] ) ) {
				$args['controls']['placeholder_color'] = $default_control_args;
			} else {
				$args['controls']['placeholder_color'] = Utils::check_default( $args['controls']['placeholder_color'], $default_control_args );
			}
		}

		$section_args = [
			'label'	=> $args['section']['label'],
			'tab'	=> \Elementor\Controls_Manager::TAB_STYLE,
		];
		if( !empty( $args['section']['condition'] ) ) {
			$section_args['condition'] = $args['section']['condition'];
		}
		if( !empty( $args['section']['conditions'] ) ) {
			$section_args['conditions'] = $args['section']['conditions'];
		}
		$object->start_controls_section( // content_section
			"style_{$args['section']['name']}",
			$section_args
		);

		if( !empty( $hover_selector ) ) {
			$object->start_controls_tabs( "tabs_{$prefix}style" );

			$object->start_controls_tab( // Normal
				"tab_{$prefix}normal",
				[
					'label'	=> $args['tabs']['normal']['label'],
				]
			);
		}

		$args = self::prepare_args( $args, $default_controls );

		foreach( $args['controls'] as $control_name => $control_args ) {
			if( !in_array( $control_name, $args['excludes'] ) ) {
				if( method_exists( self::class, $control_name ) ) {
					$control_selector = empty( $control_args['selector'] ) ? $selector : $control_args['selector'];
					if( isset( $control_args['selector'] ) ) {
						unset( $control_args['selector'] );
					}
					if( $args['mode'] == 'svg' ) {
						if( $control_name == 'color' ) {
							$control_args['selectors'] = [
								$control_selector	=> 'fill: {{VALUE}};',
							];
						}
					}
					self::{$control_name}( $object, $prefix . $control_name, $control_selector, $control_args );
				} else {
					if( empty( $control_args['_responsive'] ) ) {
						$object->add_control(
							$prefix . $control_name,
							$control_args
						);
					} else {
						$object->add_responsive_control(
							$prefix . $control_name,
							$control_args
						);
					}
				}
			}
		}

		if( !empty( $hover_selector ) ) {
			$object->end_controls_tab();

			$object->start_controls_tab( // Hover
				"tab_{$prefix}hover",
				[
					'label' => $args['tabs']['hover']['label'],
				]
			);

			foreach( $args['controls'] as $control_name => $control_args ) {
				if( !in_array( $control_name, $args['hover_excludes'] ) ) {
					if( method_exists( self::class, $control_name ) ) {
						$control_hover_selector = empty( $control_args['hover_selector'] ) ? $hover_selector : $control_args['hover_selector'];
						if( isset( $control_args['selector'] ) ) {
							unset( $control_args['selector'] );
						}
						if( $args['mode'] == 'svg' ) {
							if( $control_name == 'color' ) {
								$control_args['selectors'] = [
									$control_hover_selector	=> 'fill: {{VALUE}};',
								];
							}
						}
						self::{$control_name}( $object, "{$prefix}{$control_name}_hover", $control_hover_selector, $control_args );
					} else {
						if( !empty( $control_args['hover_selectors'] ) ) {
							$control_args['selectors'] = $control_args['hover_selectors'];
							unset( $control_args['hover_selectors'] );
						}
						if( empty( $control_args['_responsive'] ) ) {
							$object->add_control(
								"{$prefix}{$control_name}_hover",
								$control_args
							);
						} else {
							$object->add_responsive_control(
								"{$prefix}{$control_name}_hover",
								$control_args
							);
						}
					}
				}
			}

			$object->end_controls_tab();
			$object->end_controls_tabs();
		}

		$object->end_controls_section();
	}

	public static function margin( $object, $id, $selector, $args = [] ) {
		$args = Utils::check_default( $args, [
			'label'			=> esc_html__( 'Margin', 'mj-whitebox' ),
			'size_units'	=> [ 'px', '%', 'em', 'rem', 'custom' ],
			'selectors'		=> [
				$selector	=> 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );
		$args['type'] = \Elementor\Controls_Manager::DIMENSIONS;
		$object->add_responsive_control( $id, $args );
	}

	public static function padding( $object, $id, $selector, $args = [] ) {
		$args = Utils::check_default( $args, [
			'label'			=> esc_html__( 'Padding', 'mj-whitebox' ),
			'size_units'	=> [ 'px', '%', 'em', 'rem', 'custom' ],
			'selectors'		=> [
				$selector	=> 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );
		$args['type'] = \Elementor\Controls_Manager::DIMENSIONS;
		$object->add_responsive_control( $id, $args );
	}

	public static function typography( $object, $id, $selector, $args = [] ) {
		$args = Utils::check_default( $args, [
			'selector'	=> $selector,
		] );
		$args['name'] = $id;
		$object->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			$args
		);
	}

	public static function icon_size( $object, $id, $selector ) {
		$args = [
			'exclude'	=> [
				'font_weight',
				'font_family',
				'font_style',
				'text_decoration',
				'letter_spacing',
				'line_height',
				'letter_spacing',
				'text_transform',
				'word_spacing',
			]
		];
		self::typography( $object, $id, $selector, $args );
	}

	public static function text_align( $object, $id, $selector, $args = [] ) {
		$args = Utils::check_default( $args, [
			'label'			=> esc_html__( 'Text align', 'mj-whitebox' ),
			'options'		=> [
				'left'		=> [
					'title'	=> esc_html__( 'Left', 'mj-whitebox' ),
					'icon'	=> 'eicon-text-align-left',
				],
				'center'	=> [
					'title'	=> esc_html__( 'Center', 'mj-whitebox' ),
					'icon'	=> 'eicon-text-align-center',
				],
				'right'		=> [
					'title'	=> esc_html__( 'Right', 'mj-whitebox' ),
					'icon'	=> 'eicon-text-align-right',
				],
				'justify'	=> [
					'title'	=> esc_html__( 'Justify', 'mj-whitebox' ),
					'icon'	=> 'eicon-text-align-justify',
				],
			],
			'selectors'		=> [
				$selector	=> 'text-align: {{VALUE}};'
			],
		] );
		$args['type'] = \Elementor\Controls_Manager::CHOOSE;
		$object->add_responsive_control( $id, $args );
	}

	public static function color( $object, $id, $selector, $args = [] ) {
		$args = Utils::check_default( $args, [
			'label'			=> esc_html__( 'Color', 'mj-whitebox' ),
			'selectors'		=> [
				$selector	=> 'color: {{VALUE}};',
			],
		] );
		$args['type'] = \Elementor\Controls_Manager::COLOR;
		$object->add_control( $id, $args );
	}

	public static function placeholder_color( $object, $id, $selector, $args = [] ) {
		if( strpos( $selector, "::placeholder" ) === false ) {
			$selector .= "::placeholder";
		}
		$args = Utils::check_default( $args, [
			'label'			=> esc_html__( 'Placeholder color', 'mj-whitebox' ),
			'selectors'		=> [
				$selector	=> 'color: {{VALUE}};',
			],
		] );
		$args['type'] = \Elementor\Controls_Manager::COLOR;
		$object->add_control( $id, $args );
	}

	public static function background( $object, $id, $selector, $args = [] ) {
		$args = Utils::check_default( $args, [
			'selector'	=> $selector,
		] );
		$args['name'] = $id;
		$object->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			$args
		);
	}

	public static function border( $object, $id, $selector, $args = [] ) {
		$args = Utils::check_default( $args, [
			'selector'	=> $selector,
		] );
		$args['name'] = $id;
		$object->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			$args
		);
	}

	public static function border_radius( $object, $id, $selector, $args = [] ) {
		$args = Utils::check_default( $args, [
			'label'			=> esc_html__( 'Border Radius', 'mj-whitebox' ),
			'size_units'	=> [ 'px', '%', 'em', 'rem', 'custom' ],
			'selectors'		=> [
				$selector	=> 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );
		$args['type'] = \Elementor\Controls_Manager::DIMENSIONS;
		$object->add_responsive_control( $id, $args );
	}

	public static function box_shadow( $object, $id, $selector, $args = [] ) {
		$args = Utils::check_default( $args, [
			'selector'	=> $selector,
		] );
		$args['name'] = $id;
		$object->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			$args
		);
	}

	public static function text_shadow( $object, $id, $selector, $args = [] ) {
		$args = Utils::check_default( $args, [
			'selector'	=> $selector,
		] );
		$args['name'] = $id;
		$object->add_group_control(
			\Elementor\Group_Control_Text_Shadow::get_type(),
			$args
		);
	}

	public static function width( $object, $id, $selector, $args = [] ) {
		$args = Utils::check_default( $args, [
			'label'			=> esc_html__( 'Width', 'mj-whitebox' ),
			'size_units'	=> [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
			'range' => [
				'px' => [
					'max' => 1000,
				],
			],
			'selectors'		=> [
				$selector	=> 'width: {{SIZE}}{{UNIT}};',
			],
		] );
		$args['type'] = \Elementor\Controls_Manager::SLIDER;
		$object->add_responsive_control( $id, $args );
	}

	public static function height( $object, $id, $selector, $args = [] ) {
		$args = Utils::check_default( $args, [
			'label'			=> esc_html__( 'Height', 'mj-whitebox' ),
			'size_units'	=> [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
			'range' => [
				'px' => [
					'max' => 1000,
				],
			],
			'selectors'		=> [
				$selector	=> 'height: {{SIZE}}{{UNIT}};',
			],
		] );
		$args['type'] = \Elementor\Controls_Manager::SLIDER;
		$object->add_responsive_control( $id, $args );
	}

	public static function display( $object, $id, $selector, $options, $args = [] ) {
		/**
		 * $options
		 
		 ''				=> __( "Default", 'mj-whitebox' ),
		'block'			=> __( "Block", 'mj-whitebox' ),
		'inline'		=> __( "Inline", 'mj-whitebox' ),
		'inline-block'	=> __( "Inline-Block", 'mj-whitebox' ),
		'flex'			=> __( "Flex", 'mj-whitebox' ),
		'grid'			=> __( "Grid", 'mj-whitebox' ),

		 */
		$args = Utils::check_default( $args, [
			'label'		=> esc_html__( 'Display', 'mj-whitebox' ),
			'default'	=> '',
			'options'	=> $options,
			'selectors'	=> [
				$selector	=> 'display: {{VALUE}};',
			],
		] );
		$args['type'] = \Elementor\Controls_Manager::SELECT;
		$object->add_responsive_control( $id, $args );

		if( in_array( 'flex', array_keys( $options ) ) || in_array( 'grid', array_keys( $options ) ) ) {
			self::justify_content( $object, "{$id}_justify_content", $selector, [$id => ['flex', 'grid']] );
			self::align_items( $object, "{$id}_align_items", $selector, [$id => ['flex', 'grid']] );
		}
		if( in_array( 'flex', array_keys( $options ) ) ) {
			self::flex_wrap( $object, "{$id}_flex_wrap", $selector, [$id => ['flex', 'grid']] );
		}
		if( in_array( 'flex', array_keys( $options ) ) || in_array( 'grid', array_keys( $options ) ) ) {
			self::align_content( $object, "{$id}_align_content", $selector, [$id => ['flex', 'grid']] );
			self::row_gap( $object, "{$id}_row_gap", $selector, [$id => ['flex', 'grid']] );
		}
		if( in_array( 'flex', array_keys( $options ) ) || in_array( 'grid', array_keys( $options ) ) ) {
			self::column_gap( $object, "{$id}_column_gap", $selector, [$id => ['flex', 'grid']] );
		}
		if( in_array( 'grid', array_keys( $options ) ) ) {
			self::columns( $object, "{$id}_columns", $selector, [$id => 'grid'] );
		}
	}

	public static function flex_wrap( $object, $id, $selector, $conditions = [], $args = [] ) {
		$args = Utils::check_default( $args, [
			'label'		=> esc_html__( 'Wrap', 'elementor' ),
			'options' => [
				'nowrap' => [
					'title' => esc_html__( 'No Wrap', 'elementor' ),
					'icon' => 'eicon-flex eicon-nowrap',
				],
				'wrap' => [
					'title' => esc_html__( 'Wrap', 'elementor' ),
					'icon' => 'eicon-flex eicon-wrap',
				],
			],
			'description' => esc_html__( 'Items within the container can stay in a single line (No wrap), or break into multiple lines (Wrap).', 'elementor' ),
			'selectors'	=> [
				$selector	=> 'flex-wrap: {{VALUE}};',
			],
			'_responsive'	=> true,
		] );
		$args['type'] = \Elementor\Controls_Manager::CHOOSE;
		if( !empty( $conditions ) ) {
			$args['condition'] = $conditions;
		}
		if( $args['_responsive'] ) {
			$object->add_responsive_control( $id, $args );
		} else {
			$object->add_control( $id, $args );
		}
	}

	public static function justify_content( $object, $id, $selector, $conditions = [], $args = [] ) {
		$args = Utils::check_default( $args, [
			'label'			=> esc_html__( 'Justify Content', 'elementor' ),
			'label_block'	=> true,
			'default'		=> '',
			'options'		=> [
				'flex-start' => [
					'title' => esc_html__( 'Start', 'elementor' ),
					'icon' => 'eicon-flex eicon-justify-start-h',
				],
				'center' => [
					'title' => esc_html__( 'Center', 'elementor' ),
					'icon' => 'eicon-flex eicon-justify-center-h',
				],
				'flex-end' => [
					'title' => esc_html__( 'End', 'elementor' ),
					'icon' => 'eicon-flex eicon-justify-end-h',
				],
				'space-between' => [
					'title' => esc_html__( 'Space Between', 'elementor' ),
					'icon' => 'eicon-flex eicon-justify-space-between-h',
				],
				'space-around' => [
					'title' => esc_html__( 'Space Around', 'elementor' ),
					'icon' => 'eicon-flex eicon-justify-space-around-h',
				],
				'space-evenly' => [
					'title' => esc_html__( 'Space Evenly', 'elementor' ),
					'icon' => 'eicon-flex eicon-justify-space-evenly-h',
				],
			],
			'selectors'	=> [
				$selector	=> 'justify-content: {{VALUE}};',
			],
			'_responsive'	=> true,
		] );
		$args['type'] = \Elementor\Controls_Manager::CHOOSE;
		if( !empty( $conditions ) ) {
			$args['condition'] = $conditions;
		}
		if( $args['_responsive'] ) {
			$object->add_responsive_control( $id, $args );
		} else {
			$object->add_control( $id, $args );
		}
	}

	public static function align_items( $object, $id, $selector, $conditions = [], $args = [] ) {
		$args = Utils::check_default( $args, [
			'label'			=> esc_html__( 'Align Items', 'elementor' ),
			'default'		=> '',
			'options'		=> [
				'flex-start' => [
					'title' => esc_html__( 'Start', 'elementor' ),
					'icon' => 'eicon-flex eicon-align-start-v',
				],
				'center' => [
					'title' => esc_html__( 'Center', 'elementor' ),
					'icon' => 'eicon-flex eicon-align-center-v',
				],
				'flex-end' => [
					'title' => esc_html__( 'End', 'elementor' ),
					'icon' => 'eicon-flex eicon-align-end-v',
				],
				'stretch' => [
					'title' => esc_html__( 'Stretch', 'elementor' ),
					'icon' => 'eicon-flex eicon-align-stretch-v',
				],
			],
			'selectors'	=> [
				$selector	=> 'align-items: {{VALUE}};',
			],
			'_responsive'	=> true,
		] );
		$args['type'] = \Elementor\Controls_Manager::CHOOSE;
		if( !empty( $conditions ) ) {
			$args['condition'] = $conditions;
		}
		if( $args['_responsive'] ) {
			$object->add_responsive_control( $id, $args );
		} else {
			$object->add_control( $id, $args );
		}
	}

	public static function align_content( $object, $id, $selector, $conditions = [], $args = [] ) {
		$args = Utils::check_default( $args, [
			'label'			=> esc_html__( 'Align Content', 'elementor' ),
			'label_block'	=> true,
			'default'		=> '',
			'options'		=> [
				'flex-start' => [
					'title' => esc_html__( 'Start', 'elementor' ),
					'icon' => 'eicon-justify-start-v',
				],
				'center' => [
					'title' => esc_html__( 'Middle', 'elementor' ),
					'icon' => 'eicon-justify-center-v',
				],
				'flex-end' => [
					'title' => esc_html__( 'End', 'elementor' ),
					'icon' => 'eicon-justify-end-v',
				],
				'space-between' => [
					'title' => esc_html__( 'Space Between', 'elementor' ),
					'icon' => 'eicon-justify-space-between-v',
				],
				'space-around' => [
					'title' => esc_html__( 'Space Around', 'elementor' ),
					'icon' => 'eicon-justify-space-around-v',
				],
				'space-evenly' => [
					'title' => esc_html__( 'Space Evenly', 'elementor' ),
					'icon' => 'eicon-justify-space-evenly-v',
				],
			],
			'selectors'	=> [
				$selector	=> 'align-content: {{VALUE}};',
			],
			'_responsive'	=> true,
		] );
		$args['type'] = \Elementor\Controls_Manager::CHOOSE;

		$conditions['wrap'] = 'wrap';

		if( !empty( $conditions ) ) {
			$args['condition'] = $conditions;
		}
		if( $args['_responsive'] ) {
			$object->add_responsive_control( $id, $args );
		} else {
			$object->add_control( $id, $args );
		}
	}

	public static function row_gap( $object, $id, $selector, $conditions = [], $args = [] ) {
		$args = Utils::check_default( $args, [
			'type'		=> \Elementor\Controls_Manager::NUMBER,
			'label'		=> esc_html__( 'Row gap (px)', 'mj-whitebox' ),
			'min'		=> 0,
			'selectors'	=> [
				$selector	=> 'row-gap: {{VALUE}}px;',
			],
			'_responsive'	=> true,
		] );
		$args['type'] = \Elementor\Controls_Manager::NUMBER;
		if( !empty( $conditions ) ) {
			$args['condition'] = $conditions;
		}
		if( $args['_responsive'] ) {
			$object->add_responsive_control( $id, $args );
		} else {
			$object->add_control( $id, $args );
		}
	}

	public static function column_gap( $object, $id, $selector, $conditions = [], $args = [] ) {
		$args = Utils::check_default( $args, [
			'type'		=> \Elementor\Controls_Manager::NUMBER,
			'label'		=> esc_html__( 'Column gap (px)', 'mj-whitebox' ),
			'min'		=> 0,
			'selectors'	=> [
				$selector	=> 'column-gap: {{VALUE}}px;',
			],
			'_responsive'	=> true,
		] );
		$args['type'] = \Elementor\Controls_Manager::NUMBER;
		if( !empty( $conditions ) ) {
			$args['condition'] = $conditions;
		}
		if( $args['_responsive'] ) {
			$object->add_responsive_control( $id, $args );
		} else {
			$object->add_control( $id, $args );
		}
	}

	public static function columns( $object, $id, $selector, $conditions = [], $args = [] ) {
		$args = Utils::check_default( $args, [
			'type'		=> \Elementor\Controls_Manager::NUMBER,
			'label'		=> esc_html__( 'Columns', 'mj-whitebox' ),
			'min'		=> 0,
			'selectors'	=> [
				$selector	=> 'grid-template-columns: repeat({{VALUE}},1fr);',
			],
		] );
		$args['type'] = \Elementor\Controls_Manager::NUMBER;
		if( !empty( $conditions ) ) {
			$args['condition'] = $conditions;
		}
		$object->add_responsive_control( $id, $args );
	}

	public static function css_filters( $object, $id, $selector, $args = [] ) {
		$args = Utils::check_default( $args, [
			'selector'	=> $selector,
		] );
		$args['name'] = $id;
		$object->add_group_control(
			\Elementor\Group_Control_Css_Filter::get_type(),
			$args
		);
	}

	/**
	 * Add text style controls to an Elementor widget, including normal and hover states.
	 *
	 * Controls include margin, padding, typography, text alignment, color, background,
	 * border, border radius, and text shadow. Supports custom selector and optional hover selector.
	 *
	 * @param object $object Elementor widget instance.
	 * @param string $selector CSS selector for the element.
	 * @param string $prefix Prefix for control names.
	 * @param string $label Label for the controls section.
	 * @param string $hover_selector Optional CSS selector for hover state. Defaults to "{$selector}:hover".
	 *
	 * @return void
	 */
	public static function text_style_controls( $object, $selector, $prefix, $label, $hover_selector = '' ) {
		$selector = "{{WRAPPER}} {$selector}";
		$hover_selector = Elementor::get_wrapper_selector( !$hover_selector ? "{$selector}:hover" : $hover_selector );

		$object->start_controls_section(
			"style_{$prefix}_section",
			[
				'label'	=> $label,
				'tab'	=> \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$object->start_controls_tabs( "tabs_{$prefix}_style" );

		$object->start_controls_tab( // Normal
			"tab_{$prefix}_normal",
			[
				'label'	=> esc_html__( 'Normal', 'mj-whitebox' ),
			]
		);

		self::margin( $object, "{$prefix}margin", $selector );
		self::padding( $object, "{$prefix}padding", $selector );
		self::typography( $object, "{$prefix}typography", $selector );
		self::text_align( $object, "{$prefix}text_align", $selector );
		self::color( $object, "{$prefix}color", $selector );
		self::background( $object, "{$prefix}background", $selector );
		self::border( $object, "{$prefix}border", $selector );
		self::border_radius( $object, "{$prefix}border_radius", $selector );
		self::text_shadow( $object, "{$prefix}text_shadow", $selector );

		$object->end_controls_tab();

		$object->start_controls_tab( // Hover
			"tab_{$prefix}_hover",
			[
				'label' => esc_html__( 'Hover', 'mj-whitebox' ),
			]
		);

		self::margin( $object, "{$prefix}margin_hover", $hover_selector );
		self::padding( $object, "{$prefix}padding_hover", $hover_selector );
		self::typography( $object, "{$prefix}typography_hover", $hover_selector );
		self::text_align( $object, "{$prefix}text_align_hover", $hover_selector );
		self::color( $object, "{$prefix}color_hover", $hover_selector );
		self::background( $object, "{$prefix}background_hover", $hover_selector );
		self::border( $object, "{$prefix}border_hover", $hover_selector );
		self::border_radius( $object, "{$prefix}border_radius_hover", $hover_selector );
		self::text_shadow( $object, "{$prefix}text_shadow_hover", $hover_selector );

		$object->end_controls_tab();
		$object->end_controls_tabs();

		$object->end_controls_section();
	}

	public static function display_settings( $object, $args = [] ) {
		$default_controls = [
			'desktop_slider' => [
				'label'			=> esc_html__( 'Desktop slider', 'mj-whitebox' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'mj-whitebox' ),
				'label_off'		=> esc_html__( 'No', 'mj-whitebox' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
			],
			'desktop_slides_type' => [
				'label'		=> esc_html__( "Desktop slides type", 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'count',
				'options'	=> [
					'count'	=> __( 'Count', 'mj-whitebox' ),
					'auto'	=> __( 'Auto', 'mj-whitebox' ),
				],
				'condition'	=> [
					'desktop_slider'	=> 'yes'
				],
			],
			'desktop_slides' => [
				'label'		=> esc_html__( "Desktop visible slides", 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'default'	=> 4,
				'condition'	=> [
					'desktop_slider'		=> 'yes',
					'desktop_slides_type'	=> 'count',
				]
			],
			'desktop_slides_space' => [
				'label'		=> esc_html__( "Desktop slides space", 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 0,
				'default'	=> 24,
				'condition'	=> [
					'desktop_slider'	=> 'yes',
				]
			],
			'desktop_cols' => [
				'label'		=> esc_html__( 'Desktop columns', 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'max'		=> 10,
				'default'	=> 5,
				'condition'	=> [
					'desktop_slider!'	=> 'yes'
				],
			],
			'desktop_row_gap' => [
				'label'		=> esc_html__( 'Desktop row gap', 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'default'	=> 16,
				'condition'	=> [
					'desktop_slider!'	=> 'yes'
				],
			],
			'desktop_column_gap' => [
				'label'		=> esc_html__( 'Desktop column gap', 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'default'	=> 16,
				'condition'	=> [
					'desktop_slider!'	=> 'yes'
				],
			],
			'tablet_slider' => [
				'label'			=> esc_html__( 'Tablet slider', 'mj-whitebox' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'mj-whitebox' ),
				'label_off'		=> esc_html__( 'No', 'mj-whitebox' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
				'separator'		=> 'before',
			],
			'tablet_slides_type' => [
				'label'		=> esc_html__( "Tablet slides type", 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'auto',
				'options'	=> [
					'count'	=> __( 'Count', 'mj-whitebox' ),
					'auto'	=> __( 'Auto', 'mj-whitebox' ),
				],
				'condition'	=> [
					'tablet_slider'	=> 'yes'
				],
			],
			'tablet_slides' => [
				'label'		=> esc_html__( "Tablet visible slides", 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'default'	=> 4,
				'condition'	=> [
					'tablet_slider'			=> 'yes',
					'tablet_slides_type'	=> 'count',
				]
			],
			'tablet_slides_space' => [
				'label'		=> esc_html__( "Tablet slides space", 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 0,
				'default'	=> 16,
				'condition'	=> [
					'tablet_slider'	=> 'yes',
				]
			],
			'tablet_cols' => [
				'label'		=> esc_html__( 'Tablet columns', 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'max'		=> 10,
				'default'	=> 2,
				'condition'	=> [
					'tablet_slider!'	=> 'yes'
				],
			],
			'tablet_row_gap' => [
				'label'		=> esc_html__( 'Tablet row gap', 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'default'	=> 16,
				'condition'	=> [
					'tablet_slider!'	=> 'yes'
				],
			],
			'tablet_column_gap' => [
				'label'		=> esc_html__( 'Tablet column gap', 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'default'	=> 16,
				'condition'	=> [
					'tablet_slider!'	=> 'yes'
				],
			],
			'mobile_slider' => [
				'label'			=> esc_html__( 'Mobile slider', 'mj-whitebox' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'mj-whitebox' ),
				'label_off'		=> esc_html__( 'No', 'mj-whitebox' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
				'separator'		=> 'before',
			],
			'mobile_slides_type' => [
				'label'		=> esc_html__( "Mobile slides type", 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'auto',
				'options'	=> [
					'count'	=> __( 'Count', 'mj-whitebox' ),
					'auto'	=> __( 'Auto', 'mj-whitebox' ),
				],
				'condition'	=> [
					'mobile_slider'	=> 'yes'
				],
			],
			'mobile_slides' => [
				'label'		=> esc_html__( "Mobile visible slides", 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'default'	=> 4,
				'condition'	=> [
					'mobile_slider'			=> 'yes',
					'mobile_slides_type'	=> 'count',
				]
			],
			'mobile_slides_space' => [
				'label'		=> esc_html__( "Mobile slides space", 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 0,
				'default'	=> 16,
				'condition'	=> [
					'mobile_slider'	=> 'yes',
				]
			],
			'mobile_cols' => [
				'label'		=> esc_html__( 'Mobile columns', 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'max'		=> 10,
				'default'	=> 1,
				'condition'	=> [
					'mobile_slider!'	=> 'yes'
				],
			],
			'mobile_row_gap' => [
				'label'		=> esc_html__( 'Mobile row gap', 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'default'	=> 16,
				'condition'	=> [
					'mobile_slider!'	=> 'yes'
				],
			],
			'mobile_column_gap' => [
				'label'		=> esc_html__( 'Mobile column gap', 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'default'	=> 16,
				'condition'	=> [
					'mobile_slider!'	=> 'yes'
				],
			],
		];
		$args = Utils::check_default( $args, [
			'section'	=> [
				'name'	=> 'display_settings_section',
				'label'	=> esc_html__( 'Display settings', 'mj-whitebox' ),
			],
			'excludes'	=> [],
			'only'		=> [],
			'controls'	=> $default_controls
		] );
		
		$args = self::prepare_args( $args, $default_controls );

		$section_args = [
			'label'	=> $args['section']['label'],
			'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
		];
		if( !empty( $args['section']['condition'] ) ) {
			$section_args['condition'] = $args['section']['condition'];
		}
		if( !empty( $args['section']['conditions'] ) ) {
			$section_args['conditions'] = $args['section']['conditions'];
		}
		$object->start_controls_section(
			$args['section']['name'],
			$section_args
		);

		foreach( $args['controls'] as $control_name => $control_args ) {
			if( !in_array( $control_name, $args['excludes'] ) ) {
				if( !empty( $control_args['condition'] ) ) {
					$control_args['condition'] = Utils::unset( $control_args['condition'], $args['excludes'] );
					if( count( $control_args['condition'] ) === 0 ) {
						unset( $control_args['condition'] );
					}
				}

				$object->add_control(
					$control_name,
					$control_args
				);
			}
		}

		$object->end_controls_section();
	}

	public static function pagination_controls( $object, $args = [] ) {
		$default_controls = [
			'ppp'				=> [
				'label'			=> esc_html__( 'Posts per page', 'mj-whitebox' ),
				'type'			=> \Elementor\Controls_Manager::NUMBER,
				'min'			=> 1,
				'default'		=> 8,
				'condition'		=> [
					'query_type!'	=> ['by_id', 'current_query']
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			],
			'offset'			=> [
				'label'			=> esc_html__( 'Offset', 'mj-whitebox' ),
				'type'			=> \Elementor\Controls_Manager::NUMBER,
				'min'			=> 0,
				'default'		=> 0,
				'condition'		=> [
					'query_type!'	=> ['by_id', 'current_query']
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			],
			'show_pagination'	=> [
				'label'			=> esc_html__( 'Show pagination', 'mj-whitebox' ),
				'description'	=> esc_html__( "Turn off pagination if you don't need it. It can improve the page's performance.", 'mj-whitebox' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'mj-whitebox' ),
				'label_off'		=> esc_html__( 'No', 'mj-whitebox' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
				'condition'		=> [
					'query_type!'	=> ['by_id', 'current_query']
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			],
		];
		$args = Utils::check_default( $args, [
			'section'	=> [
				'name'	=> 'pagination_section',
				'label'	=> esc_html__( 'Pagination settings', 'mj-whitebox' ),
			],
			'excludes'	=> [],
			'only'		=> [],
			'controls'	=> $default_controls,
		] );

		$section_args = [
			'label'	=> $args['section']['label'],
			'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
		];
		if( !empty( $args['section']['condition'] ) ) {
			$section_args['condition'] = $args['section']['condition'];
		}
		if( !empty( $args['section']['conditions'] ) ) {
			$section_args['conditions'] = $args['section']['conditions'];
		}
		$object->start_controls_section( // content_section
			$args['section']['name'],
			$section_args
		);

		self::_add_controls( $object, $default_controls, "", $args );

		$object->end_controls_section();
	}

	public static function pagination_style_controls( $object, bool $wc = false ) {
		$base_selector = !$wc ? '.pagination' : '.woocommerce-pagination';
		self::general_style_controls( $object, [ // pagination
			'prefix'		=> 'pagination_',
			'base_selector'	=> $base_selector,

			'section'	=> [
				'name'		=> 'pagination_section',
				'label'		=> esc_html__( 'Pagination style', 'mj-whitebox' ),
				'condition'	=> [
					'show_pagination'	=> 'yes',
				],
			],

			'mode'	=> 'wrap'
		] );
		
		self::general_style_controls( $object, [ // pagination_number
			'prefix'		=> 'pagination_number_',
			'base_selector'	=> $base_selector,
			'selector'		=> '.page-numbers:is(a,span):not(.next):not(.prev):not(.dots)',
			'hover_type'	=> 'normal',

			'section'	=> [
				'name'		=> 'pagination_number_section',
				'label'		=> esc_html__( 'Pagination number style', 'mj-whitebox' ),
				'condition'	=> [
					'show_pagination'	=> 'yes',
				],
			],

			'excludes'			=> ['icon_size'],
			'hover_excludes'	=> ['icon_size'],
		] );

		self::general_style_controls( $object, [ // pagination_current
			'prefix'		=> 'pagination_current_',
			'base_selector'	=> $base_selector,
			'selector'		=> '.page-numbers:is(a,span).current:not(.next):not(.prev)',
			'hover_type'	=> 'normal',

			'section'	=> [
				'name'		=> 'pagination_current_section',
				'label'		=> esc_html__( 'Pagination current style', 'mj-whitebox' ),
				'condition'	=> [
					'show_pagination'	=> 'yes',
				],
			],

			'excludes'			=> ['icon_size'],
			'hover_excludes'	=> ['icon_size'],
		] );

		self::general_style_controls( $object, [ // pagination_prev
			'prefix'		=> 'pagination_prev_',
			'base_selector'	=> $base_selector,
			'selector'		=> '.page-numbers:is(a,span).prev',
			'hover_type'	=> 'normal',

			'section'	=> [
				'name'		=> 'pagination_prev_section',
				'label'		=> esc_html__( 'Pagination previous style', 'mj-whitebox' ),
				'condition'	=> [
					'show_pagination'	=> 'yes',
				],
			],

			'excludes'			=> ['icon_size'],
			'hover_excludes'	=> ['icon_size'],
		] );

		self::general_style_controls( $object, [ // pagination_next
			'prefix'		=> 'pagination_next_',
			'base_selector'	=> $base_selector,
			'selector'		=> '.page-numbers:is(a,span).next',
			'hover_type'	=> 'normal',

			'section'	=> [
				'name'		=> 'pagination_next_section',
				'label'		=> esc_html__( 'Pagination next style', 'mj-whitebox' ),
				'condition'	=> [
					'show_pagination'	=> 'yes',
				],
			],

			'excludes'			=> ['icon_size'],
			'hover_excludes'	=> ['icon_size'],
		] );

		self::general_style_controls( $object, [ // pagination_dots
			'prefix'		=> 'pagination_dots_',
			'base_selector'	=> $base_selector,
			'selector'		=> '.page-numbers:is(a,span).dots',
			'hover_type'	=> 'normal',

			'section'	=> [
				'name'		=> 'pagination_dots_section',
				'label'		=> esc_html__( 'Pagination dots style', 'mj-whitebox' ),
				'condition'	=> [
					'show_pagination'	=> 'yes',
				],
			],

			'excludes'			=> ['icon_size'],
			'hover_excludes'	=> ['icon_size'],

			'mode'	=> 'text'
		] );
	}

	public static function query_controls( $object, bool $wc = false, array $args = [] ) {
		if( empty( $args['query_types'] ) ) {
			$query_types = [
				'latest'		=> !$wc ? esc_html__( 'Latests posts', 'mj-whitebox' ) : esc_html__( 'Latests products', 'mj-whitebox' ),
				'custom'		=> esc_html__( 'Custom', 'mj-whitebox' ),
				'current_query'	=> esc_html__( 'Current Query', 'mj-whitebox' ),
				'by_id'			=> esc_html__( 'Manual Selection', 'mj-whitebox' ),
			];
			$query_types = apply_filters( 'mj\whitebox\elementor_controls\query_controls\query_types', $query_types, $args );
		} else {
			$query_types = $args['query_types'];
		}

		// Default start controls
		$start_controls = [
			'query_type'	=> [
				'label'		=> esc_html__( 'Query type', 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'multiple'	=> false,
				'default'	=> 'custom',
				'options'	=> $query_types,
			],
		];
		if( !$wc ) {
			$start_controls['post_type'] = [
				'label'		=> esc_html__( 'Post type', 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'post',
				'options'	=> \ElementorPro\Core\Utils::get_public_post_types(),
				'condition'	=> [
					'query_type!'	=> 'current_query',
				],
			];
		}

		// Default include(tab) controls
		$includes_controls = [
			'query_include_ids'			=> [
				'label'			=> esc_html__( "Search & Select", 'mj-whitebox' ),
				'description'	=> esc_html__( 'Select posts that you want to include', 'mj-whitebox' ),
				'label_block'	=> true,
				'multiple'		=> true,
				'type' 			=> \ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
				'autocomplete'	=> [
					'object'	=> \ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_POST,
					'query'		=> [
						'post_type'	=> '',
					],
				],
				'condition'		=> [
					'query_type!'	=> 'current_query',
				],
			],
			'query_include_author'		=> [
				'label'			=> !$wc ? esc_html__( "Author", 'mj-whitebox' ) : esc_html__( "Seller", 'mj-whitebox' ),
				'label_block'	=> true,
				'multiple'		=> true,
				'type' 			=> \ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
				'autocomplete'	=> [
					'object'	=> \ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_AUTHOR,
				],
				'condition'		=> [
					'query_type!'	=> ['by_id', 'current_query'],
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			],
			'query_include_category'	=> [
				'label'			=> esc_html__( "Category", 'mj-whitebox' ),
				'label_block'	=> true,
				'multiple'		=> true,
				'type' 			=> \ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
				'autocomplete'	=> [
					'object'	=> \ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_TAX,
					'query' => [
						'taxonomy' => '',
					],
				],
				'condition'		=> [
					'query_type!'	=> ['by_id', 'current_query'],
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			],
			'query_include_tag'			=> [
				'label'			=> esc_html__( "Tag", 'mj-whitebox' ),
				'label_block'	=> true,
				'multiple'		=> true,
				'type' 			=> \ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
				'autocomplete'	=> [
					'object'	=> \ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_TAX,
					'query' => [
						'taxonomy' => '',
					],
				],
				'condition'		=> [
					'query_type!'	=> ['by_id', 'current_query'],
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			],
		];
		if( $wc ) {
			$includes_controls['only_on_sales'] = [
				'_position'		=> 1,
				'label'			=> esc_html__( 'Only on-sales', 'mj-whitebox' ),
				'description'	=> esc_html__( 'Show only on-sales products', 'mj-whitebox' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'mj-whitebox' ),
				'label_off'		=> esc_html__( 'No', 'mj-whitebox' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
				'condition'		=> [
					'query_type!'	=> 'current_query',
				],
			];

			$includes_controls['only_in_stocks'] = [
				'_position'		=> 2,
				'label'			=> esc_html__( 'Only instock', 'mj-whitebox' ),
				'description'	=> esc_html__( 'Show only instock products', 'mj-whitebox' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'mj-whitebox' ),
				'label_off'		=> esc_html__( 'No', 'mj-whitebox' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
				'condition'		=> [
					'query_type!'	=> 'current_query',
				],
			];
		}

		// Default exclude(tab) controls
		$excludes_controls = [
			'ignore_sticky_posts'		=> [
				'label'			=> esc_html__( 'Ignore sticky posts', 'mj-whitebox' ),
				'description'	=> esc_html__( 'Disabling this option will increase the performance of the page', 'mj-whitebox' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'mj-whitebox' ),
				'label_off'		=> esc_html__( 'No', 'mj-whitebox' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
				'condition'		=> [
					'query_type!'	=> ['by_id', 'current_query']
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			],
			'query_exclude_ids'			=> [
				'label'			=> esc_html__( "Search & Select", 'mj-whitebox' ),
				'description'	=> esc_html__( 'Select posts that you want to exclude', 'mj-whitebox' ),
				'label_block'	=> true,
				'multiple'		=> true,
				'type' 			=> \ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
				'autocomplete'	=> [
					'object'	=> \ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_POST,
					'query'		=> [
						'post_type'	=> '',
					],
				],
				'condition'		=> [
					'query_type!'	=> ['by_id', 'current_query'],
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			],
			'query_exclude_author'		=> [
				'label'			=> esc_html__( "Author", 'mj-whitebox' ),
				'label_block'	=> true,
				'multiple'		=> true,
				'type' 			=> \ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
				'autocomplete'	=> [
					'object'	=> \ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_AUTHOR,
				],
				'condition'		=> [
					'query_type!'	=> ['by_id', 'current_query'],
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			],
			'query_exclude_category'	=> [
				'label'			=> esc_html__( "Category", 'mj-whitebox' ),
				'label_block'	=> true,
				'multiple'		=> true,
				'type' 			=> \ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
				'autocomplete'	=> [
					'object'	=> \ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_TAX,
					'query' => [
						'taxonomy' => '',
					],
				],
				'condition'		=> [
					'query_type!'	=> ['by_id', 'current_query'],
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			],
			'query_exclude_tag'			=> [
				'label'			=> esc_html__( "Tag", 'mj-whitebox' ),
				'label_block'	=> true,
				'multiple'		=> true,
				'type' 			=> \ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
				'autocomplete'	=> [
					'object'	=> \ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_TAX,
					'query' => [
						'taxonomy' => '',
					],
				],
				'condition'		=> [
					'query_type!'	=> ['by_id', 'current_query'],
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			],
		];

		// Default end controls
		$date_types = apply_filters( 'mj\whitebox\elementor_controls\query_controls\date_types', Elementor::date_types( $args ), $wc, $args );
		$orderby_types = apply_filters( 'mj\whitebox\elementor_controls\query_controls\orderby', Elementor::orderby( $wc, [], $args ), $wc, $args );

		$end_controls = [
			'query_date'		=> [
				'label'		=> esc_html__( 'Date', 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'multiple'	=> false,
				'default'	=> 'anytime',
				'separator'	=> 'before',
				'options'	=> $date_types,
				'condition'		=> [
					'query_type!'	=> 'current_query'
				],
			],
			'query_date_before'	=> [
				'label'			=> esc_html__( 'Before', 'mj-whitebox' ),
				'type'			=> \Elementor\Controls_Manager::DATE_TIME,
				'placeholder'	=> esc_html__( 'Choose', 'mj-whitebox' ),
				'condition'		=> [
					'query_date'	=> 'exact',
					'query_type!'	=> ['by_id', 'current_query'],
				],
				'description'	=> esc_html__( 'Setting a ‘Before’ date will show all the posts published until the chosen date (inclusive).', 'mj-whitebox' ),
				'dynamic'		=> [
					'active'	=> true,
				],
			],
			'query_date_after'	=> [
				'label'			=> esc_html__( 'After', 'mj-whitebox' ),
				'description'	=> esc_html__( 'Setting an ‘After’ date will show all the posts published until the chosen date (inclusive).', 'mj-whitebox' ),
				'type'			=> \Elementor\Controls_Manager::DATE_TIME,
				'placeholder'	=> esc_html__( 'Choose', 'mj-whitebox' ),
				'condition'		=> [
					'query_date'	=> 'exact',
					'query_type!'	=> ['by_id', 'current_query'],
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			],
			'orderby'			=> [
				'label'		=> esc_html__( 'Order By', 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'multiple'	=> false,
				'default'	=> 'post_date',
				'options'	=> $orderby_types,
				'condition'		=> [
					'query_type!'	=> 'current_query'
				],
			],
			'order'				=> [
				'label'		=> esc_html__( 'Order', 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'multiple'	=> false,
				'default'	=> 'desc',
				'options'	=> [
					'asc'	=> esc_html__( 'ASC', 'mj-whitebox' ),
					'desc'	=> esc_html__( 'DESC', 'mj-whitebox' ),
				],
				'condition'		=> [
					'query_type!'	=> 'current_query'
				],
			],
			'no_posts_message'	=> [
				'label'			=> esc_html__( 'No Posts Message', 'mj-whitebox' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'dynamic'		=> [
					'active'	=> true,
				],
				'separator'		=> 'before',
			],
		];

		if( !empty( $args['start_excludes'] ) && in_array( 'query_type', $args['start_excludes'] ) ) {
			foreach( $includes_controls as $index => $control ) {
				if( !empty( $control['condition'] ) ) {
					if( !empty( $control['condition']['query_type!'] ) ) {
						unset( $control['condition']['query_type!'] );
					}
					if( !empty( $control['condition']['query_type'] ) ) {
						unset( $control['condition']['query_type'] );
					}
					$includes_controls[$index] = $control;
				}
			}

			foreach( $excludes_controls as $index => $control ) {
				if( !empty( $control['condition'] ) ) {
					if( !empty( $control['condition']['query_type!'] ) ) {
						unset( $control['condition']['query_type!'] );
					}
					if( !empty( $control['condition']['query_type'] ) ) {
						unset( $control['condition']['query_type'] );
					}
					$excludes_controls[$index] = $control;
				}
			}

			foreach( $start_controls as $index => $control ) {
				if( !empty( $control['condition'] ) ) {
					if( !empty( $control['condition']['query_type!'] ) ) {
						unset( $control['condition']['query_type!'] );
					}
					if( !empty( $control['condition']['query_type'] ) ) {
						unset( $control['condition']['query_type'] );
					}
					$start_controls[$index] = $control;
				}
			}

			foreach( $end_controls as $index => $control ) {
				if( !empty( $control['condition'] ) ) {
					if( !empty( $control['condition']['query_type!'] ) ) {
						unset( $control['condition']['query_type!'] );
					}
					if( !empty( $control['condition']['query_type'] ) ) {
						unset( $control['condition']['query_type'] );
					}
					$end_controls[$index] = $control;
				}
			}
		}

		$args = Utils::check_default( $args, [
			'section'	=> [
				'name'	=> 'settings_section',
				'label'	=> esc_html__( 'Settings', 'mj-whitebox' ),
			],
			'tabs'		=> [
				'includes'	=> [
					'label'		=> esc_html__( 'Includes', 'mj-whitebox' ),
					'condition'	=> empty( $args['start_excludes'] ) || !in_array( 'query_type', $args['start_excludes'] ) ? [
						'query_type!'	=> ['by_id', 'current_query'],
					] : [],

					'excludes'	=> [],
					'only'		=> [],
					'controls'	=> $includes_controls,
				],
				'excludes'	=> [
					'label'		=> esc_html__( 'Excludes', 'mj-whitebox' ),
					'condition'	=> empty( $args['start_excludes'] ) || !in_array( 'query_type', $args['start_excludes'] ) ? [
						'query_type!'	=> ['by_id', 'current_query'],
					] : [],

					'excludes'	=> [],
					'only'		=> [],
					'controls'	=> $excludes_controls,
				],
			],

			'post_type'	=> !$wc ? 'post' : 'product',
			'category'	=> !$wc ? 'category' : 'product_cat',
			'tag'		=> !$wc ? 'tag' : 'product_tag',

			'start_excludes'	=> [],
			'start_only'		=> [],
			'start_controls'	=> $start_controls,

			'end_excludes'	=> [],
			'end_only'		=> [],
			'end_controls'	=> $end_controls,
		] );

		// Section settings
		$section_args = [
			'label'	=> $args['section']['label'],
			'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
		];
		if( !empty( $args['section']['condition'] ) ) {
			$section_args['condition'] = $args['section']['condition'];
		}
		if( !empty( $args['section']['conditions'] ) ) {
			$section_args['conditions'] = $args['section']['conditions'];
		}
		$object->start_controls_section( // content_section
			"query_{$args['section']['name']}",
			$section_args
		);

		$start_args = self::prepare_args( [
			'excludes'	=> $args['start_excludes'],
			'only'		=> $args['start_only'],
			'controls'	=> $args['start_controls'],
		], $start_controls );

		self::_add_controls( $object, $start_controls, '', $start_args );

		$object->start_controls_tabs( 'tabs_post_archive_queries' );

		foreach( $args['tabs'] as $tab_name => $tab ) {
			$object->start_controls_tab( // Includes
				"tab_post_archive_{$tab_name}",
				[
					'label'		=> $tab['label'],
					'condition'	=> $tab['condition'],
				]
			);

			if( isset( $tab['controls']['query_include_ids'] ) ) {
				$tab['controls']['query_include_ids']['autocomplete']['query']['post_type'] = $args['post_type'];
			}
			if( isset( $tab['controls']['query_exclude_ids'] ) ) {
				$tab['controls']['query_exclude_ids']['autocomplete']['query']['post_type'] = $args['post_type'];
			}

			if( isset( $tab['controls']['query_include_category'] ) ) {
				$tab['controls']['query_include_category']['autocomplete']['query']['taxonomy'] = $args['category'];
			}
			if( isset( $tab['controls']['query_exclude_category'] ) ) {
				$tab['controls']['query_exclude_category']['autocomplete']['query']['taxonomy'] = $args['category'];
			}

			if( isset( $tab['controls']['query_include_tag'] ) ) {
				$tab['controls']['query_include_tag']['autocomplete']['query']['taxonomy'] = $args['tag'];
			}
			if( isset( $tab['controls']['query_exclude_tag'] ) ) {
				$tab['controls']['query_exclude_tag']['autocomplete']['query']['taxonomy'] = $args['tag'];
			}

			$tab_default_controls = $tab_name == 'includes' ? $includes_controls : $excludes_controls;
			$tab_args = self::prepare_args( [
				'excludes'	=> $tab['excludes'],
				'only'		=> $tab['only'],
				'controls'	=> $tab['controls'],
			], $tab_default_controls );

			self::_add_controls( $object, $tab_default_controls, '', $tab_args );

			$object->end_controls_tab();
		}

		$object->end_controls_tabs();

		$end_args = self::prepare_args( [
			'excludes'	=> $args['end_excludes'],
			'only'		=> $args['end_only'],
			'controls'	=> $args['end_controls'],
		], $end_controls );
		self::_add_controls( $object, $end_controls, '', $end_args );

		$object->end_controls_section();
	}
}