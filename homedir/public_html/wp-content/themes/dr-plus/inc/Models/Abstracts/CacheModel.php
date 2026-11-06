<?php
namespace DrPlus\Model;

use DrPlus\Utils;
use MJ\WPORM\Model;
use MJ\WPORM\Blueprint;

abstract class Cache extends Model {
	protected $fillable = ['id', 'code', 'type', 'object_id', 'val', 'expire'];
	protected $guarded = ['id'];
	protected $timestamps = false;
	protected $casts = [
		'expire'	=> 'datetime',
	];

	public function up( Blueprint $table ) {
		$table->id();
		$table->bigInteger('code');
		$table->string( 'type' );
		$table->integer( 'object_id' );
		$table->longText( 'val' );
		$table->datetime( 'expire' );
		$this->schema = $table->toSql();
	}

	public function first_by_code( $code, $object_id = 0 ) {
		$query = $this->newQuery()->where('code', $code);
		if ($object_id) {
			$query = $query->where('object_id', $object_id);
		}
		return $query->first();
	}

	public function get_by_type( $type, $object_id = 0 ) {
		$query = $this->newQuery()->where('type', $type);
		if ($object_id) {
			$query = $query->where('object_id', $object_id);
		}
		return $query->get();
	}

	public function get_by_object_id( $object_id ) {
		$query = $this->newQuery()->where('object_id', $object_id);
		return $query->get();
	}

	public function sanitize_update() {
		if( !empty( $this->code ) ) {
			$this->code = Utils::convert_chars( $this->code, true, 'absint' );
		}
		if( !empty( $this->type ) ) {
			$this->type = Utils::convert_chars( $this->type );
		}
		if( !empty( $this->object_id ) ) {
			$this->object_id = Utils::convert_chars( $this->object_id, true, 'absint' );
		}
		if( !is_scalar( $this->val ) && !Utils::is_json( $this->val ) ) {
			$this->val = wp_json_encode( $this->val );
		}
	}

	public function retrieved() {
		if( Utils::is_json( $this->val ) ) {
			$this->val = json_decode( $this->val, true );
		}
	}

	public function creating() {
		$this->sanitize_update();
	}

	public function updating() {
		$this->sanitize_update();
	}
}