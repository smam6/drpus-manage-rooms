<?php

use MJ\Whitebox\Utils\Formatters;
use Sheyda\Wallet\CustomPagination;
use Sheyda\Wallet\Models\Withdrawals;
use Sheyda\Wallet\Utils\Settings;
use Sheyda\Wallet\Utils\WC;
use SheydaWalletUtils as WalletUtils;

if( !empty( $_GET['withdrawal-id'] ) ) {
	$withdrawal = Withdrawals::find( WalletUtils::convert_chars( $_GET['withdrawal-id'], 'absint' ) );
	?>
	<div class="<?php echo self::$PREFIX ?>withdrawal-section <?php echo self::$PREFIX ?>section">
		<?php if( empty( $withdrawal ) ) { ?>
			<div class="<?php echo self::$PREFIX ?>empty-section">
				<i class="sheyda-wallet-icon-close-circle <?php echo self::$PREFIX ?>empty-section-icon"></i>
				<span class="<?php echo self::$PREFIX ?>empty-section-text">
					<?php esc_html_e( 'Failed to get withdrawal', 'sheyda_wallet' ) ?>
				</span>
			</div>
		<?php } else { ?>
			<div class="<?php echo self::$PREFIX ?>withdrawal-single">
				<span class="<?php echo self::$PREFIX ?>withdrawal-title <?php echo self::$PREFIX ?>section-title"><?php esc_html_e( 'Withdrawal details', 'sheyda_wallet' ) ?></span>
				<div class="<?php echo self::$PREFIX ?>withdrawal-single-details">
					<div class="<?php echo self::$PREFIX ?>withdrawal-single-details-section details-section-1">
						<div class="<?php echo self::$PREFIX ?>withdrawal-single-detail-item">
							<span class="<?php echo self::$PREFIX ?>withdrawal-single-detail-item-title"><?php esc_html_e( 'ID', 'sheyda_wallet' ) ?></span>
							<span class="<?php echo self::$PREFIX ?>withdrawal-single-detail-item-value"><?php echo $withdrawal->id ?></span>
						</div>
						<div class="<?php echo self::$PREFIX ?>withdrawal-single-detail-item">
							<span class="<?php echo self::$PREFIX ?>withdrawal-single-detail-item-title"><?php esc_html_e( 'Status', 'sheyda_wallet' ) ?></span>
							<span class="<?php echo self::$PREFIX ?>withdrawal-single-detail-item-value"><?php echo Withdrawals::statuses()[$withdrawal->status] ?></span>
						</div>
						<div class="<?php echo self::$PREFIX ?>withdrawal-single-detail-item">
							<span class="<?php echo self::$PREFIX ?>withdrawal-single-detail-item-title"><?php esc_html_e( 'Amount', 'sheyda_wallet' ) ?></span>
							<span class="<?php echo self::$PREFIX ?>withdrawal-single-detail-item-value"><?php echo Formatters::price( $withdrawal->amount_net, true ) ?></span>
						</div>
						<div class="<?php echo self::$PREFIX ?>withdrawal-single-detail-item">
							<span class="<?php echo self::$PREFIX ?>withdrawal-single-detail-item-title"><?php esc_html_e( 'Fee', 'sheyda_wallet' ) ?></span>
							<span class="<?php echo self::$PREFIX ?>withdrawal-single-detail-item-value"><?php echo Formatters::price( $withdrawal->fee, true ) ?></span>
						</div>
						<div class="<?php echo self::$PREFIX ?>withdrawal-single-detail-item">
							<span class="<?php echo self::$PREFIX ?>withdrawal-single-detail-item-title"><?php esc_html_e( 'Request date', 'sheyda_wallet' ) ?></span>
							<span class="<?php echo self::$PREFIX ?>withdrawal-single-detail-item-value"><?php echo date_i18n( 'Y/m/d - H:i', $withdrawal->created_at->format( 'U' ) ) ?></span>
						</div>
						<div class="<?php echo self::$PREFIX ?>withdrawal-single-detail-item">
							<span class="<?php echo self::$PREFIX ?>withdrawal-single-detail-item-title"><?php esc_html_e( 'Date of completion', 'sheyda_wallet' ) ?></span>
							<span class="<?php echo self::$PREFIX ?>withdrawal-single-detail-item-value"><?php echo date_i18n( 'Y/m/d - H:i', $withdrawal->updated_at->format( 'U' ) ) ?></span>
						</div>
					</div>
					<div class="<?php echo self::$PREFIX ?>withdrawal-single-details-section details-section-2">
						<?php if( !empty( $withdrawal->admin_notes ) ) { ?>
							<div class="<?php echo self::$PREFIX ?>withdrawal-single-detail-item">
								<span class="<?php echo self::$PREFIX ?>withdrawal-single-detail-item-title"><?php esc_html_e( 'Admin note', 'sheyda_wallet' ) ?></span>
								<span class="<?php echo self::$PREFIX ?>withdrawal-single-detail-item-value"><?php echo wpautop( $withdrawal->admin_notes ) ?></span>
							</div>
						<?php } ?>
						<div class="<?php echo self::$PREFIX ?>withdrawal-single-detail-item">
							<span class="<?php echo self::$PREFIX ?>withdrawal-single-detail-item-title"><?php esc_html_e( 'Bank info', 'sheyda_wallet' ) ?></span>
							<span class="<?php echo self::$PREFIX ?>withdrawal-single-detail-item-value">
								<span><?php printf( esc_html( '%s: %s', 'sheyda_wallet' ), WalletUtils::get_financial_types()[$withdrawal->bank_info['type']], $withdrawal->bank_info['number'] ) ?></span>
								<br>
								<span><?php printf( esc_html__( 'Owner: %s', 'sheyda_wallet' ), $withdrawal->bank_info['owner'] ) ?></span>
							</span>
						</div>
					</div>
				</div>
				<a href="<?php echo remove_query_arg( 'withdrawal-id' ) ?>" class="sheyda_wallet_button button button-small <?php echo self::$PREFIX ?>withdrawal-single-back"><?php esc_html_e( 'Back', 'sheyda_wallet' ) ?></a>
			</div>
		<?php } ?>
	</div>
	<?php
} else {
	// Get user withdrawals
	$page = WalletUtils::convert_chars( $_GET['w-page'] ?? 1, true, 'absint' );
	if( $page < 1 ) $page = 1;
	$ppp = 10;
	$withdrawals = WalletUtils::get_user_withdrawals( null, false, $ppp, ($page - 1) * $ppp );
	if( !$withdrawals->isEmpty() ) {
		$withdrawals_count = WalletUtils::get_user_withdrawals_count();
	}
	$withdrawal_statuses = Withdrawals::statuses();
	$currency_symbol = get_woocommerce_currency_symbol();
	$current_user_id = get_current_user_id();

	// Get user balance
	$user_balance = WalletUtils::get_user_balance();
	$user_available_balance = $user_balance->balance - $user_balance->locked;

	// get wallet withdrawal settings
	$withdrawal_settings = Settings::get_settings( 'withdrawal' );
	$withdrawal_access = floatval( $withdrawal_settings['minimum_withdrawal_request'] ) <= $user_available_balance;

	$user_accounts = WC::get_user_financial_accounts();
	?>
	<div class="<?php echo self::$PREFIX ?>withdrawal-section <?php echo self::$PREFIX ?>section">
		<div class="<?php echo self::$PREFIX ?>withdrawal-title-wrap <?php echo self::$PREFIX ?>section-title-wrap">
			<i class="sheyda-wallet-icon-wallet-minus"></i>
			<span class="<?php echo self::$PREFIX ?>withdrawal-title <?php echo self::$PREFIX ?>section-title"><?php esc_html_e( 'Request withdrawal', 'sheyda_wallet' ) ?></span>
		</div>
		<div class="<?php echo self::$PREFIX ?>withdrawal-notes">
			<p class="<?php echo self::$PREFIX ?>withdrawal-note withdrawable-credit"><?php printf( esc_html__( 'Withdrawable credit: %s %s.', 'sheyda_wallet' ), Formatters::price( $user_available_balance ), $currency_symbol ) ?></p>
			<?php if( !$withdrawal_access ) { ?>
				<p class="<?php echo self::$PREFIX ?>withdrawal-note note-error"><?php esc_html_e( 'You have insufficient amount to withdraw.', 'sheyda_wallet' ) ?></p>
				<?php if( (int)$withdrawal_settings['minimum_withdrawal_request'] > 0 ) { ?>
					<p class="<?php echo self::$PREFIX ?>withdrawal-note"><?php printf( esc_html__( 'Minimum withdrawal request amount is %s %s.', 'sheyda_wallet' ), Formatters::price( $withdrawal_settings['minimum_withdrawal_request'] ), $currency_symbol ) ?></p>
				<?php } ?>
			<?php } ?>
		</div>
		<?php if( $withdrawal_access ) { ?>
			<form action="" method="post" class="<?php echo self::$PREFIX ?>withdrawal-request-form" id="<?php echo self::$PREFIX ?>withdrawal-request-form">
				<input type="hidden" name="action" value="sheyda_wallet_withdrawal_form">
				<?php wp_nonce_field( self::$PREFIX . "withdrawal_request_nonce", self::$PREFIX . "withdrawal_request_nonce_value" ) ?>
				<div class="<?php echo self::$PREFIX ?>withdrawal-field-wrap">
					<label for="<?php echo self::$PREFIX ?>withdrawal-amount-field" class="<?php echo self::$PREFIX ?>withdrawal-field-label"><?php printf( esc_html__( 'Amount (%s)', 'sheyda_wallet' ), $currency_symbol ) ?></label>
					<input type="text" id="<?php echo self::$PREFIX ?>withdrawal-amount-field" class="sheyda-wallet-price-input ltr <?php echo self::$PREFIX ?>withdrawal-field" name="<?php echo self::$PREFIX ?>withdrawal_amount" data-min="<?php echo $withdrawal_settings['minimum_withdrawal_request'] ?>" data-max="<?php echo $user_available_balance ?>" inputmode="numeric" autocomplete="off">
					<div class="<?php echo self::$PREFIX ?>withdrawal-errors"></div>
				</div>
				<div class="<?php echo self::$PREFIX ?>withdrawal-field-wrap">
					<label for="<?php echo self::$PREFIX ?>withdrawal-destination-field" class="<?php echo self::$PREFIX ?>withdrawal-field-label"><?php esc_html_e( 'Destination account', 'sheyda_wallet' ) ?></label>
					<?php if( !empty( $user_accounts ) ) { ?>
					<select id="<?php echo self::$PREFIX ?>withdrawal-destination-field" name="<?php echo self::$PREFIX ?>withdrawal_destination_account">
						<?php foreach( $user_accounts as $index => $user_account ) { ?>
							<option value="<?php echo $index ?>">
								<?php if( !empty( $user_account['owner'] ) ) {
									printf( '%s (%s)', $user_account['number'], $user_account['owner'] );
								} else {
									echo $user_account['number'];
								} ?>
							</option>
						<?php } ?>
					</select>
					<?php } else { ?>
						<p class="<?php echo self::$PREFIX ?>withdrawal-financial-error"><?php printf( __( 'You don\'t register any bank account. please add one from <a href="%s">financial info section</a>', 'sheyda_wallet' ), add_query_arg( ['section' => 'financial'] ) ) ?></p>
					<?php } ?>
				</div>
				<?php if( (int)$withdrawal_settings['minimum_withdrawal_request'] > 0 ) { ?>
					<p class="<?php echo self::$PREFIX ?>withdrawal-note"><?php printf( esc_html__( 'Minimum withdrawal request amount is %s %s.', 'sheyda_wallet' ), Formatters::price( $withdrawal_settings['minimum_withdrawal_request'] ), $currency_symbol ) ?></p>
				<?php } ?>
				<?php if( !empty( $user_accounts ) ) { ?>
					<button id="<?php echo self::$PREFIX ?>withdrawal-submit" class="<?php echo self::$PREFIX ?>withdrawal-submit sheyda_wallet_button" type="submit">
						<span class="button-text"><?php esc_html_e( 'Submit', 'sheyda_wallet' ) ?></span>
						<div class="button-loading"><?php echo file_get_contents( SHEYDA_WALLET_DIR . "assets/images/loading.svg" ) ?></div>
					</button>
				<?php } ?>
			</form>
		<?php } ?>
	</div>
	<div class="<?php echo self::$PREFIX ?>withdrawals <?php echo self::$PREFIX ?>section">
		<?php if( !$withdrawals->isEmpty() ) { ?>
			<div class="<?php echo self::$PREFIX ?>withdrawals-list">
				<div class="<?php echo self::$PREFIX ?>withdrawals-head">
					<span class="<?php echo self::$PREFIX ?>withdrawals-head-item"><?php esc_html_e( 'ID', 'sheyda_wallet' ) ?></span>
					<span class="<?php echo self::$PREFIX ?>withdrawals-head-item"><?php esc_html_e( 'Amount', 'sheyda_wallet' ) ?></span>
					<span class="<?php echo self::$PREFIX ?>withdrawals-head-item"><?php esc_html_e( 'Fee', 'sheyda_wallet' ) ?></span>
					<span class="<?php echo self::$PREFIX ?>withdrawals-head-item"><?php esc_html_e( 'Status', 'sheyda_wallet' ) ?></span>
					<span class="<?php echo self::$PREFIX ?>withdrawals-head-item"><?php esc_html_e( 'Date', 'sheyda_wallet' ) ?></span>
					<span class="<?php echo self::$PREFIX ?>withdrawals-head-item"><?php esc_html_e( 'Actions', 'sheyda_wallet' ) ?></span>
				</div>
				<div class="<?php echo self::$PREFIX ?>withdrawals-items">
					<?php foreach( $withdrawals as $index => $withdrawal ) { ?>
						<div class="<?php echo self::$PREFIX ?>withdrawal">
							<span class="<?php echo self::$PREFIX ?>withdrawal-id <?php echo self::$PREFIX ?>withdrawal-item-cell">
								<span class="<?php echo self::$PREFIX ?>withdrawal-item-title"><?php esc_html_e( 'ID', 'sheyda_wallet' ) ?></span>
								<span class="<?php echo self::$PREFIX ?>withdrawal-item-value"><?php printf( '#%s', $withdrawal->id ) ?></span>
							</span>
							</span>
							<span class="<?php echo self::$PREFIX ?>withdrawal-amount <?php echo self::$PREFIX ?>withdrawal-item-cell">
								<span class="<?php echo self::$PREFIX ?>withdrawal-item-title"><?php esc_html_e( 'Amount', 'sheyda_wallet' ) ?></span>
								<span class="<?php echo self::$PREFIX ?>withdrawal-item-value"><?php echo Formatters::price( $withdrawal->amount_requested, true ) ?></span>
								
							</span>
							<span class="<?php echo self::$PREFIX ?>withdrawal-balance_after <?php echo self::$PREFIX ?>withdrawal-item-cell">
								<span class="<?php echo self::$PREFIX ?>withdrawal-item-title"><?php esc_html_e( 'Fee', 'sheyda_wallet' ) ?></span>
								<span class="<?php echo self::$PREFIX ?>withdrawal-item-value"><?php echo Formatters::price( $withdrawal->fee, true ) ?></span>
								
							</span>
							<span class="<?php echo self::$PREFIX ?>withdrawal-status <?php echo self::$PREFIX ?>withdrawal-item-cell">
								<span class="<?php echo self::$PREFIX ?>withdrawal-item-title"><?php esc_html_e( 'Status', 'sheyda_wallet' ) ?></span>
								<span class="<?php echo self::$PREFIX ?>withdrawal-item-value"><?php echo $withdrawal_statuses[$withdrawal->status] ?></span>
							</span>
							<span class="<?php echo self::$PREFIX ?>withdrawal-date <?php echo self::$PREFIX ?>withdrawal-item-cell">
								<?php
								$date_text = '';
								if( !empty( $withdrawal->updated_at ) ) {
									$date_text = '<span>' . date_i18n( 'j F Y', $withdrawal->updated_at->format( 'U' ) ) . '</span>';
									$date_text .= '<small>' . date_i18n( 'H:i', $withdrawal->updated_at->format( 'U' ) ) . '</small>';
								}
								?>
								<span class="<?php echo self::$PREFIX ?>withdrawal-item-title"><?php esc_html_e( 'Date', 'sheyda_wallet' ) ?></span>
								<span class="<?php echo self::$PREFIX ?>withdrawal-item-value"><?php echo $date_text ?></span>
							</span>
							<span class="<?php echo self::$PREFIX ?>withdrawal-action <?php echo self::$PREFIX ?>withdrawal-item-cell">
								<a href="<?php echo add_query_arg( ['withdrawal-id' => $withdrawal->id] ) ?>" class="sheyda_wallet_button button-action button-small button-fullwidth"><?php esc_html_e( 'View', 'sheyda_wallet' ) ?></a>
							</span>
						</div>
					<?php } ?>
				</div>
			</div>
			<?php
			CustomPagination::view( [
				'query_arg_name'	=> 'w-page',
				'max_num_pages'		=> ceil( $withdrawals_count / $ppp ),
				'paged'				=> $page,
			] );
			?>
		<?php } else { ?>
			<div class="<?php echo self::$PREFIX ?>empty-section">
				<i class="sheyda-wallet-icon-document-text <?php echo self::$PREFIX ?>empty-section-icon"></i>
				<span class="<?php echo self::$PREFIX ?>empty-section-text">
					<?php esc_html_e( 'No withdrawal found.', 'sheyda_wallet' ) ?>
				</span>
			</div>
		<?php } ?>
	</div>
	<?php
}