<?php
namespace DrPlus\Model;

use DrPlus\Casts\Mobile;
use MJ\WPORM\Blueprint;
use MJ\WPORM\Model;

class OTP extends Model {
	protected $table = 'drplus_otp';
	protected $fillable = ['id', 'mobile', 'otp', 'expire'];
	protected $guarded = ['id'];
	protected $timestamps = false;
	protected $casts = [
		'mobile'	=> Mobile::class,
		'otp'		=> 'int',
		'expire'	=> 'datetime',
	];

	public function up( Blueprint $table ) {
		$table->id();
		$table->string( 'mobile', 11 );
		$table->integer( 'otp' );
		$table->datetime( 'expire' );
		$this->schema = $table->toSql();
	}
}