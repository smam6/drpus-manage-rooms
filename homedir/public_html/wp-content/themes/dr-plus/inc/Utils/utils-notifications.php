<?php
namespace DrPlus\Utils;

use DrPlus\Metaboxes\Backend\Notification\Settings;
use DrPlus\Model\NotificationsUserRel;
use DrPlus\Utils;

class Notifications extends Utils {
	private static $users = [];
	private static $reads = [];

	public static function default_options() {
		return [
			'recipients'	=> 'all_users',
		];
	}

	public static function get_options( $post_id = null ) {
		$post_id = parent::get_post_id( $post_id );
		$options = parent::get_post_options( self::default_options(), $post_id );
		$options['message'] = get_the_content( null, false, $post_id );

		return $options;
	}

	public static function save_options( array $options, $post_id = 0 ) {
		$post_id = parent::get_post_id( $post_id );
		parent::save_post_options( $options, self::default_options(), $post_id );
		remove_action( 'save_post', [Settings::class, 'save'], 10 );
		wp_update_post( [
			'ID'			=> $post_id,
			'post_content'	=> $options['message'],
		] );
	}
	public static function get( $post = null, bool $get_users = true ) {
		$post = parent::get_post( $post );
		$options = self::get_options( $post->ID );
		$options['users'] = [];
		if( in_array( $options['recipients'], ['custom_users', 'custom_specialists'] ) ) {
			$user_ids = NotificationsUserRel::query()->select( 'user_id' )->where( 'notif_id', $post->ID )->get()->pluck( 'user_id' );
			if( !empty( $user_ids ) ) {
				if( $get_users ) {
					foreach( $user_ids as $user_id ) {
						if( empty( self::$users[$user_id] ) ) {
							self::$users[$user_id] = get_user_by( 'id', $user_id );
						}
						$options['users'][] = self::$users[$user_id];
					}
				} else {
					$options['users'] = $user_ids;
				}
			}
		}
		return $options;
	}

	public static function get_user_reads( $user_id = null ) : array {
		$user_id = parent::get_user_id( $user_id );
		if( !isset( self::$reads[$user_id] ) ) {
			self::$reads[$user_id] = get_user_meta( $user_id, 'drplus-read-notifs', true );
			if( !is_array( self::$reads[$user_id] ) ) self::$reads[$user_id] = [];
		}

		return self::$reads[$user_id];
	}

	public static function get_current_user_notifications( bool $include_reads = false ) : array {
		static $notifications = null;
		if( $notifications === null && is_user_logged_in() ) {
			$user_id = get_current_user_id();

			$is_specialist = UtilsSpecialists::is_user_specialist( $user_id, true );

			$notif_in = [];
			$user_notifs = NotificationsUserRel::query()->select( 'notif_id' )->where( 'user_id', $user_id );
			if( !$is_specialist ) {
				$user_notifs = $user_notifs->whereNot( 'type', 'specialists' );
			}

			$notif_in = $user_notifs->get()->pluck( 'notif_id' );

			global $wpdb;
			$query = "SELECT p.`ID`, p.`post_date`, p.`post_title`, p.`post_content` FROM `{$wpdb->posts}` AS p INNER JOIN `{$wpdb->postmeta}` AS pm ON pm.`post_id`=p.`ID` WHERE p.`post_type`=%s AND p.`post_status`=%s ";
			$prepares = [
				'notification',
				'publish',
			];

			$query .= " AND ((pm.`meta_key`=%s AND pm.`meta_value`=%s)";
			$prepares[] = '_recipients';
			$prepares[] = 'all_users';
			if( $is_specialist ) {
				$query .= " OR ( pm.`meta_key`=%s AND pm.`meta_value`=%s)";
				$prepares[] = '_recipients';
				$prepares[] = 'all_specialists';
			}
			$query .= ")";

			if( $notif_in ) {
				if( count( $notif_in ) === 1 ) {
					$query .= " OR p.`ID`=%d";
					$prepares[] = $notif_in[0];
				} else {
					$query .= " OR p.`ID` IN (" . parent::db_placeholder( $notif_in, '%d' ) . ")";
					$prepares[] = $notif_in;
				}
			}

			$query = $wpdb->prepare( $query, parent::array_flatten( $prepares ) );

			$query .= " GROUP BY p.`ID` ORDER BY p.`post_date` DESC";
		
			$notifications = $wpdb->get_results( $query );
			
			$reads = self::get_user_reads( $user_id );
			$date_format = get_option( 'date_format' );
			$time_format = get_option( 'time_format' );
			foreach( $notifications as $index => $notification ) {
				$notification = get_post( $notification );
				$notification->post_date = date_i18n( "{$date_format} {$time_format}", strtotime( $notification->post_date ) );
				$notification->read = in_array( $notification->ID, $reads );
				$notifications[$index] = $notification;
			}
		}

		$results = $notifications;
		if( !is_array( $results ) ) {
			$results = [];
		}
		if( !$include_reads && !empty( $results ) ) {
			$results = array_filter( $results, fn( $notification ) => $notification->read === false );
		}

		return $results;
	}

	public static function add_user_read( int $notif_id, $user_id = null ) {
		$user_id = parent::get_user_id( $user_id );
		$reads = self::get_user_reads( $user_id );
		if( !in_array( $notif_id, $reads ) ) {
			$reads[] = $notif_id;
			update_user_meta( $user_id, 'drplus-read-notifs', $reads );
			self::$reads[$user_id] = $reads;
		}
	}

	public static function count_user_unread() {
		return is_user_logged_in() ? count( self::get_current_user_notifications() ) : 0;
	}

	public static function recipients_types() {
		return [
			'all_users'				=> __( 'All users', 'drplus' ),
			'all_specialists'		=> __( 'All specialists', 'drplus' ),
			'custom_users'			=> __( 'Custom users', 'drplus' ),
			'custom_specialists'	=> __( 'Custom specialists', 'drplus' ),
		];
	}
}