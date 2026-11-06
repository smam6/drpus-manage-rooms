<?php

use MJ\Whitebox\Utils\Formatters;
use Sheyda\Wallet\Models\Ledger;
use Sheyda\Wallet\Utils\Settings;
use SheydaWalletUtils as WalletUtils;

$user_id = get_current_user_id();
$user_wallet = WalletUtils::get_user_balance( $user_id );

$topup_settings = Settings::get_settings( 'topup' );
$withdrawal_settings = Settings::get_settings( 'withdrawal' );

// Get user transactions
$transactions = WalletUtils::get_user_transactions( null, false, 5, 0, false );
$transaction_types = Ledger::types();
$currency_symbol = get_woocommerce_currency_symbol();

?>
<div class="<?php echo self::$PREFIX ?>dashboard <?php echo self::$PREFIX ?>section">
	<div class="<?php echo self::$PREFIX ?>dashboard-info">
		<div class="<?php echo self::$PREFIX ?>dashboard-info-inner">
			<i class="sheyda-wallet-icon-coin"></i>
			<span class="<?php echo self::$PREFIX ?>dashboard-balance-label"><?php esc_html_e( 'Your wallet balance', 'sheyda_wallet' ) ?></span>
			<span class="<?php echo self::$PREFIX ?>dashboard-balance-value"><?php printf( '%s %s', Formatters::price( $user_wallet->balance - $user_wallet->locked ), $currency_symbol ) ?></span>
			<span class="<?php echo self::$PREFIX ?>dashboard-locked"><?php printf( esc_html__( 'Locked amount: %s %s', 'sheyda_wallet' ), Formatters::price( $user_wallet->locked ), $currency_symbol ) ?></span>
		</div>
		<?php if( $withdrawal_settings['enable'] || $topup_settings['enable'] ) { ?>
			<div class="<?php echo self::$PREFIX ?>dashboard-quick-access-wrap">
				<?php if( $topup_settings['enable'] ) { ?>
					<a href="<?php echo add_query_arg( ['section' => 'topup'] ) ?>" class="<?php echo self::$PREFIX ?>dashboard-quick-access-btn">
						<i class="sheyda-wallet-icon-wallet-add"></i>
						<span><?php esc_html_e( 'Increase Balance', 'sheyda_wallet' ) ?></span>
					</a>
				<?php } ?>
				<?php if( $withdrawal_settings['enable'] ) { ?>
					<a href="<?php echo add_query_arg( ['section' => 'withdrawal'] ) ?>" class="<?php echo self::$PREFIX ?>dashboard-quick-access-btn">
						<i class="sheyda-wallet-icon-wallet-minus"></i>
						<span><?php esc_html_e( 'Request withdrawal', 'sheyda_wallet' ) ?></span>
					</a>
				<?php } ?>
			</div>
		<?php } ?>
	</div>
	<?php if( !$transactions->isEmpty() ) { ?>
		<div class="<?php echo self::$PREFIX ?>dashboard-transactions-list">
			<div class="<?php echo self::$PREFIX ?>dashboard-transactions-head">
				<span class="<?php echo self::$PREFIX ?>dashboard-transactions-title"><?php esc_html_e( 'Last transactions', 'sheyda_wallet' ) ?></span>
				<a href="<?php echo add_query_arg( ['section' => 'transactions' ] ) ?>" class="<?php echo self::$PREFIX ?>dashboard-all-transactions-link">
					<span><?php esc_html_e( 'View all', 'sheyda_wallet' ) ?></span>
					<i class="sheyda-wallet-icon-<?php echo is_rtl() ? 'left' : 'right' ?>"></i>
				</a>
			</div>
			<div class="<?php echo self::$PREFIX ?>transactions-items">
				<?php foreach( $transactions as $index => $transaction ) { ?>
					<div class="<?php echo self::$PREFIX ?>transaction">
						<span class="<?php echo self::$PREFIX ?>transaction-id <?php echo self::$PREFIX ?>transaction-item-cell">
							<span class="<?php echo self::$PREFIX ?>transaction-item-title"><?php esc_html_e( 'ID', 'sheyda_wallet' ) ?></span>
							<span class="<?php echo self::$PREFIX ?>transaction-item-value"><?php printf( '#%s', $transaction->id ) ?></span>
						</span>
						<span class="<?php echo self::$PREFIX ?>transaction-type <?php echo self::$PREFIX ?>transaction-item-cell">
							<span class="<?php echo self::$PREFIX ?>transaction-item-title"><?php esc_html_e( 'Type', 'sheyda_wallet' ) ?></span>
							<span class="<?php echo self::$PREFIX ?>transaction-item-value"><?php echo $transaction_types[$transaction->type] ?></span>
							
						</span>
						<span class="<?php echo self::$PREFIX ?>transaction-amount <?php echo self::$PREFIX ?>transaction-item-cell">
							<span class="<?php echo self::$PREFIX ?>transaction-item-title"><?php esc_html_e( 'Amount', 'sheyda_wallet' ) ?></span>
							<span class="<?php echo self::$PREFIX ?>transaction-item-value"><?php echo Formatters::price( $transaction->amount, true ) ?></span>
							
						</span>
						<span class="<?php echo self::$PREFIX ?>transaction-created-date <?php echo self::$PREFIX ?>transaction-item-cell">
							<?php
							$created_at_text = '';
							if( !empty( $transaction->created_at ) ) {
								$created_at_text = '<span>' . date_i18n( 'j F Y', $transaction->created_at->format( 'U' ) ) . '</span>';
								$created_at_text .= '<small>' . date_i18n( 'H:i', $transaction->created_at->format( 'U' ) ) . '</small>';
							}
							?>
							<span class="<?php echo self::$PREFIX ?>transaction-item-title"><?php esc_html_e( 'Created date', 'sheyda_wallet' ) ?></span>
							<span class="<?php echo self::$PREFIX ?>transaction-item-value"><?php echo $created_at_text ?></span>
						</span>
						<?php if( !empty( $transaction->meta['description'] ) ) { ?>
							<span class="<?php echo self::$PREFIX ?>transaction-description"><?php echo wpautop( $transaction->meta['description'] ) ?></span>
						<?php } ?>
					</div>
				<?php } ?>
			</div>
		</div>
	<?php } ?>
</div>