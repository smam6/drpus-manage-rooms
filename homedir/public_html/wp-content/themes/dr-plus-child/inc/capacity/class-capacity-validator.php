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
	const ERR_MISSING_TIME = 'missing_time';
	const ERR_COMPENSATING = 'compensating_rollback';

	/**
	 * WC session key holding the deferred lock context (spec 4.14).
	 */
	const SESSION_LOCK_CONTEXT = 'drplus_capacity_lock_context';

	/**
	 * Set once this request has rejected the checkout on capacity grounds, so a repeated
	 * woocommerce_checkout_process pass cannot re-count without the lock (spec 4.14).
	 *
	 * @var bool
	 */
	protected static $capacity_rejected = false;

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

		// Guard against a second capacity pass in the same request.
		// WordPress can fire woocommerce_checkout_process more than once (some gateways and
		// plugins re-run validation). A rejection released the lock, so a second pass would
		// re-count WITHOUT the lock and could accept the order — the exact check-then-act race
		// the lock exists to prevent.
		if ( self::$capacity_rejected ) {
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

		// Fail closed when the booked hour cannot be resolved.
		// An empty H:i:s would be bound as `start_time = ''`, which matches no row, so the count
		// would be 0 and an in-person booking on a FULL hour would be accepted (capacity bypass).
		if ( '' === $start_time ) {
			self::log( $booking, 0, 0, self::ERR_MISSING_TIME );
			wc_add_notice( DrPlus_Child_Capacity_Messages::missing_time(), 'error' );
			return;
		}

		$capacity = DrPlus_Child_Capacity_Service::capacity_for( $office_id );

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
			self::$capacity_rejected = true;
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
	 * Extract and normalize the booking date for the SQL query.
	 *
	 * `raw_date` (unix timestamp) comes FIRST and is authoritative, because the localized `date`
	 * key is a Persian/Jalali display string (`28 آبان 1405`) or a Jalali numeric string
	 * (`1405-08-28`). The latter is accepted by `DateTime::createFromFormat( 'Y-m-d', ... )` and by
	 * MySQL as a syntactically valid date, so trusting it makes the COUNT query look 300+ years in
	 * the future and always return 0 — the capacity bypass (Matrix C).
	 *
	 * Fallback order: `raw_date` → `date` (only when it is Gregorian) → `booking_date`.
	 *
	 * Implements: spec 3.3, Finding 5, Matrix C
	 *
	 * @param array $book Booking payload from cart, session, or POST.
	 * @return string Y-m-d or empty string.
	 */
	protected static function extract_date( $book ) {
		if ( ! is_array( $book ) ) {
			return '';
		}
		// 1. raw_date (unix timestamp) — never localized, always Gregorian-safe.
		if ( isset( $book['raw_date'] ) && '' !== $book['raw_date'] && null !== $book['raw_date'] ) {
			$normalized = self::normalize_timestamp_candidate( $book['raw_date'] );
			if ( '' !== $normalized ) {
				return $normalized;
			}
		}
		// 2. date — accepted only when it is a real Gregorian Y-m-d.
		// A Jalali triple (1405-08-28, 1405/08/28) is explicitly refused here so the caller sees
		// '' and raises the "select a date" error, instead of silently falling through to a
		// generic path that could bind the Jalali string to SQL.
		if ( isset( $book['date'] ) && '' !== $book['date'] && null !== $book['date'] ) {
			if ( self::looks_jalali( $book['date'] ) ) {
				return '';
			}
			$normalized = self::normalize_date_candidate( $book['date'] );
			if ( '' !== $normalized ) {
				return $normalized;
			}
		}
		// 3. Remaining unambiguous candidates.
		if ( isset( $book['booking_date'] ) && '' !== $book['booking_date'] && null !== $book['booking_date'] ) {
			$normalized = self::normalize_date_candidate( $book['booking_date'] );
			if ( '' !== $normalized ) {
				return $normalized;
			}
		}
		return '';
	}

	/**
	 * Extract and normalize the start time for the SQL query.
	 *
	 * `raw_time` (milliseconds since midnight) comes FIRST: it maps 1:1 onto the stored `TIME`
	 * column (`gmdate( 'H:i:s', ms / 1000 )`, verified against all 19 live rows in the SQL dump).
	 * `start_time` is only a fallback because it is a display value that can carry a localized
	 * format, whereas the DB always stores `HH:MM:SS`.
	 *
	 * Implements: spec 3.3, Finding 5, Matrix B
	 *
	 * @param array $book Booking payload from cart, session, or POST.
	 * @return string H:i:s or empty string.
	 */
	protected static function extract_start_time( $book ) {
		if ( ! is_array( $book ) ) {
			return '';
		}
		// 1. raw_time (ms since midnight) — matches the stored TIME column exactly.
		if ( isset( $book['raw_time'] ) && '' !== (string) $book['raw_time'] ) {
			$normalized = self::normalize_time_candidate( $book['raw_time'] );
			if ( '' !== $normalized ) {
				return $normalized;
			}
		}
		// 2. time / start_time / booking_time — normalized to HH:MM:SS before the query (Matrix B).
		foreach ( array( 'start_time', 'time', 'booking_time' ) as $key ) {
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
	 * Normalize a date candidate (Gregorian Y-m-d, unambiguous textual date, or unix timestamp in s/ms).
	 *
	 * Jalali/Gregorian guard (Matrix C): non-Gregorian display strings ("22 شهریور 1404") and Jalali
	 * numeric strings ("1405-08-28") return '' so callers fall back to `raw_date` instead of
	 * querying the database with a year-1405 date.
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
			return self::normalize_timestamp_candidate( $str );
		}
		return DrPlus_Child_Capacity_Repository::normalize_date( $str );
	}

	/**
	 * Does this value look like a Jalali (Solar Hijri) calendar date?
	 *
	 * Detection only — this never converts. A Jalali date must be refused, never translated,
	 * because a wrong conversion is indistinguishable from a working one until bookings land on
	 * the wrong day. The legitimate source of truth is `raw_date`; callers fall back to it.
	 *
	 * Jalali years are currently 14xx, so a leading 3-4 digit year outside [1900, 2200] in a
	 * year/month/day triple is the reliable signal. Persian digits are normalized first: a
	 * `۱۴۰۵-۰۸-۲۸` string must be caught too.
	 *
	 * Written in PHP 7.4 syntax: `str_starts_with()` is PHP 8.0+ and this theme declares
	 * `Requires PHP: 7.4`. Note also that no fixed-length check is used — `1405-8-28` (9 chars)
	 * and `1405/08/28` (separator variant) must both be caught.
	 *
	 * @param mixed $value Candidate date value.
	 * @return bool
	 */
	protected static function looks_jalali( $value ): bool {
		if ( null === $value || is_array( $value ) || is_object( $value ) ) {
			return false;
		}
		$str = trim( self::normalize_digits( (string) $value ) );
		if ( '' === $str || ! preg_match( '/^(\d{3,4})[-\/.]\d{1,2}[-\/.]\d{1,2}/', $str, $m ) ) {
			return false;
		}
		$year = (int) $m[1];
		return $year < 1900 || $year > 2200;
	}

	/**
	 * Normalize a unix timestamp candidate (raw_date in seconds, or milliseconds) to a Y-m-d.
	 *
	 * @param mixed $value Candidate timestamp.
	 * @return string Y-m-d or empty string.
	 */
	protected static function normalize_timestamp_candidate( $value ): string {
		if ( null === $value || '' === $value || is_array( $value ) || is_object( $value ) ) {
			return '';
		}
		$str = trim( self::normalize_digits( (string) $value ) );
		if ( '' === $str || ! is_numeric( $str ) ) {
			return '';
		}
		$ts = (int) $str;
		if ( $ts <= 0 ) {
			return '';
		}
		// Convert millisecond unix timestamps to seconds.
		if ( $ts > 100000000000 ) {
			$ts = (int) floor( $ts / 1000 );
		}
		return DrPlus_Child_Capacity_Repository::normalize_date( $ts, true );
	}

	/**
	 * Normalize a time candidate to HH:MM:SS.
	 *
	 * Numeric values are milliseconds since midnight (`raw_time`). Text values are parsed first so
	 * `15:00` becomes `15:00:00` before it reaches the SQL query (Matrix B).
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
			// Delegates to the single source of truth for ms-since-midnight (`raw_time`).
			// Only a whole-second value inside one day qualifies: "1405" (a Jalali year or a
			// stray counter) must never become 00:00:01.
			return DrPlus_Child_Capacity_Repository::normalize_time( (int) $str );
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
