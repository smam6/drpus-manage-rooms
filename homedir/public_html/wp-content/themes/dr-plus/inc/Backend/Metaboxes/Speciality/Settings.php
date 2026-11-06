<?php
namespace DrPlus\Backend\Metaboxes\Speciality;

use DrPlus\AdminScripts;
use DrPlus\Utils;
use DrPlus\Utils\AdminUI;
use DrPlus\Utils\Speciality;

class Settings {
	PRIVATE STATIC $PREFIX = "drplus_speciality_";
	PRIVATE STATIC $POST_TYPES = ['speciality'];

	public static function enqueue( $hook ) {
		if( !in_array( $hook, ['post-new.php', 'post.php'] ) || !in_array( get_post_type(), self::$POST_TYPES ) ) return;

		AdminScripts::metabox( ['icon_picker'] );
		AdminScripts::modal();
		AdminScripts::icon_picker();
	}

	public static function add() {
		add_meta_box(
			self::$PREFIX,				// id
			__( 'Settings', 'drplus' ),	// title
			[__CLASS__, 'view'],		// callback
			self::$POST_TYPES			// screens
		);
	}

	public static function view( $post ) {
		wp_nonce_field( self::$PREFIX . "save_speciality", self::$PREFIX . "nonce" );

		$settings = Speciality::get_options( $post->ID );
		?>
		<table class="form-table">
			<tr>
				<th>
					<label for="<?php echo self::$PREFIX ?>subtitle"><?php esc_html_e( 'Subtitle', 'drplus' ) ?></label>
				</th>

				<td>
					<input type="text" name="<?php echo self::$PREFIX ?>subtitle" id="<?php echo self::$PREFIX ?>subtitle" class="regular-text" value="<?php echo $settings['subtitle'] ?>">
				</td>
			</tr>

			<tr>
				<th>
					<label for="<?php echo self::$PREFIX ?>icon"><?php esc_html_e( 'Icon', 'drplus' ) ?></label>
				</th>

				<td>
					<?php
					AdminUI::icon_picker( [
						'id'		=> self::$PREFIX . "icon",
						'name'		=> self::$PREFIX . "icon",
						'icon'		=> $settings['icon'],
						'modal_id'	=> 'drplus-icon-picker-modal',
					] );
					?>
				</td>
			</tr>
		</table>
		<?php
		AdminUI::modal( [
			'id'				=> "drplus-icon-picker-modal",
			'title'				=> esc_html__( "Select your icon", 'drplus' ),
			'classes'			=> ['icon-picker-modal'],
			'submit_btn_text'	=> esc_html__( "Select icon", 'drplus' ),
		] );
	}

	public static function save( $post_id, $post ) {
		if( !in_array( $post->post_type, self::$POST_TYPES ) || empty( $_POST ) ) return;

		// Check nonce value
		if( empty( $_POST[self::$PREFIX . "nonce"] ) ) return;
				
		// Check nonce
		$nonce = Utils::convert_chars( $_POST[self::$PREFIX . "nonce"] );
		if( !wp_verify_nonce( $nonce, self::$PREFIX . "save_speciality" ) ) return;
		
		$settings = [
			'subtitle'	=> Utils::convert_chars( $_POST[self::$PREFIX . "subtitle"] ),
			'icon'		=> Utils::convert_chars( $_POST[self::$PREFIX . "icon"] ),
		];
		Speciality::save_options( $settings, $post_id );
	}
}
add_action( 'admin_enqueue_scripts', [Settings::class, 'enqueue'] );
add_action( 'add_meta_boxes', [Settings::class, 'add'] );
add_action( 'save_post', [Settings::class, 'save'], 10, 2 );