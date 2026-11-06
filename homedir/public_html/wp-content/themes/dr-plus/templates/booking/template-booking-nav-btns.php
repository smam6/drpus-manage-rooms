<?php

use DrPlus\Components\Button;
use DrPlus\Utils;

$args = Utils::check_default( $args, [
	'current_step'	=> '',
	'prev_step'		=> '',
	'booking_url'	=> '',
] );

if( $args['prev_step'] == 'specialist' && DRPLUS_SINGLE_SPECIALIST_MODE ) {
	$back_url = home_url();
} else {
	$back_url = esc_url( stripslashes( $args['booking_url'] ) . $args['prev_step'] );
}

?>
<div class="booking-nav-btns">
	<?php Button::view( [
		'text'		=> esc_html__( 'Continue', 'drplus' ),
		'type'		=> 'primary',
		'disabled'	=> true,
		'align'		=> 'start',
		'classes'	=> ['booking-next-step-btn', 'booking-nav-btn'],
		'atts'		=> [
			'type' => 'submit',
			'name'	=> 'booking_step_submit',
			'value'	=> esc_attr( $args['current_step'] )
		]
	] );
	Button::view( [
		'text'		=> esc_html__( 'Back', 'drplus' ),
		'type'		=> 'bordered',
		'align'		=> 'end',
		'classes'	=> ['booking-prev-step-btn', 'booking-nav-btn'],
		'link'		=> esc_url( $back_url )
	] );
	?>
</div>