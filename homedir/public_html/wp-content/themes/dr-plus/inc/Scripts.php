<?php
namespace DrPlus;

use DrPlus\Utils\Auth;
use DrPlus\Utils\Booking;
use DrPlus\Utils\Onboard;
use DrPlus\Utils\Options;
use DrPlus\Utils\SMS;

if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( "\DrPlus\Scripts" ) ) {
	class Scripts {
		PRIVATE STATIC $IS_RTL = false;

		public static function main() {
			$options = Options::get_options( [
				'm-icons'			=> false,
				'sms'				=> true,
				'auth'				=> true,
				'auth_sms'			=> true,
				'auth_email'		=> true,
				'use-outside-iran'	=> false,
			] );

			self::$IS_RTL = is_rtl();

			$active_fonts = Utils::get_active_fonts();
			// Load active fonts
			foreach( $active_fonts as $font ) {
				wp_enqueue_style( "drplus-font-{$font}", Utils::get_font_stylesheet( $font ), [], DRPLUS_VERSION );
			}

			if( DRPLUS_DEV ) {
				wp_enqueue_script( 'drplus-utils', DRPLUS_URI . "assets/js/utils.js", ['jquery'], DRPLUS_VERSION, true );
				wp_enqueue_script( 'drplus', DRPLUS_URI . "assets/js/drplus.js", ['jquery'], DRPLUS_VERSION, true );
				wp_enqueue_script( 'drplus-front', DRPLUS_URI . "assets/js/script.js", ['jquery'], DRPLUS_VERSION, true );
				if( !Auth::is_login() ) {
					wp_enqueue_script( 'drplus-custom_select', DRPLUS_URI . "assets/js/custom_select.js", ['jquery'], DRPLUS_VERSION, true );
					wp_enqueue_script( 'drplus-search_input', DRPLUS_URI . "assets/js/search_input.js", ['jquery'], DRPLUS_VERSION, true );
					wp_enqueue_script( 'drplus-elementor', DRPLUS_URI . "assets/js/elementor.js", ['jquery'], DRPLUS_VERSION, true );
				}
			} else {
				wp_enqueue_script( 'drplus-utils', DRPLUS_URI . "assets/js/utils.min.js", ['jquery'], DRPLUS_VERSION, true );
				wp_enqueue_script( 'drplus', DRPLUS_URI . "assets/js/drplus.min.js", ['jquery'], DRPLUS_VERSION, true );
				wp_enqueue_script( 'drplus-front', DRPLUS_URI . "assets/js/script.min.js", ['jquery'], DRPLUS_VERSION, true );
				if( !Auth::is_login() ) {
					wp_enqueue_script( 'drplus-custom_select', DRPLUS_URI . "assets/js/custom_select.min.js", ['jquery'], DRPLUS_VERSION, true );
					wp_enqueue_script( 'drplus-search_input', DRPLUS_URI . "assets/js/search_input.min.js", ['jquery'], DRPLUS_VERSION, true );
					wp_enqueue_script( 'drplus-elementor', DRPLUS_URI . "assets/js/elementor.min.js", ['jquery'], DRPLUS_VERSION, true );
				}
			}
			wp_localize_script( 'drplus-front', 'drplusFront', [
				'i18n'		=> [
					'allCities'	=> __( "All cities", 'drplus' ),
				],
			] );

			Utils::drplus_vars_localize();

			if( !Auth::is_login() ) {
				PublicScripts::select2();
				PublicScripts::slider();
			}

			self::bootstrap();

			wp_enqueue_style( 'drplus-font-awesome', DRPLUS_URI . "assets/libs/fontawesome/css/fa.min.css", [], DRPLUS_VERSION );
			wp_enqueue_style( 'drplus-icons', DRPLUS_URI . "assets/css/iconly.min.css", [], DRPLUS_VERSION );
			if( $options['m-icons'] ) {
				wp_enqueue_style( 'drplus-m-icons', DRPLUS_URI . "assets/css/drplus-m.min.css", [], DRPLUS_VERSION );
			}
			wp_enqueue_style( 'drplus', DRPLUS_URI . "assets/css/style.min.css", [], DRPLUS_VERSION );

			if( class_exists( 'WooCommerce' ) && !Auth::is_login() ) {
				if( DRPLUS_DEV ) {
					wp_enqueue_script( 'drplus-wc', DRPLUS_URI . "assets/js/wc/wc.js", ['jquery'], DRPLUS_VERSION, true );
					wp_enqueue_script( 'drplus-wishlist', DRPLUS_URI . "assets/js/wc/wishlist.js", ['jquery'], DRPLUS_VERSION, true );
				} else {
					wp_enqueue_script( 'drplus-wc', DRPLUS_URI . "assets/js/wc/wc.min.js", ['jquery'], DRPLUS_VERSION, true );
					wp_enqueue_script( 'drplus-wishlist', DRPLUS_URI . "assets/js/wc/wishlist.min.js", ['jquery'], DRPLUS_VERSION, true );
				}
				wp_enqueue_style( 'drplus-wc', DRPLUS_URI . "assets/css/wc/wc.min.css", [], DRPLUS_VERSION );
				wp_enqueue_style( 'drplus-wishlist', DRPLUS_URI . "assets/css/wc/wishlist.min.css", [], DRPLUS_VERSION );
				if( is_rtl() ) {
					wp_enqueue_style( 'drplus-wishlist-rtl', DRPLUS_URI . "assets/css/wc/wishlist.rtl.min.css", [], DRPLUS_VERSION );
				}

				if( is_cart() ) {
					wp_enqueue_style( 'drplus-wc-cart', DRPLUS_URI . "assets/css/wc/cart.min.css", [], DRPLUS_VERSION );
				} else if( is_checkout() ) {
					wp_enqueue_style( 'drplus-wc-checkout', DRPLUS_URI . "assets/css/wc/checkout.min.css", [], DRPLUS_VERSION );
					if( is_order_received_page() ) {
						self::map_popup();
						PublicScripts::dragscroll();
						wp_enqueue_style( 'drplus-wc-order', DRPLUS_URI . "assets/css/wc/order.min.css", [], DRPLUS_VERSION );
					}
					wp_enqueue_style( 'drplus-booking', DRPLUS_URI . "assets/css/booking/booking.min.css", [], DRPLUS_VERSION );
					if( is_rtl() ) {
						wp_enqueue_style( 'drplus-booking-rtl', DRPLUS_URI . "assets/css/booking/booking.rtl.min.css", [], DRPLUS_VERSION );
					}
					if( DRPLUS_DEV ) {
						wp_enqueue_script( 'drplus-booking', DRPLUS_URI . "assets/js/booking.js", [], DRPLUS_VERSION, true );
					} else {
						wp_enqueue_script( 'drplus-booking', DRPLUS_URI . "assets/js/booking.min.js", [], DRPLUS_VERSION, true );
					}
				} else if( is_account_page() ) {
					wp_enqueue_media();
					
					self::map_popup();
					wp_enqueue_style( 'drplus-wc-my-account', DRPLUS_URI . "assets/css/wc/my-account.min.css", [], DRPLUS_VERSION );
					wp_enqueue_style( 'drplus-wc-order', DRPLUS_URI . "assets/css/wc/order.min.css", [], DRPLUS_VERSION );
					if( is_rtl() ) {
						wp_enqueue_style( 'drplus-booking-rtl', DRPLUS_URI . "assets/css/booking/booking.rtl.min.css", [], DRPLUS_VERSION );
					}
					if( is_user_logged_in() ) {
						PublicScripts::dropzone();
						PublicScripts::swapy();
						wp_enqueue_style( 'drplus-booking', DRPLUS_URI . "assets/css/booking/booking.min.css", [], DRPLUS_VERSION );
						wp_enqueue_style( 'drplus-comments', DRPLUS_URI . "assets/css/comments.min.css", [], DRPLUS_VERSION );
						if( DRPLUS_DEV ) {
							wp_enqueue_script( 'drplus-wc-my-account', DRPLUS_URI . "assets/js/wc/my-account.js", ['jquery'], DRPLUS_VERSION, true );
						} else {
							wp_enqueue_script( 'drplus-wc-my-account', DRPLUS_URI . "assets/js/wc/my-account.min.js", ['jquery'], DRPLUS_VERSION, true );
						}
						$my_account_localize = [
							'i18n'	=> [
								'confirmRemoveTime'	=> __( "Are you sure?", 'drplus' ),
							],
						];
						if( !$options['use-outside-iran'] && $options['sms'] && $options['auth'] && $options['auth_sms'] ) {
							$my_account_localize['otpTimer'] = SMS::get_settings()['settings']['auth']['login']['otp_timer'];
							$my_account_localize['nonces']['sendOtp'] = wp_create_nonce( "drplus-profile-send-otp" );
							$my_account_localize['nonces']['checkOtp'] = wp_create_nonce( "drplus-profile-check-otp" );
						}

						wp_localize_script( 'drplus-wc-my-account', 'drplusMyAccount', $my_account_localize );

						global $wp;
						$is_single_chat = false;
						if( !empty( $wp->query_vars['chats'] ) ) {
							$is_single_chat = true;
						} else if( isset( $wp->query_vars['specialist-dashboard'] ) ) {
							if( DRPLUS_DEV ) {
								wp_enqueue_script( 'drplus-wc-specialist-dashboard', DRPLUS_URI . "assets/js/wc/specialist-dashboard.js", ['jquery'], DRPLUS_VERSION, true );
							} else {
								wp_enqueue_script( 'drplus-wc-specialist-dashboard', DRPLUS_URI . "assets/js/wc/specialist-dashboard.js", ['jquery'], DRPLUS_VERSION, true );
							}

							$page_query = explode( '/', Utils::convert_chars( $wp->query_vars['specialist-dashboard'], true ) );
							if( count( $page_query ) == 2 && $page_query[0] == 'specialist-chats' ) {
								$is_single_chat = true;
							}
						}
						if( $is_single_chat ) {
							PublicScripts::circle_progress();
							wp_enqueue_script('wp-util');
							if( DRPLUS_DEV ) {
								wp_enqueue_script( 'drplus-chat-api', DRPLUS_URI . "assets/js/chat-api.js", ['jquery'], DRPLUS_VERSION, true );
								wp_enqueue_script( 'drplus-chat', DRPLUS_URI . "assets/js/chat.js", ['jquery'], DRPLUS_VERSION, true );
							} else {
								wp_enqueue_script( 'drplus-chat-api', DRPLUS_URI . "assets/js/chat-api.min.js", ['jquery'], DRPLUS_VERSION, true );
								wp_enqueue_script( 'drplus-chat', DRPLUS_URI . "assets/js/chat.min.js", ['jquery'], DRPLUS_VERSION, true );
							}
						}
					}
				} else if( is_product() ) {
					wp_enqueue_style( 'drplus-wc-single', DRPLUS_URI . "assets/css/wc/single.min.css", [], DRPLUS_VERSION );
					if( is_rtl() ) {
						wp_enqueue_style( 'drplus-wc-single-rtl', DRPLUS_URI . "assets/css/wc/single.rtl.min.css", [], DRPLUS_VERSION );
					}
					if( DRPLUS_DEV ) {
						wp_enqueue_script( 'drplus-wc-single', DRPLUS_URI . "assets/js/wc/single.js", ['drplus-slider'], DRPLUS_VERSION, true );
					} else {
						wp_enqueue_script( 'drplus-wc-single', DRPLUS_URI . "assets/js/wc/single.min.js", ['drplus-slider'], DRPLUS_VERSION, true );
					}
				}
			}

			self::not_found();
			if( !Auth::is_login() || !Utils::to_bool( $options['auth'] ) ) {
				self::singles();
				self::pages();
			}
			if( Auth::is_login() && Utils::to_bool( $options['auth'] ) ) {
				wp_enqueue_style( 'drplus-auth', DRPLUS_URI . "assets/css/auth.min.css", [], DRPLUS_VERSION );
				if( DRPLUS_DEV ) {
					wp_enqueue_script( 'drplus-auth', DRPLUS_URI . "assets/js/auth.js", ['jquery'], DRPLUS_VERSION, true );
				} else {
					wp_enqueue_script( 'drplus-auth', DRPLUS_URI . "assets/js/auth.min.js", ['jquery'], DRPLUS_VERSION, true );
				}
				$auth_localize = [
					'emailAuth'	=> $options['auth_email']
				];
				$sms_settings = [];
				if( Utils::to_bool( $options['sms'] ) ) {
					$sms_settings = SMS::get_settings();
				}
				if( !empty( $sms_settings ) ) {
					$auth_localize['mobileOneForm'] = $sms_settings['settings']['auth']['one_form'];
					if( !empty( $sms_settings['settings']['auth']['login']['enabled'] ) ) {
						$auth_localize['otpLoginTime'] = $sms_settings['settings']['auth']['login']['otp_timer'];
					}
					if( !empty( $sms_settings['settings']['auth']['register']['enabled'] ) ) {
						$auth_localize['otpRegisterTime'] = $sms_settings['settings']['auth']['register']['otp_timer'];
					}
				}
				wp_localize_script( 'drplus-auth', 'drplusAuth', $auth_localize );
			}

			if( Utils::is_wc_active() ) {
				PublicScripts::pdp();
				if( is_user_logged_in() ) {
					if( Onboard::is_onboard() || ( is_account_page() && wc_is_current_account_menu_item( 'specialist-dashboard' ) ) ) {
						PublicScripts::localizations( ['cities'] );
						wp_enqueue_style( 'drplus-onboard', DRPLUS_URI . "assets/css/specialists/onboard.min.css", [], DRPLUS_VERSION );
						if( is_rtl() ) {
							wp_enqueue_style( 'drplus-onboard-rtl', DRPLUS_URI . "assets/css/specialists/onboard.rtl.min.css", [], DRPLUS_VERSION );
						}
						if( DRPLUS_DEV ) {
							wp_enqueue_script( 'drplus-onboard', DRPLUS_URI . "assets/js/onboard.js", ['jquery'], DRPLUS_VERSION, true );
						} else {
							wp_enqueue_script( 'drplus-onboard', DRPLUS_URI . "assets/js/onboard.min.js", ['jquery'], DRPLUS_VERSION, true );
						}
						$onboard_localize = [
							'nonces'	=> [],
							'i18n'		=> [
								'selectHospitals'		=> __( 'Search & select Hospitals', 'drplus' ),
								'selectSpecialities'	=> __( 'Select specialities', 'drplus' ),
								'requiredField'			=> __( 'This field is required', 'drplus' ),
								'wrongEmail'			=> __( 'Please enter a valid email', 'drplus' ),
								'wrongIDCode'			=> __( 'Please enter a valid National ID', 'drplus' ),
								'wrongMobile'			=> __( 'Please enter a valid mobile', 'drplus' ),
								'wrongCardNumber'		=> __( 'Please enter a valid Card Number', 'drplus' ),
								'wrongShabaNumber'		=> __( 'Please enter a valid Shaba Number', 'drplus' ),
								'add'					=> __( 'Add', 'drplus' )
							],
						];
						wp_localize_script( 'drplus-onboard', 'drplusOnboard', $onboard_localize );
					}
				} else {
					if( is_account_page() ) {
						wp_enqueue_style( 'drplus-wc-login-form', DRPLUS_URI . "assets/css/wc/my-account/login-form.min.css", [], DRPLUS_VERSION );
						if( is_rtl() ) {
							wp_enqueue_style( 'drplus-wc-login-form-rtl', DRPLUS_URI . "assets/css/wc/my-account/login-form.rtl.min.css", [], DRPLUS_VERSION );
						}
					}
				}
			}

			if( is_search() ) {
				wp_enqueue_style( 'drplus-search', DRPLUS_URI . "assets/css/search.min.css", [], DRPLUS_VERSION );	
			}

			$upload_dir = wp_upload_dir();
			if( !file_exists( $upload_dir['basedir'] . "/drplus.css" ) ) {
				file_put_contents( $upload_dir['basedir'] . "/drplus.css", '' );
			}
			$custom_style_version = get_option( 'drplus_custom_style_version', time() );
			wp_enqueue_style( 'drplus-custom', $upload_dir['baseurl'] . "/drplus.css", [], $custom_style_version );
		}

		private static function not_found() {
			if( !is_404() ) return;

			wp_enqueue_style( 'drplus-404', DRPLUS_URI . "assets/css/404.min.css", [], DRPLUS_VERSION );
		}

		private static function pages() {
			if( !is_page() ) return;

			if( get_the_ID() == Booking::get_booking_page_id() ) {
				wp_enqueue_script('wp-util');
				wp_enqueue_style( 'drplus-booking', DRPLUS_URI . "/assets/css/booking/booking.min.css", [], DRPLUS_VERSION );
				if( DRPLUS_DEV ) {
					wp_enqueue_script( 'drplus-booking', DRPLUS_URI . "assets/js/booking.js", [], DRPLUS_VERSION, true );
				} else {
					wp_enqueue_script( 'drplus-booking', DRPLUS_URI . "assets/js/booking.min.js", [], DRPLUS_VERSION, true );
				}
			}
		}

		private static function bootstrap() {
			wp_enqueue_style( 'drplus-bootstrap', DRPLUS_URI . "assets/libs/bootstrap-grid.min.css", [], DRPLUS_VERSION );	
			if( self::$IS_RTL ) {
				wp_enqueue_style( 'drplus-bootstrap-rtl', DRPLUS_URI . "assets/libs/bootstrap-grid.rtl.min.css", [], DRPLUS_VERSION );	
			}
		}

		private static function singles( $post_types = [] ) {
			if( !is_singular() && empty( $post_type ) ) return;

			if( DRPLUS_DEV ) {
				wp_enqueue_script( 'drplus-single', DRPLUS_URI . "assets/js/single.js", [], DRPLUS_VERSION, true );
			} else {
				wp_enqueue_script( 'drplus-single', DRPLUS_URI . "assets/js/single.min.js", [], DRPLUS_VERSION, true );
			}
			wp_enqueue_style( 'drplus-single', DRPLUS_URI . "assets/css/single.min.css", [], DRPLUS_VERSION );
			if( self::$IS_RTL ) {
				wp_enqueue_style( 'drplus-single-rtl', DRPLUS_URI . "assets/css/single.rtl.min.css", [], DRPLUS_VERSION );
			}

			if( is_singular( 'hospital' ) || in_array( 'hospital', $post_types ) ) {
				self::map_popup();
				wp_enqueue_style( 'drplus-hospital-single', DRPLUS_URI . "assets/css/hospitals/single.min.css", [], DRPLUS_VERSION );
				if( is_rtl() ) {
					wp_enqueue_style( 'drplus-hospital-single-rtl', DRPLUS_URI . "assets/css/hospitals/single.rtl.min.css", [], DRPLUS_VERSION );
				}
				if( DRPLUS_DEV ) {
					wp_enqueue_script( 'drplus-hospital-single', DRPLUS_URI . "assets/js/hospital.js", [], DRPLUS_VERSION, true );
				} else {
					wp_enqueue_script( 'drplus-hospital-single', DRPLUS_URI . "assets/js/hospital.min.js", [], DRPLUS_VERSION, true );
				}
				wp_localize_script( 'drplus-hospital-single', 'drplusHospital', [
					'i18n'	=> [
						'copy'	=> __( "Copied:", 'drplus' ),
					]
				] );
			}
			if( is_singular( 'specialist' ) || in_array( 'specialist', $post_types ) ) {
				PublicScripts::lightgallery();
				self::map_popup();
				if( DRPLUS_DEV ) {
					wp_enqueue_script( 'drplus-specialist-single', DRPLUS_URI . "assets/js/specialist.js", [], DRPLUS_VERSION, true );
				} else {
					wp_enqueue_script( 'drplus-specialist-single', DRPLUS_URI . "assets/js/specialist.min.js", [], DRPLUS_VERSION, true );
				}
				wp_enqueue_style( 'drplus-specialist-single', DRPLUS_URI . "assets/css/specialists/single.min.css", [], DRPLUS_VERSION );
			}

			if( comments_open() && get_option( 'thread_comments' ) || !empty( $post_types ) ) {
				wp_enqueue_script( 'comment-reply' );
				wp_enqueue_style( 'drplus-comments', DRPLUS_URI . "assets/css/comments.min.css", [], DRPLUS_VERSION );
			}
		}

		private static function map_popup() {
			wp_enqueue_style( 'drplus-map-popup', DRPLUS_URI . "assets/css/components/map_popup.min.css", [], DRPLUS_VERSION );
		}

		public static function elementor_enqueue() {
			self::singles( ['hospital', 'specialist']);
		}
	}
	Scripts::main();
}
add_action( 'elementor/preview/enqueue_styles', [Scripts::class, 'elementor_enqueue'] );