<?php

use DrPlus\Utils\Cache;

if ( !wp_next_scheduled( 'drplus_sync_cache_hook' ) ) {
    wp_schedule_event( time(), 'drplus_five_minutes', 'drplus_sync_cache_hook' );
}

add_action( 'drplus_sync_cache_hook', 'drplus_sync_cache_exec' );
function drplus_sync_cache_exec() {
	Cache::sync_all_sections();
}