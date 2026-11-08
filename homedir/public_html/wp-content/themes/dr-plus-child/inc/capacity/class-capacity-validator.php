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
	const ERR_CAPACITY = 'capacity_exceeded';
	const ERR_MISSING_DATE = 'missing_date';
	const ERR_COMPENSATING = 'compensating_rollback';

	/**
	 * WC session key holding the deferred lock context (spec 4.14).
	 */
	const SESSION_LOCK_CONTEXT = 'drplus_capacity_lock_context';

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
	 * Layer 1: acquires the slot advisory lock BEFORE counting and holds it across the
	 * parent's insert (deferred release in post_insert_verify_and_release, spec 4.13, 4.14).
	 * On rejection the lock is released immediately.
	 *
	 * Implements: spec 3.1-3.11, 3.14, 3.15, 3.16, 4.13, 4.14, 4.17
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

		// Layer 1: acquire the global advisory lock BEFORE counting (spec 4.13). Timeout 5s max.
		$acquired = DrPlus_Child_Capacity_Lock_Manager::acquire( $office_id, $date, $start_time, 5 );
		if ( ! $acquired ) {
			self::log( $book, 0, $capacity, DrPlus_Child_Capacity_Repository::ERR_LOCK_FAILED );
			wc_add_notice( DrPlus_Child_Capacity_Messages::system_busy(), 'error' );
			return;
		}

		// Lock is now held. It is released either here (rejection) or deferred to
		// post_insert_verify_and_release (after the parent's INSERT) — never before (spec 4.14).
		$count = DrPlus_Child_Capacity_Repository::count_distinct_specialists( $office_id, $date, $start_time );

		if ( $count >= $capacity ) {
			// Reject and release immediately (spec 4.14).
			self::log( $book, $count, $capacity, self::ERR_CAPACITY );
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

// Layer 1 deferred release + Layer 2 compensating check: fires after the parent's
// booking insert (woocommerce_checkout_order_processed), priority 9999 (spec 4.14, 4.15).
add_action( 'woocommerce_checkout_order_processed', array( 'DrPlus_Child_Capacity_Validator', 'post_insert_verify_and_release' ), 9999, 3 );
