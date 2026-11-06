<?php
namespace DrPlus\Utils;

use DrPlus\Utils;

class Cache extends Utils {
	public static function sections() {
		return [
			'specialist',
		];
	}

	public static function sync_all_sections() {
		foreach( self::sections() as $section ) {
			$section = Utils::convert_to_pascal_case( $section );
			$class = "DrPlus\Cache\\" . $section . "Cache";
			$cache = new $class();
			$cache->sync_all();
		}
	}

	public static function delete_expired_caches() {
		foreach( self::sections() as $section ) {
			$section = Utils::convert_to_pascal_case( $section );
			$class = "DrPlus\Cache\\" . $section . "Cache";
			$cache = new $class();
			$cache->delete_by( [
				'expired'	=> true,
			] );
		}
	}
}