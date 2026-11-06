<?php
// Remove expired caches

use DrPlus\Utils\Cache;

if ( !wp_next_scheduled( 'drplus_remove_cache_hook' ) ) {
    wp_schedule_event( time(), 'drplus_five_minutes', 'drplus_remove_cache_hook' );
}

add_action( 'drplus_remove_cache_hook', 'drplus_remove_cache_exec' );
function drplus_remove_cache_exec() {
	Cache::delete_expired_caches();
}