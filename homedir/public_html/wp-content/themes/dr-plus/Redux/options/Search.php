<?php

use DrPlus\Utils;
use DrPlus\Utils\Search;

defined( 'ABSPATH' ) || exit;

Redux::set_section( // Search page settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'Search page settings', 'drplus' ),
		'id'			=> 'search-page-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // general_search_no_results
				'id'		=> 'general_search_no_results',
				'type'		=> 'text',
				'title'		=> __( 'Search no results', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), __( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'drplus' ) ),
				'compiler'	=> true,
				'default'	=> __( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'drplus' ),
			],
			[ // search_breadcrumb
				'id'		=> 'search_breadcrumb',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show breadcrumb', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // search_show_sidebar
				'id'		=> 'search_show_sidebar',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show search page sidebar', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // search_sidebar
				'id'		=> 'search_sidebar',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Search page sidebar', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Search page sidebar', 'drplus' ) ),
				'data'		=> 'sidebars',
				'default'	=> 'search',
				'required'	=> [
					['search_show_sidebar','=',true],
				]
			],
			[ // search_show_cities
				'id'		=> 'search_show_cities',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show cities field', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
			],
		),
	)
);

$post_types_fields = [
	[ // exclude_post_types
		'id'		=> 'exclude_post_types',
		'type'		=> 'select',
		'title'		=> __( 'Exclude post types', 'drplus' ),
		'data'		=> 'post_types',
		'multi'		=> true,
		'default'	=> ['page', 'attachment', 'e-floating-buttons'],
	],
	[
		'id'	=> 'search_divider_1',
		'type'	=> 'divide',
	],
	[ // search_post_title_tag
		'id'		=> 'search_post_title_tag',
		'type'		=> 'select',
		'title'		=> esc_html__( 'Posts title tag', 'drplus' ), 
		'subtitle'	=> sprintf( esc_html__( "Default: %s", 'drplus' ), __( "H2", 'drplus' ) ),
		'options'	=> Utils::custom_tags(),
		'default'	=> 'h2',
		'required'	=> [
			['exclude_post_types', 'not_contain', 'post'],
		],
	],
	[ // search_post_time_type
		'id'		=> 'search_post_time_type',
		'type'		=> 'select',
		'title'		=> esc_html__( 'Posts time type', 'drplus' ),
		'subtitle'	=> sprintf( esc_html__( "Default: %s", 'drplus' ), __( "Date", 'drplus' ) ),
		'options'	=> [
			'date'			=> __( "Date", 'drplus' ),
			'difference'	=> __( "Difference", 'drplus' ),
		],
		'default'	=> 'date',
		'required'	=> [
			['exclude_post_types', 'not_contain', 'post'],
		],
	],
	[ // search_post_show_read_more
		'id'		=> 'search_post_show_read_more',
		'type'		=> 'switch',
		'title'		=> esc_html__( 'Show posts read more button', 'drplus' ),
		'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
		'on'		=> esc_html__( 'Show', 'drplus' ),
		'off'		=> esc_html__( 'Hide', 'drplus' ),
		'default'	=> true,
		'required'	=> [
			['exclude_post_types', 'not_contain', 'post'],
		],
	],
	[ // search_post_read_more_text
		'id'		=> 'search_post_read_more_text',
		'type'		=> 'text',
		'title'		=> __( 'Posts read more text', 'drplus' ),
		'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Read more', 'drplus' ) ),
		'compiler'	=> true,
		'default'	=> __( 'Read more', 'drplus' ),
		'required'	=> [
			['search_post_show_read_more','=',true],
			['exclude_post_types', 'not_contain', 'post'],
		],
	],
	[ // search_post_read_more_icon
		'id'			=> 'search_post_read_more_icon',
		'type'			=> 'icon_select',
		'title'			=> esc_html__( 'Posts read more icon', 'drplus' ),
		'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'drplus' ), is_rtl() ? 'drplus-icon-arrow-left' : 'drplus-icon-arrow-right' ),
		'compiler'		=> true,
		'default'		=> is_rtl() ? 'drplus-icon-arrow-left' : 'drplus-icon-arrow-right',
		'enqueue_frontend'	=> false,
		'stylesheet'	=> [
			[
				'url'		=> DRPLUS_URI . 'assets/css/iconly.min.css',
				'title'		=> __( 'Doctor plus icons', 'drplus' ),
				'prefix'	=> 'drplus-icon',
			],
		],
		'required'		=> [
			['search_post_show_read_more','=',true],
			['exclude_post_types', 'not_contain', 'post'],
		],
	],
	[
		'id'	=> 'search_divider_2',
		'type'	=> 'divide',
		'desc'	=> esc_html__( "In this section you can set icons for each post types in search page", 'drplus' )
	],
];
$default_section_icons = [
	'speciality'	=> 'drplus-icon-plus',
	'product'		=> 'drplus-icon-shopping-cart',
	'hospital'		=> 'drplus-icon-hospital-pin',
	'post'			=> 'drplus-icon-diamond',
];

foreach( Search::get_post_types_object() as $post_type ) {
	$post_types_fields[] = [
		'id'			=> "search_{$post_type->name}_icon",
		'type'			=> 'icon_select',
		/* translators: %s: post type name. */
		'title'			=> sprintf( esc_html__( "%s icon", 'drplus' ), $post_type->label ),
		'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), $default_section_icons[$post_type->name] ?? 'drplus-icon-grid-fill' ),
		'default'		=> $default_section_icons[$post_type->name] ?? 'drplus-icon-grid-fill',
		'enqueue_frontend'	=> false,
		'stylesheet'	=> [
			[
				'url'		=> DRPLUS_URI . 'assets/css/iconly.min.css',
				'title'		=> __( 'Doctor plus icons', 'drplus' ),
				'prefix'	=> 'drplus-icon',
			],
		],
		'required'	=> [
			['exclude_post_types', 'not_contain', $post_type->name]
		]
	];
}

Redux::set_section( // Post types settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'Post types settings', 'drplus' ),
		'id'			=> 'search-post-types-section',
		'subsection'	=> true,
		'fields'		=> $post_types_fields,
	)
);

Redux::set_section( // Specialist settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'Specialist settings', 'drplus' ),
		'id'			=> 'search-specialists-section',
		'subsection'	=> true,
		'fields'		=> [
			[ // search_specialist
				'id'		=> 'search_specialist',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Search in specialists', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
			],
			[ // search_specialist_icon
				'id'			=> "search_specialist_icon",
				'type'			=> 'icon_select',
				'title'			=> esc_html__( "Specialists icon", 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), 'drplus-icon-stethoscope' ),
				'default'		=> 'drplus-icon-stethoscope',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> DRPLUS_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Doctor plus icons', 'drplus' ),
						'prefix'	=> 'drplus-icon',
					],
				],
				'required'	=> [
					['search_specialist','=',true]
				]
			],
			[ // search_specialist_title
				'id'		=> 'search_specialist_title',
				'type'		=> 'text',
				'title'		=> __( 'Specialists', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), __( 'Specialists', 'drplus' ) ),
				'compiler'	=> true,
				'default'	=> __( 'Specialists', 'drplus' ),
				'required'	=> [
					['search_specialist','=',true]
				],
			],
		],
	)
);