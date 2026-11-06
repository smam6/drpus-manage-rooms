<?php
namespace DrPlus\Updates;

class V1_5_0_0 {
	public static $run_at_install = false;

	public static function update() {
		global $wpdb;
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_booking` CHANGE `total_price` `total_price` DECIMAL(20, 8) NOT NULL;" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_booking` CHANGE `commission` `commission` DECIMAL(20, 8) NULL DEFAULT NULL;" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_booking` CHANGE `specialist_income` `specialist_income` DECIMAL(20, 8) NULL DEFAULT NULL;" );
		self::fix_missing_commission();
		flush_rewrite_rules();
	}

	private static function fix_missing_commission() {
		global $wpdb;
		$table = "{$wpdb->prefix}drplus_booking";

		$wpdb->query(
			"UPDATE `{$table}` 
				SET `commission` = (`total_price` - `specialist_income`) 
				WHERE (`commission` = 0) 
					AND `specialist_income` IS NOT NULL 
					AND `total_price` IS NOT NULL 
					AND `specialist_income` <> `total_price`"
		);
	}
}
