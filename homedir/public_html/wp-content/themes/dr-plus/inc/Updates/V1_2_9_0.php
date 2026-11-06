<?php
namespace DrPlus\Updates;

class V1_2_9_0 {
	public static $run_at_install = false;
	
	public static function update() {
		global $wpdb;
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_otp` DROP `created_at`;" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_specialist_city_rel` DROP `created_at`, DROP `updated_at`;" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_specialist_hospitals_rel` DROP `created_at`, DROP `updated_at`;" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_specialist_insurances_rel` DROP `created_at`, DROP `updated_at`;" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_specialist_speciality_rel` DROP `created_at`, DROP `updated_at`;" );

		// Move notification messages to post_content
		$notifications = get_posts( [
			'post_type'		=> 'notification',
			'numberposts'	=> -1,
			'post_status'	=> 'any',
		] );
		$wpdb->query( 'START TRANSACTION' );

		foreach( $notifications as $notification ) {
			$message = get_post_meta( $notification->ID, '_message', true );
			delete_post_meta( $notification->ID, '_message' );
			if( !$message ) continue;

			$wpdb->update( $wpdb->posts, [
				'post_content'	=> $message
			], [
				'ID'	=> $notification->ID,
			] );
		}

		// Update offices for specialists
		$specialists = $wpdb->get_results( "SELECT `id`, `offices` FROM `{$wpdb->prefix}drplus_specialists`" );
		if( $specialists ) {
			foreach( $specialists as $specialist ) {
				$offices = $specialist->offices;
				if( !empty( $offices ) ) {
					$offices = json_decode( $offices, true );
					$new_offices = [];
					foreach( $offices as $office ) {
						if( empty( $office['id'] ) ) continue;
						$new_offices[$office['id']] = $office;
					}
					$wpdb->update( "{$wpdb->prefix}drplus_specialists", [
						'offices'	=> $new_offices
					], [
						'id'	=> $specialist->id
					] );
				}
			}
		}

		$wpdb->query( 'COMMIT' );

		flush_rewrite_rules(); // Just for sure
	}
}