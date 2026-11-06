<?php
namespace DrPlus\Backend\Metaboxes\Product;

use DrPlus\Utils;
use DrPlus\Utils\Product;

class Subtitle {
	PRIVATE STATIC $PREFIX = 'drplus_product_subtitle_';
	PRIVATE STATIC $POST_TYPES = ['product'];

	public static function add() {
		add_meta_box(
			self::$PREFIX . "subtitle",	// id
			__( 'Subtitle', 'drplus' ),	// title
			[__CLASS__, 'view'],		// callback
			self::$POST_TYPES,			// screens
			'advanced',					// context
			'high'						// priority
		);
	}

	public static function view( $post ) {
		wp_nonce_field( self::$PREFIX . "nonce_value", self::$PREFIX . "nonce" );
		?>
		<p class="description"><?php esc_html_e( 'This text will show under product name', 'drplus' ) ?></p>
		<input type="text" 	class="large-text" name="<?php echo self::$PREFIX ?>field" id="<?php echo self::$PREFIX ?>field" value="<?php echo esc_attr( Product::get_subtitle( $post->ID ) ) ?>">
		<?php
	}

	public static function save( $post_id, $post ) {
		if( !in_array( $post->post_type, self::$POST_TYPES ) || empty( $_POST ) ) return;
		
		// Check nonce value
		if( !isset( $_POST[self::$PREFIX . "nonce"] ) ) return;
				
		// Check nonce
		$nonce = Utils::convert_chars( $_POST[self::$PREFIX . "nonce"] );
		if( !wp_verify_nonce( $nonce, self::$PREFIX . "nonce_value" ) ) return;
		
		$subtitle = Utils::convert_chars( $_POST[self::$PREFIX . "field"] );
		Product::save_subtitle( $post_id, $subtitle );
	}
}
add_action( 'add_meta_boxes', [Subtitle::class, 'add'] );
add_action( 'save_post', [Subtitle::class, 'save'], 10, 2 );