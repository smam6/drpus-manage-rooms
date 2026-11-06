<?php
namespace DrPlus\PostTypes;

use DrPlus\Utils\Notifications;

class Notification {
	PRIVATE STATIC $POST_TYPE_NAME = 'notification'; // Singular word

	public static function register() {
		$labels = [
			'name'					=> __( 'Notifications', 'drplus' ),
			'singular_name'			=> __( 'Notification', 'drplus' ),
			'menu_name'				=> __( 'Notifications', 'drplus' ),
			'name_admin_bar'		=> __( 'Notifications', 'drplus' ),
			'add_new'				=> __( 'Add New notification', 'drplus' ),
			'add_new_item'			=> __( 'Add New notification', 'drplus' ),
			'new_item'				=> __( 'New notification', 'drplus' ),
			'edit_item'				=> __( 'Edit notification', 'drplus' ),
			'all_items'				=> __( 'All notifications', 'drplus' ),
			'search_items'			=> __( 'Search notifications', 'drplus' ),
			'not_found'				=> __( 'No notification found', 'drplus' ),
			'not_found_in_trash'	=> __( 'No notification found', 'drplus' ),
		];
		$args = [
			'labels'				=> $labels,
			'public'				=> false,
			'show_in_rest'			=> false,
			'show_ui'				=> true,
			'show_in_menu'			=> true,
			'menu_icon'				=> "dashicons-format-status",
			'capability_type'		=> 'post',
			'has_archive'			=> false,
			'hierarchical'			=> false,
			'supports'				=> ['title'],
			'exclude_from_search'	=> true,
		];
		register_post_type( self::$POST_TYPE_NAME, $args );
	}

	private static function _columns() {
		return [
			'users'	=> esc_html__( 'Users', 'drplus' ),
		];
	}

	public static function columns( $columns ) {
		$columns = array_merge( $columns, self::_columns() );

		return $columns;
	}

	public static function columns_value( $column, $post_id ) {
		if( in_array( $column, array_keys( self::_columns() ) ) ) {
			$notification = Notifications::get( $post_id );
			if( $column == 'users' ) {
				if( $notification['recipients'] == 'all_users' ) {
					esc_html_e( "All users", 'drplus' );
				} else if( $notification['recipients'] == 'all_specialists' ) {
					esc_html_e( "All specialists", 'drplus' );
				} else {
					$type = $notification['recipients'] == 'custom_users' ? esc_html__( 'Users', 'drplus' ) : esc_html__( 'Specialists', 'drplus' );
					if( !empty( $notification['users'] ) ) {
						$users =  implode( " , ", wp_list_pluck( $notification['users'], 'display_name' ) );
					}
					echo "<strong>{$type}:</strong> {$users}";
				}
			}
		}
	}
}
Notification::register();
add_filter( "manage_notification_posts_columns", [Notification::class, 'columns'] );
add_action( "manage_notification_posts_custom_column", [Notification::class, 'columns_value'], 10, 2 );