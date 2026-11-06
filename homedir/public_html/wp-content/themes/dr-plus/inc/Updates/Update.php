<?php
namespace DrPlus\Updates;

use DrPlus\Utils;

class Update {
	private static $TYPE = 'theme'; // theme | plugin
	private static $NAME = 'drplus'; // Name at the sheyda server
	private static $VERSION = DRPLUS_VERSION; // Current version of the project
	private static $LAST_UPDATE_OPTION = 'drplus_last_updated_version'; // Option name to store last updated version
	private static $DIR = DRPLUS_DIR; // DIR path for list of updates file
	private static $UPDATE_DIR = DRPLUS_DIR . "inc/Updates"; // DIR path for list of updates file
	private static $UPDATE_CLASS = "DrPlus\Updates\V"; // Prefixed class to execute the updates file
	private static $UPDATE_NOTICE_OPTION = 'drplus_update_notice'; // Option name for show update notice of the product
	private static $UPDATE_LOCK_OPTION = 'drplus_update_lock';
	private static $DEV_MODE = DRPLUS_DEV;
	private static $DID_CHECK_UPDATE = false;
	private static $cached_update_response = null;
	
	// Only for plugin
	private static $PLUGIN_FILE = '';

	// Settings
	private static $request_args = [
		'timeout'				=> 15,
		'httpversion'			=> '1.1',
		'redirection'			=> 3,
		'sslverify'				=> true,
		'reject_unsafe_urls'	=> true,
		'limit_response_size'	=> 1048576,
		'headers'				=> [
			'Accept' => 'application/json',
		],
	];

	private static function texts() {
		return [
			'update_notice'		=> __( "A new version of the <strong>%s</strong> is available.", 'drplus' ),
			'installed_version'	=> esc_html__( "Your installed version", 'drplus' ),
			'new_version'		=> esc_html__( "Latest published version", 'drplus' ),
			'see_update'		=> esc_html__( "See updates", 'drplus' ),
			'download'			=> esc_html__( "Direct download new version", 'drplus' ),
		];
	}

	public static function execute() {
		$last_updated_version = get_option( self::$LAST_UPDATE_OPTION, '' );
		$fresh_install = $last_updated_version === '';
		if( !$fresh_install && !version_compare( self::$VERSION, $last_updated_version, '>' ) ) {
			return;
		}

		$lock_time = (int)get_option( self::$UPDATE_LOCK_OPTION, 0 );
		if( $lock_time > time() - 300 ) {
			return;
		}
		if( $lock_time > 0 ) {
			delete_option( self::$UPDATE_LOCK_OPTION );
		}
		if( !add_option( self::$UPDATE_LOCK_OPTION, time(), '', false ) ) {
			return;
		}

		try {
			self::run_updates( $last_updated_version, $fresh_install );
		} finally {
			delete_option( self::$UPDATE_LOCK_OPTION );
		}
	}

	private static function run_updates( $last_updated_version, $fresh_install ) {
		if( $fresh_install ) {
			$last_updated_version = '1.0.0.0';
		}

		if( !is_dir( self::$UPDATE_DIR ) || !is_readable( self::$UPDATE_DIR ) ) {
			error_log( sprintf( 'Sheyda updater: update directory is not readable: %s', self::$UPDATE_DIR ) );
			return;
		}

		$update_files = glob( self::$UPDATE_DIR . '/V*.php' );
		if( $update_files === false ) {
			error_log( 'Sheyda updater: failed to read update files.' );
			return;
		}

		$versions = [];
		foreach( $update_files as $filename ) {
			$basename = basename( $filename );
			if( !preg_match( '/^V([0-9]+(?:_[0-9]+)*)\.php$/', $basename, $matches ) ) {
				continue;
			}

			$version = str_replace( '_', '.', $matches[1] );
			if(
				version_compare( $version, $last_updated_version, '>' )
				&& version_compare( $version, self::$VERSION, '<=' )
			) {
				$versions[$version] = $filename;
			}
		}

		uksort( $versions, 'version_compare' );

		foreach( $versions as $version => $filename ) {
			$version_filename = str_replace( '.', '_', $version );
			$class = self::$UPDATE_CLASS . $version_filename;

			try {
				require_once( $filename );
				if( !class_exists( $class, false ) || !is_callable( [$class, 'update'] ) ) {
					throw new \RuntimeException( sprintf( 'Invalid update handler: %s', $class ) );
				}

				$run_at_install = property_exists( $class, 'run_at_install' ) && !empty( $class::$run_at_install );
				if( !$fresh_install || $run_at_install ) {
					$class::update();
				}

				update_option( self::$LAST_UPDATE_OPTION, $version, true );
			} catch( \Throwable $throwable ) {
				error_log(
					sprintf(
						'Sheyda updater failed at version %s: %s',
						$version,
						$throwable->getMessage()
					)
				);
				return;
			}
		}

		update_option( self::$LAST_UPDATE_OPTION, self::$VERSION, true );
	}

	public static function enqueue( $hook ) {
		if( $hook != 'update-core.php' ) return;
		?>
		<style>
			#TB_window.plugin-details-modal:has(iframe[src*="sheyda"]) {
				border-radius: 32px;
				overflow: hidden;
			}
		</style>
		<?php
	}

	private static function get_update_response() {
		if( self::$DID_CHECK_UPDATE ) {
			return self::$cached_update_response;
		}

		self::$DID_CHECK_UPDATE = true;

		$endpoint = add_query_arg(
			[
				'product' => self::$NAME,
				'version' => self::$VERSION,
			],
			'/sheyda/v2/product/check_version'
		);
		$hosts = [
			'https://sheydateam.ir/api',
			'https://sheydateam.com/api',
		];
		$request_args = self::$request_args;

		if( self::$DEV_MODE ) {
			$hosts = ['http://localhost/sheyda/api'];
			$request_args['reject_unsafe_urls'] = false;
		}

		$no_update_response = [
			'need_update'	=> false,
			'file'			=> '',
			'version'		=> '',
			'rtl_url'		=> '',
			'icon'			=> '',
		];
		
		foreach( $hosts as $host ) {
			$url = $host . $endpoint;
			$request = wp_remote_get( $url, $request_args );
			
			if ( is_wp_error( $request ) ) {
				continue;
			}
			
			$response_code = wp_remote_retrieve_response_code( $request );
			if ( $response_code !== 200 ) {
				continue;
			}
			
			$response = json_decode( wp_remote_retrieve_body( $request ), true );
			
			if(
				!is_array( $response )
				|| !array_key_exists( 'need_update', $response )
				|| !Utils::to_bool( $response['need_update'] )
			) {
				continue;
			}

			$need_update = Utils::to_bool( $response['need_update'] );
			if( !$need_update ) {
				self::$cached_update_response = $no_update_response;
				return self::$cached_update_response;
			}

			$version = isset( $response['version'] ) && is_scalar( $response['version'] )
				? sanitize_text_field( (string)$response['version'] )
				: '';
			$file = self::validate_update_url( $response['file'] ?? '', true );

			if(
				!preg_match( '/^[0-9]+(?:\.[0-9]+)*(?:[-+][0-9A-Za-z.-]+)?$/', $version )
				|| !version_compare( $version, self::$VERSION, '>' )
				|| empty( $file )
			) {
				continue;
			}

			self::$cached_update_response = [
				'need_update'	=> true,
				'file'			=> $file,
				'version'		=> $version,
				'rtl_url'		=> self::validate_update_url( $response['rtl_url'] ?? '' ),
				'icon'			=> self::validate_update_url( $response['icon'] ?? '' ),
			];
			return self::$cached_update_response;
		}

		return null;
	}

	private static function validate_update_url( $url, $package = false ) {
		if( !is_scalar( $url ) ) return '';

		$url = esc_url_raw( (string)$url, ['http', 'https'] );
		if( empty( $url ) ) return '';

		$parts = wp_parse_url( $url );
		if( empty( $parts['scheme'] ) || empty( $parts['host'] ) ) return '';

		if( self::$DEV_MODE ) {
			return strtolower( $parts['host'] ) === 'localhost' ? $url : '';
		}

		if( strtolower( $parts['scheme'] ) !== 'https' ) return '';

		if( $package && !wp_http_validate_url( $url ) ) return '';

		return $url;
	}

	private static function get_plugin_filepath() {
		return basename( untrailingslashit( self::$DIR ) ) . '/' . self::$PLUGIN_FILE;
	}

	private static function get_plugin_data() {
		$plugins = get_plugins();
		$plugin_filepath = self::get_plugin_filepath();

		return $plugins[$plugin_filepath] ?? [];
	}

	private static function get_changelog_url() {
		return self::$DEV_MODE
			? 'http://localhost/sheyda/?changelog=' . rawurlencode( self::$NAME )
			: 'https://sheydateam.ir/?changelog=' . rawurlencode( self::$NAME );
	}

	public static function theme_update_checker( $update_themes ) {
		if( self::$TYPE != 'theme' ) return $update_themes;

		$update_response = self::get_update_response();
		if( $update_response === null ) return $update_themes;

		if( !is_object( $update_themes ) ) {
			$update_themes = new \stdClass();
		}
		if( !isset( $update_themes->response ) || !is_array( $update_themes->response ) ) {
			$update_themes->response = [];
		}

		$theme = wp_get_theme( get_template() );
		$stylesheet = $theme->get_stylesheet();

		if( $update_response['need_update'] ) {
			$update_themes->response[$stylesheet] = [
				'id'				=> $update_response['file'],
				'theme'				=> $theme->get( 'Name' ),
				'new_version'		=> $update_response['version'],
				'url'				=> $update_response['rtl_url'],
				'package'			=> $update_response['file'],
				'theme_data'		=> $theme,
				'theme_stylesheet'	=> $stylesheet,
			];
			update_option( self::$UPDATE_NOTICE_OPTION, $update_response, false );
		} else {
			unset( $update_themes->response[$stylesheet] );
			delete_option( self::$UPDATE_NOTICE_OPTION );
		}

		return $update_themes;
	}

	public static function plugin_update_checker( $update_plugins ) {
		if( self::$TYPE != 'plugin' ) return $update_plugins;

		$update_response = self::get_update_response();
		if( $update_response === null ) return $update_plugins;

		if( !is_object( $update_plugins ) ) {
			$update_plugins = new \stdClass();
		}
		if( !isset( $update_plugins->response ) || !is_array( $update_plugins->response ) ) {
			$update_plugins->response = [];
		}

		$plugin_filepath = self::get_plugin_filepath();

		if( $update_response['need_update'] ) {
			$plugin_update_info = new \stdClass();
			$plugin_update_info->id = $update_response['file'];
			$plugin_update_info->slug = self::get_changelog_url();
			$plugin_update_info->plugin = $plugin_filepath;
			$plugin_update_info->new_version = $update_response['version'];
			$plugin_update_info->url = $update_response['rtl_url'];
			$plugin_update_info->package = $update_response['file'];
			$plugin_update_info->plugin_data = self::get_plugin_data();
			$plugin_update_info->plugin_file = $plugin_filepath;
			$plugin_update_info->icons = [
				'default' => $update_response['icon'],
			];

			$update_plugins->response[$plugin_filepath] = $plugin_update_info;
			update_option( self::$UPDATE_NOTICE_OPTION, $update_response, false );
		} else {
			unset( $update_plugins->response[$plugin_filepath] );
			delete_option( self::$UPDATE_NOTICE_OPTION );
		}

		return $update_plugins;
	}

	public static function update_notice() {
		if ( ( self::$TYPE == 'plugin' && !current_user_can( 'update_plugins' ) ) || ( self::$TYPE == 'theme' && !current_user_can( 'update_themes' ) ) ) {
			return;
		}

		$screen = get_current_screen();
		if( $screen && $screen->id == 'update-core' ) return;
		
		$update_response = get_option( self::$UPDATE_NOTICE_OPTION, [] );
		if( empty( $update_response['version'] ) || empty( $update_response['file'] ) ) return;

		if ( version_compare( self::$VERSION, $update_response['version'], '>=' ) ) {
			delete_option( self::$UPDATE_NOTICE_OPTION );
			return;
		}

		if( self::$TYPE == 'theme' ) {
			$theme = wp_get_theme( get_template() );
			$text_domain = $theme->get( 'TextDomain' );
			$name = __( $theme->get( 'Name' ), $text_domain );
		} else {
			$plugins = get_plugins();
			$plugin_filepath = basename( self::$DIR ) . "/" . self::$PLUGIN_FILE;
			$plugin = $plugins[$plugin_filepath];
			$text_domain = $plugin['TextDomain'];
			$name = __( $plugin['Name'], $text_domain );
		}

		$texts = self::texts();

		$table_id = self::$TYPE == 'plugin' ? "update-plugins-table" : "update-themes-table";

		$message = sprintf( $texts['update_notice'], esc_html( $name ) );
		$message .= '<br><strong>' . $texts['installed_version'] . ':</strong> ' . esc_html( self::$VERSION );
		$message .= '<br><strong>' . $texts['new_version'] . ':</strong> ' . esc_html( $update_response['version'] );
		$message .= '<br><a href="' . esc_url( admin_url( 'update-core.php' ) ) . '#' . $table_id . '" target="_blank" rel="noopener noreferrer" class="button button-primary">' . $texts['see_update'] . '</a>';
		$message .= '<br><a href="' . esc_url( $update_response['file'] ) . '" class="button" download>' . $texts['download'] . '</a><br>';

		wp_admin_notice( $message, [
			'type'			=> 'warning',
			'dismissible'	=> false,
			'id'			=> self::$UPDATE_NOTICE_OPTION,
		] );
	}

	public static function plugins_api( $result, $action, $args ) {
		if( $action !== 'plugin_information' || empty( $args->slug ) ) return $result;

		$changelog_url = self::get_changelog_url();
		if( esc_url_raw( $args->slug ) !== esc_url_raw( $changelog_url ) ) return $result;

		$request_args = self::$request_args;
		$request_args['headers']['Accept'] = 'text/html';
		if( self::$DEV_MODE ) {
			$request_args['reject_unsafe_urls'] = false;
		}

		$request = wp_remote_get( $changelog_url, $request_args );
		if( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) !== 200 ) return $result;

		$html = wp_remote_retrieve_body( $request );
		if( $html === '' ) return $result;

		$plugin = self::get_plugin_data();
		$update_response = get_option( self::$UPDATE_NOTICE_OPTION, [] );
		$plugin_information = new \stdClass();
		$plugin_information->name = $plugin['Name'] ?? self::$NAME;
		$plugin_information->slug = $args->slug;
		$plugin_information->version = $update_response['version'] ?? self::$VERSION;
		$plugin_information->author = wp_kses_post( $plugin['Author'] ?? '' );
		$plugin_information->homepage = esc_url_raw( $plugin['PluginURI'] ?? $changelog_url );
		$plugin_information->download_link = self::validate_update_url( $update_response['file'] ?? '', true );
		$plugin_information->sections = [
			'changelog' => $html,
		];

		return $plugin_information;
	}

	public static function after_update( $upgrader, $options ) {
		if ( ! isset( $options['type'], $options['action'] ) || $options['action'] !== 'update' ) return;

		$update_response = get_option( self::$UPDATE_NOTICE_OPTION, [] );
		if ( empty( $update_response['version'] ) ) return;

		if ( self::$TYPE === 'theme' && $options['type'] === 'theme' ) {
			$themes = (array) ( $options['themes'] ?? [] );
			if ( !empty( $options['theme'] ) ) {
				$themes[] = $options['theme'];
			}
			$parent_theme_slug = get_template();
			if ( ! in_array( $parent_theme_slug, $themes, true ) ) return;

			$parent_theme = wp_get_theme( $parent_theme_slug );
			$installed_version = $parent_theme->get( 'Version' );

			if ( version_compare( $installed_version, $update_response['version'], '>=' ) ) {
				delete_option( self::$UPDATE_NOTICE_OPTION );
			}

		} elseif ( self::$TYPE === 'plugin' && $options['type'] === 'plugin' ) {
			$plugins = (array) ( $options['plugins'] ?? [] );
			if ( !empty( $options['plugin'] ) ) {
				$plugins[] = $options['plugin'];
			}
			$plugin_filepath = self::get_plugin_filepath();
			if ( ! in_array( $plugin_filepath, $plugins, true ) ) return;

			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_filepath, false, false );
			$installed_version = $plugin_data['Version'] ?? '';

			if ( ! empty( $installed_version ) && version_compare( $installed_version, $update_response['version'], '>=' ) ) {
				delete_option( self::$UPDATE_NOTICE_OPTION );
			}
		}
	}

	public static function after_theme_switch( $new_name, $new_theme ) {
		if ( self::$TYPE !== 'theme' ) return;

		$parent_theme = wp_get_theme( get_template() );
		$matches = strtolower( $new_theme->get_stylesheet() ) === strtolower( self::$NAME )
				|| strtolower( $new_theme->get_template() )  === strtolower( self::$NAME );

		if ( ! $matches ) return;

		$installed_version = $parent_theme->get( 'Version' );

		$update_response = get_option( self::$UPDATE_NOTICE_OPTION, [] );
		if ( empty( $update_response['version'] ) ) return;

		if ( version_compare( $installed_version, $update_response['version'], '>=' ) ) {
			delete_option( self::$UPDATE_NOTICE_OPTION );
		}
	}
}
Update::execute();
add_action( 'admin_enqueue_scripts', [Update::class, 'enqueue'] );
add_filter( 'pre_set_site_transient_update_themes', [Update::class, 'theme_update_checker'], 998 );
add_filter( 'pre_set_site_transient_update_plugins', [Update::class, 'plugin_update_checker'], 998 );
add_action( 'admin_notices', [Update::class, 'update_notice'] );
add_filter( 'plugins_api', [Update::class, 'plugins_api'], 10, 3 );
add_action( 'upgrader_process_complete', [Update::class, 'after_update'], 999, 2 );
add_action( 'switch_theme', [Update::class, 'after_theme_switch'], 999, 2 );