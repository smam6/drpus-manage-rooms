<?php
namespace DrPlus\Utils;

use DrPlus\Utils;

class Elementor extends Utils {
	/**
	 * Generate link HTML attributes from Elementor link settings
	 *
	 * @param array $link Elementor link settings
	 * @return array HTML attributes of the link
	 */
	public static function get_link_attributes( $link = [] ) {
		$url_attrs = [];
		$rel_string = '';

		if( is_string( $link ) ) {
			$link = ['url' => $link];
		}

		if ( ! empty( $link['url'] ) ) {
			$url_attrs['href'] = esc_url( $link['url'] );
		}

		if ( ! empty( $link['is_external'] ) ) {
			$url_attrs['target'] = '_blank';
			$rel_string .= 'noopener ';
		}

		if ( ! empty( $link['nofollow'] ) ) {
			$rel_string .= 'nofollow ';
		}

		if ( ! empty( $rel_string ) ) {
			$url_attrs['rel'] = $rel_string;
		}

		if( class_exists( 'Elementor\Utils' ) ) {
			$url_combined_attrs = array_merge(
				$url_attrs,
				\Elementor\Utils::parse_custom_attributes( $link['custom_attributes'] ?? '' ),
			);
		} else {
			$url_combined_attrs = $url_attrs;
		}

		return $url_combined_attrs;
	}

	public static function get_slider_device_slides( $args, $device ) {
		return $args["{$device}_slides_type"] === 'count' ? $args["{$device}_slides"] : 'auto';
	}

	public static function get_display_attributes( $settings, $slider_mode = false ) {
		$wrap_classes = [];
		$classes = [];
		$args = [];
		$styles = [];
		$devices = ['desktop', 'tablet', 'mobile'];

		if( !empty( $settings ) ) {
			foreach( $devices as $device ) {
				if( $slider_mode ) {
					$settings["{$device}_slider"] = true;
				}

				$args[$device]["slider"]['enabled'] = parent::to_bool( $settings["{$device}_slider"] ?? false );
				if( $args[$device]["slider"]['enabled'] ) {
					$wrap_classes[] = "{$device}-slider-wrap";
					$classes[] = "{$device}-slider";
					$args[$device]["slider"]["slidesPerView"] = $settings["{$device}_slides_type"] == 'count' ? $settings["{$device}_slides"] : 'auto';
					$args[$device]["slider"]["spaceBetween"] = $settings["{$device}_slides_space"];

					if( $settings["{$device}_slides_type"] == 'auto' ) {
						$classes[] = "{$device}-slider-auto";
					}
					$styles["--{$device}-space"] = "{$settings["{$device}_slides_space"]}px";
				} else {
					$wrap_classes[] = "{$device}-columns-wrap";
					$classes[] = "{$device}-columns";
					$classes[] = "{$device}-columns-{$settings["{$device}_cols"]}";
					$display = 'grid';
					if( !empty( $settings["{$device}_display"] ) ) {
						$display = $settings["{$device}_display"];
					}
					$classes[] = "{$device}-display-{$display}";
					if( $display === 'grid' ) {
						$args[$device]["columns"] = $settings["{$device}_cols"];
						$styles["--{$device}-cols"] = $settings["{$device}_cols"];
					}
					if( isset( $settings["{$device}_gap"] ) ) {
						$styles["--{$device}-gap"] = $settings["{$device}_gap"] . "px";
					}
				}
			}
		}

		return [
			'wrap_classes'	=> $wrap_classes,
			'classes'		=> $classes,
			'args'			=> $args,
			'style'			=> $styles
		];
	}

	public static function get_button_args( array $settings, string $prefix = 'button_' ) {
		$icon = '';
		if( isset( $settings['button_icon'] ) && is_array( $settings['button_icon'] ) ) {
			$icon = $settings['button_icon']['value'];
			if( is_array( $icon ) && !empty( $icon['url'] ) ) {
				$icon = $icon['url'];
			}
		}

		$args = [];
		if( isset( $settings['button_type'] ) ) {
			$args["{$prefix}type"] = $settings['button_type'];
		}
		if( isset( $settings['button_transparent'] ) ) {
			$args["{$prefix}transparent"] = Utils::to_bool( $settings['button_transparent'] );
		}
		if( isset( $settings['button_small'] ) ) {
			$args["{$prefix}small"] = Utils::to_bool( $settings['button_small'] );
		}
		if( $icon ) {
			$args["{$prefix}icon"] = $icon;
		}
		if( isset( $settings['button_text'] ) ) {
			$args["{$prefix}text"] = $settings['button_text'];
		}
		if( isset( $settings['button_link'] ) ) {
			$args["{$prefix}link"] = $settings['button_link'];
		}
		if( isset( $settings['button_new_tab'] ) ) {
			$args["{$prefix}new_tab"] = Utils::to_bool( $settings['button_new_tab'] );
			if( $args["{$prefix}new_tab"] ) {
				$args["{$prefix}link"]['is_external'] = 'on';
			}
		}
		if( isset( $settings['button_icon_align'] ) ) {
			$args["{$prefix}icon_align"] = $settings['button_icon_align'];
		}
		if( isset( $settings['button_style'] ) ) {
			$args["{$prefix}style"] = $settings['button_style'];
		}
		if( isset( $settings['button_fullwidth'] ) ) {
			$args["{$prefix}fullwidth"] = $settings['button_fullwidth'];
		}
		if( isset( $settings['button_align'] ) ) {
			$args["{$prefix}align"] = $settings['button_align'];
		}
		
		return $args;
	}

	public static function check_button_defaults( array $args, string $prefix = 'button_' ) {
		if( isset( $args["{$prefix}link"] ) && !is_array( $args["{$prefix}link"] ) ) {
			$args["{$prefix}link"] = [
				'url'				=> $args["{$prefix}link"],
				'is_external'		=> !empty( $args["{$prefix}new_tab"] ),
				'nofollow'			=> false,
				'custom_attributes'	=> '',
			];
		}

		$args = Utils::check_default( $args, [
			"{$prefix}transparent"	=> false,
			"{$prefix}type"			=> 'primary',
			"{$prefix}small"		=> false,
			"{$prefix}icon"			=> '',
			"{$prefix}text"			=> '',
			"{$prefix}title"		=> '',
			"{$prefix}link"			=> [],
			"{$prefix}new_tab"		=> false,
			"{$prefix}icon_align"	=> 'start',
			"{$prefix}style"		=> 'rounded',
			"{$prefix}align"		=> 'start',
			"{$prefix}fullwidth"	=> false,
			"{$prefix}classes"		=> [],
			"{$prefix}id"			=> '',
			"{$prefix}disabled"		=> false,
			"{$prefix}loading"		=> false,
			"{$prefix}button_type"	=> '',
			"{$prefix}popup"		=> '',
		], ["{$prefix}icon"] );
		return $args;
	}

	public static function get_wrapper_selector( $string ) {
		return strpos( $string, '{{WRAPPER}}' ) !== false ? $string : "{{WRAPPER}} {$string}";
	}

	public static function parse_text_editor( $content ) {
		$content = shortcode_unautop( $content );
		$content = do_shortcode( $content );
		$content = wptexturize( $content );

		if ( $GLOBALS['wp_embed'] instanceof \WP_Embed ) {
			$content = $GLOBALS['wp_embed']->autoembed( $content );
		}

		return $content;
	}

	public static function get_responsive_settings_data( $settings, $name ) {
		$data = [
			'desktop'	=> $settings[$name]
		];

		$data['tablet'] = !empty( $settings["{$name}_tablet"] ) ? $settings["{$name}_tablet"] : $data['desktop'];
		$data['mobile'] = !empty( $settings["{$name}_mobile"] ) ? $settings["{$name}_mobile"] : $data['tablet'];

		return $data;
	}

	public static function get_elementor_template_type() {
		if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			return false;
		}

		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return false;
		}

		if ( get_post_type($post_id) !== 'elementor_library' ) {
			return false;
		}

		return get_post_meta($post_id, '_elementor_template_type', true);
	}

	public static function is_allowed_context( $allowed ) {
		$template_type = self::get_elementor_template_type();

		if ( in_array( $template_type, $allowed ) ) {
			return true;
		}

		return false;
	}
}