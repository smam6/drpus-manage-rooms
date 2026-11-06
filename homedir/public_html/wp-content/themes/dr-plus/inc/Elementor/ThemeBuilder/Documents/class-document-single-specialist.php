<?php
namespace DrPlus\Elementor\ThemeBuilder;

use DrPlus\Elementor\ThemeBuilder;

class SingleSpecialist extends \ElementorPro\Modules\ThemeBuilder\Documents\Single {

	public static function get_type() {
		return 'single-specialist';
	}

	public function get_name() {
		return 'single-specialist';
	}

	public static function get_title() {
		return __( 'Single Specialist', 'drplus' );
	}

	public static function get_preview_as_default() {
		return 'single/specialist';
	}

	public static function get_plural_title() {
		return __( 'Single Specialists', 'drplus' );
	}

	public function get_preview_as_post_id() {
		return ThemeBuilder::get_default_single_preview_id( 'specialist' );
	}

	public function get_preview_as_query_args() {
		$preview_id = $this->get_preview_as_post_id();

		if ( ! $preview_id ) {
			return [];
		}

		return [
			'p'         => (int) $preview_id,
			'post_type' => 'specialist',
			'name'      => get_post_field( 'post_name', $preview_id ),
		];
	}


	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['location'] = 'single-specialist';
		$properties['condition_type'] = 'singular';
		$properties['supports'] = [
			'title' => true,
		];

		return $properties;
	}

	public static function get_preview_type() {
		return 'single';
	}
}