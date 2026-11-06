<?php
namespace Sheyda\Wallet\Models;

use MJ\WPORM\Blueprint;
use MJ\WPORM\Model;

class Ledger extends Model {
	protected $table = 'sheyda_wallet_ledger';
	protected $fillable = ['id', 'user_id', 'type', 'amount', 'balance_after', 'created_by', 'related_id', 'meta', 'created_at'];
	protected $guarded = ['id'];
	protected $timestamps = false;
	protected $casts = [
		'user_id'		=> 'int',
		'amount'		=> 'float',
		'balance_after'	=> 'float',
		'created_by'	=> 'int',
		'related_id'	=> 'int',
		'meta'			=> 'array',
		'created_at'	=> 'datetime',
	];

	public function up( Blueprint $table ) {
		$table->id();
		$table->integer( 'user_id' );
		$table->string( 'type' );
		$table->decimal( 'amount', 20, 8 );
		$table->decimal( 'balance_after', 20, 8 );
		$table->integer( 'created_by' );
		$table->integer( 'related_id' )->nullable();
		$table->longText( 'meta' )->nullable();
		$table->dateTimeWithDefaultCurrentOnUpdate( 'created_at' );
		$table->index( 'user_id' );
		$table->index( 'type' );
		$table->index( 'created_at' );
		$this->schema = $table->toSql();
	}

	public static function types() {
		$types = [
			'topup'				=> __( "Topup", 'sheyda_wallet' ),
			'purchase'			=> __( "Purchase", 'sheyda_wallet' ),
			'refund'			=> __( "Refund", 'sheyda_wallet' ),
			'withdraw_request'	=> __( "Withdraw request", 'sheyda_wallet' ),
			'withdraw_paid'		=> __( "Withdraw paid", 'sheyda_wallet' ),
			'withdraw_reject'	=> __( "Withdraw reject", 'sheyda_wallet' ),
			'transfer_in'		=> __( "Transfer in", 'sheyda_wallet' ),
			'transfer_out'		=> __( "Transfer out", 'sheyda_wallet' ),
			'adjust_credit'		=> __( "Adjust credit", 'sheyda_wallet' ),
			'adjust_debit'		=> __( "Adjust debit", 'sheyda_wallet' ),
			'lock'				=> __( "Lock", 'sheyda_wallet' ),
			'unlock'			=> __( "Unlock", 'sheyda_wallet' ),
			'fee'				=> __( "Fee", 'sheyda_wallet' ),
		];

		return apply_filters( 'sheyda/wallet/ledger/types', $types );
	}

	public static function withdraw_types() {
		$withdraw_types = ['withdraw_request', 'withdraw_paid', 'withdraw_reject'];
		return apply_filters( 'sheyda/wallet/ledger/withdraw_types', $withdraw_types );
	}

	public static function transfer_types() {
		$transfer_types = ['transfer_in', 'transfer_out'];
		return apply_filters( 'sheyda/wallet/ledger/transfer_types', $transfer_types );
	}

	public static function adjust_types() {
		$adjust_types = ['adjust_credit', 'adjust_debit'];
		return apply_filters( 'sheyda/wallet/ledger/adjust_types', $adjust_types );
	}

	public static function lock_types() {
		$lock_types = ['lock', 'unlock'];
		return apply_filters( 'sheyda/wallet/ledger/lock_types', $lock_types );
	}
}