<?php
namespace Sheyda\Wallet\Backend;

use MJ\Whitebox\Utils\Formatters;
use Sheyda\Wallet\AdminScripts;
use Sheyda\Wallet\Backend\Pages\ListTables\Withdrawals;
use Sheyda\Wallet\Models\Withdrawals as ModelsWithdrawals;
use Sheyda\Wallet\PublicScripts;
use Sheyda\Wallet\Utils\AdminUI;
use SheydaWalletUtils as WalletUtils;

class WalletWithdrawals {
	public static function view() {
		if( !empty( $_GET['id'] ) ) {
			self::single_view();
		} else {
			self::list_view();
		}
	}

	public static function list_view() {
		?>
		<div id="sheyda-wallet-header">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Wallet withdrawals', 'sheyda_wallet' ) ?></h1>
			<hr class="wp-header-end">
		</div>
		<?php
		include_once( SHEYDA_WALLET_DIR . "Backend/Pages/ListTables/Withdrawals.php" );
		$table = new Withdrawals;
		$table->show_all = true;
		$table->prepare_items();
		?>
		<form method="GET" id="posts-filter">
			<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ?? '' ); ?>">
			<?php if( !empty( $_REQUEST['section'] ) ) { ?>
				<input type="hidden" name="section" value="<?php echo esc_attr( $_REQUEST['section'] ); ?>">
			<?php } ?>
			<?php
			$table->views();
			$table->search_box( __( 'Search user', 'sheyda_wallet' ), 'search_user' );
			$table->display();
			?>
		</form>
		<?php
	}

	public static function single_view() {
		$id = WalletUtils::convert_chars( $_GET['id'], 'absint' );

		$withdrawal = ModelsWithdrawals::find( $id );
		?>
		<div id="sheyda-wallet-header">
			<h1 class="wp-heading-inline"><?php printf( esc_html__( 'withdrawal #%s', 'sheyda_wallet' ), $id ) ?></h1>
			<hr class="wp-header-end">
		</div>
		<?php if( !empty( $withdrawal ) ) { ?>
			<div class="sheyda-wallet-withdrawal-single-wrap">
				<div class="sheyda-wallet-withdrawal-single-table">
					<table class="form-table">
						<tbody>
							<tr class="withdrawal-user">
								<th><?php esc_html_e( 'Requesting user', 'sheyda_wallet' ) ?></th>
								<td><?php echo get_user_by( 'id', $withdrawal['user_id'] )->display_name; ?></td>
							</tr>
							<tr class="withdrawal-amount_requested">
								<th><?php esc_html_e( 'Amount received by the user', 'sheyda_wallet' ) ?></th>
								<td>
									<strong><?php echo Formatters::price( $withdrawal->amount_net, true ) ?></strong>
								</td>
							</tr>
							<tr class="withdrawal-status">
								<th><?php esc_html_e( 'Status', 'sheyda_wallet' ) ?></th>
								<td>
									<span class="status-<?php echo $withdrawal->status ?>"><?php echo ModelsWithdrawals::statuses()[$withdrawal->status] ?></span>
								</td>
							</tr>
							<tr class="withdrawal-amount_details">
								<th><?php esc_html_e( 'Details', 'sheyda_wallet' ) ?></th>
								<td>
									<p class="withdrawal-detail-item"><?php printf( esc_html__( 'Total amount: %s', 'sheyda_wallet' ), Formatters::price( $withdrawal->amount_requested, true ) ) ?></p>
									<p class="withdrawal-detail-item"><?php printf( esc_html__( 'Fee: %s', 'sheyda_wallet' ), Formatters::price( $withdrawal->fee, true ) ) ?></p>
								</td>
							</tr>
							<tr class="withdrawal-bank_info">
								<th><?php esc_html_e( 'Bank Info', 'sheyda_wallet' ) ?></th>
								<td>
									<p class="withdrawal-bank_info-item"><?php printf( esc_html( '%s: %s', 'sheyda_wallet' ), WalletUtils::get_financial_types()[$withdrawal->bank_info['type']], $withdrawal->bank_info['number'] ) ?></p>
									<p class="withdrawal-bank_info-item"><?php printf( esc_html__( 'Owner: %s', 'sheyda_wallet' ), $withdrawal->bank_info['owner'] ) ?></p>
								</td>
							</tr>
							<?php if( !empty( $withdrawal->admin_notes ) ) { ?>
								<tr class="withdrawal-admin_notes">
									<th><?php esc_html_e( 'Admin note', 'sheyda_wallet' ) ?></th>
									<td><?php echo wpautop( $withdrawal->admin_notes ) ?></td>
								</tr>
							<?php } ?>
							<tr class="withdrawal-created-at">
								<th><?php esc_html_e( 'Created at', 'sheyda_wallet' ) ?></th>
								<td>
									<?php
									$created_at_text = '<div>' . date_i18n( 'Y-m-d', $withdrawal->created_at->format( 'U' ) ) . '</div>';
									$created_at_text .= '<small>' . date_i18n( 'H:i:s',$withdrawal->created_at->format( 'U' ) ) . '</small>';
									echo $created_at_text;
									?>
								</td>
							</tr>
							<?php if( $withdrawal->updated_at != $withdrawal->created_at ) { ?>
								<tr class="withdrawal-updated-at">
									<th><?php esc_html_e( 'Updated at', 'sheyda_wallet' ) ?></th>
									<td>
										<?php
										$created_at_text = '<div>' . date_i18n( 'Y-m-d', $withdrawal->updated_at->format( 'U' ) ) . '</div>';
										$created_at_text .= '<small>' . date_i18n( 'H:i:s',$withdrawal->updated_at->format( 'U' ) ) . '</small>';
										echo $created_at_text;
										?>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
				<form action="" method="post" class="sheyda-wallet-withdrawal-update-status-form">
					<?php wp_nonce_field( "sheyda_wallet_update_withdrawal_{$withdrawal->id}", "sheyda_wallet_update_withdrawal" ) ?>
					<h2 class="sheyda-wallet-withdrawal-single-statuses-title"><?php esc_html_e( 'Change Status to:', 'sheyda_wallet' ) ?></h2>
					<table class="form-table">
						<tbody>
							<tr>
								<th>
									<label for="sheyda-wallet-withdrawal-single-status"><?php esc_html_e( 'Status', 'sheyda_wallet' ) ?></label>
								</th>
								<td>
									<?php
									AdminUI::select_with_label( [
										'label'		=> esc_html__( 'Type', 'sheyda_wallet' ),
										'name'		=> "sheyda_wallet_withdrawal_status",
										'id'		=> "sheyda-wallet-withdrawal-single-status",
										'classes'	=> ['sheyda-wallet-select2'],
										'options'	=> ModelsWithdrawals::statuses(),
										'value'		=> $withdrawal->status,
										'required'	=> true,
									] );
									?>
								</td>
							</tr>
							<tr>
								<th>
									<label for="sheyda-wallet-withdrawal-single-admin-note"><?php esc_html_e( 'Admin note', 'sheyda_wallet' ) ?></label>
								</th>
								<td>
									<?php
									AdminUI::input_with_label( [
										'label'				=> esc_html__( 'Admin note', 'sheyda_wallet' ),
										'name'				=> "sheyda_wallet_withdrawal_admin_note",
										'id'				=> "sheyda-wallet-withdrawal-single-admin-note",
										'type'				=> 'text',
										'input_classes'		=> ['regular-text'],
										'rows'				=> 4,
										'textarea'			=> true,
										'value'				=> $withdrawal->admin_notes
									] );
									?>
									<p class="description"><?php esc_html_e( 'This note will replace the previous note.', 'sheyda_wallet' ) ?></p>
								</td>
							</tr>
							<tr>
								<th></th>
								<td>
									<button type="submit" id="sheyda-wallet-withdrawal-update-status-btn" class="button button-primary"><?php esc_html_e( 'Update withdrawal status', 'sheyda_wallet' ) ?></button>
								</td>
							</tr>
						</tbody>
					</table>
				</form>
			</div>
		<?php } else { ?>
			<p><?php esc_html_e( 'Failed to get withdrawal info', 'sheyda_wallet' ) ?></p>
		<?php }
	}

	public static function enqueue() {
		PublicScripts::select2();
		AdminScripts::form_group();
		wp_enqueue_style( 'sheyda-wallet-withdrawals', SHEYDA_WALLET_URI . "assets/css/backend/wallet-withdrawals.min.css", [], SHEYDA_WALLET_VERSION );
		if( SHEYDA_WALLET_DEV ) {
			wp_enqueue_script( 'sheyda-wallet-withdrawals', SHEYDA_WALLET_URI . "assets/js/backend/withdrawals.js", ['jquery'], SHEYDA_WALLET_VERSION, true );
		} else {
			wp_enqueue_script( 'sheyda-wallet-withdrawals', SHEYDA_WALLET_URI . "assets/js/backend/withdrawals.min.js", ['jquery'], SHEYDA_WALLET_VERSION, true );
		}
	}

	public static function save() {
		if( empty( $_POST['sheyda_wallet_update_withdrawal'] ) || empty( $_GET['id'] ) ) return;
	
		$id = WalletUtils::convert_chars( $_GET['id'], 'absint' );
		$nonce = WalletUtils::convert_chars( $_POST['sheyda_wallet_update_withdrawal'] );
		if( !wp_verify_nonce( $nonce, "sheyda_wallet_update_withdrawal_{$id}" ) ) return;
		
		$status = WalletUtils::convert_chars( $_POST['sheyda_wallet_withdrawal_status'] );
		if( !in_array( $status, array_keys( ModelsWithdrawals::statuses() ) ) ) return;

		$note = sanitize_textarea_field( $_POST['sheyda_wallet_withdrawal_admin_note'] );
		
		// Update withdrawal record
		$withdrawal = ModelsWithdrawals::find( $id );
		if( empty( $withdrawal ) ) return;

		// set status label
		$status_labels = ModelsWithdrawals::statuses();
		$previous_status = $withdrawal->status;
		$previous_status_label = $status_labels[$withdrawal->status];
		$withdrawal->status = $status;
		$withdrawal->admin_notes = $note;
		$withdrawal->save();


		// Add ledger record
		if( $status == 'approved' || $status == $previous_status || $status == 'pending' ) return;
		$current_user = get_user_by( 'id', get_current_user_id() );
		$meta = [
			'fee'			=> $withdrawal->fee,
			'description'	=> sprintf( __( 'Status change from <strong>%s</strong> to <strong>%s</strong> by %s.<br> Admin notes: %s', 'sheyda_wallet' ), $previous_status_label, $status_labels[$status], $current_user->display_name, $note )
		];
		if( $status == 'paid' ) {
			if( $previous_status == 'rejected' ) {
				// Lock amount again
				WalletUtils::add_user_lock_record( $withdrawal->amount_requested, $withdrawal->user_id, get_current_user_id(), $withdrawal->id, ['fee' => $withdrawal->fee] );
			}
			WalletUtils::add_user_withdraw_paid_record( $withdrawal->amount_net, $withdrawal->user_id, get_current_user_id(), $withdrawal->id, $meta );
			if( !empty( $withdrawal->fee ) ) {
				WalletUtils::add_user_unlock_record( $withdrawal->fee, $withdrawal->user_id, get_current_user_id(), $withdrawal->id, ['description' => sprintf( __( 'Unlock fee of withdrawal %s to withdraw', 'sheyda_wallet' ), $withdrawal->id ), 'relation_type' => 'withdrawal'] );
				WalletUtils::add_user_fee_record( $withdrawal->fee, $withdrawal->user_id, get_current_user_id(), $withdrawal->id, ['description' => sprintf( __( 'Fee of withdrawal %s', 'sheyda_wallet' ), $withdrawal->id ) , 'relation_type' => 'withdrawal'] );
			}
		} else if( $status == 'rejected' ) {
			WalletUtils::add_user_withdraw_reject_record( $withdrawal->amount_requested, $withdrawal->user_id, get_current_user_id(), $withdrawal->id, $meta );
		}
	}
}
