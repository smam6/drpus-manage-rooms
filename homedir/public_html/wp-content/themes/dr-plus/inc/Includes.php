<?php
namespace DrPlus;

use DrPlus\Utils\Booking;
use DrPlus\Utils\Options;

class Includes {
	private static $is_admin = false;
	public static function main( $is_admin ) {
		self::$is_admin = $is_admin;

		include( DRPLUS_DIR . "inc/Logger.php" );

		include( DRPLUS_DIR . "inc/Updates/Update.php" );

		include( DRPLUS_DIR . "inc/Utils/utils-sanitizers.php" );
		include( DRPLUS_DIR . "inc/Utils/utils-validators.php" );
		include( DRPLUS_DIR . "inc/Utils/utils-formatters.php" );
		include( DRPLUS_DIR . "inc/Utils/utils-page.php" );
		include( DRPLUS_DIR . "inc/Utils/utils-archive.php" );
		include( DRPLUS_DIR . "inc/Utils/utils-product.php" );
		include( DRPLUS_DIR . "inc/Utils/utils-ui.php" );
		include( DRPLUS_DIR . "inc/Utils/utils-date.php" );
		include( DRPLUS_DIR . "inc/Utils/utils-elementor.php" );
		include( DRPLUS_DIR . "inc/Utils/utils-user.php" );
		include( DRPLUS_DIR . "inc/Utils/utils-hospital.php" );
		include( DRPLUS_DIR . "inc/Utils/utils-wc.php" );
		include( DRPLUS_DIR . "inc/Utils/utils-wishlist.php" );
		include( DRPLUS_DIR . "inc/Utils/utils-speciality.php" );
		include( DRPLUS_DIR . "inc/Utils/utils-search.php" );
		include( DRPLUS_DIR . "inc/Utils/utils-subscription-plans.php" );
		include( DRPLUS_DIR . "inc/Utils/utils-onboard.php" );
		include( DRPLUS_DIR . "inc/Utils/utils-auth.php" );
		include( DRPLUS_DIR . "inc/Utils/utils-notifications.php" );
		include( DRPLUS_DIR . "inc/Utils/utils-booking.php" );
		include( DRPLUS_DIR . "inc/Utils/utils-medical.php" );
		include( DRPLUS_DIR . "inc/Utils/utils-location.php" );
		if( $is_admin ) {
			include( DRPLUS_DIR . "inc/Utils/utils-admin-ui.php" );
		}
		include( DRPLUS_DIR . "inc/Utils/utils-widgets.php" );

		// Specialists utils files
		include( DRPLUS_DIR . "inc/Utils/Specialists/utils-specialists.php" );
		include( DRPLUS_DIR . "inc/Utils/Specialists/utils-specialist-specialities-rel.php" );
		include( DRPLUS_DIR . "inc/Utils/Specialists/utils-specialist-hospitals-rel.php" );
		include( DRPLUS_DIR . "inc/Utils/Specialists/utils-specialist-insurances-rel.php" );

		// Chat
		include( DRPLUS_DIR . "inc/Utils/utils-chat.php" );
		include( DRPLUS_DIR . "inc/Utils/utils-skyroom.php" );

		if( Booking::is_booking_active() ) {
			include( DRPLUS_DIR . "inc/SaveBookingProcess.php" );
			include( DRPLUS_DIR . "inc/ProcessBuyPlan.php" );
		}

		self::post_types();

		include_once( DRPLUS_DIR . 'inc/Elementor/ThemeBuilder/elementor-theme-builder.php' );

		if( !isset( $redux_demo ) && file_exists( DRPLUS_DIR . 'Redux/Options.php' ) ) {
			include_once( DRPLUS_DIR . 'Redux/Options.php' );
		}

		if( wp_doing_ajax() ) {
			include( DRPLUS_DIR . "inc/AJAX.php" );
		} else {
			include( DRPLUS_DIR . "inc/PublicScripts.php" );
		}

		self::models();

		if( $is_admin ) {
			self::meta_boxes();
			include( DRPLUS_DIR . "Redux/Save.php" );

			include( DRPLUS_DIR . "inc/Backend/Dashboard.php" );

			// Specialists
			if ( ! class_exists( 'WP_List_Table' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
			}
			include( DRPLUS_DIR . "inc/Backend/Specialists/specialists-settings.php" );

			include( DRPLUS_DIR . "inc/Backend/Appointments/appointments-list.php" );
			include( DRPLUS_DIR . "inc/Backend/Appointments/class-appointments-list-table.php" );
			
			include( DRPLUS_DIR . "inc/Backend/WidgetsPage.php" );

			include( DRPLUS_DIR . "inc/Backend/Messages.php" );
		}

		include( DRPLUS_DIR . "inc/MenuItem.php" );

		self::sms();
	}

	public static function casts() {
		include( DRPLUS_DIR . "inc/Casts/Mobile.php" );
		include( DRPLUS_DIR . "inc/Casts/Time.php" );
	}

	public static function cronjobs() {
		$options = Options::get_options( [
			'sms'	=> true,
		] );
		include( DRPLUS_DIR . "inc/Cronjobs/CheckSkyroomUsersID.php" );
		include( DRPLUS_DIR . "inc/Cronjobs/CheckBookingStatuses.php" );
		include( DRPLUS_DIR . "inc/Cronjobs/ReminderLogCleanUp.php" );
		if( $options['sms'] ) {
			include( DRPLUS_DIR . "inc/Cronjobs/CheckBookingMessages.php" );
		}
	}

	private static function models() {
		$options = Options::get_options( [
			'wishlist'		=> true,
			'auth'			=> true,
		] );
		if( Utils::to_bool( $options['wishlist'] ) ) {
			include_once( DRPLUS_DIR . "inc/Models/Wishlist.php" );
		}
		if( Utils::to_bool( $options['auth'] ) ) {
			include_once( DRPLUS_DIR . "inc/Models/OTP.php" );
		}

		// Specialists Models files
		include_once( DRPLUS_DIR . "inc/Models/Specialists.php" );
		include_once( DRPLUS_DIR . "inc/Models/SpecialistSpecialitiesRel.php" );
		include_once( DRPLUS_DIR . "inc/Models/SpecialistHospitalsRel.php" );
		include_once( DRPLUS_DIR . "inc/Models/SpecialistInsurancesRel.php" );
		
		include_once( DRPLUS_DIR . "inc/Models/Times.php" );
		include_once( DRPLUS_DIR . "inc/Models/Booking.php" );
		include_once( DRPLUS_DIR . "inc/Models/NotificationsUserRel.php" );
		include_once( DRPLUS_DIR . "inc/Models/ReminderLog.php" );

		// Chat
		include_once( DRPLUS_DIR . "inc/Models/ChatSession.php" );
		include_once( DRPLUS_DIR . "inc/Models/ChatMessage.php" );
	}

	private static function post_types() {
		include( DRPLUS_DIR . "inc/PostTypes/post-type-specialist.php" );
		include( DRPLUS_DIR . "inc/PostTypes/post-type-speciality.php" );
		include( DRPLUS_DIR . "inc/PostTypes/post-type-hospital.php" );
		include( DRPLUS_DIR . "inc/PostTypes/post-type-notification.php" );
	}

	private static function meta_boxes() {
		include( DRPLUS_DIR . "inc/Backend/Metaboxes/Page/Settings.php" );
		include( DRPLUS_DIR . "inc/Backend/Metaboxes/Hospital/Settings.php" );
		include( DRPLUS_DIR . "inc/Backend/Metaboxes/Speciality/Settings.php" );
		include( DRPLUS_DIR . "inc/Backend/Metaboxes/Notification/Settings.php" );
		include( DRPLUS_DIR . "inc/Backend/Metaboxes/Product/Attribute.php" );
		include( DRPLUS_DIR . "inc/Backend/Metaboxes/Product/Subtitle.php" );
	}

	public static function wc( $is_admin ) {
		if( !Utils::is_wc_active() ) return;
		
		include( DRPLUS_DIR . "woocommerce/WC.php" );

		if( $is_admin ) {
			include( DRPLUS_DIR . "inc/Backend/WCAttributeFields.php" );
		}
	}

	public static function integrations( $is_admin ) {
		include( DRPLUS_DIR . "inc/Integrations/integrations-wp-rocket.php" );
	}

	public static function backend_pages() {
		include( DRPLUS_DIR . "inc/Backend/Comments.php" );
		include( DRPLUS_DIR . "inc/Backend/Specialists/specialists-functions.php" );

		include( DRPLUS_DIR . "inc/Backend/Permalinks.php" );

		include( DRPLUS_DIR . "inc/Changelogs/Changelog.php" );

		include( DRPLUS_DIR . "inc/Backend/Pages/SubscriptionPlans.php" );
	}
	
	public static function components() {
		$components = [
			'alert',
			'button',
			'custom-select',
			'simple-icon',
			'select'	,
			'loading'	,
			'pro-icon'	,
			'section-title',
			'line-dot'
		];
		
		foreach( $components as $component ) {
			include( DRPLUS_DIR . "inc/Components/component-{$component}.php" );
		}
	}

	private static function sms() {
		$options = Options::get_options( [
			'sms'	=> true,
		] );
		if( !$options['sms'] ) return;

		include( DRPLUS_DIR . "inc/sms/Includes.php" );
		include( DRPLUS_DIR . "inc/Utils/utils-sms.php" );
	}

	public static function wallet() {
		if( !Utils::is_wc_active() ) return;
		
		include( DRPLUS_DIR . "inc/wallet/ThemeInit.php" );
	}

	public static function rest_api() {
		include( DRPLUS_DIR . "inc/REST/Permissions.php" );
		include( DRPLUS_DIR . "inc/REST/RateLimiter.php" );
		include( DRPLUS_DIR . "inc/REST/ChatController.php" );
		include( DRPLUS_DIR . "inc/REST/ChatRoutes.php" );
	}
}
$is_admin = is_admin();
Includes::main( $is_admin );
Includes::casts();
Includes::wc( $is_admin );
Includes::integrations( $is_admin );
Includes::components();
Includes::cronjobs();
if( $is_admin ) {
	Includes::backend_pages();
}
Includes::wallet();
Includes::rest_api();