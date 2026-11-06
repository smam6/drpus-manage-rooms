<?php
namespace Sheyda\Wallet\Backend;

use Sheyda\Wallet\Utils\AdminUI;

class WalletAdjust {
	public static function view( $wallet_id ) {
		$user = get_user_by( 'id', $wallet_id );
		settings_errors( "sheyda-wallet-adjust" );
		?>
		<div id="sheyda-wallet-header">
			<h1 class="wp-heading-inline"><?php printf( esc_html__( 'Adjust %s balance', 'sheyda_wallet' ), $user->display_name ) ?></h1>
			<hr class="wp-header-end">
		</div>
		<form action="" method="post" id="wallet-adjust-form">
			<table class="form-table">
				<?php wp_nonce_field( 'sheyda_adjust_wallet_nonce_value', 'sheyda_adjust_wallet_nonce' ) ?>
				<input type="hidden" name="section" value="adjust_wallet">
				<input type="hidden" name="user_id" value="<?php echo $wallet_id ?>">
				<tbody>
					<tr>
						<th><?php esc_html_e( 'Type', 'sheyda_wallet' ) ?></th>
						<td>
							<?php
							AdminUI::select_with_label( [
								'label'		=> esc_html__( 'Type', 'sheyda_wallet' ),
								'name'		=> "adjust_type",
								'id'		=> "adjust_type",
								'classes'	=> ['sheyda-wallet-select2'],
								'options'	=> [
									'credit'	=> esc_html__( 'Increase balance', 'sheyda_wallet' ),
									'debit'		=> esc_html__( 'Decrease balance', 'sheyda_wallet' ),
									'lock'		=> esc_html__( 'Lock balance', 'sheyda_wallet' ),
									'unlock'	=> esc_html__( 'Unlock balance', 'sheyda_wallet' ),
								],
							] )
							?>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Amount', 'sheyda_wallet' ) ?></th>
						<td>
							<?php
							AdminUI::input_with_label( [
								'label'				=> esc_html__( 'Amount', 'sheyda_wallet' ),
								'name'				=> "adjust_amount",
								'id'				=> "adjust_amount",
								'type'				=> 'text',
								'input_classes'		=> ['regular-text', 'sheyda-wallet-price-input', 'ltr'],
								'required'			=> true,
							] );
							?>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Note', 'sheyda_wallet' ) ?></th>
						<td>
							<?php
							AdminUI::input_with_label( [
								'label'				=> esc_html__( 'Note', 'sheyda_wallet' ),
								'name'				=> "adjust_note",
								'id'				=> "adjust_note",
								'type'				=> 'text',
								'input_classes'		=> ['regular-text'],
								'rows'				=> 4,
								'textarea'			=> true
							] );
							?>
						</td>
					</tr>
				</tbody>
			</table>
			<button type="submit" class="button button-primary" id="wallet-adjust-btn"><?php esc_html_e( 'Submit', 'sheyda_wallet' ) ?></button>
		</form>
		<?php
	}
}