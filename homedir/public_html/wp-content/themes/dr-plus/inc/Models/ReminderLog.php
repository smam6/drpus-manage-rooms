<?php
namespace DrPlus\Model;

use DrPlus\Casts\Mobile;
use MJ\WPORM\Blueprint;
use MJ\WPORM\Model;

class ReminderLog extends Model {
	protected $table = 'drplus_reminder_log';
	protected $fillable = ['id', 'order_id', 'timing_id', 'receiver_type', 'to', 'send_time', 'status', 'variables'];
	protected $guarded = ['id'];
	protected $casts = [
		'order_id'	=> 'int',
		'to'		=> Mobile::class,
		'send_time'	=> 'datetime',
		'variables'	=> 'array',
	];

	public function up( Blueprint $table ) {
		$table->id();
		$table->integer( 'order_id' );
		$table->tinyText( 'timing_id' );
		$table->string( 'receiver_type', 20 );
		$table->string( 'to', 11 );
		$table->datetime( 'send_time' );
		$table->string( 'status', 20 )->nullable();
		$table->longText( 'variables' )->nullable();
		$table->timestamps();

		$table->index( 'status' );
		$table->index( 'send_time' );

		$this->schema = $table->toSql();
	}
}