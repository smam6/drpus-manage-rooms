<?php
namespace DrPlus\Model;

use DrPlus\Casts\Time;
use MJ\WPORM\Blueprint;
use MJ\WPORM\Model;

class Booking extends Model {
	protected $table = 'drplus_booking';
	protected $fillable = ['book_id', 'customer_id', 'specialist_id', 'office_id', 'date', 'start_time', 'end_time', 'total_price', 'commission', 'specialist_income', 'order_id', 'order_status'];
	protected $primaryKey = 'book_id';
	protected $guarded = ['book_id'];
	protected $casts = [
		'customer_id'		=> 'int',
		'specialist_id'		=> 'int',
		'start_time'		=> Time::class,
		'end_time'			=> Time::class,
		'total_price'		=> 'float',
		'commission'		=> 'float',
		'specialist_income'	=> 'float',
		'order_id'			=> 'int',
	];

	public function up( Blueprint $table ) {
		$table->id('book_id');
		$table->integer( 'customer_id' );
		$table->integer( 'specialist_id' );
		$table->integer( 'office_id' );
		$table->date( 'date' );
		$table->time( 'start_time' );
		$table->time( 'end_time' );
		$table->decimal( 'total_price', 20, 8 );
		$table->decimal( 'commission', 20, 8 )->nullable();
		$table->decimal( 'specialist_income', 20, 8 )->nullable();
		$table->integer( 'order_id' )->nullable();
		$table->string( 'order_status', 50 );
		$table->timestamps();

		$table->index( 'specialist_id' );
		$table->index( ['specialist_id', 'order_status'] );
		$table->index( '`date`' );
		$table->index( ['customer_id', 'order_status'] );
		$table->index( ['specialist_id', 'office_id', '`date`', 'start_time', 'order_status'] );
		$table->index( 'office_id' );

		$this->schema = $table->toSql();
	}
}