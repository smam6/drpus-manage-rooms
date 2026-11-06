<?php
namespace MJ\Changelog;

abstract class AbstractChangelog {
	protected static string $module_id = 'changelog';
	protected static string $module_version = '1.0.0.0';

	static string $name;
	static bool $dev_mode;
	static string $current_version;
	static string $dir;
	static string $menu_parent;
	static string $menu_slug;
	static string $logo_url;
	static string $css_url;
	static string $js_file; // Without .min.js or .js
	static string $rtl_page;
	static string $last_updated_version_option_name;
	static string $last_showed_changelog_option_name;

	abstract static function i18n() : array;

	public static function get_module_version() {
		return self::$module_version;
	}

	public static function add_menu() {
		add_submenu_page(
			static::$menu_parent,			// $parent_slug:string,
			static::i18n()['page_title'],	// $page_title:string,
			static::i18n()['menu_title'],	// $menu_title:string,
			'manage_options',				// $capability:string,
			static::$menu_slug,				// $menu_slug:string,
			[static::class, 'view'],		// $callback:callable,
			999								// $position:integer|float|null
		);
	}

	public static function enqueue() {
		if( empty( $_GET['page'] ) || $_GET['page'] != static::$menu_slug ) return;

		wp_enqueue_style( 'mj-changelog', static::$css_url, [], self::$module_version );
		if( static::$dev_mode ) {
			wp_enqueue_script( 'mj-changelog', static::$js_file . ".js", ['jquery'], self::$module_version, true );
		} else {
			wp_enqueue_script( 'mj-changelog', static::$js_file . ".min.js", ['jquery'], self::$module_version, true );
		}
	}

	public static function view() {
		$changelogs = [];
		foreach( glob( static::$dir . "*.json" ) as $changelog_file ) {
			$version = str_replace( ['V', '.json'], '', wp_basename( $changelog_file ) );
			$version = str_replace( '_', '.', $version );
			$changelogs[$version] = wp_json_file_decode( $changelog_file, ['associative' => true] );
		}
		$changelogs = array_reverse( $changelogs );
		wp_localize_script( 'mj-changelog', 'changelogItems', $changelogs );
		
		$active_version = array_key_first( $changelogs );
		?>
		<div class="wrap">
			<h1 class="page-title"><?php echo esc_html( get_admin_page_title() ) ?></h1>
			<hr>
			<div id="changelogs-wrap">
				<div id="changelogs-sidebar">
					<div class="changelogs-box" id="changelogs-sidebar-main">
						<div id="changelogs-sidebar-info">
							<a href="<?php echo static::$rtl_page ?>" target="_blank"><img src="<?php echo static::$logo_url ?>" alt=""></a>
							<div id="changelogs-sidebar-version"><?php printf( static::i18n()['current_version'], static::$current_version ) ?></div>
						</div>

						<div id="changelogs-sidebar-versions">
							<?php foreach( $changelogs as $version => $version_data ) { ?>
								<div class="changelogs-sidebar-version<?php echo $version == $active_version ? ' active' : '' ?>" data-version="<?php echo $version ?>">
									<div class="changelogs-sidebar-version-label"><?php echo $version ?></div>
									<div class="changelogs-sidebar-version-time"><?php echo date_i18n( "Y-m-d", $version_data['time'] ) ?></div>
								</div>
							<?php } ?>
						</div>
					</div>

					<div class="changelogs-box" id="changelogs-sidebar-support">
						<div id="changelogs-sidebar-support-stars">
							<?php for( $index = 1; $index <= 5; $index++ ) { ?>
								<svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg"><path d="M449.536 149.082c18.01-55.476 96.557-55.476 114.568 0L634.64 366.17c8.07 24.817 31.2 41.623 57.283 41.623h228.23c58.37 0 82.644 74.632 35.42 108.966L770.89 650.9c-21.08 15.36-29.936 42.526-21.864 67.343L819.56 935.27c18.014 55.537-45.474 101.678-92.7 67.404L542.24 868.472c-21.143-15.3-49.694-15.3-70.837 0l-184.62 134.205c-47.223 34.274-110.71-11.867-92.7-67.404l70.535-217.027c8.07-24.817-.783-51.983-21.866-67.343L58.067 516.76c-47.165-34.335-22.95-108.967 35.417-108.967h228.232c26.082 0 49.212-16.806 57.284-41.623l70.538-217.088z"/></svg>
							<?php } ?>
						</div>
						<a href="https://www.rtl-theme.com/dashboard/#/downloads" target="_blank" class="button button-primary"><svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg"><path d="M449.536 149.082c18.01-55.476 96.557-55.476 114.568 0L634.64 366.17c8.07 24.817 31.2 41.623 57.283 41.623h228.23c58.37 0 82.644 74.632 35.42 108.966L770.89 650.9c-21.08 15.36-29.936 42.526-21.864 67.343L819.56 935.27c18.014 55.537-45.474 101.678-92.7 67.404L542.24 868.472c-21.143-15.3-49.694-15.3-70.837 0l-184.62 134.205c-47.223 34.274-110.71-11.867-92.7-67.404l70.535-217.027c8.07-24.817-.783-51.983-21.866-67.343L58.067 516.76c-47.165-34.335-22.95-108.967 35.417-108.967h228.232c26.082 0 49.212-16.806 57.284-41.623l70.538-217.088z"/></svg><?php echo static::i18n()['submit_score'] ?></a>
					</div>
				</div>

				<div class="changelogs-box" id="changelogs-content">
					<div id="changelogs-version"><?php echo $active_version ?></div>
					<div id="changelogs-time"><?php echo date_i18n( "Y-m-d", $changelogs[$active_version]['time'] ) ?></div>

					<div id="changelogs-items">
						<?php foreach( $changelogs[$active_version]['log'] as $item ) { ?>
							<div class="changelogs-item"><?php echo $item ?></div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public static function notice() {
		$last_updated_version = get_option( static::$last_updated_version_option_name, '' );
		$fresh_install = false;
		if( $last_updated_version !== '' ) {
			$last_showed_changelog = get_option( static::$last_showed_changelog_option_name, '1.0.0.0' );
			$should_show = version_compare( $last_updated_version, $last_showed_changelog ) !== 0;
		} else {
			$last_showed_changelog = '1.0.0.0';
			$fresh_install = true;
			$should_show = true;
		}
		if( !$should_show || $fresh_install ) return;

		$versions_files = [];
		if( $fresh_install ) {
			$version_filename = "V" . str_replace( '.', '_', static::$current_version ) . ".json";
			$changelog_file = static::$dir . $version_filename;
			if( !file_exists( $changelog_file ) ) return;
			$versions_files[static::$current_version] = $changelog_file;
		} else {
			foreach( glob( static::$dir . "*.json" ) as $json_file ) {
				$filename = pathinfo( $json_file, PATHINFO_FILENAME );
				$version = strtr( $filename, [
					'_'	=> '.',
					'V'	=> '',
				] );
				if( version_compare( $version, $last_showed_changelog, '>' ) && version_compare( $version, static::$current_version, '<=' ) ) {
					$versions_files[$version] = static::$dir . "{$filename}.json";
				}
			}
		}

		foreach( $versions_files as $version => $changelog_file ) {
			$changelog = wp_json_file_decode( $changelog_file, ['associative' => true] )['log'];
			?>
			<div class="<?php echo static::$name ?>-update-notice notice notice-success is-dismissible">
				<p><strong><?php printf( static::i18n()['update_successful'], $version ) ?></strong></p>
				<div class="<?php echo static::$name ?>-update-notice-content">
					<ul>
						<?php foreach( $changelog as $item ) { ?>
							<li><?php echo $item ?></li>
						<?php } ?>
					</ul>

					<a href="<?php echo static::$rtl_page ?>" target="_blank"><img src="<?php echo static::$logo_url ?>" alt=""></a>
				</div>
				<p>
					<a href="<?php echo admin_url( 'admin.php?page=' . static::$menu_slug ) ?>" class="button" target="_blank" title="<?php echo esc_attr( static::i18n()['show_changelogs'] ) ?>"><?php echo esc_html( static::i18n()['show_changelogs'] ) ?></a>
				</p>
			</div>
			<?php
		}
		update_option( static::$last_showed_changelog_option_name, static::$current_version, false );
	}

	public static function init() {
		add_action( 'admin_menu', [static::class, 'add_menu'], 99 );
		add_action( 'admin_enqueue_scripts', [static::class, 'enqueue'] );
		add_action( 'admin_notices', [static::class, 'notice'] );
	}
}