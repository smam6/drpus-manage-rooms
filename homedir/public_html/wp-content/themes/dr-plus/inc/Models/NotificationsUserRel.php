<?php
namespace DrPlus\Model;

use MJ\WPORM\Blueprint;
use MJ\WPORM\Model;

class NotificationsUserRel extends Model {
	protected $table = 'drplus_notifications_user_rel';
	protected $fillable = ['id', 'notif_id', 'user_id', 'type'];
	protected $guarded = ['id'];
	protected $timestamps = false;
	protected $casts = [
		'notif_id'	=> 'int',
		'user_id'	=> 'int',
	];

	public function up( Blueprint $table ) {
		$table->id();
		$table->integer( 'notif_id' );
		$table->integer( 'user_id' );
		$table->string( 'type', 11 );

		$table->index( 'notif_id' );
		$table->index( 'user_id' );

		$this->schema = $table->toSql();
	}
}