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
	const ERR_SPECIALIST_CONFLICT = 'specialist_conflict';

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
		DrPlus_Child_Capacity_Logger::diag( 'check_missing_date() ENTERED (priority 4)' );
		$booking = self::extract_booking_from_request();
		if ( null === $booking ) {
			DrPlus_Child_Capacity_Logger::diag( 'check_missing_date: not a booking order' );
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
		DrPlus_Child_Capacity_Logger::diag( 'validate_capacity() ENTERED (woocommerce_checkout_process fired)' );

		if ( ! DrPlus_Child_Capacity_Service::enabled() ) {
			DrPlus_Child_Capacity_Logger::diag( 'EXIT: module disabled by filter drplus_child_capacity/enabled' );
			return;
		}

		// Guard against a second capacity pass in the same request.
		// WordPress can fire woocommerce_checkout_process more than once (some gateways and
		// plugins re-run validation). A rejection released the lock, so a second pass would
		// re-count WITHOUT the lock and could accept the order — the exact check-then-act race
		// the lock exists to prevent.
		if ( self::$capacity_rejected ) {
			DrPlus_Child_Capacity_Logger::diag( 'EXIT: already rejected earlier in this request (allowing a 2nd pass would re-count unlocked)' );
			return;
		}

		$booking = self::extract_booking_from_request();
		if ( null === $booking ) {
			DrPlus_Child_Capacity_Logger::diag( 'EXIT: extract_booking_from_request() returned NULL — this is NOT recognised as a booking cart/POST. Capacity will not be checked.' );
			return; // Not a booking, or already reported by the priority-4 date check (spec 3.6).
		}
		if ( empty( $booking['date'] ) ) {
			DrPlus_Child_Capacity_Logger::diag( 'EXIT: booking found but DATE is empty (Jalali rejected or raw_date missing) — the missing-date notice already fired.' );
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
		DrPlus_Child_Capacity_Logger::diag( 'validate_booking_capacity() payload: ' . wp_json_encode( $booking ) );

		if ( ! DrPlus_Child_Capacity_Service::enabled() ) {
			DrPlus_Child_Capacity_Logger::diag( 'EXIT: module disabled' );
			return;
		}
		$office_id = isset( $booking['office_id'] ) ? (string) $booking['office_id'] : '';
		if ( '' === $office_id ) {
			DrPlus_Child_Capacity_Logger::diag( 'EXIT: office_id is EMPTY — cannot identify a clinic, so no capacity check. THIS IS A FAIL-OPEN PATH.' );
			return;
		}
		// Visit type is inferred from office_id — book_data has no visit_type key (spec 3.4, Finding 4).
		$is_online = DrPlus_Child_Capacity_Service::is_online_visit( $office_id );
		DrPlus_Child_Capacity_Logger::diag( sprintf(
			'VALIDATOR: office=%s date=%s time=%s specialist=%d is_online=%s conflict_mode=%s',
			$office_id, (string) $booking['date'], (string) ( $booking['start_time'] ?? '' ),
			(int) ( $booking['specialist_id'] ?? 0 ), $is_online ? 'yes' : 'no',
			DrPlus_Child_Capacity_Service::conflict_mode()
		) );

		if ( $is_online && ! DrPlus_Child_Capacity_Service::virtual_visit_blocks_room() ) {
			// Default 'room' mode: an online visit occupies no room, so room capacity cannot
			// constrain it, and it must never be refused for "the clinic is full".
			DrPlus_Child_Capacity_Logger::diag( 'EXIT: office_id "' . $office_id . '" is treated as an ONLINE visit (exempt by design). If this was an in-person booking, the office_id is wrong.' );
			return; // Online visits exempt (spec 3.5).
		}

		// 'any' mode reaches here for an online visit ON PURPOSE: the specialist is one person,
		// so a room booking at 17:00 must stop a phone consultation at 17:00. Only the
		// per-specialist conflict is checked below — room CAPACITY never applies to an online
		// visit, because it does not consume a room.
		if ( $is_online ) {
			DrPlus_Child_Capacity_Logger::diag( 'conflict_mode=any: ONLINE visit "' . $office_id . '" still subject to the per-specialist conflict check (room capacity does not apply).' );
		}
		$date       = (string) $booking['date'];
		$start_time = isset( $booking['start_time'] ) ? (string) $booking['start_time'] : '';
		$specialist = isset( $booking['specialist_id'] ) ? (int) $booking['specialist_id'] : 0;

		// Is this id the customer's choice, or did we borrow it from a session that may be
		// stale? Set by rescue_specialist(); absent on every normal path.
		$specialist_inferred = ! empty( $booking['specialist_id_is_inferred'] );

		// Bug D: the DB column is TIME, so `14:00` must become `14:00:00` or the comparison
		// silently matches nothing and the hour reads as empty. extract_start_time() already
		// normalizes, but re-asserting here keeps the guarantee at the point of use: a future
		// caller passing an un-normalized value gets a rejection, not a quiet bypass.
		$start_time = DrPlus_Child_Capacity_Repository::normalize_time( $start_time );

		// Fail closed when the booked hour cannot be resolved.
		// An empty H:i:s would be bound as `start_time = ''`, which matches no row, so the count
		// would be 0 and an in-person booking on a FULL hour would be accepted (capacity bypass).
		if ( '' === $start_time ) {
			DrPlus_Child_Capacity_Logger::diag( 'REJECT: start_time could not be resolved — refusing (fail-closed). raw_time=' . ( isset( $booking['raw_time'] ) ? wp_json_encode( $booking['raw_time'] ) : 'MISSING' ) );
			self::log( $booking, 0, 0, self::ERR_MISSING_TIME );
			wc_add_notice( DrPlus_Child_Capacity_Messages::missing_time(), 'error' );
			return;
		}

		$capacity = DrPlus_Child_Capacity_Service::capacity_for( $office_id );

		// Bug C: a specialist cannot be in two rooms at once. This is independent of room
		// capacity — two DIFFERENT specialists fill a 2-room hour and raise the count, but the
		// SAME specialist booking twice leaves the distinct count at 1, so the room check alone
		// never sees it. Runs before the room count so the clearer message wins.
		if ( DrPlus_Child_Capacity_Repository::specialist_already_booked( $specialist, $date, $start_time ) ) {
			// The guard also refuses when it CANNOT VERIFY (missing specialist id, unusable
			// date, unusable time). Those must not be reported as a conflict: there is no
			// booking to point at, and claiming one exists is what sent the customer hunting
			// for a phantom appointment. Ask the repository which input failed.
			$unverifiable = DrPlus_Child_Capacity_Repository::last_unverifiable_reason();
			if ( '' !== $unverifiable ) {
				DrPlus_Child_Capacity_Logger::diag( sprintf(
					'REJECT: cannot verify the slot (missing %s) — refusing. specialist=%d date=%s time=%s office=%s',
					$unverifiable,
					$specialist,
					$date,
					$start_time,
					$office_id
				) );
				self::log( $booking, 0, $capacity, self::ERR_SPECIALIST_CONFLICT );
				// A missing specialist means the page lost track of WHO is being booked, which
				// the customer fixes by going back a step — not by picking another hour, which
				// would fail identically for every hour.
				wc_add_notice(
					'specialist' === $unverifiable
						? DrPlus_Child_Capacity_Messages::missing_specialist()
						: DrPlus_Child_Capacity_Messages::unverifiable_slot(),
					'error'
				);
				return;
			}

			// Distinguish "booked at this clinic" from "booked elsewhere at the same hour".
			// Both must be refused — a specialist cannot be in two places at once — but the
			// customer experience differs: in the second case the hour may well still be
			// visible in this clinic's grid (the parent's grid and our filter are both scoped
			// per clinic), so the message must explain that the SPECIALIST is the constraint,
			// not the room, or the customer just sees a slot that refuses to be booked.
			$same_clinic = DrPlus_Child_Capacity_Repository::specialist_conflict_in_scope( $specialist, $date, $start_time, $office_id );

			// THE SPECIALIST ID WAS A GUESS. DO NOT ACCUSE THAT SPECIALIST.
			//
			// When the cart did not name a specialist, rescue_specialist() borrowed one from
			// the parent (a `sid` in the URL, or — far more often — a leftover
			// `$_SESSION['booking']['specialist_id']`). If that borrowed id happens to hold
			// this hour, the check above fires and the customer is told the specialist they
			// chose is already booked. They chose someone else. Nothing about the message is
			// true for them, and no hour they pick will work, because the stale id keeps
			// matching whatever that other doctor has on their calendar.
			//
			// We cannot simply skip the check: the borrowed id is also our only defence
			// against a real double-booking when the cart is genuinely missing the field. The
			// honest answer is neither "conflict" nor "fine" — it is "we could not tell who
			// you are booking". That message sends the customer back one step (where the
			// specialist is chosen), which actually fixes the state, instead of making them
			// hunt for a phantom appointment in another doctor's calendar.
			if ( $specialist_inferred ) {
				DrPlus_Child_Capacity_Logger::diag( sprintf(
					'REJECT: conflict on INFERRED specialist %d at %s %s (same_clinic=%s) — refusing as UNVERIFIABLE, not as a conflict, because the cart never named this specialist',
					$specialist,
					$date,
					$start_time,
					$same_clinic ? 'yes' : 'no'
				) );
				self::log( $booking, 0, $capacity, self::ERR_SPECIALIST_CONFLICT );
				wc_add_notice( DrPlus_Child_Capacity_Messages::missing_specialist(), 'error' );
				return;
			}

			DrPlus_Child_Capacity_Logger::diag( sprintf(
				'REJECT: specialist %d already booked at %s %s (same_clinic=%s)',
				$specialist,
				$date,
				$start_time,
				$same_clinic ? 'yes' : 'no — booked at another clinic'
			) );
			self::log( $booking, 0, $capacity, self::ERR_SPECIALIST_CONFLICT );
			wc_add_notice(
				$same_clinic
					? DrPlus_Child_Capacity_Messages::specialist_conflict()
					: DrPlus_Child_Capacity_Messages::specialist_conflict_other_clinic(),
				'error'
			);
			return;
		}

		// Layer 1: acquire the global advisory lock BEFORE counting (spec 4.13). Timeout 5s max.
		$acquired = DrPlus_Child_Capacity_Lock_Manager::acquire( $office_id, $date, $start_time, 5 );
		if ( ! $acquired ) {
			DrPlus_Child_Capacity_Logger::diag( 'REJECT: could not acquire lock ' . DrPlus_Child_Capacity_Lock_Manager::build_lock_name( $office_id, $date, $start_time ) );
			self::log( $booking, 0, $capacity, DrPlus_Child_Capacity_Repository::ERR_LOCK_FAILED );
			wc_add_notice( DrPlus_Child_Capacity_Messages::system_busy(), 'error' );
			return;
		}

		// Lock is now held. It is released either here (rejection) or deferred to
		// post_insert_verify_and_release (after the parent's INSERT) — never before (spec 4.14).
		// An ONLINE visit consumes no room, so room capacity must never refuse it — even in
		// 'any' mode, where it is still subject to the per-specialist conflict check above.
		// Counting it here would reject a phone consultation because "the clinic is full",
		// which is both wrong and impossible for the customer to understand.
		$count = $is_online
			? 0
			: DrPlus_Child_Capacity_Repository::count_distinct_specialists( $office_id, $date, $start_time );

		DrPlus_Child_Capacity_Logger::diag( sprintf(
			'CAPACITY CHECK -> office=%s date=%s time=%s capacity=%d current_count=%d verdict=%s',
			$office_id,
			$date,
			$start_time,
			$capacity,
			$count,
			( $count >= $capacity ? 'REJECT' : 'ALLOW' )
		) );

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
			DrPlus_Child_Capacity_Logger::diag( 'extract: cart has ' . ( is_iterable( $cart_items ) ? count( (array) $cart_items ) : 0 ) . ' item(s)' );
			if ( is_iterable( $cart_items ) ) {
				foreach ( $cart_items as $cart_item ) {
					if ( ! is_array( $cart_item ) ) {
						continue;
					}
					DrPlus_Child_Capacity_Logger::diag( 'extract: cart item keys=[' . implode( ',', array_keys( $cart_item ) ) . '] is_booking=' . ( empty( $cart_item['is_booking'] ) ? 'no' : 'yes' ) . ' has_book_data=' . ( empty( $cart_item['book_data'] ) ? 'no' : 'yes' ) );
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
							DrPlus_Child_Capacity_Logger::diag( 'SOURCE A (cart): date resolved -> ' . $date );
							// Source D lives here too, not only after Source C: this early return is
							// the NORMAL path when the cart carries a date, so a cart item without
							// `specialist_id` used to leave this function with 0 and never reach
							// any rescue. That is the exact shape of the reported bug.
							return self::rescue_specialist( $booking, 'A (cart)' );
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

			// THE CART ALREADY NAMED THE SPECIALIST. THE SESSION MUST NOT OVERRULE IT.
			//
			// A cart item without a date still yields a `partial_booking` holding the
			// specialist the customer actually put in the basket. The merge below prefers the
			// session's id only when the cart has none, which is right — but if the two
			// DISAGREE, the cart is the newer, customer-authored fact and the session is the
			// leftover. Trusting the session there is the same false-conflict bug in a rarer
			// disguise, so it is refused outright and logged.
			$session_specialist = isset( $session_data['specialist_id'] ) ? (int) $session_data['specialist_id'] : 0;
			if ( null !== $partial_booking
				&& (int) $partial_booking['specialist_id'] > 0
				&& $session_specialist > 0
				&& (int) $partial_booking['specialist_id'] !== $session_specialist ) {
				DrPlus_Child_Capacity_Logger::diag( sprintf(
					'SOURCE B: session specialist %d DISAGREES with cart specialist %d — trusting the cart (the customer put it there)',
					$session_specialist,
					(int) $partial_booking['specialist_id']
				) );
				$session_specialist = 0; // Never used for the conflict check.
			}

			if ( '' !== $date ) {
				$office_id     = isset( $session_data['office_id'] )
					? (string) $session_data['office_id']
					: ( isset( $session_data['office'] ) ? (string) $session_data['office'] : '' );
				$start_time    = self::extract_start_time( $session_data );
				$specialist_id = $session_specialist;

				// THE SESSION IS A CACHE OF A CHOICE THE CUSTOMER MAY HAVE ALREADY CHANGED.
				//
				// `$_SESSION['booking']` is written by the booking wizard and is only refreshed
				// when the wizard runs again. Going back one step and picking a different doctor,
				// then jumping straight to checkout, leaves the OLD doctor in this array while the
				// customer books the NEW one. The parent resolves the same array in
				// Booking::get_specialist(), so the order really is for the new doctor — but this
				// validator would check the old one and refuse the hour with «این متخصص در این
				// ساعت قبلاً رزرو دارد», a conflict in a calendar the customer is not using.
				//
				// A cart item, when present, carries what the customer actually put in the basket
				// and outranks the session. When the session is used on its own we cannot prove it
				// still reflects the current choice, so the id is marked INFERRED: the room count
				// may use it (a guess is fine for "is this clinic full"), but a CONFLICT will not
				// be reported against it (see validate_booking_capacity).

				if ( null !== $partial_booking ) {
					DrPlus_Child_Capacity_Logger::diag( 'SOURCE B (session, merged with partial cart): date resolved -> ' . $date );
					return self::rescue_specialist( array(
						'office_id'     => '' !== $partial_booking['office_id'] ? $partial_booking['office_id'] : $office_id,
						'date'          => $date,
						'start_time'    => '' !== $partial_booking['start_time'] ? $partial_booking['start_time'] : $start_time,
						'specialist_id' => $partial_booking['specialist_id'] > 0 ? $partial_booking['specialist_id'] : $specialist_id,
					), 'B (session)' );
				}

				return self::rescue_specialist( array(
					'office_id'     => $office_id,
					'date'          => $date,
					'start_time'    => $start_time,
					'specialist_id' => $specialist_id,
				), 'B (session)', true );
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
					return self::rescue_specialist( array(
						'office_id'     => '' !== $partial_booking['office_id'] ? $partial_booking['office_id'] : $post_office,
						'date'          => $date,
						'start_time'    => '' !== $partial_booking['start_time'] ? $partial_booking['start_time'] : $post_time,
						'specialist_id' => $partial_booking['specialist_id'] > 0 ? $partial_booking['specialist_id'] : $post_specialist,
					), 'C (POST)' );
				}

				DrPlus_Child_Capacity_Logger::diag( 'SOURCE C (POST): date resolved -> ' . $date );
				return self::rescue_specialist( array(
					'office_id'     => $post_office,
					'date'          => $date,
					'start_time'    => $post_time,
					'specialist_id' => $post_specialist,
				), 'C (POST)' );
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

		DrPlus_Child_Capacity_Logger::diag( 'extract RESULT: ' . ( null === $partial_booking ? 'NULL (no booking source matched)' : wp_json_encode( $partial_booking ) ) );
		return $partial_booking;
	}

	/**
	 * Fill in a missing specialist id before the booking leaves extraction.
	 *
	 * WHY EVERY RETURN ROUTES THROUGH HERE (the "booked when it is not" bug)
	 *
	 * Every source above can legitimately fail to find a specialist id: a cart item kept from
	 * an older session has no `specialist_id` key, a POST body omits the hidden field, or the
	 * field arrives as "abc". The extracted booking then carries specialist_id = 0.
	 *
	 * Downstream, 0 is not "unknown" — specialist_already_booked() refuses it as unverifiable.
	 * The customer was told «این متخصص در این ساعت قبلاً رزرو دارد» for an hour with literally
	 * zero bookings, and EVERY slot failed the same way, so the whole day looked booked.
	 *
	 * This helper must sit on ALL FOUR return paths. An earlier attempt placed it only after
	 * Source C, which never ran for the common case (cart with a date, no specialist id),
	 * because Source A returns early. The bug survived that attempt; the placement is the fix.
	 *
	 * Failing closed on a missing id is still right — but only when the id truly cannot be
	 * learned. The parent already knows the specialist, so ask it first.
	 *
	 * @param array  $booking Extracted booking.
	 * @param string $source  Which source produced it, for the log.
	 * @return array Booking with specialist_id recovered when possible.
	 */
	protected static function rescue_specialist( array $booking, $source, $from_session = false ) {
		if ( isset( $booking['specialist_id'] ) && (int) $booking['specialist_id'] > 0 ) {
			if ( $from_session ) {
				// The id came from the session, which survives a change of mind. Keep it, but
				// record that it is not a proof of who is being booked right now.
				$booking['specialist_id_is_inferred'] = true;
				DrPlus_Child_Capacity_Logger::diag( 'SOURCE B: specialist_id=' . (int) $booking['specialist_id'] . ' taken from the session — marked INFERRED (a session value can lag behind the customer\'s current choice)' );
			}
			return $booking; // Nothing to rescue.
		}

		$rescued = self::resolve_specialist_from_parent();
		if ( $rescued <= 0 ) {
			DrPlus_Child_Capacity_Logger::diag( 'SOURCE D: specialist_id still 0 after ' . $source . ' — no sid, no booking session. The guard will refuse and say so (missing_specialist).' );
			return $booking;
		}

		// A GUESSED SPECIALIST MUST NOT RAISE A CONFLICT ERROR ABOUT A REAL BOOKING.
		//
		// WHAT WENT WRONG
		//
		// The cart owns the authority on who is being booked. When a cart item arrives without
		// `specialist_id`, this rescue borrows the id from the parent — which reads
		// `$_GET['sid']` and, failing that, `$_SESSION['booking']['specialist_id']`. That
		// session value is a leftover: it survives the moment the customer goes back and picks
		// a different doctor, and the parent only overwrites it on the steps that run the
		// booking wizard. During checkout it is frequently the PREVIOUS choice.
		//
		// The result was the reported bug, reproduced end-to-end: the customer selects
		// specialist 13 (completely free), the stale session still says 15, 15 happens to hold
		// 10:00, and checkout refuses with «این متخصص در این ساعت قبلاً رزرو دارد» — naming a
		// conflict that belongs to a doctor the customer is not booking. From the outside the
		// chosen specialist has no booking at all, which is exactly what the operator reported.
		//
		// WHY THE FIX IS NOT "STOP RESCUING"
		//
		// Some real themes/plugins do drop `specialist_id` from the cart payload, and without
		// any id the validator refuses every hour with missing_specialist() — a worse
		// experience than a wrong conflict (every slot of every day becomes unbookable). So the
		// rescue stays, but it is now marked as what it is: a GUESS. The conflict check below
		// honours the guess for the room count (a guess is fine for "is the clinic full") but
		// refuses to attribute a booking to that specialist when the guess is unverified.
		$booking['specialist_id']            = $rescued;
		$booking['specialist_id_is_inferred'] = true;
		DrPlus_Child_Capacity_Logger::diag( 'SOURCE D: recovered specialist_id=' . $rescued . ' from the parent while reading ' . $source . ' — marked INFERRED (the cart did not name a specialist)' );

		return $booking;
	}

	/**
	 * Ask the parent theme which specialist this booking is for.
	 *
	 * Reads the same two places the parent reads (utils-booking.php:137-143): the `sid` query
	 * argument and the booking session. Deliberately does NOT invent an id from the cart,
	 * because a cart may hold several bookings.
	 *
	 * @return int Specialist id, or 0 when genuinely unknown.
	 */
	protected static function resolve_specialist_from_parent() {
		// The parent's own accessor, when the theme is loaded. Wrapped because this module
		// must stay loadable outside a full WordPress request (unit tests, CLI).
		if ( class_exists( 'DrPlus\\Utils\\Booking' ) && method_exists( 'DrPlus\\Utils\\Booking', 'get_specialist' ) ) {
			$from_parent = \DrPlus\Utils\Booking::get_specialist( true );
			if ( (int) $from_parent > 0 ) {
				return (int) $from_parent;
			}
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only id lookup.
		if ( isset( $_GET['sid'] ) && ! is_array( $_GET['sid'] ) ) {
			$sid = (int) self::clean_text( $_GET['sid'] );
			if ( $sid > 0 ) {
				return $sid;
			}
		}

		if ( isset( $_SESSION['booking']['specialist_id'] ) ) {
			$sid = (int) $_SESSION['booking']['specialist_id'];
			if ( $sid > 0 ) {
				return $sid;
			}
		}

		return 0;
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
			DrPlus_Child_Capacity_Logger::diag( 'LAYER 2 (order_processed): NO lock context in session — validate_capacity() never reached the lock. Layer 2 has nothing to verify.' );
			return; // No capacity lock held for this request.
		}
		DrPlus_Child_Capacity_Logger::diag( 'LAYER 2 (order_processed): order #' . (int) $order_id . ' context=' . wp_json_encode( $context ) );

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

		DrPlus_Child_Capacity_Logger::diag( 'LAYER 2: re-count=' . $count_now . ' capacity=' . (int) $context['capacity'] . ' verdict=' . ( $count_now > (int) $context['capacity'] ? 'ROLLBACK' : 'KEEP' ) );

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
			// A BARE 8-DIGIT STRING IS A COMPACT CALENDAR DATE, NOT A UNIX TIMESTAMP.
			//
			// `20271201` is_numeric() and reached normalize_timestamp_candidate(), which
			// read it as 20,271,201 seconds after the epoch — 1970-08-23. A booking for
			// 2027-12-01 was therefore validated, counted and stored against a date in
			// 1970, which is in the past and consequently never blocks anything: the
			// capacity check silently passed for every such request.
			//
			// The ambiguity is real — 20271201 is both a plausible YYYYMMDD and a valid
			// (absurd) epoch — but only one reading can be a booking date a customer
			// meant. A 1970 timestamp is not a bookable appointment, so YYYYMMDD wins
			// whenever the value is exactly eight digits AND parses to a real calendar
			// date. If it does not parse (e.g. 20271301), we fall through to the
			// timestamp path and let normalisation decide.
			if ( 8 === strlen( $str ) && ctype_digit( $str ) ) {
				$compact = DrPlus_Child_Capacity_Repository::normalize_date( $str, false );
				if ( '' !== $compact ) {
					return $compact;
				}
			}
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
