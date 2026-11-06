# MJ Changelog

A lightweight WordPress admin changelog viewer for plugins and themes.

`MJ\Changelog\AbstractChangelog` lets you add a dedicated changelog page to the WordPress admin area and show update notices after a product version changes. Changelog entries are stored as simple JSON files, so releases can be documented without creating custom database tables.

## Features

- Adds a WordPress admin submenu page for product changelogs.
- Reads changelog data from versioned JSON files.
- Displays versions in a sidebar and changelog items in the main panel.
- Shows an admin update notice for new versions.
- Supports custom labels through WordPress translation functions.
- Includes ready-to-use CSS and JavaScript assets.
- Works with plugins, themes, and other WordPress modules.

## Requirements

- PHP 7.4 or higher.
- WordPress with admin access.
- jQuery, provided by WordPress admin by default.

## Installation

### Composer

After the package is published on Packagist, install it with Composer:

```bash
composer require mj/changelog
```

Then load Composer's autoloader in your plugin or theme:

```php
require_once __DIR__ . '/vendor/autoload.php';
```

Replace `mj/changelog` with the final package name used on Packagist.

### Manual Installation

You can also copy these files into your plugin or theme:

```text
AbstractChangelog.php
assets/css/changelog.min.css
assets/js/changelog.js
```

Then include the abstract class manually:

```php
require_once __DIR__ . '/path/to/AbstractChangelog.php';
```

## Basic Usage

Create a class that extends `MJ\Changelog\AbstractChangelog`, configure the static properties, return translated labels from `i18n()`, and call `init()`.

```php
<?php

use MJ\Changelog\AbstractChangelog;

class Changelog extends AbstractChangelog {
	static string $name = 'example_changelog';
	static bool $dev_mode = true;
	static string $current_version = '1.2.0.0';
	static string $dir = __DIR__ . '/example/';
	static string $menu_parent = 'options-general.php';
	static string $menu_slug = 'changelogs';
	static string $logo_url = plugin_dir_url( __FILE__ ) . 'assets/img/logo.svg';
	static string $css_url = plugin_dir_url( __FILE__ ) . 'libs/vendor/mjkhajeh/changelog/assets/css/changelog.min.css';
	static string $js_file = plugin_dir_url( __FILE__ ) . 'libs/vendor/mjkhajeh/changelog/assets/js/changelog';
	static string $rtl_page = 'https://www.rtl-theme.com';
	static string $last_updated_version_option_name = 'project_last_updated_version';
	static string $last_showed_changelog_option_name = 'project_last_showed_changelog';

	public static function i18n() : array {
		return [
			'page_title'        => __( 'Changelogs', 'textdomain' ),
			'menu_title'        => __( 'Changelogs', 'textdomain' ),
			'current_version'   => __( 'Current version: %s', 'textdomain' ),
			'submit_score'      => __( 'Submit your score', 'textdomain' ),
			'update_successful' => __( 'Project has been successfully updated. View the changelog for version %s:', 'textdomain' ),
			'show_changelogs'   => __( 'Show more changelogs', 'textdomain' ),
		];
	}
}

Changelog::init();
```

The repository also includes a working example in `example/example.php`.

## Changelog JSON Files

Each release needs a JSON file inside the directory configured by `static::$dir`.

### File Name Format

Use this format:

```text
V{VERSION_WITH_UNDERSCORES}.json
```

Examples:

```text
V1_0_0_0.json
V1_1_0_0.json
V1_2_0_0.json
```

The class converts file names like `V1_2_0_0.json` to version `1.2.0.0`.

### File Content Format

```json
{
	"time": 1733529600,
	"log": [
		"First item",
		"Second item"
	]
}
```

Fields:

- `time`: Unix timestamp for the release date.
- `log`: Array of changelog items shown in the admin UI and update notice.

The included example file is available at `example/V1_2_0_0.json`.

## Configuration

Your child class must define these static properties:

| Property | Description |
| --- | --- |
| `$name` | Unique name used in generated notice CSS classes. |
| `$dev_mode` | When `true`, loads `{$js_file}.js`; when `false`, loads `{$js_file}.min.js`. |
| `$current_version` | Current product version. |
| `$dir` | Directory path where changelog JSON files are stored. |
| `$menu_parent` | WordPress admin parent menu slug, such as `options-general.php`. |
| `$menu_slug` | Slug for the changelog submenu page. |
| `$logo_url` | Logo URL displayed in the sidebar and update notice. |
| `$css_url` | URL to `changelog.min.css`. |
| `$js_file` | URL/path to the changelog script without `.js` or `.min.js`. |
| `$rtl_page` | Product page URL used by the logo and rating button. |
| `$last_updated_version_option_name` | WordPress option that stores the latest updated version. |
| `$last_showed_changelog_option_name` | WordPress option that stores the latest version already shown to the user. |

## How Update Notices Work

`AbstractChangelog::notice()` compares two WordPress options:

- `$last_updated_version_option_name`
- `$last_showed_changelog_option_name`

If WordPress has a newer updated version than the last shown changelog version, the class loads all matching JSON changelog files and displays an admin notice.

After displaying notices, it updates `$last_showed_changelog_option_name` to `$current_version`.

You should update `$last_updated_version_option_name` from your own plugin or theme update logic when your product is upgraded.

Example:

```php
update_option( 'project_last_updated_version', '1.2.0.0', false );
```

## Registered WordPress Hooks

Calling `Changelog::init()` registers these hooks:

| Hook | Method | Purpose |
| --- | --- | --- |
| `admin_menu` | `add_menu()` | Adds the changelog submenu page. |
| `admin_enqueue_scripts` | `enqueue()` | Loads CSS and JavaScript only on the changelog page. |
| `admin_notices` | `notice()` | Shows update notices when needed. |

## Assets

Included assets:

```text
assets/scss/changelog.scss
assets/css/changelog.min.css
assets/css/changelog.min.css.map
assets/js/changelog.js
```

If `$dev_mode` is `false`, the class tries to load a minified JavaScript file named:

```text
assets/js/changelog.min.js
```

If you use production mode, create this minified file or change the enqueue logic to match your build process.

## Notes

- The example directory in this repository is named `example`.
- Changelog item HTML is currently printed directly in the admin page and notice output. Only use trusted changelog content, or escape/sanitize items before output if changelog files can be edited by untrusted users.
- The admin page is available only to users with the `manage_options` capability.

## License

This project is licensed under the MIT License. See the LICENSE file or [MIT License](https://opensource.org/licenses/MIT) for details.
