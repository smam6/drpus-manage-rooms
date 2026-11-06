<?php
namespace DrPlus\REST;

use DrPlusUtilsChat as Chat;
use DrPlus\Model\ChatSession;
use DrPlus\Model\ChatMessage;

/**
 * REST API Controller for Chat endpoints
 * 
 * Handles all chat-related REST API operations with proper security checks
 */
class ChatController {
	
	/**
	 * Send a new message to a conversation
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function send_message( $request ) {
		// SECURITY: Rate limit message sending (10 per minute)
		$rate_check = RateLimiter::check( 
			Permissions::get_current_user_id(), 
			'chat_send_message',
			10
		);
		if ( is_wp_error( $rate_check ) ) {
			return $rate_check;
		}
		
		$user_id = Permissions::get_current_user_id();
		$session_id = (int) $request->get_url_params()['id'];
		
		// SECURITY: Verify user is participant
		if ( ! Chat::is_participant( $session_id, $user_id ) ) {
			return new \WP_Error(
				'forbidden',
				__( 'You do not have permission to send messages in this conversation.', 'drplus' ),
				[ 'status' => 403 ]
			);
		}
		
		// Get and sanitize input
		$message = wp_kses( $request->get_param( 'message' ), [
			'br' => []
		] );
		$type = sanitize_text_field( $request->get_param( 'type' ) ?? 'text' );
		$file_url = sanitize_url( $request->get_param( 'file_url' ) ?? '' );
		
		// Validate message content
		if ( 'text' === $type && empty( $message ) ) {
			return new \WP_Error(
				'empty_message',
				__( 'Message cannot be empty.', 'drplus' ),
				[ 'status' => 400 ]
			);
		}
		
		if ( 'text' !== $type && empty( $file_url ) ) {
			return new \WP_Error(
				'empty_file_url',
				__( 'File URL is required for file messages.', 'drplus' ),
				[ 'status' => 400 ]
			);
		}
		
		// Validate message length
		if ( strlen( $message ) > 10000 ) {
			return new \WP_Error(
				'message_too_long',
				__( 'Message is too long. Maximum 10000 characters.', 'drplus' ),
				[ 'status' => 400 ]
			);
		}
		
		// Send the message
		$message_id = Chat::send_message( $session_id, $user_id, $message, $type, $file_url );
		
		if ( ! $message_id ) {
			return new \WP_Error(
				'message_send_failed',
				__( 'Failed to send message.', 'drplus' ),
				[ 'status' => 500 ]
			);
		}
		
		// Fire action hook
		do_action( 'drplus/chat/message_sent', $session_id, $user_id, 'web' );
		
		// Get the sent message to return
		$sent_message = ChatMessage::find( $message_id );
		
		$response_data = [
			'id' => (int) $sent_message->id,
			'session_id' => (int) $sent_message->session_id,
			'sender_id' => (int) $sent_message->sender_id,
			'message' => wp_kses_post( $sent_message->message ),
			'type' => sanitize_text_field( $sent_message->type ),
			'file_url' => ! empty( $sent_message->file_url ) ? sanitize_url( $sent_message->file_url ) : null,
			'is_seen' => (bool) $sent_message->is_seen,
			'created_at' => $sent_message->created_at,
		];
		
		return rest_ensure_response( [
			'success' => true,
			'data' => $response_data
		] );
	}
	
	/**
	 * Get messages from a conversation
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_messages( $request ) {
		// SECURITY: Rate limit polling (20 per minute)
		$rate_check = RateLimiter::check( 
			Permissions::get_current_user_id(), 
			'chat_get_messages',
			20
		);
		if ( is_wp_error( $rate_check ) ) {
			return $rate_check;
		}
		
		$user_id = Permissions::get_current_user_id();
		$session_id = (int) $request->get_url_params()['id'];
		
		// SECURITY: Verify user is participant
		if ( ! Chat::is_participant( $session_id, $user_id ) ) {
			return new \WP_Error(
				'forbidden',
				__( 'You do not have permission to access messages in this conversation.', 'drplus' ),
				[ 'status' => 403 ]
			);
		}
		
		// Get pagination and filter parameters
		$after_id = (int) $request->get_param( 'after_id' ) ?? 0;
		$per_page = min( (int) $request->get_param( 'per_page' ) ?? 50, 100 ); // Max 100
		$page = max( 1, (int) $request->get_param( 'page' ) ?? 1 );
		
		// Get messages after specific ID (for delta updates)
		if ( $after_id > 0 ) {
			$messages = Chat::get_messages( $session_id, $after_id );
		} else {
			// Traditional pagination using direct SQL to avoid ORM issues
			global $wpdb;
			$offset = ( $page - 1 ) * $per_page;
			$query = $wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}drplus_chat_messages WHERE session_id = %d ORDER BY created_at ASC LIMIT %d OFFSET %d",
				$session_id,
				$per_page + 1, // Get one extra to know if there are more
				$offset
			);
			$messages = $wpdb->get_results( $query, ARRAY_A );
		}
		
		// Format messages for response
		$formatted_messages = [];
		foreach ( $messages as $msg ) {
			$formatted_messages[] = [
				'id' => (int) $msg['id'],
				'session_id' => (int) $msg['session_id'],
				'sender_id' => (int) $msg['sender_id'],
				'message' => wp_kses_post( $msg['message'] ),
				'type' => sanitize_text_field( $msg['type'] ),
				'file_url' => ! empty( $msg['file_url'] ) ? sanitize_url( $msg['file_url'] ) : null,
				'is_seen' => (bool) $msg['is_seen'],
				'created_at' => $msg['created_at'],
			];
		}
		
		return rest_ensure_response( [
			'success' => true,
			'data' => [
				'messages' => $formatted_messages,
				'page' => $page,
				'per_page' => $per_page,
				'has_more' => count( $messages ) > $per_page,
			]
		] );
	}
	
	/**
	 * Mark messages as seen
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function mark_seen( $request ) {
		$user_id = Permissions::get_current_user_id();
		$session_id = (int) $request->get_url_params()['id'];
		
		// SECURITY: Verify user is participant
		if ( ! Chat::is_participant( $session_id, $user_id ) ) {
			return new \WP_Error(
				'forbidden',
				__( 'You do not have permission to update messages in this conversation.', 'drplus' ),
				[ 'status' => 403 ]
			);
		}
		
		// Mark all messages from other users as seen
		Chat::mark_seen( $session_id, $user_id );
		
		return rest_ensure_response( [
			'success' => true,
			'data' => [ 'marked' => true ]
		] );
	}
	
	/**
	 * Get all conversations for current user
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_conversations( $request ) {
		$user_id = Permissions::get_current_user_id();
		
		// Get type parameter (all, specialist, customer)
		$type = sanitize_text_field( $request->get_param( 'type' ) ?? 'all' );
		if ( ! in_array( $type, [ 'all', 'specialist', 'customer' ], true ) ) {
			$type = 'all';
		}
		
		// Get conversations using existing function
		$sessions = Chat::get_sessions( $user_id, $type );
		
		// Format response
		$formatted_sessions = [];
		foreach ( $sessions as $session ) {
			$formatted_session = [
				'id' => (int) $session['id'],
				'user_1_id' => (int) $session['user_1_id'],
				'user_2_id' => (int) $session['user_2_id'],
				'context_id' => (int) $session['context_id'],
				'subject' => sanitize_text_field( $session['subject'] ?? '' ),
				'is_closed' => (bool) $session['is_closed'],
				'is_seen' => (bool) $session['is_seen'],
				'created_at' => $session['created_at'],
				'updated_at' => $session['updated_at'],
			];
			
			// Add last message if available
			if ( ! empty( $session['last_message'] ) ) {
				$formatted_session['last_message'] = [
					'id' => (int) $session['last_message']['id'],
					'message' => wp_kses_post( $session['last_message']['message'] ),
					'type' => sanitize_text_field( $session['last_message']['type'] ),
					'sender_id' => (int) $session['last_message']['sender_id'],
					'created_at' => $session['last_message']['created_at'],
				];
			}
			
			$formatted_sessions[] = $formatted_session;
		}
		
		return rest_ensure_response( [
			'success' => true,
			'data' => [
				'conversations' => $formatted_sessions,
				'total' => count( $formatted_sessions ),
			]
		] );
	}
	
	/**
	 * Get single conversation details
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_conversation( $request ) {
		$user_id = Permissions::get_current_user_id();
		$session_id = (int) $request->get_url_params()['id'];
		
		// First check if conversation exists
		$session = ChatSession::find( $session_id );
		
		if ( ! $session ) {
			return new \WP_Error(
				'not_found',
				__( 'Conversation not found.', 'drplus' ),
				[ 'status' => 404 ]
			);
		}
		
		// SECURITY: Verify user is participant
		if ( ! Chat::is_participant( $session_id, $user_id ) ) {
			return new \WP_Error(
				'forbidden',
				__( 'You do not have permission to access this conversation.', 'drplus' ),
				[ 'status' => 403 ]
			);
		}
		
		$response_data = [
			'id'			=> (int) $session->id,
			'user_1_id'		=> (int) $session->user_1_id,
			'user_2_id'		=> (int) $session->user_2_id,
			'context_id'	=> (int) $session->context_id,
			'subject'		=> sanitize_text_field( $session->subject ?? '' ),
			'is_closed'		=> (bool) $session->is_closed,
			'open_at'		=> $session->open_at,
			'closed_at'		=> $session->closed_at,
			'created_at'	=> $session->created_at,
			'updated_at'	=> $session->updated_at,
		];
		
		return rest_ensure_response( [
			'success' => true,
			'data' => $response_data
		] );
	}
	
	/**
	 * Upload file to conversation
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function upload_file( $request ) {
		// SECURITY: Rate limit file uploads (5 per minute)
		$rate_check = RateLimiter::check( 
			Permissions::get_current_user_id(), 
			'chat_upload_file',
			5
		);
		if ( is_wp_error( $rate_check ) ) {
			return $rate_check;
		}
		
		$user_id = Permissions::get_current_user_id();
		$session_id = (int) $request->get_url_params()['id'];
		
		// SECURITY: Verify user is participant BEFORE allowing upload
		if ( ! Chat::is_participant( $session_id, $user_id ) ) {
			return new \WP_Error(
				'forbidden',
				__( 'You do not have permission to upload files to this conversation.', 'drplus' ),
				[ 'status' => 403 ]
			);
		}
		
		// Get file from request
		$files = $request->get_file_params();
		if ( empty( $files['file'] ) ) {
			return new \WP_Error(
				'no_file',
				__( 'No file provided.', 'drplus' ),
				[ 'status' => 400 ]
			);
		}
		
		// Upload the file using existing utility
		$file_url = Chat::upload_file( $files['file'], $user_id );
		
		if ( is_wp_error( $file_url ) ) {
			return $file_url;
		}
		
		return rest_ensure_response( [
			'success' => true,
			'data' => [
				'file_url' => sanitize_url( $file_url ),
				'file_name' => sanitize_file_name( $files['file']['name'] ),
			]
		] );
	}
}
