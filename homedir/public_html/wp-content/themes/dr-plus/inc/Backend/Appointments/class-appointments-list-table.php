<?php
namespace DrPlus\Backend\Appointments;

use DrPlus\Model\Booking as ModelBooking;
use DrPlus\Model\Specialists;
use DrPlus\Utils;
use DrPlus\Utils\Booking;

class AppointmentsListTable extends \WP_List_Table {
	private static $current_order_meta = null;
	private static $order_statuses = [];
	private static $status_filters = [];
	private static $consultation_offices = [];

	/** Class constructor */
	public function __construct() {
		parent::__construct( [
			'singular'	=> __( 'Appointment', 'drplus' ), //singular name of the listed records
			'plural'	=> __( 'Appointments', 'drplus' ), //plural name of the listed records
			'ajax'		=> false	//should this table support ajax?
		] );
		self::$order_statuses = Booking::get_order_statuses();
		self::$status_filters = Booking::get_status_filters();
		self::$consultation_offices = Booking::consultation_offices();
	}

	/**
	* Retrieve appointments’s data from the database
	*
	* @param int $per_page
	* @param int $page_number
	*
	* @return mixed
	*/
	public static function get_appointments( $per_page = 20, $page_number = 1 ) {
		$limit = $per_page;
		$offset = ( $page_number - 1 ) * $per_page;

		$appointments = ModelBooking::query()->orderBy( '`book_id`', 'desc' )->limit( $limit )->offset( $offset );

		if( !empty( $_GET['status'] ) ) {
			$status = Utils::convert_chars( $_GET['status'] );
			$status_filters = Booking::get_status_filters( $status );
			$appointments = $appointments->whereIn( 'order_status', $status_filters['statuses'] );
		}

		$appointments = $appointments->get();

		return $appointments->toArray();
	}

	public function no_items() {
		_e( 'No appointment has been registered yet.', 'drplus' );
	}

	public function get_views() {
		$current = !empty( $_GET['status'] ) ? Utils::convert_chars( $_GET['status'] ) : 'all';

		$items = self::$status_filters;

		$views = [];
		foreach( $items as $status => $data ) {
			if( $status == 'all' ) {
				$url = admin_url( 'admin.php?page=appointments' );
				$data['value'] = ModelBooking::query()->count();
			} else {
				$url = add_query_arg( 'status', $status );
				$data['value'] = ModelBooking::query()->whereIn( 'order_status', $data['statuses'] )->count();
			}
			$views[$status] = sprintf(
				'<a href="%s"%s>%s <span class="count">(%s)</span></a>',
				esc_url( $url ),
				$current === $status ? ' class="current"' : '',
				$data['text'],
				$data['value']
			);
		}
	
		return $views;
	}

	/**
	* Render a column when no column specific method exists.
	*
	* @param array $item
	* @param string $column_name
	*
	* @return mixed
	*/
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'specialist':
				return sprintf( '%s <br> (%s)', self::$current_order_meta['specialist_name'], self::$current_order_meta['specialist_subtitle'] );
			case 'appointment_type':
				return in_array( $item['office_id'], array_keys( self::$consultation_offices ) ) ? self::$consultation_offices[$item['office_id']]['label'] : esc_html__( 'in-person visit', 'drplus' );
			case 'time':
				return sprintf( '%s<br>%s', Utils::convert_chars( self::$current_order_meta['date'] ), self::$current_order_meta['start_time'] );
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	/**
	* Method for id column
	*
	* @param array $item an array of DB data
	*
	* @return string
	*/
	function column_patient( $item ) {
		// Get book order meta
		$order = wc_get_order( $item['order_id'] );
		if( !empty( $order ) ) {
			self::$current_order_meta = $order->get_meta( '_booking_data' );
		}
		if( empty( $order ) || empty( self::$current_order_meta ) ){
			// Get needed data
			$specialist = (new Specialists)->find( $item['specialist_id'] );
			$customer_user = get_user_by( 'id', $item['customer_id'] );
			$specialist_user = get_user_by( 'id', $specialist->user_id );
			self::$current_order_meta = [
				'first_name' 			=> $customer_user->display_name ?? esc_html__( 'Customer', 'drplus' ),
				'last_name'				=> "",
				'specialist_name'		=> $specialist_user->display_name,
				'specialist_subtitle'	=> $specialist->subtitle,
				'date'					=> date_i18n( "d F Y", strtotime( $item['date'] ) ),
				'start_time'			=> $item['start_time'],
			];
		}

		// Process actions links
		// $cancel_nonce = wp_create_nonce( "drplus_cancel_appointment-{$item['book_id']}" );
		$view_link = add_query_arg( [
			'tab'		=> 'view',
			'book_id'	=> $item['book_id'],
		] );
		$edit_link = add_query_arg( ['action' => 'edit'], $view_link );
		if( empty( $item['order_id'] ) ) {
			return sprintf( '<span><strong>#%s %s %s</strong></span>', $item['book_id'], self::$current_order_meta['first_name'], self::$current_order_meta['last_name'] );
		}
		$title = sprintf( '<a href="%s"><strong>#%s %s %s</strong></a>', $view_link, $item['book_id'], self::$current_order_meta['first_name'], self::$current_order_meta['last_name'] );

		$actions = [
			'view'	=> sprintf( '<a href="%s">%s</a>', $view_link, esc_html__( 'View details', 'drplus' ) ),
			'edit'	=> sprintf( '<a href="%s">%s</a>', $edit_link, esc_html__( 'Edit', 'drplus' ) ),
			// 'delete' => sprintf(
			// 	'<a href="?page=%s&action=%s&book_id=%s&_wpnonce=%s">%s</a>',
			// 	esc_attr( $_GET['page'] ),
			// 	'cancel',
			// 	absint( $item['book_id'] ),
			// 	$cancel_nonce,
			// 	esc_html__( 'Cancel appointment', 'drplus' )
			// ),
		];

		if( in_array( $item['order_status'], self::$status_filters['cancelled'] ) ) {
			unset( $actions['delete'] );
		}
		
		return $title . $this->row_actions( $actions );
	}

	/**
	* Method for id column
	*
	* @param array $item an array of DB data
	*
	* @return string
	*/
	function column_status( $item ) {
		$text = sprintf( '<span class="appointments_app-status appointments_app-status-%s">%s</span>', $item['order_status'], self::$order_statuses[$item['order_status']] );

		if( empty( $item['order_id'] ) ) {
			$text .= '<div class="appointments_app-status-desc">' . esc_html__( 'Reservation not completed by user', 'drplus' ) . '</div>';
		}

		return $text;
	}

	/**
	* Render the bulk edit checkbox
	*
	* @param array $item
	*
	* @return string
	*/
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['book_id']
		);
	}

	/**
	* Returns the count of records in the database.
	*
	* @return null|string
	*/
	public static function record_count() {
		$app_counts = ModelBooking::query();
		if( !empty( $_GET['status'] ) ) {
			$items = self::$status_filters;
			$status = Utils::convert_chars( $_GET['status'] );
			$app_counts = $app_counts->whereIn( 'order_status', $items[$status]['statuses'] );
		}
		return $app_counts->count();
	}

	/**
	* Associative array of columns
	*
	* @return array
	*/
	function get_columns() {
		$columns = [
			// 'cb'				=> '<input type="checkbox" />',
			'patient'			=> __( 'Patient name', 'drplus' ),
			'specialist'		=> __( 'Specialist name', 'drplus' ),
			'appointment_type'	=> __( 'Appointment type', 'drplus' ),
			'time'				=> __( 'Time', 'drplus' ),
			'status'			=> __( 'Status', 'drplus' ),
		];
		
		return $columns;
	}

	/**
	* Columns to make sortable.
	*
	* @return array
	*/
	public function get_sortable_columns() {
		$sortable_columns = [];
		
		return $sortable_columns;
	}

	/**
	* Returns an associative array containing the bulk action
	*
	* @return array
	*/
	public function get_bulk_actions() {
		$actions = [
			// 'bulk-cancel' => esc_html__( 'Cancel Appointments', 'drplus' )
		];
		
		return $actions;
	}

	/**
	* Handles data query and filter, sorting, and pagination.
	*/
	public function prepare_items() {

		$this->_column_headers = $this->get_column_info();
		
		/** Process bulk action */
		$this->process_bulk_action();
		
		$per_page = $this->get_items_per_page( 'appointments_per_page', 12 );
		$current_page = $this->get_pagenum();
		$total_items = self::record_count();
		
		$this->set_pagination_args( [
			'total_items'	=> $total_items, //WE have to calculate the total number of items
			'per_page'		=> $per_page //WE have to determine how many items to show on a page
		] );
		
		$this->items = self::get_appointments( $per_page, $current_page );
	}

	public function process_bulk_action() {
		// Detect when a bulk action is being triggered...
		$action = $this->current_action();
		if( !empty( $_REQUEST["_wpnonce"] ) ) {
			$nonce = Utils::convert_chars( $_REQUEST["_wpnonce"] );
		}
		if( !empty( $nonce ) ) {
			if( 'cancel' == $action && !empty( $_GET['book_id'] ) ) {
				$item_id = Utils::convert_chars( $_GET['book_id'], true, 'absint' );
				
				if( !wp_verify_nonce( $nonce, "drplus_cancel_appointment-{$item_id}" ) ) {
					wp_die( esc_html__( 'Unable to Cancel appointment', 'drplus' ) );
				} else {
					// Process to cancel appointment
				
					wp_redirect( esc_url( add_query_arg() ) );
					exit;
				}
			}
		}
			
		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-cancel' ) || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-cancel' ) ) {
			$cancel_ids = esc_sql( $_POST['bulk-cancel'] );
			// loop over the array of record IDs and delete them
			foreach ( $cancel_ids as $id ) {
				// Process to cancel appointment
			}
		
			wp_redirect( esc_url( add_query_arg() ) );
			exit;
		}
	}
}