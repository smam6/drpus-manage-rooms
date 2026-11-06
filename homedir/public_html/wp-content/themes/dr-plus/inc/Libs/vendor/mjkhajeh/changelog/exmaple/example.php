<?php
use MJ\Changelog\AbstractChangelog;

include_once( dirname( __FILE__ ) . "/AbstractChangelog.php" );

class Changelog extends AbstractChangelog {
	static string $name = 'example_changelog';
	static bool $dev_mode = true;
	static string $current_version = "1.2.0.0";
	static string $dir = "example/";
	static string $menu_parent = 'options-general.php';
	static string $menu_slug = 'changelogs';
	static string $logo_url = "assets/img/logo.svg";
	static string $css_url = "assets/css/changelog.min.css";
	static string $js_file = "assets/js/changelog"; // Don't use .js or .min.js
	static string $rtl_page = 'https://www.rtl-theme.com';
	static string $last_updated_version_option_name = 'project_last_updated_version';
	static string $last_showed_changelog_option_name = 'project_last_showed_changelog';

	public static function i18n() : array {
		return [
			'page_title'		=> __( 'Changelogs', 'textdomain' ),
			'menu_title'		=> __( 'Changelogs', 'textdomain' ),
			'current_version'	=> __( "Current version: %s", 'textdomain' ),
			'submit_score'		=> __( 'Submit your score', 'textdomain' ),
			'update_successful'	=> __( 'Project has been successfully updated. View the changelog for version %s:', 'textdomain' ),
			'show_changelogs'	=> __( 'Show more changelogs', 'textdomain' ),
		];
	}
}
Changelog::init();
