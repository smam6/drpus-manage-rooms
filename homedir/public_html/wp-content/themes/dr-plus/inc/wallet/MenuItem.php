<?php
namespace Sheyda\Wallet;

use MJ\Whitebox\Utils\Formatters;
use SheydaWalletUtils as WalletUtils;

class MenuItem {
	private const WALLET_PLACEHOLDER = '{wallet_balance}';

	public static function register_wallet_menu_item() {
		add_meta_box(
			'sheyda-wallet-menu-item',
			esc_html__( 'Wallet balance', 'sheyda_wallet' ),
			[__CLASS__, 'wallet_menu_item_metabox'],
			'nav-menus',
			'side',
			'default'
		);
	}

	public static function wallet_menu_item_metabox() {
		global $_nav_menu_placeholder, $nav_menu_selected_id;

		$_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1;
		$placeholder_id = $_nav_menu_placeholder;
		$default_label = esc_html__( 'Wallet balance: {wallet_balance}', 'sheyda_wallet' );
		?>
		<div id="sheyda-wallet-menu-item" class="sheyda-wallet-menu-item">
			<p class="howto"><?php esc_html_e( 'Add the wallet balance item to your menu.', 'sheyda_wallet' ) ?></p>
			<p class="howto">
				<label for="sheyda-wallet-menu-item-title"><?php esc_html_e( 'Menu text', 'sheyda_wallet' ) ?></label>
				<input type="text" id="sheyda-wallet-menu-item-title" class="widefat" value="<?php echo esc_attr( $default_label ) ?>">
				<span class="description"><?php printf( esc_html__( 'Use %s anywhere in the text to show the current wallet balance.', 'sheyda_wallet' ), '<code>' . esc_html( self::WALLET_PLACEHOLDER ) . '</code>' ) ?></span>
			</p>
			<div id="tabs-panel-sheyda-wallet" class="tabs-panel tabs-panel-active">
				<ul id="sheyda-wallet-checklist" class="categorychecklist form-no-clear">
					<li>
						<label class="menu-item-title">
							<input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php echo $placeholder_id; ?>][menu-item-object-id]" value="<?php echo esc_attr( $placeholder_id ); ?>" /> <?php esc_html_e( 'Wallet balance', 'sheyda_wallet' ); ?>
						</label>
						<input type="hidden" class="menu-item-type" name="menu-item[<?php echo $placeholder_id; ?>][menu-item-type]" value="custom" />
						<input type="hidden" class="menu-item-title menu-item-title-input" name="menu-item[<?php echo $placeholder_id; ?>][menu-item-title]" value="<?php echo esc_attr( $default_label ); ?>" />
						<input type="hidden" class="menu-item-url" name="menu-item[<?php echo $placeholder_id; ?>][menu-item-url]" value="<?php echo esc_url( wc_get_account_endpoint_url( 'sheyda-wallet' ) ) ?>" />
						<input type="hidden" class="menu-item-classes" name="menu-item[<?php echo $placeholder_id; ?>][menu-item-classes]" value="sheyda-wallet-menu-item" />
					</li>
				</ul>
			</div>
			<p class="button-controls wp-clearfix">
				<span class="add-to-menu">
					<input type="submit" <?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-sheyda-wallet-menu-item" id="submit-sheyda-wallet-menu-item" />
					<span class="spinner"></span>
				</span>
			</p>
		</div>
		<script>
		( function( $ ) {
			const $wrap = $( '#sheyda-wallet-menu-item' );
			const $title = $( '#sheyda-wallet-menu-item-title' );
			const $hiddenTitle = $wrap.find( '.menu-item-title-input' );
			const syncTitle = () => $hiddenTitle.val( $title.val() );
			$title.on( 'input', syncTitle );
			$wrap.find( '.submit-add-to-menu' ).on( 'click', syncTitle );
		} )( jQuery );
		</script>
		<?php
	}

	public static function modify_title( $title, $item ) {
		$title = self::replace_wallet_placeholder( $title );
		return $title;
	}

	private static function replace_wallet_placeholder( string $title ) : string {
		if( strpos( $title, self::WALLET_PLACEHOLDER ) === false ) return $title;

		$currency = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '';
		$balance_text = Formatters::price( WalletUtils::get_user_balance_amount(), false, $currency ? " {$currency}" : '' );
		$balance_text = '<span class="sheyda-wallet-menu-balance-text">' . $balance_text . '</span>';

		return str_replace( self::WALLET_PLACEHOLDER, $balance_text, $title );
	}
}
add_filter( 'nav_menu_item_title', [MenuItem::class, 'modify_title'], 10, 2 );
add_action( 'admin_head-nav-menus.php', [MenuItem::class, 'register_wallet_menu_item'] );
