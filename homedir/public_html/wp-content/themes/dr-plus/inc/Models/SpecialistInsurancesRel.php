<?php
namespace DrPlus\Model;

use MJ\WPORM\Blueprint;
use MJ\WPORM\Model;

class SpecialistInsurancesRel extends Model {
	protected $table = 'drplus_specialist_insurances_rel';
	protected $fillable = ['id', 'user_id', 'insurance_id'];
	protected $guarded = ['id'];
	protected $timestamps = false;
	protected $casts = [
		'user_id'		=> 'int',
		'insurance_id'	=> 'int'
	];

	public function up( Blueprint $table ) {
		$table->id();
		$table->integer( 'user_id' );
		$table->integer( 'insurance_id' );

		$table->index( 'user_id' );
		$table->index( 'insurance_id' );

		$this->schema = $table->toSql();
	}

	public function specialists( $foreignKey = 'user_id', $localKey = 'user_id' ) {
		$this->belongsTo( Specialists::class, 'user_id', 'user_id' );
	}
}