<?php
namespace MJ\Whitebox\ElementorControls;

use MJ\Whitebox\ElementorControls;
use MJ\Whitebox\Utils;

class SectionTitle extends ElementorControls {
	/**
	 * Create section title controls
	 *
	 * @param object $object
	 * @param array $args 
	 * @return void
	 */
	public static function settings( $object, array $args = [] ) {
		$args = Utils::check_default( $args, [
			'section'	=> [
				'name'	=> 'section_title_section',
				'label'	=> esc_html__( 'Section title', 'mj-whitebox' ),
			],
			'excludes'	=> [],
			'only'		=> [],
			'controls'	=> [], // Additional controls or other settings for current controls
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

		self::controls( $object, $args );

		$object->end_controls_section();
	}

	public static function default_controls() {
		return [
			'tag'	=> [
				'type'			=> \Elementor\Controls_Manager::SELECT,
				'label'			=> esc_html__( 'Tag', 'mj-whitebox' ),
				'label_block'	=> true,
				'default'		=> 'h2',
				'options'		=> Utils::custom_tags()
			],
			'icon'	=> [
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Title icon', 'mj-whitebox' ),
				'skin'			=> 'inline',
				'label_block'	=> false,
			],
			'title'	=> [
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label'			=> esc_html__( "Title", 'mj-whitebox' ),
				'label_block'	=> true,
				'default'		=> esc_html__( "Lorem", 'mj-whitebox' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			],
			'link'	=> [
				'label'		=> esc_html__( 'Title link', 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::URL,
				'separator'	=> 'after',
				'default'	=> [
					'url'	=> '#'
				],
				'dynamic'	=> [
					'active'	=> true,
				],
			],
		];
	}

	/**
	 * Add section title controls to an Elementor widget.
	 *
	 * @param \Elementor\Widget_Base $object The Elementor widget instance to which controls are added.
	 * @param array[string|mixed] $args Optional arguments, including 'prefix' and control overrides.
	 *
	 * @return void
	 */
	public static function controls( $object, $args = [] ) {
		if( !isset( $args['prefix'] ) ) $args['prefix'] = 'section_title_';

		parent::_add_controls( $object, static::default_controls(), $args['prefix'], $args );
	}

	public static function row_style( $object, $args = [] ) {
		$args = Utils::check_default( $args, [
			'prefix'	=> 'section_title_row_',
			'selector'	=> '.section-title-wrap',
			
			'section'	=> [
				'name'	=> 'section_title_row',
				'label'	=> esc_html__( 'Section title row style', 'mj-whitebox' ),
			],

			'mode'	=> 'wrap',
		] );
		parent::general_style_controls( $object, $args );
	}

	public static function icon_style( $object, $args = [] ) {
		$args = Utils::check_default( $args, [
			'prefix'		=> 'section_title_icon_',
			'base_selector'	=> '.section-title-wrap',
			'selector'		=> '.section-title-icon',
			
			'section'	=> [
				'name'	=> 'section_title_icon',
				'label'	=> esc_html__( 'Section title icon style', 'mj-whitebox' ),
			],

			'mode'	=> 'icon',
		] );
		parent::general_style_controls( $object, $args );
	}

	public static function title_style( $object, $args = [] ) {
		$args = Utils::check_default( $args, [
			'prefix'		=> 'section_title_',
			'base_selector'	=> '.section-title-wrap',
			'selector'		=> '.section-title-title',
			
			'section'	=> [
				'name'	=> 'section_title',
				'label'	=> esc_html__( 'Section title text style', 'mj-whitebox' ),
			],

			'mode'	=> 'text',
		] );
		parent::general_style_controls( $object, $args );
	}

	public static function arrows_style( $object, $args = [] ) {
		$args = Utils::check_default( $args, [
			'prefix'	=> 'section_button_',
			'selector'	=> '.section-title .button',
			
			'section'	=> [
				'name'	=> 'section_button',
				'label'	=> esc_html__( 'Slider arrows style', 'mj-whitebox' ),
			],

			'mode'	=> 'icon',
		] );
		parent::general_style_controls( $object, $args );
	}

	public static function styles( $object, $icon = true, $arrows = false, $args = [] ) {
		$args = Utils::check_default( $args, [
			'row'		=> [],
			'icon'		=> [],
			'title'		=> [],
			'arrows'	=> [],
		] );
		self::row_style( $object, $args['row'] );
		if( $icon ) {
			self::icon_style( $object, $args['icon'] );
		}
		self::title_style( $object, $args['title'] );
		if( $arrows ) {
			self::arrows_style( $object, $args['arrows'] );
		}
	}
}