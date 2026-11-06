<?php
namespace DrPlus\Updates;

class V2_5_0_0 {
	public static $run_at_install = false;

	public static function update() {
		global $wpdb;
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_specialists` ADD `name` TEXT NULL AFTER `slug`;" );

		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_specialists` CHANGE `status` `status` VARCHAR(50);" );

		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_times` CHANGE `office` `office` VARCHAR(50);" );

		// Indexing tables
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}sheyda_wallet_ledger` CHANGE `type` `type` VARCHAR(255)" );

		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_specialist_speciality_rel` ADD INDEX `index_e8701ad48ba05a91604e480dd60899a3` (`user_id`);" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_specialist_speciality_rel` ADD INDEX `index_66db61fe8042b731b913a800756f8eaf` (`speciality_id`);" );

		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_wishlist` ADD INDEX `index_9bea82def865d4d0e499eb252805f127` (`product_id`);" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_wishlist` ADD INDEX `index_e8701ad48ba05a91604e480dd60899a3` (`user_id`);" );
		
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_times` ADD INDEX `index_ee0aff17bef1c2d762444fcd2bae2d73` (`office`, `user_id`);" );

		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_specialists` ADD INDEX `index_47b34f35542a03174fbf8cf868f98646` (`post_id`, `status`);" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_specialists` ADD INDEX `index_e8701ad48ba05a91604e480dd60899a3` (`user_id`);" );

		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_specialist_insurances_rel` ADD INDEX `index_e8701ad48ba05a91604e480dd60899a3` (`user_id`);" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_specialist_insurances_rel` ADD INDEX `index_c1dd638f8c658dc10fe408083929fca4` (`insurance_id`);" );

		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_specialist_hospitals_rel` ADD INDEX `index_e8701ad48ba05a91604e480dd60899a3` (`user_id`);" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_specialist_hospitals_rel` ADD INDEX `index_8a738e0047b59b4b7b62e400e97d0d2d` (`hospital_id`);" );

		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_reminder_log` ADD INDEX `index_9acb44549b41563697bb490144ec6258` (`status`);" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_reminder_log` ADD INDEX `index_bbf2f240f43e0717288722ab5f288b98` (`send_time`);" );

		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_notifications_user_rel` ADD INDEX `index_f86ed18808597fb5c3a355b924216b12` (`notif_id`);" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_notifications_user_rel` ADD INDEX `index_e8701ad48ba05a91604e480dd60899a3` (`user_id`);" );

		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_chat_sessions` ADD INDEX `index_bc585c3afe7f0faadefeb6de003a9649` (`context_id`);" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_chat_sessions` ADD INDEX `index_45704e102ace7c7ed32a96473cb6a329` (`user_1_id`, `user_2_id`);" );

		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_chat_messages` ADD INDEX `index_7fc8ef54a8154c28341bf9a47443a5ce` (`session_id`);" );

		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_booking` ADD INDEX `index_9e126c6ff437e6a4fd18f87cfa3e0518` (`specialist_id`);" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_booking` ADD INDEX `index_f0b195e4a53a8562b6938e00bd0fda75` (`specialist_id`, `order_status`);" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_booking` ADD INDEX `index_0dbad5b96349237116f63f0f389bd7f4` (`date`);" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_booking` ADD INDEX `index_6a3d8bda39fe48dc5a1e9a14504c3335` (`customer_id`, `order_status`);" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_booking` ADD INDEX `index_5e1fb9d1d5db4425d1fdd10e47d7230a` (`specialist_id`, `office_id`, `date`, `start_time`, `order_status`);" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}drplus_booking` ADD INDEX `index_cc247b0596a4d3465c8013b7edb59235` (`office_id`);" );

		$wpdb->query( "DROP TABLE `{$wpdb->prefix}drplus_specialist_city_rel`" );
	}
}