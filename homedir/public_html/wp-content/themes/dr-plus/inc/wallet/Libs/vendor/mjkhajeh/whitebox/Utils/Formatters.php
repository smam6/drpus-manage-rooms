<?php
namespace MJ\Whitebox\Utils;

use MJ\Whitebox\Utils;

class Formatters extends Utils {
	private static function spliter( string $string, array $blocks ) {
		$last_offset = 0;
		$string_array = [];
		foreach( $blocks as $block ) {
			$string_array[] = substr( $string, $last_offset, $block );
			$last_offset += $block;
		}
		return implode( " ", $string_array );
	}

	/**
	 * Get formatted price
	 *
	 * @param string|number $price
	 * @param boolean $wc Use WooCommerce price format
	 * @param string $suffix Insert suffix for the price. It works when $wc is false. It doesn't insert space after the price
	 * @param null|int $decimals Number of decimal places
	 * @return string Price
	 */
	public static function price( $price, $wc = false, $suffix = '', $decimals = null ) {
		$price = floatval( $price );
		if( $wc && function_exists( 'wc_price' ) ) {
			$price = wc_price( $price );
		} else {
			if( $decimals === null ) {
				$price = parent::number_decimal_format( $price );
			} else {
				$price = number_format_i18n( $price, $decimals );
			}
			$price .= $suffix;
		}

		return $price;
	}

	/**
	 * Format string as phone number by Iran format
	 *
	 * @param string $string The string with raw phone number
	 * @param boolean $reverse Reverse the phone parts.
	 * @return string Formatted phone number
	 */
	public static function phone( string $string, bool $reverse = false ) : string {
		$string = Sanitizers::phone( $string );
		$phone = self::spliter( $string, [4,3,4] );

		if( $reverse ) {
			$phone = implode( " ", array_reverse( explode( " ", $phone ) ) );
		}

		return $phone;
	}

	/**
	 * Format a credit card number in groups of 4 digits.
	 *
	 * Removes spaces and splits the number into 4-digit groups.
	 * Optionally reverses the order of the groups.
	 *
	 * @param string $string The input card number.
	 * @param bool $reverse Optional. Reverse the order of 4-digit groups. Default false.
	 *
	 * @return string Formatted card number.
	 */
	public static function card_number( string $string, bool $reverse = false ) : string {
		if( !$string ) return $string;
		$string = str_replace( " ", "", $string );
		$card_number = self::spliter( $string, [4,4,4,4] );

		if( $reverse ) {
			$card_number = implode( " ", array_reverse( explode( " ", $card_number ) ) );
		}

		return $card_number;
	}

	/**
	 * Format an IBAN (Shaba) number into standard groups.
	 *
	 * Removes spaces, splits into groups, optionally reverses the groups,
	 * and prepends the "IR" country code.
	 *
	 * @param string $string The input Shaba/IBAN number.
	 * @param bool $reverse Optional. Reverse the order of the groups. Default false.
	 *
	 * @return string Formatted Shaba number.
	 */
	public static function shaba_number( string $string, bool $reverse = false ) : string {
		if( !$string ) return $string;
		$string = str_replace( " ", "", $string );
		$shaba = self::spliter( $string, [2,4,4,4,4,4,2] );

		if( $reverse ) {
			$shaba = implode( " ", array_reverse( explode( " ", $shaba ) ) );
		}

		$shaba = "IR{$shaba}";

		return $shaba;
	}
}