<?php

use DrPlus\Components\Alert;
use DrPlus\Components\Button;
use DrPlus\Components\Loading;
use DrPlus\Components\SectionTitle;
use DrPlus\Utils;
use DrPlus\Utils\Booking;
use DrPlus\Utils\Options;
use MJ\Whitebox\Utils as WhiteboxUtils;

$specialist = Booking::get_specialist();
if( !$specialist ) {
	return; // Exit from file
}

$show_only_consultation_offices = false;
if( !empty( $_GET['consultation'] ) ) {
	$show_only_consultation_offices = true;
}

$active_consultation_offices = array_keys( Booking::consultation_offices( true ) );
$is_instant_chat_available = in_array( 'instant_chat_consultation', $active_consultation_offices );

$book_offices = [];
foreach( $specialist->offices as $office ) {
	if( !Utils::to_bool( $office['enable_booking'] ?? 0 ) ) continue;
	if( $office['type'] == 'consultation' && ( !$specialist->online_visit || !in_array( $office['id'], $active_consultation_offices ) ) ) continue;
	if( $office['type'] != 'consultation' && ( !$specialist->offline_visit || $show_only_consultation_offices ) ) continue;

	if( !isset( $office['max_booking_days'] ) ) $office['max_booking_days'] = "";
	if( !isset( $office['custom_off_days'] ) ) $office['custom_off_days'] = [];

	$book_offices[$office['id']] = $office;
}

if( !empty( $_GET['in_person_visit'] ) ) {
	foreach( $book_offices as $id => $office ) {
		if( $office['type'] == 'consultation' ) unset($book_offices[$id]);
	}
}

if( empty( $book_offices ) ) {
	Alert::view( [
		'type'	=> 'warning',
		'icon'	=> 'drplus-icon-error',
		'text'	=> sprintf( esc_html__( '%s has not registered any offices for appointment booking.', 'drplus' ), $specialist->display_name ),
		'classes'	=> ['booking-alert']
	] );
	// Get prev page url
	$prev_page_url = wp_get_referer();
	if( empty( $prev_page_url ) ) {
		$prev_page_url = Booking::get_booking_page_url();
	}
	Button::view( [
		'text'		=> esc_html__( 'Return to previous page', 'drplus' ),
		'link'		=> esc_url( $prev_page_url ),
		'type'		=> 'bordered',
		'align' 	=> 'center',
	] );
	return;
}

$options = Options::get_options( [
	'booking-time-section-title'	=> esc_html__( 'Select visit time', 'drplus' ),
	'booking-time-section-icon'		=> 'drplus-icon-clock-fill',
	'booking-datepicker'			=> 'persian'
] );

// Set the first office as main for default
$main_office_id = 0;
$instant_chat_times = [];

// Get offices available days
$offices_off_days = [];
foreach( $book_offices as $office ) {
	// Set main office ID
	if( isset( $office['main'] ) && Utils::to_bool( $office['main'] ) ) {
		$main_office_id = $office['id'];
	}
	
	// Set off days
	$off_days = [];
	$office_time = Booking::get_times_by_office( $office['id'], $specialist );
	if( $office['id'] == 'instant_chat_consultation' ) $instant_chat_times = $office_time;
	foreach( $office_time['days'] as $day ) {
		if( empty( $day['times'] ) || !Utils::to_bool( $day['status'] ) ) {
			$off_days[] = $day['day'];
		}
	}
	sort( $off_days );
	$offices_off_days[$office['id']] = $off_days;
}

if( empty( $main_office_id ) ) $main_office_id = $book_offices[array_key_first( $book_offices )]['id'];


// Set nearest date offices
$now = new \DateTime( 'now', wp_timezone() );
// Adjust the weekday index to start from Saturday
$current_day_index = ((int)$now->format('w') + 7) % 7;

$nearest_date_timestamps = [];
foreach( $book_offices as $office ) {
	$nearest_date = null;

	$max_days = !empty( $office['max_booking_days'] ) ? (int)$office['max_booking_days'] : 365;
	$max_days = min( 365, max( 0, $max_days ) );

	$search_date = clone $now;

	for ($i = 0; $i <= $max_days; $i++) {
		// Adjust the weekday index to start from Saturday
		$weekday_index = ((int)$search_date->format('w') + 1) % 7;
		$date_str = $search_date->format('Y-m-d');
		
		if (
			isset( $offices_off_days[$office['id']] ) &&
			in_array( $weekday_index-1, $offices_off_days[$office['id']] )  // minus 1 because our week start from saturday but off days are saved in standard weekday (monday is 1)
		) {
			$search_date->modify('+1 day');
			continue;
		}

		// Check for custom off days
		$custom_off_days = isset( $office['custom_off_days'] ) && is_array( $office['custom_off_days'] ) ? $office['custom_off_days'] : [];
		$custom_off_day_strings = array_map( function( $ts ) {
			if( empty( $ts ) ) return '';
			$dt = ( $ts instanceof \DateTime ) ? $ts : ( is_numeric( $ts ) ? ( new \DateTime( '@' . intval( $ts ) ) )->setTimezone( wp_timezone() ) : new \DateTime( $ts, wp_timezone() ) );
			return $dt->format( 'Y-m-d' );
		}, $custom_off_days );
		if ( in_array( $date_str, $custom_off_day_strings ) ) {
			$search_date->modify('+1 day');
			continue;
		}

		$nearest_date = clone $search_date;
		break;
	}

	$nearest_date_timestamps[$office['id']] = $nearest_date ? $nearest_date->getTimestamp() : '';
}

if( !empty( $nearest_date_timestamps['instant_chat_consultation'] ) && date_i18n( 'Y-m-d', $nearest_date_timestamps['instant_chat_consultation'] ) == date_i18n( 'Y-m-d' ) ) {
	foreach( $instant_chat_times['days'] as $instant_chat_day_times ) {
		if( $instant_chat_day_times['day'] != $current_day_index ) continue;
		if( $is_instant_chat_available ) break;
		
		if( !$instant_chat_day_times['status'] ) break;
		$current_time = WhiteboxUtils::convert_chars( date_i18n( 'H:i' ) );
		if( $instant_chat_day_times['use_default'] ) {
			foreach( $instant_chat_times['default_times'] as $default_time ) {
				if( $default_time->from > $current_time || $default_time->to < $current_time ) continue;
				$is_instant_chat_available = true;
				break;
			}
		} else {
			foreach( $instant_chat_day_times['times'] as $day_time ) {
				if( $day_time->from > $current_time || $day_time->to < $current_time ) continue;
				$is_instant_chat_available = true;
				break;
			}
		}
	}
}

$specialist_id = Utils::convert_chars( $_GET['sid'] ?? 0, true, 'absint' );
if( empty( $specialist_id ) && !empty( $_SESSION['booking']['specialist_id'] ) ) {
	$specialist_id = $_SESSION['booking']['specialist_id'];
}

$selected_office_id = !empty( $_SESSION['booking']['office'] ) ? $_SESSION['booking']['office'] : $main_office_id;

wp_localize_script( 'drplus-booking', 'drplusBooking', [
	'specialistID'				=> $specialist->id,
	'selectedOffice'			=> $selected_office_id,
	'offDays'					=> $offices_off_days,
	'customOffDays'				=> wp_list_pluck( $book_offices, 'custom_off_days', 'id' ),
	'chunkTimes'				=> wp_list_pluck( $book_offices, 'visit_time', 'id' ),
	'maxBookingDays'			=> wp_list_pluck( $book_offices, 'max_booking_days', 'id' ),
	'nearestDateTimestamps'		=> $nearest_date_timestamps,
	'PersianDatePicker'			=> $options['booking-datepicker'] === 'persian',
	'isInstantChatAvailable'	=> $is_instant_chat_available
] );

?>
<input type="hidden" name="booking_specialist_id" value="<?php echo $specialist_id ?>">
<div class="booking-section booking-specialist-offices-section">
	<?php Booking::specialist_info_html( $specialist ) ?>
	<div class="booking-specialist-offices">
		<?php foreach( $book_offices as $office ) { ?>
			<?php if( $office['type'] == 'consultation' ) {
				$office['image'] = DRPLUS_URI . 'assets/images/online-consultation.webp';
			} ?>
			<label for="booking_office_<?php echo esc_attr( $office['id'] ) ?>" class="booking-specialist-office-wrap">
				<input type="radio" name="booking_office" id="booking_office_<?php echo esc_attr( $office['id'] ) ?>" class="booking-specialist-office-radio" value="<?php echo esc_attr( $office['id'] ) ?>" <?php checked( $office['id'], $selected_office_id ) ?>>
				<?php Booking::specialist_office_html( $office, false, false, true ); ?>
			</label>
		<?php } ?>
	</div>
</div>

<div class="booking-section booking-time-section">
	<?php SectionTitle::view( [
		'title'	=> $options['booking-time-section-title'],
		'icon'	=> $options['booking-time-section-icon']
	] ); ?>

	<div class="booking-calendar-wrap">
		<input type="hidden" name="booking_date" class="booking-date-calendar" id="booking-date" value="<?php echo $nearest_date_timestamps[$selected_office_id] ?>" required>
	</div>

	<div class="booking-times-wrap">
		<?php
		Loading::view( [
			'classes'	=> ['booking-times-loading']
		] );
		?>
		<input type="hidden" name="booking_time" class="booking-time" id="booking-time" data-nonce="<?php echo wp_create_nonce( 'booking_available_times' ) ?>" required>
		<span class="booking_time_empty_slot_notice"><?php echo esc_html__( 'There are no active times on the selected date.', 'drplus' ) ?></span>
		<div class="booking-time-slots"></div>
	</div>

	<?php if( !$is_instant_chat_available ) { ?>
		<div class="booking-instant-chat-notice" style="display:none">
			<?php esc_html__( 'Instant chat not available at this time', 'drplus' ) ?>
		</div>
	<?php } ?>

	<?php
	Alert::view( [
		'type'		=> 'error',
		'icon'		=> 'drplus-icon-close-circle-bold',
		'text'		=> esc_html__( 'An error occurred while loading available times. Please try again later.', 'drplus' ),
		'classes'	=> ['booking-times-error-alert']
	] );
	?>

	<?php get_template_part( 'templates/booking/template-booking', 'nav-btns', [
		'current_step'	=> 'time',
		'prev_step'		=> 'specialist',
		'booking_url'	=> $args['booking_url'],
	] ) ?>
</div>

<script type="text/html" id="tmpl-drplus-time-slot">
	<#
		var disabledClass = ( !data.available ) ? ' disabled' : '';
		var ariaDisabled = ( !data.available ) ? 'true' : 'false';
		var ariaLabel = data.time + ' - ' + data.label;
	#>
	<div
		class="booking-time-slot{{ disabledClass }}"
		data-timestamp="{{{data.timestamp}}}"
		role="button"
		tabindex="{{ !data.available ? '-1' : '0' }}"
		aria-disabled="{{ ariaDisabled }}"
		aria-label="{{ ariaLabel }}"
	>
		<span class="booking-time-slot-time">{{{data.time}}}</span>
		<span class="booking-time-slot-divider" aria-hidden="true"></span>
		<span class="booking-time-slot-label">{{{data.label}}}</span>
		<i class="drplus-icon-tick booking-time-slot-selected-icon" aria-hidden="true"></i>
	</div>
</script>