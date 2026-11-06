<?php
namespace Sheyda\Wallet\Backend\Pages\ListTables;

use SheydaWalletUtils as WalletUtils;
use MJ\Whitebox\Utils\Formatters;
use Sheyda\Wallet\Models\Ledger;

if( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Ledgers extends \WP_List_Table {
	private $ledgers = [];
	private $results = [];
	private $users = [];
	public $show_all = false;

	public function __construct() {
		parent::__construct( [
			'singular'	=> __( 'Ledger', 'sheyda_wallet' ),
			'plural'	=> __( 'Ledgers', 'sheyda_wallet' ),
			'ajax'		=> false,
		] );
	}

	private function get_ledgers( $per_page = 50, $page_number = 1 ) {
		if( empty( $this->ledgers ) ) {
			$ledgers = Ledger::query();
			if( !$this->show_all ) {
				$user_id = WalletUtils::convert_chars( $_GET['user'], true, 'absint' );
				$ledgers->where( 'user_id', $user_id );
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
					$this->ledgers = [
						'data'  => [],
						'total' => 0,
					];
				} else {
					$ledgers->whereIn( 'user_id', $user_ids );
				}
			}

			// Filter by type
			$filter_type = !empty( $_REQUEST['type'] ) ? WalletUtils::convert_chars( wp_unslash( $_REQUEST['type'] ) ) : '';
			if( !empty( $filter_type ) && in_array( $filter_type, array_keys( Ledger::types() ) ) ) {
				$ledgers->where( 'type', $filter_type );
			}

			// Sorting
			$sortable_columns = [
				'type'         => 'type',
				'amount'       => 'amount',
				'created_date' => 'created_at',
			];
			$orderby = !empty( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'created_date';
			$order = ( !empty( $_REQUEST['order'] ) && strtolower( $_REQUEST['order'] ) === 'asc' ) ? 'ASC' : 'DESC';
			$order_column = $sortable_columns[$orderby] ?? 'created_at';
			$ledgers->orderBy( $order_column, $order );
			if( $order_column !== 'id' ) {
				$ledgers->orderBy( 'id', 'DESC' );
			}

			if( empty( $this->ledgers ) ) {
				$this->ledgers = $ledgers->paginate( $per_page, $page_number );
			}
		}
		if( empty( $this->results ) ) {
			$index = ( ( $page_number - 1 ) * $per_page ) + 1;
			$this->results = [];
			foreach( $this->ledgers['data'] as $ledger ) {
				$this->results[] = [
					'id'			=> $index,
					'user_id'		=> $ledger->user_id,
					'type'			=> $ledger->type,
					'amount'		=> $ledger->amount,
					'balance_after'	=> $ledger->balance_after,
					'created_by'	=> $ledger->created_by,
					'related_id'	=> $ledger->related_id,
					'description'	=> !empty( $ledger->meta ) && !empty( $ledger->meta['description'] ) ? $ledger->meta['description'] : '',
					'created_date'	=> $ledger->created_at,
					'meta'			=> $ledger->meta
				];
				$index++;
			}
		}
		return $this->results;
	}

	public function no_items() {
		_e( 'No transactions have been recorded yet.', 'sheyda_wallet' );
	}

	/**
	* Associative array of columns
	*
	* @return array
	*/
	public function get_columns() {
		$columns = [
			'user'			=> __( 'User', 'sheyda_wallet' ),
			'type'			=> __( 'Type', 'sheyda_wallet' ),
			'amount'		=> __( 'Amount', 'sheyda_wallet' ),
			'balance_after'	=> __( 'Balance after', 'sheyda_wallet' ),
			'created_by'	=> __( 'Created by', 'sheyda_wallet' ),
			'related_id'	=> __( 'Related ID', 'sheyda_wallet' ),
			'description'	=> __( 'Description', 'sheyda_wallet' ),
			'created_date'	=> __( 'Date', 'sheyda_wallet' ),
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
			'type'         => ['type', false],
			'amount'       => ['amount', false],
			'created_date' => ['created_at', true],
		];
	}

	/**
	 * Filters above the table
	 *
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {
		if( $which !== 'top' ) return;

		$current_type = !empty( $_REQUEST['type'] ) ? WalletUtils::convert_chars( wp_unslash( $_REQUEST['type'] ) ) : '';
		$types = Ledger::types();

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

		// Compute counts per type (respecting user_id and search when applicable)
		$counts = [];
		foreach( $types as $type_key => $type_label ) {
			$q = Ledger::query();
			if( !$this->show_all && !empty( $user_id ) ) {
				$q->where( 'user_id', $user_id );
			} else if( $this->show_all && $search_term !== '' ) {
				if( empty( $user_ids ) ) {
					$counts[$type_key] = 0;
					continue;
				}
				$q->whereIn( 'user_id', $user_ids );
			}
			$q->where( 'type', $type_key );
			$counts[$type_key] = (int) $q->count();
		}

		// Total count (all types)
		$total_q = Ledger::query();
		if( !$this->show_all && !empty( $user_id ) ) {
			$total_q->where( 'user_id', $user_id );
		} else if( $this->show_all && $search_term !== '' ) {
			if( empty( $user_ids ) ) {
				$total = 0;
			} else {
				$total_q->whereIn( 'user_id', $user_ids );
			}
		}
		if( !isset( $total ) ) {
			$total = (int) $total_q->count();
		}
		?>
		<div class="alignleft actions">
			<label class="screen-reader-text" for="filter-by-type"><?php esc_html_e( 'Filter by type', 'sheyda_wallet' ) ?></label>
			<select name="type" id="filter-by-type">
				<option value=""><?php printf( esc_html__( 'All types (%d)', 'sheyda_wallet' ), $total ) ?></option>
				<?php foreach( $types as $type_key => $type_label ) { ?>
					<option value="<?php echo esc_attr( $type_key ) ?>" <?php selected( $current_type, $type_key ) ?>>
						<?php echo esc_html( sprintf( '%s (%d)', $type_label, isset( $counts[$type_key] ) ? $counts[$type_key] : 0 ) ) ?>
					</option>
				<?php } ?>
			</select>
			<?php submit_button( __( 'Filter' ), '', 'filter_action', false ); ?>
		</div>
		<?php
	}

	/**
	* Returns the count of records in the database.
	*
	* @return null|string
	*/
	public function record_count() {
		return $this->ledgers['total'];
	}

	public function prepare_items() {
		$this->_column_headers = [
			$this->get_columns(),
			[],
			$this->get_sortable_columns()
		];

		$current_page = $this->get_pagenum();
		$per_page = $this->get_items_per_page( 'items_per_page', 20 );
		$this->items = $this->get_ledgers( $per_page, $current_page );

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
			case 'type':
				if( $this->show_all ) {
					return  Ledger::types()[$item['type']];
				} else {
					return sprintf( '%d - %s', $item['id'], Ledger::types()[$item['type']] );
				}
			case 'amount':
				return Formatters::price( $item['amount'], true );
			case 'balance_after':
				return Formatters::price( $item['balance_after'], true );
			case 'created_by':
				$text = '-----';
				if( $item['created_by'] === 0 ) {
					$text = esc_html__( 'Auto', 'sheyda_wallet' );
				} else if( !empty( $item['created_by'] ) ) {
					$text = get_user_by( 'id', $item['created_by'] )->display_name;
				}
				return $text;
			case 'related_id':
				$text = '-----';
				$type = $item['type'];
				if( in_array( $type, ['topup', 'purchase', 'refund'] ) ) { // related_id = order_id
					$text = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-orders&action=edit&id=' . $item['related_id'] ) ) . '" target="_blank">' . sprintf( __( 'Order #%s', 'sheyda_wallet' ), $item['related_id'] ) . '</a>';
				} else if( in_array( $type, Ledger::withdraw_types() ) || ( !empty( $item['meta']['relation_type'] ) && $item['meta']['relation_type'] == 'withdrawal' ) ) {
					$text = '<a href="' . esc_url( add_query_arg( ['section' => 'withdrawals', 'id' => $item['related_id']] ) ) . '" target="_blank">' . sprintf( __( 'Withdraw #%s', 'sheyda_wallet' ), $item['related_id'] ) . '</a>';
				} else if( in_array( $type, Ledger::transfer_types() ) ) {
					$text = '<a href="' . esc_url( admin_url( 'admin.php?page=sheyda-wallet-transfers&action=view&id=' . $item['related_id'] ) ) . '" target="_blank">' . sprintf( __( 'Transfer #%s', 'sheyda_wallet' ), $item['related_id'] ) . '</a>';
				}
				$text = apply_filters( 'sheyda/wallet/wp_dashboard/ledgers_table/item/related_id_text', $text, $item );
				return $text;
			case 'description':
				return wpautop( $item['description'] );
			case 'created_date':
				$created_at_text = '';
				if( !empty( $item['created_date'] ) ) {
					$created_at_text = '<div>' . date_i18n( 'Y-m-d', $item['created_date']->format( 'U' ) ) . '</div>';
					$created_at_text .= '<small>' . date_i18n( 'H:i:s', $item['created_date']->format( 'U' ) ) . '</small>';
				}
				return $created_at_text;
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}
}
