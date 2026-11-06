<?php
namespace DrPlus\AJAX;

use DrPlus\AJAX;
use DrPlus\Utils;
use DrPlusUtilsChat as Chat;

class ChatAjax extends AJAX {
	public static function get_instance() {
		static $instance = null;
		if( $instance === null ) {
			$instance = new self;
		}
		return $instance;
	}

	public function __construct() {
		return $this;
	}

	
	public function chat_send_message() {
		$this->set_request_data();

		$user_id = get_current_user_id();
		$session_id = Utils::convert_chars( $this->data['session_id'], true, 'intval' );
		$message = wp_kses( $this->data['message'], [
			'br' => []
		] );
		$type = Utils::convert_chars( $this->data['type'] ?? 'text' );
		$file_url = Utils::convert_chars( $this->data['file_url'] ?? null );
		$sender_type = Utils::convert_chars( $this->data['sender_type'] ?? null );

		$this->check_set_session( $session_id );
		if( ( $type == 'text' && empty( $message ) ) || ( $type != 'text' && empty( $file_url ) ) ) {
			$this->result( 'error', [
				'code'		=> empty( $message ) ? 'empty_message' : 'empty_file',
				'message'	=> empty( $message ) ? __( 'Message is empty', 'drplus' ) : __( 'File is empty', 'drplus' ),
			], 403 );
		}
		$this->is_participant( $session_id, $user_id );

		$id = Chat::send_message( $session_id, $user_id, $message, $type, $file_url );
		if( $id ) {
			do_action( 'drplus/chat/message_sent', $session_id, $user_id, $sender_type );
		}
		$this->result( 'success', [
			'code'	=> 'success',
			'id'	=> $id
		] );
	}

	public function chat_get_messages() {
		$this->set_request_data();

		$user_id = get_current_user_id();
		$session_id = Utils::convert_chars( $this->data['session_id'], true, 'intval' );
		$after_id = Utils::convert_chars( $this->data['after_id'] ?? 0, true, 'intval' );
		if( empty( $session_id ) ) {
			$this->result( 'error', [
				'code'		=> 'empty_session_id',
				'message'	=> __( 'Session ID is empty', 'drplus' ),
			], 403 );
		}
		$this->check_set_session( $session_id );
		$this->is_participant( $session_id, $user_id );

		$messages = Chat::get_messages( $session_id, $after_id );
		foreach( $messages as $index => $message ) {
			$messages[$index]['created_at'] = date_i18n( 'H:i', strtotime( $message['created_at'] ) );
			$messages[$index]['message'] = nl2br( $message['message'] );
		}
		$this->result( 'success', [
			'code'		=> 'success',
			'messages'	=> $messages
		] );
	}

	public function chat_mark_seen() {
		$this->set_request_data();

		$user_id = get_current_user_id();
		$session_id = Utils::convert_chars( $this->data['session_id'], true, 'intval' );
		$this->check_set_session( $session_id );
		$this->is_participant( $session_id, $user_id );

		Chat::mark_seen( $session_id, $user_id );
		$this->result( 'success', [
			'code'	=> 'success',
		] );
	}

	public function chat_get_sessions() {
		$this->set_request_data();

		$user_id = get_current_user_id();

		$sessions = Chat::get_sessions( $user_id );
		$this->result( 'success', [
			'code'		=> 'success',
			'sessions'	=> $sessions
		] );
	}

	public function chat_upload_file() {
		$this->set_request_data();

		$user_id = get_current_user_id();
		$session_id = Utils::convert_chars( $this->data['session_id'], true, 'intval' );
		$this->check_set_session( $session_id );

		if( empty( $_FILES['file'] ) || !isset($_FILES['file']['tmp_name']) ) {
			header('Content-Type: application/json');
			echo json_encode([
				'success' => false,
				'data' => [
					'code' => 'no_file',
					'message' => __( 'File is not uploaded', 'drplus' ),
				]
			]);
			exit;
		}

		$file_url = Chat::upload_file( $_FILES['file'], $user_id );
		if( is_wp_error( $file_url ) ) {
			header('Content-Type: application/json');
			echo json_encode([
				'success' => false,
				'data' => [
					'code'		=> array_keys( $file_url->errors )[0],
					'message'	=> $file_url->get_error_message(),
				]
			]);
			exit;
		}
		header('Content-Type: application/json');
		echo json_encode([
			'success' => true,
			'data' => [
				'code' => 'success',
				'file_url' => $file_url
			]
		]);
		exit;
	}

	private function is_participant( $session_id, $user_id ) {
		if( !Chat::is_participant( $session_id, $user_id ) ) {
			wp_send_json_error( ['error' => 'forbidden'], 403 );
			$this->result( 'error', [
				'code'		=> 'forbidden',
				'message'	=> __( 'Access forbidden', 'drplus' ),
			], 403 );
		}
	}

	private function check_set_session( $session_id ) {
		if( empty( $session_id ) ) {
			$this->result( 'error', [
				'code'		=> 'empty_session_id',
				'message'	=> __( 'Session ID is empty', 'drplus' ),
			], 403 );
		}
	}
}