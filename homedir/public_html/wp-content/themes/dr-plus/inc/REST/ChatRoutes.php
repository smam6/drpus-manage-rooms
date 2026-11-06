<?php
namespace DrPlus\REST;

use WP_REST_Server;

/**
 * Register REST API routes for Chat
 * 
 * All routes are registered under: /wp-json/drplus_chat/v1/
 */
class ChatRoutes {

	/**
	 * REST namespace.
	 *
	 * @var string
	 */
	const NAMESPACE = 'drplus_chat/v1';

	/**
	 * Register all chat REST endpoints
	 */
	public static function register_routes() {
		$controller = new ChatController();
		// GET /wp-json/drplus_chat/v1/conversations
		// List all conversations for current user
		register_rest_route( 
			self::NAMESPACE,
			'/conversations',
			[
				'methods'				=> WP_REST_Server::READABLE,
				'callback'				=> [ $controller, 'get_conversations' ],
				'permission_callback'	=> [ 'DrPlus\REST\Permissions', 'is_authenticated' ],
				'args'					=> [
					'type' => [
						'description'		=> __( 'Type of conversations to return', 'drplus' ),
						'type'				=> 'string',
						'enum'				=> [ 'all', 'specialist', 'customer' ],
						'default'			=> 'all',
						'sanitize_callback'	=> 'sanitize_text_field',
						'validate_callback' => [ __CLASS__, 'validate_positive_int' ],
					],
				],
			]
		);

		// GET /wp-json/drplus_chat/v1/conversations/{id}
		// Get specific conversation details
		register_rest_route(
			self::NAMESPACE,
			'/conversations/(?P<id>\d+)$',
			[
				'methods'				=> WP_REST_Server::READABLE,
				'callback'				=> [ $controller, 'get_conversation' ],
				'permission_callback'	=> [ 'DrPlus\REST\Permissions', 'is_participant' ],
				'args' 					=> [
					'id' => [
						'description'		=> __( 'Conversation ID', 'drplus' ),
						'type'				=> 'integer',
						'required'			=> true,
						'sanitize_callback'	=> 'absint',
						'validate_callback' => [ __CLASS__, 'validate_positive_int' ],
					],
				],
			]
		);

		// GET /wp-json/drplus_chat/v1/conversations/{id}/messages
		// Get messages from a conversation
		register_rest_route(
			self::NAMESPACE,
			'/conversations/(?P<id>\d+)/messages',
			[
				'methods'					=> WP_REST_Server::READABLE,
				'callback'					=> [ $controller, 'get_messages' ],
				'permission_callback'		=> [ 'DrPlus\REST\Permissions', 'is_participant' ],
				'args'						=> [
					'id'		=> [
						'description'		=> __( 'Conversation ID', 'drplus' ),
						'type'				=> 'integer',
						'required'			=> true,
						'sanitize_callback'	=> 'absint',
						'validate_callback' => [ __CLASS__, 'validate_positive_int' ],
					],
					'after_id'	=> [
						'description'		=> __( 'Get messages after this message ID (for delta updates)', 'drplus' ),
						'type'				=> 'integer',
						'default'			=> 0,
						'sanitize_callback'	=> 'absint',
					],
					'page'		=> [
						'description'		=> __( 'Page number for pagination', 'drplus' ),
						'type'				=> 'integer',
						'default'			=> 1,
						'sanitize_callback'	=> 'absint',
						'validate_callback' => [ __CLASS__, 'validate_positive_int' ],
					],
					'per_page'	=> [
						'description'		=> __( 'Messages per page (max 100)', 'drplus' ),
						'type'				=> 'integer',
						'default'			=> 50,
						'sanitize_callback'	=> 'absint',
						'validate_callback'	=> [ __CLASS__, 'validate_per_page' ],
					],
				],
			]
		);

		// POST /wp-json/drplus_chat/v1/conversations/{id}/messages
		// Send a new message
		register_rest_route(
			self::NAMESPACE,
			'/conversations/(?P<id>\d+)/messages',
			[
				'methods'				=> WP_REST_Server::CREATABLE,
				'callback'				=> [ $controller, 'send_message' ],
				'permission_callback'	=> [ 'DrPlus\REST\Permissions', 'is_participant' ],
				'args'					=> [
					'id'		=> [
						'description'		=> __( 'Conversation ID', 'drplus' ),
						'type'				=> 'integer',
						'required'			=> true,
						'sanitize_callback'	=> 'absint',
						'validate_callback' => [ __CLASS__, 'validate_positive_int' ],
					],
					'message'	=> [
						'description'		=> __( 'Message content', 'drplus' ),
						'type'				=> 'string',
						'required'			=> true,
						'sanitize_callback'	=> [ 'DrPlus\REST\ChatRoutes', 'sanitize_message' ],
					],
					'type'		=> [
						'description'		=> __( 'Message type', 'drplus' ),
						'type'				=> 'string',
						'enum'				=> [ 'text', 'file', 'voice' ],
						'default'			=> 'text',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => [ __CLASS__, 'validate_message_type' ],
					],
					'file_url'	=> [
						'description'		=> __( 'URL of file (required for file/voice messages)', 'drplus' ),
						'type'				=> 'string',
						'sanitize_callback' => 'esc_url_raw',
					],
				],
			]
		);

		// POST /wp-json/drplus_chat/v1/conversations/{id}/messages/mark-seen
		// Mark messages as seen
		register_rest_route(
			self::NAMESPACE,
			'/conversations/(?P<id>\d+)/messages/mark-seen',
			[
				'methods'				=> WP_REST_Server::CREATABLE,
				'callback'				=> [ $controller, 'mark_seen' ],
				'permission_callback'	=> [ 'DrPlus\REST\Permissions', 'is_participant' ],
				'args'					=> [
					'id' => [
						'description'		=> __( 'Conversation ID', 'drplus' ),
						'type'				=> 'integer',
						'required'			=> true,
						'sanitize_callback'	=> 'absint',
						'validate_callback' => [ __CLASS__, 'validate_positive_int' ],
					],
				],
			]
		);

		// POST /wp-json/drplus_chat/v1/conversations/{id}/upload
		// Upload file to conversation
		register_rest_route(
			self::NAMESPACE,
			'/conversations/(?P<id>\d+)/upload',
			[
				'methods'				=> WP_REST_Server::CREATABLE,
				'callback'				=> [ $controller, 'upload_file' ],
				'permission_callback'	=> [ 'DrPlus\REST\Permissions', 'is_participant' ],
				'args'					=> [
					'id' => [
						'description'		=> __( 'Conversation ID', 'drplus' ),
						'type'				=> 'integer',
						'required'			=> true,
						'sanitize_callback'	=> 'absint',
						'validate_callback' => [ __CLASS__, 'validate_positive_int' ],
					],
				],
			]
		);
	}

	/**
	 * Sanitize message body.
	 *
	 * @param string $message Raw message.
	 * @return string
	 */
	public static function sanitize_message( $message ) {
		$message = wp_unslash( $message );

		/*
		 * Chat messages should not allow arbitrary HTML.
		 * If your current UX supports links, convert links during rendering instead.
		 */
		$message = wp_strip_all_tags( $message );
		$message = trim( $message );

		return $message;
	}

	/**
	 * Validate positive integer.
	 *
	 * @param mixed $value Value.
	 * @return bool
	 */
	public static function validate_positive_int( $value ) {
		return absint( $value ) > 0;
	}

	/**
	 * Validate per-page value.
	 *
	 * @param mixed $value Value.
	 * @return bool
	 */
	public static function validate_per_page( $value ) {
		$value = absint( $value );

		return $value >= 1 && $value <= 100;
	}

	/**
	 * Validate message type.
	 *
	 * @param string $type Message type.
	 * @return bool
	 */
	public static function validate_message_type( $type ) {
		$allowed = array(
			'text',
			'image',
			'file',
			'audio',
			'voice',
		);

		return in_array( sanitize_key( $type ), $allowed, true );
	}
}

// Register routes on init
add_action( 'rest_api_init', [ ChatRoutes::class, 'register_routes' ] );