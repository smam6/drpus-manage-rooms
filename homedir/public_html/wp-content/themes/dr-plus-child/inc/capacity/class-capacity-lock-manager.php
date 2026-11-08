<?php
/**
 * Dr Plus Child Theme — Room Capacity Management
 *
 * Implements: spec 4.13, 4.14, 4.15, 4.16, 4.17
 * Category: 4 — Race condition (advisory lock held across the parent's insert)
 *
 * @package DrPlusChild\Capacity
 * @since   1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lock manager: MySQL GET_LOCK/RELEASE_LOCK advisory locks per slot.
 *
 * Single responsibility: acquire/release slot locks. The lock is held from
 * woocommerce_checkout_process until AFTER the parent theme's insert
 * (woocommerce_checkout_order_processed), closing the check-then-act race
 * (audit residual #18, spec 4.13, 4.14).
 */
class DrPlus_Child_Capacity_Lock_Manager {

	/**
	 * Locks currently held in this request.
	 *
	 * Implements: spec 4.14
	 *
	 * @var array<string, string> map of lock_name => lock_name
	 */
	private static $held_locks = array();

	/**
	 * Acquire an advisory lock for the given slot identity (spec 4.13).
	 *
	 * Implements: spec 4.13, 4.16
	 *
	 * @param string $office_id Clinic identifier.
	 * @param string $date      Booking date (Y-m-d).
	 * @param string $start     Start time (H:i:s).
	 * @param int    $timeout   Seconds to wait for the lock (default 5, never more — spec: no timeout > 5s).
	 * @return bool True on success, false on timeout.
	 */
	public static function acquire( string $office_id, string $date, string $start, int $timeout = 5 ): bool {
		global $wpdb;

		$lock_name = self::build_lock_name( $office_id, $date, $start );

		$result = $wpdb->get_var(
			$wpdb->prepare( 'SELECT GET_LOCK(%s, %d)', $lock_name, $timeout )
		);

		if ( '1' === (string) $result ) {
			self::$held_locks[ $lock_name ] = $lock_name;
			return true;
		}

		// 0 = timeout, NULL = error (e.g. MySQL unreachable) (spec 4.6).
		return false;
	}

	/**
	 * Release a specific lock. Safe to call on a lock not held (no-op).
	 *
	 * Implements: spec 4.14
	 *
	 * @param string $lock_name Full lock name.
	 * @return void
	 */
	public static function release( string $lock_name ): void {
		if ( ! isset( self::$held_locks[ $lock_name ] ) ) {
			return;
		}

		global $wpdb;
		$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock_name ) );

		unset( self::$held_locks[ $lock_name ] );
	}

	/**
	 * Release all locks held in this request. Called on `shutdown` (failsafe, spec 4.14).
	 *
	 * Implements: spec 4.14
	 *
	 * @return void
	 */
	public static function release_all(): void {
		foreach ( array_keys( self::$held_locks ) as $lock_name ) {
			self::release( $lock_name );
		}
	}

	/**
	 * Build a lock name from a slot identity. MySQL lock names must be <= 64 chars,
	 * so we use md5 — direct concatenation is forbidden (spec 4.16).
	 *
	 * Implements: spec 4.16
	 *
	 * @param string $office_id Clinic identifier.
	 * @param string $date      Booking date.
	 * @param string $start     Start time.
	 * @return string 44-char lock name ('drplus_cap_' . md5).
	 */
	public static function build_lock_name( string $office_id, string $date, string $start ): string {
		return 'drplus_cap_' . md5( $office_id . '|' . $date . '|' . $start );
	}

	/**
	 * Return the lock name currently held for a slot, if any.
	 *
	 * Implements: spec 4.14
	 *
	 * @param string $office_id Clinic identifier.
	 * @param string $date      Booking date.
	 * @param string $start     Start time.
	 * @return string|null
	 */
	public static function held_for( string $office_id, string $date, string $start ): ?string {
		$lock_name = self::build_lock_name( $office_id, $date, $start );
		return isset( self::$held_locks[ $lock_name ] ) ? $lock_name : null;
	}
}

// Failsafe: release any lock still held at request shutdown (spec 4.14).
add_action( 'shutdown', array( 'DrPlus_Child_Capacity_Lock_Manager', 'release_all' ), 1 );
