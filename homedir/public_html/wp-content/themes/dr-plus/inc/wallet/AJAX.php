<?php
namespace Sheyda\Wallet;

use SheydaWalletUtils as WalletUtils;

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

		if( SHEYDA_WALLET_DEV ) {
			WalletUtils::show_errors();
		}

		// Without _ at the end
		$action_prefix = 'sheyda_wallet';

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
			'process_topup'	=> [
				'file'			=> 'ProcessTopUp',
				'class'			=> 'ProcessTopUp',
				'guest'			=> false,
				'user'			=> true,
				'need_login'	=> true,
				'function'		=> 'process_topup',
				'nonce'			=> 'sheyda_wallet_process_topup',
				'requires'		=> ['amount'],
			],
		];
		$dir = SHEYDA_WALLET_DIR . "Ajax/";

		$this->set_request_data();
		if( empty( $this->data['action'] ) ) return;

		foreach( $actions as $key => $data ) {
			$action = $default_action;
			if( is_array( $data ) ) {
				$action = WalletUtils::check_default( $data, $default_action );
			}

			// Prepare filename to include
			if( empty( $action['file'] ) ) {
				$action['file'] = $data;
			}
			$action['file'] = WalletUtils::convert_to_pascal_case( $action['file'] );
			include_once( $dir . $action['file'] . ".php" );

			if( empty( $action['class'] ) ) {
				$action['class'] = $data;
			}
			$action['class'] = "\Sheyda\Wallet\AJAX\\" . WalletUtils::convert_to_pascal_case( $action['class'] );
			
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
			'message'	=> __( "Security error", 'sheyda_wallet' ),
		];
	}

	/**
	 * Automatically select the HTTP method
	 *
	 * @return array
	 */
	public function set_request_data() {
		$this->data = array_change_key_case( SHEYDA_WALLET_DEV ? $_REQUEST : $_POST );
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
		$result = !empty( $this->data ) && !empty( $this->data['nonce'] ) && wp_verify_nonce( WalletUtils::convert_chars( $this->data['nonce'] ), $action );
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
			'message'	=> sprintf( __( '%s are required', 'sheyda_wallet' ), $requires ),
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
		if( WalletUtils::check_requires( $this->data, $requires, $check_empty ) ) {
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