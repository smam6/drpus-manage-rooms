<?php
namespace DrPlus\Utils;

use DrPlus\Model\SpecialistInsurancesRel as ModelSpecialistInsurancesRel;
use DrPlus\Utils;

class SpecialistInsurancesRel extends Utils {
	public static function delete( $id ) {
		return ModelSpecialistInsurancesRel::query()->where( 'id', $id )->delete();
	}

	/**
	 * Add user insurances
	 *
	 * @param int $user_id
	 * @param array $insurances array of insurance ids (NOT DB ids)
	 * @return void
	 */
	public static function add_user_insurances( $user_id, $insurances ) {
		foreach( $insurances as $insurance ) {
			$item_db = new ModelSpecialistInsurancesRel;
			$item_db->user_id = $user_id;
			$item_db->insurance_id = $insurance;
			$item_db->save();
		}
	}

	public static function get_user_insurances( $user_id ) {
		$user_id = Utils::get_user_id( $user_id );
		if( empty( $user_id ) ) return [];
		return ModelSpecialistInsurancesRel::query()->where( 'user_id', $user_id )->get();
	}
}