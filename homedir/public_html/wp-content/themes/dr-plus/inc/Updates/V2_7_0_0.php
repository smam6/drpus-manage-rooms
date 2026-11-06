<?php
namespace DrPlus\Updates;

class V2_7_0_0 {
	public static $run_at_install = true;

	public static function update() {
		// Change users display name for RTL
		if( is_rtl() ) {
			$sms_settings = get_option( 'drplus_sms_settings', [] );
			if( !isset( $sms_settings['security'] ) || ( !empty( $sms_settings['security']['hide_mobile'] ) && in_array( $sms_settings['security']['hide_mobile'], ['mid_star', 'end_star'] ) ) ) {
				$type = !isset( $sms_settings['security'] ) ? 'mid_star' : $sms_settings['security']['hide_mobile'];
				global $wpdb;
				$users_display_name = $wpdb->get_results( "SELECT ID, display_name FROM {$wpdb->users}" );
				foreach( $users_display_name as $user ) {
					if( self::check_display_name_hidden( $user->display_name ) ) {
						$display_name = '';
						if( $type == 'mid_star' ) {
							$display_name = implode( "***", array_reverse( explode( "***", $user->display_name ) ) );
						} else {
							if( preg_match( '/^\d{7}\*{4}$/', $user->display_name ) ) {
								$display_name = "****" . substr( $user->display_name, 0, 7 );
							}
						}
						if( $display_name ) {
							$wpdb->update( $wpdb->users, [
								'display_name'	=> $display_name,
							], [
								'ID'	=> $user->ID,
							] );
						}
					}
				}
			}
		}

		// Update melipayamak
		$sms_settings = get_option( 'drplus_sms_settings', [] );
		if( !empty( $sms_settings ) && !empty( $sms_settings['gateway'] ) && $sms_settings['gateway'] == 'melipayamak' ) {
			if( !empty( $sms_settings['melipayamak'] ) && !empty( $sms_settings['melipayamak']['password'] ) ) {
				$sms_settings['melipayamak']['api_key'] = $sms_settings['melipayamak']['password'];
				update_option( 'drplus_sms_settings', $sms_settings, false );
			}
		}
	}

	private static function check_display_name_hidden( $string ) {
		$pattern = '/^\d{4}\*\*\*\d{4}$/';
		$pattern2 = '/^\d{7}\*{4}$/';
		if( preg_match( $pattern, $string ) || preg_match( $pattern2, $string ) ) {
			return true;
		} else {
			return false;
		}
	}
}