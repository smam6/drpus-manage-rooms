<?php

use DrPlus\Model\ReminderLog;
use DrPlus\Utils;
use DrPlus\Utils\Date;

if ( !wp_next_scheduled( 'drplus_reminder_log_clean_up_hook' ) ) {
	wp_schedule_event( time(), 'twicedaily', 'drplus_reminder_log_clean_up_hook' );
}

function drplus_reminder_log_clean_up_exec() {
	// Delete reminders with not_sent or cancelled status older than 1 day
	$date = Date::maybe_j2g( date_i18n( 'Y-m-d', strtotime( '-1 days' ) ) );
	$time = date_i18n( 'H:i:s' );
	ReminderLog::query()->where( 'send_time', '<', Utils::convert_chars( "{$date} {$time}" ) )->whereIn( 'status', ['not_sent', 'cancelled'] )->delete();

	// Delete reminders with sent or failed status older than 30 days
	$date_30 = Date::maybe_j2g( date_i18n( 'Y-m-d', strtotime( '-30 days' ) ) );
	ReminderLog::query()->where( 'send_time', '<', Utils::convert_chars( "{$date_30} {$time}" ) )->whereIn( 'status', ['sent', 'failed'] )->delete();
}
add_action( 'drplus_reminder_log_clean_up_hook', 'drplus_reminder_log_clean_up_exec' );