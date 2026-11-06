<?php
namespace DrPlus\Backend;

use DrPlus\AdminScripts;
use DrPlus\Utils\AdminUI;

class WidgetsPage {
	public static function enqueue( $hook ) {
		if( $hook != 'widgets.php' ) return;
		AdminScripts::modal();
		AdminScripts::icon_picker();
	}

	public static function modal() {
		$screen = get_current_screen();
		if( $screen->parent_base != 'themes' || $screen->base != 'widgets' ) return;
		AdminUI::modal( [
			'id'				=> "drplus-icon-picker-modal",
			'title'				=> esc_html__( "Select your icon", 'drplus' ),
			'classes'			=> ['icon-picker-modal'],
			'submit_btn_text'	=> esc_html__( "Select icon", 'drplus' ),
		] );
	}

	public static function hospital_variables() {
		return [
			'name'		=> esc_html__( "Hospital name", 'drplus' ),
			'province'	=> esc_html__( "Province", 'drplus' ),
			'city'		=> esc_html__( "City", 'drplus' ),
		];
	}
}
add_action( 'admin_enqueue_scripts', [WidgetsPage::class, 'enqueue'] );
add_action( 'admin_footer', [WidgetsPage::class, 'modal'] );