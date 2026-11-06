<?php
namespace DrPlus\Backend;

use DrPlus\Utils;

class Comments {
	public static function filter_links( $views ) {
		$current = isset( $_GET['only_specialists'] ) && Utils::to_bool( $_GET['only_specialists'] );
		// Count the number of comments that meet your custom condition
		$count = get_comments( [
			'count'		=> true,
			'post_type'	=> 'specialist',
		] );
		// Add the new tab link
		$url = add_query_arg( 'only_specialists', '1', admin_url( 'edit-comments.php' ) );
		$class = $current ? 'current' : '';

		$views['only_specialists'] = sprintf(
			'<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
			esc_url( $url ),
			esc_attr( $class ),
			__( 'Only specialists', 'drplus' ),
			$count
		);

		Utils::reposition_array_element( $views, 'only_specialists', 2 );

		return $views;
	}

	public static function filter_comments( $query ) {
		if( !is_admin() ) return;

		if( isset( $_GET['only_specialists'] ) && Utils::to_bool( $_GET['only_specialists'] ) ) {
			$query->query_vars['post_type'] = 'specialist';
		}

		if( isset( $_GET['specialist'] ) && !empty( absint( $_GET['specialist'] ) ) ) {
			$post = get_posts( [
				'meta_key'		=> '_drplus_specialist_id',
				'meta_value'	=> absint( $_GET['specialist'] ),
				'post_type'		=> 'specialist',
				'numberposts'	=> 1
			] );
			if( !empty( $post[0] ) ) {
				$query->query_vars['post__in'] = [$post[0]->ID];
			} else {
				$query->query_vars['post__in'] = [0];
			}
		}
	}
}
add_filter( 'views_edit-comments', [Comments::class, 'filter_links'] );
add_action( 'pre_get_comments', [Comments::class, 'filter_comments'] );