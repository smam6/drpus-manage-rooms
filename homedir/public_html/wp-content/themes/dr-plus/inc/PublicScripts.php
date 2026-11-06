<?php
namespace DrPlus;

if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( "\DrPlus\PublicScripts" ) ) {
	class PublicScripts {
		public static function dragscroll() {
			wp_enqueue_script( 'drplus-dragscroll', DRPLUS_URI . "assets/libs/dragscroll/dragscroll.js", [], DRPLUS_VERSION, true );
		}
		
		public static function swiper() {
			wp_enqueue_style( 'drplus-swiper', DRPLUS_URI . "assets/libs/swiper/swiper-bundle.min.css", [], DRPLUS_VERSION );
			wp_enqueue_script( 'drplus-swiper', DRPLUS_URI . "assets/libs/swiper/swiper-bundle.min.js", [], DRPLUS_VERSION, true );
		}

		public static function slider( $include_dependencies = true ) {
			if( $include_dependencies ) {
				self::swiper();
			}

			if( DRPLUS_DEV ) {
				wp_enqueue_script( 'drplus-slider', DRPLUS_URI . "assets/js/slider.js", ['jquery'], DRPLUS_VERSION, true );
			} else {
				wp_enqueue_script( 'drplus-slider', DRPLUS_URI . "assets/js/slider.min.js", ['jquery'], DRPLUS_VERSION, true );
			}
		}

		public static function pdp() {
			wp_enqueue_style( 'drplus-pdp', DRPLUS_URI . "assets/libs/pdp/persian-datepicker.min.css", [], DRPLUS_VERSION );
			wp_enqueue_script( 'drplus-pd', DRPLUS_URI . "assets/libs/pdp/persian-date.min.js", ['jquery'], DRPLUS_VERSION, true );
			wp_enqueue_script( 'drplus-pdp', DRPLUS_URI . "assets/libs/pdp/persian-datepicker.min.js", ['jquery'], DRPLUS_VERSION, true );
		}

		public static function select2() {
			wp_enqueue_style( 'drplus-select2', DRPLUS_URI . "assets/libs/select2/select2.min.css", [], DRPLUS_VERSION );
			wp_enqueue_script( 'drplus-select2', DRPLUS_URI . "assets/libs/select2/select2.min.js", ['jquery'], DRPLUS_VERSION, true );
		}

		public static function swapy() {
			wp_enqueue_script( 'drplus-swapy', DRPLUS_URI . "assets/libs/swapy/swapy.min.js", [], DRPLUS_VERSION, true );
		}

		public static function dropzone() {
			wp_enqueue_style( 'drplus-dropzone', DRPLUS_URI . "assets/libs/dropzone/dropzone.min.css", [], DRPLUS_VERSION );
			wp_enqueue_script( 'drplus-dropzone', DRPLUS_URI . "assets/libs/dropzone/dropzone.min.js", ['jquery'], DRPLUS_VERSION, true );
		}

		public static function circle_progress() {
			wp_enqueue_script( 'drplus-circle-progress', DRPLUS_URI . "assets/libs/circle-progress.min.js", ['jquery'], DRPLUS_VERSION, true );
		}

		public static function lightgallery() {
			static $enqueued = false;
			if( !$enqueued ) {
				wp_enqueue_style( 'drplus-lightgallery', DRPLUS_URI . "assets/libs/lightgallery/css/lightgallery-bundle.min.css", [], DRPLUS_VERSION );
				wp_enqueue_script( 'drplus-lightgallery', DRPLUS_URI . "assets/libs/lightgallery/lightgallery.umd.min.js", [], DRPLUS_VERSION, true );

				$enqueued = true;
			}
		}

		public static function localizations( array $components = [] ) {
			if( in_array( 'cities', $components ) ) {
				$all_locations = get_terms( [
					'taxonomy'		=> 'location',
					'hide_empty'	=> false,
				] );
				// Prepare cities data
				$cities = [];
				foreach( $all_locations as $term ) {
					if( $term->parent != 0 ) {
						$cities[$term->parent][] = [
							'id'	=> $term->term_id,
							'name'	=> $term->name,
						];
					}
				}
				wp_localize_script( 'drplus', 'drplusCities', $cities );
			}

		}
	}
}