<?php
namespace Sheyda\Wallet\Models;

use SheydaWalletUtils as WalletUtils;
use MJ\WPORM\Blueprint;
use MJ\WPORM\Model;
use MJ\WPROM\ExtraCasts\CustomEnum;

class Withdrawals extends Model {
	protected $table = 'sheyda_wallet_withdrawals';
	protected $fillable = ['id', 'user_id', 'amount_requested', 'fee', 'amount_net', 'status', 'bank_info', 'admin_notes'];
	protected $guarded = ['id'];
	protected $casts = [
		'user_id'			=> 'int',
		'amount_requested'	=> 'float',
		'fee'				=> 'float',
		'amount_net'		=> 'float',
		'status'			=> [CustomEnum::class, ['pending','approved','paid','rejected']],
		'bank_info'			=> 'array',
		'created_at'		=> 'datetime',
		'updated_at'		=> 'datetime',
	];

	public function up( Blueprint $table ) {
		$table->id();
		$table->integer( 'user_id' );
		$table->decimal( 'amount_requested', 20, 8 );
		$table->decimal( 'fee', 20, 8 );
		$table->decimal( 'amount_net', 20, 8 );
		$table->enum( 'status', array_keys( self::statuses() ) );
		$table->longText( 'bank_info' );
		$table->text( 'admin_notes' )->nullable();
		$table->timestamps();

		$table->index( 'user_id' );
		$table->index( 'status' );

		$this->schema = $table->toSql();
	}

	private function saving() {
		$this->status = WalletUtils::ensure_values_in_array( $this->status, array_keys( self::statuses() ), 'pending' ); // Default
	}

	public function updating() {
		$this->saving();
	}

	public function creating() {
		$this->saving();
	}

	public static function statuses() {
		return [
			'pending'	=> __( "Pending", 'sheyda_wallet' ),
			'approved'	=> __( "Approved", 'sheyda_wallet' ),
			'paid'		=> __( "Paid", 'sheyda_wallet' ),
			'rejected'	=> __( "Rejected", 'sheyda_wallet' ),
		];
	}
}