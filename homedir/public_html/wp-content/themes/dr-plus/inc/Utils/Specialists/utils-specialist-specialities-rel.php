<?php
namespace DrPlus\Utils;

use DrPlus\Model\SpecialistSpecialitiesRel as ModelSpecialistSpecialitiesRel;
use DrPlus\Utils;

class SpecialistSpecialitiesRel extends Utils {
	public static function delete( $id ) {
		return ModelSpecialistSpecialitiesRel::query()->where( 'id', $id )->delete();
	}

	/**
	 * Add user specialities
	 *
	 * @param int $user_id
	 * @param array $specialities array of speciality ids (NOT DB ids)
	 * @return void
	 */
	public static function add_user_specialities( $user_id, $specialities ) {
		foreach( $specialities as $speciality ) {
			$item_db = new ModelSpecialistSpecialitiesRel;
			$item_db->user_id = $user_id;
			$item_db->speciality_id = $speciality;
			$item_db->save();
		}
	}

	public static function get_user_specialities( $user_id ) {
		$user_id = Utils::get_user_id( $user_id );
		if( empty( $user_id ) ) return [];
		return ModelSpecialistSpecialitiesRel::query()->where( 'user_id', $user_id )->get();
	}
}