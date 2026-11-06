<?php
namespace Sheyda\Wallet;

class CustomPagination {
	public static function view( $args ) {
		$query_arg_name = !empty( $args['query_arg_name'] ) ? sanitize_text_field( $args['query_arg_name'] ) : 'page';

		$base = remove_query_arg( array_keys( $_GET ) );
		$url_query_args = array_merge( $_GET, array( $query_arg_name => '%#%' ) );
		$link = add_query_arg( $url_query_args, $base );
		?>
		<div class="pagination sheyda_wallet_pagination">
			<?php
			$is_rtl = is_rtl();

			$prev_text = $is_rtl ? "<i class='sheyda-wallet-icon-chevron-right-dot'></i>" : "<i class='sheyda-wallet-icon-chevron-left-dot'></i>";
			$prev_text .= esc_html__( 'Previous page', 'sheyda_wallet' );
			$next_text = esc_html__( 'Next page', 'sheyda_wallet' );
			$next_text .= $is_rtl ? "<i class='sheyda-wallet-icon-chevron-left-dot'></i>" : "<i class='sheyda-wallet-icon-chevron-right-dot'></i>";

			echo paginate_links( array(
				'base'					=> $link,
				'total'					=> $args['max_num_pages'] ?? $args['query']->max_num_pages,
				'current'				=> max( 1, $args['paged'] ),
				'format'				=> '?paged=%#%',
				'prev_next'				=> true,
				'type'					=> 'plain',
				'prev_text'				=> $prev_text,
				'next_text'				=> $next_text,
				'mid_size'				=> 3,
				/* translators: Hidden accessibility text. */
				'before_page_number'	=> '<span class="meta-nav screen-reader-text">' . __( 'Page', 'sheyda_wallet' ) . ' </span>',
			) );
			?>
		</div>
		<?php
	}
}