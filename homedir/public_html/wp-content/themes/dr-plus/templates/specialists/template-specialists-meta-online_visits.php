<?php

use DrPlus\Utils;
use DrPlus\Utils\Booking;

if( !defined( 'ABSPATH' ) ) exit;

if( empty( $args['specialist'] ) ) return;
if( !Booking::is_booking_active() ) return;

$specialist = $args['specialist'];

// Get consultation visit time
$consultation_times = [];
$specialist->offices = Utils::obj_to_array( $specialist->offices, true );
$active_consultation_offices = array_keys( Booking::consultation_offices( true ) );
foreach( $specialist->offices as $office_id => $office ) {
	if( empty( $office['type'] ) ) continue;
	if( $office['type'] == 'consultation' && in_array( $office_id, $active_consultation_offices ) && Utils::to_bool( $office['enable_booking'] ) ) {
		if( !empty( $office['visit_time'] ) ) {
			$consultation_times[] = $office['visit_time'];
		}
	}
}

if( !empty( $consultation_times ) ) {
	$consultation_times = array_unique( $consultation_times );
	$min_consultation_time = min( $consultation_times );
	if( count( $consultation_times ) > 1 ) {
		$consultation_time_text = sprintf( esc_html__( 'from %s minutes', 'drplus' ), $min_consultation_time );
	} else {
		$consultation_time_text = sprintf( esc_html__( '%s minutes', 'drplus' ), $min_consultation_time );
	}
	?>
	<?php if( !empty( $min_consultation_time ) ) { ?>
		<a href="<?php echo $args['page_link'] ?>" class="specialist-meta specialist-meta-consulting-duration">
			<div class="specialist-meta-title"><?php esc_html_e( 'Duration of consultation', 'drplus' ) ?></div>
			<div class="specialist-meta-value">
				<?php echo $consultation_time_text ?>
			</div>
		</a>
	<?php } ?>
<?php } ?>


<?php if( false ) { ?>
	<a href="<?php echo $args['page_link'] ?>" class="specialist-meta specialist-meta-response-status">
		<div class="specialist-meta-title"><?php esc_html_e( 'Response status', 'drplus' ) ?></div>
		<div class="specialist-meta-value">آماده پاسخگویی</div>
	</a>
<?php } ?>