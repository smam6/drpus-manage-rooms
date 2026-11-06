<?php

namespace DrPlus\Backend\Specialists;

use DrPlus\Utils;
use DrPlus\Utils\AdminUI;
use DrPlus\Utils\UtilsSpecialists;

class SpecialistIdentity extends SpecialistView {
	public static function view() {
		$max_upload_size_bytes = UtilsSpecialists::get_identity_max_upload_size( false );
		foreach( UtilsSpecialists::get_identity_types_terms() as $index => $type ) {
			$required = !Utils::to_bool( get_term_meta( $type['id'], 'optional', true ) );
			AdminUI::dropzone( [
				'title'				=> $type['name'],
				'description'		=> $type['description'],
				'max_upload_size'	=> $max_upload_size_bytes,
				'input_name'		=> parent::$PREFIX . "documents[{$type['name']}]",
				'input_id'			=> parent::$PREFIX . "documents-{$index}",
				'value'				=> parent::$specialist->documents[$type['name']] ?? '',
				'required'			=> $required ? 'required' : '',
			] );
			if( $required && empty( parent::$specialist->documents[$type['name']] ) ) {
				parent::$active_submit_button = false;
			}
		}
	}
}