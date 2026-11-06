<?php
namespace DrPlus\Model;

use DrPlus\Utils;
use DrPlus\Utils\UtilsSpecialists;
use MJ\WPORM\Blueprint;
use MJ\WPORM\Model;

class Specialists extends Model {
	protected $table = 'drplus_specialists';
	protected $fillable = ['id', 'user_id', 'post_id', 'slug', 'name', 'subtitle', 'offline_visit', 'online_visit', 'is_verified', 'meta', 'offices', 'documents', 'status', 'reject'];
	protected $guarded = ['id'];
	protected $appends = ['user', 'display_name', 'about'];
	protected $casts = [
		'user_id'		=> 'int',
		'post_id'		=> 'int',
		'offline_visit'	=> 'bool',
		'online_visit'	=> 'bool',
		'is_verified'	=> 'bool',
		'meta'			=> 'array',
		'offices'		=> 'array',
		'documents'		=> 'array',
	];

	public function up( Blueprint $table ) {
		$table->id();
		$table->integer( 'user_id' )->unique();
		$table->integer( 'post_id' )->unique()->default( 0 );
		$table->text( 'name' )->nullable();
		$table->string( 'slug', 190 )->unique(); // utf8 issue
		$table->text( 'subtitle' );
		$table->boolean( 'offline_visit' );
		$table->boolean( 'online_visit' );
		$table->boolean( 'is_verified' );
		$table->longText( 'meta' );
		$table->longText( 'offices' );
		$table->longText( 'documents' );
		$table->string( 'status', 50 ); // utf8 issue
		$table->text( 'reject' );
		$table->timestamps();

		$table->index( ['post_id', 'status'] );
		$table->index( 'user_id' );

		$this->schema = $table->toSql();
	}

	public function sanitize_save() {
		if( isset( $this->user ) ) {
			unset( $this->user );
		}
		if( isset( $this->status ) ) {
			$this->status = Utils::ensure_values_in_array( sanitize_text_field( $this->status ), array_keys( UtilsSpecialists::statuses( true ) ), 'incomplete' );
		}
	}

	public function creating() {
		$this->sanitize_save();
	}

	public function updating() {
		$this->sanitize_save();
	}

	public function getUserAttribute() {
		return get_user_by( 'id', $this->user_id );
	}

	public function getDisplayNameAttribute() {
		if( !empty( $this->name ) ) {
			return $this->name;
		}
		if( empty( $this->user ) || !is_object( $this->user ) ) {
			if( $this->user_id ) {
				$this->user = $this->getUserAttribute();
			} else {
				return "";
			}
		}
		return trim( "{$this->user->first_name} {$this->user->last_name}" );
	}

	public function getAboutAttribute() {
		if( empty( $this->post_id ) ) return "";
		return get_the_content( null, false, $this->post_id );
	}

	public function specialities( $foreignKey = 'user_id', $localKey = 'user_id' ) {
		return $this->hasMany( SpecialistSpecialitiesRel::class, 'user_id', 'user_id' );
	}

	public function hospitals( $foreignKey = 'user_id', $localKey = 'user_id' ) {
		return $this->hasMany( SpecialistHospitalsRel::class, 'user_id', 'user_id' );
	}

	public function insurances( $foreignKey = 'user_id', $localKey = 'user_id' ) {
		return $this->hasMany( SpecialistInsurancesRel::class, 'user_id', 'user_id' );
	}
}