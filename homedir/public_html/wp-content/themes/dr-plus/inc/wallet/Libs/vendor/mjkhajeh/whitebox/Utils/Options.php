<?php
namespace MJ\Whitebox\Utils;

use MJ\Whitebox\Utils;

abstract class Options extends Utils {
	abstract public static function get_option_name();

	public static function get_options( array $defaults ) : array {
		$option_name = static::get_option_name();
		$options = !empty( $GLOBALS[$option_name] ) ? $GLOBALS[$option_name] : get_option( $option_name, $defaults );
		foreach( $defaults as $key => $value ) {
			if( isset( $options[$key] ) ) continue;

			if( is_array( $value ) && empty( $options[$key] ) ) continue; // Repeaters

			$options[$key] = $value;
		}
		if( empty( $options ) ) {
			$options = $defaults;
		}
		return $options;
	}

	/**
	 * Get logo content from options
	 *
	 * @param array $keys [
	 * 		type,
	 * 		text-type,
	 * 		text-custom
	 * 		img
	 * 		img-size
	 * ]
	 * @param string $bloginfo_option Accepts: name | description
	 * @return string
	 */
	public static function get_logo( $keys, $defaults, $bloginfo_option = 'name' ) {
		$options = static::get_options( $defaults );
		$logo_content = '';
		if( isset( $options[$keys['type']] ) ) {
			if( $options[$keys['type']] == 'text' ) {
				if( isset( $options[$keys['text-type']] ) ) {
					if( $options[$keys['text-type']] == 'title' || $options[$keys['text-type']] == 'tagline' ) {
						$logo_content = get_bloginfo( $bloginfo_option );
					} else {
						$logo_content = $options[$keys['text-custom']];
					}
				}
			} else if( $options[$keys['type']] == 'img' ) {
				if( !empty( $options[$keys['img']] ) ) {
					if( is_array( $options[$keys['img']] ) && !empty( $options[$keys['img']]['id'] ) ) {
						$size = 'full';
						if( !empty( $options[$keys['img-size']] ) ) {
							if( !empty( $options[$keys['img-size']]['width'] ) && $options[$keys['img-size']]['width'] !== 'px' && !empty( $options[$keys['img-size']]['height'] ) && $options[$keys['img-size']]['height'] !== 'px' ) {
								$size = [absint( $options[$keys['img-size']]['width'] ), absint( $options[$keys['img-size']]['height'] )];
							}
						}
						$logo_content = wp_get_attachment_image( $options[$keys['img']]['id'], $size );
					} else {
						$img_url = is_array( $options[$keys['img']] ) ? $options[$keys['img']]['url'] : $options[$keys['img']];
						$img_attrs = [
							'src'	=> esc_url( $img_url, ['http', 'https'] ),
							'alt'	=> get_bloginfo( 'name' ),
						];
						if( !empty( $options[$keys['img-size']] ) ) {
							if( !empty( $options[$keys['img-size']]['width'] ) && $options[$keys['img-size']]['width'] !== 'px' ) {
								$img_attrs['width'] = absint( $options[$keys['img-size']]['width'] );
							}
							if( !empty( $options[$keys['img-size']]['height'] ) && $options[$keys['img-size']]['height'] !== 'px' ) {
								$img_attrs['height'] = absint( $options[$keys['img-size']]['height'] );
							}
						}
						$logo_content = '<img ' . parent::get_html_attributes( $img_attrs ) . '>';
					}
				}
			}
		}
		return $logo_content;
	}

	public static function get_color( $color ) {
		if( substr( $color, 0, 1 ) == '#' ) {
			return parent::minify_hex( $color );
		} else {
			return $color;
		}
	}
}