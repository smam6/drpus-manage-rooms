<?php
namespace DrPlus\Utils;

use DrPlus\Model\SpecialistHospitalsRel as ModelSpecialistHospitalsRel;
use DrPlus\Model\Times;
use DrPlus\Utils;

class SpecialistHospitalsRel extends Utils {
	public static function delete( $id, $user_id ) {
		$delete_hospital = ModelSpecialistHospitalsRel::query()->where( 'id', $id )->delete();
		$delete_times = Times::query()->where( [
			'office'	=> $id,
			'user_id'	=> $user_id
		] )->delete();

		return $delete_hospital && $delete_times;
	}

	/**
	 * Add user hospitals
	 *
	 * @param int $user_id
	 * @param array $hospitals array of hospital ids (NOT DB ids)
	 * @return void
	 */
	public static function add_user_hospitals( $user_id, $hospitals ) {
		foreach( $hospitals as $hospital ) {
			$item_db = new ModelSpecialistHospitalsRel;
			$item_db->user_id = $user_id;
			$item_db->hospital_id = $hospital;
			$item_db->save();
		}
	}

	public static function get_user_hospitals( $user_id ) {
		$user_id = Utils::get_user_id( $user_id );
		if( empty( $user_id ) ) return [];
		return ModelSpecialistHospitalsRel::query()->where( 'user_id', $user_id )->get();
	}

	

	/**
	 * Trigger location sync for connected specialists when a hospital location changes.
	 *
	 * @return void
	 */
	public static function handle_hospital_location_change( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) {
		if( $taxonomy !== 'location' ) return;
		if( \get_post_type( $object_id ) !== 'hospital' ) return;
		if( \is_wp_error( $tt_ids ) ) return;

		$new_tt_ids = array_map( 'intval', (array) $tt_ids );
		$old_tt_ids = array_map( 'intval', (array) $old_tt_ids );

		sort( $new_tt_ids );
		sort( $old_tt_ids );

		if( $new_tt_ids === $old_tt_ids ) return;

		Location::sync_specialists_location_terms( $object_id );
	}
}

add_action( 'set_object_terms', [SpecialistHospitalsRel::class, 'handle_hospital_location_change'], 10, 6 );