<?php
namespace DrPlus\Metaboxes\Backend\Notification;

use DrPlus\AdminScripts;
use DrPlus\Model\NotificationsUserRel;
use DrPlus\PublicScripts;
use DrPlus\Utils;
use DrPlus\Utils\Notifications;

if( !defined( 'ABSPATH' ) ) exit;

class Settings {
	PRIVATE STATIC $PREFIX = "drplus_notification_";
	PRIVATE STATIC $POST_TYPES = ['notification'];

	public static function enqueue( $hook ) {
		if( !in_array( $hook, ['post-new.php', 'post.php'] ) || !in_array( get_post_type(), self::$POST_TYPES ) ) return;
		
		PublicScripts::select2();
		AdminScripts::metabox( ['get_user'] );

		if( DRPLUS_DEV ) {
			wp_enqueue_script( 'drplus-notification-metabox', DRPLUS_URI . "assets/js/backend/metaboxes/notification.js", ['jquery'], DRPLUS_VERSION, true );
		} else {
			wp_enqueue_script( 'drplus-notification-metabox', DRPLUS_URI . "assets/js/backend/metaboxes/notification.min.js", ['jquery'], DRPLUS_VERSION, true );
		}
	}

	public static function add() {
		add_meta_box(
			self::$PREFIX . "message",		// id
			__( 'Message', 'drplus' ),		// title
			[__CLASS__, 'message_view'],	// callback
			self::$POST_TYPES				// screens
		);
		add_meta_box(
			self::$PREFIX . "settings",		// id
			__( 'Users', 'drplus' ),		// title
			[__CLASS__, 'settings_view'],	// callback
			self::$POST_TYPES				// screens
		);
	}

	public static function message_view( $post ) {
		wp_nonce_field( self::$PREFIX . "save_notification", self::$PREFIX . "nonce" );

		$notification = Notifications::get( $post );
		?>
		<div class="drplus_metabox">
			<table class="form-table">
				<tr>
					<th>
						<label for="<?php echo self::$PREFIX ?>message"><?php esc_html_e( 'Message', 'drplus' ) ?></label>
					</th>

					<td>
						<textarea name="<?php echo self::$PREFIX ?>message" id="<?php echo self::$PREFIX ?>message" style="width: 100%" rows="10"><?php echo esc_textarea( get_the_content() ) ?></textarea>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	public static function settings_view( $post ) {
		$notification = Notifications::get( $post );
		$recipients_types = Notifications::recipients_types();
		?>
		<div class="drplus_metabox">
			<table class="form-table">
				<tr>
					<th>
						<label for="<?php echo self::$PREFIX ?>recipients"><?php esc_html_e( 'Recipients', 'drplus' ) ?></label>
					</th>

					<td>
						<label>
							<select name="<?php echo self::$PREFIX ?>recipients" id="<?php echo self::$PREFIX ?>recipients">
								<?php foreach( $recipients_types as $key => $value ) { ?>
									<option value="<?php echo esc_attr( $key ) ?>"<?php selected( $notification['recipients'], $key ) ?>><?php echo esc_html( $value ) ?></option>
								<?php } ?>
							</select>
						</label>
					</td>
				</tr>

				<tr id="<?php echo self::$PREFIX ?>select_user"<?php echo in_array( $notification['recipients'], ['all_users', 'all_specialists'] ) ? ' style="//display:none"' : '' ?>>
					<th>
						<label for="<?php echo self::$PREFIX ?>users"><?php esc_html_e( 'Users', 'drplus' ) ?></label>
					</th>

					<td>
						<select name="<?php echo self::$PREFIX ?>users[]" id="<?php echo self::$PREFIX ?>users" multiple>
							<?php foreach( $notification['users'] as $user ) { ?>
								<option value="<?php echo esc_attr( $user->ID ) ?>" selected><?php echo esc_html( $user->display_name ) ?></option>
							<?php } ?>
						</select>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	public static function save( $post_id, $post ) {
		if( !in_array( $post->post_type, self::$POST_TYPES ) || empty( $_POST ) ) return;

		// Check nonce value
		if( empty( $_POST[self::$PREFIX . "nonce"] ) ) return;
				
		// Check nonce
		$nonce = Utils::convert_chars( $_POST[self::$PREFIX . "nonce"] );
		if( !wp_verify_nonce( $nonce, self::$PREFIX . "save_notification" ) ) return;

		$settings = [];
		$settings['message'] = Utils::convert_chars( $_POST[self::$PREFIX . "message"], true, 'sanitize_textarea_field' );
		$settings['recipients'] = Utils::convert_chars( $_POST[self::$PREFIX . "recipients"] ?? '' );
		$settings['recipients'] = Utils::ensure_values_in_array( $settings['recipients'], array_keys( Notifications::recipients_types() ), 'all_users' );
		Notifications::save_options( $settings, $post_id );

		if( in_array( $settings['recipients'], ['custom_users', 'custom_specialists'] ) ) {
			$old_data = Notifications::get( $post, false );
			if( !empty( $_POST[self::$PREFIX . "users"] ) ) {
				foreach( $_POST[self::$PREFIX . "users"] as $user_id ) {
					if( !in_array( $user_id, $old_data['users'] ) ) { // New
						$notif_rel = new NotificationsUserRel;
						$notif_rel->notif_id = $post_id;
						$notif_rel->user_id = $user_id;
						$notif_rel->type = $settings['recipients'] == 'custom_users' ? 'users' : 'specialists';
						$notif_rel->save();
					} else {
						// unset
						$key = array_search( $user_id, $old_data['users'] );
						unset( $old_data['users'][$key] );
					}
				}
			}
			if( !empty( $old_data['users'] ) ) {
				NotificationsUserRel::query()->whereIn( 'user_id', $old_data['users'] )->where( 'notif_id', $post_id )->delete();
			}
		} else { // Now it's not custom. Then delete notification rel
			NotificationsUserRel::query()->where( 'notif_id', $post_id )->delete();
		}
	}
}
add_action( 'admin_enqueue_scripts', [Settings::class, 'enqueue'] );
add_action( 'add_meta_boxes', [Settings::class, 'add'] );
add_action( 'save_post', [Settings::class, 'save'], 10, 2 );