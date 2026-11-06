<?php
use MJ\Whitebox\Utils\Formatters;
use Sheyda\Wallet\Utils\Settings;
use SheydaWalletUtils as WalletUtils;

$topup_settings = Settings::get_settings( 'topup' );
$amounts = apply_filters( 'sheyda/wallet/topup/predefined_amounts', $topup_settings['predefined_amounts'] );
?>
<div class="<?php echo self::$PREFIX ?>topup-form <?php echo self::$PREFIX ?>section">
	<div class="<?php echo self::$PREFIX ?>topup-title-wrap <?php echo self::$PREFIX ?>section-title-wrap">
		<i class="sheyda-wallet-icon-wallet-add"></i>
		<span class="<?php echo self::$PREFIX ?>topup-title <?php echo self::$PREFIX ?>section-title"><?php esc_html_e( 'Increase wallet balance', 'sheyda_wallet' ) ?></span>
	</div>
	<span class="<?php echo self::$PREFIX ?>topup-text"><?php printf( esc_html__( 'Amount (%s)', 'sheyda_wallet' ), WalletUtils::get_price_symbol() ) ?>:</span>
	<?php if( !empty( $amounts ) ) { ?>
		<div class="<?php echo self::$PREFIX ?>topup-predefined-amounts">
			<?php
			foreach( $amounts as $amount ) {
				?>
				<button class="<?php echo self::$PREFIX ?>topup-predefined-amount-btn" type="button" data-amount="<?php echo esc_attr( $amount ) ?>"><?php echo Formatters::price( $amount, false, WalletUtils::get_price_symbol() ) ?></button>
				<?php
			}
			?>
		</div>
	<?php } ?>
	<div class="<?php echo self::$PREFIX ?>topup-amount-field-wrap">
		<?php if( !empty( $amounts ) ) { ?>
			<div class="<?php echo self::$PREFIX ?>topup-amount-field-separator-wrap">
				<span class="<?php echo self::$PREFIX ?>topup-amount-field-separator"><?php esc_html_e( 'OR', 'sheyda_wallet' ) ?></span>
			</div>
		<?php } ?>
		<input type="text" id="<?php echo self::$PREFIX ?>topup-amount-field" class="sheyda-wallet-price-input ltr" placeholder="<?php echo esc_html__( 'Enter your desired amount...', 'sheyda_wallet' ) ?>" inputmode="numeric" autocomplete="off">
	</div>
	<button id="<?php echo self::$PREFIX ?>topup-submit" class="<?php echo self::$PREFIX ?>topup-submit sheyda_wallet_button" type="button" data-nonce="<?php echo wp_create_nonce( 'sheyda_wallet_process_topup' ) ?>">
		<span class="button-text"><?php esc_html_e( 'Increase wallet balance', 'sheyda_wallet' ) ?></span>
		<div class="button-loading"><?php echo file_get_contents( SHEYDA_WALLET_DIR . "assets/images/loading.svg" ) ?></div>
	</button>
	<div class="<?php echo self::$PREFIX ?>topup-errors"></div>
</div>