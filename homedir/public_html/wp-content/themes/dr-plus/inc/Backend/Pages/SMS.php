<?php
namespace DrPlus\Backend\Pages;

use DrPlus\AdminScripts;
use DrPlus\PublicScripts;
use DrPlus\Utils;
use DrPlus\Utils\SMS as UtilsSMS;

class SMS {
	PRIVATE STATIC $PREFIX = 'drplus_sms_';

	public static function enqueue() {
		if( empty( $_GET['page'] ) || $_GET['page'] != 'drplus-sms' ) return;

		wp_enqueue_style( 'drplus-icons', DRPLUS_URI . "assets/css/iconly.min.css", [], DRPLUS_VERSION );
		PublicScripts::select2();
		AdminScripts::switch();
		wp_enqueue_script( 'wp-util' );
		
		wp_enqueue_style( 'drplus-sms', DRPLUS_URI . "assets/css/backend/pages/sms.min.css", [], DRPLUS_VERSION );
		if( DRPLUS_DEV ) {
			wp_enqueue_script( 'drplus-sms', DRPLUS_URI . "assets/js/backend/sms.js", ['jquery'], DRPLUS_VERSION, true );
		} else {
			wp_enqueue_script( 'drplus-sms', DRPLUS_URI . "assets/js/backend/sms.min.js", ['jquery'], DRPLUS_VERSION, true );
		}
		wp_localize_script( 'drplus-sms', 'drplusSMS', [
			'gateways'	=> UtilsSMS::gateways(),
		] );
	}

	public static function menu() {
		add_submenu_page(
			'drplus', 						// $parent_slug:string,
			__( 'SMS settings', 'drplus' ),	// $page_title:string,
			__( 'SMS settings', 'drplus' ),	// $menu_title:string,
			'manage_options',				// $capability:string,
			'drplus-sms',					// $menu_slug:string,
			[__CLASS__, 'view'],			// $callback:callable,
			9999							// $position:integer|float|null
		);
	}

	public static function view() {
		$sidebar_items = [
			'gateway'	=> [
				'label'		=> _x( 'Gateway', 'SMS', 'drplus' ),
				'icon'		=> 'mobile',
			],
			'settings'	=> [
				'label'	=> _x( 'Messages settings', 'SMS', 'drplus' ),
				'icon'	=> 'message-text',
				'subitems'	=> [
					'auth'			=> [
						'label'	=> _x( 'Authentication', 'SMS', 'drplus' ),
						'icon'	=> 'user',
					],
					'reserve'	=> [
						'label'	=> _x( 'Reserve messages', 'SMS', 'drplus' ),
						'icon'	=> 'messages-2',
					],
					'specialist'	=> [
						'label'	=> _x( 'Specialist Panel', 'SMS', 'drplus' ),
						'icon'	=> 'stethoscope',
					],
				],
			],
			'security'	=> [
				'label'	=> _x( 'Security settings', 'SMS', 'drplus' ),
				'icon'	=> 'shield-tick',
			],
		];

		$gateways = UtilsSMS::gateways();

		$settings = UtilsSMS::get_settings();
		?>
		<div class="wrap">
			<?php echo settings_errors( 'drplus-sms-settings' ); ?>
			<h1 class="page-title"><?php echo esc_html( get_admin_page_title() ) ?></h1>
			<p class="description"><?php esc_html_e( "In this section, you can configure Doctor plus's SMS settings.", 'drplus' ) ?></p>
			<hr>

			<form method="post" action="" id="<?php echo self::$PREFIX ?>form">
				<?php wp_nonce_field( self::$PREFIX . "save", self::$PREFIX . "nonce" ) ?>
				<div id="<?php echo self::$PREFIX ?>sidebar">
					<?php
					foreach( $sidebar_items as $id => $item ) {
						$item_classes = [self::$PREFIX . 'sidebar-item'];
						if( $id == 'gateway' ) {
							$item_classes[] = 'active';
						}
						if( !empty( $item['subitems'] ) ) {
							$item_classes[] = 'has-subitems';
						}
						?>
						<div class="<?php echo Utils::prepare_html_classes( $item_classes ) ?>" data-tab="<?php echo $id ?>">
							<div class="<?php echo self::$PREFIX ?>sidebar-item-inner">
								<i class="<?php echo self::$PREFIX ?>sidebar-item-icon drplus-icon-<?php echo $item['icon'] ?>"></i>
								<span class="<?php echo self::$PREFIX ?>sidebar-item-label"><?php echo esc_html( $item['label'] ) ?></span>
							</div>
							<?php if( !empty( $item['subitems'] ) ) { ?>
								<div class="<?php echo self::$PREFIX ?>sidebar-sub-items">
									<?php
									foreach( $item['subitems'] as $subitem_id => $subitem ) {
										$subitem_classes = [self::$PREFIX . 'sidebar-sub-item', self::$PREFIX . 'sidebar-item'];
										if( array_keys( $item['subitems'] )[0] == $subitem_id ) {
											$subitem_classes[] = 'active';
										}
										?>
										<div class="<?php echo Utils::prepare_html_classes( $subitem_classes ) ?>" data-section="<?php echo $subitem_id ?>">
											<div class="<?php echo self::$PREFIX ?>sidebar-item-inner">
												<i class="<?php echo self::$PREFIX ?>sidebar-item-icon drplus-icon-<?php echo $subitem['icon'] ?>"></i>
												<span class="<?php echo self::$PREFIX ?>sidebar-item-label"><?php echo esc_html( $subitem['label'] ) ?></span>
											</div>
										</div>
									<?php } ?>
								</div>
							<?php } ?>
						</div>
					<?php } ?>
				</div>

				<div id="<?php echo self::$PREFIX ?>body">
					<?php include( DRPLUS_DIR . "inc/Backend/Pages/SMS/Gateways.php" ); ?>
					<?php include( DRPLUS_DIR . "inc/Backend/Pages/SMS/Settings.php" ); ?>
					<?php include( DRPLUS_DIR . "inc/Backend/Pages/SMS/Security.php" ); ?>

					<button type="submit" id="<?php echo self::$PREFIX ?>submit"><?php esc_html_e( 'Save settings', 'drplus' ) ?></button>
				</div>
			</form>
		</div>
		<?php
	}

	public static function save() {
		if( empty( $_POST ) || empty( $_POST[self::$PREFIX . "nonce"] ) ) return;

		$nonce = Utils::convert_chars( $_POST[self::$PREFIX . 'nonce'] );
		if( empty( $nonce ) || !wp_verify_nonce( $nonce, self::$PREFIX . "save" ) ) return;

		UtilsSMS::save_settings( $_POST );
	}
}
add_action( 'admin_enqueue_scripts', [SMS::class, 'enqueue'] );
add_action( 'admin_menu', [SMS::class, 'menu'], 11 );
add_action( 'admin_init', [SMS::class, 'save'] );