<?php
namespace DrPlus;

class AJAX {
	public $data = [];

	public static function get_instance() {
		static $instance = null;
		if( $instance === null ) {
			$instance = new self;
		}
		return $instance;
	}

	private function __construct() {
		if( !wp_doing_ajax() ) return;

		if( DRPLUS_DEV ) {
			Utils::show_errors();
		}

		// Without _ at the end
		$action_prefix = 'drplus';

		// Fill this with you actions - prefix will automatically added
		/**
		 * Key for action name	=> [
		 * 	file		=> string File address. By default it will use the key name as filename by PascalCase template.
		 * 	class		=> string Name of the class. By default it will use the key name as class name by PascalCase template.
		 * 	guest		=> boolean Run this action for guest users [Default: true]
		 * 	user		=> boolean Run this action for logged in users. [Default: true]
		 * 	need_login	=> boolean Send need login message for guest users. [Default: true]
		 * 	function	=> string Custom name for function. [Default: 'view']
		 * 	nonce		=> string Nonce name of this action. If nonce is empty it will not check the nonce
		 * 	requires	=> array[mixed] List of requires keys in HTTP request(POST | REQUEST(On Dev mode))
		 * ]
		 */
		$default_action = [
			'file'			=> '',
			'class'			=> '',
			'guest'			=> true,
			'user'			=> true,
			'need_login'	=> true,
			'function'		=> '',
			'nonce'			=> '',
			'requires'		=> [],
		];
		$actions = [
			'update_mini_cart'	=> [
				'file'			=> 'MiniCartSetQTY',
				'class'			=> 'MiniCartSetQTY',
				'guest'			=> true,
				'user'			=> true,
				'need_login'	=> false,
				'function'		=> 'update',
				'requires'		=> ['nonce', 'item_key'],
			],
			'toggle_wishlist'	=> [
				'file'			=> 'Wishlist',
				'class'			=> 'Wishlist',
				'guest'			=> false,
				'user'			=> true,
				'need_login'	=> true,
				'function'		=> 'toggle',
				'requires'		=> ['nonce', 'product_id'],
			],
			'get_notices'	=> [
				'file'			=> 'Notices',
				'class'			=> 'Notices',
				'guest'			=> false,
				'user'			=> true,
				'need_login'	=> false,
				'function'		=> 'get',
			],
			'dismiss_notice'	=> [
				'file'			=> 'Notices',
				'class'			=> 'Notices',
				'guest'			=> false,
				'user'			=> true,
				'need_login'	=> false,
				'function'		=> 'dismiss',
				'nonce'			=> 'drplus_dismiss_notice',
				'requires'		=> ['id'],
			],
			'icon_picker'	=> [
				'file'			=> 'IconPicker',
				'class'			=> 'IconPicker',
				'guest'			=> false,
				'user'			=> true,
				'need_login'	=> false,
				'function'		=> 'html',
				'nonce'			=> 'drplus-icon-picker',
			],
			'get_users'	=> [
				'file'			=> 'GetUsers',
				'class'			=> 'GetUsers',
				'guest'			=> false,
				'user'			=> true,
				'need_login'	=> true,
				'function'		=> 'get_users',
				'nonce'			=> 'drplus_get_users_nonce',
				'requires'		=> ['nonce', 'name'],
			],
			'get_user_data'	=> [
				'file'			=> 'GetUserData',
				'class'			=> 'GetUserData',
				'guest'			=> false,
				'user'			=> true,
				'need_login'	=> true,
				'function'		=> 'get_user_data',
				'nonce'			=> 'drplus_get_user_data_nonce',
				'requires'		=> ['nonce', 'user_id'],
			],
			'search'	=> [
				'file'			=> 'Search',
				'class'			=> 'Search',
				'guest'			=> true,
				'user'			=> true,
				'need_login'	=> false,
				'function'		=> 'exec',
				'nonce'			=> 'drplus-search',
				'requires'		=> ['text'],
			],
			'search_onboard'	=> [
				'file'			=> 'Search',
				'class'			=> 'Search',
				'guest'			=> true,
				'user'			=> true,
				'need_login'	=> false,
				'function'		=> 'onboard',
				'nonce'			=> 'drplus-search-onboard',
				'requires'		=> ['text', 'type', 'current_values'],
			],
			'find_hospital'	=> [
				'file'			=> 'FindHospital',
				'class'			=> 'FindHospital',
				'guest'			=> false,
				'user'			=> true,
				'need_login'	=> true,
				'function'		=> 'query',
				'nonce'			=> 'drplus_find_hospital_nonce',
				'requires'		=> ['text'],
			],
			'login' => [
				'file'			=> 'Auth',
				'class'			=> 'Auth',
				'guest'			=> true,
				'user'			=> DRPLUS_DEV,
				'need_login'	=> false,
				'function'		=> 'login',
				'nonce'			=> 'drplus-auth-login',
				'requires'		=> ['username', 'password'],
			],
			'signup' => [
				'file'			=> 'Auth',
				'class'			=> 'Auth',
				'guest'			=> true,
				'user'			=> DRPLUS_DEV,
				'need_login'	=> false,
				'function'		=> 'signup',
				'nonce'			=> 'drplus-auth-signup',
			],
			'send_otp' => [
				'file'			=> 'Auth',
				'class'			=> 'Auth',
				'guest'			=> true,
				'user'			=> DRPLUS_DEV,
				'need_login'	=> false,
				'function'		=> 'send_otp',
				'nonce'			=> 'drplus-auth-mobile',
				'requires'		=> ['mobile'],
			],
			'check_otp' => [
				'file'			=> 'Auth',
				'class'			=> 'Auth',
				'guest'			=> true,
				'user'			=> DRPLUS_DEV,
				'need_login'	=> false,
				'function'		=> 'check_otp',
				'nonce'			=> 'drplus-auth-otp',
				'requires'		=> ['mobile', 'otp'],
			],
			'lost_password' => [
				'file'			=> 'Auth',
				'class'			=> 'Auth',
				'guest'			=> true,
				'user'			=> DRPLUS_DEV,
				'need_login'	=> false,
				'function'		=> 'lost_password',
				'nonce'			=> 'drplus-auth-lost_password',
				'requires'		=> ['entry'],
			],
			'set_notification_read'	=> [
				'file'			=> 'Notifications',
				'class'			=> 'Notifications',
				'guest'			=> false,
				'user'			=> true,
				'need_login'	=> true,
				'function'		=> 'set_read',
				'requires'		=> ['id'],
			],
			'upload' => [
				'file'			=> 'Upload',
				'class'			=> 'Upload',
				'guest'			=> false,
				'user'			=> true,
				'need_login'	=> true,
				'nonce'			=> 'drplus-dropzone_upload_nonce',
				'function'		=> 'upload',
			],
			'get_available_times'	=> [
				'file'			=> 'GetAvailableTimes',
				'class'			=> 'GetAvailableTimes',
				'guest'			=> true,
				'user'			=> true,
				'need_login'	=> false,
				'function'		=> 'get_available_times',
				'nonce'			=> 'booking_available_times',
				'requires'		=> ['specialist', 'office', 'date', 'chunk', 'nonce'],
			],
			'cache_sync'		=> [
				'file'			=> 'Cache',
				'class'			=> 'Cache',
				'guest'			=> true,
				'user'			=> true,
				'need_login'	=> false,
				'function'		=> 'sync',
			],
			'chat_send_message'	=> [
				'file'			=> 'Chat',
				'class'			=> 'ChatAjax',
				'guest'			=> false,
				'user'			=> true,
				'need_login'	=> true,
				'function'		=> 'chat_send_message',
				'nonce'			=> 'chat_message',
				'requires'		=> ['session_id', 'nonce'],
			],
			'chat_get_messages'	=> [
				'file'			=> 'Chat',
				'class'			=> 'ChatAjax',
				'guest'			=> false,
				'user'			=> true,
				'need_login'	=> true,
				'function'		=> 'chat_get_messages',
				'nonce'			=> 'chat_message',
				'requires'		=> ['session_id'],
			],
			'chat_mark_seen'	=> [
				'file'			=> 'Chat',
				'class'			=> 'ChatAjax',
				'guest'			=> false,
				'user'			=> true,
				'need_login'	=> true,
				'function'		=> 'chat_mark_seen',
				'nonce'			=> 'chat_message',
				'requires'		=> ['session_id'],
			],
			'chat_get_sessions'	=> [
				'file'			=> 'Chat',
				'class'			=> 'ChatAjax',
				'guest'			=> false,
				'user'			=> true,
				'need_login'	=> true,
				'function'		=> 'chat_get_sessions',
				'nonce'			=> 'chat_message',
			],
			'chat_upload_file'	=> [
				'file'			=> 'Chat',
				'class'			=> 'ChatAjax',
				'guest'			=> false,
				'user'			=> true,
				'need_login'	=> true,
				'function'		=> 'chat_upload_file',
				'nonce'			=> 'chat_message',
				'requires'		=> ['session_id'],
			],
			'process_buy_plan'	=> [
				'file'			=> 'ProcessBuyPlan',
				'class'			=> 'ProcessBuyPlan',
				'guest'			=> false,
				'user'			=> true,
				'need_login'	=> true,
				'function'		=> 'process_buy_plan',
				'nonce'			=> 'drplus_subscription_plan',
				'requires'		=> ['plan_id'],
			],
			'search_cities' => [
				'file'			=> 'Search',
				'class'			=> 'Search',
				'guest'			=> true,
				'user'			=> true,
				'need_login'	=> false,
				'function'		=> 'cities',
				'nonce'			=> 'drplus-search-cities',
				'requires'		=> ['text'],
			],
			'profile_send_otp' => [
				'file'			=> 'ProfileOTP',
				'class'			=> 'ProfileOTP',
				'guest'			=> false,
				'user'			=> true,
				'need_login'	=> false,
				'function'		=> 'send_otp',
				'nonce'			=> 'drplus-profile-send-otp',
				'requires'		=> ['mobile'],
			],
			'profile_check_otp' => [
				'file'			=> 'ProfileOTP',
				'class'			=> 'ProfileOTP',
				'guest'			=> false,
				'user'			=> true,
				'need_login'	=> false,
				'function'		=> 'check_otp',
				'nonce'			=> 'drplus-profile-check-otp',
				'requires'		=> ['mobile', 'otp'],
			],
			'skyroom_get_room_link' => [
				'file'			=> 'Skyroom',
				'class'			=> 'Skyroom',
				'guest'			=> false,
				'user'			=> true,
				'need_login'	=> true,
				'function'		=> 'get_room_link',
				'requires'		=> ['order_id', 'user_id'],
			],
		];
		$dir = DRPLUS_DIR . "inc/AJAX/";

		$this->set_request_data();
		if( empty( $this->data['action'] ) ) return;

		foreach( $actions as $key => $data ) {
			$action = $default_action;
			if( is_array( $data ) ) {
				$action = Utils::check_default( $data, $default_action );
			}

			// Prepare filename to include
			if( empty( $action['file'] ) ) {
				$action['file'] = $data;
			}
			$action['file'] = Utils::convert_to_pascal_case( $action['file'] );
			include_once( $dir . $action['file'] . ".php" );

			if( empty( $action['class'] ) ) {
				$action['class'] = $data;
			}
			$action['class'] = "\DrPlus\AJAX\\" . Utils::convert_to_pascal_case( $action['class'] );
			
			$actions[$key] = $action;
		}
		$action_name_without_prefix = str_replace( "{$action_prefix}_", '', $this->data['action'] );
		if( !in_array( $action_name_without_prefix, array_keys( $actions ) ) ) return;
		
		$action = $actions[$action_name_without_prefix];

		if( $action['need_login'] ) {
			add_action( "wp_ajax_nopriv_{$this->data['action']}", [$this, 'need_login'] );
		}

		if( !empty( $action['nonce'] ) ) {
			$this->check_nonce( $action['nonce'] );
		}

		if( !empty( $action['requires'] ) ) {
			$this->check_requires( $action['requires'] );
		}

		if( $action['guest'] ) {
			if( !$action['need_login'] ) {
				add_action( "wp_ajax_nopriv_{$this->data['action']}", [$action['class']::get_instance(), $action['function']] );
			}
		}
		if( $action['user'] ) {
			add_action( "wp_ajax_{$this->data['action']}", [$action['class']::get_instance(), $action['function']] );
		}
	}

	/**
	 * Send result to response
	 *
	 * @param string $type Accepts: error | success
	 * @param mixed $data Your data
	 * @return void
	 */
	public function result( $type, $data = '', $status_code = null ) {
		if( $type == 'error' ) {
			wp_send_json_error( $data, $status_code );
		} else {
			wp_send_json_success( $data, $status_code );
		}
		die;
	}

	/**
	 * Array of nonce error
	 *
	 * @return array
	 */
	public function nonce_error() {
		return [
			'code'		=> 'security_error',
			'message'	=> __( "Security error", 'drplus' ),
		];
	}

	/**
	 * Automatically select the HTTP method
	 *
	 * @return array
	 */
	public function set_request_data() {
		$this->data = array_change_key_case( DRPLUS_DEV ? $_REQUEST : $_POST );
		return $this;
	}

	/**
	 * Check nonce
	 *
	 * @param string $action The nonce action
	 * @param boolean $send_error Automatically send error in response or return boolean
	 * @return void|boolean
	 */
	public function check_nonce( $action, $send_error = true ) {
		$result = !empty( $this->data ) && !empty( $this->data['nonce'] ) && wp_verify_nonce( Utils::convert_chars( $this->data['nonce'] ), $action );
		if( $result ) {
			return true;
		} else {
			if( $send_error ) {
				$this->result( 'error', $this->nonce_error(), 403 );
			} else {
				return false;
			}
		}
	}

	/**
	 * Array of requires error
	 *
	 * @param array $requires List of requires
	 * @return array
	 */
	public function requires_error( $requires ) : array {
		$requires = array_map( function( $require ) {
			return str_replace( '_', ' ', $require );
		}, $requires );
		$requires = implode( ", ", $requires );
		return [
			'code'		=> 'invalid_requires',
			'message'	=> sprintf( __( '%s are required', 'drplus' ), $requires ),
		];
	}

	/**
	 * Check data requires
	 *
	 * @param array[string] $requires List of required keys
	 * @param boolean $send_error Automatically send error in response or return boolean
	 * @return boolean
	 */
	public function check_requires( $requires, $send_error = true, $check_empty = true ) : bool {
		if( Utils::check_requires( $this->data, $requires, $check_empty ) ) {
			return true;
		} else {
			if( $send_error ) {
				$this->result( 'error', $this->requires_error( $requires ) );
			}
			return false;
		}
	}

	public function need_login() : void {
		$this->result( 'error', [
			'code'		=> 'forbidden',
			'message'	=> '',
		] );
	}
}
AJAX::get_instance();