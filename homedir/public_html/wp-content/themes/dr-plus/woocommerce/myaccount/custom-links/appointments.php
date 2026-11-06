<?php

use DrPlus\Components\Button;
use DrPlus\Model\Booking as ModelBooking;
use DrPlus\Utils;
use DrPlus\Utils\Booking;
use DrPlus\Utils\Date;
use DrPlus\Utils\Options;

if( !empty( $_GET['order_id'] ) ) {
	$order_id = Utils::convert_chars( $_GET['order_id'], true, 'absint' );
	$order = wc_get_order( $order_id );

	if( !empty( $order ) ) {
		$book_data = $order->get_meta( '_booking_data' );
	}

	if( empty( $order ) || empty( $book_data ) || $order->get_customer_id() != get_current_user_id() ) { ?>
		<div class="drplus-app-empty-order">
			<?php echo esc_html__( 'You have no access to this order.', 'drplus' ) ?>
		</div>
		<?php return; ?>
	<?php }

	if( !empty( $order ) ) {
		get_template_part( "templates/booking/template-booking-step", 'receipt', [
			'book_data'		=> $book_data,
			'order'			=> $order,
			'show_back_btn'	=> true,
			'view_type'		=> 'user',
		] );
		return;
	}
}

$page = Utils::convert_chars( $_GET['apps-page'] ?? 1, true, 'absint' );
if( $page < 1 ) $page = 1;
$ppp = 10;
$offset = ($page - 1) * $ppp;

$current_filter = !empty( $_GET['filter'] ) ? Utils::convert_chars( $_GET['filter'] ) : "all";
$current_filter = Utils::ensure_values_in_array( $current_filter, ['ongoing', 'cancelled', 'completed', 'all', 'pending', 'processing'], 'all' );
if( $current_filter === 'processing' ) $current_filter = 'ongoing';

// compute threshold time for completed/processing conditions (same logic used for counts)
$current_time = Date::maybe_j2g( date_i18n('Y-m-d H:i:s' ) );
$date = new \DateTime( $current_time );
$time_1hr_ago = $date->modify('-60 minutes')->format( 'Y-m-d H:i:s' );
$user_id = get_current_user_id();

$appointments = ModelBooking::query()
	->select( ['book_id', 'date', 'start_time', 'order_status', 'order_id', 'office_id'] )
	->orderBy( '`date`', 'desc' )
	->orderBy( 'start_time', 'asc' );

if( $current_filter == 'cancelled' ) {
	$appointments = $appointments
		->where( 'customer_id', $user_id )
		->whereIn( 'order_status', ['failed', 'cancelled', 'refunded'] );
} else if( $current_filter == 'pending' ) {
	$appointments = $appointments
		->where( 'customer_id', $user_id )
		->where( 'order_status', 'pending' );
} else if( $current_filter == 'completed' ) {
	$appointments = $appointments
		->where( function( $query ) use ( $user_id ) {
			$query->where( 'customer_id', $user_id );
			$query->where( 'order_status', 'completed' );
		} )
		->orWhere( function( $query ) use ( $time_1hr_ago, $user_id ) {
			$query->where( 'customer_id', $user_id );
			$query->where( 'order_status', 'processing' );
			$query->where( "CONCAT(`date`, ' ', `start_time`)", '<', $time_1hr_ago );
		} );
} else if( $current_filter == 'ongoing' ) {
	$appointments = $appointments
		->where( 'customer_id', $user_id )
		->where( 'order_status', 'processing' )
		->where( "CONCAT(`date`, ' ', `start_time`)", '>=', $time_1hr_ago );
} else { // All
	$appointments = $appointments
		->where( 'customer_id', $user_id )
		->whereNot( 'order_status', NULL );
}

$appointments = $appointments
	->limit( intval( $ppp ) )
	->offset( intval( $offset ) )
	->get()
	->toArray();

// Aggregate counts in one query
$counts = ModelBooking::query()
	->where( 'customer_id', $user_id )
	->select( [
		"SUM(CASE WHEN `order_status` IN ('failed','cancelled','refunded') THEN 1 ELSE 0 END) AS cancelled_count",
		"SUM(CASE WHEN `order_status` = 'pending' THEN 1 ELSE 0 END) AS pending_count",
		"SUM(CASE WHEN `order_status` = 'completed' OR (`order_status` = 'processing' AND CONCAT(`date`, ' ', `start_time`) < DATE_SUB(NOW(), INTERVAL 1 HOUR)) THEN 1 ELSE 0 END) AS completed_count",
		"SUM(CASE WHEN `order_status` = 'processing' AND CONCAT(`date`, ' ', `start_time`) >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 1 ELSE 0 END) AS ongoing_count",
		"COUNT(*) AS total_count",
		]
	)->first();

$status_filters = Booking::get_status_filters();
$status_filters['cancelled']['value'] = intval( $counts->cancelled_count ?? 0 );
$status_filters['pending']['value']   = intval( $counts->pending_count ?? 0 );
$status_filters['completed']['value'] = intval( $counts->completed_count ?? 0 );
$status_filters['ongoing']['value']   = intval( $counts->ongoing_count ?? 0 );
$status_filters['all']['value']       = intval( $counts->total_count ?? 0 );

// filter appointments by status
foreach( $appointments as $app_index => $appointment ) {	
	if( $current_filter != 'all' ) {
		$appointments[$app_index]['status_label'] = $status_filters[$current_filter]['text'];
	} else {
		if( in_array( $appointment['order_status'], ['failed', 'cancelled', 'refunded'] ) ) { // failed, cancelled, refunded
			$appointments[$app_index]['status_label'] = $status_filters['cancelled']['text'];
		} else if( $appointment['order_status'] == 'pending' || !in_array( $appointment['order_status'], ['ongoing', 'cancelled', 'completed', 'all', 'pending', 'processing'] ) ) { // pending
			$appointments[$app_index]['status_label'] = $status_filters['pending']['text'];
		} else if( "{$appointment['date']} {$appointment['start_time']}" < $time_1hr_ago || $appointment['order_status'] == 'completed' ) { // completed
			// We also check time with OR operator because change order to completed is doing with cronjobs every 5 min.
			$appointments[$app_index]['status_label'] = $status_filters['completed']['text'];
		} else { // ongoing
			$appointments[$app_index]['status_label'] = $status_filters['ongoing']['text'];
		}
	}
}

$consultation_offices = Booking::consultation_offices();

?>
<h2 class="drplus-myaccount-page-title"><?php esc_html_e( 'Your appointments', 'drplus' ) ?></h2>

<div id="appointments-filters">
	<?php if( !empty( $status_filters['all']['value'] ) ) { ?>
		<?php foreach( $status_filters as $filter_key => $filter ) { ?>
			<a href="<?php echo esc_url( $filter['link'] ) ?>" class="appointments-filter-item<?php echo $current_filter == $filter_key ? ' current-filter' : "" ?>"><?php printf( '%s (%s)', esc_html( $filter['text'] ), esc_html( $filter['value'] ) ) ?></a>
		<?php } ?>
	<?php } ?>
</div>
<?php if( empty( $appointments ) ) : ?>
	<?php
	$options = Options::get_options( [
		'wc_empty_appointments_text'	=> esc_html__( 'You have not booked any appointments yet.', 'drplus' ),
	] );	
	?>
	<div class="empty-page">
		<i class="empty-page-icon empty-cart-icon drplus-icon-calendar-2"></i>
		<div class='empty-page-text'>
			<?php echo esc_html( $options['wc_empty_appointments_text']  ) ?>
		</div>
	</div>
<?php else : ?>
	<div id="appointments-content">
		<table id="appointments" class="drplus-appointments-table">
			<thead>
				<tr>
					<th>
						<span><?php echo esc_html__( 'Visit ID', 'drplus' ) ?></span>
					</th>
					<th>
						<span><?php echo esc_html__( 'Appointment type', 'drplus' ) ?></span>
					</th>
					<th>
						<span><?php echo esc_html__( 'Status', 'drplus' ) ?></span>
					</th>
					<th>
						<span><?php echo esc_html__( 'Visit date', 'drplus' ) ?></span>
					</th>
					<th>
						<span><?php echo esc_html__( 'Actions', 'drplus' ) ?></span>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach( $appointments as $appointment ) { ?>
					<tr>
						<td>
							<span class="woocommerce-orders-table__cell-col-name"><?php echo esc_html__( 'Visit ID', 'drplus' ) ?></span>
							<span><?php echo esc_html( $appointment['book_id'] ) ?></span>
						</td>
						<td>
							<span class="woocommerce-orders-table__cell-col-name"><?php echo esc_html__( 'Appointment type', 'drplus' ) ?></span>
							<span><?php echo in_array( $appointment['office_id'], array_keys( $consultation_offices ) ) ? $consultation_offices[$appointment['office_id']]['label'] : esc_html__( 'in-person visit', 'drplus' ) ?></span>
						</td>
						<td class="order-status-is-<?php echo esc_attr( $appointment['order_status'] ) ?>">
							<span class="woocommerce-orders-table__cell-col-name"><?php echo esc_html__( 'Status', 'drplus' ) ?></span>
							<span><?php echo esc_html( $appointment['status_label'] ) ?></span>
						</td>
						<td>
							<span class="woocommerce-orders-table__cell-col-name"><?php echo esc_html__( 'Visit date', 'drplus' ) ?></span>
							<span><?php echo esc_html( date_i18n( Utils::is_iran_timezone() ? 'Y/m/d' : get_option( 'date_format' ), strtotime( $appointment['date'] ) ) ) ?></span>
						</td>
						<td>
							<?php Button::view( [
								'text'		=> esc_html__( 'View', 'drplus' ),
								'link'		=> add_query_arg( 'order_id', $appointment['order_id'], strtok( $_SERVER["REQUEST_URI"], '?' ) ),
								'small'		=> true,
								'type'		=> 'action',
								'fullwidth'	=> true,
							] ) ?>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
		<?php
		$max_num_page = ceil( $status_filters[$current_filter]['value']  / $ppp );
		if( $max_num_page > 1 ) {
			get_template_part( 'templates/archives/template-archives-pagination', 'custom', [
				'max_num_pages'		=> $max_num_page,
				'paged'				=> $page,
				'query_arg_name'	=> 'apps-page',
			] );
		}
		?>
	</div>
<?php endif; ?>
