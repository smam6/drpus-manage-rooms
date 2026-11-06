<?php
namespace DrPlus\Backend\Specialists;

use DrPlus\AdminScripts;
use DrPlus\Model\Specialists;
use DrPlus\PublicScripts;
use DrPlus\Utils;
use DrPlus\Utils\Options;

class Settings {
	protected static $PREFIX = "specialists_";
	private static $default_tab = 'all';

	public static $table;

	private static function get_active_tab() {
		$sections = array_keys( self::sections() );
		self::$default_tab = $sections[0];
		$active_tab = !empty( $_GET['tab'] ) ? Utils::convert_chars( $_GET['tab'] ) : self::$default_tab;
		return Utils::ensure_values_in_array( $active_tab, $sections, self::$default_tab );
	}

	public static function sections() {
		return [
			'view'	=> [
				'menu_title'	=> __( 'New Specialist', 'drplus' ),
				'page_title'	=> __( 'Specialist', 'drplus' ),
				'class'			=> 'SpecialistView',
			],
		];
	}
	public static function menu() {
		$parent_slug = 'edit.php?post_type=specialist';
		$menu_slug = 'specialists';

		$options = Options::get_options( [
			'insurance'	=> true,
		] );
		
		// Hidden page for Specialists editor (accessible via direct link/metabox)
		add_submenu_page(
			'', // $parent_slug:string
			__( 'Specialists', 'drplus' ), // $page_title:string
			__( 'Specialists', 'drplus' ), // $menu_title:string
			'manage_options', // $capability:string
			$menu_slug, // $menu_slug:string
			[__CLASS__, 'view'], // $callback:callable
			30
		);

		// No submenu entries for view tabs; page remains accessible directly.

		if( $options['insurance'] ) {
			$insurance_taxonomy = get_taxonomy( 'insurance' );
			if( $insurance_taxonomy ) {
				add_submenu_page(
					$parent_slug, // $parent_slug:string
					$insurance_taxonomy->labels->name, // $page_title:string
					$insurance_taxonomy->labels->name, // $menu_title:string
					$insurance_taxonomy->cap->manage_terms, // $capability:string
					"edit-tags.php?taxonomy=insurance" // $menu_slug:string
				);
			}
		}

		$identity_type_taxonomy = get_taxonomy( 'identity_type' );
		if( $identity_type_taxonomy ) {
			add_submenu_page(
				$parent_slug, // $parent_slug:string
				$identity_type_taxonomy->labels->name, // $page_title:string
				$identity_type_taxonomy->labels->name, // $menu_title:string
				$identity_type_taxonomy->cap->manage_terms, // $capability:string
				"edit-tags.php?taxonomy=identity_type" // $menu_slug:string
			);
		}

		add_submenu_page(
			$parent_slug, // $parent_slug:string
			__( "Specialists settings", 'drplus' ), // $page_title:string
			__( "Specialists settings", 'drplus' ), // $menu_title:string
			'manage_options', // $capability:string
			"admin.php?page=drplus&tab=80" // $menu_slug:string
		);
	}

	public static function change_active_menu( $parent_file ) {
		if( $parent_file == 'edit.php' && !empty( $_GET['taxonomy'] ) ) {
			$specialist_taxonomies = ['insurance', 'identity_type'];
			if( in_array( $_GET['taxonomy'], $specialist_taxonomies ) ) {
				$parent_file = 'edit.php?post_type=specialist';
			}
		}
		if( !empty( $_GET['page'] ) && $_GET['page'] == 'specialists' ) {
			$parent_file = 'edit.php?post_type=specialist';
		}
		return $parent_file;
	}

	public static function change_active_submenu( $submenu_file, $parent_file ) {
		if( !empty( $_GET['page'] ) && $_GET['page'] == 'specialists' ) {
			$submenu_file = 'edit.php?post_type=specialist';
		}
		return $submenu_file;
	}

	public static function view() {
		$sections = self::sections();
		$active_tab = self::get_active_tab();
		$page_title = '';
		if( $active_tab == self::$default_tab ) {
			$page_title = get_admin_page_title();
		} else {
			$page_title = $sections[$active_tab]['page_title'];
		}
		?>
		<div class="wrap">
			<div id="<?php echo self::$PREFIX ?>header">
				<h1 class="wp-heading-inline"><?php echo esc_html( $page_title ) ?></h1>
				<a href="<?php echo add_query_arg( ['page' => 'specialists','tab' => 'view'], admin_url( 'admin.php' ) ) ?>" class="page-title-action"><?php echo __( 'Add new specialist', 'drplus' ) ?></a>
				<hr class="wp-header-end">
			</div>
			<div id="<?php echo self::$PREFIX ?>container">
				<?php settings_errors( "drplus-specialists-settings" ); ?>
				<div id="<?php echo self::$PREFIX ?>content-wrap">
					<div id="<?php echo self::$PREFIX ?>content">
						<?php
						if( file_exists( DRPLUS_DIR . "inc/Backend/Specialists/class-specialists-{$active_tab}.php" ) ) {
							$active_tab_class = $sections[$active_tab]['class'];
							include_once( DRPLUS_DIR . "inc/Backend/Specialists/class-specialists-{$active_tab}.php" );
							// call view function
							$class = "DrPlus\Backend\Specialists\\" . $active_tab_class;
							$class::view();
						}
						?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public static function save() {
		if( empty( $_POST ) ) return;

		if( empty( $_GET['page'] ) || $_GET['page'] !='specialists' ) return;
		
		$sections = self::sections();
		$active_tab = self::get_active_tab();
		if( $active_tab == 'all' ) return;
		$active_tab_class = $sections[$active_tab]['class'];
		if( file_exists( DRPLUS_DIR . "inc/Backend/Specialists/class-specialists-{$active_tab}.php" ) ) {
			include_once( DRPLUS_DIR . "inc/Backend/Specialists/class-specialists-{$active_tab}.php" );
			$class = "DrPlus\Backend\Specialists\\" . $active_tab_class;
			
			// Check nonce
			if( !isset( $_POST[$class::$PREFIX . "nonce_value"] ) || !wp_verify_nonce( $_POST[$class::$PREFIX . "nonce_value"], $class::$PREFIX . "nonce" ) ) return;
			
			// call save function
			$class::save();
		}
	}

	public static function admin_enqueue() {
		$screen = get_current_screen();
		if( $screen->id !== 'admin_page_specialists' ) return;

		$active_tab = self::get_active_tab();
		if( $active_tab == 'view' ) {
			PublicScripts::localizations( ['cities'] );
			PublicScripts::select2();
			PublicScripts::pdp();
			PublicScripts::dropzone();
			PublicScripts::swapy();

			AdminScripts::tabs();
			AdminScripts::form_group();
			AdminScripts::switch();

			wp_enqueue_media();
			wp_enqueue_script( 'wp-util' );
			wp_enqueue_editor();

			if( DRPLUS_DEV ) {
				wp_enqueue_script( 'drplus-specialist-view', DRPLUS_URI . "assets/js/backend/specialists/specialist-view.js", ['jquery'], DRPLUS_VERSION, true );
			} else {
				wp_enqueue_script( 'drplus-specialist-view', DRPLUS_URI . "assets/js/backend/specialists/specialist-view.min.js", ['jquery'], DRPLUS_VERSION, true );
			}
			wp_enqueue_style( 'drplus-specialist-view', DRPLUS_URI . "assets/css/backend/specialists/specialist-view.min.css", [], DRPLUS_VERSION );
			wp_localize_script( 'drplus-specialist-view', 'drplusSpecialist', [
				'i18n'	=> [
					'selectUser'			=> __( 'Select a user', 'drplus' ),
					'selectHospitals'		=> __( 'Search & select Hospitals', 'drplus' ),
					'selectSpecialities'	=> __( 'Select specialities', 'drplus' ),
					'requiredField'			=> __( 'This field is required', 'drplus' ),
					'wrongEmail'			=> __( 'Please enter a valid email', 'drplus' ),
					'wrongIDCode'			=> __( 'Please enter a valid National ID', 'drplus' ),
					'wrongMobile'			=> __( 'Please enter a valid mobile', 'drplus' ),
					'wrongCardNumber'		=> __( 'Please enter a valid Card Number', 'drplus' ),
					'wrongShabaNumber'		=> __( 'Please enter a valid Shaba Number', 'drplus' ),
					'confirmRemoveTime'		=> __( "Are you sure?", 'drplus' ),
					'add'					=> __( 'Add', 'drplus' )
				],
				'nonces'	=> [
					'findHospital'	=> wp_create_nonce( 'drplus_find_hospital_nonce' ),
					'getUsers'		=> wp_create_nonce( 'drplus_get_users_nonce' ),
					'getUserData'	=> wp_create_nonce( 'drplus_get_user_data_nonce' ),
				]
			] );
		}
	}
}
add_action( 'admin_menu', [Settings::class, 'menu'] );
add_filter( 'parent_file', [Settings::class, 'change_active_menu'], 100 );
add_filter( 'submenu_file', [Settings::class, 'change_active_submenu'], 100, 2 );
add_action( 'admin_init', [Settings::class, 'save'] );
add_action( 'admin_enqueue_scripts', [Settings::class, 'admin_enqueue'] );
