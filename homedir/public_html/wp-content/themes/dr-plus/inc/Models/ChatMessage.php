<?php
namespace DrPlus\Model;

use MJ\WPORM\Blueprint;
use MJ\WPORM\Model;

class ChatMessage extends Model {
	protected $table = 'drplus_chat_messages';
	protected $fillable = ['id', 'session_id', 'sender_id', 'message', 'type', 'file_url', 'is_seen', 'created_at'];
	protected $guarded = ['id'];
	protected $casts = [
		'session_id'	=> 'int',
		'sender_id'		=> 'int',
		'is_seen'		=> 'bool',
	];

	public function up(Blueprint $table) {
		$table->id();
		$table->integer('session_id');
		$table->integer('sender_id');
		$table->text('message');
		$table->string('type', 20)->default('text');
		$table->string('file_url', 255)->nullable();
		$table->tinyInteger('is_seen')->default(0);
		$table->timestamps();

		$table->index( 'session_id' );

		$this->schema = $table->toSql();
	}
}
