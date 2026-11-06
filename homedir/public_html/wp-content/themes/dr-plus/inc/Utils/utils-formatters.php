<?php
namespace DrPlus\Utils;

use DrPlus\Utils;

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
	 * Format string as phone number by Iran format
	 *
	 * @param string $string The string with raw phone number
	 * @param boolean $reverse Reverse the phone sections.
	 * @return string Formatted phone number
	 */
	public static function phone( string $string, bool $reverse = false ) : string {
		$string = str_replace( " ", "", $string );
		$phone = self::spliter( $string, [4,3,4] );

		if( $reverse ) {
			$phone = implode( " ", array_reverse( explode( " ", $phone ) ) );
		}

		return $phone;
	}

	public static function card_number( string $string, bool $reverse = false ) : string {
		if( !$string ) return $string;
		$string = str_replace( " ", "", $string );
		$card_number = self::spliter( $string, [4,4,4,4] );

		if( $reverse ) {
			$card_number = implode( " ", array_reverse( explode( " ", $card_number ) ) );
		}

		return $card_number;
	}

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

	/**
	 * Get formatted price
	 *
	 * @param string|number $price
	 * @param boolean $wc Use WooCommerce price format
	 * @param string $suffix Insert suffix for the price. It works when $wc is false. It doesn't insert space after the price
	 * 
	 * @return string Price
	 */
	public static function price( $price, $wc = false, $suffix = '' ) {
		$price = floatval( $price );

		if( $wc && function_exists( 'wc_price' ) ) {
			$price = wc_price( $price );
		} else {
			$price = parent::number_decimal_format( $price );
			$price .= $suffix;
		}

		return $price;
	}
}