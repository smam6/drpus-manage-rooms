<?php
namespace MJ\Whitebox\Utils;

use MJ\Whitebox\Utils;

class Shortcode extends Utils {
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
	 * Splits specific string values in the given attributes array into arrays
	 * based on a defined separator, and trims whitespace from each element.
	 *
	 * @param array  $atts            The attributes array containing key-value pairs.
	 * @param array  $separated_keys  The list of keys whose values should be split into arrays.
	 * @param string $separator       Optional. The separator string used to split values. Default '&&'.
	 * 
	 * @return array The modified attributes array with specified keys converted into arrays.
	 */
	public static function separated_to_array( array $atts, array $separated_keys, string $separator = '&&' ) {
		foreach( $separated_keys as $key ) {
			if( !isset( $atts[$key] ) ) {
				$atts[$key] = [];
			} else {
				$atts[$key] = array_map( fn( $value ) => trim( $value ), explode( $separator, $atts[$key] ) );
			}
		}
		return $atts;
	}
}