<?php
namespace DrPlus;

use DrPlus\Utils\Options;

class Utils {
	// Local cache variables
	private static $posts_avg_scores = [];

	/**
	 * Checks and applies default values to an array based on provided defaults and skip indexes. Also, checking the type of the value based on defaults.
	 *
	 * This function iterates through the given array of defaults and applies them to the input array,
	 * considering skip indexes to preserve certain values. It performs type checks and conversions
	 * for mismatched types and handles nested arrays recursively.
	 *
	 * @param array $value The input array to which default values need to be applied.
	 * @param array $defaults An associative array containing default values to be applied.
	 * @param array $skips An array of indexes to be skipped when applying default values.
	 * @param boolean $fill_empty Check if the key is set but it's empty and fill with default value
	 * @return array The input array with default values applied where necessary.
	 */
	public static function check_default( $value, $defaults, $skips = [], $fill_empty = false ) {
		foreach( $defaults as $index => $default ) {
			if( in_array( $index, $skips ) ) {
				if( !isset( $value[$index] ) ) {
					$value[$index] = $default;
				}
				continue;
			}

			if( isset( $value[$index] ) && gettype( $default ) != gettype( $value[$index] ) ) {
				if( gettype( $default ) == 'boolean' ) {
					$value[$index] = self::to_bool( $value[$index] );
				} else {
					if( is_numeric( $default ) && !is_numeric( $value[$index] ) ) {
						$value[$index] = $default;
					} else if( !is_numeric( $default ) && !is_numeric( $value[$index] ) ) {
						$value[$index] = $default;
					}
				}
			}
			if( !isset( $value[$index] ) || ( $fill_empty && empty( $value[$index] ) ) ) {
				$value[$index] = $default;
				continue;
			}
			if( is_array( $default ) && !empty( $default ) ) {
				$value[$index] = self::check_default( $value[$index], $default );
			}
		}
		return $value;
	}

	/**
	 * Convert value to boolean
	 *
	 * @param mixed $value
	 * @return boolean
	 */
	public static function to_bool( $value ) {
		if( empty( $value ) || is_wp_error( $value ) || is_null( $value ) ) return false;
		$value = sanitize_text_field( strtolower( $value ) );
		if( in_array( $value, ["false", 'no', 'off', '0'] ) ) return false;
		if( in_array( $value, ["true", 'yes', 'on', '1'] ) ) return true;

		return wp_validate_boolean( $value );
	}

	/**
	 * Convert and sanitize string
	 *
	 * @param string $string
	 * @param string|array|boolean|callback $sanitize Write functions you want to sanitize the string with them. Separate each function with '&&' for multiple functions. Or write a callback. Bool mode will exec sanitize_text_field
	 * @param string|array|boolean|callback $name Like sanitize param
	 * @param boolean $reverse Convert English chars to persian
	 * 
	 * @return string
	 */
	public static function convert_chars( $string, $sanitize = 'sanitize_text_field', $sanitize_after = '', $reverse = false ) {
		if( !empty( $sanitize ) ) {
			if( is_callable( $sanitize ) ) {
				$string = call_user_func( $sanitize, $string );
			} else {
				$functions = $sanitize;
				if( is_string( $functions ) ) {
					$functions = explode( '&&', $functions );
				} else if( is_bool( $functions ) ) {
					$functions = ['sanitize_text_field'];
				}
				foreach( $functions as $function ) {
					// Sanitize the function name
					if( is_string( $function ) ) {
						$function = sanitize_text_field( $function );
						$function = remove_accents( $function );
						$function = wp_strip_all_tags( $function );
						$function = str_replace( [' ', '&'], '', $function );
						$function = preg_replace( '|%([a-fA-F0-9][a-fA-F0-9])|', '', $function );
						// Remove HTML entities.
						$function = preg_replace( '/&.+?;/', '', $function );
						$function = str_replace( ['Utils::'], 'self::', $function );
					}
					if( is_callable( $function ) ) {
						$string = call_user_func( $function, $string );
					}
				}
			}
		}

		if( is_string( $string ) || $reverse ) {
			$chars = [
				'۰'	=> '0',
				'۱'	=> '1',
				'۲'	=> '2',
				'۳'	=> '3',
				'۴'	=> '4',
				'۵'	=> '5',
				'۶'	=> '6',
				'۷'	=> '7',
				'۸'	=> '8',
				'۹'	=> '9',
				'٪'	=> '%',
				'÷'	=> '/',
				'×'	=> '*',
				'-'	=> '-',
				'ـ'	=> '_',
				'ي'	=> 'ی',
				'ك'	=> 'ک',
			];

			$string = !$reverse ? str_replace( array_keys( $chars ), array_values( $chars ), $string ) : str_replace( array_values( $chars ), array_keys( $chars ), $string );
		}
		return $sanitize_after ? self::convert_chars( $string, $sanitize_after, [], false ) : $string;
	}

	/**
	 * Displays errors by adjusting the PHP error reporting settings.
	 *
	 * If the debug mode is active (DEV_CONST is true) or if debug mode checking is disabled,
	 * this function sets the necessary PHP configurations to display errors.
	 *
	 * @param bool $check_debug_is_active Optional. Whether to check if debug mode is active before showing errors. [Default: true]
	 * @param array $users List of usernames to only show for these users
	 *
	 * @return void
	 */
	public static function show_errors( bool $check_debug_is_active = true, array $users = [] ) : void {
		if( !$check_debug_is_active || ( $check_debug_is_active && DRPLUS_DEV ) ) {
			if( !empty( $users ) && !in_array( wp_get_current_user()->user_login, $users ) ) return;
			ini_set('display_errors', 1);
			ini_set('display_startup_errors', 1);
			error_reporting(E_ALL);
		}
	}

	/**
	 * Convert array of HTML classes to string
	 *
	 * @param array $classes
	 * @param bool $add_class_arg Add class="" for return
	 * @return string
	 */
	public static function prepare_html_classes( array $classes, $add_class_arg = false ) {
		$new_array = [];
		foreach( $classes as $class ) {
			$class = sanitize_html_class( sanitize_text_field( $class ) );
			if( empty( $class ) ) continue;
			$new_array[] = $class;
		}
		$new_array = array_unique( $new_array );
		$classes = implode( " ", $new_array );
		if( $classes === '' ) return '';
		return $add_class_arg ? "class=\"{$classes}\"" : implode( " ", $new_array );
	}

	/**
	 * Convert array of args to string for placing at shortcode
	 *
	 * @param array $args
	 * @return string
	 */
	public static function prepare_shortcode_args( $args ) {
		$result = '';
		foreach( $args as $key => $value ) {
			$result .= " {$key}=\"{$value}\"";
		}
		return $result;
	}

	/**
	 * Ensures that values in the source array are allowed according to the specified set of allowed values.
	 *
	 * @param mixed $source The source data to be checked. Can be a scalar or an array of values.
	 * @param array $check An array containing the allowed values.
	 * @param mixed $default The default value to return if any source value is not allowed. Default is an empty string.
	 * 
	 * @return mixed Returns the source data if all values are allowed, or the default value if any value is not allowed.
	 * 
	 * @example It will check the 'personal'(source) is exists in 'check' or not and if not, return the 'personal'(default)
	 * $type = Utils::ensure_values_in_array( 'personal', ['personal', 'legal'], 'personal' );
	 */
	public static function ensure_values_in_array( $source, $allowed_values, $default = '' ) {
		$is_in_array = true;
		$data = $source;
		if( is_scalar( $data ) ) {
			$data = [$data];
		}
		foreach( $data as $value ) {
			if( !in_array( $value, $allowed_values ) ) {
				$is_in_array = false;
				break;
			}
		}

		return $is_in_array ? $source : $default;
	}

	/**
	 * Convert the number to string and add zero if it's lower than 10 or -10
	 *
	 * @param string $number
	 * @return string
	 */
	public static function add_zero( $number ) {
		if( is_numeric( $number ) && $number < 10 ) {
			if( $number >= 0 ) {
				$number = floatval( $number );
				$number = "0{$number}";
			} else {
				$number = "-0{$number}";
			}
		}
		return $number;
	}

	public static function get_post_id( $post = null ) {
		if( $post === 0 ) {
			return 0;
		}
		
		if( is_a( $post, 'WP_Post' ) ) {
			return $post->ID;
		}

		if( is_numeric( $post ) && absint( $post ) !== 0 ) {
			return absint( $post );
		}

		if( absint( $post ) === 0 && !is_singular() ) return 0;

		return get_the_ID();
	}

	public static function get_post_options( $defaults, $post_id = 0, array $options = [] ) {
		$post_id = self::get_post_id( $post_id );

		if( !$options ) {
			$options = $defaults;
		} else {
			foreach( $options as $index => $option ) {
				if( isset( $defaults[$option] ) ) {
					$options[$option] = $defaults[$option];
				}
				unset( $options[$index] );
			}
		}
		foreach( $options as $key => $default ) {
			$value = get_post_meta( $post_id, "_{$key}", true );
			if( is_bool( $default ) ) {
				if( $value === '' ) {
					$value = $default;
				}
				$value = self::to_bool( $value );
			}
			if( $value === '' ) {
				if( !metadata_exists( 'post', $post_id, $key ) ) {
					$value = $defaults[$key];
				}
			}
			$options[$key] = $value;
		}
		return self::check_default( $options, $defaults );
	}

	public static function save_post_options( array $options, $defaults, $post_id = 0 ) {
		$post_id = self::get_post_id( $post_id );

		foreach( $defaults as $option_key => $value ) {
			if( isset( $options[$option_key] ) ) {
				$value = $options[$option_key];
				if( is_scalar( $value ) ) {
					if( is_bool( $value ) ) {
						$value = self::to_bool( $value );
						if( $value === false ) {
							$value = "false";
						}
					} else {
						$value = self::convert_chars( $value );
					}
				}
				update_post_meta( $post_id, "_{$option_key}", $value );
			}
		}
	}

	/**
	 * Unset indexes if exists from array or object
	 *
	 * @param array|object $data The main data
	 * @param array $removes An array of things you want to remove. Each index can be string(Name of the index of the $data) OR array. If it was array it will find the key in the $data and search for childs to remove
	 * @param array $skips List of skips of remove list
	 * @param boolean $search_in_values If true, it will search for the value in the data and remove it. Otherwise, it will just remove the key
	 *
	 * @return array|object $data with removed things
	 */
	public static function unset( $data, array $removes, array $skips = [], bool $search_in_values = false ) {
		foreach( $removes as $key => $remove ) {
			if( in_array( $remove, $skips ) ) continue;

			if( is_array( $remove ) ) {
				if( isset( $data[$key] ) && is_array( $data[$key] ) ) {
					$data[$key] = self::unset( $data[$key], $remove, [], $search_in_values );
				}
			} else {
				if( $search_in_values ) {
					foreach( $data as $index => $value ) {
						if( $value === $remove ) {
							if( is_array( $data ) ) {
								unset( $data[$index] );
							} else {
								unset( $data->$index );
							}
						}
					}
				} else {
					if( ( is_array( $data ) && isset( $data[$remove] ) ) || ( is_object( $data ) && isset( $data->$remove ) ) ) {
						if( is_array( $data ) ) {
							unset( $data[$remove] );
						} else {
							unset( $data->$remove );
						}
					}
				}
			}
		}

		return $data;
	}

	/**
	 * Remove empty indexes from array
	 *
	 * @param array $array
	 * @param boolean $all_empty_values By default the function just check for empty string. But with this arg it can check all types of empty
	 * @param boolean $remove_nulls Remove null values
	 *
	 * @return array
	 */
	public static function remove_empty_indexes( array $array, bool $all_empty_values = false, bool $remove_nulls = false ) {
		foreach( $array as $index => $value ) {
			if(
				( !$all_empty_values && $value === '' ) ||
				( $all_empty_values && empty( $value ) ) ||
				( !$all_empty_values && $remove_nulls && is_null( $value ) )
			) {
				unset( $array[$index] );
			}
		}
		return $array;
	}

	/**
	 * Reposition an array element by its key.
	 *
	 * @param array      $array The array is being reordered.
	 * @param string|int $key The key of the element you want to reposition.
	 * @param int        $order The position in the array you want to move the element to. (0 is first)
	 */
	public static function reposition_array_element( array &$array, $key, int $order ) {
		if( ($a = array_search( $key, array_keys( $array ) ) ) === false ) {
			return false;
		}
		$p1 = array_splice( $array, $a, 1 );
		$p2 = array_splice( $array, 0, $order );
		$array = array_merge( $p2, $p1, $array );
	}

	/**
	 * Converts a string to PascalCase.
	 *
	 * PascalCase is a naming convention where each word in the string begins with a capital letter,
	 * and there are no spaces or punctuation between words.
	 * 
	 * This function is used in DB and AJAX
	 *
	 * @param string $input The input string to be converted to PascalCase.
	 *
	 * @return string The PascalCase version of the input string.
	 */
	public static function convert_to_pascal_case( string $input ) {
		$input = str_replace( ['-', '_'], ' ', $input );
		$words = explode( ' ', $input ); // Split input string into an array of words
		$capitalizedWords = array_map( 'ucwords', $words ); // Capitalize the first letter of each word
		$pascalCaseString = implode( '', $capitalizedWords ); // Combine the words back into a string
		return str_replace( ' ', '', $pascalCaseString ); // Remove spaces
	}

	/**
	 * Checks if the given value meets specified requirements.
	 *
	 * This function verifies whether the provided value fulfills the requirements specified
	 * in the $requires array or object. It can optionally perform checks for emptiness of
	 * required fields.
	 *
	 * @param array|object $value The value to be checked against requirements.
	 * @param array|object $requires An array or object containing the required fields.
	 * @param bool $check_empty Whether to also check for empty values in required fields.
	 * @return bool Returns true if the value meets the requirements, false otherwise.
	 */
	public static function check_requires( $value, $requires, $check_empty = true ) {
		if( empty( $value ) ) return false;
		if( empty( $requires ) || !is_array( $requires ) ) return false;

		foreach( $requires as $require ) {
			if( $check_empty ) {
				if( is_array( $value ) ) {
					if( empty( $value[$require] ) ) return false;
				} else {
					if( empty( $value->$require ) ) return false;
				}
			} else {
				if( is_array( $value ) ) {
					if( !isset( $value[$require] ) ) return false;
				} else {
					if( !isset( $value->$require ) ) return false;
				}
			}
		}

		return true;
	}

	/**
	 * Flattens a multi-dimensional array into a single-dimensional array.
	 *
	 * This function recursively iterates through the input array and flattens it by merging sub-arrays or non-array elements into a single array.
	 *
	 * @param array $items The input array is to be flattened.
	 * @return array The flattened array containing all non-array elements.
	 */
	public static function array_flatten( array $items ) {
		$result = [];
		foreach( $items as $item ) {
			if( !is_array( $item ) ) {
				$result[] = $item;
			} else {
				$result = array_merge( $result, array_values( $item ) );
			}
		}

		return $result;
	}

	/**
	 * Convert an object to an array
	 *
	 * @param object $obj
	 * @param boolean $force
	 *
	 * @return array
	 */
	public static function obj_to_array( $obj, $force = false ) {
		return is_object( $obj ) || $force ? json_decode( json_encode( $obj ), true ) : $obj;
	}

	/**
	 * Convert an array to an object
	 *
	 * @param array $array
	 * @param boolean $force
	 *
	 * @return object
	 */
	public static function array_to_obj( $array, $force = false ) {
		return is_array( $array ) || $force ? json_decode( json_encode( $array ) ) : $array;
	}

	/**
	 * Retrieves the upload directory path for storing files.
	 *
	 * This method returns the path to the WordPress uploads directory.
	 * It uses caching to avoid redundant calls to `wp_upload_dir()`.
	 * If the upload path does not exist, it attempts to create it.
	 * Returns the full path to the writable upload directory.
	 * 
	 * @param string $type Accepts: path | base
	 *
	 *
	 * @return string Full filesystem path to the upload directory.
	 */
	public static function get_upload_dir( string $type = 'path' ) {
		static $upload_dir_path = null;
		static $upload_dir_base = null;
		if( $type == 'path' ) {
			if( $upload_dir_path === null ) {
				$upload_dir = wp_upload_dir();
				if( wp_mkdir_p( $upload_dir['path'] ) ) {
					$upload_dir_path = $upload_dir['path'];
				}
			}
			return $upload_dir_path;
		}
		if( $type == 'base' ) {
			if( $upload_dir_base === null ) {
				$upload_dir = wp_upload_dir();
				if( wp_mkdir_p( $upload_dir['basedir'] ) ) {
					$upload_dir_base = $upload_dir['basedir'];
				}
			}
			return $upload_dir_base;
		}
	}

	/**
	 * Retrieves the full file path for a given filename within the WordPress uploads directory.
	 *
	 * This function ensures that the necessary upload directory structure is created if it doesn't exist.
	 *
	 * @param string $filename The name of the file.
	 *
	 * @return string The full file path including the filename within the WordPress uploads directory.
	 */
	public static function get_file_path( $filename ) : string {
		/**
		 * WordPress upload directory details.
		 *
		 * @return string File path
		 */
		return trailingslashit( self::get_upload_dir() ) . $filename;
	}

	/**
	 * Hide the element if not value and current is the same
	 *
	 * @param mixed $value
	 * @param mixed $current
	 * @param boolean $display
	 * @return string Hide style attribute
	 */
	public static function hide( $value, $current = true, $display = true ) {
		if ( (string) $value !== (string) $current ) {
			$result = " style='display:none'";
		} else {
			$result = '';
		}
	
		if ( $display ) {
			echo $result;
		}
	
		return $result;
	}

	public static function get_module_name( $index, $module ) {
		return is_array( $module ) ? $index : $module;
	}

	/**
	 * Check the module requirements
	 *
	 * @param string|array $requirements
	 * @return boolean
	 */
	public static function should_include_module( $requirements = [] ) {
		if( empty( $requirements ) || !is_array( $requirements ) ) return true;
		if( is_string( $requirements ) ) $requirements = [$requirements];
		// Check WC is active
		if( in_array( 'woocommerce', $requirements ) && !self::is_wc_active() ) {
			return false;
		}
		return true;
	}

	public static function button_types() {
		return [
			'primary'	=> esc_html_x( 'Primary', 'Button type', 'drplus' ),
			'secondary'	=> esc_html_x( 'Secondary', 'Button type', 'drplus' ),
			'gray'		=> esc_html_x( 'Gray', 'Button type', 'drplus' ),
			'white'		=> esc_html_x( 'White', 'Button type', 'drplus' ),
			'action'	=> esc_html_x( 'Action', 'Button type', 'drplus' ),
			'bordered'	=> esc_html_x( 'Bordered', 'Button type', 'drplus' ),
		];
	}

	public static function button_styles() {
		return [
			'normal'	=> esc_html_x( 'Normal', 'Button style', 'drplus' ),
			'rounded'	=> esc_html_x( 'Rounded', 'Button style', 'drplus' ),
			'circle'	=> esc_html_x( 'Circle', 'Button style', 'drplus' ),
		];
	}

	public static function custom_tags() {
		return [
			'h1'	=> __( "H1", 'drplus' ),
			'h2'	=> __( "H2", 'drplus' ),
			'h3'	=> __( "H3", 'drplus' ),
			'h4'	=> __( "H4", 'drplus' ),
			'h5'	=> __( "H5", 'drplus' ),
			'h6'	=> __( "H6", 'drplus' ),
			'div'	=> __( "div", 'drplus' ),
			'p'		=> __( "p", 'drplus' ),
			'span'	=> __( "span", 'drplus' )
		];
	}

	public static function is_wc_active() {
		static $is = null;
		if( $is === null ) {
			$plugin = 'woocommerce/woocommerce.php';
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			return is_plugin_active( $plugin )
					// validate if $plugin actually exists, the plugin might be active however not installed.
					&& is_file( trailingslashit( WP_PLUGIN_DIR ) . $plugin );
		}
		return $is;
	}

	public static function number_decimal_format( $value ) {
		$decimals = explode( ".", $value );
		$decimals = !empty( $decimals[1] ) ? strlen( $decimals[1] ) : 0;
		return number_format( $value, $decimals );
	}

	public static function get_wc_account_endpoint( $items = [] ) {
		static $endpoint = '';
		if( $endpoint === '' ) {
			if( empty( $items ) ) {
				$items = array_keys( wc_get_account_menu_items() );
			} else {
				$items = array_keys( $items );
			}
			$items[] = 'view-order';
			global $wp;
			if( !empty( $wp->query_vars ) ) {
				$intersect = array_intersect( $items, array_keys( $wp->query_vars ) );
				if( $intersect ) {
					$endpoint = array_values( $intersect )[0];
				} else {
					$endpoint = 'dashboard';
				}
			}
		}
		return $endpoint;
	}

	/**
	 * Extract selected keys from an array or object
	 *
	 * @param array|object $data
	 * @param array $keys List of keys you want to extract
	 * @return mixed Array of extracted keys from $data
	 */
	public static function extract( $data, array $keys ) {
		$result = [];
		foreach( $keys as $key_index => $key ) {
			if( is_array( $key ) ) {
				if( isset( $data[$key_index] ) && is_array( $data[$key_index] ) ) {
					$data[$key_index] = self::extract( $data[$key_index], $key );
				}
			} else {
				if( ( is_array( $data ) && isset( $data[$key] ) ) || ( is_object( $data ) && isset( $data->$key ) ) ) {
					if( is_array( $data ) && isset( $data[$key] ) ) {
						$result[$key] = $data[$key];
					} else if( is_object( $data ) && isset( $data->$key ) ) {
						$result[$key] = $data->$key;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Retrieves a nested value from a multi-dimensional array using a dot-notated string path.
	 *
	 * This function allows you to access deeply nested array elements by specifying
	 * a string path with keys separated by a delimiter (default is '.').
	 *
	 * @param array $array      The array to traverse.
	 * @param string $path      The dot-notated string representing the key path (e.g., "a.b.c").
	 * @param string $delimiter The delimiter used to separate keys in the path (default is '.').
	 *
	 * @return mixed|null       Returns the value at the specified path if found, or null if any key is missing.
	 */
	public static function get_nested_value( array $array, string $path, string $delimiter = '.' ) {
		$keys = explode( $delimiter, $path );
		
		foreach ( $keys as $key ) {
			if ( is_array( $array ) && array_key_exists( $key, $array ) ) {
				$array = $array[ $key ];
			} else {
				return null; // or throw exception or return default value
			}
		}
		
		return $array;
	}

	public static function fonts() {
		return apply_filters( 'drplus/fonts', [
			'IRANYekanX'		=> [
				'fa'	=> __( 'IRANYekanX', 'drplus' ),
				'en'	=> 'IRANYekanX'
			],
			'IRANYekanXFANum'	=> [
				'fa'	=> __( 'IRANYekanXFANum', 'drplus' ),
				'en'	=> 'IRANYekanXFANum'
			],
			'Anjoman'		=> [
				'fa'	=> __( 'Anjoman', 'drplus' ),
				'en'	=> 'Anjoman'
			],
			'AnjomanFANum'	=> [
				'fa'	=> __( 'AnjomanFANum', 'drplus' ),
				'en'	=> 'AnjomanFANum'
			],
			'AbiFANum'			=> [
				'fa'	=> __( 'AbiFANum', 'drplus' ),
				'en'	=> 'AbiFANum'
			],
			'Abi'				=> [
				'fa'	=> __( 'Abi', 'drplus' ),
				'en'	=> 'Abi'
			],
			'Rokh'				=> [
				'fa'	=> __( 'Rokh', 'drplus' ),
				'en'	=> 'Rokh'
			],
			'RokhFANum'			=> [
				'fa'	=> __( 'RokhFANum', 'drplus' ),
				'en'	=> 'RokhFANum'
			],
			'Vazirmatn'			=> [
				'fa'	=> __( 'Vazirmatn', 'drplus' ),
				'en'	=> 'Vazirmatn'
			],
			'VazirmatnFANum'	=> [
				'fa'	=> __( 'VazirmatnFANum', 'drplus' ),
				'en'	=> 'VazirmatnFANum'
			],
			'DanaFANum'	=> [
				'fa'	=> __( 'DanaFANum', 'drplus' ),
				'en'	=> 'DanaFANum'
			],
		] );
	}

	public static function default_active_fonts() {
		return apply_filters( 'drplus/fonts/default_actives', ['IRANYekanXFANum', 'AnjomanFANum'] );
	}

	public static function get_font_stylesheet( string $font_name ) {
		return apply_filters( "drplus/fonts/{$font_name}/stylesheet", DRPLUS_URI . "assets/css/fonts/{$font_name}.min.css" );
	}

	public static function get_active_fonts() {
		$fonts = array_keys( self::fonts() );
		$default_options = [];
		foreach( $fonts as $font ) {
			$default_options["font_{$font}"] = in_array( $font, self::default_active_fonts() );
		}
		$options = array_filter( Options::get_options( $default_options ) );
		
		$active_fonts = [];
		foreach( $fonts as $font ) {
			if( !empty( $options["font_{$font}"] ) ) {
				$active_fonts[] = $font;
			}
		}

		return apply_filters( 'drplus/fonts/active', $active_fonts );
	}

	/**
	 * Minifies a hexadecimal color code by converting it to lowercase and optionally adding a hashtag.
	 *
	 * This method ensures the hex code does not convert to a 3-character format, which could cause issues with opacity values.
	 *
	 * @param string $string      The hexadecimal color code to be minified.
	 * @param bool   $add_hashtag Optional. Whether to add a hashtag to the minified hex code. Default is true.
	 *
	 * @return string The minified hexadecimal color code.
	 */
	public static function minify_hex( string $string, bool $add_hashtag = true ) {
		if( empty( $string ) ) {
			return '';
		}

		$string = strtolower( $string );
		$has_hashtag = str_starts_with( $string, '#' );
		$hex = $has_hashtag ? substr( $string, 1 ) : $string;

		// The hex code shouldn't convert to 3 chars. It will cause the wrong code for variables with opacity. #f63 ---opacity:10---> #f631a X(wrong);
		return $add_hashtag || $has_hashtag ? "#{$hex}" : $hex;
	}

	public static function get_user_id( $user_id = 0 ) {
		if( !empty( $user_id ) ) {
			if( is_numeric( $user_id ) ) {
				return $user_id;
			}
			if( is_object( $user_id ) ) {
				if( !empty( $user_id->ID ) ) {
					return $user_id->ID;
				} else if( !empty( $user_id->id ) ) {
					return $user_id->id;
				}
			}
			if( is_array( $user_id ) ) {
				if( !empty( $user_id['ID'] ) ) {
					return $user_id['ID'];
				} else if( !empty( $user_id['id'] ) ) {
					return $user_id['id'];
				}
			}
			$user = self::get_user_object( $user_id );
			if( !empty( $user ) ) {
				return $user->ID;
			}
		}

		return get_current_user_id();
	}

	public static function get_user_object( $user = null ) {
		if( !empty( $user ) ) {
			if( is_numeric( $user ) ) {
				$user = get_user_by( 'id', $user );
			} else if( is_array( $user ) ) {
				$user = get_user_by( 'id', $user['ID'] );
			} else if( is_string( $user ) && is_email( $user ) ) {
				$user = get_user_by( 'email', $user );
			}
		} else {
			if( is_user_logged_in() ) {
				$user = wp_get_current_user();
			}
		}
		return $user;
	}

	public static function get_post( $post = null ) {
		if( is_object( $post ) || is_numeric( $post ) ) {
			return get_post( $post );
		} else if( is_array( $post ) ) {
			if( !empty( $post['ID'] ) ) {
				return get_post( $post['ID'] );
			} else if( !empty( $post['id'] ) ) {
				return get_post( $post['id'] );
			}
		}
		global $post;
		return $post;
	}

	public static function time_leading_zero( $length ) {
		return implode( ":", array_map( fn( $number ) => self::add_zero( $number ), explode( ":", $length ) ) );
	}

	/**
	 * Generates a string of HTML attributes from an associative array.
	 *
	 * This method processes an array of attributes, converting boolean values to their 
	 * string representations and handling class names as needed. The resulting string 
	 * is suitable for use in HTML elements.
	 *
	 * @param array $attributes An associative array of attributes where the key is the attribute name 
	 *                          and the value is the attribute value.
	 * If the value is a boolean, it will be converted to "true" or "false" depending on the value.
	 * If the value is array or object it will convert to json encoded string.
	 * If the value is array and the key is "class", it will convert to space separated class names.
	 * 
	 * @return string A string of HTML attributes formatted for output.
	 */
	public static function get_html_attributes( array $attributes, array $skips = [] ) : string {
		$result = '';

		if( $skips ) {
			$attributes = self::unset( $attributes, $skips );
		}

		if( isset( $attributes['classes'] ) ) {
			if( !is_array( $attributes['classes'] ) ) {
				$attributes['classes'] = explode( " ", $attributes['classes'] );
			}
			if( !isset( $attributes['class'] ) ) {
				$attributes['class'] = [];
			}
			if( !is_array( $attributes['class'] ) ) {
				$attributes['class'] = explode( " ", $attributes['class'] );
			}
			$attributes['class'] = array_merge( $attributes['class'], $attributes['classes'] );
			unset( $attributes['classes'] );
		}

		$remove_attrs_if_empty = ['disabled', 'required', 'readonly'];
	
		foreach( $attributes as $key => $value ) {
			if( in_array( $key, $remove_attrs_if_empty ) && empty( $value ) ) continue;
			// Convert boolean values to "true" or "false"
			if( is_bool( $value ) ) {
				$result .= esc_attr( $key ) . '="' . ( $value ? 'true' : 'false') . '" ';
			} else {
				if( $key == 'class' ) {
					if( !is_array( $value ) ) {
						$value = explode( " ", $value );
					}
					if( is_array( $value ) ) {
						$value = implode( " ", array_filter( array_unique( array_map( 'sanitize_html_class', $value ) ) ) );
					}
					if( !$value ) continue;
				} else if( $key == 'style' ) {
					if( is_array( $value ) ) {
						$style = [];
						foreach( $value as $property => $style_value ) {
							$style[] = "{$property}:{$style_value}";
						}
						$value = implode( ";", $style );
					}
				}
				if( !is_scalar( $value ) ) {
					$value = wp_json_encode( $value );
					$result .= "{$key}='{$value}'";
				} else {
					$result .= esc_attr( $key ) . '="' . esc_attr( $value ) . '" ';
				}
			}
		}
	
		return trim( $result );
	}

	public static function get_cart_count() : int {
		if( !function_exists( 'WC' ) ) return 0;
		if( empty( WC()->cart ) ) return 0;
		static $count = null;
		if( $count === null ) {
			$count = WC()->cart->get_cart_contents_count();
		}
		return $count;
	}

	/**
	 * Get list of all registered sidebars
	 *
	 * @return array
	 */
	public static function sidebars_list() : array {
		global $wp_registered_sidebars;

		if( empty( $wp_registered_sidebars ) ) return [];

		return $wp_registered_sidebars;
	}

	/**
	 * Get nav menu items by location
	 *
	 * @param $location The menu location id
	 */
	public static function get_nav_menu_items_by_location( $location, $args = [] ) {
	
		// Get all locations
		$locations = get_nav_menu_locations();
	
		// Get object id by location
		$object = wp_get_nav_menu_object( $locations[$location] );
	
		// Get menu items by menu name
		$menu_items = wp_get_nav_menu_items( $object->name, $args );
	
		// Return menu post objects
		return $menu_items;
	}

	public static function get_icon_packs() {
		static $packs = null;
		if( !file_exists( DRPLUS_DIR . "assets/icons.json" ) ) return [];
		if( $packs === null ) {
			$theme_packs = wp_json_file_decode( DRPLUS_DIR . "assets/icons.json", ['associative' => true] );
			$packs = apply_filters( 'drplus/icon-picker/packs', $theme_packs );

			// Set svg icons url
			foreach( $packs as $pack_name => $pack ) {
				if( $pack['mode'] == 'svg' ) {
					$pack['dir'] = trailingslashit( $pack['dir'] );
					foreach( $pack['icons'] as $icon_index => $icon ) {
						$svg_url = DRPLUS_URI . "assets/{$pack['dir']}{$icon}";
						if( !isset( $theme_packs[$pack_name] ) ) {
							$svg_url = "{$pack['dir']}{$icon}";
						}
						if( substr( $svg_url, -4 ) != '.svg' ) {
							$svg_url .= '.svg';
						}

						if( $pack['label_icon'] == str_replace( ".svg", "", $icon ) ) {
							$packs[$pack_name]['label_icon'] = $svg_url;
						}

						$packs[$pack_name]['icons'][$icon_index] = $svg_url;
					}
				}
			}
		}

		return $packs;
	}

	/**
	 * Get HTML of the variables can be used in the string options
	 *
	 * @param array $variables. Key for variable and value for description
	 * @param bool $row
	 * @return void HTML
	 */
	public static function variables_html( array $variables, bool $row = false ) {
		?>
		<div class="drplus_variables">
			<?php foreach( $variables as $variable => $description ) { ?>
				<div class="drplus_variable<?php echo $row ? ' drplus_variable-row' : '' ?>" data-variable="<?php echo esc_attr( $variable ) ?>">
					<code class="drplus_variable-value">{<?php echo esc_html( $variable ) ?>}</code><span class="drplus_variable-description"><?php echo esc_html( $description ) ?></span>
				</div>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * Check a string is json or not
	 *
	 * @param string $string
	 * @return boolean
	 */
	public static function is_json( $string ) {
		if( !is_string( $string ) ) return false;
		json_decode( $string );
		return json_last_error() === JSON_ERROR_NONE;
	}

	/**
	 * Outputs hidden form inputs for each query string variable.
	 *
	 * @since 3.0.0
	 * @param string|array $values Name value pairs, or a URL to parse.
	 * @param array        $exclude Keys to exclude.
	 * @param string       $current_key Current key we are outputting.
	 * @param bool         $return Whether to return.
	 * @return string
	 */
	public static function query_string_form_fields( $values = null, $exclude = array(), $current_key = '', $return = false ) {
		if ( is_null( $values ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$values = $_GET;
		} elseif ( is_string( $values ) ) {
			$url_parts = wp_parse_url( $values );
			$values    = array();

			if ( ! empty( $url_parts['query'] ) ) {
				// This is to preserve full-stops, pluses and spaces in the query string when ran through parse_str.
				$replace_chars = array(
					'.' => '{dot}',
					'+' => '{plus}',
				);

				$query_string = str_replace( array_keys( $replace_chars ), array_values( $replace_chars ), $url_parts['query'] );

				// Parse the string.
				parse_str( $query_string, $parsed_query_string );

				// Convert the full-stops, pluses and spaces back and add to values array.
				foreach ( $parsed_query_string as $key => $value ) {
					$new_key            = str_replace( array_values( $replace_chars ), array_keys( $replace_chars ), $key );
					$new_value          = str_replace( array_values( $replace_chars ), array_keys( $replace_chars ), $value );
					$values[ $new_key ] = $new_value;
				}
			}
		}
		$html = '';

		foreach ( $values as $key => $value ) {
			if ( in_array( $key, $exclude, true ) ) {
				continue;
			}
			if ( $current_key ) {
				$key = $current_key . '[' . $key . ']';
			}
			if ( is_array( $value ) ) {
				$html .= self::query_string_form_fields( $value, $exclude, $key, true );
			} else {
				$html .= '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( wp_unslash( $value ) ) . '" />';
			}
		}

		if ( $return ) {
			return $html;
		}

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public static function calc_product_discount_percentage( $regular_price, $sale_price ) : int {
		$percentage = absint( (100 - round( $sale_price / $regular_price * 100 )) );
		if( $percentage === 100 && !empty( $sale_price ) ) {
			$percentage = 99;
		}
		if( $percentage < 1 ) {
			$percentage = 1;
		}
		return $percentage;
	}

	public static function maybe_define( $name, $value ) {
		if( !defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Converts seconds into a formatted string containing hours, minutes, and seconds.
	 * 
	 * - If hours are not needed, the output will only include minutes and seconds.
	 * - The format will be "HH:MM:SS" or "MM:SS" depending on the input.
	 * 
	 * @param int $seconds The total number of seconds to convert.
	 * @return string The formatted time string.
	 */
	public static function second_to_string( int $seconds ) : string {
		if ($seconds < 0) return "";

		$hours = intdiv($seconds, 3600);
		$minutes = intdiv($seconds % 3600, 60);
		$remainingSeconds = $seconds % 60;

		if ($hours > 0) {
			return sprintf("%02d:%02d:%02d", $hours, $minutes, $remainingSeconds);
		}

		return sprintf("%02d:%02d", $minutes, $remainingSeconds);
	}

	/**
	 * Converts a given value to an absolute integer and ensures it falls within a specified range.
	 *
	 * This function uses `absint()` to convert the input to a non-negative integer.
	 * If a minimum (`$min`) or maximum (`$max`) value is provided, the result is clamped within the given range.
	 *
	 * @param mixed $string The value to be converted into an absolute integer.
	 * @param int|null $min Optional. The minimum allowable value. If null, no lower limit is applied. Default null.
	 * @param int|null $max Optional. The maximum allowable value. If null, no upper limit is applied. Default null.
	 *
	 * @return int The absolute integer value, constrained within the specified range if limits are provided.
	 */
	public static function absint_pro( $string, $min = null, $max = null ) : int {
		$number = absint( $string );
		if( $min !== null ) {
			$number = $number < $min ? $min : $number;
		}
		if( $max !== null ) {
			$number = $number > $max ? $max : $number;
		}
		return $number;
	}

	/**
	 * Converts a given value to a specified data type.
	 *
	 * @param mixed  $value The value to be converted.
	 * @param string $type  The target type. Supported types:
	 *                      - 'numeric', 'float': Converts to float.
	 *                      - 'string': Converts to string.
	 *                      - 'strupper', 'strtoupper', 'stringupper', 'stringtoupper': Converts to uppercase string.
	 *                      - 'strlower', 'strtolower', 'stringlower', 'stringtolower': Converts to lowercase string.
	 *                      - 'int', 'integer': Converts to integer.
	 *                      - 'absint', 'absinteger': Converts to absolute integer.
	 *                      - 'bool', 'boolean': Converts to boolean using self::to_bool().
	 *                      - 'array', '[]': Converts to an array, converting objects via self::obj_to_array().
	 *                      - 'obj', 'object', '{}': Converts to an object, converting arrays via self::array_to_obj().
	 *
	 * @return mixed The converted value based on the specified type.
	 */
	public static function check_var_type( $value, string $type ) {
		if( $type == 'numeric' || $type == 'float' ) {
			$value = floatval( $value );
		} else if( $type == 'string' ) {
			$value = "{$value}";
		} else if( $type == 'strupper' || $type == 'strtoupper' || $type == 'stringupper' || $type == 'stringtoupper' ) {
			$value = strtoupper( "{$value}" );
		} else if( $type == 'strlower' || $type == 'strtolower' || $type == 'stringlower' || $type == 'stringtolower' ) {
			$value = strtolower( "{$value}" );
		} else if( $type == 'int' || $type == 'integer' ) {
			$value = intval( $value );
		} else if( $type == 'absint' || $type == 'absinteger' ) {
			$value = absint( $value );
		} else if( $type == 'bool' || $type == 'boolean' ) {
			$value = self::to_bool( $value );
		} else if( $type == 'array' || $type == '[]' ) {
			if( is_object( $value ) ) {
				$value = self::obj_to_array( $value, true );
			} else if( !is_array( $value ) ) {
				$value = [$value];
			}
		} else if( $type == 'obj' || $type == 'object' || $type == '{}' ) {
			if( !is_object( $value ) ) {
				if( !is_array( $value ) ) {
					$value = [$value];
				}
				$value = self::array_to_obj( $value, true );
			}
		}
		return $value;
	}

	/**
	 * Converts the values of an associative array to specified data types.
	 *
	 * @param array $array The input array with values to be converted.
	 * @param array $types An associative array where keys correspond to input array keys,
	 *                     and values specify the target data type.
	 *                     Supported types are the same as those in check_var_type().
	 *
	 * @return array The array with converted values based on the specified types.
	 */
	public static function check_array_types( array $array, array $types ) {
		foreach( $types as $key => $type ) {
			if( isset( $array[$key] ) ) {
				$array[$key] = self::check_var_type( $array[$key], $type );
			}
		}
		return $array;
	}

	public static function remove_prefix_from_array_keys( array $array, string $prefix ) {
		foreach( $array as $key => $value ) {
			if( strpos( $key, $prefix ) === 0 ) {
				$array[substr( $key, strlen( $prefix ) )] = $value;
				unset( $array[$key] );
			}
		}
		return $array;
	}

	public static function get_max_upload_size() {
		static $size = null;
		if( $size === null ) {
			$size = wp_max_upload_size();
		}
		return $size;
	}

	public static function convert_bytes_to_mb( int $bytes, bool $add_suffix = true, int $decimal_places = 0 ) {
		if( $bytes < 0) {
			return '';
		}
	
		$mb = $bytes / MB_IN_BYTES; // 1 MB = 1024 * 1024 bytes
	
		if( $add_suffix ) {
			return number_format( $mb, $decimal_places ) . ' ' . _x( 'MB', 'unit symbol' );
		} else {
			return $mb;
		}
	}

	public static function convert_mb_to_bytes( int $megabytes ) : int {
		if( $megabytes < 0 ) {
			return 0;
		}
	
		return $megabytes * MB_IN_BYTES; // 1 MB = 1024 * 1024 bytes
	}

	public static function drplus_vars_localize() {
		$options = Options::get_options( [
			'booking-datepicker'			=> 'persian'
		] );
		$vars = [
			'ajaxUrl'				=> admin_url( 'admin-ajax.php' ),
			'restApiBaseUrl'		=> esc_url_raw( rest_url( 'drplus_chat/v1/' ) ),
			'isRtl'					=> is_rtl(),
			'isIranTimezone'		=> Utils::is_iran_timezone(),
			'PersianDatePicker'		=> $options['booking-datepicker'] === 'persian',
			'i18n'					=> [
				'today'		=> __( "Today", 'drplus' ),
				'submit'	=> _x( "Submit", 'Date picker', 'drplus' ),
				'dropzone'	=> [
					'dictDefaultMessage'	=> __( "Drop files here or click to upload", 'drplus' ),
				],
				'wrongIDCode'	=> __( 'Please enter a valid National ID', 'drplus' ),
				'wrongMobile'	=> __( 'Please enter a valid mobile', 'drplus' ),
			],
			'nonces'			=> [
				'dropzone'	=> wp_create_nonce( 'drplus-dropzone_upload_nonce' ),
			],
			'defaults'			=> [
				'avatar'	=> DRPLUS_URI . "assets/images/user.svg"
			],
		];

		wp_localize_script( 'drplus', 'drplusVars', $vars );
	}

	/**
	 * Get average score from post
	 *
	 * @param integer $post_id
	 * @param integer|boolean $decimals
	 * @param integer $comments_count You can pass the comments count. cache
	 * @return float
	 */
	public static function get_post_avg( $post_id = 0, $decimals = 1, int $comments_count = 0 ) : float {
		$post_id = self::get_post_id( $post_id );
		if( !$post_id ) return 0;

		$local_cache_key = "{$post_id}-{$decimals}";
		if( empty( self::$posts_avg_scores[$local_cache_key] ) ) {
			$total_scores = absint( get_post_meta( $post_id, '_drplus_total_scores', true ) );
			if( $comments_count === 0 ) {
				$comments_count = get_comment_count( $post_id )['approved'];
			}

			if( empty( $comments_count ) ) {
				return 0;
			}
			$result = $total_scores/$comments_count;
			if( $decimals !== false ) {
				$result = round( $result, $decimals );
			}
			self::$posts_avg_scores[$local_cache_key] = $result;
		}

		return self::$posts_avg_scores[$local_cache_key];
	}

	public static function get_archive_post_type() {
		global $wp_query;
		$q = $wp_query->query;
		$post_type = is_archive() ? 'post' : '';
		if( is_archive() ) {
			$post_type = 'post';
		} else if( is_page() ) {
			$is_blog = Page::get_options()['is_blog'];
			if( $is_blog ) {
				$post_type = 'post';
				return $post_type;
			}
		}
		if( !empty( $q['post_type'] ) ) {
			$post_type = Utils::convert_chars( $q['post_type'] );
		} else {
			$q = get_queried_object();
			if( is_a( $q, 'WP_Term' ) ) {
				if( !empty( $q->taxonomy ) ) {
					if( in_array( $q->taxonomy, ['video-cat', 'video-tag'] ) ) {
						$post_type = 'video';
					} else if( in_array( $q->taxonomy, ['portfolio-cat', 'portfolio-tag'] ) ) {
						$post_type = 'portfolio';
					} else if( in_array( $q->taxonomy, ['product_cat', 'product_tag'] ) ) {
						$post_type = 'product';
					}
				}
			}
		}
		return $post_type;
	}

	public static function apply_general_variables( string $text, array $custom_variables = [] ) {
		$text = stripslashes( $text );
		$text = str_replace( "{name}", get_bloginfo( 'blogname' ), $text );
		$text = str_replace( "{domain}", str_replace( ['http://', 'https://'], '', get_bloginfo( 'url' ) ), $text );
		foreach( $custom_variables as $variable => $value ) {
			$text = str_replace( "{" . $variable . "}", $value, $text );
		}
		return $text;
	}

	/**
	 * check if timezone set to Asia/Tehran
	 */
	public static function is_iran_timezone() {
		static $timezone_string = null;
		if( $timezone_string === null ) {
			$timezone_string = wp_timezone_string();
		}
		return $timezone_string == 'Asia/Tehran' || $timezone_string == '+03:30';
	}

	/**
	 * Create placeholder for use in DB query
	 *
	 * @param array|object $object
	 * @param string $type Accepts: %d, %s, %f
	 * @return string
	 */
	public static function db_placeholder( $object, $type ) {
		$object = self::obj_to_array( $object );
		return implode( ", ", array_fill( 0, count( $object ), $type ) );
	}

	/**
	 * Parses and processes content from a WordPress text editor.
	 *
	 * Applies standard WordPress content filters such as shortcodes,
	 * texturization, and auto-embeds to return the final formatted content.
	 *
	 * @param string $content The raw content from the editor.
	 * 
	 * @return string The processed and formatted content.
	 */
	public static function parse_text_editor( $content ) {
		$content = shortcode_unautop( $content );
		$content = do_shortcode( $content );
		$content = wptexturize( $content );

		if ( $GLOBALS['wp_embed'] instanceof \WP_Embed ) {
			$content = $GLOBALS['wp_embed']->autoembed( $content );
		}

		return $content;
	}

	/**
	 * Merge two associative arrays with support for one-level deep array merging.
	 *
	 * If a key exists in both arrays and both values are arrays,
	 * their values will be merged using array_merge.
	 * Otherwise, the value from the second array will overwrite the first.
	 *
	 * This function is useful for merging configuration-like arrays
	 * where certain keys (e.g. "section") contain nested data.
	 *
	 * @param array $base   Base array (primary structure)
	 * @param array $extra  Extra array whose values will be merged into base
	 *
	 * @return array        The merged result array
	 */
	public static function merge_sectioned_array( array $base, array $extra ) : array {
		foreach ( $extra as $key => $value ) {
			if ( isset($base[$key]) && is_array($base[$key]) && is_array($value) ) {
				$base[$key] = array_merge($base[$key], $value);
			} else {
				$base[$key] = $value;
			}
		}
		return $base;
	}

	public static function normalize_map_src( string $input ): string {
		$input = trim( $input );

		if ( stripos( $input, '<iframe' ) !== false ) {
			return self::extract_iframe_src_dom( $input );
		}

		if ( filter_var( $input, FILTER_VALIDATE_URL ) ) {
			return esc_url_raw( $input );
		}

		return '';
	}

	public static function extract_iframe_src_dom( string $input ): string {
		if ( stripos( $input, '<iframe' ) === false ) {
			return '';
		}

		libxml_use_internal_errors( true );

		$dom = new \DOMDocument();
		$dom->loadHTML( $input, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );

		$iframes = $dom->getElementsByTagName( 'iframe' );

		if ( $iframes->length === 0 ) {
			return '';
		}

		$src = $iframes->item( 0 )->getAttribute( 'src' );

		return $src ? esc_url_raw( $src ) : '';
	}
}