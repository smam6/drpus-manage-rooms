<?php
namespace MJ\Whitebox\Utils;

use MJ\Whitebox\Utils;

class API extends Utils {
	/**
	 * Convert all keys of array to lowercase recursively
	 *
	 * @param array $data
	 * @return array
	 */
	public static function to_lower_before_response( array $data ) : array {
		if( !is_array( $data ) ) return $data;

		$data = array_change_key_case( $data, CASE_LOWER );
		foreach( $data as $key => $value ) {
			if( is_array( $value ) ) {
				$data[$key] = self::to_lower_before_response( $data[$key] );
			}
		}

		return $data;
	}

	public static function convert_id_to_attachment_array( $id ) {
		return [
			'id'	=> absint( $id ),
			'url'	=> wp_get_attachment_url( $id )
		];
	}

	public static function convert_id_to_attachment_url( $id ) {
		return wp_get_attachment_url( $id );
	}

	public static function convert_array_ids_to_attachment_array( $ids ) {
		$ids = array_values( array_filter( $ids ) );
		return array_map( fn( $id ) => static::convert_id_to_attachment_array( $id ) , $ids );
	}

	public static function convert_array_ids_to_attachment_url( $ids ) {
		$ids = array_values( array_filter( $ids ) );
		return array_map( 'wp_get_attachment_url', $ids );
	}
}