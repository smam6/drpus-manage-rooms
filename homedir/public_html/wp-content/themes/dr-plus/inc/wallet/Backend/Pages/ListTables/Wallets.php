<?php
namespace Sheyda\Wallet\Backend\Pages\ListTables;

use MJ\Whitebox\Utils\Formatters;
use SheydaWalletUtils as WalletUtils;

if( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Wallets extends \WP_List_Table {
	public function __construct() {
		parent::__construct( [
			'singular'	=> __( 'Wallet', 'sheyda_wallet' ),
			'plural'	=> __( 'Wallets', 'sheyda_wallet' ),
			'ajax'		=> false
		] );
	}

	public static function get_users( $per_page = 20, $page_number = 1 ) {
		$users_args = [
			'number'	=> $per_page,
			'page'		=> $page_number,
		];
		if( !empty( $_GET['s'] ) ) {
			$users_args['search'] = '*' . sanitize_text_field( $_GET['s'] ) . "*";
		}
		$users = get_users( $users_args );
		$result = [];
		foreach( $users as $user ) {
			$user_balance = WalletUtils::get_user_balance( $user->ID );
			$result[] = [
				'user'			=> $user,
				'balance'		=> !empty( $user_balance ) ? $user_balance->balance - $user_balance->locked : 0,
				'locked'		=> !empty( $user_balance ) ? $user_balance->locked : 0,
				'updated_at'	=> !empty( $user_balance ) ? $user_balance->updated_at : '',
			];
		}
		return $result;
	}

	public function no_items() {
		_e( 'No wallets available.', 'sheyda_wallet' );
	}

	/**
	* Associative array of columns
	*
	* @return array
	*/
	public function get_columns() {
		$columns = [
			'user'			=> __( 'User', 'sheyda_wallet' ),
			'balance'		=> __( 'Balance', 'sheyda_wallet' ),
			'locked'		=> __( 'Locked amount', 'sheyda_wallet' ),
			'updated_at'	=> __( 'Latest update', 'sheyda_wallet' ),
			'actions'		=> __( 'Actions', 'sheyda_wallet' ),
		];
		
		return $columns;
	}

	/**
	* Returns the count of records in the database.
	*
	* @return null|string
	*/
	public static function record_count() {
		return get_user_count();
	}

	public function prepare_items() {
		$this->_column_headers = $this->get_column_info();
		
		$per_page = $this->get_items_per_page( 'items_per_page', 20 );
		$current_page = $this->get_pagenum();
		$total_items = self::record_count();
		
		$this->set_pagination_args( [
			'total_items'	=> $total_items, //WE have to calculate the total number of items
			'per_page'		=> $per_page //WE have to determine how many items to show on a page
		] );
		
		$this->items = self::get_users( $per_page, $current_page );
	}

	function column_user( $item ) {
		$details_page_url = admin_url( 'admin.php?page=sheyda-wallet&user=' . $item['user']->ID );
		$title = '<strong><a href="' . $details_page_url . '">' . $item['user']->display_name . '</a></strong>';

		$actions = [
			'details'	=> sprintf(
				'<a href="%s">%s</a>',
				$details_page_url,
				esc_html__( 'Details', 'sheyda_wallet' )
			),
		];
		return $title . $this->row_actions( $actions );
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'balance':
				return Formatters::price( $item['balance'], true );
			case 'locked':
				return Formatters::price( $item['locked'], true );
			case 'updated_at':
				$updated_at_text = '';
				if( !empty( $item['updated_at'] ) ) {
					$updated_at_text = '<div>' . date_i18n( 'Y-m-d', $item['updated_at']->format( 'U' ) ) . '</div>';
					$updated_at_text .= '<small>' . date_i18n( 'H:i:s', $item['updated_at']->format( 'U' ) ) . '</small>';
				}
				return $updated_at_text;
			case 'actions':
				$actions = [];
				$actions['adjust_credit'] = sprintf(
					'<a href="%s" class="button">%s</a>',
					admin_url( "admin.php?page=sheyda-wallet&action=adjust&user={$item['user']->ID}" ),
					esc_html_x( "Adjust credit", 'action btn' , 'sheyda_wallet' )
				);
				return implode( "", $actions );
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}
}