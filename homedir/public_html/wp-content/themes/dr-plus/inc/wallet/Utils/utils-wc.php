<?php
namespace Sheyda\Wallet\Utils;

use MJ\Whitebox\Utils\Users;
use SheydaWalletUtils as WalletUtils;

class WC extends WalletUtils {
	public static function update_user_financial_accounts( $data, $user_id = 0 ) {
		$user_id = Users::get_user_id( $user_id );
		$data = apply_filters( 'sheyda/wallet/update_user_financial_accounts', $data, $user_id );
		update_user_meta( $user_id, '_sheyda_wallet_financial_accounts', $data );
	}

	public static function get_user_financial_accounts( $user_id = 0 ) {
		$user_id = Users::get_user_id( $user_id );
		$data = get_user_meta( $user_id, '_sheyda_wallet_financial_accounts', true );
		if( empty( $data ) || !is_array( $data ) ) $data = [];
		return apply_filters( 'sheyda/wallet/get_user_financial_accounts', $data, $user_id );
	}
}