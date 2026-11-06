<?php
namespace DrPlus\Casts;

use DrPlus\Utils;
use DrPlus\Utils\Sanitizers;
use MJ\WPORM\Casts\CastableInterface;

class Mobile implements CastableInterface {
	public function get( $value ) {
		return $value;
	}

	public function set( $value ) {
		return Utils::convert_chars( $value );
	}
}