<?php

use DrPlus\Utils\Booking;
use DrPlus\Utils\Options;

if( !Booking::is_booking_active() ) return;
$options = Options::get_options( [
	'single_specialist_not_available_reserve_text'	=> esc_html__( 'Online appointment booking is not yet available for {name}', 'drplus' ),
] );

if( !defined( 'ABSPATH' ) ) exit;

if( empty( $args['specialist'] ) ) return;

?>
<div class="specialist-meta specialist-meta-inline specialist-meta-not-available-reserve">
	<span class="specialist-meta-title">
		<?php echo str_replace( '{name}', $args['specialist']->user->display_name, $options['single_specialist_not_available_reserve_text'] ) ?>
	</span>
</div>