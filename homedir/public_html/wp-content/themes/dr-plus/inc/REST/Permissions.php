<?php
namespace DrPlus\REST;

use DrPlusUtilsChat as Chat;

/**
 * Permission callbacks for REST API endpoints
 * 
 * Handles authentication and authorization checks for all chat endpoints
 */
class Permissions {
	
	/**
	 * Check if user is authenticated
	 *
	 * @param \WP_REST_Request $request REST request object
	 * @return bool|\WP_Error
	 */
	public static function is_authenticated( $request ) {
		$user_id = get_current_user_id();
		
		if ( ! $user_id ) {
			return new \WP_Error(
				'rest_not_logged_in',
				__( 'You must be logged in to access this endpoint.', 'drplus' ),
				[ 'status' => 401 ]
			);
		}
		
		return true;
	}
	
	/**
	 * Check if user is a participant in the chat session
	 * 
	 * SECURITY: Ownership verification - prevents IDOR (Insecure Direct Object Reference)
	 * User can only access conversations they are a participant in
	 *
	 * @param \WP_REST_Request $request REST request object
	 * @return bool|\WP_Error
	 */
	public static function is_participant( $request ) {
		// First check authentication
		$auth_check = self::is_authenticated( $request );
		if ( is_wp_error( $auth_check ) ) {
			return $auth_check;
		}
		
		$user_id = get_current_user_id();
		$session_id = (int) $request->get_param( 'session_id' ) 
			?: (int) $request->get_param( 'id' )
			?: (int) $request->get_url_params()['id'] ?? 0;
		
		if ( ! $session_id ) {
			return new \WP_Error(
				'invalid_session',
				__( 'Invalid conversation ID.', 'drplus' ),
				[ 'status' => 400 ]
			);
		}
		
		// SECURITY: Verify user is participant before allowing access
		if ( ! Chat::is_participant( $session_id, $user_id ) ) {
			return new \WP_Error(
				'forbidden',
				__( 'You do not have permission to access this conversation.', 'drplus' ),
				[ 'status' => 403 ]
			);
		}
		
		return true;
	}
	
	/**
	 * Get current user ID (for use in controllers)
	 * 
	 * SECURITY: Always get user ID from WordPress, never trust client input
	 *
	 * @return int
	 */
	public static function get_current_user_id() {
		return get_current_user_id();
	}
}
