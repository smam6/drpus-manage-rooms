<?php
namespace DrPlus\Utils;

use DrPlus\Utils;

class Auth extends Utils {
	public static function is_login() {
		static $is_login = null;
		if( $is_login === null ) {
			$is_login = defined( 'DRPLUS_LOGIN' ) && DRPLUS_LOGIN;
		}
		return $is_login;
	}
}