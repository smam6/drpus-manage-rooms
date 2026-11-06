<?php

use DrPlus\Utils;

global $wp;
$chat_id = isset( $wp->query_vars['chats'] ) ? Utils::convert_chars( $wp->query_vars['chats'], true, 'absint' ) : false;

get_template_part( "templates/chats/template-chats", $chat_id ? 'single' : 'list', [
	'view_type'		=> 'customer',
	'chat_id'		=> $chat_id
] );
return;