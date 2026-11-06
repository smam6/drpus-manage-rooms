<?php
namespace DrPlus\Model;

use MJ\WPORM\Blueprint;
use MJ\WPORM\Model;

class SpecialistSpecialitiesRel extends Model {
	protected $table = 'drplus_specialist_speciality_rel';
	protected $fillable = ['id', 'user_id', 'speciality_id'];
	protected $guarded = ['id'];
	protected $timestamps = false;
	protected $casts = [
		'user_id'		=> 'int',
		'speciality_id'	=> 'int'
	];

	public function up( Blueprint $table ) {
		$table->id();
		$table->integer( 'user_id' );
		$table->integer( 'speciality_id' );

		$table->index( 'user_id' );
		$table->index( 'speciality_id' );

		$this->schema = $table->toSql();
	}

	public function specialists( $foreignKey = 'user_id', $localKey = 'user_id' ) {
		return $this->belongsTo( Specialists::class, 'user_id', 'user_id' );
	}
}