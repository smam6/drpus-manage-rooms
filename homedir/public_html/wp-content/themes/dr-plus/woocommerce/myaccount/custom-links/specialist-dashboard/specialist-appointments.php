<?php

use DrPlus\Components\Button;
use DrPlus\Model\Booking as ModelBooking;
use DrPlus\Utils;
use DrPlus\Utils\Booking;
use DrPlus\Utils\Date;

$show_save_button = false;

if( !empty( $_GET['order_id'] ) ) {
	$back_link = remove_query_arg( 'order_id' );
	get_template_part( 'templates/specialists/template-specialists-appointment', null, [
		'specialist_id'	=> $specialist->id,
	] );
	return;
}

// Get day from filter
$app_date = Utils::convert_chars( $_GET['app_date'] ?? '' );
$show_specific_date_apps = false;
if( !empty( $app_date ) ) {
	$show_specific_date_apps = true;
	$app_date_gregorian = Date::maybe_j2g( $app_date );
	// $app_date_gregorian = Utils::convert_chars( $app_date_gregorian );
	$app_date_timestamp = date( 'U', strtotime( $app_date_gregorian ) );
}

// Get specialist apps of only one day
$apps = ModelBooking::query()
	->select( ['start_time', 'customer_id', 'order_id', 'office_id', 'order_status'] )
	->where( 'specialist_id', $specialist->id )
	->where( 'order_id', '!=', null );

if( $show_specific_date_apps ) {
	$apps = $apps->where( '`date`', $app_date_gregorian );
} else {
	$ppp = 12;
	$page_number = Utils::convert_chars( $_GET['apps_page'] ?? 1, true, 'absint' );
	$total_apps_count = $apps->count();
	
	// Show last 10 incoming app
	$apps = $apps->limit( $ppp )->offset( ( $page_number-1 ) * $ppp )->orderBy( '`date`', 'desc' )->orderBy('start_time', 'desc');
}
$apps = $apps->whereIn( 'order_status', ['processing', 'completed', 'cancelled'] )->get()->toArray();

$consultation_offices = Booking::consultation_offices();
?>

<h2 class="drplus-myaccount-page-title"><?php echo esc_html( $sections['specialist-appointments']['label'] ) ?></h2>
<form action="" method="get" class="drplus-specialist-form">
	<div class="drplus-specialist-apps-filters-wrap">
		<h3 class="drplus-specialist-apps-title"><?php echo esc_html__( 'Filter appointments', 'drplus' ) ?></h3>
		<input
			type="text"
			class="drplus-datepicker-input"
			id="drplus_app_date_input"
			data-time="<?php echo esc_attr( $app_date_timestamp ?? "" ) ?>",
			data-options='<?php echo wp_json_encode( ['altFormat' => 'YYYY-MM-DD'] ) ?>'
			placeholder="<?php echo esc_html__( 'Select date', 'drplus' ) ?>"
			readonly
		>
		<input type="hidden" name="app_date" id="drplus_app_date_alt">
		<?php
		Button::view( [
			'text'	=> esc_html__( 'Filter', 'drplus' ),
			'small'	=> true,
			'type'	=> 'bordered',
			'align'	=> 'end',
			'atts'	=> [
				'type'	=> 'submit'
			]
		] )
		?>
	</div>


	<div class="drplus-specialist-apps-container">
		<?php if( $show_specific_date_apps ) { ?>
			<h3 class="drplus-specialist-apps-title">
				<?php printf( esc_html_x( 'Appointments of %s', 'date title', 'drplus' ), date_i18n( 'd F Y', $app_date_timestamp ) ); ?>
			</h3>
		<?php } ?>

		<div class="drplus-specialist-apps-list">
			<?php if( empty( $apps ) ) { ?>
				<div class="empty-page">
					<i class="empty-page-icon empty-apps-icon drplus-icon-calendar-2"></i>
					<p class="empty-page-text empty-apps-text">
						<?php if( $show_specific_date_apps ) {						
							esc_html_e( 'No appointments have been scheduled for you on the selected date.', 'drplus' );
						} else {
							esc_html_e( 'No appointments have been scheduled for you.', 'drplus' );
						} ?>
					</p>
				</div>
			<?php } else { ?>
				<?php foreach( $apps as $app ) { ?>
					<?php
					$order = wc_get_order( $app['order_id'] );
					if( empty( $order ) ) continue; // to Check maybe order was removed manually!
					$book_data = [];
					foreach( $order->get_meta_data() as $meta ) {
						if( $meta->key == '_booking_data' ) {
							$book_data = $meta->value;
							break;
						}
					}
					// backward compatibility
					if( $app['office_id'] == 'consultation' ) $app['office_id'] = 'phone_consultation';
					?>
					<a href="<?php echo add_query_arg( 'order_id', $app['order_id'], remove_query_arg( 'app_date' ) ) ?>" class="drplus-specialist-apps-item status-<?php echo $app['order_status'] ?> drplus-app-<?php echo $app['office_id'] ?>">
						<div class="drplus-specialist-apps-item-time-container">
							<?php if( !$show_specific_date_apps ) { ?>
								<div class="drplus-specialist-apps-item-time-wrap">
									<span class="drplus-specialist-apps-item-date"><?php echo date_i18n( 'j F', $book_data['raw_date'] ) ?></span>
									<span class="drplus-specialist-apps-item-date-year"><?php echo date_i18n( 'Y', $book_data['raw_date'] ) ?></span>
								</div>
							<?php } ?>
							<div class="drplus-specialist-apps-item-time-wrap">
								<span class="drplus-specialist-apps-item-time"><?php echo $book_data['start_time'] ?></span>
								<span class="drplus-specialist-apps-item-time-period"><?php echo Date::get_time_period( (int) explode( ':', $book_data['start_time'] )[0] ) ?></span>
							</div>
						</div>
						<div class="drplus-specialist-apps-item-customer-wrap">
							<span class="drplus-specialist-apps-item-customer-title"><?php echo esc_html__( 'Patient name', 'drplus' ); ?>:</span>
							<span class="drplus-specialist-apps-item-customer-name"><?php printf( '%s %s', esc_html( $book_data['first_name'] ), esc_html( $book_data['last_name'] ) ) ?></span>
						</div>
						<div class="drplus-specialist-apps-item-type-wrap">
							<?php echo in_array( $app['office_id'], array_keys( $consultation_offices ) ) ? $consultation_offices[$app['office_id']]['label'] : esc_html__( 'in-person visit', 'drplus' ) ?>
						</div>
						<div class="drplus-specialist-apps-item-status status-<?php echo $app['order_status'] ?>">
							<?php echo Booking::get_order_statuses()[$app['order_status']] ?>
						</div>
					</a>
				<?php } ?>
			<?php } ?>
		</div>

		<div class="drplus-specialist-apps-pagination-wrap">
			<?php if( $show_specific_date_apps ) { ?>
				<?php
				// Date pagination: 3 previous, current, 3 next
				$next_date = strtotime( "$app_date_gregorian +1 day" );
				$next_date = Utils::convert_chars( date_i18n( 'Y-m-d', $next_date ) );
				$previous_date = strtotime( "$app_date_gregorian -1 day" );
				$previous_date = Utils::convert_chars( date_i18n( 'Y-m-d', $previous_date ) );
				?>
				<?php Button::view( [
					'text'			=> esc_html__( 'Next day', 'drplus' ),
					'link'			=> esc_url( add_query_arg( 'app_date', $next_date, remove_query_arg( 'order_id' ) ) ),
					'type'			=> 'bordered',
					'small'			=> true,
					'align'			=> 'start',
					'icon'			=> 'drplus-icon-chevron-right-dot',
					'icon_align'	=> 'start',
				] ) ?>
				<?php Button::view( [
					'text'			=> esc_html__( 'Previous day', 'drplus' ),
					'link'			=> esc_url( add_query_arg( 'app_date', $previous_date, remove_query_arg( 'order_id' ) ) ),
					'type'			=> 'bordered',
					'small'			=> true,
					'align'			=> 'end',
					'icon'			=> 'drplus-icon-chevron-left-dot',
					'icon_align'	=> 'end',
				] ) ?>
				
			<?php } else {
				get_template_part( "templates/archives/template-archives-pagination", 'custom', [
					'max_num_pages'		=> ceil( $total_apps_count / $ppp ),
					'paged'				=> $page_number,
					'query_arg_name'	=> 'apps_page',
				] );
			} ?>
		</div>
	</div>
</form>