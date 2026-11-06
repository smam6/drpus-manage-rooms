<?php
namespace DrPlus\Model;

use MJ\WPORM\Blueprint;
use MJ\WPORM\Model;

class ChatSession extends Model {
	protected $table = 'drplus_chat_sessions';
	protected $fillable = ['id', 'user_1_id', 'user_2_id', 'context_id', 'subject', 'is_closed', 'open_at', 'closed_at', 'created_at', 'updated_at'];
	protected $guarded = ['id'];
	protected $casts = [
		'user_1_id'		=> 'int',
		'user_2_id'		=> 'int',
		'context_id'	=> 'int',
		'is_closed'		=> 'bool'
	];

	public function up( Blueprint $table ) {
		$table->id();
		$table->integer('user_1_id'); // specialist user id
		$table->integer('user_2_id'); // customer user id
		$table->integer('context_id');
		$table->string( 'subject' )->nullable();
		$table->tinyInteger('is_closed')->default(0);
		$table->timestamp('open_at');
		$table->timestamp('closed_at');
		$table->timestamps();

		$table->index( 'context_id' );
		$table->index( ['user_1_id', 'user_2_id'] );

		$this->schema = $table->toSql();
	}
}
