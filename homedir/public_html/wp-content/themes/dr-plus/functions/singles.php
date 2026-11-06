<?php

use DrPlus\Utils\UI;

if( !function_exists( "drplus_singles_add_map_popup" ) ) {
	function drplus_singles_add_map_popup() {
		if( !is_singular( ['specialist', 'hospital'] ) ) return;

		UI::map_popup();
	}
}
add_action( 'wp_footer', 'drplus_singles_add_map_popup' );