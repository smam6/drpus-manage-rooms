<?php
namespace DrPlus;

use DrPlus\Utils\Archive;
use DrPlus\Utils\Elementor;
use DrPlus\Utils\User;

if( !defined( 'ABSPATH' ) ) exit;

if( isset( $_GET['elementor_updater'] ) ) return;

class ElementorControls {
	private static function prepare_args( $args, $default_controls ) {
		if( !empty( $args['only'] ) ) {
			foreach( array_keys( $default_controls ) as $default_control ) {
				if( !in_array( $default_control, $args['only'] ) ) {
					$args['excludes'][] = $default_control;
					$args['hover_excludes'][] = $default_control;
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

	private static function _add_controls( $object, $default_controls, $prefix, $args = [] ) {
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
	
	public static function slider_settings_controls( $object, $args = [] ) {
		$default_controls = [
			'desktop_slides_type' => [
				'label'		=> esc_html__( "Desktop slides type", 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'count',
				'options'	=> [
					'count'	=> __( 'Count', 'drplus' ),
					'auto'	=> __( 'Auto', 'drplus' ),
				],
			],
			'desktop_slides' => [
				'label'		=> esc_html__( "Desktop visible slides", 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'default'	=> 4,
				'condition'	=> [
					'desktop_slides_type'	=> 'count',
				]
			],
			'desktop_slides_space' => [
				'label'		=> esc_html__( "Desktop slides space", 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 0,
				'default'	=> 24,
			],
			'tablet_slides_type' => [
				'label'		=> esc_html__( "Tablet slides type", 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'auto',
				'options'	=> [
					'count'	=> __( 'Count', 'drplus' ),
					'auto'	=> __( 'Auto', 'drplus' ),
				],
			],
			'tablet_slides' => [
				'label'		=> esc_html__( "Tablet visible slides", 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'default'	=> 4,
				'condition'	=> [
					'tablet_slides_type'	=> 'count',
				]
			],
			'tablet_slides_space' => [
				'label'		=> esc_html__( "Tablet slides space", 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 0,
				'default'	=> 16,
			],
			'mobile_slides_type' => [
				'label'		=> esc_html__( "Mobile slides type", 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'auto',
				'options'	=> [
					'count'	=> __( 'Count', 'drplus' ),
					'auto'	=> __( 'Auto', 'drplus' ),
				],
			],
			'mobile_slides' => [
				'label'		=> esc_html__( "Mobile visible slides", 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'default'	=> 4,
				'condition'	=> [
					'mobile_slides_type'	=> 'count',
				]
			],
			'mobile_slides_space' => [
				'label'		=> esc_html__( "Mobile slides space", 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 0,
				'default'	=> 16,
			],
		];
		$args = Utils::check_default( $args, [
			'section'	=> [
				'name'	=> 'slider_settings_section',
				'label'	=> esc_html__( 'Slider settings', 'drplus' ),
			],
			'only'		=> [],
			'excludes'	=> [],
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

		self::_add_controls( $object, $default_controls, '', $args );

		$object->end_controls_section();
	}

	/**
	 * Create section title controls
	 *
	 * @param object $object
	 * @param array $args [
	 * 		default_title	=> string
	 * ]
	 * @return void
	 */
	public static function section_title_settings( $object, array $args = [] ) {
		$args = Utils::check_default( $args, [
			'section'	=> [
				'name'	=> 'section_title_section',
				'label'	=> esc_html__( 'Section title', 'drplus' ),
			],
			'excludes'	=> [],
			'controls'	=> [],
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

		self::section_title_controls( $object, $args );

		$object->end_controls_section();
	}

	public static function section_title_controls( $object, $args = [] ) {
		if( !isset( $args['prefix'] ) ) $args['prefix'] = 'section_title_';
		$default_controls = [
			'tag'	=> [
				'type'			=> \Elementor\Controls_Manager::SELECT,
				'label'			=> esc_html__( 'Tag', 'drplus' ),
				'label_block'	=> true,
				'default'		=> 'h2',
				'options'		=> Utils::custom_tags()
			],
			'icon'	=> [
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Title icon', 'drplus' ),
				'skin'			=> 'inline',
				'label_block'	=> false,
				'default'		=> [
					'value'		=> 'drplus-icon-grid-fill',
					'library'	=> 'drplus-icon'
				],
			],
			'icon_has_bg' => [
				'label'			=> esc_html__( 'Show icon background', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			],
			'title'	=> [
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label'			=> esc_html__( "Title", 'drplus' ),
				'label_block'	=> true,
				'default'		=> esc_html__( "Lorem {Ipsum}", 'drplus' ),
				'description'	=> esc_html__( "To color a portion of text, enclose the text in { and }. Example: {percentage}", 'drplus' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			],
			'link'	=> [
				'label'		=> esc_html__( 'Title link', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::URL,
				'separator'	=> 'after',
				'default'	=> [
					'url'	=> '#'
				],
				'dynamic'	=> [
					'active'	=> true,
				],
			],
			'subtitle'	=> [
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label'			=> esc_html__( "Subtitle", 'drplus' ),
				'label_block'	=> true,
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			],
		];

		self::_add_controls( $object, $default_controls, $args['prefix'], $args );
	}

	public static function margin( $object, $id, $selector, $args = [] ) {
		$args = Utils::check_default( $args, [
			'label'			=> esc_html__( 'Margin', 'drplus' ),
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
			'label'			=> esc_html__( 'Padding', 'drplus' ),
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
			'label'			=> esc_html__( 'Text align', 'drplus' ),
			'options'		=> [
				'left'		=> [
					'title'	=> esc_html__( 'Left', 'drplus' ),
					'icon'	=> 'eicon-text-align-left',
				],
				'center'	=> [
					'title'	=> esc_html__( 'Center', 'drplus' ),
					'icon'	=> 'eicon-text-align-center',
				],
				'right'		=> [
					'title'	=> esc_html__( 'Right', 'drplus' ),
					'icon'	=> 'eicon-text-align-right',
				],
				'justify'	=> [
					'title'	=> esc_html__( 'Justify', 'drplus' ),
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
			'label'			=> esc_html__( 'Color', 'drplus' ),
			'selectors'		=> [
				$selector	=> 'color: {{VALUE}};',
			],
		] );
		$args['type'] = \Elementor\Controls_Manager::COLOR;
		$object->add_control( $id, $args );
	}

	public static function placeholder_color( $object, $id, $selector, $args = [] ) {
		if( strpos( $selector, "::placeholder" ) === false ) {
			$selectors = explode( ',', $selector );
			foreach( $selectors as &$_selector ) {
				$_selector .= "::placeholder";
			}
			$selector = implode( ',', $selectors );
		}
		$args = Utils::check_default( $args, [
			'label'			=> esc_html__( 'Placeholder color', 'drplus' ),
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
			'label'			=> esc_html__( 'Border Radius', 'drplus' ),
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
			'label'			=> esc_html__( 'Width', 'drplus' ),
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
			'label'			=> esc_html__( 'Height', 'drplus' ),
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
		 
		 ''				=> __( "Default", 'drplus' ),
		'block'			=> __( "Block", 'drplus' ),
		'inline'		=> __( "Inline", 'drplus' ),
		'inline-block'	=> __( "Inline-Block", 'drplus' ),
		'flex'			=> __( "Flex", 'drplus' ),
		'grid'			=> __( "Grid", 'drplus' ),

		 */
		$args = Utils::check_default( $args, [
			'label'		=> esc_html__( 'Display', 'drplus' ),
			'default'	=> '',
			'options'	=> $options,
			'selectors'	=> [
				$selector	=> 'display: {{VALUE}};',
			],
		] );
		$args['type'] = \Elementor\Controls_Manager::SELECT;
		$object->add_responsive_control( $id, $args );

		self::row_gap( $object, "{$id}_row_gap", $selector, [$id => ['flex', 'grid']] );
		self::column_gap( $object, "{$id}_column_gap", $selector, [$id => ['flex', 'grid']] );
		self::columns( $object, "{$id}_columns", $selector, [$id => 'grid'] );
	}

	public static function row_gap( $object, $id, $selector, $conditions = [], $args = [] ) {
		$args = Utils::check_default( $args, [
			'type'		=> \Elementor\Controls_Manager::NUMBER,
			'label'		=> esc_html__( 'Row gap (px)', 'drplus' ),
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
			'label'		=> esc_html__( 'Column gap (px)', 'drplus' ),
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
			'label'		=> esc_html__( 'Columns', 'drplus' ),
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

	public static function text_style_controls( $object, $selector, $prefix, $label, $hover_selector = '', $is_dark = false ) {
		$selector = "{{WRAPPER}} {$selector}";
		$section_args = [
			'label'	=> $label,
			'tab'	=> \Elementor\Controls_Manager::TAB_STYLE,
		];
		if( $is_dark ) {
			$selector = 'html[data-theme="dark"] ' . $selector;
			$section_args['label'] = self::dark_control_label( $section_args['label'] );
			$section_args['condition'] = [
				'enable_dark_mode' => 'yes'
			];
			$prefix = "dark_" . $prefix;
		}
		$hover_selector = Elementor::get_wrapper_selector( !$hover_selector ? "{$selector}:hover" : $hover_selector );

		$object->start_controls_section(
			"style_{$prefix}_section",
			$section_args
		);

		$object->start_controls_tabs( "tabs_{$prefix}_style" );

		$object->start_controls_tab( // Normal
			"tab_{$prefix}_normal",
			[
				'label'	=> esc_html__( 'Normal', 'drplus' ),
			]
		);

		if( !$is_dark ) {
			self::margin( $object, "{$prefix}margin", $selector );
			self::padding( $object, "{$prefix}padding", $selector );
			self::typography( $object, "{$prefix}typography", $selector );
			self::text_align( $object, "{$prefix}text_align", $selector );
		}
		self::color( $object, "{$prefix}color", $selector );
		self::background( $object, "{$prefix}background", $selector );
		if( !$is_dark ) {
			self::border_radius( $object, "{$prefix}border_radius", $selector );
		}
		self::border( $object, "{$prefix}border", $selector );
		self::text_shadow( $object, "{$prefix}text_shadow", $selector );

		$object->end_controls_tab();

		$object->start_controls_tab( // Hover
			"tab_{$prefix}_hover",
			[
				'label' => esc_html__( 'Hover', 'drplus' ),
			]
		);

		if( !$is_dark ) {
			self::margin( $object, "{$prefix}margin_hover", $hover_selector );
			self::padding( $object, "{$prefix}padding_hover", $hover_selector );
			self::typography( $object, "{$prefix}typography_hover", $hover_selector );
			self::text_align( $object, "{$prefix}text_align_hover", $hover_selector );
		}
		self::color( $object, "{$prefix}color_hover", $hover_selector );
		self::background( $object, "{$prefix}background_hover", $hover_selector );
		self::border( $object, "{$prefix}border_hover", $hover_selector );
		if( !$is_dark ) {
			self::border_radius( $object, "{$prefix}border_radius_hover", $hover_selector );
		}
		self::text_shadow( $object, "{$prefix}text_shadow_hover", $hover_selector );

		$object->end_controls_tab();
		$object->end_controls_tabs();

		$object->end_controls_section();
	}

	public static function query_controls( $object, bool $wc = false, array $args = [] ) {
		$query_types = [
			'latest'		=> !$wc ? esc_html__( 'Latests posts', 'drplus' ) : esc_html__( 'Latests products', 'drplus' ),
			'custom'		=> esc_html__( 'Custom', 'drplus' ),
			'current_query'	=> esc_html__( 'Current Query', 'drplus' ),
			'by_id'			=> esc_html__( 'Manual Selection', 'drplus' ),
		];

		// Default start controls
		$start_controls = [
			'query_type'	=> [
				'label'		=> esc_html__( 'Query type', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'multiple'	=> false,
				'default'	=> 'custom',
				'options'	=> $query_types,
			],
		];
		if( !$wc ) {
			$start_controls['post_type'] = [
				'label'		=> esc_html__( 'Post type', 'drplus' ),
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
				'label'			=> esc_html__( "Search & Select", 'drplus' ),
				'description'	=> esc_html__( 'Select posts that you want to include', 'drplus' ),
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
				'label'			=> !$wc ? esc_html__( "Author", 'drplus' ) : esc_html__( "Seller", 'drplus' ),
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
				'label'			=> esc_html__( "Category", 'drplus' ),
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
				'label'			=> esc_html__( "Tag", 'drplus' ),
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
				'label'			=> esc_html__( 'Only on-sales', 'drplus' ),
				'description'	=> esc_html__( 'Show only on-sales products', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
				'condition'		=> [
					'query_type!'	=> 'current_query',
				],
			];

			$includes_controls['only_in_stocks'] = [
				'_position'		=> 2,
				'label'			=> esc_html__( 'Only instock', 'drplus' ),
				'description'	=> esc_html__( 'Show only instock products', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
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
				'label'			=> esc_html__( 'Ignore sticky posts', 'drplus' ),
				'description'	=> esc_html__( 'Disabling this option will increase the performance of the page', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
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
				'label'			=> esc_html__( "Search & Select", 'drplus' ),
				'description'	=> esc_html__( 'Select posts that you want to exclude', 'drplus' ),
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
				'label'			=> esc_html__( "Author", 'drplus' ),
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
				'label'			=> esc_html__( "Category", 'drplus' ),
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
				'label'			=> esc_html__( "Tag", 'drplus' ),
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
		$end_controls = [
			'query_date'		=> [
				'label'		=> esc_html__( 'Date', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'multiple'	=> false,
				'default'	=> 'anytime',
				'separator'	=> 'before',
				'options'	=> [
					'anytime'	=> esc_html__( 'All', 'drplus' ),
					'today'		=> esc_html__( 'Past Day', 'drplus' ),
					'week'		=> esc_html__( 'Past Week', 'drplus' ),
					'month'		=> esc_html__( 'Past Month', 'drplus' ),
					'quarter'	=> esc_html__( 'Past Quarter', 'drplus' ),
					'year'		=> esc_html__( 'Past Year', 'drplus' ),
					'exact'		=> esc_html__( 'Custom', 'drplus' ),
				],
				'condition'		=> [
					'query_type!'	=> 'current_query'
				],
			],
			'query_date_before'	=> [
				'label'			=> esc_html__( 'Before', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::DATE_TIME,
				'placeholder'	=> esc_html__( 'Choose', 'drplus' ),
				'condition'		=> [
					'query_date'	=> 'exact',
					'query_type!'	=> ['by_id', 'current_query'],
				],
				'description'	=> esc_html__( 'Setting a ‘Before’ date will show all the posts published until the chosen date (inclusive).', 'drplus' ),
				'dynamic'		=> [
					'active'	=> true,
				],
			],
			'query_date_after'	=> [
				'label'			=> esc_html__( 'After', 'drplus' ),
				'description'	=> esc_html__( 'Setting an ‘After’ date will show all the posts published until the chosen date (inclusive).', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::DATE_TIME,
				'placeholder'	=> esc_html__( 'Choose', 'drplus' ),
				'condition'		=> [
					'query_date'	=> 'exact',
					'query_type!'	=> ['by_id', 'current_query'],
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			],
			'orderby'			=> [
				'label'		=> esc_html__( 'Order By', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'multiple'	=> false,
				'default'	=> 'post_date',
				'options'	=> Archive::order_by( $wc ),
				'condition'		=> [
					'query_type!'	=> 'current_query'
				],
			],
			'order'				=> [
				'label'		=> esc_html__( 'Order', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'multiple'	=> false,
				'default'	=> 'desc',
				'options'	=> [
					'asc'	=> esc_html__( 'ASC', 'drplus' ),
					'desc'	=> esc_html__( 'DESC', 'drplus' ),
				],
				'condition'		=> [
					'query_type!'	=> 'current_query'
				],
			],
			'no_posts_message'	=> [
				'label'			=> esc_html__( 'No Posts Message', 'drplus' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'dynamic'		=> [
					'active'	=> true,
				],
				'separator'		=> 'before',
			],
		];

		$args = Utils::check_default( $args, [
			'section'	=> [
				'name'	=> 'settings_section',
				'label'	=> esc_html__( 'Settings', 'drplus' ),
			],
			'tabs'		=> [
				'includes'	=> [
					'label'		=> esc_html__( 'Includes', 'drplus' ),
					'condition'	=> [
						'query_type!'	=> ['by_id', 'current_query'],
					],

					'excludes'	=> [],
					'controls'	=> $includes_controls,
				],
				'excludes'	=> [
					'label'		=> esc_html__( 'Excludes', 'drplus' ),
					'condition'	=> [
						'query_type!'	=> ['by_id', 'current_query'],
					],

					'excludes'	=> [],
					'controls'	=> $excludes_controls,
				],
			],

			'post_type'	=> !$wc ? 'post' : 'product',
			'category'	=> !$wc ? 'category' : 'product_cat',
			'tag'		=> !$wc ? 'tag' : 'product_tag',

			'start_excludes'	=> [],
			'start_controls'	=> $start_controls,

			'end_excludes'	=> [],
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

		self::_add_controls( $object, $start_controls, '', [
			'excludes'	=> $args['start_excludes'],
			'controls'	=> $args['start_controls'],
		] );

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

			self::_add_controls( $object, $tab_name == 'includes' ? $includes_controls : $excludes_controls, '', [
				'excludes'	=> $tab['excludes'],
				'controls'	=> $tab['controls'],
			] );

			$object->end_controls_tab();
		}

		$object->end_controls_tabs();

		self::_add_controls( $object, $end_controls, '', [
			'excludes'	=> $args['end_excludes'],
			'controls'	=> $args['end_controls'],
		] );

		$object->end_controls_section();
	}

	/**
	 * Create query controls section for specialists
	 *
	 * @param object $object
	 * @param string $mode Accepts: offline_visits | online_visits
	 * @return void
	 */
	public static function specialists_query_control( $object, $mode = '', $args = [] ) {
		$query_types = [
			'default'	=> esc_html__( 'Default', 'drplus' ),
			'by_id'		=> esc_html__( 'Manual Selection', 'drplus' ),
		];

		$public_controls = [
			'style'			=> [
				'label'		=> esc_html__( 'Items style', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'multiple'	=> false,
				'default'	=> 'card-1',
				'separator'	=> 'before',
				'options'	=> [
					'card-1'	=> esc_html__( 'Card 1', 'drplus' ),
					'card-2'	=> esc_html__( 'Card 2', 'drplus' ),
					'card-3'	=> esc_html__( 'Card 3', 'drplus' ),
					'list'		=> esc_html__( 'List', 'drplus' ),
				],
			],
			'show_score'	=> [
				'label'			=> esc_html__( 'Show specialist score', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
				'condition'		=> [
					'style'	=> 'card-3'
				]
			],
			'reserve_btn_icon'	=> [
				'label'			=> esc_html__( 'Reserve button icon', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'skin'			=> 'inline',
				'label_block'	=> false,
				'default'		=> [
					'value'		=> 'drplus-icon-messages-2',
					'library'	=> 'drplus-icon'
				],
				'separator'		=> 'after',
				'condition'		=> [
					'style'	=> 'card-3'
				]
			],
			'query_type'	=> [
				'label'		=> esc_html__( 'Query type', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'multiple'	=> false,
				'default'	=> 'default',
				'options'	=> $query_types,
			],
			'query_include_ids' => [
				'label'			=> esc_html__( "Search & Select", 'drplus' ),
				'description'	=> esc_html__( 'Select specialists that you want to include', 'drplus' ),
				'label_block'	=> true,
				'multiple'		=> true,
				'type' 			=> \ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
				'autocomplete'	=> [
					'object'	=> \ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_USER,
					'display'	=> 'detailed',
					'query'		=> [
						"only_{$mode}"		=> true,
						'specialists'		=> true,
						'search_columns'	=> ['user_login', 'user_email', 'ID'],
					],
				],
				'condition'		=> [
					'query_type!'	=> 'default'
				]
			],
			'specialities'	=> [
				'label'			=> esc_html__( "Select specialities", 'drplus' ),
				'label_block'	=> true,
				'multiple'		=> true,
				'type' 			=> \ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
				'autocomplete'	=> [
					'object'	=> \ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_POST,
					'query'		=> [
						'post_type'	=> 'speciality',
					],
				],
			],
			'only_verified'	=> [
				'label'			=> esc_html__( 'Only verified specialists', 'drplus' ),
				'description'	=> esc_html__( 'Show only verified specialists', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
			],
			'verified-text' => [
				'label'			=> esc_html__( 'Verified text', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label_block'	=> true,
				'default'		=> sprintf( esc_html__( 'Verified by %s', 'drplus' ), get_bloginfo( 'name' ) ),
				'description'	=> esc_html__( 'Text that will be displayed for verified specialists. To hide, leave this field blank.', 'drplus' ),
				'dynamic'		=> [
					'active'	=> true,
				],
				'condition'		=> [
					'style!'	=> 'card-3'
				]
			],
			'orderby'	=> [
				'label'		=> esc_html__( 'Order By', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'multiple'	=> false,
				'default'	=> 'user_registered',
				'separator'	=> 'before',
				'options'	=> User::order_by(),
			],
			'order'	=> [
				'label'		=> esc_html__( 'Order', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'multiple'	=> false,
				'default'	=> 'DESC',
				'options'	=> [
					'ASC'	=> esc_html__( 'ASC', 'drplus' ),
					'DESC'	=> esc_html__( 'DESC', 'drplus' ),
				],
				'separator'		=> 'after',
				'condition'		=> [
					'orderby!'	=> ['first_name_alphabetic', 'first_name_reverse', 'last_name_alphabetic', 'last_name_reverse', 'auto'],
				],
			],
			'no_users_message'	=> [
				'label'			=> esc_html__( 'No Specialists Message', 'drplus' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'dynamic'		=> [
					'active'	=> true,
				],
			],
		];

		if( $mode == 'offline_visits' ) {
			$default_controls = [];
		} else {
			$default_controls = [];
		}

		$default_controls = $public_controls + $default_controls;

		$args = Utils::check_default( $args, [
			'section'	=> [
				'name'	=> 'settings_section',
				'label'	=> esc_html__( 'Settings', 'drplus' ),
			],

			'excludes'		=> [],
			'controls'		=> $default_controls,
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

		self::_add_controls( $object, $default_controls, '', $args );

		$object->end_controls_section();
	}

	public static function specialists_seo_control( $object, $args = [] ) {
		$default_controls = [
			'name-tag'	=> [
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'label'		=> esc_html__( 'Name tag', 'drplus' ),
				'default'	=> 'h2',
				'options'	=> Utils::custom_tags()
			],
			'short_bio-tag'	=> [
				'type'			=> \Elementor\Controls_Manager::SELECT,
				'label'			=> esc_html__( 'Subtitle tag', 'drplus' ),
				'description'	=> esc_html__( 'The subtitle is displayed under the name.', 'drplus' ),
				'default'		=> 'div',
				'options'		=> Utils::custom_tags()
			],
		];
		$args = Utils::check_default( $args, [
			'section'	=> [
				'name'	=> 'seo_settings_section',
				'label'	=> esc_html__( 'SEO Settings', 'drplus' ),
			],

			'only'		=> [],
			'excludes'	=> [],
			'controls'	=> $default_controls,
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

		self::_add_controls( $object, $default_controls, '', $args );

		$object->end_controls_section();
	}

	public static function specialists_card_style_control( $object, $mode = 'offline_visit', $is_dark = false ) {
		$base_prefix = $is_dark ? "dark_" : "";
		$base_selector_prefix = $is_dark ? 'html[data-theme="dark"] {{WRAPPER}} ' : '';
		$addition_args = [];
		if( $is_dark ) {
			$addition_args['excludes'] = ElementorControls::dark_excludes();
			$addition_args['section']['condition'] = [
			'enable_dark_mode' 	=> 'yes',
			];
		}
		self::general_style_controls( $object, Utils::merge_sectioned_array( [ // card_
			'prefix'		=> $base_prefix . 'card_',
			'base_selector'	=> $base_selector_prefix . '.specialist-card',
			
			'section'	=> [
				'name'	=> $base_prefix . 'card_',
				'label'	=> self::maybe_dark_label( esc_html__( 'Specialist card', 'drplus' ), $is_dark ),
			],

			'mode'	=> 'wrap',
		], $addition_args ) );
		self::general_style_controls( $object, Utils::merge_sectioned_array( [ // image_
			'prefix'		=> $base_prefix . 'image_',
			'base_selector'	=> $base_selector_prefix . '.specialist-card',
			'selector'		=> '.specialist-avatar-wrap a',

			'section'	=> [
				'name'	=> $base_prefix . 'image_',
				'label'	=> self::maybe_dark_label( esc_html__( 'Specialist image', 'drplus' ), $is_dark ),
			],

			'mode'	=> 'image',
		], $addition_args ) );
		self::general_style_controls( $object, Utils::merge_sectioned_array( [ // name_
			'prefix'		=> $base_prefix . 'name_',
			'base_selector'	=> $base_selector_prefix . '.specialist-card',
			'selector'		=> '.specialist-name',

			'section'	=> [
				'name'	=> $base_prefix . 'name_',
				'label'	=> self::maybe_dark_label( esc_html__( 'Specialist name', 'drplus' ), $is_dark ),
			],

			'mode'	=> 'text',
		], $addition_args ) );
		self::general_style_controls( $object, Utils::merge_sectioned_array( [ // subtitle_
			'prefix'		=> $base_prefix . 'subtitle_',
			'base_selector'	=> $base_selector_prefix . '.specialist-card',
			'selector'		=> '.specialist-short_bio',

			'section'	=> [
				'name'	=> $base_prefix . 'subtitle_',
				'label'	=> self::maybe_dark_label( esc_html__( 'Specialist subtitle', 'drplus' ), $is_dark ),
			],

			'mode'	=> 'text',
		], $addition_args ) );
		self::general_style_controls( $object, Utils::merge_sectioned_array( [ // verified_wrap_
			'prefix'		=> $base_prefix . 'verified_wrap_',
			'base_selector'	=> $base_selector_prefix . '.specialist-card',
			'selector'		=> '.specialist-is-verified',

			'section'	=> [
				'name'	=> $base_prefix . 'verified_wrap_',
				'label'	=> self::maybe_dark_label( esc_html__( 'Specialist verified wrap', 'drplus' ), $is_dark ),
			],

			'mode'	=> 'wrap',
		], $addition_args ) );
		self::general_style_controls( $object, Utils::merge_sectioned_array( [ // verified_icon_
			'prefix'		=> $base_prefix . 'verified_icon_',
			'base_selector'	=> $base_selector_prefix . '.specialist-card',
			'selector'		=> '.specialist-is-verified i',

			'section'	=> [
				'name'	=> $base_prefix . 'verified_icon_',
				'label'	=> self::maybe_dark_label( esc_html__( 'Specialist verified icon', 'drplus' ), $is_dark ),
			],

			'mode'	=> 'icon',
		], $addition_args ) );
		self::general_style_controls( $object, Utils::merge_sectioned_array( [ // verified_text_
			'prefix'		=> $base_prefix . 'verified_text_',
			'base_selector'	=> $base_selector_prefix . '.specialist-card',
			'selector'		=> '.specialist-is-verified-text',

			'section'	=> [
				'name'	=> $base_prefix . 'verified_text_',
				'label'	=> self::maybe_dark_label( esc_html__( 'Specialist verified text', 'drplus' ), $is_dark ),
			],

			'mode'	=> 'text',
		], $addition_args ) );
		if( $mode == 'all' || $mode == 'online_visit' || $mode == 'online_visits' ) {
			self::general_style_controls( $object, Utils::merge_sectioned_array( [ // meta_title_
				'prefix'		=> $base_prefix . 'meta_title_',
				'base_selector'	=> $base_selector_prefix . '.specialist-card',
				'selector'		=> '.specialist-meta-title',
	
				'section'	=> [
					'name'	=> $base_prefix . 'meta_title_',
					'label'	=> self::maybe_dark_label( esc_html__( 'Specialist info title', 'drplus' ), $is_dark ),
				],
	
				'mode'	=> 'text',
			], $addition_args ) );
			self::general_style_controls( $object, Utils::merge_sectioned_array( [ // meta_value_
				'prefix'		=> $base_prefix . 'meta_value_',
				'base_selector'	=> $base_selector_prefix . '.specialist-card',
				'selector'		=> '.specialist-meta-value',
	
				'section'	=> [
					'name'	=> $base_prefix . 'meta_value_',
					'label'	=> self::maybe_dark_label( esc_html__( 'Specialist info value', 'drplus' ), $is_dark ),
				],
	
				'mode'	=> 'text',
			], $addition_args ) );
		}
		if( $mode == 'all' || $mode == 'offline_visit' || $mode == 'offline_visits' ) {
			self::general_style_controls( $object, Utils::merge_sectioned_array( [ // address_icon_
				'prefix'		=> $base_prefix . 'address_icon_',
				'base_selector'	=> $base_selector_prefix . '.specialist-card',
				'selector'		=> '.specialist-meta-address i',
	
				'section'	=> [
					'name'	=> $base_prefix . 'address_icon_',
					'label'	=> self::maybe_dark_label( esc_html__( 'Specialist address icon', 'drplus' ), $is_dark ),
				],
	
				'mode'	=> 'icon',
			], $addition_args ) );
			self::general_style_controls( $object, Utils::merge_sectioned_array( [ // address_
				'prefix'		=> $base_prefix . 'address_',
				'base_selector'	=> $base_selector_prefix . '.specialist-card',
				'selector'		=> '.specialist-meta-address',
	
				'section'	=> [
					'name'	=> $base_prefix . 'address_',
					'label'	=> self::maybe_dark_label( esc_html__( 'Specialist address', 'drplus' ), $is_dark ),
				],
	
				'mode'	=> 'text',
			], $addition_args ) );
		}
		self::general_style_controls( $object, Utils::merge_sectioned_array( [ // book_button_
			'prefix'		=> $base_prefix . 'book_button_',
			'base_selector'	=> $base_selector_prefix . '.specialist-btn',

			'section'	=> [
				'name'	=> $base_prefix . 'book_button_',
				'label'	=> self::maybe_dark_label( esc_html__( 'Book button style', 'drplus' ), $is_dark ),
			],

			'mode'	=> 'wrap',
		], $addition_args ) );
		self::general_style_controls( $object, Utils::merge_sectioned_array( [ // book_button_text_
			'prefix'		=> $base_prefix . 'book_button_text_',
			'base_selector'	=> $base_selector_prefix . '.specialist-btn',
			'selector'		=> '.button-text',

			'section'	=> [
				'name'	=> $base_prefix . 'book_button_text_',
				'label'	=> self::maybe_dark_label( esc_html__( 'Book button text', 'drplus' ), $is_dark ),
			],

			'mode'	=> 'text',
		], $addition_args ) );
		self::general_style_controls( $object, Utils::merge_sectioned_array( [ // book_button_icon_
			'prefix'		=> $base_prefix . 'book_button_icon_',
			'base_selector'	=> $base_selector_prefix . '.specialist-btn',
			'selector'		=> '.button-icon',

			'section'	=> [
				'name'	=> $base_prefix . 'book_button_icon_',
				'label'	=> self::maybe_dark_label( esc_html__( 'Book button icon', 'drplus' ), $is_dark ),
			],

			'mode'	=> 'icon',
		], $addition_args ) );
	}

	public static function section_title_row_style( $object, $is_dark = false ) {
		$prefix = 'section_title_row_';
		$selector = "{{WRAPPER}} .section-title-wrap";
		$section_args = [
			'label'	=> esc_html__( 'Section title row style', 'drplus' ),
			'tab'	=> \Elementor\Controls_Manager::TAB_STYLE,
		];
		if( $is_dark ) {
			$selector = 'html[data-theme="dark"] ' . $selector;
			$prefix = 'dark_' . $prefix;
			$section_args['label'] = self::dark_control_label( $section_args['label'] );
			$section_args['condition'] = [
				'enable_dark_mode' 	=> 'yes',
			];
		}
		$hover_selector = "{$selector}:hover";
		

		$object->start_controls_section(
			"style_{$prefix}section",
			$section_args
		);

		$object->start_controls_tabs( "tabs_{$prefix}style" );

		$object->start_controls_tab( // Normal
			"tab_{$prefix}normal",
			[
				'label'	=> esc_html__( 'Normal', 'drplus' ),
			]
		);

		if( !$is_dark ) {
			self::margin( $object, "{$prefix}margin", $selector );
			self::padding( $object, "{$prefix}padding", $selector );
		}
		self::background( $object, "{$prefix}background", $selector );
		self::border( $object, "{$prefix}border", $selector );
		if( !$is_dark ) {
			self::border_radius( $object, "{$prefix}border_radius", $selector );
		}
		self::box_shadow( $object, "{$prefix}box_shadow", $selector );

		$object->end_controls_tab();

		$object->start_controls_tab( // Hover
			"tab_{$prefix}hover",
			[
				'label' => esc_html__( 'Hover', 'drplus' ),
			]
		);

		if( !$is_dark ) {
			self::margin( $object, "{$prefix}margin_hover", $hover_selector );
			self::padding( $object, "{$prefix}padding_hover", $hover_selector );
		}
		self::background( $object, "{$prefix}background_hover", $hover_selector );
		self::border( $object, "{$prefix}border_hover", $hover_selector );
		if( !$is_dark ) {
			self::border_radius( $object, "{$prefix}border_radius_hover", $hover_selector );
		}
		self::box_shadow( $object, "{$prefix}box_shadow_hover", $hover_selector );

		$object->end_controls_tab();
		$object->end_controls_tabs();

		$object->end_controls_section();
	}

	public static function section_title_icon_style( $object, $is_dark = false ) {
		$prefix = 'icon_';
		$selector = "{{WRAPPER}} .section-title-icon";
		$hover_selector = "{{WRAPPER}} .section-title-wrap:hover .section-title-icon";
		$section_args = [
			'label'	=> esc_html__( 'Section title icon style', 'drplus' ),
			'tab'	=> \Elementor\Controls_Manager::TAB_STYLE,
		];
		if( $is_dark ) {
			$selector = 'html[data-theme="dark"] ' . $selector;
			$hover_selector = 'html[data-theme="dark"] ' . $hover_selector;
			$prefix = 'dark_' . $prefix;
			$section_args['label'] = self::dark_control_label( $section_args['label'] );
			$section_args['condition'] = [
				'enable_dark_mode' 	=> 'yes',
			];
		}


		$object->start_controls_section(
			"style_{$prefix}section",
			$section_args
		);

		$object->start_controls_tabs( "tabs_{$prefix}style" );

		$object->start_controls_tab( // Normal
			"tab_{$prefix}normal",
			[
				'label'	=> esc_html__( 'Normal', 'drplus' ),
			]
		);

		if( !$is_dark ) {
			self::margin( $object, "{$prefix}margin", $selector );
			self::padding( $object, "{$prefix}padding", $selector );
		}
		self::background( $object, "{$prefix}background", $selector );
		self::color( $object, "{$prefix}color", $selector );
		if( !$is_dark ) {
			self::icon_size( $object, "{$prefix}icon_size", $selector );
			self::border_radius( $object, "{$prefix}border_radius", $selector );
		}
		self::border( $object, "{$prefix}border", $selector );
		self::box_shadow( $object, "{$prefix}box_shadow", $selector );
		self::text_shadow( $object, "{$prefix}text_shadow", $selector );

		$object->end_controls_tab();

		$object->start_controls_tab( // Hover
			"tab_{$prefix}hover",
			[
				'label' => esc_html__( 'Hover', 'drplus' ),
			]
		);

		if( !$is_dark ) {
			self::margin( $object, "{$prefix}margin_hover", $hover_selector );
			self::padding( $object, "{$prefix}padding_hover", $hover_selector );
		}
		self::background( $object, "{$prefix}background_hover", $hover_selector );
		self::color( $object, "{$prefix}color_hover", $hover_selector );
		if( !$is_dark ) {
			self::icon_size( $object, "{$prefix}icon_size_hover", $hover_selector );
			self::border_radius( $object, "{$prefix}border_radius_hover", $hover_selector );
		}
		self::border( $object, "{$prefix}border_hover", $hover_selector );
		self::box_shadow( $object, "{$prefix}box_shadow_hover", $hover_selector );
		self::text_shadow( $object, "{$prefix}text_shadow_hover", $hover_selector );

		$object->end_controls_tab();
		$object->end_controls_tabs();

		$object->end_controls_section();
	}

	public static function section_title_styles( $object, $arrows = false, $icon = true, $is_dark = false ) {
		self::section_title_row_style( $object, $is_dark );
		if( $icon ) {
			self::section_title_icon_style( $object, $is_dark );
		}
		self::text_style_controls(
			$object, 
			'.section-title-title',
			'section_title_',
			__( "Section title style", 'drplus' ),
			$is_dark ? 'html[data-theme="dark"] {{WRAPPER}} .section-title-wrap:hover .section-title-title' : '{{WRAPPER}} .section-title-wrap:hover .section-title-title',
			$is_dark
		);
		self::text_style_controls(
			$object, 
			'.section-title-title span',
			'section_title_symbol',
			__( "Section title symbol style", 'drplus' ),
			$is_dark ? 'html[data-theme="dark"] {{WRAPPER}} .section-title-wrap:hover .section-title-title span' : '{{WRAPPER}} .section-title-wrap:hover .section-title-title span',
			$is_dark
		);
		if( $arrows ) {
			self::text_style_controls(
				$object, 
				'.section-title-wrap .button',
				'section_button_',
				__( "Slider arrows style", 'drplus' ),
				'',
				$is_dark
			);
		}
		self::text_style_controls(
			$object, 
			'.section-title-subtitle',
			'section_subtitle_',
			__( "Section subtitle style", 'drplus' ),
			$is_dark ? 'html[data-theme="dark"] {{WRAPPER}} .section-title-wrap:hover .section-title-subtitle' : '{{WRAPPER}} .section-title-wrap:hover .section-title-subtitle',
			$is_dark
		);
	}

	public static function autoplay_controls( $object, array $args = [] ) {
		$default_controls = [
			'autoplay'		=> [
				'label'			=> esc_html__( 'Autoplay', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			],
			'autoplay_time'	=> [
				'label'			=> esc_html__( 'Autoplay time (s)', 'drplus' ),
				'description'	=> esc_html__( 'seconds', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::NUMBER,
				'min'			=> 1,
				'default'		=> 10,
				'condition'		=> [
					'autoplay'	=> 'yes'
				],
			]
		];
		$args = Utils::check_default( $args, [
			'excludes'	=> [],
			'controls'	=> $default_controls,
		] );

		self::_add_controls( $object, $default_controls, '', $args );
	}

	public static function grid_display_settings( $object, $args = [] ) {
		$default_controls = [
			'desktop_cols' => [
				'label'		=> esc_html__( 'Desktop columns', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'max'		=> 10,
				'default'	=> 5,
			],
			'desktop_gap' => [
				'label'		=> esc_html__( 'Desktop gap', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 0,
				'default'	=> 40,
			],
			'tablet_cols' => [
				'label'		=> esc_html__( 'Tablet columns', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'max'		=> 10,
				'default'	=> 2,
			],
			'tablet_gap' => [
				'label'		=> esc_html__( 'Tablet gap', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 0,
				'default'	=> 40,
			],
			'mobile_cols' => [
				'label'		=> esc_html__( 'Mobile columns', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'max'		=> 10,
				'default'	=> 1,
			],
			'mobile_gap' => [
				'label'		=> esc_html__( 'Mobile gap', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 0,
				'default'	=> 40,
			],
		];
		$args = Utils::check_default( $args, [
			'section'	=> [
				'name'	=> 'grid_display_settings_section',
				'label'	=> esc_html__( 'Display settings', 'drplus' ),
			],
			'only'		=> [],
			'excludes'	=> [],
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

		self::_add_controls( $object, $default_controls, '', $args );

		$object->end_controls_section();
	}

	public static function display_settings( $object, $args = [] ) {
		$default_controls = [
			'desktop_slider' => [
				'label'			=> esc_html__( 'Desktop slider', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
			],
			'desktop_slides_type' => [
				'label'		=> esc_html__( "Desktop slides type", 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'count',
				'options'	=> [
					'count'	=> __( 'Count', 'drplus' ),
					'auto'	=> __( 'Auto', 'drplus' ),
				],
				'condition'	=> [
					'desktop_slider'	=> 'yes'
				],
			],
			'desktop_slides' => [
				'label'		=> esc_html__( "Desktop visible slides", 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'default'	=> 4,
				'condition'	=> [
					'desktop_slider'		=> 'yes',
					'desktop_slides_type'	=> 'count',
				]
			],
			'desktop_slides_space' => [
				'label'		=> esc_html__( "Desktop slides space", 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 0,
				'default'	=> 24,
				'condition'	=> [
					'desktop_slider'	=> 'yes',
				]
			],
			'desktop_cols' => [
				'label'		=> esc_html__( 'Desktop columns', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'max'		=> 10,
				'default'	=> 5,
				'condition'	=> [
					'desktop_slider!'	=> 'yes'
				],
			],
			'desktop_gap' => [
				'label'		=> esc_html__( 'Desktop gap', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 0,
				'default'	=> 40,
				'condition'	=> [
					'desktop_slider!'	=> 'yes'
				],
				'selectors'	=> [
					'{{WRAPPER}} .desktop-columns'	=> '--desktop-gap: {{VALUE}}px'
				],
			],
			'tablet_slider' => [
				'label'			=> esc_html__( 'Tablet slider', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
				'separator'		=> 'before',
			],
			'tablet_slides_type' => [
				'label'		=> esc_html__( "Tablet slides type", 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'auto',
				'options'	=> [
					'count'	=> __( 'Count', 'drplus' ),
					'auto'	=> __( 'Auto', 'drplus' ),
				],
				'condition'	=> [
					'tablet_slider'	=> 'yes'
				],
			],
			'tablet_slides' => [
				'label'		=> esc_html__( "Tablet visible slides", 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'default'	=> 4,
				'condition'	=> [
					'tablet_slider'			=> 'yes',
					'tablet_slides_type'	=> 'count',
				]
			],
			'tablet_slides_space' => [
				'label'		=> esc_html__( "Tablet slides space", 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 0,
				'default'	=> 16,
				'condition'	=> [
					'tablet_slider'	=> 'yes',
				]
			],
			'tablet_cols' => [
				'label'		=> esc_html__( 'Tablet columns', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'max'		=> 10,
				'default'	=> 2,
				'condition'	=> [
					'tablet_slider!'	=> 'yes'
				],
			],
			'tablet_gap' => [
				'label'		=> esc_html__( 'Tablet gap', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 0,
				'default'	=> 40,
				'condition'	=> [
					'tablet_slider!'	=> 'yes'
				],
				'selectors'	=> [
					'{{WRAPPER}} .tablet-columns'	=> '--tablet-gap: {{VALUE}}px'
				],
			],
			'mobile_slider' => [
				'label'			=> esc_html__( 'Mobile slider', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
				'separator'		=> 'before',
			],
			'mobile_slides_type' => [
				'label'		=> esc_html__( "Mobile slides type", 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'auto',
				'options'	=> [
					'count'	=> __( 'Count', 'drplus' ),
					'auto'	=> __( 'Auto', 'drplus' ),
				],
				'condition'	=> [
					'mobile_slider'	=> 'yes'
				],
			],
			'mobile_slides' => [
				'label'		=> esc_html__( "Mobile visible slides", 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'default'	=> 4,
				'condition'	=> [
					'mobile_slider'			=> 'yes',
					'mobile_slides_type'	=> 'count',
				]
			],
			'mobile_slides_space' => [
				'label'		=> esc_html__( "Mobile slides space", 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 0,
				'default'	=> 16,
				'condition'	=> [
					'mobile_slider'	=> 'yes',
				]
			],
			'mobile_cols' => [
				'label'		=> esc_html__( 'Mobile columns', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'max'		=> 10,
				'default'	=> 1,
				'condition'	=> [
					'mobile_slider!'	=> 'yes'
				],
			],
			'mobile_gap' => [
				'label'		=> esc_html__( 'Mobile gap', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 0,
				'default'	=> 40,
				'condition'	=> [
					'mobile_slider!'	=> 'yes'
				],
				'selectors'	=> [
					'{{WRAPPER}} .mobile-columns'	=> '--mobile-gap: {{VALUE}}px'
				],
			],
		];
		$args = Utils::check_default( $args, [
			'section'	=> [
				'name'	=> 'display_settings_section',
				'label'	=> esc_html__( 'Display settings', 'drplus' ),
			],
			'only'		=> [],
			'excludes'	=> [],
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

		self::_add_controls( $object, $default_controls, '', $args );

		$object->end_controls_section();
	}

	public static function button_settings( $object, $args = [] ) {
		$args = Utils::check_default( $args, [
			'section'	=> [
				'name'	=> 'button_settings_section',
				'label'	=> esc_html__( 'Button settings', 'drplus' ),
			],
			'excludes'	=> [],
			'controls'	=> [],
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

		self::button_controls( $object, $args );

		$object->end_controls_section();
	}

	public static function button_controls( $object, $args = [] ) {
		$default_controls = [
			'text'			=> [
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label'			=> esc_html__( 'Text', 'drplus' ),
				'label_block'	=> true,
				'default'		=> __( 'Home', 'drplus' ),
				'description'	=> esc_html__( 'HTML tags allowed', 'drplus' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			],
			'link'			=> [
				'label'		=> esc_html__( 'Link', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::URL,
				'dynamic'	=> [
					'active'	=> true,
				],
			],
			'new_tab'		=> [
				'label'			=> esc_html__( "Open in new tab", 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
			],
			'transparent'	=> [
				'label'			=> esc_html__( 'Transparent button', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
			],
			'type'			=> [
				'label'		=> esc_html__( 'Button type', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'primary',
				'options'	=> Utils::button_types(),
				'condition'	=> [
					'button_transparent!'	=> 'yes'
				]
			],
			'small'			=> [
				'label'			=> esc_html__( 'Small button', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
			],
			'icon'			=> [
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Icon', 'drplus' ),
				'skin'			=> 'inline',
				'label_block'	=> false,
			],
			'icon_align'	=> [
				'label'		=> esc_html__( 'Icon Position', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::CHOOSE,
				'default'	=> 'start',
				'options'	=> [
					'start'	=> [
						'title'	=> esc_html__( 'Start', 'drplus' ),
						'icon'	=> 'eicon-h-align-left',
					],
					'end'	=> [
						'title'	=> esc_html__( 'End', 'drplus' ),
						'icon'	=> 'eicon-h-align-right',
					],
				],
				'condition'	=> [
					'button_icon[value]!'	=> '',
				],
			],
			'style'			=> [
				'label'		=> esc_html__( 'Button style', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'rounded',
				'options'	=> Utils::button_styles(),
				'condition'	=> [
					'button_transparent!'	=> 'yes'
				]
			],
			'fullwidth'			=> [
				'label'			=> esc_html__( 'Fullwidth', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
			],
			'align'			=> [
				'label'		=> esc_html__( 'Alignment', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::CHOOSE,
				'options'	=> [
					'start'		=> [
						'title'	=> esc_html__( 'Start', 'drplus' ),
						'icon'	=> 'eicon-text-align-left',
					],
					'center'	=> [
						'title'	=> esc_html__( 'Center', 'drplus' ),
						'icon'	=> 'eicon-text-align-center',
					],
					'end'		=> [
						'title'	=> esc_html__( 'End', 'drplus' ),
						'icon'	=> 'eicon-text-align-right',
					],
				],
				'default'	=> 'start',
				'toggle'	=> true,
				'condition'	=> [
					'button_fullwidth!'	=> 'yes'
				],
			],
		];

		self::_add_controls( $object, $default_controls, "button_", $args );
	}

	public static function pagination_controls( $object, $args = [] ) {
		$default_controls = [
			'ppp'				=> [
				'label'			=> esc_html__( 'Posts per page', 'drplus' ),
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
				'label'			=> esc_html__( 'Offset', 'drplus' ),
				'description'	=> esc_html__( 'The offset causes the first few results to be skipped and provides the number of posts from that point onward.', 'drplus' ),
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
				'label'			=> esc_html__( 'Show pagination', 'drplus' ),
				'description'	=> esc_html__( "Turn off pagination if you don't need it. It can improve the page's performance.", 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
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
				'label'	=> esc_html__( 'Pagination settings', 'drplus' ),
				'condition'		=> [
					'query_type!'	=> ['by_id', 'current_query']
				],
			],
			'excludes'	=> [],
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

	public static function slider_arrow_style_controls( $object, $selector ) {
		$base_selector = "{{WRAPPER}} {$selector}";
		$arrow_selector = '.drplus-slider-nav-btn';
		$selector = "{$base_selector} {$arrow_selector}";
		$hover_selector = "{$selector}:hover";

		$prefix = "slider_arrows_";

		$object->start_controls_section(
			"style_{$prefix}section",
			[
				'label'	=> esc_html__( "Slider arrows style", 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$object->start_controls_tabs( "tabs_{$prefix}style" );

		$object->start_controls_tab( // Normal
			"tab_{$prefix}normal",
			[
				'label'	=> esc_html__( 'Normal', 'drplus' ),
			]
		);

		self::margin( $object, "{$prefix}margin", $selector );
		self::padding( $object, "{$prefix}padding", $selector );
		self::background( $object, "{$prefix}background", $selector );
		self::color( $object, "{$prefix}color", $selector );
		self::icon_size( $object, "{$prefix}icon_size", $selector );
		self::border( $object, "{$prefix}border", $selector );
		self::border_radius( $object, "{$prefix}border_radius", $selector );
		self::box_shadow( $object, "{$prefix}box_shadow", $selector );
		self::text_shadow( $object, "{$prefix}text_shadow", $selector );

		$object->end_controls_tab();

		$object->start_controls_tab( // Hover
			"tab_{$prefix}hover",
			[
				'label' => esc_html__( 'Hover', 'drplus' ),
			]
		);

		self::margin( $object, "{$prefix}margin_hover", $hover_selector );
		self::padding( $object, "{$prefix}padding_hover", $hover_selector );
		self::background( $object, "{$prefix}background_hover", $hover_selector );
		self::color( $object, "{$prefix}color_hover", $hover_selector );
		self::icon_size( $object, "{$prefix}icon_size_hover", $hover_selector );
		self::border( $object, "{$prefix}border_hover", $hover_selector );
		self::border_radius( $object, "{$prefix}border_radius_hover", $hover_selector );
		self::box_shadow( $object, "{$prefix}box_shadow_hover", $hover_selector );
		self::text_shadow( $object, "{$prefix}text_shadow_hover", $hover_selector );

		$object->end_controls_tab();
		$object->end_controls_tabs();

		$object->end_controls_section();
	}

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
					'label'	=> esc_html__( 'Normal', 'drplus' ),
				],
				'hover'	=> [
					'label'	=> esc_html__( 'Hover', 'drplus' ),
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
					$control_selector = $control_args['selector'];
					if( empty( $control_selector ) ) {
						$control_selector = $selector;
					}
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
					if( !empty( $control_args['is_group_control'] ) ) {
						$object->add_group_control(
							$control_args['type'],
							$control_args
						);
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
						$control_hover_selector = $control_args['hover_selector'];
						if( empty( $control_hover_selector ) ) {
							$control_hover_selector = $hover_selector;
						}
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

	public static function pagination_style_controls( $object, bool $wc = false, $is_dark = false ) {
		$base_selector = !$wc ? '.pagination' : '.woocommerce-pagination';
		$prefix = 'pagination_';

		$section_conditions = [
			'show_pagination'	=> 'yes',
			'query_type!'		=> ['by_id', 'current_query']
		];
		$excludes = [];
		if( $is_dark ) {
			$base_selector = 'html[data-theme="dark"] {{WRAPPER}} ' . $base_selector;
			$prefix = 'dark_' . $prefix;
			$section_conditions['enable_dark_mode'] = 'yes';
			$excludes = ['margin','padding','typography','border_radius','text_align','icon_size'];
		}
		self::general_style_controls( $object, [ // pagination
			'prefix'		=> $prefix,
			'base_selector'	=> $base_selector,

			'section'	=> [
				'name'		=> $prefix . 'section',
				'label'		=> self::maybe_dark_label( esc_html__( 'Pagination style', 'drplus' ), $is_dark ),
				'condition'	=> $section_conditions,
			],

			'excludes'	=> $excludes,
			'mode'		=> 'wrap'
		] );
		
		self::general_style_controls( $object, [ // pagination_number
			'prefix'		=> $prefix . 'number_',
			'base_selector'	=> $base_selector,
			'selector'		=> '.page-numbers:is(a,span):not(.next):not(.prev):not(.dots)',
			'hover_type'	=> 'normal',

			'section'	=> [
				'name'		=> $prefix . 'number_section',
				'label'		=> self::maybe_dark_label( esc_html__( 'Pagination number style', 'drplus' ), $is_dark ),
				'condition'	=> $section_conditions,
			],

			'excludes'	=> $excludes,
			'mode' => 'text'
		] );

		self::general_style_controls( $object, [ // pagination_current
			'prefix'		=> $prefix . 'current_',
			'base_selector'	=> $base_selector,
			'selector'		=> '.page-numbers:is(a,span).current',
			'hover_type'	=> 'normal',

			'section'	=> [
				'name'		=> $prefix . 'current_section',
				'label'		=> self::maybe_dark_label( esc_html__( 'Pagination current style', 'drplus' ), $is_dark ),
				'condition'	=> $section_conditions,
			],

			'excludes'	=> $excludes,
			'mode' => 'text'
		] );

		self::general_style_controls( $object, [ // pagination_prev
			'prefix'		=> $prefix . 'prev_',
			'base_selector'	=> $base_selector,
			'selector'		=> '.page-numbers:is(a,span).prev',
			'hover_type'	=> 'normal',

			'section'	=> [
				'name'		=> $prefix . 'prev_section',
				'label'		=> self::maybe_dark_label( esc_html__( 'Pagination previous style', 'drplus' ), $is_dark ),
				'condition'	=> $section_conditions,
			],

			'excludes'	=> $excludes,
			'mode' => 'text'
		] );

		self::general_style_controls( $object, [ // pagination_next
			'prefix'		=> $prefix . 'next_',
			'base_selector'	=> $base_selector,
			'selector'		=> '.page-numbers:is(a,span).next',
			'hover_type'	=> 'normal',

			'section'	=> [
				'name'		=> $prefix . 'next_section',
				'label'		=> self::maybe_dark_label( esc_html__( 'Pagination next style', 'drplus' ), $is_dark ),
				'condition'	=> $section_conditions,
			],

			'excludes'	=> $excludes,
			'mode' => 'text'
		] );

		self::general_style_controls( $object, [ // pagination_dots
			'prefix'		=> $prefix . 'dots_',
			'base_selector'	=> $base_selector,
			'selector'		=> '.page-numbers:is(a,span).dots',
			'hover_type'	=> 'normal',

			'section'	=> [
				'name'		=> $prefix . 'dots_section',
				'label'		=> self::maybe_dark_label( esc_html__( 'Pagination dots style', 'drplus' ), $is_dark ),
				'condition'	=> $section_conditions,
			],

			'excludes'	=> $excludes,
			'mode'	=> 'text'
		] );
	}

	public static function dark_mode_toggle_controls( $object ) {
		$object->start_controls_section(
			'dark_mode_toggle',
			[
				'label'	=> esc_html__( 'Dark mode', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$object->add_control(
			'enable_dark_mode',
			[
				'label'			=> esc_html__( 'Customize dark mode styles', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
				'description'	=> esc_html__( 'Enable to set separate colors for dark mode.', 'drplus' ),
			]
		);

		$object->end_controls_section();
	}

	public static function dark_excludes() {
		return ['margin', 'padding', 'typography', 'border_radius', 'text_align', 'icon_size'];
	}

	public static function dark_condition() {
		return [
			'enable_dark_mode' 	=> 'yes',
		];
	}

	public static function dark_control_label( $label ) {
		return sprintf( '%s %s', $label, esc_html__( '(Dark)', 'drplus' ) );
	}

	public static function maybe_dark_label( $label, $is_dark ) {
		return $is_dark ? self::dark_control_label( $label ) : $label;
	}
}