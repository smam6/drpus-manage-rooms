<?php

use DrPlus\Utils as DrPlusUtils;
use DrPlus\Utils\Options;
use DrPlus\Utils\UI;
use MJ\Whitebox\Utils;

if( !function_exists( 'drplus_color_mode_html_attributes' ) ) {
	function drplus_color_mode_html_attributes( $output ) {
		if( is_admin() ) return $output;
	
		$config = UI::get_color_mode_settings();
		$attributes = [
			'data-theme-mode'			=> $config['mode'],
			'data-theme-auto'			=> $config['auto'],
			'data-theme-user-switch'	=> $config['allow_switch'] ? '1' : '0',
			'data-theme-storage'		=> $config['storage_key'],
			'class'						=> [ "drplus-theme-mode-{$config['mode']}" ],
		];
	
		return $output . ' ' . Utils::get_html_attributes( $attributes );
	}
}
add_filter( 'language_attributes', 'drplus_color_mode_html_attributes' );

if( !function_exists( 'drplus_body_class' ) ) {
	function drplus_body_class( $classes ) {
		if( is_admin() ) return $classes;
	
		$config = UI::get_color_mode_settings();
		$classes[] = $config['initial'] === 'dark' ? 'is-dark' : 'is-light';
		$classes[] = "drplus-theme-initial-{$config['initial']}";

		$options = Options::get_options( [
			'button-bg-style'	=> 'simple'
		] );
		if( $options['button-bg-style'] == 'gradient' ) {
			$classes[] = "drplus-button-gradient";
		}
	
		return $classes;
	}
}
add_filter( 'body_class', 'drplus_body_class' );

if( !function_exists( 'drplus_color_mode_script' ) ) {
	function drplus_color_mode_script() {
		if( is_admin() ) return;
	
		$config = UI::get_color_mode_settings();
		?>
		<script id="drplus-color-mode" data-key="<?php echo esc_attr( $config['storage_key'] ) ?>">
		(function() {
			var cfg = <?php echo wp_json_encode( $config ); ?>;
			var doc = document.documentElement;
			var bodyClassList = null;
			var prefersDark = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;
	
			function setBodyClassList() {
				if( bodyClassList || !document.body ) return;
				bodyClassList = document.body.classList;
			}
	
			function applyMode(mode, persist) {
				var isDark = mode === 'dark';
				var add = isDark ? 'is-dark' : 'is-light';
				var remove = isDark ? 'is-light' : 'is-dark';
				doc.classList.remove(remove);
				doc.classList.add(add);
				doc.setAttribute('data-theme', mode);
	
				setBodyClassList();
				if( bodyClassList ) {
					bodyClassList.remove(remove);
					bodyClassList.add(add);
				}
	
				if( persist && cfg.allow_switch ) {
					try {
						localStorage.setItem(cfg.storage_key, mode);
					} catch(e) {}
				}
			}
	
			function getStoredChoice() {
				if( !cfg.allow_switch ) return null;
				try {
					var stored = localStorage.getItem(cfg.storage_key);
					if( stored === 'light' || stored === 'dark' ) {
						return stored;
					}
				} catch(e) {}
				return null;
			}
	
			function resolveMode() {
				if( cfg.mode === 'light' || cfg.mode === 'dark' ) return cfg.mode;
	
				var stored = getStoredChoice();
				if( stored ) return stored;
	
				if( cfg.auto === 'system' && prefersDark ) {
					return prefersDark.matches ? 'dark' : 'light';
				}
				if( cfg.auto === 'prefer_dark' ) return 'dark';
				if( cfg.auto === 'prefer_light' ) return 'light';
				return 'light';
			}
	
			var mode = resolveMode();
			applyMode(mode, false);

			if( cfg.mode === 'both' && cfg.auto === 'system' && prefersDark ) {
				var listener = function(event) {
					if( getStoredChoice() ) return;
					applyMode(event.matches ? 'dark' : 'light', false);
				};
				if( prefersDark.addEventListener ) {
					prefersDark.addEventListener('change', listener);
				} else if( prefersDark.addListener ) {
					prefersDark.addListener(listener);
				}
			}
	
			window.drplusTheme = {
				set: function(mode) {
					if( mode !== 'light' && mode !== 'dark' ) return;
					applyMode(mode, true);
				},
				get: function() {
					return doc.classList.contains('is-dark') ? 'dark' : 'light';
				},
				config: cfg
			};
	
			if( document.readyState === 'loading' ) {
				document.addEventListener('DOMContentLoaded', function() {
					<?php
					if( !empty( $_GET['theme'] ) ) {
						$theme = DrPlusUtils::ensure_values_in_array( DrPlusUtils::convert_chars( $_GET['theme'] ), ['light', 'dark'], 'light' );
						?>
						window.drplusTheme.set("<?php echo $theme ?>");
					<?php } else { ?>
						applyMode(doc.classList.contains('is-dark') ? 'dark' : 'light', false);
					<?php } ?>
				});
			}
		})();
		</script>
		<?php
	}
}
add_action( 'wp_head', 'drplus_color_mode_script', 0 );
add_action( 'drplus/onboard/head', 'drplus_color_mode_script', 0 );