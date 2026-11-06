<?php

use DrPlus\Model\ReminderLog;
use DrPlus\Utils\SMS as UtilsSMS;
use DrPlus\SMS\SMS as SMSSender;
use DrPlus\Utils\Date;

if ( !wp_next_scheduled( 'drplus_check_send_booking_sms_hook' ) ) {
	wp_schedule_event( time(), 'drplus_one_minutes', 'drplus_check_send_booking_sms_hook' );
}

function drplus_check_send_booking_sms_exec() {
	$current_time = Date::maybe_j2g( date_i18n('Y-m-d H:i:s' ) );
	$date = new \DateTime($current_time);
	$date->modify('-15 minutes');
	$timestamp = $date->getTimestamp();
	$time_15min_ago = date( 'Y-m-d H:i:s', $timestamp );

	// Get reminders to send
	$reminders = ReminderLog::query()->where( 'status', 'not_sent' )->whereBetween( 'send_time', [$time_15min_ago, $current_time] )->get();
	if ( empty( $reminders->toArray() ) ) return;

	// Get reminder settings
	$reminder_settings = UtilsSMS::get_reserve_notif_reminder_sms_settings();

	$sent_ids = [];
	$failed_ids = [];

	foreach ( $reminders as $reminder ) {
		$type = $reminder->receiver_type; // 'specialist' or 'patient'
		$timing_id = $reminder->timing_id;
		$to = $reminder->to;
		$variables = !is_array( $reminder->variables ) ? json_decode( $reminder->variables ) : $reminder->variables;
		
		if( !$reminder_settings[$type]['status'] ) continue;

		// Get message template for this reminder
		$message = '';
		if (
			!empty( $reminder_settings[$type]['items'][$timing_id] ) &&
			!empty( $reminder_settings[$type]['items'][$timing_id]['message'] )
		) {
			$message = $reminder_settings[$type]['items'][$timing_id]['message'];
		}

		if ( empty( $message ) ) continue;

		// Send SMS
		$result = SMSSender::send( $to, "reserve_notification.{$type}.reminder.{$timing_id}", $variables ); // '' uses default gateway

		if ( !is_wp_error( $result ) ) {
			$sent_ids[] = $reminder->id;
		} else {
			$failed_ids[] = $reminder->id;
		}
	}

	// Bulk update statuses
	if ( !empty( $sent_ids ) ) {
		ReminderLog::query()->whereIn( 'id', $sent_ids )->update( [
			'status'	=> 'sent'
		] );
	}
	if ( !empty( $failed_ids ) ) {
		ReminderLog::query()->whereIn( 'id', $failed_ids )->update( [
			'status'	=> 'failed'
		] );
	}
}
add_action( 'drplus_check_send_booking_sms_hook', 'drplus_check_send_booking_sms_exec' );