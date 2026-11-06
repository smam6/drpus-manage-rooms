<?php
/**
 * CleanLite Theme functions and definitions
 */

function cleanlite_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'script',
			'style',
		)
	);

	add_theme_support(
		'custom-header',
		array(
			'default-text-color' => '1e73be',
			'width'              => 1200,
			'height'             => 250,
			'flex-height'        => true,
			'flex-width'         => true,
			'header-text'        => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'cleanlite' ),
		)
	);

	add_editor_style( 'assets/css/main.css' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );

	add_theme_support(
		'custom-logo',
		array(
			'width'       => 250,
			'height'      => 60,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);

	add_theme_support(
		'custom-background',
		array(
			'default-color' => 'ffffff',
		)
	);

	add_theme_support( 'customize-selective-refresh-widgets' );
}
add_action( 'after_setup_theme', 'cleanlite_setup' );

/**
 * Enqueue styles and scripts.
 */
function cleanlite_enqueue_assets() {
	$theme_version = wp_get_theme()->get( 'Version' );

	wp_enqueue_style( 'cleanlite-style', get_stylesheet_uri(), array(), $theme_version );
	wp_enqueue_style( 'cleanlite-main', get_template_directory_uri() . '/assets/css/main.css', array(), $theme_version );
	wp_enqueue_script( 'cleanlite-navigation', get_template_directory_uri() . '/assets/js/navigation.js', array(), $theme_version, true );

	// Inline script for Sticky Header Scroll detection
	if ( get_theme_mod( 'cleanlite_sticky_header', false ) ) {
		$js = '
		document.addEventListener("DOMContentLoaded", function() {
			var header = document.querySelector(".site-header");
			if (!header) return;
			var scrollFn = function() {
				if (window.scrollY > 10) {
					header.classList.add("has-scrolled");
				} else {
					header.classList.remove("has-scrolled");
				}
			};
			window.addEventListener("scroll", scrollFn);
			scrollFn(); // Init
		});
		';
		wp_add_inline_script( 'cleanlite-navigation', $js );
	}
}
add_action( 'wp_enqueue_scripts', 'cleanlite_enqueue_assets' );

/**
 * Enqueue comment-reply.
 */
function cleanlite_enqueue_comment_reply() {
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'cleanlite_enqueue_comment_reply' );

/**
 * Widget areas.
 */
function cleanlite_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Sidebar', 'cleanlite' ),
			'id'            => 'sidebar-1',
			'description'   => __( 'Main sidebar that appears on the right.', 'cleanlite' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'cleanlite_widgets_init' );

/**
 * Customizer additions.
 */
function cleanlite_customize_register( $wp_customize ) {

	// HEADER OPTIONS
	$wp_customize->add_section(
		'cleanlite_header_options',
		array(
			'title'    => __( 'Header Options', 'cleanlite' ),
			'priority' => 25,
		)
	);

	// 1. Header Background Color
	$wp_customize->add_setting( 'cleanlite_header_bg_color', array(
		'default' => '#ffffff', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'refresh',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'cleanlite_header_bg_color', array(
		'label' => __( 'Header Background Color', 'cleanlite' ), 'section' => 'cleanlite_header_options',
	) ) );

	// 2. Header Layout Alignment
	$wp_customize->add_setting( 'cleanlite_header_alignment', array(
		'default' => 'space-between', 'sanitize_callback' => 'cleanlite_sanitize_select', 'transport' => 'refresh',
	) );
	$wp_customize->add_control( 'cleanlite_header_alignment', array(
		'label' => __( 'Header Layout Alignment', 'cleanlite' ),
		'description' => __( 'Choose "Space Between" to push items to edges, or "Left"/"Center" to align them.', 'cleanlite' ),
		'section' => 'cleanlite_header_options',
		'type' => 'select',
		'choices' => array(
			'space-between' => __( 'Space Between (Default)', 'cleanlite' ),
			'flex-start'    => __( 'Left', 'cleanlite' ),
			'center'        => __( 'Center', 'cleanlite' ),
			'flex-end'      => __( 'Right', 'cleanlite' ),
		),
	) );

	// 3. Spacing Between Logo & Menu
	$wp_customize->add_setting( 'cleanlite_header_gap', array(
		'default' => 30, 'sanitize_callback' => 'absint', 'transport' => 'refresh',
	) );
	$wp_customize->add_control( 'cleanlite_header_gap', array(
		'label' => __( 'Spacing Between Logo & Menu (px)', 'cleanlite' ),
		'section' => 'cleanlite_header_options',
		'type' => 'range', 'input_attrs' => array( 'min' => 0, 'max' => 200, 'step' => 5 ),
	) );

	// 4. Sticky Header Toggle
	$wp_customize->add_setting( 'cleanlite_sticky_header', array(
		'default' => false, 'sanitize_callback' => 'cleanlite_sanitize_checkbox', 'transport' => 'refresh',
	) );
	$wp_customize->add_control( 'cleanlite_sticky_header', array(
		'label' => __( 'Enable Sticky Header', 'cleanlite' ), 'section' => 'cleanlite_header_options', 'type' => 'checkbox',
	) );

	// 5. Sticky Header Background
	$wp_customize->add_setting( 'cleanlite_sticky_bg_color', array(
		'default' => '#ffffff', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'refresh',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'cleanlite_sticky_bg_color', array(
		'label' => __( 'Sticky Header Background', 'cleanlite' ), 'section' => 'cleanlite_header_options',
	) ) );

	// 6. Logo Width
	$wp_customize->add_setting( 'cleanlite_logo_width', array(
		'default' => 150, 'sanitize_callback' => 'absint', 'transport' => 'refresh',
	) );
	$wp_customize->add_control( 'cleanlite_logo_width', array(
		'label' => __( 'Logo Max Width (px)', 'cleanlite' ),
		'section' => 'title_tagline',
		'type' => 'range', 'input_attrs' => array( 'min' => 50, 'max' => 400, 'step' => 5 ),
	) );

	// 7. Menu Item Spacing
	$wp_customize->add_setting( 'cleanlite_menu_spacing', array(
		'default' => 20, 'sanitize_callback' => 'absint', 'transport' => 'refresh',
	) );
	$wp_customize->add_control( 'cleanlite_menu_spacing', array(
		'label' => __( 'Menu Item Spacing (px)', 'cleanlite' ), 'section' => 'cleanlite_header_options',
		'type' => 'range', 'input_attrs' => array( 'min' => 0, 'max' => 100, 'step' => 2 ),
	) );

	// Menu Colors
	$wp_customize->add_setting( 'cleanlite_menu_link_color', array(
		'default' => '#1e73be', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'refresh',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'cleanlite_menu_link_color', array(
		'label' => __( 'Menu Link Color', 'cleanlite' ), 'section' => 'cleanlite_header_options',
	) ) );

	$wp_customize->add_setting( 'cleanlite_menu_hover_color', array(
		'default' => '#135a96', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'refresh',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'cleanlite_menu_hover_color', array(
		'label' => __( 'Menu Link Hover Color', 'cleanlite' ), 'section' => 'cleanlite_header_options',
	) ) );

	// Tagline
	$wp_customize->add_setting( 'cleanlite_show_tagline', array(
		'default' => true, 'sanitize_callback' => 'cleanlite_sanitize_checkbox', 'transport' => 'refresh',
	) );
	$wp_customize->add_control( 'cleanlite_show_tagline', array(
		'label' => __( 'Show Site Tagline', 'cleanlite' ), 'section' => 'cleanlite_header_options', 'type' => 'checkbox',
	) );
}
add_action( 'customize_register', 'cleanlite_customize_register' );

/**
 * Sanitize checkbox.
 */
function cleanlite_sanitize_checkbox( $checked ) {
	return ( ( isset( $checked ) && true == $checked ) ? true : false );
}

/**
 * Sanitize select.
 */
function cleanlite_sanitize_select( $input, $setting ) {
	$input = sanitize_key( $input );
	$choices = $setting->manager->get_control( $setting->id )->choices;
	return ( array_key_exists( $input, $choices ) ? $input : $setting->default );
}

/**
 * Output header styles.
 */
function cleanlite_header_style() {
	$header_text_color = get_header_textcolor();
	$header_bg_color   = get_theme_mod( 'cleanlite_header_bg_color', '#ffffff' );
	$sticky_bg_color   = get_theme_mod( 'cleanlite_sticky_bg_color', '#ffffff' );
	$logo_width        = get_theme_mod( 'cleanlite_logo_width', 150 );
	$menu_spacing      = get_theme_mod( 'cleanlite_menu_spacing', 20 );
	$header_align      = get_theme_mod( 'cleanlite_header_alignment', 'space-between' );
	$header_gap        = get_theme_mod( 'cleanlite_header_gap', 30 );
	$menu_link_color   = get_theme_mod( 'cleanlite_menu_link_color', '#1e73be' );
	$menu_hover_color  = get_theme_mod( 'cleanlite_menu_hover_color', '#135a96' );
	$show_tagline      = get_theme_mod( 'cleanlite_show_tagline', true );

	$custom_css = '';
	$custom_css .= ".site-header { background-color: " . esc_attr( $header_bg_color ) . "; }\n";
	
	// FIX: Use 'body.has-sticky-header .site-header' instead of '.is-sticky'
	$custom_css .= "body.has-sticky-header .site-header.has-scrolled, body.has-sticky-header .site-header { background-color: " . esc_attr( $sticky_bg_color ) . "; }\n";
	
	$custom_css .= ".custom-logo-link img { max-width: " . absint( $logo_width ) . "px; }\n";
	
	// Apply layout settings only to desktop
	$custom_css .= "@media (min-width: 769px) { \n";
	$custom_css .= "  .site-header .wrap { justify-content: " . esc_attr( $header_align ) . "; gap: " . absint( $header_gap ) . "px; }\n";
	$custom_css .= "  .main-navigation .menu { gap: " . absint( $menu_spacing ) . "px; }\n";
	$custom_css .= "}\n";

	if ( 'blank' === $header_text_color ) {
		$custom_css .= ".site-title, .site-description { position: absolute; clip: rect(1px, 1px, 1px, 1px); }\n";
	} else {
		$custom_css .= ".site-title a, .site-title, .site-description { color: #" . esc_attr( $header_text_color ) . "; }\n";
	}

	if ( ! $show_tagline ) {
		$custom_css .= ".site-description { display: none; }\n";
	}

	$custom_css .= ".main-navigation .menu a, .main-navigation a, #primary-menu a, .menu a { color: " . esc_attr( $menu_link_color ) . "; }\n";
	$custom_css .= ".main-navigation .menu a:hover, .main-navigation .menu a:focus, #primary-menu a:hover, #primary-menu a:focus { color: " . esc_attr( $menu_hover_color ) . "; }\n";

	wp_add_inline_style( 'cleanlite-main', $custom_css );
}
add_action( 'wp_enqueue_scripts', 'cleanlite_header_style' );

/**
 * Add body classes.
 */
function cleanlite_body_classes( $classes ) {
	if ( get_theme_mod( 'cleanlite_sticky_header', false ) ) {
		$classes[] = 'has-sticky-header';
	}
	return $classes;
}
add_filter( 'body_class', 'cleanlite_body_classes' );

/**
 * Register Block Patterns.
 */
function cleanlite_register_patterns() {
	register_block_pattern(
		'cleanlite/hero-section',
		array(
			'title'       => __( 'Hero Section', 'cleanlite' ),
			'description' => _x( 'A hero section with heading and button.', 'Block pattern description', 'cleanlite' ),
			'categories'  => array( 'featured' ),
			'content'     => '<div class="wp-block-group alignfull has-background-color has-secondary-background-color has-text-color has-background" style="padding-top:120px;padding-bottom:120px"><h1 class="has-text-align-center">' . esc_html__( 'Welcome to CleanLite', 'cleanlite' ) . '</h1><p class="has-text-align-center has-large-font-size">' . esc_html__( 'A minimal, accessible starter theme for your next big idea.', 'cleanlite' ) . '</p><div class="wp-block-buttons"><div class="wp-block-button"><a class="wp-block-button__link has-white-color has-primary-background-color has-text-color has-background wp-element-button">' . esc_html__( 'Get Started', 'cleanlite' ) . '</a></div></div></div>',
		)
	);
}
add_action( 'init', 'cleanlite_register_patterns' );

/**
 * Register Block Styles.
 */
function cleanlite_register_block_styles() {
	register_block_style(
		'core/button',
		array(
			'name'  => 'cleanlite-outline',
			'label' => __( 'Outline', 'cleanlite' ),
		)
	);
}
add_action( 'init', 'cleanlite_register_block_styles' );