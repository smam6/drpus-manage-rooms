<?php

use DrPlus\Components\Alert;
use DrPlus\Utils;
use DrPlus\Utils\Booking;
use DrPlus\Utils\SubscriptionPlans;
use DrPlus\Utils\UtilsSpecialists;

if( !Booking::is_booking_active() ) {
	esc_html_e( 'Booking is not active', 'drplus' );
	return;
}

$booking_url = Booking::get_booking_page_url( "", true );
$active_step = Booking::get_current_step();

// Check for specialist plan
if( $active_step == 'time' ) {
	$specialist_id = Utils::convert_chars( $_GET['sid'] ?? 0, true, 'absint' );
	if( empty( $specialist_id ) && !empty( $_SESSION['booking']['specialist_id'] ) ) {
		$specialist_id = $_SESSION['booking']['specialist_id'] ?? 0;
	}
	if( !empty( $specialist_id ) ) {
		$specialist_user_id = UtilsSpecialists::get_user_id_by_specialist_id( $specialist_id );
		if( !SubscriptionPlans::is_specialist_plan_active( $specialist_user_id ) ) $active_step = 'specialist';
	}
}

// Next step
$next_step = $active_step;
$booking_steps = Booking::booking_steps();
$next_step_index = array_search( $active_step, array_keys( $booking_steps ) ) + 1;
if( $next_step_index < count( $booking_steps ) ) {
	$next_step = array_keys( $booking_steps )[$next_step_index];
}
$next_step_url = stripslashes( $booking_url ) . $next_step;

if( !empty( $_SESSION['booking']['error'] ) ) {
	Alert::view( [
		'type'		=> 'error',
		'icon'		=> 'drplus-icon-error',
		'text'		=> $_SESSION['booking']['error_message'],
		'classes'	=> ['booking-alert'],
	] );
	unset( $_SESSION['booking']['error'] );
	unset( $_SESSION['booking']['error_message'] );
}
?>

<?php if( $active_step == 'specialist' ) { ?>

	<div id="booking-<?php echo esc_attr( $active_step ) ?>_section" class="booking_wrap" data-section="<?php echo esc_attr( $active_step ) ?>">
		<?php get_template_part( "templates/booking/template-booking-step", $active_step, [
			'booking_url'	=> $booking_url
		] ); ?>
	</div>

<?php } else { ?>

	<form method="post" action="<?php echo $next_step_url ?>" id="booking-form">
		<?php wp_nonce_field( "booking_step_{$active_step}", 'booking_nonce' ) ?>
		<input type="hidden" name="booking_temp_id" value="<?php echo $active_step == 'time' ? uniqid() : $_SESSION['booking']['temp_id'] ?? "" ?>">

		<div id="booking-<?php echo esc_attr( $active_step ) ?>_section" class="booking_wrap" data-section="<?php echo esc_attr( $active_step ) ?>">
			<?php get_template_part( "templates/booking/template-booking-step", $active_step, [
				'booking_url'	=> $booking_url
			] ); ?>
		</div>
	</form>

<?php } ?>

<?php if( DRPLUS_DEV ) { ?>
	<pre class="ltr">
		<?php
		if( !empty( $_SESSION['booking'] ) ) {
			print_r( $_SESSION['booking'] );
		} else {
			echo "Session is empty";
		}
		?>
	</pre>
<?php } ?>