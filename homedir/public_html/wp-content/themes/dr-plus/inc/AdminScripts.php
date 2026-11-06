<?php
namespace DrPlus;

use DrPlus\Utils\Options;

class AdminScripts {
	public static function metabox( $components ) {
		$_components = [
			'post_finder'	=> [
				'nonces'	=> [
					'postFinder'	=> wp_create_nonce( "drplus-metabox-post-finder" )
				],
			],
			'get_user'		=> [
				'nonces'	=> [
					'getUsers'	=> wp_create_nonce( "drplus_get_users_nonce" )
				],
				'i18n'		=> [
					'SearchUsersPlaceholder' => __( 'Select a user', 'drplus' ),
				],
			],
			'icon_picker'	=> [
				'nonces'	=> [
					'iconPicker'	=> wp_create_nonce( "drplus-metabox-icon-picker" )
				],
			],
		];
		if( DRPLUS_DEV ) {
			wp_enqueue_script( 'drplus-metaboxes', DRPLUS_URI . "assets/js/backend/metaboxes.js", ['jquery'], DRPLUS_VERSION, true );
		} else {
			wp_enqueue_script( 'drplus-metaboxes', DRPLUS_URI . "assets/js/backend/metaboxes.min.js", ['jquery'], DRPLUS_VERSION, true );
		}

		$localize = [
			'ajaxUrl'	=> admin_url( 'admin-ajax.php' ),
		];

		if( empty( $components ) ) {
			$components = array_keys( $_components );
		}
		foreach( $components as $component ) {
			if( !isset( $_components[$component] ) ) continue;

			$localize = array_merge_recursive( $localize, $_components[$component] );
		}

		wp_localize_script( 'drplus-metaboxes', 'drplusMetabox', $localize );
	}

	public static function tabs() {
		wp_enqueue_style( 'drplus-tabs', DRPLUS_URI . "assets/css/backend/components/tabs.min.css", [], DRPLUS_VERSION );
	}

	public static function switch() {
		wp_enqueue_style( 'drplus-switch', DRPLUS_URI . "assets/css/backend/components/switch.min.css", [], DRPLUS_VERSION );
		if( is_rtl() ) {
			wp_enqueue_style( 'drplus-switch-rtl', DRPLUS_URI . "assets/css/backend/components/switch.rtl.min.css", [], DRPLUS_VERSION );
		}
	}

	public static function switch_select() {
		wp_enqueue_style( 'drplus-switch-select', DRPLUS_URI . "assets/css/backend/components/switch-select.min.css", [], DRPLUS_VERSION );
	}

	public static function alert() {
		wp_enqueue_style( 'drplus-alert', DRPLUS_URI . "assets/css/backend/components/alert.min.css", [], DRPLUS_VERSION );
	}

	public static function attachment() {
		wp_enqueue_style( 'drplus-attachment', DRPLUS_URI . "assets/css/backend/components/attachment.min.css", [], DRPLUS_VERSION );
	}

	public static function modal() {
		wp_enqueue_style( "drplus-modal", DRPLUS_URI . "assets/css/backend/components/modal.min.css", [], DRPLUS_VERSION );
		if( DRPLUS_DEV ) {
			wp_enqueue_script( 'drplus-modal', DRPLUS_URI . "assets/js/backend/components/modal.js", ['jquery'], DRPLUS_VERSION, true );
		} else {
			wp_enqueue_script( 'drplus-modal', DRPLUS_URI . "assets/js/backend/components/modal.min.js", ['jquery'], DRPLUS_VERSION, true );
		}
	}

	public static function icon_picker() {
		$options = Options::get_options( [
			'm-icons'	=> false,
		] );
		wp_enqueue_style( 'drplus-font-awesome', DRPLUS_URI . "assets/libs/fontawesome/css/fa.min.css", [], DRPLUS_VERSION );
		wp_enqueue_style( 'drplus-icons', DRPLUS_URI . "assets/css/iconly.min.css", [], DRPLUS_VERSION );
		if( $options['m-icons'] ) {
			wp_enqueue_style( 'drplus-m-icons', DRPLUS_URI . "assets/css/drplus-m.min.css", [], DRPLUS_VERSION );
		}
		wp_enqueue_style( "drplus-icon-picker", DRPLUS_URI . "assets/css/backend/components/icon-picker.min.css", [], DRPLUS_VERSION );
		if( DRPLUS_DEV ) {
			wp_enqueue_script( 'drplus-icon-picker', DRPLUS_URI . "assets/js/backend/components/icon-picker.js", ['jquery'], DRPLUS_VERSION, true );
		} else {
			wp_enqueue_script( 'drplus-icon-picker', DRPLUS_URI . "assets/js/backend/components/icon-picker.min.js", ['jquery'], DRPLUS_VERSION, true );
		}
		wp_localize_script( 'drplus-icon-picker', 'drplusIconPicker', [
			'ajaxUrl'	=> admin_url( 'admin-ajax.php' ),
			'nonce'		=> wp_create_nonce( "drplus-icon-picker" ),
		] );
	}

	public static function repeater() {
		wp_enqueue_style( 'drplus-repeater', DRPLUS_URI . "assets/css/backend/components/repeater.min.css", [], DRPLUS_VERSION );
	}

	public static function form_group() {
		wp_enqueue_style( 'drplus-form-group', DRPLUS_URI . "assets/css/backend/components/form_group.min.css", [], DRPLUS_VERSION );
	}
}