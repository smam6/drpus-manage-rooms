<?php
namespace DrPlus\AJAX;

use DrPlus\AJAX;

class Upload extends AJAX {
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

	public function upload() {
		if( !function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
	 
		$uploaded_file = $_FILES['file'];
		$upload_overrides = ['test_form' => false];
	 
		$movefile = wp_handle_upload( $uploaded_file, $upload_overrides );
	 
		if( $movefile && !isset( $movefile['error'] ) ) {
			 // Optionally, insert the file into the media library
			 $attachment = [
				'guid'           => $movefile['url'],
				'post_mime_type' => $movefile['type'],
				'post_title'     => basename( $movefile['file'] ),
				'post_content'   => '',
				'post_status'    => 'inherit'
			];
	 
			$attach_id = wp_insert_attachment( $attachment, $movefile['file'] );
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$attach_data = wp_generate_attachment_metadata( $attach_id, $movefile['file'] );
			wp_update_attachment_metadata( $attach_id, $attach_data );
	 
			$this->result( 'success', [
				'url'		=> $movefile['url'],
				'id'		=> $attach_id,
				'filename'	=> wp_basename( $movefile['file'] ),
				'size'		=> size_format( filesize( $movefile['file'] ) )
			] );
		} else {
			$this->result( 'error', [
				'code'	=> 'error',
				'msg'	=> $movefile['error']
			] );
		}
	}
}