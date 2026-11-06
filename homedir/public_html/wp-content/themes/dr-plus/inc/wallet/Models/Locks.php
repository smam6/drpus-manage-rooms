<?php
namespace Sheyda\Wallet\Models;

use MJ\WPORM\Blueprint;
use MJ\WPORM\Model;

class Locks extends Model {
	protected $table = 'sheyda_wallet_locks';
	protected $fillable = ['id', 'user_id', 'amount', 'reason', 'expires_at', 'created_at'];
	protected $guarded = ['id'];
	protected $timestamps = false;
	protected $casts = [
		'user_id'		=> 'int',
		'amount'		=> 'float',
		'expires_at'	=> 'datetime',
		'created_at'	=> 'datetime',
	];

	public function up( Blueprint $table ) {
		$table->id();
		$table->integer( 'user_id' );
		$table->decimal( 'amount', 20, 8 );
		$table->string( 'reason' )->nullable();
		$table->datetime( 'expires_at' )->nullable();
		$table->dateTimeWithDefault( 'created_at' );

		$table->index( 'user_id' );

		$this->schema = $table->toSql();
	}
}