<?php
namespace DrPlus\Backend\Appointments;

use DrPlus\AdminScripts;
use DrPlus\PublicScripts;
use DrPlus\Utils;

class AppointmentsList {
	protected static $PREFIX = "appointments_";
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
			'all'	=> [
				'menu_title'	=> __( 'All appointments', 'drplus' ),
				'page_title'	=> __( 'All appointments', 'drplus' ),
				'class'			=> 'All',
			],
			'view'	=> [
				'menu_title'	=> __( 'View appointment', 'drplus' ),
				'page_title'	=> __( 'Appointment', 'drplus' ),
				'class'			=> 'View',
			],
		];
	}
	public static function menu() {
		$sections = self::sections();
		$first_link = "appointments";
		
		$icon = 'data:image/svg+xml;base64,' . base64_encode( file_get_contents( DRPLUS_DIR . 'assets/icons/calendar-2.svg' ) );
		$hook = add_menu_page(
			__( 'Appointments', 'drplus' ),	// $page_title:string
			__( 'Appointments', 'drplus' ),	// $menu_title:string
			'manage_options',				// $capability:string
			$first_link,					// $menu_slug:string
			[__CLASS__, 'view'],			// $callback:callable
			$icon,							// $icon_url:string
			30
		);
		add_action( "load-{$hook}", [ __CLASS__, 'screen_option' ] );

		foreach( $sections as $slug => $section ) {
			if( $slug == 'all' ) {
				$slug = $first_link;
			} else {
				$slug = "appointments&tab={$slug}";
			}
			add_submenu_page(
				$first_link, // $parent_slug:string
				$section['page_title'], // $page_title:string
				$section['menu_title'], // $menu_title:string
				'manage_options', // $capability:string
				$slug, // $menu_slug:string
				[__CLASS__, 'view'], // $function:callable
			);
		}
	}

	public static function screen_option() {
		$option = 'per_page';
		$args = [
			'label' => __( "Appointments", 'drplus' ),
			'default' => 12,
			'option' => 'appointments_per_page'
		];

		self::$table = new AppointmentsListTable();

		add_screen_option( $option, $args );
	}

	public static function set_screen_option( $status, $option, $value ) {
		if ( $option === 'appointments_per_page' ) {
			return $value;
		}

		return $status;
	}

	public static function change_active_submenu( $submenu_file, $parent_file ) {
		if( !empty( $_GET['page'] ) && $_GET['page'] == 'appointments' && !empty( $_GET['tab'] ) && $_GET['tab'] == 'view' ) {
			if( !empty( $_GET['sid'] ) ) {
				$submenu_file = 'appointments';
			} else {
				$submenu_file = 'appointments&tab=view';
			}
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
				<hr class="wp-header-end">
			</div>
			<div id="<?php echo self::$PREFIX ?>container">
				<?php settings_errors( "drplus-appointments-settings" ); ?>
				<div id="<?php echo self::$PREFIX ?>content-wrap">
					<div id="<?php echo self::$PREFIX ?>content">
						<?php
						if( file_exists( DRPLUS_DIR . "inc/Backend/Appointments/class-appointments-{$active_tab}.php" ) ) {
							$active_tab_class = $sections[$active_tab]['class'];
							include_once( DRPLUS_DIR . "inc/Backend/Appointments/class-appointments-{$active_tab}.php" );
							// call view function
							$class = "DrPlus\Backend\Appointments\\" . $active_tab_class;
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

		if( empty( $_GET['page'] ) || $_GET['page'] !='appointments' ) return;
		
		$sections = self::sections();
		$active_tab = self::get_active_tab();
		if( $active_tab == 'all' ) return;
		$active_tab_class = $sections[$active_tab]['class'];
		if( file_exists( DRPLUS_DIR . "inc/Backend/Appointments/class-appointments-{$active_tab}.php" ) ) {
			include_once( DRPLUS_DIR . "inc/Backend/Appointments/class-appointments-{$active_tab}.php" );
			$class = "DrPlus\Backend\Appointments\\" . $active_tab_class;
			
			// Check nonce
			if( !isset( $_POST[self::$PREFIX . "nonce_value"] ) || !wp_verify_nonce( $_POST[self::$PREFIX . "nonce_value"], self::$PREFIX . "nonce" ) ) {
				add_settings_error( 'drplus-appointments-settings', self::$PREFIX . 'settings', __( "Something went wrong!", 'drplus' ), 'error' );
				return;
			}
			
			// call save function
			$class::save();
		}
	}

	public static function admin_enqueue() {
		$screen = get_current_screen();
		if( strpos( $screen->base, 'toplevel_page_appointments' ) !== 0 ) return;

		$active_tab = self::get_active_tab();
		if( $active_tab == 'view' ) {
			PublicScripts::select2();
			PublicScripts::pdp();
			AdminScripts::form_group();
			wp_enqueue_style( 'drplus-dashboard-app-view', DRPLUS_URI . "assets/css/backend/appointments/appointment-view.min.css", [], DRPLUS_VERSION );
			wp_enqueue_style( 'drplus-booking', DRPLUS_URI . "assets/css/booking/booking.min.css", [], DRPLUS_VERSION );
			if( DRPLUS_DEV ) {
				wp_enqueue_script( 'drplus-booking', DRPLUS_URI . "assets/js/booking.js", [], DRPLUS_VERSION, true );
			} else {
				wp_enqueue_script( 'drplus-booking', DRPLUS_URI . "assets/js/booking.min.js", [], DRPLUS_VERSION, true );
			}
			if( DRPLUS_DEV ) {
				wp_enqueue_script( 'drplus-dashboard-app-view', DRPLUS_URI . "assets/js/backend/appointments/appointment-view.js", ['jquery'], DRPLUS_VERSION, true );
			} else {
				wp_enqueue_script( 'drplus-dashboard-app-view', DRPLUS_URI . "assets/js/backend/appointments/appointment-view.min.js", ['jquery'], DRPLUS_VERSION, true );
			}
			if( is_rtl() ) {
				wp_enqueue_style( 'drplus-booking-rtl', DRPLUS_URI . "assets/css/booking/booking.rtl.min.css", [], DRPLUS_VERSION );
			}
			wp_localize_script( 'drplus-dashboard-app-view', 'drplusAppointment', [
				'i18n'	=> [
					'requiredField'			=> __( 'This field is required', 'drplus' ),
					'wrongEmail'			=> __( 'Please enter a valid email', 'drplus' ),
					'wrongIDCode'			=> __( 'Please enter a valid National ID', 'drplus' ),
					'wrongMobile'			=> __( 'Please enter a valid mobile', 'drplus' ),
				]
			] );
		} else if ( $active_tab == 'all' ) {
			wp_enqueue_style( 'drplus-dashboard-app-list', DRPLUS_URI . "assets/css/backend/appointments/appointments-list.min.css", [], DRPLUS_VERSION );
		}
	}
}
add_action( 'admin_menu', [AppointmentsList::class, 'menu'] );
add_filter( 'submenu_file', [AppointmentsList::class, 'change_active_submenu'], 100, 2 );
add_action( 'admin_init', [AppointmentsList::class, 'save'] );
add_action( 'admin_enqueue_scripts', [AppointmentsList::class, 'admin_enqueue'] );
add_filter( 'set-screen-option', [AppointmentsList::class, 'set_screen_option'], 10, 3 );