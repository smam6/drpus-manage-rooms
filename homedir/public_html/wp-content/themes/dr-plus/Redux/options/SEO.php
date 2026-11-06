<?php
defined( 'ABSPATH' ) || exit;

Redux::set_section( // General
	$opt_name,
	array(
		'title'			=> esc_html__( 'Title tags', 'drplus' ),
		'id'			=> 'seo-title-tags-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // homepage-site-title-tag
				'id'		=> 'homepage-site-title-tag',
				'type'		=> 'select',
				'title'		=> __( 'Home page site title', 'drplus' ),
				'default'	=> 'h1',
				'options'	=> $tags
			],
			[ // otherpage-site-title-tag
				'id'		=> 'otherpage-site-title-tag',
				'type'		=> 'select',
				'title'		=> __( 'Other pages site title', 'drplus' ),
				'default'	=> 'div',
				'options'	=> $tags
			],
			[ // archive-title-tag
				'id'		=> 'archive-title-tag',
				'type'		=> 'select',
				'title'		=> __( 'Archive page title', 'drplus' ),
				'default'	=> 'h1',
				'options'	=> $tags
			],
			[ // shop-page-title-tag
				'id'		=> 'shop-page-title-tag',
				'type'		=> 'select',
				'title'		=> __( 'Shop page title', 'drplus' ),
				'default'	=> 'h1',
				'options'	=> $tags
			],
			[ // page-title-tag
				'id'		=> 'page-title-tag',
				'type'		=> 'select',
				'title'		=> __( 'Pages title', 'drplus' ),
				'default'	=> 'h1',
				'options'	=> $tags
			],
			[ // post-title-tag
				'id'		=> 'post-title-tag',
				'type'		=> 'select',
				'title'		=> __( 'Single post title', 'drplus' ),
				'default'	=> 'h1',
				'options'	=> $tags
			],
			[ // product-title-tag
				'id'		=> 'product-title-tag',
				'type'		=> 'select',
				'title'		=> __( 'Single product title', 'drplus' ),
				'default'	=> 'h1',
				'options'	=> $tags
			],
			[ // single_specialist_title_tag
				'id'		=> 'single_specialist_title_tag',
				'type'		=> 'select',
				'title'		=> __( 'Single specialist name', 'drplus' ),
				'default'	=> 'h1',
				'options'	=> $tags,
			],
			[ // single_specialist_subtitle_tag
				'id'		=> 'single_specialist_subtitle_tag',
				'type'		=> 'select',
				'title'		=> __( 'Single specialist subtitle', 'drplus' ),
				'default'	=> 'h2',
				'options'	=> $tags,
			],
			[ // single_specialist_sections_tag
				'id'		=> 'single_specialist_sections_tag',
				'type'		=> 'select',
				'title'		=> __( 'Single specialist sections', 'drplus' ),
				'default'	=> 'h3',
				'options'	=> $tags,
			],
			[ // single_specialist_sections_tag
				'id'		=> 'single_specialist_sections_tag',
				'type'		=> 'select',
				'title'		=> __( 'Single specialist sections', 'drplus' ),
				'default'	=> 'h3',
				'options'	=> $tags,
			],
			[ // single_specialist_related_specialists_name_tag
				'id'		=> 'single_specialist_related_specialists_name_tag',
				'type'		=> 'select',
				'title'		=> __( 'Single specialist related specialist name', 'drplus' ),
				'default'	=> 'h3',
				'options'	=> $tags,
			],
			[ // single_specialist_related_specialists_short_bio_tag
				'id'		=> 'single_specialist_related_specialists_short_bio_tag',
				'type'		=> 'select',
				'title'		=> __( 'Single specialist related subtitle', 'drplus' ),
				'default'	=> 'h3',
				'options'	=> $tags,
			],
			[ // hospital-single-title-tag
				'id'		=> 'hospital-single-title-tag',
				'type'		=> 'select',
				'title'		=> __( 'Single hospital title', 'drplus' ),
				'default'	=> 'h1',
				'options'	=> $tags,
				'required'	=> [
					['single_hospital_head_title','=',true]
				]
			],
			[ // hospital-single-subtitle-tag
				'id'		=> 'hospital-single-subtitle-tag',
				'type'		=> 'select',
				'title'		=> __( 'Single hospital subtitle', 'drplus' ),
				'default'	=> 'h2',
				'options'	=> $tags,
				'required'	=> [
					['single_hospital_head_subtitle','=',true]
				]
			],
			[ // archive_specialist_title_tag
				'id'		=> 'archive_specialist_title_tag',
				'type'		=> 'select',
				'title'		=> __( 'Specialist archive item title', 'drplus' ),
				'default'	=> 'h2',
				'options'	=> $tags,
				'required'	=> [
					['archive_specialist_show_title','=',true]
				]
			],
		),
	)
);
Redux::set_section( // Schema
	$opt_name,
	array(
		'title'			=> esc_html__( 'Schema', 'drplus' ),
		'id'			=> 'seo-schema-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // seo-enable-specialist-schema
				'id'		=> 'seo-enable-specialist-schema',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Enable Specialist schema', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enable', 'drplus' ) ),
				'on'		=> esc_html__( 'Enable', 'drplus' ),
				'off'		=> esc_html__( 'Disable', 'drplus' ),
				'default'	=> true,
			],
			[ // seo-enable-hospital-schema
				'id'		=> 'seo-enable-hospital-schema',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Enable hospital schema', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enable', 'drplus' ) ),
				'on'		=> esc_html__( 'Enable', 'drplus' ),
				'off'		=> esc_html__( 'Disable', 'drplus' ),
				'default'	=> true,
			],
		),
	)
);