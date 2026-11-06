<?php
namespace DrPlus\Utils;

use DrPlus\Utils;

class Page extends Utils {
	private static $options_cache = [];

	public static function default_options() {
		return [
			'disable_header'		=> false,
			'disable_header_user'	=> 'all',
			'show_breadcrumb'		=> true,
			'show_title'			=> true,
			'show_sidebar'			=> true,
			'fullwidth'				=> false,
			'use_content_style'		=> true,
			'disable_footer'		=> false,
			'disable_footer_user'	=> 'all',
			'page_icon'				=> 'drplus-icon-discovery',
			'sidebar'				=> 'page',
		];
	}

	public static function get_options( $post_id = null ) {
		$post_id = parent::get_post_id( $post_id );

		if( !empty( self::$options_cache[$post_id] ) ) {
			return self::$options_cache[$post_id];
		}

		self::$options_cache[$post_id] = parent::get_post_options( self::default_options(), $post_id );
		
		return self::$options_cache[$post_id];
	}

	public static function save_options( array $options, $post_id = null ) {
		parent::save_post_options( $options, self::default_options(), $post_id );
	}
}