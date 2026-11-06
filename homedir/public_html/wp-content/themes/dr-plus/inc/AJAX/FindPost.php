<?php
namespace DrPlus\AJAX;

use DrPlus\AJAX;

class FindPost extends AJAX {
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

	public function query() {
		$this->set_request_data();
		
		$this->data['text'] = sanitize_text_field( $this->data['text'] );

		$args = [
			's'					=> $this->data['text'],
			'posts_per_page'	=> -1,
			'post_type'			=> 'any'
		];
		$query = new \WP_Query( $args );
		$posts = [];
		$post_types = [];
		if( $query->have_posts() ) {
			while( $query->have_posts() ) {
				$query->the_post();
				
				$post_type = get_post_type();
				if( !isset( $post_types[$post_type] ) ) {
					$post_types[$post_type] = get_post_type_object( $post_type )->labels->name;
				}

				$posts[] = [
					'id'	=> get_the_ID(),
					'text'	=> "({$post_types[$post_type]}) " . get_the_title(),
				];
			}
			wp_reset_postdata();
		}

		$this->result( 'success', $posts );
	}
}