<?php
namespace Sheyda\Wallet\Backend;

use SheydaWalletUtils as WalletUtils;
use Sheyda\Wallet\AdminScripts;
use Sheyda\Wallet\Utils\AdminUI;

class Settings {
	public static $PREFIX = 'sheyda_wallet_settings_';

	public static function get_active_tab() {
		$sidebar_items = self::sidebar_items();
		return WalletUtils::ensure_values_in_array( WalletUtils::convert_chars( $_GET['tab'] ?? "" ), array_keys( $sidebar_items ), array_key_first( $sidebar_items ) );
	}

	public static function sidebar_items() {
		$sections = [
			'general'	=> [
				'label'			=> __( 'General', 'sheyda_wallet' ),
				'icon'			=> 'sheyda-wallet-icon-setting',
				'class'			=> GeneralSettings::class,
			],
			'withdrawal'	=> [
				'label'			=> __( 'Withdrawal', 'sheyda_wallet' ),
				'icon'			=> 'sheyda-wallet-icon-setting',
				'class'			=> WithdrawalSettings::class,
			],
			'payment'	=> [
				'label'			=> __( 'Payment', 'sheyda_wallet' ),
				'icon'			=> 'sheyda-wallet-icon-setting',
				'class'			=> PaymentSettings::class,
			],
			'topup'	=> [
				'label'			=> __( 'Top-up', 'sheyda_wallet' ),
				'icon'			=> 'sheyda-wallet-icon-setting',
				'class'			=> TopUpSettings::class,
			],
			'style'	=> [
				'label'			=> __( 'Style', 'sheyda_wallet' ),
				'icon'			=> 'sheyda-wallet-icon-setting',
				'class'			=> StyleSettings::class,
			]
		];
		return apply_filters( 'sheyda/wallet/settings/sections', $sections );
	}

	public static function view() {
		$sidebar_items = self::sidebar_items();
		$active_item = self::get_active_tab();

		?>
		<div class="wrap">
			<div id="sheyda-wallet-header">
				<h1 class="wp-heading-inline"><?php esc_html_e( 'Wallet settings', 'sheyda_wallet' ) ?></h1>
				<hr class="wp-header-end">
			</div>
			<?php echo settings_errors( 'sheyda-wallet-settings' ); ?>

			<div id="<?php echo self::$PREFIX ?>container">
				<?php wp_nonce_field( self::$PREFIX . "save", self::$PREFIX . "nonce" ) ?>
				<div id="<?php echo self::$PREFIX ?>sidebar">
					<?php
					$page_link = add_query_arg( ['page' => 'sheyda-wallet', 'section' => 'settings' ], admin_url( 'admin.php' ) );
					foreach( $sidebar_items as $id => $item ) {
						$item_classes = [self::$PREFIX . 'sidebar-item'];
						if( $id == $active_item ) {
							$item_classes[] = 'active';
						}
						?>
						<div class="<?php echo WalletUtils::prepare_html_classes( $item_classes ) ?>" data-tab="<?php echo $id ?>">
							<a href="<?php echo add_query_arg( ['tab' => $id], $page_link ) ?>" class="<?php echo self::$PREFIX ?>sidebar-item-inner">
								<i class="<?php echo self::$PREFIX ?>sidebar-item-icon <?php echo $item['icon'] ?>"></i>
								<span class="<?php echo self::$PREFIX ?>sidebar-item-label"><?php echo esc_html( $item['label'] ) ?></span>
							</a>
						</div>
					<?php } ?>
				</div>

				<div id="<?php echo self::$PREFIX ?>body">
					<?php
					if( WalletUtils::is_wc_active() ) {
						do_action( "sheyda/wallet/settings/start_{$active_item}_view", ['prefix' => self::$PREFIX] );
						if( !empty( $sidebar_items[$active_item]['class'] ) && file_exists( SHEYDA_WALLET_DIR . "Backend/Pages/Settings/class-settings-{$active_item}.php" ) ) {						
							include_once( SHEYDA_WALLET_DIR . "Backend/Pages/Settings/class-settings-{$active_item}.php" );
							$sidebar_items[$active_item]['class']::view();
						}
						do_action( "sheyda/wallet/settings/end_{$active_item}_view", ['prefix' => self::$PREFIX] );
					} else {
						AdminUI::alert( [
							'text'	=> esc_html__( 'Woocommerce is required for wallet. please install and enable woocommerce to continue', 'sheyda_wallet' ),
							'type'	=> 'warning'
						] );
					}
					?>
				</div>
			</div>
		</div>
		<?php
	}

	public static function create_nonce() {
		wp_nonce_field( self::$PREFIX . 'save', self::$PREFIX . 'nonce' );
	}

	public static function save() {
		if( empty( $_POST ) || empty( $_POST[self::$PREFIX . "nonce"] ) ) return;

		$nonce = WalletUtils::convert_chars( $_POST[self::$PREFIX . 'nonce'] );
		if( empty( $nonce ) || !wp_verify_nonce( $nonce, self::$PREFIX . "save" ) ) return;

		$sidebar_items = self::sidebar_items();
		$active_item = self::get_active_tab();
		if( !empty( $sidebar_items[$active_item]['class'] ) && file_exists( SHEYDA_WALLET_DIR . "Backend/Pages/Settings/class-settings-{$active_item}.php" ) ) {						
			include_once( SHEYDA_WALLET_DIR . "Backend/Pages/Settings/class-settings-{$active_item}.php" );
			$sidebar_items[$active_item]['class']::save();
		}
		do_action( "sheyda/wallet/settings/{$active_item}_save" );
	}

	public static function enqueue() {
		wp_enqueue_style( 'sheyda-wallet-settings', SHEYDA_WALLET_URI . "assets/css/backend/wallet-settings.min.css", [], SHEYDA_WALLET_VERSION );
		AdminScripts::alert();

		WalletUtils::enqueue_script( 'sheyda-wallet-settings', SHEYDA_WALLET_URI . "assets/js/backend/settings.js" );

		$sidebar_items = self::sidebar_items();
		$active_item = self::get_active_tab();
		if( !empty( $sidebar_items[$active_item]['class'] ) && file_exists( SHEYDA_WALLET_DIR . "Backend/Pages/Settings/class-settings-{$active_item}.php" ) ) {						
			include_once( SHEYDA_WALLET_DIR . "Backend/Pages/Settings/class-settings-{$active_item}.php" );
			$sidebar_items[$active_item]['class']::enqueue();
		}
		do_action( "sheyda/wallet/settings/{$active_item}_enqueue" );
	}
}