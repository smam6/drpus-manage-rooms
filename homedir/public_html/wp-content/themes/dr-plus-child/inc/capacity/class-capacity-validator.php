<?php
/**
 * Dr Plus Child Theme — Room Capacity Management
 *
 * Implements: spec 3.1-3.17, 7.7-7.9 (server-side date guard), 5.6.53
 * Category: 3 — Server-side validation on woocommerce_checkout_process
 *
 * Fires BEFORE the booking insert: woocommerce_checkout_process at
 * plugins/woocommerce/includes/class-wc-checkout.php:1378 runs before
 * create_order() at :1411 and woocommerce_checkout_order_processed at :1427.
 *
 * @package DrPlusChild\Capacity
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validator: rejects over-capacity and missing-date bookings at checkout.
 *
 * Single responsibility: checkout validation; counting is delegated to the
 * repository, logging to the logger (spec 5.6.53).
 */
class DrPlus_Child_Capacity_Validator {

	/**
	 * Error codes as class constants (spec 3.15, 5.6.67).
	 */
	const ERR_CAPACITY = 'capacity_exceeded';
	const ERR_MISSING_DATE = 'missing_date';

	/**
	 * Priority 4: missing-date check runs before the capacity check (fail fast).
	 *
	 * Implements: spec 3.6, 7.7, 7.8, 7.9
	 *
	 * @return void
	 */
	public static function check_missing_date() {
		$book = self::cart_book_data();
		if ( null === $book ) {
			return; // Not a booking order.
		}
		$date = self::extract_date( $book );
		if ( '' === $date ) {
			wc_add_notice( DrPlus_Child_Capacity_Messages::missing_date(), 'error' );
			self::log( $book, 0, 0, self::ERR_MISSING_DATE );
		}
	}

	/**
	 * Priority 5: capacity validation on woocommerce_checkout_process (spec 3.1, 3.8).
	 *
	 * Implements: spec 3.1-3.11, 3.14, 3.15, 3.16
	 *
	 * @return void
	 */
	public static function validate_capacity() {
		if ( ! DrPlus_Child_Capacity_Service::enabled() ) {
			return;
		}
		$book = self::cart_book_data();
		if ( null === $book ) {
			return;
		}
		$office_id = isset( $book['office_id'] ) ? (string) $book['office_id'] : '';
		if ( '' === $office_id ) {
			return;
		}
		// Visit type is inferred from office_id — book_data has no visit_type key (spec 3.4, Finding 4).
		if ( DrPlus_Child_Capacity_Service::is_online_visit( $office_id ) ) {
			return; // Online visits exempt (spec 3.5).
		}
		$date = self::extract_date( $book );
		if ( '' === $date ) {
			return; // Already reported by the priority-4 date check (spec 3.6).
		}
		$start_time = self::extract_start_time( $book );
		$specialist = isset( $book['specialist_id'] ) ? (int) $book['specialist_id'] : 0;
		$capacity   = DrPlus_Child_Capacity_Service::capacity_for( $office_id );
		// Race-safe decision: FOR UPDATE on InnoDB, GET_LOCK otherwise (spec 3.14, Category 4).
		list( $allowed, $count, $error ) = DrPlus_Child_Capacity_Repository::check_slot_with_lock( $office_id, $date, $start_time, $capacity );
		if ( $allowed ) {
			return; // Existing bookings are never touched (spec 3.11, 8.12).
		}
		self::log( $book, $count, $capacity, $error ? $error : self::ERR_CAPACITY );
		// Rejection must include all available hours of the day (decision #4, spec 3.9).
		$slots = self::day_slots_for_notice( $office_id, $date, isset( $book['specialist_id'] ) ? (int) $book['specialist_id'] : 0 );
		$hours = DrPlus_Child_Capacity_Service::get_available_hours( $office_id, $date, $slots );
		wc_add_notice( DrPlus_Child_Capacity_Messages::capacity_exceeded( $hours ), 'error' );
	}

	/**
	 * Read the booking payload from the cart (spec 3.2).
	 *
	 * Implements: spec 3.2
	 *
	 * @return array|null book_data array or null when the cart is not a booking order.
	 */
	protected static function cart_book_data() {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return null;
		}
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			if ( ! empty( $cart_item['is_booking'] ) && ! empty( $cart_item['book_data'] ) && is_array( $cart_item['book_data'] ) ) {
				return $cart_item['book_data'];
			}
		}
		return null;
	}

	/**
	 * Extract and normalize the booking date: primary `date` (Y-m-d), fallback `raw_date` (unix) (spec 3.3).
	 *
	 * Implements: spec 3.3, Finding 5
	 *
	 * @param array $book book_data from the cart item.
	 * @return string Y-m-d or empty string.
	 */
	protected static function extract_date( $book ) {
		if ( ! empty( $book['date'] ) ) {
			return DrPlus_Child_Capacity_Repository::normalize_date( $book['date'] );
		}
		if ( ! empty( $book['raw_date'] ) ) {
			return DrPlus_Child_Capacity_Repository::normalize_date( $book['raw_date'] );
		}
		return '';
	}

	/**
	 * Extract and normalize the start time: primary `start_time` (H:i:s), fallback `raw_time` (ms) (spec 3.3).
	 *
	 * Implements: spec 3.3, Finding 5
	 *
	 * @param array $book book_data from the cart item.
	 * @return string H:i:s or empty string.
	 */
	protected static function extract_start_time( $book ) {
		if ( ! empty( $book['start_time'] ) ) {
			return DrPlus_Child_Capacity_Repository::normalize_time( $book['start_time'] );
		}
		if ( ! empty( $book['raw_time'] ) ) {
			return DrPlus_Child_Capacity_Repository::normalize_time( $book['raw_time'] );
		}
		return '';
	}

	/**
	 * Fetch the parent's slot list for the notice (best effort; may be empty when the
	 * parent class is unavailable, e.g. very early hooks).
	 *
	 * Implements: spec 3.9
	 *
	 * @param string $office_id      Clinic id.
	 * @param string $date           Y-m-d.
	 * @param int    $specialist_id  Specialist id.
	 * @return array
	 */
	protected static function day_slots_for_notice( $office_id, $date, $specialist_id ) {
		if ( class_exists( 'DrPlus\\Utils\\Booking' ) && $specialist_id ) {
			$chunk = 30;
			$slots = \DrPlus\Utils\Booking::get_available_time_slots( $date, $specialist_id, $office_id, $chunk );
			if ( is_array( $slots ) ) {
				return $slots;
			}
		}
		return array();
	}

	/**
	 * Log a blocked attempt (delegates to the logger; never throws).
	 *
	 * Implements: spec 3.8, 6.6
	 *
	 * @param array  $book          book_data.
	 * @param int    $count         Current distinct count.
	 * @param int    $capacity      Capacity limit.
	 * @param string $reason        Error code.
	 * @return void
	 */
	protected static function log( $book, $count, $capacity, $reason ) {
		DrPlus_Child_Capacity_Logger::log_blocked( array(
			'office_id'      => isset( $book['office_id'] ) ? (string) $book['office_id'] : '',
			'booking_date'   => self::extract_date( $book ),
			'start_time'     => self::extract_start_time( $book ),
			'specialist_id'  => isset( $book['specialist_id'] ) ? (int) $book['specialist_id'] : 0,
			'current_count'  => $count,
			'capacity_limit' => $capacity,
			'reason'         => $reason,
		) );
	}
}
