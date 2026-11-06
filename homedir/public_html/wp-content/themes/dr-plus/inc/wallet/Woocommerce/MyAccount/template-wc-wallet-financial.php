<?php

use Sheyda\Wallet\Utils\WC;
use SheydaWalletUtils as WalletUtils;

$user_accounts = WC::get_user_financial_accounts();

$is_fa = get_locale() == 'fa_IR';
?>
<form action="" method="post" class="<?php echo self::$PREFIX ?>financial-section <?php echo self::$PREFIX ?>section">
	<input type="hidden" name="action" value="sheyda_wallet_financial_form">
	<?php wp_nonce_field( self::$PREFIX . "financial_nonce", self::$PREFIX . "financial_nonce_value" ) ?>
	<div class="<?php echo self::$PREFIX ?>financial-title-wrap <?php echo self::$PREFIX ?>section-title-wrap">
		<i class="sheyda-wallet-icon-cardmoney"></i>
		<p class="<?php echo self::$PREFIX ?>financial-title <?php echo self::$PREFIX ?>section-title"><?php esc_html_e( 'Financial info', 'sheyda_wallet' ) ?></p>
	</div>
	<p class="<?php echo self::$PREFIX ?>financial-desc"><?php esc_html_e( 'Please enter at least one account number for use in withdrawal requests', 'sheyda_wallet' ) ?></p>

	<div class="<?php echo self::$PREFIX ?>financial-accounts">
		<?php foreach( array_values( $user_accounts ) as $index => $user_account ) { ?>
			<div class="<?php echo self::$PREFIX ?>financial-account">
				<div class="<?php echo self::$PREFIX ?>financial-account-field-wrap">
					<label class="<?php echo self::$PREFIX ?>financial-account_field_label" for="<?php echo self::$PREFIX ?>financial_account_type_<?php echo $index ?>"><?php esc_html_e( 'Type', 'sheyda_wallet' ) ?></label>
					<select class="<?php echo self::$PREFIX ?>financial-account_type" name="<?php echo self::$PREFIX ?>financial_account[<?php echo $index ?>][type]" id="<?php echo self::$PREFIX ?>financial_account_type_<?php echo $index ?>">
						<?php foreach( WalletUtils::get_financial_types() as $financial_type => $financial_type_label ) { ?>
							<option value="<?php echo $financial_type ?>" <?php echo selected( $financial_type, $user_account['type'] ) ?>><?php echo $financial_type_label ?></option>
						<?php } ?>
					</select>
				</div>
				<i class="<?php echo self::$PREFIX ?>financial-account-remove sheyda-wallet-icon-trash"></i>
				<div class="<?php echo self::$PREFIX ?>financial-account-field-wrap <?php echo self::$PREFIX ?>financial-account-card-number">
					<label class="<?php echo self::$PREFIX ?>financial-account_card_number_label <?php echo self::$PREFIX ?>financial-account_field_label" for="<?php echo self::$PREFIX ?>financial_account_card_number_<?php echo $index ?>" data-type="card" <?php echo $user_account['type'] != 'card' ? ' style="display:none"' : "" ?>><?php esc_html_e( 'Card Number', 'sheyda_wallet' ) ?></label>
					<label class="<?php echo self::$PREFIX ?>financial-account_card_number_label <?php echo self::$PREFIX ?>financial-account_field_label" for="<?php echo self::$PREFIX ?>financial_account_card_number_<?php echo $index ?>" data-type="shaba" <?php echo $user_account['type'] != 'shaba' ? ' style="display:none"' : "" ?>><?php esc_html_e( 'Shaba Number', 'sheyda_wallet' ) ?></label>
					<label class="<?php echo self::$PREFIX ?>financial-account_card_number_label <?php echo self::$PREFIX ?>financial-account_field_label" for="<?php echo self::$PREFIX ?>financial_account_card_number_<?php echo $index ?>" data-type="account" <?php echo $user_account['type'] != 'account' ? ' style="display:none"' : "" ?>><?php esc_html_e( 'Account Number', 'sheyda_wallet' ) ?></label>
					<input type="text" class="<?php echo self::$PREFIX ?>financial-account_field ltr <?php echo $is_fa ? 'sheyda-wallet-check-account-number' : 'sheyda-wallet-numeric-input' ?>" id="<?php echo self::$PREFIX ?>financial_account_card_number_<?php echo $index ?>" name="<?php echo self::$PREFIX ?>financial_account[<?php echo $index ?>][number]" value="<?php echo $user_account['number'] ?>">
				</div>
				<div class="<?php echo self::$PREFIX ?>financial-account-field-wrap">
					<label class="<?php echo self::$PREFIX ?>financial-account_field_label" for="<?php echo self::$PREFIX ?>financial_account_owner_<?php echo $index ?>"><?php esc_html_e( 'Account owner', 'sheyda_wallet' ) ?></label>
					<input type="text" class="<?php echo self::$PREFIX ?>financial-account_field" id="<?php echo self::$PREFIX ?>financial_account_owner_<?php echo $index ?>" name="<?php echo self::$PREFIX ?>financial_account[<?php echo $index ?>][owner]" value="<?php echo $user_account['owner'] ?>">
				</div>
			</div>
		<?php } ?>
	</div>

	<button id="<?php echo self::$PREFIX ?>financial-new-account-btn" class="<?php echo self::$PREFIX ?>financial-new-account-btn sheyda_wallet_button button-action button-small" type="button" data-nonce="<?php echo wp_create_nonce( 'sheyda_wallet_process_topup' ) ?>">
		<i class="button-icon sheyda-wallet-icon-plus"></i>
		<span class="button-text"><?php esc_html_e( 'Add new account', 'sheyda_wallet' ) ?></span>
	</button>

	<button id="<?php echo self::$PREFIX ?>financial-submit" class="<?php echo self::$PREFIX ?>financial-submit sheyda_wallet_button" type="submit" data-nonce="<?php echo wp_create_nonce( 'sheyda_wallet_process_topup' ) ?>">
		<span class="button-text"><?php esc_html_e( 'Submit', 'sheyda_wallet' ) ?></span>
	</button>
</form>

<script type="text/html" id="tmpl-sheyda-wallet-financial-account">
	<div class="<?php echo self::$PREFIX ?>financial-account">
		<div class="<?php echo self::$PREFIX ?>financial-account-field-wrap">
			<label class="<?php echo self::$PREFIX ?>financial-account_field_label" for="<?php echo self::$PREFIX ?>financial_account_type_{{{data.index}}}"><?php esc_html_e( 'Type', 'sheyda_wallet' ) ?></label>
			<select class="<?php echo self::$PREFIX ?>financial-account_type" name="<?php echo self::$PREFIX ?>financial_account[{{{data.index}}}][type]" id="<?php echo self::$PREFIX ?>financial_account_type_{{{data.index}}}">
				<?php foreach( WalletUtils::get_financial_types() as $financial_type => $financial_type_label ) { ?>
					<option value="<?php echo $financial_type ?>" <?php echo selected( $financial_type, 'card' ) ?>><?php echo $financial_type_label ?></option>
				<?php } ?>
			</select>
		</div>
		<i class="<?php echo self::$PREFIX ?>financial-account-remove sheyda-wallet-icon-trash"></i>
		<div class="<?php echo self::$PREFIX ?>financial-account-field-wrap <?php echo self::$PREFIX ?>financial-account-card-number">
			<label class="<?php echo self::$PREFIX ?>financial-account_card_number_label <?php echo self::$PREFIX ?>financial-account_field_label" for="<?php echo self::$PREFIX ?>financial_account_card_number_{{{data.index}}}" data-type="card"><?php esc_html_e( 'Card Number', 'sheyda_wallet' ) ?></label>
			<label class="<?php echo self::$PREFIX ?>financial-account_card_number_label <?php echo self::$PREFIX ?>financial-account_field_label" for="<?php echo self::$PREFIX ?>financial_account_card_number_{{{data.index}}}" data-type="shaba" style="display:none"><?php esc_html_e( 'Shaba Number', 'sheyda_wallet' ) ?></label>
			<label class="<?php echo self::$PREFIX ?>financial-account_card_number_label <?php echo self::$PREFIX ?>financial-account_field_label" for="<?php echo self::$PREFIX ?>financial_account_card_number_{{{data.index}}}" data-type="account" style="display:none"><?php esc_html_e( 'Account Number', 'sheyda_wallet' ) ?></label>
			<input type="text" class="<?php echo self::$PREFIX ?>financial-account_field ltr <?php echo $is_fa ? 'sheyda-wallet-check-account-number' : 'sheyda-wallet-numeric-input' ?>" id="<?php echo self::$PREFIX ?>financial_account_card_number_{{{data.index}}}" name="<?php echo self::$PREFIX ?>financial_account[{{{data.index}}}][number]">
		</div>
		<div class="<?php echo self::$PREFIX ?>financial-account-field-wrap">
			<label class="<?php echo self::$PREFIX ?>financial-account_field_label" for="<?php echo self::$PREFIX ?>financial_account_owner_{{{data.index}}}"><?php esc_html_e( 'Account owner', 'sheyda_wallet' ) ?></label>
			<input type="text" class="<?php echo self::$PREFIX ?>financial-account_field" id="<?php echo self::$PREFIX ?>financial_account_owner_{{{data.index}}}" name="<?php echo self::$PREFIX ?>financial_account[{{{data.index}}}][owner]">
		</div>
	</div>
</script>