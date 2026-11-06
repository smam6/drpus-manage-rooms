<?php

use MJ\Whitebox\Utils\Formatters;
use Sheyda\Wallet\CustomPagination;
use Sheyda\Wallet\Models\Ledger;
use SheydaWalletUtils as WalletUtils;

// Get user transactions
$page = WalletUtils::convert_chars( $_GET['t-page'] ?? 1, true, 'absint' );
if( $page < 1 ) $page = 1;
$ppp = 10;
$transactions = WalletUtils::get_user_transactions( null, false, $ppp, ($page - 1) * $ppp, false );
if( !$transactions->isEmpty() ) {
	$transactions_count = WalletUtils::get_user_transactions_count( null, false, false );
}
$transaction_types = Ledger::types();
$currency_symbol = get_woocommerce_currency_symbol();
$current_user_id = get_current_user_id();
?>
<div class="<?php echo self::$PREFIX ?>transactions <?php echo self::$PREFIX ?>section">
	<?php if( !$transactions->isEmpty() ) { ?>
		<div class="<?php echo self::$PREFIX ?>transactions-list">
			<div class="<?php echo self::$PREFIX ?>transactions-head">
				<span class="<?php echo self::$PREFIX ?>transactions-head-item"><?php esc_html_e( 'ID', 'sheyda_wallet' ) ?></span>
				<span class="<?php echo self::$PREFIX ?>transactions-head-item"><?php esc_html_e( 'Type', 'sheyda_wallet' ) ?></span>
				<span class="<?php echo self::$PREFIX ?>transactions-head-item"><?php esc_html_e( 'Amount', 'sheyda_wallet' ) ?></span>
				<span class="<?php echo self::$PREFIX ?>transactions-head-item"><?php esc_html_e( 'Date', 'sheyda_wallet' ) ?></span>
				<span class="<?php echo self::$PREFIX ?>transactions-head-item"><?php esc_html_e( 'Description', 'sheyda_wallet' ) ?></span>
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
		<?php
		CustomPagination::view( [
			'query_arg_name'	=> 't-page',
			'max_num_pages'		=> ceil( $transactions_count / $ppp ),
			'paged'				=> $page,
		] );
		?>
	<?php } else { ?>
		<div class="<?php echo self::$PREFIX ?>empty-section">
			<i class="sheyda-wallet-icon-document-text <?php echo self::$PREFIX ?>empty-section-icon"></i>
			<span class="<?php echo self::$PREFIX ?>empty-section-text">
				<?php esc_html_e( 'No transactions found.', 'sheyda_wallet' ) ?>
			</span>
		</div>
	<?php } ?>
</div>