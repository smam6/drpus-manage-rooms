<?php
namespace Sheyda\Wallet\Models;

use MJ\WPORM\Blueprint;
use MJ\WPORM\Model;

// این جدول به ازای هر کاربر فقط یک رکورد ذخیره میکند
class Balances extends Model {
	protected $table = 'sheyda_wallet_balances';
	protected $fillable = ['id', 'user_id', 'balance', 'locked', 'updated_at'];
	protected $guarded = ['id'];
	protected $timestamps = false;
	protected $casts = [
		'user_id'		=> 'int',
		'balance'		=> 'float', // Total balance
		'locked'		=> 'float',
		'updated_at'	=> 'datetime',
	];

	public function up( Blueprint $table ) {
		$table->id();
		$table->integer( 'user_id' )->unique();
		$table->decimal( 'balance', 20, 8 );
		$table->decimal( 'locked', 20, 8 );
		$table->dateTimeWithDefaultCurrentOnUpdate( 'updated_at' );
		$this->schema = $table->toSql();
	}
}