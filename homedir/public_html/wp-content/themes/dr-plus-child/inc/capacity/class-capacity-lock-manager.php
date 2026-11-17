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
	 * Memoised answer to "does this engine have GET_LOCK()?" (probed once per request).
	 *
	 * @var bool|null
	 */
	private static $supports_advisory = null;

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
		$lock_name = self::build_lock_name( $office_id, $date, $start );

		$ok = self::engine_supports_advisory_locks()
			? self::acquire_advisory( $lock_name, $timeout )
			: self::acquire_row( $lock_name, $timeout );

		if ( $ok ) {
			self::$held_locks[ $lock_name ] = $lock_name;
			return true;
		}

		// Timeout or error (spec 4.6).
		return false;
	}

	/**
	 * Does this database engine implement MySQL server-side advisory locks?
	 *
	 * GET_LOCK() is a MySQL/MariaDB function. Any other backend (SQLite through
	 * the official driver, or a future engine) either errors or, worse, silently
	 * returns something that is not '1' — which used to make every checkout fail
	 * with "system busy" while the lock itself was never held.
	 *
	 * @return bool
	 */
	protected static function engine_supports_advisory_locks(): bool {
		global $wpdb;

		if ( isset( self::$supports_advisory ) ) {
			return self::$supports_advisory;
		}

		self::$supports_advisory = false;

		// A driver that declares itself as something other than MySQL is decisive.
		if ( $wpdb instanceof WP_SQLite_DB ) {
			return self::$supports_advisory;
		}

		$version = (string) $wpdb->db_version();
		if ( false !== stripos( $version, 'sqlite' ) ) {
			return self::$supports_advisory;
		}

		// Prove it: acquire+release a throwaway lock and require the literal '1'.
		$probe = self::build_lock_name( 'probe', (string) wp_rand(), (string) time() );
		$got   = $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, %d)', $probe, 0 ) );

		if ( '1' === (string) $got ) {
			$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $probe ) );
			self::$supports_advisory = true;
		} else {
			// Clear any error picked up from probing on a backend that lacks the function.
			$wpdb->last_error = '';
		}

		return self::$supports_advisory;
	}

	/**
	 * MySQL path: server-side advisory lock (the production engine).
	 *
	 * @param string $lock_name Lock name.
	 * @param int    $timeout   Seconds to wait.
	 * @return bool
	 */
	protected static function acquire_advisory( string $lock_name, int $timeout ): bool {
		global $wpdb;
		$result = $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, %d)', $lock_name, $timeout ) );
		return '1' === (string) $result;
	}

	/**
	 * Portable path: claim a row keyed by the lock name, with a TTL.
	 *
	 * The PRIMARY KEY on lock_name is the mutex: the second concurrent INSERT
	 * fails, which is exactly the serialisation GET_LOCK() provides. A row whose
	 * expires_at has passed is considered free and may be taken over, so a PHP
	 * process killed mid-checkout cannot wedge a slot forever (spec 4.16).
	 *
	 * @param string $lock_name Lock name.
	 * @param int    $timeout   Seconds to wait for the lock.
	 * @return bool
	 */
	protected static function acquire_row( string $lock_name, int $timeout ): bool {
		global $wpdb;

		$table = DrPlus_Child_Capacity_Installer::lock_table();
		$deadline = microtime( true ) + max( 0, $timeout );

		do {
			$now = time();
			$ttl = $now + max( 10, $timeout + 5 );

			// Reclaim an expired row first: a crashed request leaves the row behind.
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE `{$table}` SET expires_at = %d WHERE lock_name = %s AND expires_at < %d",
					$ttl,
					$lock_name,
					$now
				)
			);

			// A claim that is still alive means someone else holds it.
			$held = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM `{$table}` WHERE lock_name = %s AND expires_at >= %d",
					$lock_name,
					$now
				)
			);

			if ( '0' === (string) $held ) {
				$inserted = $wpdb->query(
					$wpdb->prepare(
						"INSERT INTO `{$table}` (lock_name, expires_at) VALUES (%s, %d)",
						$lock_name,
						$ttl
					)
				);
				if ( false !== $inserted ) {
					$wpdb->last_error = '';
					return true;
				}
				// Duplicate key: lost the race by microseconds — retry.
				$wpdb->last_error = '';
			}

			usleep( 50000 );
		} while ( microtime( true ) < $deadline );

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

		if ( self::engine_supports_advisory_locks() ) {
			$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock_name ) );
		} else {
			$wpdb->delete( DrPlus_Child_Capacity_Installer::lock_table(), array( 'lock_name' => $lock_name ) );
		}

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
