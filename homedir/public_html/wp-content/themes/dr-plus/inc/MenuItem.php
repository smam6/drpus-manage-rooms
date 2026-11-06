<?php
namespace DrPlus;

use DrPlus\Utils\AdminUI;
use DrPlus\Utils\UI;

class MenuItem {
	private static $logged_in = false;
	private static $display_caches = [];
	private static $removed_items = [];

	private static function _get_display( $item_id ) {
		if( !isset( self::$display_caches[$item_id] ) ) {
			self::$display_caches[$item_id] = get_post_meta( $item_id, '_drplus_display', true ) ?: 'all';
		}
		return self::$display_caches[$item_id];
	}

	public static function enqueue( $hook ) {
		if( $hook != 'nav-menus.php' ) return;
		AdminScripts::modal();
		AdminScripts::icon_picker();
	}

	public static function fields( $item_id, $item, $depth ) {
		$icon = get_post_meta( $item_id, '_drplus_icon', true );
		$display = self::_get_display( $item_id );
		if( $depth > 0 ) {
		}
		$subtitle = $depth == 0 ? "" : get_post_meta( $item_id, '_drplus_subtitle', true );

		?>
		<p class="field-display_meta description-wide">
			<label for="edit-menu-item-attr-display-<?php echo $item_id; ?>">
				<?php esc_html_e( 'Display condition', 'drplus' ); ?><br/>
				<select name="menu_item[<?php echo $item_id ?>][display]" id="edit-menu-item-attr-display-<?php echo $item_id; ?>">
					<option value="all" <?php selected( $display, 'all' ) ?>><?php esc_html_e( 'All users', 'drplus' ) ?></option>
					<option value="guests" <?php selected( $display, 'guests' ) ?>><?php esc_html_e( 'Guests only', 'drplus' ) ?></option>
					<option value="users" <?php selected( $display, 'users' ) ?>><?php esc_html_e( 'Logged in users only', 'drplus' ) ?></option>
				</select>
				<p class="description"><?php esc_html_e( 'Show this item for specific users', 'drplus' ) ?></p>
			</label>
		</p>
		<?php if( $depth > 0 ) { ?>
			<p class="field-subtitle description description-wide">
				<label for="edit-menu-item-subtitle-<?php echo $item_id; ?>">
					<?php esc_html_e( 'Subtitle (optional)', 'drplus' ); ?><br/>
					<input type="text" id="edit-menu-item-subtitle-<?php echo $item_id; ?>" class="widefat edit-menu-item-subtitle" name="menu_item[<?php echo $item_id; ?>][subtitle]" value="<?php echo esc_attr( $subtitle ); ?>" />
				</label>
			</p>
		<?php } ?>
		<p class="field-icon description description-thin">
			<label for="edit-menu-item-icon-<?php echo $item_id?>">
				<span><?php esc_html_e( 'Icon (optional)', 'drplus' ) ?></span>
			</label>
		</p>
		<p class="field-icon description description-thin">
			<label for="edit-menu-item-icon-<?php echo $item_id?>">
				<?php AdminUI::icon_picker( [
					'name'		=> "menu_item[{$item_id}][icon]",
					'id'		=> "edit-menu-item-icon{$item_id}",
					'icon'		=> $icon,
					'modal_id'	=> "drplus-icon-picker-modal"
				] ); ?>
			</label>
		</p>
		<?php
	}

	public static function icon_picker_modal() {
		$screen = get_current_screen();
		if( $screen->base != 'nav-menus' ) return;
		AdminUI::modal( [
			'id'					=> "drplus-icon-picker-modal",
			'title'					=> esc_html__( "Select your icon", 'drplus' ),
			'classes'				=> ['icon-picker-modal'],
			'submit_btn_text'		=> esc_html__( "Select icon", 'drplus' ),
		] );
	}

	public static function save( $menu_id, $menu_item_db_id, $args = [] ) {
		if( empty( $_POST ) && empty( $args ) ) return;
		$menu_item = $_POST['menu_item'][$menu_item_db_id] ?? [];

		$display = 'all';
		if( !empty( $menu_item ) && !empty( $menu_item['display'] ) ) {
			$display = Utils::convert_chars( $menu_item['display'] );
			$display = Utils::ensure_values_in_array( $display, ['all', 'guests', 'users'], 'all' );
			update_post_meta( $menu_item_db_id, '_drplus_display', $display );
		} else {
			delete_post_meta( $menu_item_db_id, '_drplus_display' );
		}

		$subtitle = '';
		if( !empty( $menu_item ) && !empty( $menu_item['subtitle'] ) ) {
			$subtitle = Utils::convert_chars( $menu_item['subtitle'] );
			update_post_meta( $menu_item_db_id, '_drplus_subtitle', $subtitle );
		} else {
			delete_post_meta( $menu_item_db_id, '_drplus_subtitle' );
		}

		$icon = '';
		if( !empty( $menu_item ) && !empty( $menu_item['icon'] ) ) {
			$icon = Utils::convert_chars( $menu_item['icon'] );
			update_post_meta( $menu_item_db_id, '_drplus_icon', $icon );
		} else {
			delete_post_meta( $menu_item_db_id, '_drplus_icon' );
		}
	}

	public static function modify_title( $title, $item, $args ) {
		if( is_a( $item, 'WP_Post' ) && isset( $item->ID ) ) {
			$icon = "";
			$subtitle = "";
			$menu_style = 'style-1';
			if( !empty( $args->menu_style ) ) {
				$menu_style = $args->menu_style;
				if( $menu_style == 'style-2' && in_array( 'menu-item-has-children', $item->classes ) ) {
					$title .= '<span class="drplus-icon-bottom menu-item-arrow"></span>';
				}
			}
			if( $args->theme_location == 'account-menu' ) $args->show_icons = false;
			if( !isset( $args->show_icons ) || !empty( $args->show_icons ) ) {
				$icon = UI::get_menu_icon( $item->ID, 'html', $menu_style );
			}
			if( !isset( $args->show_subtitles ) || !empty( $args->show_subtitles ) ) {
				$subtitle = UI::get_menu_subtitle( $item->ID );
			}
			$title = $icon . $subtitle . '<span class="menu-item-title">' . $title . '</span>';
		}
		return $title;
	}

	public static function set_display_items( $items ) {
		self::$logged_in = is_user_logged_in();
		foreach( $items as $key => $item ) {
			if( in_array( $item->menu_item_parent, self::$removed_items ) ) {
				self::$removed_items[] = $item->ID;
				unset( $items[$key] );
				continue;
			}
			
			$display = self::_get_display( $item->ID );
			if( $display == 'users' ) {
				if( !self::$logged_in ) {
					self::$removed_items[] = $item->ID;
					unset( $items[$key] );
					continue;
				}
			} else if( $display == 'guests' ) {
				if( self::$logged_in ) {
					self::$removed_items[] = $item->ID;
					unset( $items[$key] );
					continue;
				}
			}
		}

		return $items;
	}
}
add_action( 'admin_enqueue_scripts', [MenuItem::class, 'enqueue'] );
add_action( 'wp_nav_menu_item_custom_fields', [MenuItem::class, 'fields'], 10, 3 );
add_action( 'wp_update_nav_menu_item', [MenuItem::class, 'save'], 10, 3 );
add_filter( 'nav_menu_item_title', [MenuItem::class, 'modify_title'], 10, 4 );
add_action( 'admin_print_footer_scripts', [MenuItem::class, 'icon_picker_modal'] );
add_filter( 'wp_nav_menu_objects', [MenuItem::class, 'set_display_items'], 1 );