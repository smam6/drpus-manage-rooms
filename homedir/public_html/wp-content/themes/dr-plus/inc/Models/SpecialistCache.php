<?php
namespace DrPlus\Model;

use DrPlus\Utils;

class SpecialistCache extends Cache {
	protected $table = 'drplus_specialist_cache';

	public function retrieved() {
		if( Utils::is_json( $this->val ) ) {
			$values = json_decode( $this->val, true );
			$this->val = Specialists::query()->whereIn( 'id', $values )->get();
		}
	}
}