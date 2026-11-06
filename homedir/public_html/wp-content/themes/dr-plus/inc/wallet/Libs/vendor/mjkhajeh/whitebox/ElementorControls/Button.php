<?php
namespace MJ\Whitebox\ElementorControls;

use MJ\Whitebox\ElementorControls;
use MJ\Whitebox\Utils;
use MJ\Whitebox\Utils\Elementor;

class Button extends ElementorControls {
	public static function settings( $object, $args = [], $prefix = "button_" ) {
		$args = Utils::check_default( $args, [
			'section'	=> [
				'name'	=> 'button_settings_section',
				'label'	=> esc_html__( 'Button settings', 'mj-whitebox' ),
			],
			'excludes'	=> [],
			'only'		=> [],
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

		self::controls( $object, $args, $prefix );

		$object->end_controls_section();
	}

	public static function default_controls( $args = [], $prefix = 'button_' ) {
		return [
			'text'			=> [
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label'			=> esc_html__( 'Text', 'mj-whitebox' ),
				'label_block'	=> true,
				'default'		=> __( 'Home', 'mj-whitebox' ),
				'description'	=> esc_html__( 'HTML tags allowed', 'mj-whitebox' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			],
			'link'			=> [
				'label'		=> esc_html__( 'Link', 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::URL,
				'dynamic'	=> [
					'active'	=> true,
				],
			],
			'new_tab'		=> [
				'label'			=> esc_html__( "Open in new tab", 'mj-whitebox' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'mj-whitebox' ),
				'label_off'		=> esc_html__( 'No', 'mj-whitebox' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
			],
			'transparent'	=> [
				'label'			=> esc_html__( 'Transparent button', 'mj-whitebox' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'mj-whitebox' ),
				'label_off'		=> esc_html__( 'No', 'mj-whitebox' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
			],
			'type'			=> [
				'label'		=> esc_html__( 'Button type', 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'primary',
				'options'	=> Elementor::button_types( $args ),
				'condition'	=> [
					$prefix . 'transparent!'	=> 'yes'
				]
			],
			'small'			=> [
				'label'			=> esc_html__( 'Small button', 'mj-whitebox' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'mj-whitebox' ),
				'label_off'		=> esc_html__( 'No', 'mj-whitebox' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
			],
			'icon'			=> [
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Icon', 'mj-whitebox' ),
				'skin'			=> 'inline',
				'label_block'	=> false,
			],
			'icon_align'	=> [
				'label'		=> esc_html__( 'Icon Position', 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::CHOOSE,
				'default'	=> 'start',
				'options'	=> [
					'start'	=> [
						'title'	=> esc_html__( 'Start', 'mj-whitebox' ),
						'icon'	=> 'eicon-h-align-left',
					],
					'end'	=> [
						'title'	=> esc_html__( 'End', 'mj-whitebox' ),
						'icon'	=> 'eicon-h-align-right',
					],
				],
				'condition'	=> [
					$prefix . 'icon[value]!'	=> '',
				],
			],
			'style'			=> [
				'label'		=> esc_html__( 'Button style', 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'rounded',
				'options'	=> Elementor::button_styles( $args ),
			],
			'fullwidth'			=> [
				'label'			=> esc_html__( 'Fullwidth', 'mj-whitebox' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'mj-whitebox' ),
				'label_off'		=> esc_html__( 'No', 'mj-whitebox' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
			],
			'align'			=> [
				'label'		=> esc_html__( 'Alignment', 'mj-whitebox' ),
				'type'		=> \Elementor\Controls_Manager::CHOOSE,
				'options'	=> [
					'start'		=> [
						'title'	=> esc_html__( 'Start', 'mj-whitebox' ),
						'icon'	=> 'eicon-text-align-left',
					],
					'center'	=> [
						'title'	=> esc_html__( 'Center', 'mj-whitebox' ),
						'icon'	=> 'eicon-text-align-center',
					],
					'end'		=> [
						'title'	=> esc_html__( 'End', 'mj-whitebox' ),
						'icon'	=> 'eicon-text-align-right',
					],
				],
				'default'	=> 'start',
				'toggle'	=> true,
				'condition'	=> [
					$prefix . 'fullwidth!'	=> 'yes'
				],
			],
		];
	}

	public static function controls( $object, $args = [], $prefix = 'button_' ) {
		parent::_add_controls( $object, static::default_controls( [], $prefix ), $prefix, $args );
	}
}