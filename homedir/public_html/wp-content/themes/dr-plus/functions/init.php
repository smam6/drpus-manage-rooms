<?php

use DrPlus\Logger;
use DrPlus\Utils;
use DrPlus\Utils\Options;

if( !function_exists( "drplus_setup" ) ) {
	function drplus_setup() {
		$supports = ['title-tag', 'post-thumbnails', 'automatic-feed-links', 'menus', 'widgets', 'woocommerce'];
		foreach( $supports as $support ) {
			add_theme_support( $support );
		}
	}
}
add_action( 'after_setup_theme', 'drplus_setup' );

if( !function_exists( "drplus_init" ) ) {
	function drplus_init() {
		load_theme_textdomain( 'drplus',  DRPLUS_DIR . 'languages' );
		load_textdomain( 'mj-whitebox', DRPLUS_DIR . 'inc/Libs/vendor/mjkhajeh/whitebox/languages/mj-whitebox-' . get_locale() . '.mo' );

		include( DRPLUS_DIR . "inc/Libs/vendor/autoload.php" );

		include( DRPLUS_DIR . "inc/Utils.php" );
		include( DRPLUS_DIR . "inc/Utils/utils-options.php" );

		// Nav menus
		register_nav_menus( [
			'main-menu'		=> esc_html__( 'Header (Main menu)', 'drplus' ),
			'mobile-menu'	=> esc_html__( 'Mobile menu (Main menu)', 'drplus' ),
			'footer-menu'	=> esc_html__( 'Footer', 'drplus' ),
			'account-menu'	=> esc_html__( 'Account menu (Logged in users)', 'drplus' ),
		] );

		include_once( DRPLUS_DIR . "inc/TGM/tgm.php" );

		include( DRPLUS_DIR . "inc/Includes.php" );

		Logger::init();
		register_shutdown_function( function() {
			$error = error_get_last();
			if( $error !== NULL ) {
				if( strpos( $error["file"], DRPLUS_DIR ) === false ) return;
				
				Logger::critical( 'Fatal error' , $error );
			}
		} );
	}
}
add_action( 'init', 'drplus_init', 0 );

if( !function_exists( "drplus_init_tables" ) ) {
	function drplus_init_tables() {
		include_once( DRPLUS_DIR . "inc/Libs/vendor/autoload.php" );
		foreach( glob( DRPLUS_DIR . "inc/Models/*.php" ) as $file ) {
			include_once( $file );
			$class = "DrPlus\Model\\" . pathinfo( $file, PATHINFO_FILENAME );
			if( class_exists( $class ) ) {
				new $class;
			}
		}
	}
}
add_action( 'after_switch_theme', 'drplus_init_tables' );

if( !function_exists( "drplus_admin_enqueue" ) ) {
	function drplus_admin_enqueue() {
		$options = Options::get_options( [
			'm-icons'	=> false,
		] );

		$screen = get_current_screen();
		if( $screen->base === 'toplevel_page_drplus' ) {
			if( is_rtl() ) {
				wp_enqueue_style( 'drplus-options', DRPLUS_URI . "assets/css/backend/options.rtl.min.css", [], DRPLUS_VERSION );
			}
		}
		wp_enqueue_style( "drplus-icon", DRPLUS_URI . "assets/css/iconly.min.css", [], DRPLUS_VERSION );
		if( $options['m-icons'] ) {
			wp_enqueue_style( 'drplus-m-icons', DRPLUS_URI . "assets/css/drplus-m.min.css", [], DRPLUS_VERSION );
		}

		if( DRPLUS_DEV ) {
			wp_enqueue_script( 'drplus-utils', DRPLUS_URI . "assets/js/utils.js", ['jquery'], DRPLUS_VERSION, true );
			wp_enqueue_script( 'drplus', DRPLUS_URI . "assets/js/drplus.js", ['jquery'], DRPLUS_VERSION, true );
		} else {
			wp_enqueue_script( 'drplus-utils', DRPLUS_URI . "assets/js/utils.min.js", ['jquery'], DRPLUS_VERSION, true );
			wp_enqueue_script( 'drplus', DRPLUS_URI . "assets/js/drplus.min.js", ['jquery'], DRPLUS_VERSION, true );
		}

		Utils::drplus_vars_localize();

		if( DRPLUS_DEV ) {
			wp_enqueue_script( 'drplus-backend', DRPLUS_URI . "assets/js/backend/backend.js", ['jquery'], DRPLUS_VERSION, true );
		} else {
			wp_enqueue_script( 'drplus-backend', DRPLUS_URI . "assets/js/backend/backend.min.js", ['jquery'], DRPLUS_VERSION, true );
		}
		
		include( DRPLUS_DIR . "inc/AdminScripts.php" );
	}
}
add_action( 'admin_enqueue_scripts', "drplus_admin_enqueue", 9 );

if( !function_exists( "drplus_enqueue" ) ) {
	function drplus_enqueue() {
		include( DRPLUS_DIR . "inc/Scripts.php" );
	}
}
add_action( 'wp_enqueue_scripts', "drplus_enqueue" );

if( !function_exists( "drplus_load_taxonomies" ) ) {
	function drplus_load_taxonomies() {
		$taxonomies = [
			'Product'	=> [
				'product-service',
				'product-badge',
			],
			'Hospital'	=> [
				'hospital_category',
				'hospital-service',
			],
			'Specialist'	=> [
				'insurance',
				'identity_types',
				'location'
			]
		];
		foreach( $taxonomies as $dir => $files ) {
			if( is_array( $files ) ) {
				foreach( $files as $filename ) {
					if( file_exists( DRPLUS_DIR . "inc/Taxonomies/{$dir}/taxonomy-{$filename}.php" ) ) {
						include( DRPLUS_DIR . "inc/Taxonomies/{$dir}/taxonomy-{$filename}.php" );
					}
				}
			} else {
				$filename = $files;
				if( file_exists( DRPLUS_DIR . "inc/Taxonomies/taxonomy-{$filename}.php" ) ) {
					include( DRPLUS_DIR . "inc/Taxonomies/taxonomy-{$filename}.php" );
				}
			}
		}
	}
}
add_action( 'init', 'drplus_load_taxonomies' );

// MARK: Shortcodes
if( !function_exists( "drplus_include_shortcodes" ) ) {
	function drplus_include_shortcodes() {
		$shortcodes = [
			'post-views',
			'booking'
		];
		foreach( $shortcodes as $index => $shortcode ) {
			if( !Utils::should_include_module( $index, $shortcode ) ) continue;
			$shortcode = Utils::get_module_name( $index, $shortcode );
			if( file_exists( DRPLUS_DIR . "inc/Shortcodes/shortcode-{$shortcode}.php" ) ) {
				include( DRPLUS_DIR . "inc/Shortcodes/shortcode-{$shortcode}.php" );
			}
		}
	}
}
add_action( 'wp_loaded', 'drplus_include_shortcodes' );

if( !function_exists( 'drplus_custom_head_code' ) ) {
	function drplus_custom_head_code() {
		if( empty( $GLOBALS['drplus'] ) ) return;
		
		$options = $GLOBALS['drplus'];
		echo isset( $options['header_custom_code'] ) ? $options['header_custom_code'] : '';
	}
}
add_action( 'wp_head', 'drplus_custom_head_code' );

if( !function_exists( 'drplus_custom_footer_code' ) ) {
	function drplus_custom_footer_code() {
		if( empty( $GLOBALS['drplus'] ) ) return;

		$options = $GLOBALS['drplus'];
		echo isset( $options['footer_custom_code'] ) ? $options['footer_custom_code'] : '';
	}
}
add_action( 'wp_footer', 'drplus_custom_footer_code' );

// Add custom select html
if( !function_exists( "drplus_custom_select_html" ) ) {
	function drplus_custom_select_html() {
		?>
		<div class="drplus-custom-select-popover">
			<div class="drplus-custom-select-popover-search-wrap">
				<input type="search" class="drplus-custom-select-popover-search" placeholder="<?php esc_attr_e( "Search...", 'drplus' ) ?>">
			</div>

			<div class="drplus-custom-select-popover-list"></div>
		</div>
		<?php
	}
}
add_action( 'wp_footer', 'drplus_custom_select_html', 9999 );

// Add search input html
if( !function_exists( "drplus_search_input_html" ) ) {
	function drplus_search_input_html() {
		?>
		<div class="drplus-search-input-popover loading">
			<?php get_template_part( "templates/components/template-components-loading", null, [
				'text'		=> esc_html__( "Searching...", 'drplus' ),
				'classes'	=> ['drplus-search-input-popover-loading'],
			] ) ?>

			<div class="drplus-search-input-popover-list"></div>

			<div class="drplus-search-input-popover-empty">
				<i class="drplus-icon-search-2"></i>
				<span class="drplus-search-input-popover-empty-text"><?php esc_html_e( 'No results. Please try again with a different text.', 'drplus' ) ?></span>
			</div>
		</div>
		<?php
	}
}
add_action( 'wp_footer', 'drplus_search_input_html', 9999 );

// Add overlay
if( !function_exists( "drplus_overlay" ) ) {
	function drplus_overlay() {
		?>
		<div id="drplus-overlay" class="drplus-overlay"></div>
		<?php
	}
}
add_action( 'wp_footer', 'drplus_overlay' );

if( !function_exists( "drplus_redux_prevent_icons_request" ) ) {
	function drplus_redux_prevent_icons_request( $response, $parsed_args, $url ) {
		if( !is_admin() ) {
			$urls = [DRPLUS_URI . "assets/css/iconly.min.css", DRPLUS_URI . "assets/css/drplus-m.min.css"];
			return in_array( $url, $urls );
		}
		return $response;
	}
}
add_filter( 'pre_http_request', 'drplus_redux_prevent_icons_request', 10, 3 );

if( !function_exists( 'drplus_flush_rewrite' ) ) {
	function drplus_flush_rewrite() {
		flush_rewrite_rules();
	}
}
add_action( 'after_switch_theme', 'drplus_flush_rewrite' );

if( !function_exists( "drplus_cron_schedules" ) ) {
	function drplus_cron_schedules( $schedules ) {
		$schedules['drplus_five_minutes'] = [
			'interval'	=> 5 * 60,
			'display'	=> esc_html__( 'Every Five Minutes', 'drplus' )
		];
		$schedules['drplus_one_minutes'] = [
			'interval'	=> 60,
			'display'	=> esc_html__( 'Every Minutes', 'drplus' )
		];
		return $schedules;
	}
}
add_filter( 'cron_schedules', 'drplus_cron_schedules' );

/**
 * This function used to translate some strings that used dynamically or other ways
 *
 * @return void
 */
function translatables() {
	__( "Blood type", 'drplus' );
	_x( 'Height', 'User', 'drplus' );
	__( 'Weight', 'drplus' );
	_x( 'Height (cm)', 'User', 'drplus' );
	__( 'Weight (kg)', 'drplus' );
	_x( 'cm', 'Centimeters', 'drplus' );
	_x( 'kg', 'Kilograms', 'drplus' );
}

add_filter( 'wp_theme_json_get_style_nodes', function($nodes) {
	if (!is_array($nodes)) {
		return $nodes;
	}

	$nodes = array_filter($nodes, function ($node) {
		if (
			!empty($node['selector']) &&
			$node['selector'] == 'a:where(:not(.wp-element-button))'
		) {
			return false;
		}

		return true;
	});

	return $nodes;
});

// Include Medicine icons
if( !function_exists( "drplus_medicine_icons" ) ) {
	function drplus_medicine_icons( $packs ) {
		$options = Options::get_options( [
			'm-icons'	=> false,
		] );
		if( $options['m-icons'] ) {
			$packs['medicine-icons'] = [
				'label'			=> "Medicine icons",
				'label_fa'		=> 'آیکون‌های پزشکی',
				'label_icon'	=> "drplus-m-icon-ambulance-2",
				'mode'			=> "font-icon",
				'prefix'		=> "drplus-m-icon-",
				'icons'			=> array_map( fn( $filename ) => pathinfo( $filename, PATHINFO_FILENAME ), glob( DRPLUS_DIR . "assets/drplus-m-icons/*.svg" ) ),
			];
		}
		return $packs;
	}
}
add_filter( 'drplus/icon-picker/packs', 'drplus_medicine_icons' );