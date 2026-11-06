<?php

use DrPlus\Utils;
use DrPlusUtilsChat as Chat;

if( !function_exists( 'drplus_serve_chat_file' ) ) {
	function drplus_serve_chat_file() {
		if( isset( $_GET['chat_file'] ) ) {
			$file_url = Utils::convert_chars( $_GET['chat_file'] );
			$user_id = get_current_user_id();
			// Call your permission-checked file serving function
			Chat::serve_file($file_url, $user_id);
			exit;
		}
	}
}
add_action( 'init', 'drplus_serve_chat_file', 1 );