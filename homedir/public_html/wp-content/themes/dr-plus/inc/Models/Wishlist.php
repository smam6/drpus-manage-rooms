<?php
namespace DrPlus\Model;

use MJ\WPORM\Blueprint;
use MJ\WPORM\Model;

class Wishlist extends Model {
	protected $table = 'drplus_wishlist';
	protected $fillable = ['id', 'product_id', 'user_id'];
	protected $guarded = ['id'];
	protected $timestamps = false;
	protected $casts = [
		'product_id'	=> 'int',
		'user_id'		=> 'int',
	];

	public function up( Blueprint $table ) {
		$table->id();
		$table->integer( 'product_id' );
		$table->integer( 'user_id' );

		$table->index( 'product_id' );
		$table->index( 'user_id' );

		$this->schema = $table->toSql();
	}
}