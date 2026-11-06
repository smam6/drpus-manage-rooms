<?php
namespace DrPlus\REST;

/**
 * Rate Limiter for REST API endpoints
 * 
 * Implements rate limiting using WordPress transients
 * Key format: drplus_rate_limit_{user_id}_{endpoint}_{minute}
 */
class RateLimiter {
	
	/**
	 * Check if a request should be rate limited
	 *
	 * @param int $user_id WordPress user ID
	 * @param string $endpoint Endpoint identifier (e.g., 'chat_send_message')
	 * @param int $limit Maximum requests allowed per minute
	 * @return array|true Returns true if allowed, or array with WP_Error if rate limited
	 */
	public static function check( $user_id, $endpoint, $limit = 10 ) {
		$current_minute = (int) ( time() / 60 );
		$transient_key = "drplus_rate_limit_{$user_id}_{$endpoint}_{$current_minute}";
		
		$current_count = (int) get_transient( $transient_key );
		
		if ( $current_count >= $limit ) {
			return new \WP_Error(
				'rate_limit_exceeded',
				__( 'Too many requests. Please try again later.', 'drplus' ),
				[ 'status' => 429 ]
			);
		}
		
		// Increment counter
		set_transient( $transient_key, $current_count + 1, 61 );
		return true;
	}
	
	/**
	 * Get remaining requests for user on endpoint
	 *
	 * @param int $user_id WordPress user ID
	 * @param string $endpoint Endpoint identifier
	 * @param int $limit Maximum requests allowed per minute
	 * @return int Remaining requests
	 */
	public static function get_remaining( $user_id, $endpoint, $limit = 10 ) {
		$current_minute = (int) ( time() / 60 );
		$transient_key = "drplus_rate_limit_{$user_id}_{$endpoint}_{$current_minute}";
		
		$current_count = (int) get_transient( $transient_key );
		return max( 0, $limit - $current_count );
	}
	
	/**
	 * Reset rate limit for testing purposes
	 *
	 * @param int $user_id WordPress user ID
	 * @param string $endpoint Endpoint identifier
	 * @return void
	 */
	public static function reset( $user_id, $endpoint ) {
		$current_minute = (int) ( time() / 60 );
		$transient_key = "drplus_rate_limit_{$user_id}_{$endpoint}_{$current_minute}";
		delete_transient( $transient_key );
	}
}
