<?php

use DrPlus\Utils;
use DrPlus\Utils\Archive;

defined( 'ABSPATH' ) || exit;

Redux::set_section( // Archives settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'Archives settings', 'drplus' ),
		'id'			=> 'general-archive-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // default_archive_sort
				'id'		=> 'default_archive_sort',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Default archive sort', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( "Default: %s", 'drplus' ), _x( "Newest", 'Archive sort item', 'drplus' ) ),
				'options'	=> Archive::sorts(),
				'default'	=> 'newest'
			],
			[ // archive_posts_style
				'id'		=> 'archive_posts_style',
				'type'		=> 'image_select',
				'title'		=> esc_html__( "Default posts card style", 'drplus' ),
				'subtitle'	=> esc_html__( "Default posts card style in archives, categories, tags, search and other general pages", 'drplus' ),
				'options'	=> [
					'style-1'	=> [
						'alt'	=> esc_html__( "Posts style 1", 'drplus' ),
						'img'	=> DRPLUS_URI . "assets/images/backend/posts-style-1.png"
					],
					'style-2'	=> [
						'alt'	=> esc_html__( "Posts style 2", 'drplus' ),
						'img'	=> DRPLUS_URI . "assets/images/backend/posts-style-2.jpg"
					],
				],
				'default'	=> 'style-1',
			],
			[ // archive_breadcrumb
				'id'		=> 'archive_breadcrumb',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show breadcrumb', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // archive_show_title
				'id'		=> 'archive_show_title',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show archive title', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // archive_title_icon
				'id'			=> 'archive_title_icon',
				'type'			=> 'icon_select',
				'title'			=> esc_html__( 'Archive title icon', 'drplus' ),
				'compiler'		=> true,
				'default'		=> 'drplus-icon-discovery',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> DRPLUS_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Doctor plus icons', 'drplus' ),
						'prefix'	=> 'drplus-icon',
					],
				],
				'required'		=> [
					['archive_show_title','=',true]
				]
			],
			[ // archive_show_sidebar
				'id'		=> 'archive_show_sidebar',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show archive sidebar', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // archive_sidebar
				'id'		=> 'archive_sidebar',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Archive sidebar', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Blog sidebar', 'drplus' ) ),
				'data'		=> 'sidebars',
				'default'	=> 'blog',
				'required'	=> [
					['archive_show_sidebar','=',true],
				]
			],
			[ // archive_desktop_cols
				'id'		=> 'archive_desktop_cols',
				'type'		=> 'spinner',
				'title'		=> esc_html__( "Desktop columns", 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '3' ),
				'min'		=> 1,
				'max'		=> 6,
				'default'	=> 3,
			],
			[ // archive_desktop_gap
				'id'		=> 'archive_desktop_gap',
				'type'		=> 'spinner',
				'title'		=> esc_html__( "Desktop gap (px)", 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '24' ),
				'min'		=> 1,
				'max'		=> 64,
				'default'	=> 24,
			],
			[ // archive_tablet_cols
				'id'		=> 'archive_tablet_cols',
				'type'		=> 'spinner',
				'title'		=> esc_html__( "Tablet columns", 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '2' ),
				'min'		=> 1,
				'max'		=> 6,
				'default'	=> 2,
			],
			[ // archive_tablet_gap
				'id'		=> 'archive_tablet_gap',
				'type'		=> 'spinner',
				'title'		=> esc_html__( "Tablet gap (px)", 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '24' ),
				'min'		=> 1,
				'max'		=> 64,
				'default'	=> 16,
			],
			[ // archive_mobile_cols
				'id'		=> 'archive_mobile_cols',
				'type'		=> 'spinner',
				'title'		=> esc_html__( "Mobile columns", 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '1' ),
				'min'		=> 1,
				'max'		=> 6,
				'default'	=> 1,
			],
			[ // archive_mobile_gap
				'id'		=> 'archive_mobile_gap',
				'type'		=> 'spinner',
				'title'		=> esc_html__( "Mobile gap (px)", 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), '24' ),
				'min'		=> 1,
				'max'		=> 64,
				'default'	=> 16,
			],
		),
	)
);

Redux::set_section( // Posts settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'Posts settings', 'drplus' ),
		'id'			=> 'posts-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // archive_post_title_tag
				'id'		=> 'archive_post_title_tag',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Archive post title tag', 'drplus' ), 
				'subtitle'	=> sprintf( esc_html__( "Default: %s", 'drplus' ), __( "H2", 'drplus' ) ),
				'options'	=> Utils::custom_tags(),
				'default'	=> 'h2',
			],
			[ // archive_post_show_time
				'id'		=> 'archive_post_show_time',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show post time', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
			],
			[ // archive_post_time_type
				'id'		=> 'archive_post_time_type',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Archive post time type', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( "Default: %s", 'drplus' ), __( "Date", 'drplus' ) ),
				'options'	=> [
					'date'			=> __( "Date", 'drplus' ),
					'difference'	=> __( "Difference", 'drplus' ),
				],
				'default'	=> 'date',
				'required'	=> [
					['archive_post_show_time','=',true]
				],
			],
			[ // archive_post_show_read_more
				'id'		=> 'archive_post_show_read_more',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show read more button', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Show', 'drplus' ) ),
				'on'		=> esc_html__( 'Show', 'drplus' ),
				'off'		=> esc_html__( 'Hide', 'drplus' ),
				'default'	=> true,
			],
			[ // archive_post_read_more_text
				'id'		=> 'archive_post_read_more_text',
				'type'		=> 'text',
				'title'		=> __( 'Read more text', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Read more', 'drplus' ) ),
				'compiler'	=> true,
				'default'	=> __( 'Read more', 'drplus' ),
				'required'	=> [
					['archive_post_show_read_more','=',true]
				],
			],
			[ // archive_post_read_more_icon
				'id'			=> 'archive_post_read_more_icon',
				'type'			=> 'icon_select',
				'title'			=> esc_html__( 'Read more icon', 'drplus' ),
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
					['archive_post_show_read_more','=',true]
				],
			],
		)
	)
);

Redux::set_section( // Post view settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'Post view settings', 'drplus' ),
		'id'			=> 'posts-post-view-section',
		'subsection'	=> true,
		'fields'		=> array(
			[
				'id'		=> 'post_views_status',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Post views', 'drplus' ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true
			],
			[
				'id'		=> 'min_post_views',
				'type'		=> 'spinner',
				'title'		=> __( 'Minimum post views to show', 'drplus' ),
				'subtitle'	=> __( 'If views of a post is less than this value, it will not be shown', 'drplus' ),
				'default'	=> '0',
				'min'		=> '0',
				'max'		=> '1000000000000',
				'required'	=> [
					['post_views_status','=',true]
				]
			]
		),
	)
);

Redux::set_section( // Single post settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'Single post settings', 'drplus' ),
		'id'			=> 'posts-single-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // single_post_show_breadcrumb
				'id'		=> 'single_post_show_breadcrumb',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show post breadcrumb', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( "Default: %s", 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
			],
			[ // single_post_show_thumbnail
				'id'		=> 'single_post_show_thumbnail',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show post thumbnail', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( "Default: %s", 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
			],
			[ // single_post_show_time
				'id'		=> 'single_post_show_time',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show post time', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( "Default: %s", 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
			],
			[ // single_post_show_comment_count
				'id'		=> 'single_post_show_comment_count',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show comment count', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( "Default: %s", 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
			],
			[ // single_post_show_view_count
				'id'		=> 'single_post_show_view_count',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show view count', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( "Default: %s", 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
			],
			[ // single_post_show_author
				'id'		=> 'single_post_show_author',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show author', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( "Default: %s", 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
			],
			[ // single_post_show_share
				'id'		=> 'single_post_show_share',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show share button', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( "Default: %s", 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
			],
			[ // single_post_images_style
				'id'		=> 'single_post_images_style',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Using theme style for images', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( "Default: %s", 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
			],

			[ // divider
				'id'	=> 'single_post_end_posts_divider',
				'type'	=> 'divide',
			],

			[ // single_post_show_end_posts
				'id'		=> 'single_post_show_end_posts',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show end posts in single page', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( "Default: %s", 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
			],
			[ // single_post_end_posts_title
				'id'			=> 'single_post_end_posts_title',
				'type'			=> 'text',
				'title'			=> esc_html__( 'End posts title', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Health Magazine', 'drplus' ) ),
				'default'		=> esc_html__( 'Health Magazine', 'drplus' ),
				'placeholder'	=> esc_html__( 'Health Magazine', 'drplus' ),
				'required'		=> [
					['single_post_show_end_posts','=',true],
				],
			],
			[ // single_post_end_posts_title_icon
				'id'				=> 'single_post_end_posts_title_icon',
				'type'				=> 'icon_select',
				'title'				=> esc_html__( 'End posts title icon', 'drplus' ),
				'subtitle'			=> sprintf( __( "Default: %s", 'drplus' ), 'drplus-icon-diamond' ),
				'default'			=> 'drplus-icon-diamond',
				'enqueue_frontend'	=> false,
				'stylesheet'		=> [
					[
						'url'		=> DRPLUS_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Doctor plus icons', 'drplus' ),
						'prefix'	=> 'drplus-icon',
					],
				],
				'required'			=> [
					['single_post_show_end_posts','=',true],
				],
			],
			[ // single_post_end_posts_title_tag
				'id'		=> 'single_post_end_posts_title_tag',
				'type'		=> 'select',
				'title'		=> __( 'End posts title tag', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'H3', 'drplus' ) ),
				'default'	=> 'h3',
				'options'	=> $tags,
				'required'		=> [
					['single_post_show_end_posts','=',true],
				]
			],
			[ // single_post_time_type
				'id'		=> 'single_post_time_type',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Single post time type', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( "Default: %s", 'drplus' ), __( "Date", 'drplus' ) ),
				'options'	=> [
					'date'			=> __( "Date", 'drplus' ),
					'difference'	=> __( "Difference", 'drplus' ),
				],
				'default'	=> 'date'
			],
			[ // single_post_end_posts_ppp
				'id'		=> 'single_post_end_posts_ppp',
				'type'		=> 'spinner',
				'title'		=> __( 'Post to show', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), '4' ),
				'default'	=> '4',
				'min'		=> '1',
				'max'		=> '8',
				'required'	=> [
					['single_post_show_end_posts','=',true]
				]
			],
			[ // single_post_end_posts_type
				'id'		=> 'single_post_end_posts_type',
				'type'		=> 'select',
				'title'		=> __( 'End posts type', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Related posts', 'drplus' ) ),
				'default'	=> 'related',
				'options'	=> [
					'related'	=> esc_html__( "Related posts", 'drplus' ),
					'latests'	=> esc_html__( "Latests posts", 'drplus' ),
				],
				'required'		=> [
					['single_post_show_end_posts','=',true],
				]
			],
			[ // single_post_end_posts_term_type
				'id'		=> 'single_post_end_posts_term_type',
				'type'		=> 'select',
				'title'		=> __( 'End posts related type', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Category', 'drplus' ) ),
				'default'	=> 'category',
				'options'	=> [
					'category'	=> esc_html__( "Same category", 'drplus' ),
					'tag'		=> esc_html__( "Same tag", 'drplus' ),
				],
				'required'		=> [
					['single_post_show_end_posts','=',true],
					['single_post_end_posts_type','=','related'],
				]
			],
		),
	)
);