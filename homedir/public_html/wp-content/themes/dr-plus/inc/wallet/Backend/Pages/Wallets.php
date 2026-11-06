<?php
namespace Sheyda\Wallet\Backend\Pages;

use MJ\Whitebox\Utils\Formatters;
use MJ\Whitebox\Utils\Sanitizers;
use Sheyda\Wallet\AdminScripts;
use Sheyda\Wallet\Backend\Settings;
use Sheyda\Wallet\Backend\WalletWithdrawals;
use Sheyda\Wallet\Models\Withdrawals;
use Sheyda\Wallet\PublicScripts;
use SheydaWalletUtils as WalletUtils;

class Wallets {
	public static $wallets_table;
	public static $wallet_id = 0;
	public static $default_section;
	public static $active_section = null;

	public static function sections() {
		return [
			'wallets'	=> [
				'menu_title'	=> __( 'All wallets', 'sheyda_wallet' ),
				'page_title'	=> __( 'All Wallets', 'sheyda_wallet' ),
			],
			'transactions'	=> [
				'menu_title'	=> __( 'Transactions', 'sheyda_wallet' ),
				'page_title'	=> __( 'Wallets transactions', 'sheyda_wallet' ),
				'class'			=> 'WalletTransactions',
			],
			'withdrawals'	=> [
				'menu_title'	=> __( 'Withdrawals', 'sheyda_wallet' ),
				'page_title'	=> __( 'Wallets withdrawals', 'sheyda_wallet' ),
				'class'			=> 'WalletWithdrawals',
			],
			'settings'	=> [
				'menu_title'	=> __( 'Settings', 'sheyda_wallet' ),
				'page_title'	=> __( 'Wallet Settings', 'sheyda_wallet' ),
				'class'			=> 'WalletSettings',
			],
		];
	}

	private static function get_active_section() {
		if( self::$active_section === null ) {
			$sections = array_keys( self::sections() );
			self::$default_section = $sections[0];
			$active_section = !empty( $_GET['section'] ) ? WalletUtils::convert_chars( $_GET['section'] ) : self::$default_section;
			self::$active_section = WalletUtils::ensure_values_in_array( $active_section, $sections, self::$default_section );
		}
		return self::$active_section;
	}

	public static function add_menu() {
		$sections = self::sections();
		$menu_slug = "sheyda-wallet";

		$icon = SHEYDA_WALLET_URI . "assets/images/wallet.png";

		$hook = add_menu_page(
			__( 'Wallet', 'sheyda_wallet' ),	// $page_title:string,
			__( 'Wallet', 'sheyda_wallet' ),	// $menu_title:string,
			'manage_woocommerce',				// $capability:string,
			$menu_slug,							// $menu_slug:string,
			[__CLASS__, 'view'],				// $callback:callable,
			$icon,								// $icon_url:string,
			71,									// $position:integer|float|null
		);
		add_action( "load-{$hook}", [ __CLASS__, 'screen_option' ] );

		foreach( $sections as $slug => $section ) {
			$pending_bubble = '';
			if( $slug == 'wallets' ) {
				$slug = $menu_slug;
			} else {
				if( $slug == 'withdrawals' ) {
					$pending_count = Withdrawals::query()->select( 'COUNT( `id` ) AS counts' )->where( 'status', 'pending' )->first();
					if( !empty( $pending_count ) )  {
						$pending_count = $pending_count->counts;
					} else {
						$pending_count = 0;
					}
					if( $pending_count ) {
						$pending_bubble = '<span class="update-plugins count-' . $pending_count . '"><span class="plugin-count">' . $pending_count . '</span></span>';
					}
				}
				$slug = "{$menu_slug}&section={$slug}";
			}

			add_submenu_page(
				$menu_slug, 			// $parent_slug:string
				$section['page_title'], // $page_title:string
				$section['menu_title'] . $pending_bubble, // $menu_title:string
				'manage_woocommerce', 	// $capability:string
				$slug, 					// $menu_slug:string
				[__CLASS__, 'view'], 	// $function:callable
			);
		}
	}

	public static function screen_option() {
		self::$wallet_id = $_GET['user'] ?? 0;
		$option = 'per_page';
		$screen = get_current_screen();
		if( !self::$wallet_id ) {
			$active_section = self::get_active_section();
			$label = __( "Users", 'sheyda_wallet' );
			if( $active_section == 'transactions' ) {
				$label = __( "Transactions", 'sheyda_wallet' );
			} else if( $active_section == 'withdrawals' ) {
				$label = __( "Withdrawals", 'sheyda_wallet' );
			}
			$args = [
				'label'		=> $label,
				'default'	=> 20,
				'option'	=> 'items_per_page',
			];

			$table_for_columns = null;
			if( $active_section == 'transactions' ) {
				include_once( SHEYDA_WALLET_DIR . "Backend/Pages/ListTables/Ledgers.php" );
				$table_for_columns = new ListTables\Ledgers();
			} else if( $active_section == 'withdrawals' ) {
				include_once( SHEYDA_WALLET_DIR . "Backend/Pages/ListTables/Withdrawals.php" );
				$table_for_columns = new ListTables\Withdrawals();
			} else {
				include_once( SHEYDA_WALLET_DIR . "Backend/Pages/ListTables/Wallets.php" );
				self::$wallets_table = new ListTables\Wallets();
				$table_for_columns = self::$wallets_table;
			}
			if( $table_for_columns && $screen ) {
				add_filter( "manage_{$screen->id}_columns", [ $table_for_columns, 'get_columns' ] );
			}

			add_screen_option( $option, $args );
		}
	}

	public static function set_screen_option( $status, $option, $value ) {
		if ( $option === 'items_per_page' ) {
			return $value;
		}

		return $status;
	}

	public static function change_active_submenu( $submenu_file, $parent_file ) {
		if( !empty( $_GET['page'] ) && $_GET['page'] == 'sheyda-wallet' && !empty( $_GET['section'] ) ) {
			$active_section = self::get_active_section();
			$submenu_file = "sheyda-wallet&section={$active_section}";
		}
		return $submenu_file;
	}

	public static function view() {
		$sections = self::sections();
		$active_section = self::get_active_section();
		?>
		<div class="wrap">
			<?php
			if( $active_section == 'wallets' ) {
				if( self::$wallet_id ) {
					$action = !empty( $_GET['action'] ) ? $_GET['action'] : 'details';
					if( $action == 'details' ) {
						include( SHEYDA_WALLET_DIR . "Backend/Pages/Wallets/class-wallet-single.php" );
						\Sheyda\Wallet\Backend\WalletSingle::view();
					} else if( $action == 'adjust' ) {
						include( SHEYDA_WALLET_DIR . "Backend/Pages/Wallets/class-wallet-adjust.php" );
						\Sheyda\Wallet\Backend\WalletAdjust::view( self::$wallet_id );
					}
				} else {
					include( SHEYDA_WALLET_DIR . "Backend/Pages/Wallets/class-wallet-list.php" );
					\Sheyda\Wallet\Backend\WalletsList::view( self::$wallets_table );
				}
			} else if( $active_section == 'settings' ) {
				include_once( SHEYDA_WALLET_DIR . "Backend/Pages/Settings/class-settings.php" );
				\Sheyda\Wallet\Backend\Settings::view();
			} else {
				if( file_exists( SHEYDA_WALLET_DIR . "Backend/Pages/Wallets/class-wallet-{$active_section}.php" ) ) {
					include_once( SHEYDA_WALLET_DIR . "Backend/Pages/Wallets/class-wallet-{$active_section}.php" );
					$active_section_class = $sections[$active_section]['class'];
					// call view function
					$class = "Sheyda\Wallet\Backend\\" . $active_section_class;
					$class::view();
				}
			}
			?>
		</div>
		<?php
	}

	public static function save() {
		if( empty( $_POST ) || empty( $_GET["page"] ) ) return;

		$page = WalletUtils::convert_chars( $_GET['page'] ?? "" );
		if( $page != 'sheyda-wallet' ) return;

		$section = self::get_active_section();
		if( $section == 'wallets' ) {
			if( empty( $_POST["sheyda_adjust_wallet_nonce"] ) ) return;
			$nonce = WalletUtils::convert_chars( $_POST["sheyda_adjust_wallet_nonce"] );
			if( empty( $nonce ) || !wp_verify_nonce( $nonce, "sheyda_adjust_wallet_nonce_value" ) ) return;

			$user_id = WalletUtils::convert_chars( $_POST['user_id'], 'absint' );
			if( empty( $user_id ) ) return;

			$type = WalletUtils::ensure_values_in_array( WalletUtils::convert_chars( $_POST['adjust_type'] ), ['credit', 'debit', 'lock', 'unlock'], '' );
			if( empty( $type ) ) return;

			$amount = Sanitizers::price( $_POST['adjust_amount'] );
			if( empty( $amount ) ) return;

			$note = WalletUtils::convert_chars( $_POST['adjust_note'], 'sanitize_textarea_field' );

			$created_by = get_current_user_id();
			$meta = [];
			if( !empty( $note ) ) $meta['description'] = $note;

			if( $type == 'credit' ) {
				$res = WalletUtils::add_user_adjust_credit_record( $amount, $user_id, $created_by, '', $meta );
				if( !is_wp_error( $res ) ) {
					add_settings_error( 'sheyda-wallet-adjust', "", sprintf( esc_html__( 'Successfully adjusted credit (%s) for %s', 'sheyda_wallet' ), Formatters::price( $amount, true ), get_user_by( 'id', $user_id )->display_name ), 'success' );
				}
			} else if( $type == 'debit' ) {
				$res = WalletUtils::add_user_adjust_debit_record( $amount, $user_id, $created_by, '', $meta );
				if( !is_wp_error( $res ) ) {
					add_settings_error( 'sheyda-wallet-adjust', "", sprintf( esc_html__( 'Successfully adjusted debit (%s) for %s', 'sheyda_wallet' ), Formatters::price( $amount, true ), get_user_by( 'id', $user_id )->display_name ), 'success' );
				}
			} else if( $type == 'lock' ) {
				$res = WalletUtils::add_user_lock_record( $amount, $user_id, $created_by, '', $meta );
				if( !is_wp_error( $res ) ) {
					add_settings_error( 'sheyda-wallet-adjust', "", sprintf( esc_html__( 'Successfully adjusted lock (%s) for %s', 'sheyda_wallet' ), Formatters::price( $amount, true ), get_user_by( 'id', $user_id )->display_name ), 'success' );
				}
			} else if( $type == 'unlock' ) {
				$res = WalletUtils::add_user_unlock_record( $amount, $user_id, $created_by, '', $meta );
				if( !is_wp_error( $res ) ) {
					add_settings_error( 'sheyda-wallet-adjust', "", sprintf( esc_html__( 'Successfully adjusted unlock (%s) for %s', 'sheyda_wallet' ), Formatters::price( $amount, true ), get_user_by( 'id', $user_id )->display_name ), 'success' );
				}
			}

			if( is_wp_error( $res ) ) {
				add_settings_error( 'sheyda-wallet-adjust', $res->get_error_code(), $res->get_error_message(), 'error' );
			}

		} else if( $section == 'settings' ) {
			include_once( SHEYDA_WALLET_DIR . "Backend/Pages/Settings/class-settings.php" );
			\Sheyda\Wallet\Backend\Settings::save();
		} else if( $section == 'withdrawals' ) {
			include_once( SHEYDA_WALLET_DIR . "Backend/Pages/Wallets/class-wallet-withdrawals.php" );
			WalletWithdrawals::save();
		}
	}

	public static function enqueue() {
		if( empty( $_GET['page'] ) || $_GET['page'] != 'sheyda-wallet' ) return;

		if( SHEYDA_WALLET_DEV ) {
			wp_enqueue_script( 'sheyda-wallet', SHEYDA_WALLET_URI . "assets/js/wallet.js", ['jquery'], SHEYDA_WALLET_VERSION, true );
		} else {
			wp_enqueue_script( 'sheyda-wallet', SHEYDA_WALLET_URI . "assets/js/wallet.min.js", ['jquery'], SHEYDA_WALLET_VERSION, true );
		}

		WalletUtils::wallet_vars_localize();

		$active_section = self::get_active_section();
		if( $active_section == 'wallets' ) {
			if( WalletUtils::convert_chars( $_GET['action'] ?? "" ) == 'adjust' ) {
				PublicScripts::select2();
				AdminScripts::form_group();
				wp_enqueue_style( 'sheyda-wallet-adjust', SHEYDA_WALLET_URI . "assets/css/backend/wallet-adjust.min.css", [], SHEYDA_WALLET_VERSION );
			}
		} else if( $active_section == 'settings' ) {
			include_once( SHEYDA_WALLET_DIR . "Backend/Pages/Settings/class-settings.php" );
			Settings::enqueue();
		} else if( $active_section == 'withdrawals' ) {
			include_once( SHEYDA_WALLET_DIR . "Backend/Pages/Wallets/class-wallet-withdrawals.php" );
			WalletWithdrawals::enqueue();
		}
	}
}
add_action( 'admin_menu', [Wallets::class, 'add_menu'] );
add_action( 'admin_init', [Wallets::class, 'save'] );
add_filter( 'set-screen-option', [Wallets::class, 'set_screen_option'], 10, 3 );
add_filter( 'submenu_file', [Wallets::class, 'change_active_submenu'], 100, 2 );
add_action( 'admin_enqueue_scripts', [Wallets::class, 'enqueue'] );
