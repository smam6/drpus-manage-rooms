<?php
/**
 * Dr Plus Child Theme — Room Capacity Management
 *
 * Implements: spec 3.1-3.17, 7.7-7.9 (server-side date guard), 5.6.53, 4.13-4.17 (layered locking)
 * Category: 3 — Server-side validation on woocommerce_checkout_process;
 *           Category 4 — Race condition (GET_LOCK held across the parent's insert + compensating rollback)
 *
 * Fires BEFORE the booking insert: woocommerce_checkout_process at
 * plugins/woocommerce/includes/class-wc-checkout.php:1378 runs before
 * create_order() at :1411 and woocommerce_checkout_order_processed at :1427.
 *
 * Booking extraction (`extract_booking_from_request`) inspects three sources in order:
 *   1. WooCommerce Cart (`WC()->cart->get_cart()` item `is_booking` / `book_data`,
 *      falling back from localized display `date` to unix `raw_date` and from
 *      `start_time` to ms `raw_time`).
 *   2. PHP Session (`$_SESSION['booking']`).
 *   3. Request POST (`$_POST['booking_date']` or `$_POST['date']`).
 *
 * Two-layer race fix (closes audit residual #18):
 *   Layer 1 — DrPlus_Child_Capacity_Lock_Manager::acquire() at checkout, held until
 *             woocommerce_checkout_order_processed (after the parent's INSERT) (spec 4.13, 4.14).
 *   Layer 2 — post-insert re-count; if over capacity, soft-delete THIS order's booking
 *             and cancel the order (compensating rollback, spec 4.15).
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
 * repository, locking to the lock manager, logging to the logger (spec 5.6.53).
 */
class DrPlus_Child_Capacity_Validator {

	/**
	 * Error codes as class constants (spec 3.15, 5.6.67).
	 */
	const ERR_CAPACITY     = 'capacity_exceeded';
	const ERR_MISSING_DATE = 'missing_date';
	const ERR_COMPENSATING = 'compensating_rollback';

	/**
	 * WC session key holding the deferred lock context (spec 4.14).
	 */
	const SESSION_LOCK_CONTEXT = 'drplus_capacity_lock_context';

	/**
	 * Unified checkout validator: conditional date guard + online exemption + capacity check.
	 *
	 * Returns early with no notices when the request/cart is not a booking order.
	 *
	 * @return void
	 */
	public static function validate_checkout(): void {
		$booking = self::extract_booking_from_request();

		// If it's not a booking at all (e.g., normal product), return early and do nothing.
		if ( null === $booking ) {
			return;
		}

		// If it IS a booking, but the date is missing, NOW we show the error.
		if ( empty( $booking['date'] ) ) {
			wc_add_notice(
				__( 'لطفاً ابتدا تاریخ مراجعه را انتخاب کنید.', 'drplus-child' ),
				'error'
			);
			self::log( $booking, 0, 0, self::ERR_MISSING_DATE );
			return;
		}

		self::validate_booking_capacity( $booking );
	}

	/**
	 * Priority 4: missing-date check runs before the capacity check (fail fast).
	 *
	 * Implements: spec 3.6, 7.7, 7.8, 7.9
	 *
	 * @return void
	 */
	public static function check_missing_date(): void {
		$booking = self::extract_booking_from_request();
		if ( null === $booking ) {
			return; // Not a booking order.
		}
		if ( empty( $booking['date'] ) ) {
			wc_add_notice( DrPlus_Child_Capacity_Messages::missing_date(), 'error' );
			self::log( $booking, 0, 0, self::ERR_MISSING_DATE );
		}
	}

	/**
	 * Priority 5: capacity validation on woocommerce_checkout_process (spec 3.1, 3.8).
	 *
	 * Layer 1: acquires the slot advisory lock BEFORE counting and holds it across the
	 * parent's insert (deferred release in post_insert_verify_and_release, spec 4.13, 4.14).
	 * On rejection the lock is released immediately.
	 *
	 * Implements: spec 3.1-3.11, 3.14, 3.15, 3.16, 4.13, 4.14, 4.17
	 *
	 * @return void
	 */
	public static function validate_capacity(): void {
		if ( ! DrPlus_Child_Capacity_Service::enabled() ) {
			return;
		}
		$booking = self::extract_booking_from_request();
		if ( null === $booking || empty( $booking['date'] ) ) {
			return; // Not a booking, or already reported by the priority-4 date check (spec 3.6).
		}

		self::validate_booking_capacity( $booking );
	}

	/**
	 * Execute online-visit exemption, Layer 1 locking, capacity check, and deferred release setup.
	 *
	 * @param array $booking Normalized booking array from extract_booking_from_request().
	 * @return void
	 */
	protected static function validate_booking_capacity( array $booking ): void {
		if ( ! DrPlus_Child_Capacity_Service::enabled() ) {
			return;
		}
		$office_id = isset( $booking['office_id'] ) ? (string) $booking['office_id'] : '';
		if ( '' === $office_id ) {
			return;
		}
		// Visit type is inferred from office_id — book_data has no visit_type key (spec 3.4, Finding 4).
		if ( DrPlus_Child_Capacity_Service::is_online_visit( $office_id ) ) {
			return; // Online visits exempt (spec 3.5).
		}
		$date       = (string) $booking['date'];
		$start_time = isset( $booking['start_time'] ) ? (string) $booking['start_time'] : '';
		$specialist = isset( $booking['specialist_id'] ) ? (int) $booking['specialist_id'] : 0;
		$capacity   = DrPlus_Child_Capacity_Service::capacity_for( $office_id );

		// Layer 1: acquire the global advisory lock BEFORE counting (spec 4.13). Timeout 5s max.
		$acquired = DrPlus_Child_Capacity_Lock_Manager::acquire( $office_id, $date, $start_time, 5 );
		if ( ! $acquired ) {
			self::log( $booking, 0, $capacity, DrPlus_Child_Capacity_Repository::ERR_LOCK_FAILED );
			wc_add_notice( DrPlus_Child_Capacity_Messages::system_busy(), 'error' );
			return;
		}

		// Lock is now held. It is released either here (rejection) or deferred to
		// post_insert_verify_and_release (after the parent's INSERT) — never before (spec 4.14).
		$count = DrPlus_Child_Capacity_Repository::count_distinct_specialists( $office_id, $date, $start_time );

		if ( $count >= $capacity ) {
			// Reject and release immediately (spec 4.14).
			self::log( $booking, $count, $capacity, self::ERR_CAPACITY );
			// Rejection must include all available hours of the day (decision #4, spec 3.9).
			$slots = self::day_slots_for_notice( $office_id, $date, $specialist );
			$hours = DrPlus_Child_Capacity_Service::get_available_hours( $office_id, $date, $slots );
			wc_add_notice( DrPlus_Child_Capacity_Messages::capacity_exceeded( $hours ), 'error' );
			DrPlus_Child_Capacity_Lock_Manager::release(
				DrPlus_Child_Capacity_Lock_Manager::build_lock_name( $office_id, $date, $start_time )
			);
			return;
		}

		// Capacity OK. Do NOT release the lock — post_insert_verify_and_release handles it
		// after the parent's insert (spec 4.14). Store the lock context on the WC session.
		if ( function_exists( 'WC' ) && WC()->session ) {
			WC()->session->set( self::SESSION_LOCK_CONTEXT, array(
				'office_id'     => $office_id,
				'date'          => $date,
				'start_time'    => $start_time,
				'capacity'      => $capacity,
				'pre_count'     => $count,
				'specialist_id' => $specialist,
			) );
		}
	}

	/**
	 * Extract booking details from WooCommerce Cart, PHP Session, or POST in priority order.
	 *
	 * Source A: WC()->cart->get_cart() -> $cart_item['book_data'] (returns immediately if date resolves).
	 * Source B: $_SESSION['booking'] (fallback if cart check fails).
	 * Source C: $_POST['booking_date'] / $_POST['date'] (final fallback).
	 *
	 * @return array{office_id:string,date:string,start_time:string,specialist_id:int}|null
	 */
	public static function extract_booking_from_request(): ?array {
		$partial_booking = null;

		// Source A: WooCommerce Cart.
		if ( function_exists( 'WC' ) && WC()->cart && method_exists( WC()->cart, 'get_cart' ) ) {
			$cart_items = WC()->cart->get_cart();
			if ( is_iterable( $cart_items ) ) {
				foreach ( $cart_items as $cart_item ) {
					if ( ! is_array( $cart_item ) ) {
						continue;
					}
					if ( ! empty( $cart_item['is_booking'] ) || ! empty( $cart_item['book_data'] ) ) {
						$raw_data = $cart_item['book_data'] ?? array();
						if ( is_object( $raw_data ) ) {
							$raw_data = get_object_vars( $raw_data );
						}
						$data = is_array( $raw_data ) ? $raw_data : array();

						$office_id     = isset( $data['office_id'] )
							? (string) $data['office_id']
							: ( isset( $data['office'] ) ? (string) $data['office'] : '' );
						$date          = self::extract_date( $data );
						$start_time    = self::extract_start_time( $data );
						$specialist_id = isset( $data['specialist_id'] ) ? (int) $data['specialist_id'] : 0;

						$booking = array(
							'office_id'     => $office_id,
							'date'          => $date,
							'start_time'    => $start_time,
							'specialist_id' => $specialist_id,
						);

						if ( '' !== $date ) {
							return $booking;
						}

						if ( null === $partial_booking ) {
							$partial_booking = $booking;
						}
					}
				}
			}
		}

		// Source B: Session (Fallback).
		if ( isset( $_SESSION['booking'] ) && is_array( $_SESSION['booking'] ) && ! empty( $_SESSION['booking'] ) ) {
			$session_data = $_SESSION['booking'];
			$date         = self::extract_date( $session_data );

			if ( '' !== $date ) {
				$office_id     = isset( $session_data['office_id'] )
					? (string) $session_data['office_id']
					: ( isset( $session_data['office'] ) ? (string) $session_data['office'] : '' );
				$start_time    = self::extract_start_time( $session_data );
				$specialist_id = isset( $session_data['specialist_id'] ) ? (int) $session_data['specialist_id'] : 0;

				if ( null !== $partial_booking ) {
					return array(
						'office_id'     => '' !== $partial_booking['office_id'] ? $partial_booking['office_id'] : $office_id,
						'date'          => $date,
						'start_time'    => '' !== $partial_booking['start_time'] ? $partial_booking['start_time'] : $start_time,
						'specialist_id' => $partial_booking['specialist_id'] > 0 ? $partial_booking['specialist_id'] : $specialist_id,
					);
				}

				return array(
					'office_id'     => $office_id,
					'date'          => $date,
					'start_time'    => $start_time,
					'specialist_id' => $specialist_id,
				);
			}
		}

		// Source C: POST (Fallback).
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- read-only extraction during checkout/booking validation.
		$has_post_booking_date = isset( $_POST['booking_date'] ) && ! is_array( $_POST['booking_date'] );
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$has_post_date = isset( $_POST['date'] ) && ! is_array( $_POST['date'] );

		if ( $has_post_booking_date || $has_post_date ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$raw_post_date = ( $has_post_booking_date && '' !== trim( (string) $_POST['booking_date'] ) )
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				? $_POST['booking_date']
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				: ( $has_post_date ? $_POST['date'] : '' );

			$date = self::normalize_date_candidate( self::clean_text( $raw_post_date ) );

			$post_office = '';
			foreach ( array( 'booking_office', 'office_id', 'office' ) as $office_key ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				if ( isset( $_POST[ $office_key ] ) && ! is_array( $_POST[ $office_key ] ) && '' !== (string) $_POST[ $office_key ] ) {
					// phpcs:ignore WordPress.Security.NonceVerification.Missing
					$post_office = self::clean_text( $_POST[ $office_key ] );
					break;
				}
			}

			$post_time = '';
			foreach ( array( 'booking_time', 'start_time', 'raw_time' ) as $time_key ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				if ( isset( $_POST[ $time_key ] ) && ! is_array( $_POST[ $time_key ] ) && '' !== (string) $_POST[ $time_key ] ) {
					// phpcs:ignore WordPress.Security.NonceVerification.Missing
					$post_time = self::normalize_time_candidate( self::clean_text( $_POST[ $time_key ] ) );
					if ( '' !== $post_time ) {
						break;
					}
				}
			}

			$post_specialist = 0;
			foreach ( array( 'booking_specialist_id', 'specialist_id' ) as $spec_key ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				if ( isset( $_POST[ $spec_key ] ) && ! is_array( $_POST[ $spec_key ] ) && '' !== (string) $_POST[ $spec_key ] ) {
					// phpcs:ignore WordPress.Security.NonceVerification.Missing
					$post_specialist = (int) self::clean_text( $_POST[ $spec_key ] );
					break;
				}
			}

			if ( '' !== $date ) {
				if ( null !== $partial_booking ) {
					return array(
						'office_id'     => '' !== $partial_booking['office_id'] ? $partial_booking['office_id'] : $post_office,
						'date'          => $date,
						'start_time'    => '' !== $partial_booking['start_time'] ? $partial_booking['start_time'] : $post_time,
						'specialist_id' => $partial_booking['specialist_id'] > 0 ? $partial_booking['specialist_id'] : $post_specialist,
					);
				}

				return array(
					'office_id'     => $post_office,
					'date'          => $date,
					'start_time'    => $post_time,
					'specialist_id' => $post_specialist,
				);
			}

			if ( null === $partial_booking && $has_post_booking_date ) {
				$partial_booking = array(
					'office_id'     => $post_office,
					'date'          => '',
					'start_time'    => $post_time,
					'specialist_id' => $post_specialist,
				);
			}
		}

		return $partial_booking;
	}

	/**
	 * Layer 2 + deferred release: runs AFTER the parent's insert on
	 * woocommerce_checkout_order_processed (priority 9999). Re-counts the slot;
	 * if over capacity, rolls back THIS order's booking (spec 4.15). Then clears
	 * the session context and releases the Layer 1 lock (spec 4.14).
	 *
	 * Implements: spec 4.14, 4.15
	 *
	 * @param int      $order_id    Order ID just created.
	 * @param array    $posted_data Posted checkout data.
	 * @param WC_Order $order       Order object.
	 * @return void
	 */
	public static function post_insert_verify_and_release( $order_id, $posted_data, $order ) {
		if ( ! function_exists( 'WC' ) || ! WC()->session ) {
			return;
		}
		$context = WC()->session->get( self::SESSION_LOCK_CONTEXT );
		if ( ! is_array( $context ) ) {
			return; // No capacity lock held for this request.
		}

		$lock_name = DrPlus_Child_Capacity_Lock_Manager::build_lock_name(
			$context['office_id'],
			$context['date'],
			$context['start_time']
		);

		// Layer 2: re-count after the parent's insert (spec 4.15).
		$count_now = DrPlus_Child_Capacity_Repository::count_distinct_specialists(
			$context['office_id'],
			$context['date'],
			$context['start_time']
		);

		if ( $count_now > (int) $context['capacity'] ) {
			// Compensating rollback — safety net, never fires when Layer 1 works (spec 4.15).
			self::rollback_this_order( (int) $order_id, $context );
		}

		// Clear context and release the lock — always, on every exit path (spec 4.14).
		WC()->session->set( self::SESSION_LOCK_CONTEXT, null );
		DrPlus_Child_Capacity_Lock_Manager::release( $lock_name );
	}

	/**
	 * Compensating rollback: soft-delete THIS order's booking row, log the rollback
	 * with reason 'compensating_rollback', and cancel the WooCommerce order with a
	 * Persian notice (spec 4.15).
	 *
	 * Implements: spec 4.15
	 *
	 * @param int   $order_id Order ID.
	 * @param array $context  Lock context from the session.
	 * @return void
	 */
	protected static function rollback_this_order( int $order_id, array $context ) {
		global $wpdb;
		$table = DrPlus_Child_Capacity_Repository::booking_table();

		$book_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT book_id FROM {$table} WHERE order_id = %d AND deleted = 0 LIMIT 1",
				$order_id
			)
		);

		if ( ! $book_id ) {
			return;
		}

		$wpdb->update(
			$table,
			array( 'deleted' => 1, 'order_status' => 'cancelled' ),
			array( 'book_id' => (int) $book_id ),
			array( '%d', '%s' ),
			array( '%d' )
		);

		DrPlus_Child_Capacity_Logger::log_blocked( array(
			'office_id'      => (string) $context['office_id'],
			'booking_date'   => (string) $context['date'],
			'start_time'     => (string) $context['start_time'],
			'specialist_id'  => isset( $context['specialist_id'] ) ? (int) $context['specialist_id'] : 0,
			'current_count'  => (int) $context['capacity'] + 1,
			'capacity_limit' => (int) $context['capacity'],
			'reason'         => self::ERR_COMPENSATING,
		) );

		$order = wc_get_order( $order_id );
		if ( $order ) {
			$order->update_status( 'cancelled', DrPlus_Child_Capacity_Messages::compensating_rollback() );
		}
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
			if ( ( ! empty( $cart_item['is_booking'] ) || ! empty( $cart_item['book_data'] ) ) && ! empty( $cart_item['book_data'] ) && is_array( $cart_item['book_data'] ) ) {
				return $cart_item['book_data'];
			}
		}
		return null;
	}

	/**
	 * Extract and normalize the booking date.
	 *
	 * Checks `date` first (when already Y-m-d or unix timestamp), then falls back to
	 * `raw_date` (unix timestamp used by Dr Plus when `date` is a localized Jalali string)
	 * and `booking_date`.
	 *
	 * Implements: spec 3.3, Finding 5
	 *
	 * @param array $book Booking payload from cart, session, or POST.
	 * @return string Y-m-d or empty string.
	 */
	protected static function extract_date( $book ) {
		if ( ! is_array( $book ) ) {
			return '';
		}
		foreach ( array( 'date', 'raw_date', 'booking_date' ) as $key ) {
			if ( isset( $book[ $key ] ) && '' !== $book[ $key ] && null !== $book[ $key ] ) {
				$normalized = self::normalize_date_candidate( $book[ $key ] );
				if ( '' !== $normalized ) {
					return $normalized;
				}
			}
		}
		return '';
	}

	/**
	 * Extract and normalize the start time: primary `start_time` (H:i / H:i:s),
	 * fallback `raw_time` / `time` (ms since midnight) (spec 3.3).
	 *
	 * Implements: spec 3.3, Finding 5
	 *
	 * @param array $book Booking payload from cart, session, or POST.
	 * @return string H:i:s or empty string.
	 */
	protected static function extract_start_time( $book ) {
		if ( ! is_array( $book ) ) {
			return '';
		}
		if ( ! empty( $book['start_time'] ) ) {
			$normalized = DrPlus_Child_Capacity_Repository::normalize_time( $book['start_time'] );
			if ( '' !== $normalized ) {
				return $normalized;
			}
		}
		foreach ( array( 'raw_time', 'time', 'booking_time' ) as $key ) {
			if ( isset( $book[ $key ] ) && '' !== (string) $book[ $key ] ) {
				$normalized = self::normalize_time_candidate( $book[ $key ] );
				if ( '' !== $normalized ) {
					return $normalized;
				}
			}
		}
		return '';
	}

	/**
	 * Normalize a raw date candidate (Y-m-d, strtotime-compatible string, or unix timestamp in s/ms).
	 *
	 * Non-Gregorian display strings (e.g. "22 شهریور 1404") return '' so callers can fall back to `raw_date`.
	 *
	 * @param mixed $value Candidate date value.
	 * @return string Y-m-d or empty string.
	 */
	protected static function normalize_date_candidate( $value ): string {
		if ( null === $value || '' === $value || is_array( $value ) || is_object( $value ) ) {
			return '';
		}
		$str = trim( self::normalize_digits( (string) $value ) );
		if ( '' === $str ) {
			return '';
		}
		if ( is_numeric( $str ) ) {
			$ts = (int) $str;
			if ( $ts <= 0 ) {
				return '';
			}
			// Convert millisecond unix timestamps to seconds.
			if ( $ts > 100000000000 ) {
				$ts = (int) floor( $ts / 1000 );
			}
			return DrPlus_Child_Capacity_Repository::normalize_date( $ts );
		}
		return DrPlus_Child_Capacity_Repository::normalize_date( $str );
	}

	/**
	 * Normalize a raw time candidate (H:i[:s] or milliseconds since midnight).
	 *
	 * @param mixed $value Candidate time value.
	 * @return string H:i:s or empty string.
	 */
	protected static function normalize_time_candidate( $value ): string {
		if ( null === $value || '' === $value || is_array( $value ) || is_object( $value ) ) {
			return '';
		}
		$str = trim( self::normalize_digits( (string) $value ) );
		if ( '' === $str ) {
			return '';
		}
		if ( is_numeric( $str ) ) {
			$seconds = (int) floor( ( (int) $str ) / 1000 );
			return gmdate( 'H:i:s', ( ( $seconds % 86400 ) + 86400 ) % 86400 );
		}
		return DrPlus_Child_Capacity_Repository::normalize_time( $str );
	}

	/**
	 * Convert Persian/Arabic numerals to ASCII digits.
	 *
	 * @param string $value Input string.
	 * @return string
	 */
	protected static function normalize_digits( string $value ): string {
		return strtr(
			$value,
			array(
				'۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
				'۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
				'٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
				'٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
			)
		);
	}

	/**
	 * Sanitize a raw text input value safely.
	 *
	 * @param mixed $value Raw input.
	 * @return string
	 */
	protected static function clean_text( $value ): string {
		if ( is_array( $value ) || is_object( $value ) ) {
			return '';
		}
		$raw = function_exists( 'wp_unslash' ) ? wp_unslash( (string) $value ) : (string) $value;
		return function_exists( 'sanitize_text_field' ) ? sanitize_text_field( $raw ) : trim( $raw );
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
	 * @param array  $book          book_data or normalized booking array.
	 * @param int    $count         Current distinct count.
	 * @param int    $capacity      Capacity limit.
	 * @param string $reason        Error code.
	 * @return void
	 */
	protected static function log( $book, $count, $capacity, $reason ) {
		$booking_date = ! empty( $book['date'] ) ? self::normalize_date_candidate( $book['date'] ) : '';
		if ( '' === $booking_date ) {
			$booking_date = self::extract_date( is_array( $book ) ? $book : array() );
		}
		$start_time = ! empty( $book['start_time'] ) ? self::normalize_time_candidate( $book['start_time'] ) : '';
		if ( '' === $start_time ) {
			$start_time = self::extract_start_time( is_array( $book ) ? $book : array() );
		}
		DrPlus_Child_Capacity_Logger::log_blocked( array(
			'office_id'      => isset( $book['office_id'] ) ? (string) $book['office_id'] : '',
			'booking_date'   => $booking_date,
			'start_time'     => $start_time,
			'specialist_id'  => isset( $book['specialist_id'] ) ? (int) $book['specialist_id'] : 0,
			'current_count'  => $count,
			'capacity_limit' => $capacity,
			'reason'         => $reason,
		) );
	}
}

// Layer 1 deferred release + Layer 2 compensating check: fires after the parent's
// booking insert (woocommerce_checkout_order_processed), priority 9999 (spec 4.14, 4.15).
add_action( 'woocommerce_checkout_order_processed', array( 'DrPlus_Child_Capacity_Validator', 'post_insert_verify_and_release' ), 9999, 3 );
