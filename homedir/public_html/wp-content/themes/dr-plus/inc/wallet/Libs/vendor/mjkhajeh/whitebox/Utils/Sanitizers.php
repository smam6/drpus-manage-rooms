<?php
namespace MJ\Whitebox\Utils;

use MJ\Whitebox\Utils;

class Sanitizers extends Utils {
	/**
	 * Normalize and validate Iranian phone numbers.
	 *
	 * Returns:
	 * - normalized number (string) if valid
	 * - empty string "" if invalid
	 *
	 * @param string $phone
	 * @return string
	 */
	public static function phone( string $phone ) {
		$phone = parent::convert_chars( $phone );
		$phone = str_replace( '+98', '0', $phone );
		return str_replace( ' ', '', $phone );
	}


	/**
	 * Sanitize OTP
	 *
	 * @param string $string
	 * @param integer $length
	 * @return integer
	 */
	public static function otp( $string, $length = 4 ) {
		$string = parent::convert_chars( $string );
		preg_match_all( '/\d+/', $string, $matches );
		$string = absint( implode( "", $matches[0] ) );
		$string = substr( $string, 0, $length );
		return $string;
	}

	/**
	 * Sanitize and normalize a price value.
	 *
	 * Converts characters, removes non-numeric characters, and returns
	 * the price as an integer if possible, otherwise as a float.
	 *
	 * @param string|int|float $price The price value to process.
	 * @param bool $empty_to_zero Optional. If false, empty strings remain empty. Default true.
	 *
	 * @return int|float|string Sanitized price as int or float, or empty string if applicable.
	 */
	public static function price( $price, $empty_to_zero = true ) {
		if( !$empty_to_zero && $price === '' ) return '';

		$price = parent::convert_chars( $price );
		if( !is_numeric( $price ) ) {
			$price = preg_replace( "/[^0-9.]/", "", $price );
		}

		return absint( $price ) == $price ? absint( $price ) : floatval( $price );
	}

	/**
	 * Sanitize IP
	 *
	 * @param string $string
	 * @return string IP
	 */
	public static function ip( $string ) {
		$string = parent::convert_chars( $string );
		return filter_var( $string, FILTER_VALIDATE_IP ) ? $string : '';
	}

	/**
	 * Ensure a string is a valid HTML tag from custom tags.
	 *
	 * Converts characters and validates against allowed custom tags.
	 * Defaults to 'div' if the value is not allowed.
	 *
	 * @param string $string The input string to validate as a tag.
	 *
	 * @return string Validated HTML tag.
	 */
	public static function tag( $string ) : string {
		return parent::ensure_values_in_array( parent::convert_chars( $string ), array_keys( parent::custom_tags() ), 'div' );
	}

	/**
	 * Format and validate a credit card number.
	 *
	 * Removes spaces and ensures the number is exactly 16 characters.
	 * Returns an empty string if the length is less than 16.
	 *
	 * @param string $string The input card number.
	 *
	 * @return string Formatted 16-digit card number or empty string.
	 */
	public static function card_number( string $string ) : string {
		$string = parent::convert_chars( $string );
		$string = str_replace( " ", "", $string );
		if( strlen( $string ) > 16 ) {
			$string = substr( $string, 0, 16 );
		} else if( strlen( $string ) < 16 ) {
			$string = "";
		}
		return $string;
	}

	/**
	 * Format and validate an IBAN (Shaba) number.
	 *
	 * Removes spaces, converts characters, and extracts the last 24 digits.
	 * Returns an empty string if no valid number is found.
	 *
	 * @param string $shaba The input Shaba/IBAN number.
	 *
	 * @return string Formatted 24-digit Shaba number or empty string.
	 */
	public static function shaba_number( string $shaba ) {
		$shaba = parent::convert_chars( $shaba );
		$shaba = str_replace( " ", "", $shaba );
		preg_match('/\d{24}$/', $shaba, $shaba);
		return $shaba[0] ?? '';
	}
}