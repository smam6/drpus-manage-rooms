<?php
namespace DrPlus\Elementor\ThemeBuilder;

class ArchiveSpecialist extends \ElementorPro\Modules\ThemeBuilder\Documents\Archive {

	public static function get_type() {
		return 'archive-specialist';
	}

	public function get_name() {
		return 'archive-specialist';
	}

	public static function get_title() {
		return __( 'Specialist Archive', 'drplus' );
	}

	public static function get_plural_title() {
		return __( 'Specialists Archive', 'drplus' );
	}

	public function get_preview_as_post_id() {
		return 0;
	}

	public function get_preview_as_query_args() {
		$args = [
			'post_type' => 'specialist',
		];

		$archive_link = get_post_type_archive_link( 'specialist' );

		if ( $archive_link ) {
			$parsed = wp_parse_url( $archive_link );

			if ( ! empty( $parsed['path'] ) ) {
				$args['pagename'] = trim( $parsed['path'], '/' );
			}
		}

		return $args;
	}


	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['location'] = 'archive-specialist';
		$properties['condition_type'] = 'archive';
		$properties['supports'] = [
			'title' => true,
		];

		return $properties;
	}

	public static function get_preview_type() {
		return 'archive';
	}
}