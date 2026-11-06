<?php
namespace MJ\Whitebox\Utils;

use Elementor\Plugin;

use MJ\Whitebox\Utils;

class Elementor extends Utils {
	protected static $use_relatives_for_align = false;

	/**
	 * Ensure a CSS selector string includes the Elementor wrapper placeholder.
	 *
	 * If '{{WRAPPER}}' is not already in the string, it prepends it.
	 *
	 * @param string $string The CSS selector.
	 * @return string Selector including '{{WRAPPER}}'.
	 */
	public static function get_wrapper_selector( $string ) {
		return strpos( $string, '{{WRAPPER}}' ) !== false ? $string : "{{WRAPPER}} {$string}";
	}

	/**
	 * Get button types for Elementor controls.
	 *
	 * @param array $args Optional arguments to modify button types.
	 * @return array List of button types.
	 */
	public static function button_types( $args = [] ) {
		$types = [
			'primary'	=> esc_html_x( 'Primary', 'Button type', 'mj-whitebox' ),
			'secondary'	=> esc_html_x( 'Secondary', 'Button type', 'mj-whitebox' ),
			'gray'		=> esc_html_x( 'Gray', 'Button type', 'mj-whitebox' ),
			'white'		=> esc_html_x( 'White', 'Button type', 'mj-whitebox' ),
			'action'	=> esc_html_x( 'Action', 'Button type', 'mj-whitebox' ),
			'bordered'	=> esc_html_x( 'Bordered', 'Button type', 'mj-whitebox' ),
		];
		$types = apply_filters( 'mj\whitebox\elementor_controls\button\types', $types, $args );
		return $types;
	}

	/**
	 * Get button styles for Elementor controls.
	 *
	 * @param array $args Optional arguments to modify button styles.
	 * @return array List of button styles.
	 */
	public static function button_styles( $args = [] ) {
		$styles = [
			'normal'	=> esc_html_x( 'Normal', 'Button style', 'mj-whitebox' ),
			'rounded'	=> esc_html_x( 'Rounded', 'Button style', 'mj-whitebox' ),
			'circle'	=> esc_html_x( 'Circle', 'Button style', 'mj-whitebox' ),
		];
		$styles = apply_filters( 'mj\whitebox\elementor_controls\button\styles', $styles, $args );
		return $styles;
	}

	/**
	 * Get date types for Elementor controls.
	 *
	 * @param array $args Optional arguments to modify date types.
	 * @return array List of date types.
	 */
	public static function date_types( $args = [] ) {
		return apply_filters( 'mj\whitebox\utils\elementor\date_types', [
			'anytime'	=> esc_html__( 'All', 'mj-whitebox' ),
			'today'		=> esc_html__( 'Past Day', 'mj-whitebox' ),
			'week'		=> esc_html__( 'Past Week', 'mj-whitebox' ),
			'month'		=> esc_html__( 'Past Month', 'mj-whitebox' ),
			'quarter'	=> esc_html__( 'Past Quarter', 'mj-whitebox' ),
			'year'		=> esc_html__( 'Past Year', 'mj-whitebox' ),
			'exact'		=> esc_html__( 'Custom', 'mj-whitebox' ),
		], $args );
	}

	/**
	 * Get orderby options for queries.
	 *
	 * @param bool $wc Whether to include WooCommerce-specific options.
	 * @param array $excludes List of options to exclude.
	 * @param array $args Additional arguments.
	 * @return array List of orderby options.
	 */
	public static function orderby( $wc = false, $excludes = [], $args = [] ) {
		if( !$wc ) {
			$orderby = [
				'post_date'		=> esc_html__( 'Date', 'mj-whitebox' ),
				'post_title'	=> esc_html__( 'Title', 'mj-whitebox' ),
				'modified'		=> esc_html__( 'Last Modified', 'mj-whitebox' ),
				'comment_count'	=> esc_html__( 'Comment Count', 'mj-whitebox' ),
				'rand'			=> esc_html__( 'Random', 'mj-whitebox' ),
			];
		} else {
			$orderby = [
				'ID'			=> esc_html__( 'ID', 'mj-whitebox' ),
				'name'			=> esc_html__( 'Product name', 'mj-whitebox' ),
				'type'			=> esc_html__( 'Product type', 'mj-whitebox' ),
				'post_date'		=> esc_html__( 'Date', 'mj-whitebox' ),
				'modified'		=> esc_html__( 'Last Modified', 'mj-whitebox' ),
				'price'			=> esc_html__( 'Price', 'mj-whitebox' ),
				'popularity'	=> esc_html__( 'Popularity', 'mj-whitebox' ),
				'rating'		=> esc_html__( 'Rating', 'mj-whitebox' ),
				'sales'			=> esc_html__( 'Sales', 'mj-whitebox' ),
				'rand'			=> esc_html__( 'Random', 'mj-whitebox' ),
			];
		}
		if( !empty( $excludes ) ) {
			$orderby = parent::unset( $orderby, $excludes );
		}

		$orderby = apply_filters( 'mj\whitebox\utils\elementor\orderby', $orderby, $wc, $excludes, $args );

		return $orderby;
	}

	/**
	 * Generate link HTML attributes from Elementor link settings.
	 *
	 * @param array|string $link Elementor link settings.
	 * @return array HTML attributes of the link.
	 */
	public static function get_link_attributes( $link = [] ) {
		$url_attrs = [];
		$rel_string = '';

		if( !empty( $link ) && !is_array( $link ) ) {
			$link = [
				'url'	=> $link
			];
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

		if( class_exists( "\Elementor\Utils" ) ) {
			$url_combined_attrs = array_merge(
				$url_attrs,
				\Elementor\Utils::parse_custom_attributes( $link['custom_attributes'] ?? '' ),
			);
			return $url_combined_attrs;
		} else {
			$result = [];
			// Copied from \Elementor\Utils::parse_custom_attributes
			foreach ( $url_attrs as $attribute ) {
				$attr_key_value = explode( '|', $attribute );

				$attr_key = mb_strtolower( $attr_key_value[0] );

				// Remove any not allowed characters.
				preg_match( '/[-_a-z0-9]+/', $attr_key, $attr_key_matches );

				if ( empty( $attr_key_matches[0] ) ) {
					continue;
				}

				$attr_key = $attr_key_matches[0];

				// Avoid Javascript events and unescaped href.
				if ( 'href' === $attr_key || 'on' === substr( $attr_key, 0, 2 ) ) {
					continue;
				}

				if ( isset( $attr_key_value[1] ) ) {
					$attr_value = trim( $attr_key_value[1] );
				} else {
					$attr_value = '';
				}

				$result[ $attr_key ] = $attr_value;
			}
			return $result;
		}
	}

	/**
	 *	'desktop_slider'		=> false,
	 *	'desktop_slides_type'	=> 'auto', // auto | count
	 *	'desktop_slides'		=> $desktop_columns,
	 *	'desktop_slides_space'	=> 0,
	 *	'desktop_cols'			=> $desktop_columns,
	 *	'desktop_row_gap'		=> 16,
	 *	'desktop_column_gap'	=> 16,
	 *	
	 *	'tablet_slider'			=> false,
	 *	'tablet_slides_type'	=> 'auto',
	 *	'tablet_slides'			=> 4,
	 *	'tablet_slides_space'	=> 0,
	 *	'tablet_cols'			=> 2,
	 *	'tablet_row_gap'		=> 16,
	 *	'tablet_column_gap'		=> 16,
	 * 
	 *	'mobile_slider'			=> false,
	 *	'mobile_slides_type'	=> 'auto',
	 *	'mobile_slides'			=> 4,
	 *	'mobile_slides_space'	=> 0,
	 *	'mobile_cols'			=> 1,
	 *	'mobile_row_gap'		=> 16,
	 *	'mobile_column_gap'		=> 16,
	 */
	/**
	 * Get display attributes for slider and columns in themes and plugins.
	 *
	 * @param array $settings Display settings for different devices.
	 * @param bool $slider_mode Force activate slider for all devices.
	 * @param array $other_slider_attrs Additional slider attributes.
	 * @return array Display attributes including classes, args, and styles.
	 */
	public static function get_display_attributes( array $settings, $slider_mode = false, $other_slider_attrs = [] ) {
		$wrap_classes = [];
		$classes = [];
		$args = [];
		$styles = [];
		$devices = ['desktop', 'tablet', 'mobile'];

		if( !empty( $settings ) ) {
			if( !empty( $settings['autoplay'] ) && parent::to_bool( $settings['autoplay'] ) && isset( $settings['autoplay_time'] ) ) {
				$args['slider']['autoplay'] = [
					'delay'	=> floatval( $settings['autoplay_time'] )
				];
			}
			if( !empty( $settings['loop'] ) && parent::to_bool( $settings['loop'] ) ) {
				$args['slider']['loop'] = true;
			}
			foreach( $devices as $device ) {
				if( $slider_mode ) {
					$settings["{$device}_slider"] = true;
				}

				$args[$device]["slider"]['enabled'] = parent::to_bool( $settings["{$device}_slider"] );
				if( $args[$device]["slider"]['enabled'] ) {
					$settings["{$device}_slides_space"] = isset( $settings["{$device}_slides_space"] ) ? floatval( $settings["{$device}_slides_space"] ) : 0;

					$wrap_classes[] = "{$device}-slider-wrap";
					$classes[] = "{$device}-slider";
					$args[$device]["slider"]["slidesPerView"] = $settings["{$device}_slides_type"] == 'count' ? $settings["{$device}_slides"] : 'auto';
					$args[$device]["slider"]["spaceBetween"] = $settings["{$device}_slides_space"];

					if( $settings["{$device}_slides_type"] == 'auto' ) {
						$classes[] = "{$device}-slider-auto";
					}
					$styles["--{$device}-space"] = "{$settings["{$device}_slides_space"]}px";

					if( !empty( $settings["{$device}_rows"] ) ) {
						$args[$device]["slider"]["grid"] = [
							'rows'	=> absint( $settings["{$device}_rows"] ),
							'fill'	=> 'row',
						];
						$styles["--{$device}-rows"] = absint( $settings["{$device}_rows"] );
					}

					$args[$device]["slider"] = array_merge( $args[$device]["slider"], $other_slider_attrs );
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
					} else {
						if( isset( $settings["{$device}_row_gap"] ) && isset( $settings["{$device}_column_gap"] ) ) {
							$styles["--{$device}-gap"] = "{$settings["{$device}_row_gap"]}px {$settings["{$device}_column_gap"]}px";
						}
						if( isset( $settings["{$device}_row_gap"] ) ) {
							$styles["--{$device}-row-gap"] = $settings["{$device}_row_gap"] . "px";
						}
						if( isset( $settings["{$device}_column_gap"] ) ) {
							$styles["--{$device}-column-gap"] = $settings["{$device}_column_gap"] . "px";
						}
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

	/**
	 * Get button arguments based on settings.
	 *
	 * @param array $settings Button settings.
	 * @param string $prefix Prefix for button argument keys.
	 * @return array Button arguments.
	 */
	public static function get_button_args( array $settings, string $prefix = 'button_' ) {
		$icon = '';
		if( isset( $settings['button_icon'] ) ) {
			$icon = $settings['button_icon']['value'];
			if( is_array( $icon ) && !empty( $icon['url'] ) ) {
				$icon = $icon['url'];
			}
			unset( $settings['button_icon'] );
		}

		if( isset( $settings['button_type'] ) ) {
			$settings["{$prefix}type"] = $settings['button_type'];
			unset( $settings['button_type'] );
		}
		if( isset( $settings['button_transparent'] ) ) {
			$settings["{$prefix}transparent"] = parent::to_bool( $settings['button_transparent'] );
			unset( $settings['button_transparent'] );
		}
		if( isset( $settings['button_small'] ) ) {
			$settings["{$prefix}small"] = parent::to_bool( $settings['button_small'] );
			unset( $settings['button_small'] );
		}
		if( $icon ) {
			$settings["{$prefix}icon"] = $icon;
		}
		if( isset( $settings['button_text'] ) ) {
			$settings["{$prefix}text"] = $settings['button_text'];
			unset( $settings['button_text'] );
		}
		if( isset( $settings['button_link'] ) ) {
			$settings["{$prefix}link"] = $settings['button_link'];
			unset( $settings['button_link'] );
		}
		if( isset( $settings['button_new_tab'] ) && isset( $settings["{$prefix}link"] ) ) {
			$settings["{$prefix}new_tab"] = parent::to_bool( $settings['button_new_tab'] );
			if( $settings["{$prefix}new_tab"] ) {
				if( is_array( $settings["{$prefix}link"] ) ) {
					$settings["{$prefix}link"]['is_external'] = 'on';
				}
			}
			unset( $settings['button_new_tab'] );
		}
		if( isset( $settings['button_icon_align'] ) ) {
			$settings["{$prefix}icon_align"] = $settings['button_icon_align'];
			unset( $settings['button_icon_align'] );
		}
		if( isset( $settings['button_style'] ) ) {
			$settings["{$prefix}style"] = $settings['button_style'];
			unset( $settings['button_style'] );
		}
		if( isset( $settings['button_fullwidth'] ) ) {
			$settings["{$prefix}fullwidth"] = parent::to_bool( $settings['button_fullwidth'] );
			unset( $settings['button_fullwidth'] );
		}
		if( isset( $settings['button_align'] ) ) {
			$settings["{$prefix}align"] = $settings['button_align'];
			unset( $settings['button_align'] );
		}
		
		return $settings;
	}

	public static function button_default_args( $prefix ) {
		if( static::$use_relatives_for_align ) {
			$align = 'start';
		} else {
			$align = is_rtl() ? 'right' : 'left';
		}

		return [
			"{$prefix}transparent"	=> false,
			"{$prefix}type"			=> 'primary',
			"{$prefix}small"		=> false,
			"{$prefix}icon"			=> '',
			"{$prefix}text"			=> '',
			"{$prefix}title"		=> '',
			"{$prefix}link"			=> [],
			"{$prefix}new_tab"		=> false,
			"{$prefix}fullwidth"	=> false,
			"{$prefix}icon_align"	=> $align,
			"{$prefix}style"		=> 'rounded',
			"{$prefix}align"		=> $align,
			"{$prefix}classes"		=> [],
			"{$prefix}id"			=> '',
			"{$prefix}disabled"		=> false,
			"{$prefix}loading"		=> false,
			"{$prefix}atts"			=> [],
		];
	}

	public static function button_default_args_skips( $prefix ) {
		return ["{$prefix}icon"];
	}

	/**
	 * Check and set default values for button arguments.
	 *
	 * @param array $args Button arguments.
	 * @param string $prefix Prefix for button argument keys.
	 * @return array Button arguments with defaults applied.
	 */
	public static function check_button_defaults( array $args, string $prefix = 'button_' ) {
		if( isset( $args["{$prefix}link"] ) ) {
			if( !is_array( $args["{$prefix}link"] ) ) {
				$args["{$prefix}link"] = [
					'url'				=> $args["{$prefix}link"],
					'is_external'		=> isset( $args["{$prefix}new_tab"] ) && parent::to_bool( $args["{$prefix}new_tab"] ),
					'nofollow'			=> isset( $args["{$prefix}new_tab"] ) && parent::to_bool( $args["{$prefix}new_tab"] ),
					'custom_attributes'	=> '',
				];
			} else {
				if( isset( $args["{$prefix}new_tab"] ) ) {
					$args["{$prefix}link"]['is_external'] = parent::to_bool( $args["{$prefix}new_tab"] );
					$args["{$prefix}link"]['nofollow'] = parent::to_bool( $args["{$prefix}new_tab"] );
				}
			}
		}

		if( isset( $args["{$prefix}classes"] ) ) {
			if( is_array( $args["{$prefix}classes"] ) ) {
				$args["{$prefix}classes"] = parent::array_flatten( $args["{$prefix}classes"] );
			} else {
				$args["{$prefix}classes"] = [$args["{$prefix}classes"]];
			}
		}

		return parent::check_default( $args, static::button_default_args( $prefix ), static::button_default_args_skips( $prefix ) );
	}

	/**
	 * Check if a post is built with Elementor.
	 *
	 * @param int $id Post ID.
	 * @return bool True if the post is built with Elementor, false otherwise.
	 */
	public static function is_built_with_elementor( $id ) {
		return Plugin::$instance->documents->get( $id )->is_built_with_elementor();
	}

	/**
	 * Get the content of a post built with Elementor.
	 *
	 * @param int $id Post ID.
	 * @param bool $inline_css Whether to include inline CSS.
	 * @return string The content of the post.
	 */
	public static function get_content( $id, $inline_css = false ) {
		if( !self::is_built_with_elementor( $id ) ) return '';

		ob_start();

		$post = new \Elementor\Core\Files\CSS\Post( $id );
		$meta = $post->get_meta();

		if( !$inline_css && $post::CSS_STATUS_FILE === $meta['status'] && !wp_doing_ajax() ) {
			?>
			<link rel="stylesheet" id="elementor-post-<?php echo esc_attr( $id ); ?>-css" href="<?php echo esc_url( $post->get_url() ); ?>" type="text/css" media="all">
			<?php
		} else {
			echo '<style>' . $post->get_content() . '</style>'; //phpcs:ignore

			Plugin::$instance->frontend->print_fonts_links();
		}

		$page_assets = get_post_meta( $id, '_elementor_page_assets', true );

		if ( $page_assets ) {
			Plugin::$instance->assets_loader->enable_assets( $page_assets );
		}

		echo Plugin::$instance->frontend->get_builder_content_for_display( $id, $inline_css ); //phpcs:ignore

		if ( !$inline_css && empty( $meta['status'] ) && empty( $meta['time'] ) ) {
			$post = new \Elementor\Core\Files\CSS\Post( $id );
			$meta = $post->get_meta();

			if ( $post::CSS_STATUS_FILE === $meta['status'] && !wp_doing_ajax() ) {
				?>
				<link rel="stylesheet" id="elementor-post-<?php echo esc_attr( $id ); ?>-css" href="<?php echo esc_url( $post->get_url() ); ?>" type="text/css" media="all">
				<?php
			}
		}

		if ( $post::CSS_STATUS_FILE === $meta['status'] && !wp_doing_ajax() ) {
			wp_dequeue_style( 'elementor-post-' . $id );
			wp_deregister_style( 'elementor-post-' . $id );
		}

		return ob_get_clean();
	}

	/**
	 * Check if a link attribute contains a valid URL.
	 *
	 * @param array|string $link_attr Link attributes.
	 * @return bool True if the link attribute contains a valid URL, false otherwise.
	 */
	public static function has_link( $link_attr ) {
		return ( is_array( $link_attr ) && !empty( $link_attr['url'] ) ) || ( !is_array( $link_attr ) && !empty( $link_attr ) );
	}
}