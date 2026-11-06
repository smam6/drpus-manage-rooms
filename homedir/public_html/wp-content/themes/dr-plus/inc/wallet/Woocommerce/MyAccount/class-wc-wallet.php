<?php
namespace Sheyda\Wallet\Woocommerce;

use MJ\Whitebox\Utils\Sanitizers;
use Sheyda\Wallet\Models\Withdrawals;
use Sheyda\Wallet\PublicScripts;
use Sheyda\Wallet\Utils\Settings;
use Sheyda\Wallet\Utils\WC;
use SheydaWalletUtils as WalletUtils;

class WCWallet {
	public static $PREFIX = 'sheyda_wallet_';

	public static function wallet_sections() {
		$sections = [
			'dashboard'			=> __( 'Dashboard', 'sheyda_wallet' ),
			'topup'				=> __( 'Top-up', 'sheyda_wallet' ),
			'withdrawal'		=> __( 'Withdrawal', 'sheyda_wallet' ),
			'transactions'		=> __( 'Transactions', 'sheyda_wallet' ),
			'financial'			=> __( 'Financial info', 'sheyda_wallet' )
		];

		// Get wallet general settings
		$topup_settings = Settings::get_settings( 'topup' );
		$withdrawal_settings = Settings::get_settings( 'withdrawal' );
		if( !$withdrawal_settings['enable'] ) {
			unset( $sections['withdrawal'] );
			unset( $sections['financial'] );
		}
		if( !$topup_settings['enable'] ) unset( $sections['topup'] );

		return apply_filters( 'sheyda/wallet/woocommerce/sections', $sections );
	}

	public static function get_active_section() {
		$sidebar_items = self::wallet_sections();
		return WalletUtils::ensure_values_in_array( WalletUtils::convert_chars( $_GET['section'] ?? "" ), array_keys( $sidebar_items ), array_key_first( $sidebar_items ) );
	}

	public static function create_nonce() {
		wp_nonce_field( self::$PREFIX . 'wc_save', self::$PREFIX . 'wc_nonce' );
	}

	public static function view() {
		$sections = self::wallet_sections();
		$active_section = self::get_active_section();
		$base_url = wc_get_account_endpoint_url( 'sheyda-wallet' );

		?>
		<div class="<?php echo self::$PREFIX ?>wrap">
			<div class="<?php echo self::$PREFIX ?>head">
				<h2 class="<?php echo self::$PREFIX ?>title"><?php esc_html_e( 'Wallet', 'sheyda_wallet' ) ?></h2>
				<div class="<?php echo self::$PREFIX ?>nav_items">
					<?php foreach( $sections as $section => $label ) { ?>
						<a href="<?php echo add_query_arg( ['section' => $section], $base_url ) ?>" class="<?php echo self::$PREFIX ?>nav_item<?php echo $active_section == $section ? ' active' : "" ?>"><?php echo esc_html( $label ) ?></a>
					<?php } ?>
				</div>
			</div>
			<div class="<?php echo self::$PREFIX ?>section_content">
				<?php
				do_action( "sheyda/wallet/woocommerce/start_{$active_section}_content" );
				$file = apply_filters( "sheyda/wallet/woocommerce/content/{$active_section}", SHEYDA_WALLET_DIR . "Woocommerce/MyAccount/template-wc-wallet-{$active_section}.php" );
				if( file_exists( $file ) ) {
					include( $file );
				}
				do_action( "sheyda/wallet/woocommerce/end_{$active_section}_content" );
				?>
			</div>
		</div>
		<?php
	}

	public static function save( string $section ) {
		do_action( "sheyda/wallet/woocommerce/save/{$section}" );

		if( $section == 'financial' ) {
			$nonce = WalletUtils::convert_chars( $_POST['sheyda_wallet_financial_nonce_value'] ?? "" );
			if( empty( $nonce ) || !wp_verify_nonce( $nonce, 'sheyda_wallet_financial_nonce' ) ) return;

			$accounts = [];
			$types = array_keys( WC::get_financial_types() );
			if( !empty( $_POST['sheyda_wallet_financial_account'] ) ) {
				foreach( $_POST['sheyda_wallet_financial_account'] as $account ) {					
					$account = [
						'type'	=> WalletUtils::ensure_values_in_array( WalletUtils::convert_chars( $account['type'] ), $types, '' ),
						'number' => WalletUtils::convert_chars( $account['number'] ),
						'owner' => WalletUtils::convert_chars( $account['owner'] ),
					];
					if( empty( $account['type'] ) ) continue;
					$accounts[] = $account;
				}
			}

			WC::update_user_financial_accounts( $accounts );
		} else if( $section == 'withdrawal' ) {
			$nonce = WalletUtils::convert_chars( $_POST['sheyda_wallet_withdrawal_request_nonce_value'] ?? "" );
			if( empty( $nonce ) || !wp_verify_nonce( $nonce, 'sheyda_wallet_withdrawal_request_nonce' ) ) return;
			if( !isset( $_POST['sheyda_wallet_withdrawal_destination_account'] ) ) return;

			$amount = Sanitizers::price( $_POST['sheyda_wallet_withdrawal_amount'] );
			$account = WalletUtils::convert_chars( $_POST['sheyda_wallet_withdrawal_destination_account'] );

			// Get user balance
			$user_id = get_current_user_id();
			$user_balance = WalletUtils::get_user_balance( $user_id );
			$user_available_balance = $user_balance->balance - $user_balance->locked;

			// get wallet withdrawal settings
			$withdrawal_settings = Settings::get_settings( 'withdrawal' );
			if( $amount > $user_available_balance || floatval( $withdrawal_settings['minimum_withdrawal_request'] ) > $amount ) {
				return;
			}

			$fee = 0;
			if( $withdrawal_settings['withdrawal_fee_type'] == 'fixed' ) {
				$fee = Sanitizers::price( $withdrawal_settings['withdrawal_fixed_fee'] );
			} else if( $withdrawal_settings['withdrawal_fee_type'] == 'percentage' ) {
				$fee = $amount * ( intval( $withdrawal_settings['withdrawal_percentage_fee'] ) / 100 );
			}

			// Get user account info
			$user_accounts = WC::get_user_financial_accounts();
			$account_info = $user_accounts[$account] ?? [];
			if( empty( $account_info ) ) return;

			// Add withdrawal request for user
			Withdrawals::query()->beginTransaction();
			try {
				$withdrawal = new Withdrawals();
				$withdrawal->user_id = $user_id;
				$withdrawal->amount_requested = $amount;
				$withdrawal->fee = $fee;
				$withdrawal->amount_net = $amount - $fee;
				$withdrawal->status = 'pending';
				$withdrawal->bank_info = $account_info;
				$withdrawal->save();
			} catch (Exception $e) {
				Withdrawals::query()->rollBack();
				return;
			}

			// add withdraw request record to ledger to lock requested amount
			$res = WalletUtils::add_user_withdraw_request_record( $amount, $user_id, $user_id, $withdrawal->id, ['fee' => $fee] );

			if( !$res ) {
				Withdrawals::query()->rollBack();
				return;
			}
			Withdrawals::query()->commit();
		}
	}

	public static function enqueue() {
		$active_section = self::get_active_section();

		if( $active_section == 'financial' ) {
			PublicScripts::select2();
		}
	}
}