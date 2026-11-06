<?php

use DrPlus\Utils;

$show_save_button = false;
$show_back_button = false;
$show_form = false;

global $wp;
$chat_id = 0;
$page_query = isset( $wp->query_vars['specialist-dashboard'] ) ? explode( '/', Utils::convert_chars( $wp->query_vars['specialist-dashboard'], true ) ) : [];
if( count( $page_query ) == 2 && $page_query[0] == 'specialist-chats' ) {
	$chat_id = intval( $page_query[1] );
}

get_template_part( "templates/chats/template-chats", $chat_id ? 'single' : 'list', [
	'view_type'		=> 'specialist',
	'chat_id'		=> $chat_id,
] );
return;