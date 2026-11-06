<?php
namespace MJ\Whitebox\Utils;

use MJ\Whitebox\Utils;

class Files extends Utils {
	/**
	 * Retrieves the upload directory path for storing files.
	 *
	 * This method returns the path to the WordPress uploads directory.
	 * It uses caching to avoid redundant calls to `wp_upload_dir()`.
	 * If the upload path does not exist, it attempts to create it.
	 * Returns the full path to the writable upload directory.
	 * 
	 * @param string $type Accepts: path | base
	 *
	 *
	 * @return string Full filesystem path to the upload directory.
	 */
	public static function get_upload_dir( string $type = 'path' ) {
		static $upload_dir_path = null;
		static $upload_dir_base = null;
		if( $type == 'path' ) {
			if( $upload_dir_path === null ) {
				$upload_dir = wp_upload_dir();
				if( wp_mkdir_p( $upload_dir['path'] ) ) {
					$upload_dir_path = $upload_dir['path'];
				}
			}
			return $upload_dir_path;
		}
		if( $type == 'base' ) {
			if( $upload_dir_base === null ) {
				$upload_dir = wp_upload_dir();
				if( wp_mkdir_p( $upload_dir['basedir'] ) ) {
					$upload_dir_base = $upload_dir['basedir'];
				}
			}
			return $upload_dir_base;
		}
	}

	/**
	 * Retrieves the full file path for a given filename within the WordPress uploads directory.
	 *
	 * This function ensures that the necessary upload directory structure is created if it doesn't exist.
	 *
	 * @param string $filename The name of the file.
	 *
	 * @return string The full file path including the filename within the WordPress uploads directory.
	 */
	public static function get_file_path( $filename ) : string {
		/**
		 * WordPress upload directory details.
		 *
		 * @return string File path
		 */
		return trailingslashit( self::get_upload_dir() ) . $filename;
	}

	/**
	 * Get the maximum allowed upload size in bytes.
	 *
	 * Caches the value statically for better performance.
	 *
	 * @return int Maximum upload size in bytes.
	 */
	public static function get_max_upload_size() {
		static $size = null;
		if( $size === null ) {
			$size = wp_max_upload_size();
		}
		return $size;
	}

	/**
	 * Convert bytes to megabytes.
	 *
	 * Optionally formats the result with a "MB" suffix and a specific number of decimal places.
	 * Returns an empty string for negative values.
	 *
	 * @param int $bytes Number of bytes to convert.
	 * @param bool $add_suffix Optional. Whether to append "MB" to the result. Default true.
	 * @param int $decimal_places Optional. Number of decimal places for formatting. Default 0.
	 *
	 * @return string|float Converted size in MB. Returns string if $add_suffix is true, otherwise float.
	 */
	public static function convert_bytes_to_mb( int $bytes, bool $add_suffix = true, int $decimal_places = 0 ) {
		if( $bytes < 0) {
			return '';
		}
	
		$mb = $bytes / MB_IN_BYTES; // 1 MB = 1024 * 1024 bytes
	
		if( $add_suffix ) {
			return number_format( $mb, $decimal_places ) . ' ' . _x( 'MB', 'unit symbol' );
		} else {
			return $mb;
		}
	}

	/**
	 * Convert megabytes to bytes.
	 *
	 * Returns 0 for negative values.
	 *
	 * @param int $megabytes Size in megabytes.
	 *
	 * @return int Converted size in bytes.
	 */
	public static function convert_mb_to_bytes( int $megabytes ) : int {
		if( $megabytes < 0 ) {
			return 0;
		}
	
		return $megabytes * MB_IN_BYTES; // 1 MB = 1024 * 1024 bytes
	}

	/**
	 * Downloads a file from a given URL and saves it to the WordPress uploads directory.
	 *
	 * This function automatically generates an attachment in the media library for the downloaded file.
	 *
	 * @param string $url      The URL of the file to download.
	 * @param string $filename Optional. The desired filename for the downloaded file. If not provided,
	 *                         the filename will be derived from the URL. If provided, the filename will
	 *                         include the specified name and the file extension from the URL.
	 *
	 * @return int|WP_Error Returns the attachment ID if successful, or a WP_Error object on failure.
	 */
	public static function download( $url, $filename = '' ) {
		set_time_limit( 0 );

		// Include necessary WordPress files
		include_once( ABSPATH . 'wp-admin/includes/file.php');
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		
		// Set the filename based on the URL if not provided
		if( empty( $filename ) ) {
			$filename = wp_basename( $url );
		} else {
			$ext = pathinfo( $url, PATHINFO_EXTENSION );
			$filename = "{$filename}.{$ext}";
		}

		// Get the full file path
		$file = self::get_file_path( $filename );

		// Clean and validate the URL
		$url = str_replace( ' ', '%20', $url );
		$url = esc_url_raw( $url );
		
		// Download the file to a temporary location
		$tmp_file = download_url( $url );

		// Copy the file to the desired location
		copy( $tmp_file, $file );
		@unlink( $tmp_file );

		// Add the file to the media library as an attachment
		$wp_filetype = wp_check_filetype( $filename, null );
		$attachment = [
			'post_mime_type'	=> $wp_filetype['type'],
			'post_title'		=> sanitize_file_name( $filename ),
			'post_content'		=> '',
			'post_status'		=> 'inherit'
		];
		$attach_id = wp_insert_attachment( $attachment, $file );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		return $attach_id;
	}
}