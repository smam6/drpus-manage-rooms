<?php
namespace DrPlus\Model;

use DrPlus\Casts\Time;
use MJ\WPORM\Blueprint;
use MJ\WPORM\Model;

class Times extends Model {
	protected $table = 'drplus_times';
	protected $fillable = ['id', 'user_id', 'office', 'day', 'from', 'to', 'use_default', 'status', 'creator'];
	protected $guarded = ['id'];
	protected $casts = [
		'user_id'		=> 'int',
		'from'			=> Time::class,
		'to'			=> Time::class,
		'use_default'	=> 'bool',
		'status'		=> 'bool',
		'creator'		=> 'int'
	];

	public function up( Blueprint $table ) {
		$table->id();
		$table->integer( 'user_id' );
		$table->string( 'office', 50 ); // utf8 issue
		$table->tinyText( 'day' );
		$table->time( 'from' )->nullable();
		$table->time( 'to' )->nullable();
		$table->boolean( 'use_default' );
		$table->boolean( 'status' );
		$table->integer( 'creator' );
		$table->timestamps();

		$table->index( ['office', 'user_id'] );

		$this->schema = $table->toSql();
	}

	public static function bootIfNotBooted() {
		parent::bootIfNotBooted();

		static::addGlobalScope('actives', function($query) {
            $query->where('status', 1);
        });
	}

	public function creating() {
		$this->creator = is_user_logged_in() ? get_current_user_id() : 0;
	}

	public function specialists() {
		$this->belongsTo( Specialists::class, 'user_id', 'user_id' );
	}
}