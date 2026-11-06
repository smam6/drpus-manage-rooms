<?php
namespace DrPlus\Utils;

use DrPlus\Utils;

class Hospital extends Utils {
	private static $options = [];

	public static function default_options( $default_location = false ) {
		return [
			'icon'				=> 'drplus-icon-hospital-1',
			'subtitle'			=> '',
			'province'			=> '',
			'province_id'		=> 0,
			'city'				=> '',
			'city_id'			=> 0,
			'address'			=> '',
			'map_address'		=> '',
			'gallery'			=> [],
			'services'			=> [],
			'phones'			=> [],
			'emails'			=> [],
			'socials'			=> [],
		];
	}

	public static function default_service() {
		return [
			'title'			=> '',
			'description'	=> '',
		];
	}

	public static function default_phone() {
		return [
			'title'	=> '',
			'phone'	=> '',
		];
	}

	public static function default_email() {
		return [
			'title'	=> '',
			'email'	=> '',
		];
	}

	public static function default_social() {
		return [
			'title'	=> '',
			'icon'	=> 'drplus-icon-instagram',
			'link'	=> '#',
		];
	}

	public static function get_options( $post = 0, $default_location = false, array $options = [] ) {
		$post = parent::get_post( $post );

		// Because of the self caching maybe some options doesn't get it will cause issue
		$options = [];

		if( empty( self::$options[$post->ID] ) ) {
			if( in_array( ['city', 'province'], $options ) || empty( $options ) ) {
				$get_location = true;
				Utils::unset( $options, ['city', 'province'] );
			}

			self::$options[$post->ID] = parent::get_post_options( self::default_options( $default_location ), $post->ID, $options );

			// get location terms
			if( !empty( $get_location ) ) {
				$hospital_location_terms = get_the_terms( $post->ID, 'location' );
				if( !is_wp_error( $hospital_location_terms ) && !empty( $hospital_location_terms ) ) {

					$has_child_term = false;

					// Step 1: check if any child (city) term exists
					foreach ( $hospital_location_terms as $term ) {
						if ( ! empty( $term->parent ) ) {
							$has_child_term = true;
							break;
						}
					}

					// Step 2: assign values based on hierarchy existence
					foreach ( $hospital_location_terms as $term ) {

						if ( $has_child_term ) {

							// Normal case: province + city
							if ( empty( $term->parent ) ) {
								self::$options[$post->ID]['province']    = $term->name;
								self::$options[$post->ID]['province_id'] = $term->term_id;
							} else {
								self::$options[$post->ID]['city']    = $term->name;
								self::$options[$post->ID]['city_id'] = $term->term_id;
							}

						} else {

							// Edge case: only one level → city
							if ( empty( $term->parent ) ) {
								self::$options[$post->ID]['city']    = $term->name;
								self::$options[$post->ID]['city_id'] = $term->term_id;
							}

							// Explicitly clear province
							self::$options[$post->ID]['province']    = '';
							self::$options[$post->ID]['province_id'] = 0;
						}
					}
				}
			}
		}

		return self::$options[$post->ID];
	}

	public static function save_options( array $options, $post_id = 0 ) {
		if( isset( $options['icon'] ) ) {
			$options['icon'] = parent::convert_chars( $options['icon'] );
		}
		if( isset( $options['subtitle'] ) ) {
			$options['subtitle'] = parent::convert_chars( $options['subtitle'] );
		}
		if( isset( $options['address'] ) ) {
			$options['address'] = parent::convert_chars( $options['address'] );
		}
		if( isset( $options['map_address'] ) ) {
			$options['map_address'] = Utils::normalize_map_src( $options['map_address'] );
		}
		if( isset( $options['gallery'] ) ) {
			if( is_string( $options['gallery'] ) ) {
				$options['gallery'] = explode( ",", $options['gallery'] );
			}
			$options['gallery'] = array_filter( array_map( fn( $id ) => parent::convert_chars( $id, true, 'absint' ), $options['gallery'] ) );
		}
		if( isset( $options['services'] ) ) {
			$services = [];
			foreach( $options['services'] as $service ) {
				$service = parent::check_default( $service, self::default_service() );
				if( !$service['title'] ) continue;
				$service['title'] = parent::convert_chars( $service['title'] );
				$service['description'] = parent::convert_chars( $service['description'] );
				$services[] = $service;
			}

			$options['services'] = $services;
		}
		if( isset( $options['phones'] ) ) {
			$phones = [];
			foreach( $options['phones'] as $phone ) {
				$phone = parent::check_default( $phone, self::default_phone() );
				if( !$phone['title'] && !$phone['phone'] ) continue;
				$phone['title'] = parent::convert_chars( $phone['title'] );
				$phone['phone'] = Sanitizers::phone( $phone['phone'] );
				$phones[] = $phone;
			}

			$options['phones'] = $phones;
		}
		if( isset( $options['emails'] ) ) {
			$emails = [];
			foreach( $options['emails'] as $email ) {
				$email = parent::check_default( $email, self::default_email() );
				if( !$email['title'] && !$email['email'] ) continue;
				$email['title'] = parent::convert_chars( $email['title'] );
				$email['email'] = parent::convert_chars( $email['email'], true, 'sanitize_email' );
				$emails[] = $email;
			}

			$options['emails'] = $emails;
		}
		if( isset( $options['socials'] ) ) {
			$socials = [];
			foreach( $options['socials'] as $social ) {
				$social = parent::check_default( $social, self::default_social() );
				if( !$social['link'] ) continue;
				$social['title'] = parent::convert_chars( $social['title'] );
				$social['icon'] = parent::convert_chars( $social['icon'] );
				$social['link'] = $social['link'] != '#' ? sanitize_url( $social['link'], ['https', 'http'] ) : '#';
				$socials[] = $social;
			}

			$options['socials'] = $socials;
		}
		parent::save_post_options( $options, self::default_options(), $post_id );
	}
}