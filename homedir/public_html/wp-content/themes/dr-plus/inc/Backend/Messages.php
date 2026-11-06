<?php
namespace DrPlus\Backend;

class Messages {
	public static function server_messages() {
		wp_enqueue_style( 'drplus-notices', DRPLUS_URI . "assets/css/backend/notices.min.css", [], DRPLUS_VERSION );
		if( DRPLUS_DEV ) {
			wp_enqueue_script( 'drplus-notices', DRPLUS_URI . "assets/js/backend/notices.js", ['jquery'], DRPLUS_VERSION, true );
		} else {
			wp_enqueue_script( 'drplus-notices', DRPLUS_URI . "assets/js/backend/notices.min.js", ['jquery'], DRPLUS_VERSION, true );
		}
		wp_localize_script( 'drplus-notices', 'drplus_notices', [
			'ajaxUrl'	=> admin_url( 'admin-ajax.php' ),
			'nonce'		=> wp_create_nonce( 'drplus_dismiss_notice' ),
		] );
		?>
		<div id="drplus-notices" class="notice" style="display:none"></div>
		<?php
	}
}
add_action( 'admin_notices', [Messages::class, 'server_messages'] );