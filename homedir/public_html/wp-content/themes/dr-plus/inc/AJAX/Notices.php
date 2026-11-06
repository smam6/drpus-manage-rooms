<?php
namespace DrPlus\AJAX;

use DrPlus\AJAX;
use DrPlus\Utils;

class Notices extends AJAX {
	public static function get_instance() {
		static $instance = null;
		if( $instance === null ) {
			$instance = new self;
		}
		return $instance;
	}

	public function __construct() {
		return $this;
	}

	public function get() {
		// Get messages from server
		$url = "https://mjkhajeh.ir/api/mj/messages";
		$url = add_query_arg( "product", 'drplus', $url );
		$url = add_query_arg( "version", DRPLUS_VERSION, $url );
		$url = add_query_arg( "include_publics", true, $url );

		$user_dismissed_messages = self::get_dismissed_messages();
		if( !empty( $user_dismissed_messages ) ) {
			$url = add_query_arg( 'excludes', implode( ",", $user_dismissed_messages ), $url );
		}
		$messages = wp_remote_get( $url );
		if( !is_wp_error( $messages ) ) {
			$messages = json_decode( wp_remote_retrieve_body( $messages ) );
			if( !empty( $messages ) ) {
				foreach( $messages as $message ) {
					?>
					<div class="drplus_notice notice notice-<?php echo sanitize_html_class( $message->type ) ?><?php echo $message->dismissible ? ' is-dismissible' : '' ?>" data-id="<?php echo esc_attr( $message->id ) ?>">
						<?php echo wpautop( $message->message ) ?>
						<?php if( $message->dismissible ) { ?>
							<button type="button" class="notice-dismiss"><span class="screen-reader-text">رد کردن این اخطار</span></button>
						<?php } ?>
					</div>
					<?php
				}
			}
		}
		die;
	}

	public function dismiss() {
		$this->set_request_data();
		$id = Utils::convert_chars( $this->data['id'], true, 'absint' );
		$user_id = get_current_user_id();

		$ids = self::get_dismissed_messages( $user_id );
		if( !in_array( $id, $ids ) ) {
			$ids[] = $id;
		}
		update_user_meta( $user_id, 'dismissed_messages', $ids );
		die;
	}

	private static function get_dismissed_messages( $user_id = 0 ) {
		$user_id = Utils::convert_chars( $user_id, true, 'absint' );
		if( $user_id === 0 && is_user_logged_in() ) {
			$user_id = get_current_user_id();
		}

		$ids = get_user_meta( $user_id, 'dismissed_messages', true );
		if( empty( $ids ) || !is_array( $ids ) ) {
			$ids = [];
		}
		return $ids;
	}
}