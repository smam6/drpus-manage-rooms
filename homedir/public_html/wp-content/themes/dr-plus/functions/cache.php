<?php
namespace DrPlus\Cache;

class Init {
	public static function includes() {
		include( DRPLUS_DIR . "inc/Models/Abstracts/CacheModel.php" );
		
		include( DRPLUS_DIR . "inc/Models/SpecialistCache.php" );
		
		include( DRPLUS_DIR . "inc/Utils/utils-cache.php" );
		
		include( DRPLUS_DIR . "inc/Cache/Cache.php" );
		include( DRPLUS_DIR . "inc/Cache/Specialist.php" );

		include( DRPLUS_DIR . "inc/Cronjobs/SyncCache.php" );
		include( DRPLUS_DIR . "inc/Cronjobs/RemoveCache.php" );
	}

	public static function enqueue() {
		if( DRPLUS_DEV ) {
			wp_enqueue_script( 'drplus-cache', DRPLUS_URI . "assets/js/cache.js", ['jquery'], DRPLUS_VERSION, true );
		} else {
			wp_enqueue_script( 'drplus-cache', DRPLUS_URI . "assets/js/cache.min.js", ['jquery'], DRPLUS_VERSION, true );
		}
	}
}
add_action( 'init', [Init::class, 'includes'], 1 );
add_action( 'wp_enqueue_scripts', [Init::class, 'enqueue'], 100 );
add_action( 'admin_enqueue_scripts', [Init::class, 'enqueue'], 100 );