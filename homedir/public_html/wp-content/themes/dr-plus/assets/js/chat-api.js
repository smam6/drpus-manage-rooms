/**
 * Chat REST API Wrapper
 * 
 * Wrapper functions for all chat REST API endpoints
 * Replaces AJAX calls with WordPress REST API calls
 * 
 * Usage:
 *   chatAPI.sendMessage(sessionId, message, type, fileUrl)
 *   chatAPI.getMessages(sessionId, afterId, page, perPage)
 *   chatAPI.markSeen(sessionId)
 *   chatAPI.getConversations(type)
 *   chatAPI.uploadFile(sessionId, file)
 */

const chatAPI = (function() {
	'use strict';
	
	// Get REST API base URL
	const baseUrl = () => {
		if ( typeof drplusVars !== 'undefined' && drplusVars.restApiBaseUrl ) {
			return drplusVars.restApiBaseUrl;
		}
		return '/wp-json/drplus_chat/v1';
	};
	
	/**
	 * Get nonce for WordPress REST API
	 * 
	 * WordPress REST API uses the standard X-WP-Nonce header
	 * The nonce can be injected via wp_localize_script
	 */
	const getNonce = () => {
		if ( typeof drplusChat !== 'undefined' && drplusChat.restNonce ) {
			return drplusChat.restNonce;
		}
		// Fallback: check for nonce in meta tag or global
		const nonceElement = document.querySelector( 'meta[name="wp-rest-nonce"]' );
		return nonceElement ? nonceElement.getAttribute( 'content' ) : '';
	};
	
	/**
	 * Make REST API request
	 * 
	 * @param {string} endpoint - REST endpoint (e.g., '/conversations')
	 * @param {string} method - HTTP method (GET, POST, PUT, DELETE)
	 * @param {object} data - Request data (query params for GET, body for POST/PUT)
	 * @param {boolean} isMultipart - Whether to use FormData
	 * @returns {Promise}
	 */
	const request = async ( endpoint, method = 'GET', data = {}, isMultipart = false ) => {
		const url = new URL( baseUrl() + endpoint, window.location.origin );
		
		// For GET requests, add data as query parameters
		if ( method === 'GET' ) {
			Object.keys( data ).forEach( key => {
				if ( data[key] !== null && data[key] !== undefined ) {
					url.searchParams.append( key, data[key] );
				}
			});
		}
		
		const options = {
			method: method,
			headers: {
				'X-WP-Nonce': getNonce(),
			},
		};
		
		// For POST/PUT requests, add body
		if ( method !== 'GET' ) {
			if ( isMultipart ) {
				// For file uploads, use FormData (don't set Content-Type header)
				options.body = data;
			} else {
				// For JSON, set Content-Type and stringify
				options.headers['Content-Type'] = 'application/json';
				options.body = JSON.stringify( data );
			}
		}
		
		try {
			const response = await fetch( url.toString(), options );
			const json = await response.json();
			
			// Handle HTTP error responses
			if ( ! response.ok ) {
				if ( response.status === 429 ) {
					throw {
						status: 429,
						code: 'rate_limit_exceeded',
						message: json.message || 'Too many requests. Please try again later.',
						rest: json,
					};
				}
				throw {
					status: response.status,
					code: json.code || 'error',
					message: json.message || 'Request failed',
					rest: json,
				};
			}
			
			return json;
		} catch ( error ) {
			if ( error.code ) {
				// Already formatted error
				throw error;
			}
			// Network or parsing error
			throw {
				status: 0,
				code: 'network_error',
				message: error.message || 'Network error',
			};
		}
	};
	
	return {
		/**
		 * Send a new message to a conversation
		 * 
		 * @param {number} sessionId - Conversation ID
		 * @param {string} message - Message content
		 * @param {string} type - Message type (text, file, voice)
		 * @param {string} fileUrl - URL of file (required for file/voice messages)
		 * @returns {Promise}
		 */
		sendMessage: async ( sessionId, message, type = 'text', fileUrl = null ) => {
			const data = {
				message: message,
				type: type,
			};
			if ( fileUrl ) {
				data.file_url = fileUrl;
			}
			
			return request( `conversations/${sessionId}/messages`, 'POST', data );
		},
		
		/**
		 * Get messages from a conversation
		 * 
		 * @param {number} sessionId - Conversation ID
		 * @param {number} afterId - Get messages after this ID (for delta polling)
		 * @param {number} page - Page number for pagination
		 * @param {number} perPage - Messages per page
		 * @returns {Promise}
		 */
		getMessages: async ( sessionId, afterId = 0, page = 1, perPage = 50 ) => {
			const data = {};
			if ( afterId > 0 ) {
				data.after_id = afterId;
			}
			if ( page > 1 || afterId === 0 ) {
				data.page = page;
			}
			if ( perPage !== 50 ) {
				data.per_page = perPage;
			}
			
			return request( `conversations/${sessionId}/messages`, 'GET', data );
		},
		
		/**
		 * Mark messages as seen
		 * 
		 * @param {number} sessionId - Conversation ID
		 * @returns {Promise}
		 */
		markSeen: async ( sessionId ) => {
			return request( `conversations/${sessionId}/messages/mark-seen`, 'POST' );
		},
		
		/**
		 * Get all conversations for current user
		 * 
		 * @param {string} type - Conversation type (all, specialist, customer)
		 * @returns {Promise}
		 */
		getConversations: async ( type = 'all' ) => {
			const data = {};
			if ( type && type !== 'all' ) {
				data.type = type;
			}
			
			return request( '/conversations', 'GET', data );
		},
		
		/**
		 * Get single conversation details
		 * 
		 * @param {number} sessionId - Conversation ID
		 * @returns {Promise}
		 */
		getConversation: async ( sessionId ) => {
			return request( `conversations/${sessionId}`, 'GET' );
		},
		
		/**
		 * Upload file to conversation
		 * 
		 * @param {number} sessionId - Conversation ID
		 * @param {File} file - File object from input
		 * @returns {Promise}
		 */
		uploadFile: async ( sessionId, file ) => {
			const formData = new FormData();
			formData.append( 'file', file );
			
			return request( `conversations/${sessionId}/upload`, 'POST', formData, true );
		},
		
		/**
		 * Get file with permission check
		 * 
		 * @param {number} sessionId - Conversation ID
		 * @param {string} fileUrl - File URL to serve
		 * @returns {string} - URL to file
		 */
		getFileUrl: ( sessionId, fileUrl ) => {
			const url = new URL( baseUrl() + `conversations/${sessionId}/file`, window.location.origin );
			url.searchParams.append( 'file_url', fileUrl );
			return url.toString();
		},
		
		/**
		 * Get current nonce (useful for debugging)
		 * 
		 * @returns {string}
		 */
		getNonce: getNonce,
		
		/**
		 * Get base API URL
		 * 
		 * @returns {string}
		 */
		getBaseUrl: baseUrl,
	};
})();

// Expose globally if needed by other scripts
if ( typeof window !== 'undefined' ) {
	window.chatAPI = chatAPI;
}
