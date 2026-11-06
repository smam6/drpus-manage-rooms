<?php

use DrPlus\Model\Booking;
use DrPlus\Utils;
use DrPlus\Utils\Options;

if ( !wp_next_scheduled( 'drplus_update_booking_statuses_hook' ) ) {
    wp_schedule_event( time(), 'drplus_five_minutes', 'drplus_update_booking_statuses_hook' );
}

function drplus_update_booking_statuses_exec() {
	$options = Options::get_options( [
		'booking-check-status-to-cancellation'	=> 30
	] );
	$deadline_time = Utils::convert_chars( $options['booking-check-status-to-cancellation'], 'absint' );

	// Update booking statuses to 'cancelled' if they are 'on-hold' and older than 30 minutes
	$reserve_deadline_time = (int)date_i18n( 'U' ) - ( $deadline_time * MINUTE_IN_SECONDS );
	$expired_time = date( 'Y-m-d H:i:s', $reserve_deadline_time );
	Booking::query()->whereIn( 'order_status', ['on-hold', 'pending'] )->where( 'created_at', '<', $expired_time )->update( 'order_status', 'cancelled' );

	// Get booking 'order_id' from booking table where date is in the past
	$timezone = wp_timezone(); // Gets the timezone set in WordPress settings
	$current_time = new DateTime( 'now', $timezone );
	$current_time->modify( '-1 hour' );
	
	$past_time = $current_time->format( 'H:i:s' );
	$past_date = $current_time->format( 'Y-m-d' );
	$exclude_statuses = ['cancelled', 'refunded', 'failed', 'on-hold', 'pending', 'checkout-draft'];
	$past_apps = Booking::query()
		->select( 'order_id' )
		->where( '`date`', '<=', $past_date )
		->where( 'start_time', '<=', $past_time )
		->whereNotIn( 'order_status', $exclude_statuses )
		->whereNotIn('office_id', ['chat_consultation', 'instant_chat_consultation'])
		->get();
	if( !empty( $past_apps->toArray() ) ) {
		$order_ids = $past_apps->pluck( 'order_id' );
		foreach( $order_ids as $order_id ) {
			$order = wc_get_order( $order_id );
			if( $order ) {
				$order->update_status( 'completed' );
			}
		}
		Booking::query()->whereIn( 'order_id', $order_ids )->update( [
			'order_status'	=> 'completed'
		] );
	}
}
add_action( 'drplus_update_booking_statuses_hook', 'drplus_update_booking_statuses_exec' );