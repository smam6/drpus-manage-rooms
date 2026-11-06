<?php
namespace DrPlus\AJAX;

use DrPlus\AJAX;
use DrPlus\Utils;
use DrPlus\Utils\Hospital;
use DrPlus\Utils\Location;
use DrPlus\Utils\Options;
use DrPlus\Utils\Sanitizers;
use DrPlus\Utils\Speciality;
use DrPlus\Utils\UtilsSpecialists;

class Search extends AJAX {
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

	public function exec() {
		$this->set_request_data();

		$search_term = Utils::convert_chars( $this->data['text'] );
		if( strlen( $search_term ) < 2 ) {
			$this->result( 'error', __( 'Please enter at least 2 characters.', 'drplus' ) );
		}

		$options = Options::get_options( [
			'exclude_post_types'	=> ['page', 'attachment', 'e-floating-buttons'],
			'search_specialist'		=> true,
		] );

		if( DRPLUS_SINGLE_SPECIALIST_MODE ) $options['search_specialist'] = false;

		$post_types = get_post_types( [
			'public'				=> true,
			'exclude_from_search'	=> false,
		] );

		$excludes = [];
		$only = !empty( $this->data['only'] ) ? Utils::convert_chars( $this->data['only'] ) : '';
		if( !$only ) {
			$excludes = $options['exclude_post_types'];
			if( !empty( $this->data['excludes'] ) ) {
				$custom_excludes = json_decode( stripslashes( $this->data['excludes'] ), true );
				$excludes = array_values( array_unique( array_merge( $excludes, $custom_excludes ) ) );
			}
			$post_types = array_diff( $post_types, $excludes );
		} else {
			$post_types = [$only];
		}

		if( ( $only && in_array( $only, ['specialist', 'specialist_online_visit', 'specialist_offline_visit'] ) ) || ( !$only && Utils::to_bool( $options['search_specialist'] ) && !in_array( 'specialist', $excludes ) ) ) {
			$args = [];
			if( $only == 'specialist_online_visit' ) {
				$args['only_online_visits'] = true;
			} else if( $only == 'specialist_offline_visit' ) {
				$args['only_offline_visits'] = true;
			}
			$specialists = UtilsSpecialists::search( $search_term, $args );
		}
		
		if( $only != 'specialist' ) {
			$posts = new \WP_Query( [
				's'						=> $search_term,
				'post_type'				=> $post_types,
				'status'				=> 'publish',
				'ignore_sticky_posts'	=> true,
				'posts_per_page'		=> 5,
			] );
		}

		$items = [];
		if( !empty( $specialists ) && Utils::to_bool( $options['search_specialist'] ) ) {
			foreach( $specialists as $specialist ) {
				$items[] = [
					'value'	=> $specialist->id,
					'text'	=> $specialist->display_name,
					'sub'	=> $specialist->subtitle,
					'icon'	=> '',
					'img'	=> get_avatar_url( $specialist->user_id ),
					'link'	=> get_permalink( $specialist->post_id ),
				];
			}
		}
		if( $only != 'specialist' ) {
			if( $posts->have_posts() ) {
				while( $posts->have_posts() ) {
					$posts->the_post();
					$icon = '';
					$img = '';
					$sub = '';
					$link = '';
					$post_type = get_post_type();
					if( $post_type == 'speciality' ) {
						$speciality_options = Speciality::get_options( get_the_ID() );
						$sub = sprintf( esc_html__( "%d specialists", 'drplus' ), Speciality::count_specialists( get_the_ID() ) );
						$icon = $speciality_options['icon'];
						$img = get_the_post_thumbnail( null, [56, 56] );
						$link = Speciality::get_archive_link( get_post() ); // Archive with this speciality search term
					} else if( $post_type == 'hospital' ) {
						$hospital_options = Hospital::get_options( get_the_ID(), false, ['city'] );
						$sub = $hospital_options['city'];
						$img = get_the_post_thumbnail_url( null, [56, 56] );
						$link = get_the_permalink();
					} else if( $post_type == 'post' ) {
						$sub = drplus_get_post_views( get_the_ID() );
						$img = get_the_post_thumbnail_url( null, [56, 56] );
						$link = get_the_permalink();
					} else if( $post_type == 'product' ) {
						$product = wc_get_product( get_the_ID() );
						$sub = wc_price( $product->get_price() );
						$img_id = $product->get_image_id();
						$img = $img_id ? wp_get_attachment_image_url( $img_id, [56, 56] ) : wc_placeholder_img_src( [56, 56] );
						$link = get_permalink( $product->get_id() );
					}
					$item_args = apply_filters( 'drplus/search/ajax/item_args', [
						'value'	=> "post_" . get_the_ID(),
						'text'	=> get_the_title(),
						'sub'	=> $sub,
						'icon'	=> $icon,
						'img'	=> $img,
						'link'	=> $link ? $link : get_permalink(),
					], $post_type, $search_term );
					$items[] = $item_args;
				}
				wp_reset_postdata();
			}
		}

		$this->result( 'success', $items );
	}

	public function onboard() {
		$this->set_request_data();

		$search_term = Utils::convert_chars( $this->data['text'] );
		if( strlen( $search_term ) < 2 ) {
			$this->result( 'error', __( 'Please enter at least 2 characters.', 'drplus' ) );
		}

		$type = Utils::convert_chars( $this->data['type'] );
		$type = Utils::ensure_values_in_array( $type, ['speciality', 'hospital'], '' );
		if( !$type ) {
			$this->result( 'error', __( 'The type of the search is not correct.', 'drplus' ) );
		}

		$current_values = json_decode( stripslashes( $this->data['current_values'] ), true );

		$posts = get_posts( [
			'post_type'				=> $type,
			'numberposts'			=> -1,
			'status'				=> 'publish',
			'ignore_sticky_posts'	=> true,
			's'						=> $search_term,
		] );
		if( $posts ) {
			$html = '';
			$label_classes = ['checkbox-wrap', 'checkbox-box', 'onboard-speciality', 'onboard-search-item'];
			if( $type == 'speciality' ) {
				$label_classes[] = 'onboard-speciality';
			} else if( $type == 'hospital' ) {
				$label_classes[] = 'onboard-hospital';
			}

			$text_classes = ['checkbox-label', 'line-clamp', 'line-clamp-2'];
			if( $type == 'speciality' ) {
				$text_classes[] = 'onboard-speciality-name';
			} else if( $type == 'hospital' ) {
				$text_classes[] = 'onboard-hospital-name';
			}

			if( $type == 'speciality' ) {
				$input_name = 'specialist_specialities[]';
			} else if( $type == 'hospital' ) {
				$input_name = 'specialist_offices[hospitals][]';
			}

			foreach( $posts as $post ) {
				$item_label_classes = $label_classes;
				$input_args = [
					'type'	=> 'checkbox',
					'name'	=> $input_name,
					'class'	=> 'checkbox',
					'value'	=> $post->ID,
				];

				if( in_array( $post->ID, $current_values ) ) {
					$item_label_classes[] = 'checked';
					$input_args['checked'] = 'checked';
				}

				$label_attrs = [
					'class'		=> $item_label_classes,
					'title'		=> $post->post_title,
					'data-id'	=> $post->ID
				];

				$html .= '<label ' . Utils::get_html_attributes( $label_attrs ) . '>';
					if( $type == 'speciality' ) {
						$speciality_options = Speciality::get_options( $post->ID );
						$html .= Sanitizers::icon( $speciality_options['icon'], 'checkbox-icon onboard-speciality-icon' );
					} else if( $type == 'hospital' ) {
						$hospital_options = Hospital::get_options( $post->ID, false, ['city'] );
						if( has_post_thumbnail( $post ) ) {
							$html .= get_the_post_thumbnail( $post->ID, [56, 56] );
						} else {
							$html .= Sanitizers::icon( 'drplus-icon-hospital', 'checkbox-icon' );
						}
					}
					$html .= '<div class="' . Utils::prepare_html_classes( $text_classes ) . '">';
						$html .= esc_html( $post->post_title );
						if( $type == 'hospital' ) {
							$html .= '<div class="checkbox-label-sub onboard-hospital-city">' . esc_html( $hospital_options['city'] ) . '</div>';
						}
					$html .= '</div>';
					$html .= '<input ' . Utils::get_html_attributes( $input_args ) . '>';
				$html .= '</label>';
			}

			$this->result( 'success', [
				'html'	=> $html,
				'ids'	=> !empty( $posts ) ? wp_list_pluck( $posts, 'ID' ) : [],
			] );
		} else {
			$this->result( 'error', __( 'No results. Please try again with a different text.', 'drplus' ) );
		}
	}

	public function cities() {
		$this->set_request_data();

		$text = Utils::convert_chars( $this->data['text'] );

		$locations_terms = Location::locations( null, false, [
			'search'	=> $text,
		], true );

		$locations = [];
		$results = [];
		foreach( $locations_terms as $location ) {
			$locations[$location->term_id] = $location;
			if( !$location->parent ) continue;

			if( !isset( $locations[$location->parent] ) ) {
				$locations[$location->parent] = get_term( $location->parent, 'location' );
			}

			$results[$location->term_id] = [
				'id'		=> $location->term_id,
				'name'		=> $location->name,
				'slug'		=> $location->slug,
				'province'	=> $locations[$location->parent]->name,
			];
		}
		$this->result( 'success', $results );
	}
}