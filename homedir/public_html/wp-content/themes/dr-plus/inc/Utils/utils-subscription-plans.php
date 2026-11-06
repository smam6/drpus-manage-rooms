<?php
namespace DrPlus\Utils;

use DrPlus\Utils;

class SubscriptionPlans extends Utils {
    // cache group name for wp_cache_* calls
    protected static $cache_group = 'drplus_subscription';
    // per-request static cache mirrors to avoid repeated wp_cache_get calls
    protected static $local_cache = [
        'plans' => null,
        'settings' => null,
		'specialist_plan' => [], // keyed by user_id
		'specialist_plan_active' => [] // keyed by user_id
    ];

	public static function update_plans( $plans ) {
		$updated = update_option( 'drplus_subscription_plans', $plans, false );
		// update caches
		wp_cache_set( 'plans', $plans, self::$cache_group );
		self::$local_cache['plans'] = $plans;
		return $updated;
	}

	public static function get_plans( $only_actives = false ) {
		// Try local per-request cache first
		if ( self::$local_cache['plans'] !== null ) {
			$plans = self::$local_cache['plans'];
		} else {
			// Try persistent object cache
			$plans = wp_cache_get( 'plans', self::$cache_group );
			if ( $plans === false ) {
				$plans = get_option( 'drplus_subscription_plans', [] );
				wp_cache_set( 'plans', $plans, self::$cache_group );
			} else {
			}
			self::$local_cache['plans'] = $plans;
		}
		if( $only_actives && !empty( $plans ) ) {
			$plans = array_filter( $plans, fn($plan) => $plan['enable'] == true );
		}
		return $plans;
	}

	public static function update_settings( $settings ) {
		$settings = parent::check_default( $settings, self::default_settings() );
		$updated = update_option( 'drplus_subscription_settings', $settings, false );
		// update caches
		wp_cache_set( 'settings', $settings, self::$cache_group );
		self::$local_cache['settings'] = $settings;
		return $updated;
	}

	public static function get_settings() {
		// Local cache first
		if ( self::$local_cache['settings'] !== null ) {
			return self::$local_cache['settings'];
		}

		// Persistent object cache
		$settings = wp_cache_get( 'settings', self::$cache_group );
		if ( $settings === false ) {
			$settings = get_option( 'drplus_subscription_settings', self::default_settings() );
			$settings = Utils::check_default( $settings, self::default_settings() );
			wp_cache_set( 'settings', $settings, self::$cache_group );
		} else {
			// ensure defaults applied
			$settings = Utils::check_default( $settings, self::default_settings() );
		}

		self::$local_cache['settings'] = $settings;
		return $settings;
	}

	/**
	 * Clear caches for subscription plans and settings (useful when changed externally)
	 */
	public static function clear_cache() {
		self::$local_cache = [
			'plans' => null,
			'settings' => null,
			'specialist_plan' => [],
			'specialist_plan_active' => []
		];
		wp_cache_delete( 'plans', self::$cache_group );
		wp_cache_delete( 'settings', self::$cache_group );
		// Note: specialist_plan entries are deleted individually in add_specialist_plan
	}

	public static function default_settings() {
		return [
			'enable'				=> false,
			'special_plan'			=> "",
			'expire_warning_days'	=> 7
		];
	}

	public static function get_specialist_plan( $user_id = 0 ) {
		$user_id = User::get_user_id( $user_id );
		// Try per-request local cache
		if ( isset( self::$local_cache['specialist_plan'][ $user_id ] ) ) {
			$plan = self::$local_cache['specialist_plan'][ $user_id ];
		} else {
			// Try object cache keyed by user id
			$key = "specialist_plan_{$user_id}";
			$plan = wp_cache_get( $key, self::$cache_group );
			if ( $plan === false ) {
				$plan = get_user_meta( $user_id, 'drplus_specialist_subscription_plan', true );
				wp_cache_set( $key, $plan, self::$cache_group );
			}
			self::$local_cache['specialist_plan'][ $user_id ] = $plan;
		}
		$meta_default = [
			'id'			=> '',
			'expire_date'	=> '',
			'title'			=> '',
			'duration'		=> '',
			'reg_price'		=> '',
			'sale_price'	=> '',
		];
		if( empty( $plan ) ) $plan = [];

		$plan = Utils::check_default( $plan, $meta_default );

		$plan['plan_expire_warning'] = false;
		$plan['plan_expired'] = true;
		$plan['has_plan_before'] = true;

		if( empty( $plan['expire_date'] ) ) {
			$plan['has_plan_before'] = false;
		} else {
			$plan_expire_date = $plan['expire_date'];
			$plan_warning_days = 7;
		
			// Get remaining days and Check if plan is expiring within warning days
			$remaining_days = ( strtotime( $plan_expire_date ) - strtotime( date( 'Y-m-d' ) ) ) / ( DAY_IN_SECONDS );
			$plan['remaining_days'] = $remaining_days >= 0 ? $remaining_days : "0";
			if( $remaining_days >= 0 ) {
				$plan['plan_expired'] = false;
		
				if( $remaining_days < $plan_warning_days ) {
					$plan['plan_expire_warning'] = true;
				}
			}
			if( $plan['plan_expired'] ) {
				$plan_section_classes[] = 'subscription-expired';
			}
			if( $plan['plan_expire_warning'] ) {
				$plan_section_classes[] = 'subscription-expiring-warning';
			}
		}

		return apply_filters( 'drplus/specialist/subscription_plan', $plan, $user_id );
	}

	public static function add_specialist_plan( $new_plan, $user_id = 0 ) {
		$user_id = User::get_user_id( $user_id );

		$current_user_plan = self::get_specialist_plan( $user_id );
		$plan_expire_date = $current_user_plan['expire_date'] ?: date( 'Y-m-d' );
		$plan_duration = $new_plan['duration'];
		$plan_expire_date = (new \DateTime( $plan_expire_date ))->modify( "+{$plan_duration} day" );

		$new_plan_data = [
			'id'			=> $new_plan['id'],
			'expire_date'	=> $plan_expire_date->format( 'Y-m-d' ),
			'title'			=> $new_plan['title'],
			'duration'		=> $new_plan['duration'],
			'reg_price'		=> $new_plan['reg_price'],
			'sale_price'	=> $new_plan['sale_price'],
		];
		update_user_meta( $user_id, 'drplus_specialist_subscription_plan', $new_plan_data );

		// Update object cache and local cache mirror so subsequent reads reflect the new plan
		$key = "specialist_plan_{$user_id}";
		wp_cache_set( $key, $new_plan_data, self::$cache_group );
		self::$local_cache['specialist_plan'][ $user_id ] = $new_plan_data;

		// Also set active flag cache for this user
		$active = true;
		if ( ! empty( $new_plan_data['expire_date'] ) ) {
			$remaining_days = ( strtotime( $new_plan_data['expire_date'] ) - strtotime( date( 'Y-m-d' ) ) ) / DAY_IN_SECONDS;
			$active = $remaining_days >= 0;
		}
		$active_key = "specialist_plan_active_{$user_id}";
		wp_cache_set( $active_key, $active, self::$cache_group );
		self::$local_cache['specialist_plan_active'][ $user_id ] = $active;
	}

	public static function is_specialist_plan_active( $user_id = 0 ) {
		$user_id = User::get_user_id( $user_id );

		// If we have per-request cached value, return it
		if ( isset( self::$local_cache['specialist_plan_active'][ $user_id ] ) ) {
			return (bool) self::$local_cache['specialist_plan_active'][ $user_id ];
		}

		// Global setting: if specialist subscription is disabled globally, treat as active
		$settings = self::get_settings();
		if ( empty( $settings['enable'] ) ) {
			self::$local_cache['specialist_plan_active'][ $user_id ] = true;
			return true;
		}

		// Check object cache first
		$active_key = "specialist_plan_active_{$user_id}";
		$active = wp_cache_get( $active_key, self::$cache_group );
		if ( $active === false ) {
			$plan = self::get_specialist_plan( $user_id );
			if ( empty( $plan ) || empty( $plan['expire_date'] ) ) {
				$active = false;
			} else {
				$remaining_days = ( strtotime( $plan['expire_date'] ) - strtotime( date( 'Y-m-d' ) ) ) / DAY_IN_SECONDS;
				$active = $remaining_days >= 0;
			}
			wp_cache_set( $active_key, $active, self::$cache_group );
		}

		self::$local_cache['specialist_plan_active'][ $user_id ] = (bool) $active;
		return (bool) $active;
	}
}