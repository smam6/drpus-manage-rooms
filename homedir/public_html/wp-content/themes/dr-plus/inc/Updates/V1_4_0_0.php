<?php
namespace DrPlus\Updates;

class V1_4_0_0 {
	public static $run_at_install = false;

	public static function update() {
		self::update_specialist_offices();
	}

	private static function update_specialist_offices() {
		$online_visit_offices = [
			'phone_consultation'	=> [
				'label'	=> esc_html__( 'Phone Consultation', 'drplus' ),
				'icon'	=> 'headphone'
			],
			'chat_consultation'	=> [
				'label'	=> esc_html__( 'Chat Consultation', 'drplus' ),
				'icon'	=> 'messages-2'
			],
			'video_consultation'	=> [
				'label'	=> esc_html__( 'Video Consultation', 'drplus' ),
				'icon'	=> 'video'
			],
		];
		global $wpdb;
		$wpdb->query('START TRANSACTION');

		$specialists_table = $wpdb->prefix . "drplus_specialists";
		$specialists = $wpdb->get_results( "SELECT `id`, `offices`, `online_visit`, `offline_visit` FROM {$specialists_table}" );

		foreach( $specialists as $specialist ) {
			if( empty( $specialist->offline_visit ) && empty( $specialist->online_visit ) ) continue;
			$specialist_offices = json_decode( $specialist->offices, true );
			if( empty( $specialist_offices ) || !is_array( $specialist_offices ) ) $specialist_offices = [];
			
			if( !empty( $specialist->online_visit ) ) {
				foreach( $online_visit_offices as $key => $data ) {
					// backward compatibility
					if( $key == 'phone_consultation' && isset( $specialist_offices['consultation'] ) ) {
						$specialist_offices['phone_consultation'] = $specialist_offices['consultation'];
						$specialist_offices['phone_consultation']['id'] = 'phone_consultation';
						$specialist_offices['phone_consultation']['name'] = $data['label'];
						unset( $specialist_offices['consultation'] );
					}
					if( empty( $specialist_offices[$key] ) ) {
						$specialist_offices[$key] = [
							'type'			=> 'consultation',
							'id'			=> $key,
							'name'			=> $data['label'],
							'visit_time'	=> '',
							'visit_price'	=> 0,
							'main'			=> 0,
							'enable_booking'	=> 0,
						];
					}
				}
			}

			foreach( $specialist_offices as $key => $office ) {
				if( !isset( $office['enable_booking'] ) ) {
					$specialist_offices[$key]['enable_booking'] = 1;
				}
			}

			$specialist_offices = wp_json_encode( $specialist_offices );
			$wpdb->update( $specialists_table, ['offices' => $specialist_offices], ['id' => $specialist->id], ['%s'], ['%d'] );
		}

		// Update 'consultation' office cols to 'phone_consultation' in time db
		$time_table = $wpdb->prefix . 'drplus_times';
		$wpdb->update( $time_table, ['office' => 'phone_consultation'], ['office' => 'consultation'], ['%s'], ['%s'] );

		// update booking table
		$time_table = $wpdb->prefix . 'drplus_booking';
		$wpdb->update( $time_table, ['office_id' => 'phone_consultation'], ['office_id' => 'consultation'], ['%s'], ['%s'] );

		$wpdb->query('COMMIT');

		flush_rewrite_rules();
	}
}