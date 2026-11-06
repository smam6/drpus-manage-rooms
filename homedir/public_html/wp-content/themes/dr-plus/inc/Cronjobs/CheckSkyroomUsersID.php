<?php
// بررسی اینکه کاربرانی که آیدی اسکای روم دارن، آیا این آیدی توی اسکای روم هست یا نه و اگه نیست مجدد ساخته بشه.

use DrPlus\Utils\Skyroom;

if ( !wp_next_scheduled( 'drplus_check_skyroom_users_id' ) ) {
    wp_schedule_event( time(), 'daily', 'drplus_check_skyroom_users_id' );
}

add_action( 'drplus_check_skyroom_users_id', 'drplus_check_skyroom_users_id_exec' );
function drplus_check_skyroom_users_id_exec() {
	$skyroom_users = Skyroom::get_skyroom_users();
	if( is_wp_error( $skyroom_users ) ) return;

	$page = 1;
	do {
		$wp_users = get_users( [
			'meta_key'		=> 'skyroom_id',
			'meta_value'	=> '',
			'meta_compare'	=> '!=',
			'count_total'	=> false,
			'fields'		=> 'ID',
			'number'		=> 200,
			'paged'			=> $page,
		] );

		foreach( $wp_users as $wp_user ) {
			try {
				$skyroom_id = Skyroom::get_user_skyroom_id_from_meta( $wp_user );
				if( !isset( $skyroom_users[$skyroom_id] ) ) {
					Skyroom::create_skyroom_user( $wp_user, [
						'blocking'	=> false,
					] );
				}
			} catch ( \Exception $e ) {
				continue;
			}
		}

		$page++;
	} while ( count( $wp_users ) === 200 );
}