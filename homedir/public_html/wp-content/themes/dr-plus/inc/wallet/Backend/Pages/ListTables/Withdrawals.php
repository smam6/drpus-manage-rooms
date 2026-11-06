<?php
namespace Sheyda\Wallet\Backend\Pages\ListTables;

use SheydaWalletUtils as WalletUtils;
use MJ\Whitebox\Utils\Formatters;
use Sheyda\Wallet\Models\Withdrawals as WithdrawalsModel;

if( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Withdrawals extends \WP_List_Table {
	private $withdrawals = [];
	private $results = [];
	private $users = [];
	public $show_all = false;

	public function __construct() {
		parent::__construct( [
			'singular'	=> __( 'Withdrawal', 'sheyda_wallet' ),
			'plural'	=> __( 'Withdrawals', 'sheyda_wallet' ),
			'ajax'		=> false,
		] );
	}

	private function get_withdrawals( $per_page = 50, $page_number = 1 ) {
		if( empty( $this->withdrawals ) ) {
			$withdrawals = WithdrawalsModel::query();
			if( !$this->show_all ) {
				$user_id = WalletUtils::convert_chars( $_GET['user'], true, 'absint' );
				$withdrawals->where( 'user_id', $user_id );
			}

			// Search by user display name/login/email or user ID
			if( $this->show_all && !empty( $_REQUEST['s'] ) ) {
				$search_term = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) );
				$user_ids = [];
				$searched_id = WalletUtils::convert_chars( $search_term, true, 'absint' );
				if( !empty( $searched_id ) ) {
					$user_ids[] = $searched_id;
				}
				$user_query = new \WP_User_Query( [
					'search'          => '*' . $search_term . '*',
					'search_columns'  => ['user_login', 'user_nicename', 'user_email', 'display_name'],
					'fields'          => 'ID',
					'number'          => 200,
				] );
				$user_ids = array_unique( array_merge( $user_ids, $user_query->get_results() ) );

				if( empty( $user_ids ) ) {
					$this->withdrawals = [
						'data'  => [],
						'total' => 0,
					];
				} else {
					$withdrawals->whereIn( 'user_id', $user_ids );
				}
			}

			// Filter by type/status
			$filter_type = !empty( $_REQUEST['type'] ) ? WalletUtils::convert_chars( wp_unslash( $_REQUEST['type'] ) ) : '';
			if( !empty( $filter_type ) && in_array( $filter_type, array_keys( WithdrawalsModel::statuses() ) ) ) {
				$withdrawals->where( 'status', $filter_type );
			}

			// Sorting
			$sortable_columns = [
				'amount'       => 'amount_net',
				'created_date' => 'created_at',
				'status'       => 'status',
				'type'         => 'status', // Alias for status
			];
			$orderby = !empty( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'created_date';
			$order = ( !empty( $_REQUEST['order'] ) && strtolower( $_REQUEST['order'] ) === 'asc' ) ? 'ASC' : 'DESC';
			$order_column = $sortable_columns[$orderby] ?? 'created_at';
			$withdrawals->orderBy( $order_column, $order );
			if( $order_column !== 'id' ) {
				$withdrawals->orderBy( 'id', 'DESC' );
			}

			if( empty( $this->withdrawals ) ) {
				$this->withdrawals = $withdrawals->paginate( $per_page, $page_number );
			}
		}
		if( empty( $this->results ) ) {
			$index = ( ( $page_number - 1 ) * $per_page ) + 1;
			$this->results = [];
			foreach( $this->withdrawals['data'] as $withdrawal ) {
				$this->results[] = $withdrawal;
				$index++;
			}
		}
		return $this->results;
	}

	public function no_items() {
		_e( 'No withdrawal have been recorded yet.', 'sheyda_wallet' );
	}

	/**
	* Associative array of columns
	*
	* @return array
	*/
	public function get_columns() {
		$columns = [
			'user'			=> __( 'User', 'sheyda_wallet' ),
			'amount'		=> __( 'Amount', 'sheyda_wallet' ),
			'status'		=> __( 'Status', 'sheyda_wallet' ),
			'created_date'	=> __( 'Date', 'sheyda_wallet' ),
			'action'		=> __( 'Action', 'sheyda_wallet' )
		];

		if( !$this->show_all ) unset( $columns['user'] );
		
		return $columns;
	}

	/**
	 * Sortable columns
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		return [
			'status'       => ['status', false],
			'created_date' => ['created_at', true],
			'amount'       => ['amount_net', false],
		];
	}

	public function get_views() {
		$current_type = !empty( $_REQUEST['type'] ) ? WalletUtils::convert_chars( wp_unslash( $_REQUEST['type'] ) ) : 'all';
		$types = WithdrawalsModel::statuses();

		// Build base for counts: respect same user/search filters as the main query
		$user_id = WalletUtils::convert_chars( 
			!empty( $_GET['user'] ) ? $_GET['user'] : 0,
			true,
			'absint'
		);
		$search_term = $this->show_all && !empty( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';
		$user_ids = [];
		if( $this->show_all && !empty( $search_term ) ) {
			$searched_id = WalletUtils::convert_chars( $search_term, true, 'absint' );
			if( !empty( $searched_id ) ) {
				$user_ids[] = $searched_id;
			}
			$user_query = new \WP_User_Query( [
				'search'          => '*' . $search_term . '*',
				'search_columns'  => ['user_login', 'user_nicename', 'user_email', 'display_name'],
				'fields'          => 'ID',
				'number'          => 200,
			] );
			$user_ids = array_unique( array_merge( $user_ids, $user_query->get_results() ) );
		}

		$statuses_count = WithdrawalsModel::query()->select( ['COUNT(`id`) AS counts', 'status'] );
		if( !$this->show_all && !empty( $user_id ) ) {
			$statuses_count->where( 'user_id', $user_id );
		} else if( $this->show_all && $search_term !== '' ) {
			$statuses_count->whereIn( 'user_id', $user_ids );
		}
		$statuses_count = $statuses_count->groupBy( 'status' )->get()->pluck( 'counts', 'status' );

		// Base URL: remove pagination and existing type filter
		$base_url = remove_query_arg( ['paged', 'type'], wp_unslash( $_SERVER['REQUEST_URI'] ) );

		$views = [
			'all'	=> sprintf(
				'<a href="%s"%s>%s <span class="count">(%s)</span></a>',
				esc_url( $base_url ),
				$current_type === 'all' ? ' class="current"' : '',
				esc_html__( 'All', 'sheyda_wallet' ),
				array_sum( $statuses_count )
			)
		];
		foreach( $types as $status => $label ) {
			$url = esc_url( add_query_arg( 'type', $status, $base_url ) );

			$views[$status] = sprintf(
				'<a href="%s"%s>%s <span class="count">(%s)</span></a>',
				esc_url( add_query_arg( 'type', $status, $base_url ) ),
				$current_type === $status ? ' class="current"' : '',
				$label,
				$statuses_count[$status] ?? 0
			);
		}
	
		return $views;
	}

	/**
	* Returns the count of records in the database.
	*
	* @return null|string
	*/
	public function record_count() {
		return $this->withdrawals['total'];
	}

	public function prepare_items() {
		$this->_column_headers = [
			$this->get_columns(),
			[],
			$this->get_sortable_columns()
		];

		$current_page = $this->get_pagenum();
		$per_page = $this->get_items_per_page( 'items_per_page', 20 );
		$this->items = $this->get_withdrawals( $per_page, $current_page );

		$total_items = $this->record_count();
		
		$this->set_pagination_args( [
			'total_items'	=> $total_items,
			'per_page'		=> $per_page
		] );
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'user':
				if( empty( $this->users[$item['user_id']] ) ) {
					$this->users[$item['user_id']] = get_user_by( 'id', $item['user_id'] )->display_name;
				}
				return sprintf( '%d - %s', $item['id'], $this->users[$item['user_id']] );
			case 'amount':
				return Formatters::price( $item['amount_net'], true );
			case 'status':
				return '<span class="status-' . $item['status'] . '">' . WithdrawalsModel::statuses()[$item['status']] . '</span>';
			case 'created_date':
				$created_at_text = '';
				if( !empty( $item['created_at'] ) ) {
					$created_at_text = '<div>' . date_i18n( 'Y-m-d', $item['created_at']->format( 'U' ) ) . '</div>';
					$created_at_text .= '<small>' . date_i18n( 'H:i:s', $item['created_at']->format( 'U' ) ) . '</small>';
				}
				return $created_at_text;
			case 'action':
				$url = add_query_arg( ['id' => $item['id']] );
				return '<a href="' . $url . '" class="button-secondary" type="button">' . esc_html__( 'View', 'sheyda_wallet' ) . '</a>';
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}
}
