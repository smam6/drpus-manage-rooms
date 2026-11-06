<?php

use DrPlus\Utils\Booking;
use DrPlus\Utils\Options;

if( !function_exists( 'drplus_booking_check_logged_in' ) ) {
	function drplus_booking_check_logged_in() {
		$booking_page_id = Booking::get_booking_page_id();
		if( !$booking_page_id || get_the_ID() != $booking_page_id ) return;

		$active_step = Booking::get_current_step();
		if( DRPLUS_SINGLE_SPECIALIST_MODE && $active_step == 'specialist' ) {
			wp_redirect( Booking::get_booking_page_url() ); die;
		}

		$settings = Options::get_options( [
			'guest-booking' => true,
			'auth'	=> true,
		] );

		// New condition: if guest allow to book and drplus auth is enable: return -> auth before checkout 
		$should_redirect = !is_user_logged_in();
		if( $settings['auth'] && $settings['guest-booking'] ) $should_redirect = false;
		if( !apply_filters( 'drplus/booking/redirect_guest', true ) ) $should_redirect = false;

		// Check user login. if guest redirect to login page
		if( $should_redirect ) {
			if( $settings['auth'] ) {
				$redirect_url = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
				wp_redirect( add_query_arg( ['login' => true, 'redirect_to' => urlencode( $redirect_url ) ], home_url() ) ); die;
			} else {
				// redirect to myaccount page
				wp_redirect( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ); die;
			}
		}

		// Check specialist status
		if( $active_step == 'time' ) {
			$specialist = Booking::get_specialist();
			if( !$specialist || $specialist->status != 'active' ) {
				wp_redirect( Booking::get_booking_page_url() ); die;
			}
		}
	}
}
add_action( 'wp', 'drplus_booking_check_logged_in', 8 );

if( !function_exists( 'drplus_booking_breadcrumb' ) ) {
	function drplus_booking_breadcrumb( $parts ) {
		if( empty( get_the_ID() ) || get_the_ID() != Booking::get_booking_page_id() ) return $parts;

		$active_step = Booking::get_current_step();
		$booking_page_url = Booking::get_booking_page_url( "", true );
		if( $active_step == 'receipt' ) {
			$parts[Booking::booking_steps()[$active_step]] = '#';
		} else {
			foreach( Booking::booking_steps() as $key => $value ) {
				if( $key == $active_step ) {
					$parts[$value] = '#';
					break;
				} else {
					$parts[$value] = esc_url( trailingslashit( $booking_page_url ) . $key );
				}
			}
		}

		return $parts;
	}
}
add_filter( 'drplus/breadcrumb/parts', 'drplus_booking_breadcrumb' );

if( !function_exists( 'drplus_add_booking_endpoints' ) ) {
	function drplus_add_booking_endpoints() {
		if( !Booking::is_booking_active() ) return;
		$steps = Booking::booking_steps();

		foreach( array_keys( $steps ) as $step ) {
			add_rewrite_endpoint( $step, EP_PAGES );
		}
	}
}
add_action( 'init', 'drplus_add_booking_endpoints' );

if( !function_exists( 'drplus_booking_start_session' ) ) {
	function drplus_booking_start_session() {
		if( !in_array( get_the_ID(), [Booking::get_booking_page_id(), get_option( 'woocommerce_checkout_page_id' )] ) ) return;

		if( session_status() !== PHP_SESSION_ACTIVE ) {
			session_start();
		}
	}
}
add_action( 'template_redirect', 'drplus_booking_start_session', 1 );

if( !function_exists( 'drplus_create_booking_product' ) ) {
	function drplus_create_booking_product() {
		// Call get product id to check if product already created
		Booking::get_booking_product_id();
	}
}
add_action( 'after_switch_theme', 'drplus_create_booking_product' );

if( !function_exists( 'drplus_add_wallet_ledger_type' ) ) {
	function drplus_add_wallet_ledger_type( $types ) {
		$types['booking_payout'] = esc_html__( 'Booking Payout', 'drplus' );
		return $types;
	}
}
add_filter( 'sheyda/wallet/ledger/types', 'drplus_add_wallet_ledger_type' );

if( !function_exists( 'drplus_wallet_new_booking_payout' ) ) {
	function drplus_wallet_new_booking_payout( $info, $data ) {
		$info['balance'] += $info['amount'];
		return $info;
	}
}
add_filter( 'sheyda/wallet/ledger/booking_payout/new', 'drplus_wallet_new_booking_payout', 10, 2 );

if( !function_exists( 'drplus_wallet_booking_payout_related_id_text' ) ) {
	function drplus_wallet_booking_payout_related_id_text( $text, $item ) {
		if( $item['type'] != 'booking_payout' ) return $text;
		$url = add_query_arg( ['page' => 'appointments', 'tab' => 'view', 'book_id' => $item['meta']['book_id']], admin_url( 'admin.php' ) );
		$text = '<a href="' . esc_url( $url ) . '" target="_blank">' . sprintf( __( 'Appointment #%s', 'drplus' ), $item['meta']['book_id'] ) . '</a>';
		return $text;
	}
}
add_filter( 'sheyda/wallet/wp_dashboard/ledgers_table/item/related_id_text', 'drplus_wallet_booking_payout_related_id_text', 10, 2 );