<?php
namespace DrPlus\Utils;

use DrPlus\Utils;

class Sanitizers extends Utils {
	public static function icon( $icon, $icon_element_class = '' ) {
		if( is_array( $icon ) ) {
			if( !empty( $icon['url'] ) ) {
				$icon = sanitize_url( $icon['url'] );
			} else {
				if( is_array( $icon['value'] ) && !empty( $icon['value']['url'] ) ) {
					$icon = sanitize_url( $icon['value']['url'] );
				} else {
					$icon = Utils::convert_chars( $icon['value'] );
				}
			}
		} else {
			$icon = parent::convert_chars( $icon );
		}
		
		$icon_classes = '';
		$icon_url = '';
		if( !empty( $icon ) ) {
			if( filter_var( $icon, FILTER_VALIDATE_URL ) ) {
				$icon_url = esc_url( $icon, ['http', 'https'] );
			} else { // Icon class
				$icon_classes = explode( " ", Utils::convert_chars( $icon ) );
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
	 * Sanitize price
	 *
	 * @param string|float|int|double $price
	 * @return integer|float price
	 */
	public static function price( $price ) {
		$price = parent::convert_chars( $price );
		if( !is_numeric( $price ) ) {
			$price = preg_replace( "/[^0-9.]/", "", $price );
		}

		return absint( $price ) == $price ? absint( $price ) : floatval( $price );
	}

	public static function tag( $string ) : string {
		return parent::ensure_values_in_array( parent::convert_chars( $string ), array_keys( parent::custom_tags() ), 'div' );
	}

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

	public static function shaba_number( string $shaba ) {
		$shaba = parent::convert_chars( $shaba );
		$shaba = str_replace( " ", "", $shaba );
		preg_match('/\d{24}$/', $shaba, $shaba);
		return $shaba[0] ?? '';
	}

	public static function phone( string $phone ) {
		$phone = parent::convert_chars( $phone );
		$phone = str_replace( '+98', '0', $phone );
		return str_replace( ' ', '', $phone );
	}
}