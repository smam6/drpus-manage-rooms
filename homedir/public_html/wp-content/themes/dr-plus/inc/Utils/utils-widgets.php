<?php
namespace DrPlus\Utils;

use DrPlus\Utils;

class Widgets extends Utils {
	public static function apply_hospital_variables( string $text ) : string {
		if( empty( $text ) ) return $text;

		if( strpos( $text, '{name}' ) !== false ) {
			$text = str_replace( '{name}', get_the_title(), $text );
		}
		if( strpos( $text, '{province}' ) !== false ) {
			$hospital_settings = Hospital::get_options( get_the_ID() );
			$text = str_replace( '{province}', $hospital_settings['province'], $text );
		}
		if( strpos( $text, '{city}' ) !== false ) {
			$hospital_settings = Hospital::get_options( get_the_ID() );
			$text = str_replace( '{city}', $hospital_settings['city'], $text );
		}
		return $text;
	}
}