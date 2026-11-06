<?php
namespace MJ\Whitebox;

use stdClass;

class Utils {
	protected static string $project_dir = ''; // Used in get_icon_packs
	protected static string $project_uri = ''; // Used in get_icon_packs
	protected static string $project_slug = ''; // Used in get_icon_packs
	protected static array $taxonomies = [ // taxonomy_name => post_type
		'product_cat'	=> 'product',
		'product_tag'	=> 'product',
	];
	protected static bool $DEV_MODE = false;
	protected static string $version = '';

	/**
	 * Checks and applies default values to an array based on provided defaults and skip indexes. Also, check the type of the value based on defaults.
	 *
	 * This function iterates through the given array of defaults and applies them to the input array,
	 * Consider skip indexes to preserve certain values. It performs type checks and conversions
	 * for mismatched types and handles nested arrays recursively.
	 *
	 * @param array $value The input array to which default values need to be applied.
	 * @param array $defaults An associative array containing default values to be applied.
	 * @param array $skips An array of indexes to be skipped when applying default values.
	 * @param boolean $fill_empty Check if the key is set but it's empty and fills with default value
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
		if( is_wp_error( $value ) || is_null( $value ) ) return false;
		$value = strtolower( $value );
		if( in_array( $value, ["false", 'no', 'off', '0'] ) ) return false;
		if( in_array( $value, ["true", 'yes', 'on', '1'] ) ) return true;

		return wp_validate_boolean( $value );
	}

	/**
	 * Checks if the given value meets specified requirements.
	 *
	 * This function verifies whether the provided value fulfills the requirements specified
	 * in the $requires array or object. It can optionally perform checks for the emptiness of
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
	 * Remove empty indexes from an array
	 *
	 * @param array $array
	 * @param boolean $all_empty_values By default the function checks for empty string. But with this arg, it can check all types of empty
	 * @param boolean $remove_nulls Remove null values
	 *
	 * @return array
	 */
	public static function remove_empty_indexes( $array, $all_empty_values = false, $remove_nulls = false ) {
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
	 * Unset indexes if they exist from an array or object
	 *
	 * @param array|object $data The main data
	 * @param array $removes An array of things you want to remove. Each index can be a string(Name of the index of the $data) OR array. If it was an array it would find the key in the $data and search for childs to remove
	 * @param array $skips List of skips of remove list
	 *
	 * @return array|object $data with removed things
	 */
	public static function unset( $data, $removes, $skips = [] ) {
		foreach( $removes as $key => $remove ) {
			if( in_array( $remove, $skips ) ) continue;

			if( is_array( $remove ) ) {
				if( isset( $data[$key] ) && is_array( $data[$key] ) ) {
					$data[$key] = self::unset( $data[$key], $remove );
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

		return $data;
	}

	/**
	 * Extract selected keys from an array or object
	 *
	 * @param array|object $data
	 * @param array $keys List of keys you want to extract
	 * @return mixed Array of extracted keys from $data
	 */
	public static function extract( $data, $keys ) {
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
	 * Convert array to object
	 *
	 * @param array $array
	 * @param boolean $force
	 *
	 * @return object stdClass
	 */
	public static function array_to_obj( $array, $force = false ) {
		return is_array( $array ) || $force ? json_decode( json_encode( $array ) ) : $array;
	}

	/**
	 * Flattens a multi-dimensional array into a single-dimensional array.
	 *
	 * This function recursively iterates through the input array and flattens it by merging sub-arrays or non-array elements into a single array.
	 *
	 * @param array $items The input array is to be flattened.
	 * @return array The flattened array containing all non-array elements.
	 */
	public static function array_flatten( $items ) {
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
	 * Convert and sanitize string
	 *
	 * @param string $string
	 * @param string|array|boolean|callback $sanitize Write functions you want to sanitize the string with them. Separate each function with '&&' for multiple functions. Or write a callback. Bool mode will exec sanitize_text_field
	 * @param string|array|boolean|callback $name Like sanitize param
	 * @param boolean $reverse Convert English chars to Persian
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

		if( is_string( $string ) ) {
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

	public static function is_base64( $string ) {
		if( !is_scalar( $string ) || empty( $string ) ) {
			return false;
		}
		// Check if it's a valid base64 encoded string
		if (base64_encode(base64_decode($string, true)) === $string) {
			// Also check if it only contains valid base64 characters
			return preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $string);
		}
		return false;
	}

	/**
	 * Ensures that values in the source array are allowed according to the specified set of allowed values.
	 *
	 * @param mixed $source The source data to be checked. It can be a scalar or an array of values.
	 * @param array $check An array containing the allowed values.
	 * @param mixed $default The default value to return if any source value is not allowed. The default is an empty string.
	 * 
	 * @return mixed Returns the source data if all values are allowed, or the default value if any value is not allowed.
	 * 
	 * @example It will check the 'personal'(source) exists in 'check' or not and if not, return the 'personal'(default)
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
	public static function convert_to_pascal_case( $input ) {
		$input = str_replace( ['-', '_'], ' ', $input );
		$words = explode( ' ', $input ); // Split input string into an array of words
		$capitalizedWords = array_map( 'ucwords', $words ); // Capitalize the first letter of each word
		$pascalCaseString = implode( '', $capitalizedWords ); // Combine the words back into a string
		return str_replace( ' ', '', $pascalCaseString ); // Remove spaces
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
	 * Displays errors by adjusting the PHP error reporting settings.
	 *
	 * If the debug mode is active (DEV_CONST is true) or if debug mode checking is disabled,
	 * This function sets the necessary PHP configurations to display errors.
	 *
	 * @param bool $check_debug_is_active Optional. Whether to check if debug mode is active before showing errors. [Default: true]
	 * @param array $users List of usernames to only show for these users
	 *
	 * @return void
	 */
	public static function show_errors( bool $check_debug_is_active = true, array $users = [] ) : void {
		if( !$check_debug_is_active || ( $check_debug_is_active && WP_DEBUG ) ) {
			if( !empty( $users ) && !in_array( wp_get_current_user()->user_login, $users ) ) return;
			ini_set('display_errors', 1);
			ini_set('display_startup_errors', 1);
			error_reporting(E_ALL);
		}
	}

	/**
	 * Convert the number to a string and add zero if it's lower than 10 or -10
	 *
	 * @param string $number
	 * @return string
	 */
	public static function add_zero( $number ) {
		if( is_numeric( $number ) && $number < 10 ) {
			$number = doubleval( $number );
			if( $number >= 0 ) {
				$number = "0{$number}";
			} else {
				$number = "-0{$number}";
			}
		}
		return $number;
	}

	/**
	 * Adds leading zeros to a time string.
	 *
	 * @param string $length The time string in the format "H:M:S".
	 * @return string The time string with leading zeros added.
	 */
	public static function time_leading_zero( $length ) {
		return implode( ":", array_map( fn( $number ) => self::add_zero( $number ), explode( ":", $length ) ) );
	}

	/**
	 * Convert an array of HTML classes to a string
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
	
		foreach( $attributes as $key => $value ) {
			// Convert boolean values to "true" or "false"
			if( is_bool( $value ) ) {
				$result .= esc_attr( $key ) . '="' . ( $value ? 'true' : 'false') . '" ';
			} else {
				if( $key == 'class' ) {
					if( !is_array( $value ) ) {
						$value = explode( " ", $value );
					}
					if( is_array( $value ) ) {
						$value = array_unique( self::array_flatten( $value ) );
						$value = implode( " ", array_filter( array_map( 'sanitize_html_class', $value ) ) );
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
	public static function minify_hex( $string, $add_hashtag = true ) {
		if( empty( $string ) ) {
			return '';
		}
		if( $string == 'transparent' ) {
			return $string;
		}

		$string = strtolower( $string );
		$has_hashtag = str_starts_with( $string, '#' );
		$hex = $has_hashtag ? substr( $string, 1 ) : $string;

		// The hex code shouldn't convert to 3 chars. It will cause the wrong code for variables with opacity. #f63 ---opacity:10---> #f631a X(wrong);
		return $add_hashtag || $has_hashtag ? "#{$hex}" : $hex;
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
	 *                      - 'numeric', 'double': Converts to double.
	 *                      - 'float': Converts to float.
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
		if( $type == 'numeric' || $type == 'double' ) {
			if( !is_scalar( $value ) || !is_numeric( $value ) ) {
				$value = 0;
			} else {
				$value = doubleval( $value );
			}
		} else if( $type == 'float' ) {
			if( !is_scalar( $value ) || !is_numeric( $value ) ) {
				$value = 0;
			} else {
				$value = floatval( $value );
			}
		} else if( $type == 'string' ) {
			if( !is_scalar( $value ) ) {
				$value = '';
			} else {
				$value = "{$value}";
			}
		} else if( $type == 'strupper' || $type == 'strtoupper' || $type == 'stringupper' || $type == 'stringtoupper' ) {
			if( !is_scalar( $value ) ) {
				$value = '';
			} else {
				$value = strtoupper( "{$value}" );
			}
		} else if( $type == 'strlower' || $type == 'strtolower' || $type == 'stringlower' || $type == 'stringtolower' ) {
			if( !is_scalar( $value ) ) {
				$value = '';
			} else {
				$value = strtolower( "{$value}" );
			}
		} else if( $type == 'int' || $type == 'integer' ) {
			if( !is_scalar( $value ) || !is_numeric( $value ) ) {
				$value = 0;
			} else {
				$value = intval( $value );
			}
		} else if( $type == 'absint' || $type == 'absinteger' ) {
			if( !is_scalar( $value ) || !is_numeric( $value ) ) {
				$value = 0;
			} else {
				$value = absint( $value );
			}
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

	/**
	 * Formats a number to a decimal format with the appropriate number of decimal places.
	 *
	 * @param float $value The number to format.
	 * @return string The formatted number.
	 */
	public static function number_decimal_format( $value ) {
		$decimals = explode( ".", $value );
		$decimals = !empty( $decimals[1] ) ? strlen( $decimals[1] ) : 0;
		return number_format_i18n( $value, $decimals );
	}

	/**
	 * Convert seconds integer to human readable time
	 *
	 * @param int $time seconds
	 * @return string
	 */
	public static function human_time( $time ) {
		$time = absint( $time );

		$hours = floor( $time / 3600 );
		$minutes = floor( ( $time % 3600 ) / 60 );
		$seconds = $time % 60;

		$text = '';
		if( $hours > 0 ) {
			$text .= sprintf( _n( '%s hour', '%s hours', $hours, 'mj-whitebox' ), $hours );
		}
		if( $minutes > 0 ) {
			$text .= $text ? ' ' : '';
			$text .= sprintf( _n( '%s minute', '%s minutes', $minutes, 'mj-whitebox' ), $minutes );
		}
		if( $seconds > 0 ) {
			$text .= $text ? ' ' : '';
			$text .= sprintf( _n( '%s second', '%s seconds', $seconds, 'mj-whitebox' ), $seconds );
		}
		
		return $text;
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
	public static function second_to_string( $seconds ) : string {
		if( $seconds === null || $seconds === "" || $seconds === false || $seconds < 0 ) return "";

		$hours = intdiv($seconds, 3600);
		$minutes = intdiv($seconds % 3600, 60);
		$remainingSeconds = $seconds % 60;

		if ($hours > 0) {
			return sprintf("%02d:%02d:%02d", $hours, $minutes, $remainingSeconds);
		}

		return sprintf("%02d:%02d", $minutes, $remainingSeconds);
	}

	/**
	* Converts a time string into seconds.
	*
	* Accepts time strings in `MM:SS` or `HH:MM:SS` format and converts them
	* to the total number of seconds. The input string is first normalized using
	* {@see self::convert_chars()} before validation.
	*
	* If the string is not in a valid format, contains empty parts, or contains
	* non-numeric values, the function returns `0`.
	 *
	 * Examples:
	 *
	 * - 12:30 returns 750
	 * - 01:02:03 returns 3723
	 * - abc, 10, 10:, or 10:20:30:40 return 0
	 *
	 * @param string $string Time string in `MM:SS` or `HH:MM:SS` format.
	 *
	 * @return int Total number of seconds, or `0` on invalid input.
	 */
	public static function string_to_second( string $string ) : int {
		$string = self::convert_chars( $string );

		$parts = explode( ':', $string );

		if( count( $parts ) < 2 || count( $parts ) > 3 ) {
			return 0;
		}

		foreach( $parts as $part ) {
			if ( '' === $part || !ctype_digit( $part ) ) {
				return 0;
			}
		}

		if ( count( $parts ) === 1 ) {
			return absint( $parts[0] );
		}

		if ( count( $parts ) === 2 ) {
			$minutes = absint( $parts[0] );
			$seconds = absint( $parts[1] );

			return ( $minutes * 60 ) + $seconds;
		}

		$hours   = absint( $parts[0] );
		$minutes = absint( $parts[1] );
		$seconds = absint( $parts[2] );

		return ( $hours * 3600 ) + ( $minutes * 60 ) + $seconds;
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
	 * check if timezone set to Asia/Tehran
	 * 
	 * @return void
	 */
	public static function is_iran_timezone() {
		static $timezone_string = null;
		if( $timezone_string === null ) {
			$timezone_string = wp_timezone_string();
		}
		return $timezone_string == 'Asia/Tehran' || $timezone_string == '+03:30';
	}

	/**
	 * Remove a prefix from the keys of an array.
	 *
	 * This method iterates through the array and removes the specified prefix from each key.
	 * If a key starts with the prefix, it is removed, and the key is updated accordingly.
	 *
	 * @param array $array The input array with keys to be modified.
	 * @param string $prefix The prefix to be removed from the keys.
	 *
	 * @return array The modified array with keys having the specified prefix removed.
	 */
	public static function remove_prefix_from_array_keys( array $array, string $prefix ) {
		foreach( $array as $key => $value ) {
			if( strpos( $key, $prefix ) === 0 ) {
				$array[substr( $key, strlen( $prefix ) )] = $value;
				unset( $array[$key] );
			}
		}
		return $array;
	}

	/**
	 * Conditionally define a constant if it hasn't been defined yet.
	 *
	 * @param string $name The name of the constant.
	 * @param mixed $value The value of the constant.
	 * @return void
	 */
	public static function maybe_define( $name, $value ) {
		if( !defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Calculate the discount percentage between regular and sale prices.
	 *
	 * @param float $regular_price The regular price.
	 * @param float $sale_price The sale price.
	 * @return integer
	 */
	public static function calc_discount_percentage( $regular_price, $sale_price ) : int {
		if( !is_numeric( $regular_price ) || !is_numeric( $sale_price ) ) return 0;
		$percentage = absint( (100 - round( $sale_price / $regular_price * 100 )) );
		if( $percentage === 100 && !empty( $sale_price ) ) {
			$percentage = 99;
		}
		if( $percentage < 1 ) {
			$percentage = 1;
		}
		return $percentage;
	}

	/**
	 * Outputs hidden form inputs for each query string variable.
	 *
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

	/**
	 * Check whether Redux framework is active.
	 *
	 * This method validates if the Redux framework plugin is currently active and installed.
	 * It uses WordPress core's `is_plugin_active()` function and additionally checks
	 * if the plugin file actually exists in the plugins directory.
	 *
	 * The result is cached statically for subsequent calls to improve performance.
	 *
	 *
	 * @return bool True if Redux framework is active and installed, false otherwise.
	 */
	public static function is_redux_active() {
		static $is = null;
		if( $is === null ) {
			if( class_exists( 'Redux' ) ) {
				$is = true;
			} else {
				$plugin = 'redux-framework/redux-framework.php';
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				$is = is_plugin_active( $plugin ) && is_file( trailingslashit( WP_PLUGIN_DIR ) . $plugin );
			}
		}
		return $is;
	}

	/**
	 * Check whether WooCommerce is active.
	 *
	 * This method validates if the WooCommerce plugin is currently active and installed.
	 * It uses WordPress core's `is_plugin_active()` function and additionally checks
	 * if the plugin file actually exists in the plugins directory.
	 *
	 * The result is cached statically for subsequent calls to improve performance.
	 *
	 *
	 * @return bool True if WooCommerce is active and installed, false otherwise.
	 */
	public static function is_wc_active() {
		static $is = null;
		if( $is === null ) {
			if( class_exists( 'WooCommerce' ) ) {
				$is = true;
			} else {
				$plugin = 'woocommerce/woocommerce.php';
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				$is = is_plugin_active( $plugin ) && is_file( trailingslashit( WP_PLUGIN_DIR ) . $plugin );
			}
		}
		return $is;
	}

	/**
	 * Check whether Elementor is active and installed.
	 *
	 * Uses WordPress functions to verify the plugin is active and the main plugin file exists.
	 * Caches the result statically for performance.
	 *
	 * @return bool True if Elementor is active and installed, false otherwise.
	 */
	public static function is_elementor_active() {
		static $is = null;
		if( $is === null ) {
			if( defined( 'ELEMENTOR__FILE__' ) ) {
				$is = true;
			} else {
				$plugin = 'elementor/elementor.php';
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				$is = is_plugin_active( $plugin ) && is_file( trailingslashit( WP_PLUGIN_DIR ) . $plugin );
			}
		}
		return $is;
	}

	/**
	 * Check whether Elementor Pro is active and installed.
	 *
	 * Uses WordPress functions to verify the plugin is active and the main plugin file exists.
	 * Caches the result statically for performance.
	 *
	 * @return bool True if Elementor Pro is active and installed, false otherwise.
	 */
	public static function is_elementor_pro_active() {
		static $is = null;
		if( $is === null ) {
			if( class_exists( 'ElementorPro\Plugin' ) ) {
				$is = true;
			} else {
				$plugin = 'elementor-pro/elementor-pro.php';
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				$is = is_plugin_active( $plugin ) && is_file( trailingslashit( WP_PLUGIN_DIR ) . $plugin );
			}
		}
		return $is;
	}

	/**
	 * Retrieve a list of custom HTML tag options.
	 *
	 * This method returns an associative array of allowed/custom HTML tags
	 * that can be used within the plugin. Each key represents the HTML tag,
	 * and the value is a localized, human-readable label.
	 *
	 * Developers can modify or extend this list using the
	 * `mj\whitebox\utils\custom_tags` filter hook.
	 *
	 * Example return value:
	 * [
	 *     'h1'  => "H1",
	 *     'h2'  => "H2",
	 *     'div' => "div",
	 *     'p'   => "p",
	 * ]
	 *
	 *
	 * @return array[string] Associative array of tag slugs (keys) and their labels (values).
	 */
	public static function custom_tags() {
		/**
		 * Filter: Modify the list of custom HTML tag options.
		 *
		 * This filter allows developers to add, remove, or change the available
		 * HTML tags returned by `custom_tags()`. The array keys should be valid
		 * HTML tag names, and the values are human-readable labels.
		 *
		 * Usage example:
		 *
		 * add_filter( 'mj\whitebox\utils\custom_tags', function( $tags ) {
		 *     $tags['span'] = __( 'span', 'mj-whitebox' );
		 *     unset( $tags['h6'] );
		 *     return $tags;
		 * });
		 *
		 * @param array[string] $tags Associative array of tag slugs (keys) and their labels (values).
		 *
		 * @return array[string] Modified list of HTML tag options.
		 */
		return apply_filters( 'mj\whitebox\utils\custom_tags', [
			'h1'	=> __( "H1", 'mj-whitebox' ),
			'h2'	=> __( "H2", 'mj-whitebox' ),
			'h3'	=> __( "H3", 'mj-whitebox' ),
			'h4'	=> __( "H4", 'mj-whitebox' ),
			'h5'	=> __( "H5", 'mj-whitebox' ),
			'h6'	=> __( "H6", 'mj-whitebox' ),
			'div'	=> __( "div", 'mj-whitebox' ),
			'p'		=> __( "p", 'mj-whitebox' ),
			'span'	=> __( "span", 'mj-whitebox' ),
		] );
	}

	/**
	 * Get the module name.
	 *
	 * Returns $index if $module is an array, otherwise returns $module.
	 *
	 * @param string|int $index  Array key when $module is an array.
	 * @param array|string $module Module definition.
	 *
	 * @return string The module name.
	 */
	public static function get_module_name( $index, $module ) {
		return is_array( $module ) ? $index : $module;
	}

	/**
	 * Determines whether a module should be included based on its requirements.
	 *
	 * Evaluates the provided index and requirement data to decide if a specific
	 * module should be loaded. For WooCommerce-related modules, it checks whether
	 * WooCommerce is active before allowing inclusion.
	 *
	 * @param string       $index The module index or identifier.
	 * @param array|string $data  Optional. Requirements for including the module.
	 *                            Can be a string, an array of requirements, or an
	 *                            associative array containing a 'requirements' key.
	 * 
	 * @return bool
	 */
	public static function should_include_module( $index, $data = [] ) : bool {
		if( is_string( $index ) ) {
			if( empty( $data ) || !is_array( $data ) ) return true;
			$requirements = [];
			if( is_string( $data ) ) {
				$requirements[] = $data;
			} else {
				if( empty( $data['requirements'] ) ) {
					$requirements = $data;
				} else {
					$requirements = $data['requirements'];
				}
			}

			// Check WC is active
			if( !empty( array_intersect( ['woocommerce', 'wc', 'WC', 'WooCommerce'], $requirements ) ) && !self::is_wc_active() ) {
				return false;
			}

			// Check Elementor is active
			if( !empty( array_intersect( ['elementor', 'Elementor'], $requirements ) ) && !self::is_elementor_active() ) {
				return false;
			}

			// Check Elementor Pro is active
			if( !empty( array_intersect( ['elementor-pro', 'elementor_pro', 'elementorpro', 'ElementorPro', 'Elementorpro', 'elementorPro'], $requirements ) ) && !self::is_elementor_active() ) {
				return false;
			}
		}
		return true;
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

	/**
	 * Get the post type for the current archive or queried object.
	 *
	 * Determines the post type based on the query context, page settings,
	 * or taxonomy terms. Supports custom post types like 'video', 'portfolio', and 'product'.
	 *
	 * @return string The resolved post type. Defaults to 'post' or empty string if unknown.
	 */
	public static function get_archive_post_type() {
		global $wp_query;
		$q = $wp_query->query;
		$post_type = is_archive() ? 'post' : '';
		if( is_archive() ) {
			$post_type = 'post';
		}
		if( !empty( $q['post_type'] ) ) {
			$post_type = self::convert_chars( $q['post_type'] );
		} else {
			$q = get_queried_object();
			if( is_a( $q, 'WP_Term' ) ) {
				if( !empty( $q->taxonomy ) ) {
					if( isset( static::$taxonomies[$q->taxonomy] ) ) {
						$post_type = static::$taxonomies[$q->taxonomy];
					}
				}
			}
		}
		return $post_type;
	}

	/**
	 * Generate an HTML element for an icon.
	 *
	 * Supports icon arrays, URL strings, or CSS class names. Returns an <i> element
	 * for icon classes or an <img> element for URLs, with optional additional classes.
	 *
	 * @param string|array $icon Icon value or array containing 'url' or 'value'.
	 * @param string $icon_element_class Optional. Additional CSS classes for the icon element.
	 *
	 * @return string HTML markup for the icon.
	 */
	public static function get_icon( $icon, $icon_element_class = '' ) {
		if( empty( $icon ) ) return '';
		
		if( is_array( $icon ) ) {
			if( !empty( $icon['url'] ) ) {
				$icon = sanitize_url( $icon['url'] );
			} else {
				if( is_array( $icon['value'] ) && !empty( $icon['value']['url'] ) ) {
					$icon = sanitize_url( $icon['value']['url'] );
				} else {
					$icon = self::convert_chars( $icon['value'] );
				}
			}
		} else {
			$icon = self::convert_chars( $icon );
		}
		
		$icon_classes = '';
		$icon_url = '';
		if( !empty( $icon ) ) {
			if( wp_http_validate_url( $icon ) !== false ) {
				$icon_url = esc_url( $icon, ['http', 'https'] );
			} else { // Icon class
				$icon_classes = explode( " ", self::convert_chars( $icon ) );
				$icon_classes = implode( " ", array_filter( array_map( fn( $value ) => sanitize_html_class( $value ), $icon_classes ) ) );
			}
		}

		$icon = '';
		if( !empty( $icon_classes ) ) {
			$icon_classes = esc_attr( $icon_classes );
			$icon = "<i class=\"{$icon_classes} {$icon_element_class}\" aria-hidden=\"true\"></i>";
		} else if( !empty( $icon_url ) ) {
			$icon = "<img src=\"{$icon_url}\" alt=\"\" class=\"{$icon_element_class}\">";
		}

		return $icon;
	}

	/**
	 * Replace general and custom variables in a text string.
	 *
	 * Replaces {name} with the site name, {domain} with the site domain,
	 * and any additional custom variables provided in the array.
	 *
	 * @param string $text The input text containing variables.
	 * @param array $custom_variables Optional. Associative array of custom variables to replace.
	 *
	 * @return string Text with variables replaced.
	 */
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
	 * Counts the number of decimal digits in a given number.
	 *
	 * Splits the number by the decimal point and returns the length
	 * of the fractional part, or 0 if no decimals exist.
	 *
	 * @param float|int|string $number The number to check. Can be integer, float, or numeric string.
	 * 
	 * @return int The count of digits after the decimal point.
	 */
	public static function count_decimals( $number ) {
		$number_sections = explode( ".", $number );
		return !empty( $number_sections[1] ) ? strlen( $number_sections[1] ) : 0;
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
		$content = trim( $content );
		$content = shortcode_unautop( $content );
		$content = do_shortcode( $content );

		if( isset( $GLOBALS['wp_embed'] ) && $GLOBALS['wp_embed'] instanceof \WP_Embed ) {
			$content = $GLOBALS['wp_embed']->run_shortcode( $content );
			$content = $GLOBALS['wp_embed']->autoembed( $content );
		}

		$content = wptexturize( $content );
		$content = convert_smilies( $content );
		$content = wp_filter_content_tags( $content );
		$content = force_balance_tags( $content );

		return $content;
	}

	public static function get_icon_packs() {
		static $packs = [];
		if( empty( $packs[static::$project_slug] ) ) {
			$theme_packs = wp_json_file_decode( static::$project_dir . "assets/icons.json", ['associative' => true] );
			$packs[static::$project_slug] = apply_filters( static::$project_slug . '/icon-picker/packs', $theme_packs );

			// Set svg icons url
			foreach( $packs[static::$project_slug] as $pack_name => $pack ) {
				if( $pack['mode'] == 'svg' ) {
					$pack['dir'] = trailingslashit( $pack['dir'] );
					foreach( $pack['icons'] as $icon_index => $icon ) {
						$svg_url = static::$project_uri . "assets/{$pack['dir']}{$icon}";
						if( !isset( $theme_packs[$pack_name] ) ) {
							$svg_url = "{$pack['dir']}{$icon}";
						}
						if( substr( $svg_url, -4 ) != '.svg' ) {
							$svg_url .= '.svg';
						}

						if( $pack['label_icon'] == str_replace( ".svg", "", $icon ) ) {
							$packs[static::$project_slug][$pack_name]['label_icon'] = $svg_url;
						}

						$packs[static::$project_slug][$pack_name]['icons'][$icon_index] = $svg_url;
					}
				}
			}
		}
		if( !isset( $packs[static::$project_slug] ) ) {
			$packs[static::$project_slug] = [];
		}

		return $packs[static::$project_slug];
	}

	/**
	 * Enqueue a script, automatically resolving the correct file variant
	 * (.js for development, .min.js for production) based on DEV_MODE.
	 *
	 *
	 * @param string       $handle    Unique name of the script.
	 * @param string       $src       Full URL of the script, or path relative to
	 *                                 the plugin/theme root. May omit the file extension.
	 * @param string[]     $deps      Optional. An array of registered script handles
	 *                                 this script depends on. Default empty array.
	 * @param string|bool  $ver       Optional. Script version. If empty, static::$version
	 *                                 is used. Default ''.
	 * @param bool         $in_footer Optional. Whether to enqueue the script before
	 *                                 </body> instead of in the <head>. Default true.
	 *
	 * @return void
	 */
	public static function enqueue_script( string $handle, string $src, array $deps = ['jquery'], $ver = '', bool $in_footer = true ) {
		if( $ver === '' ) {
			$ver = static::$version;
		}

		if( substr( $src, -7 ) == '.min.js' ) {
			$src = substr( $src, 0, strlen( $src )-7 );
		}
		if( substr( $src, -3 ) == '.js' ) {
			$src = substr( $src, 0, strlen( $src )-3 );
		}

		$dir = static::src_to_path( $src );

		if( static::$DEV_MODE && ( !defined( 'SCRIPT_DEBUG' ) || !SCRIPT_DEBUG ) ) {
			// Development mode: use the unminified version (.js).
			if( file_exists( $dir . '.js' ) ) {
				$src .= '.js';
			}
		} else {
			// Production mode: prefer the minified version (.min.js).
			if( file_exists( $dir . '.min.js' ) ) {
				$src .= '.min.js';
			} elseif( file_exists( $dir . '.js' ) ) {
				// Fall back to the unminified version if no minified file exists.
				$src .= '.js';
			}
		}
		wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
	}

	/**
	 * Convert a script $src into its physical filesystem path.
	 *
	 * Handles two cases:
	 *  - A full URL (starting with http://, https://, or //): mapped to a
	 *    physical path by replacing known WordPress URL bases with their
	 *    corresponding directory constants.
	 *  - A relative path: resolved against the plugin/theme root directory.
	 *
	 * The returned path does not include a file extension; callers are expected
	 * to append ".js" or ".min.js" as needed.
	 *
	 * @param string $src Full URL or relative path of the script (extension optional).
	 *
	 * @return string Absolute filesystem path corresponding to $src.
	 */
	protected static function src_to_path( string $src ): string {
		// Case 1: a full URL (with http(s):// or protocol-relative //).
		if( preg_match( '#^(https?:)?//#', $src ) ) {
			return str_replace(
				[ content_url(), plugins_url(), site_url() ],
				[ WP_CONTENT_DIR, WP_PLUGIN_DIR, ABSPATH ],
				$src
			);
		}

		// Case 2: a path relative to the plugin/theme root.
		return rtrim( plugin_dir_path( __FILE__ ), '/' ) . '/' . ltrim( $src, '/' );
	}
}