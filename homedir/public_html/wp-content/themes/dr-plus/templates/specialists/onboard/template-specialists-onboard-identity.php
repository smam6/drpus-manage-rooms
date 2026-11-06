<?php

use DrPlus\Utils;
use DrPlus\Utils\UI;
use DrPlus\Utils\UtilsSpecialists;

extract( $args );

$max_upload_size_bytes = UtilsSpecialists::get_identity_max_upload_size( false );
foreach( UtilsSpecialists::get_identity_types_terms() as $index => $type ) {
	UI::dropzone( [
		'title'				=> $type['name'],
		'description'		=> $type['description'],
		'max_upload_size'	=> $max_upload_size_bytes,
		'input_name'		=> "specialist_documents[{$type['name']}]",
		'input_id'			=> "specialist_documents-{$index}",
		'value'				=> !empty( $specialist->documents[$type['name']] ) && !empty( $specialist->documents[$type['name']] ) ? $specialist->documents[$type['name']] : '',
		'required'			=> Utils::to_bool( get_term_meta( $type['id'], 'optional', true ) ) ? '' : 'required',
	] );
}