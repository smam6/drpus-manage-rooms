<?php

use DrPlus\Utils;
use DrPlus\Utils\UtilsSpecialists;

$prefix = 'specialist_';

$new = empty( $_GET['sid'] );
$sid = 0;
if( !$new ) {
	$sid = Utils::convert_chars( $_GET['sid'], true, 'absint' );
} else { // New mode
	$user_id = Utils::convert_chars( $_POST[self::$PREFIX . 'user_id'], true, 'absint' );

	// Check if user is already a specialist
	$old_specialist = UtilsSpecialists::get_by_user_id( $user_id );
	if( !empty( $old_specialist ) ) {
		// show html with message and a button to html for specialist view page
		$html = sprintf(
			'<p>%s</p><a href="%s" class="button button-primary">%s</a>',
			esc_html__( 'User is already a specialist', 'drplus' ),
			add_query_arg( ['sid' => $old_specialist['id'] ] ),
			esc_html__( 'View Specialist', 'drplus' ) );
		add_settings_error( 'drplus-specialists-settings', self::$PREFIX . 'settings', $html, 'error' );
		return;
	}
}

$specialist = Utils::remove_prefix_from_array_keys( $_POST, self::$PREFIX );
$sid = UtilsSpecialists::save( $specialist, $sid );
if( is_wp_error( $sid ) ) {
	add_settings_error( 'drplus-specialists-settings', self::$PREFIX . 'settings', $sid->get_error_message(), 'error' );
	return;
}
if( empty( $sid ) ) {
	add_settings_error( 'drplus-specialists-settings', self::$PREFIX . 'settings', esc_html__( 'Error saving specialist', 'drplus' ), 'error' );
	return;
} else {
	add_settings_error( 'drplus-specialists-settings', self::$PREFIX . 'settings', esc_html__( 'Specialist saved successfully', 'drplus' ), 'updated' );
}

// Redirect to view page with id
if( $new ) {
	wp_redirect( add_query_arg( ['sid' => $sid] ) );
	exit;
}