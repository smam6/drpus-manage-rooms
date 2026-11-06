<?php
namespace Drplus\Changelog;

use MJ\Changelog\AbstractChangelog;

class Changelog extends AbstractChangelog {
	static string $name = 'drplus';
	static bool $dev_mode = DRPLUS_DEV;
	static string $current_version = DRPLUS_VERSION;
	static string $dir = DRPLUS_DIR . "inc/Changelogs/";
	static string $menu_parent = 'drplus';
	static string $menu_slug = 'drplus-changelogs';
	static string $logo_url = DRPLUS_URI . "assets/images/logo.svg";
	static string $css_url = DRPLUS_URI . "inc/Libs/vendor/mjkhajeh/changelog/assets/css/changelog.min.css";
	static string $js_file = DRPLUS_URI . "inc/Libs/vendor/mjkhajeh/changelog/assets/js/changelog"; // Don't use .js or .min.js
	static string $rtl_page = 'https://www.rtl-theme.com/dr-plus-wordpress-theme/';
	static string $last_updated_version_option_name = 'drplus_last_updated_version';
	static string $last_showed_changelog_option_name = 'drplus_last_showed_changelog';

	public static function i18n() : array {
		return [
			'page_title'		=> __( 'Changelogs', 'drplus' ),
			'menu_title'		=> __( 'Changelogs', 'drplus' ),
			'current_version'	=> __( "Current version: %s", 'drplus' ),
			'submit_score'		=> __( 'Submit your score', 'drplus' ),
			'update_successful'	=> __( 'DoctorPlus has been successfully updated. View the changelog for version %s:', 'drplus' ),
			'show_changelogs'	=> __( 'Show more changelogs', 'drplus' ),
		];
	}
}
Changelog::init();