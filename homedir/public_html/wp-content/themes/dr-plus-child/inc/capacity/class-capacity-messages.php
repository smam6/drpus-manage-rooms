<?php
/**
 * Dr Plus Child Theme — Room Capacity Management
 *
 * Implements: spec 3.16, 5.6.98, 7.2, 7.8
 * Category: 3/7 — Centralized Persian user-facing strings (single source, spec 5.6.98)
 *
 * All user-facing strings are Persian and wrapped in __() with domain 'drplus-child' (spec 8.5).
 *
 * @package DrPlusChild\Capacity
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Messages: every Persian user-facing string of the module lives here.
 */
class DrPlus_Child_Capacity_Messages {

	/**
	 * Missing-date warning. Client and server MUST use this identical string (spec 7.8, 7.2).
	 *
	 * Implements: spec 7.2, 7.8, 3.6, 5.5.96, 5.5.97
	 *
	 * @return string
	 */
	public static function missing_date() {
		return __( 'لطفاً ابتدا تاریخ مراجعه را انتخاب کنید.', 'drplus-child' );
	}

	/**
	 * Capacity-exceeded message, optionally listing the day's available hours (spec 3.9, decision #4).
	 *
	 * Implements: spec 3.8, 3.9, 5.5.98
	 *
	 * @param array $hours Available hours, each {startTime,endTime}.
	 * @return string
	 */
	public static function capacity_exceeded( $hours = array() ) {
		$message = __( 'ظرفیت این ساعت تکمیل شده است. لطفاً یکی از ساعت‌های موجود در آن روز را انتخاب کنید.', 'drplus-child' );
		if ( ! empty( $hours ) ) {
			$times = array();
			foreach ( $hours as $hour ) {
				$times[] = $hour['startTime'] . ' - ' . $hour['endTime'];
			}
			$message .= ' ' . sprintf(
				/* translators: %s: comma-separated list of available time ranges. */
				__( 'ساعت‌های آزاد: %s', 'drplus-child' ),
				implode( '، ', $times )
			);
		}
		return $message;
	}

	/**
	 * Lock-acquisition failure message (spec 4.6).
	 *
	 * Implements: spec 4.6, 3.15
	 *
	 * @return string
	 */
	public static function lock_failed() {
		return __( 'خطایی در بررسی ظرفیت رخ داد. لطفاً دوباره تلاش کنید.', 'drplus-child' );
	}

	/**
	 * Admin page title (spec 5.9).
	 *
	 * @return string
	 */
	public static function admin_title() {
		return __( 'مدیریت ظرفیت اتاق‌های کلینیک', 'drplus-child' );
	}

	/**
	 * Admin menu label (spec 5.1).
	 *
	 * @return string
	 */
	public static function admin_menu() {
		return __( 'ظرفیت اتاق‌ها', 'drplus-child' );
	}

	/**
	 * Admin page description (spec 5.9).
	 *
	 * @return string
	 */
	public static function admin_description() {
		return __( 'حداکثر تعداد متخصصانی که می‌توانند در یک ساعت حضوری نوبت داشته باشند را برای هر کلینیک تعیین کنید.', 'drplus-child' );
	}

	/**
	 * Admin table column: clinic (spec 5.3).
	 *
	 * @return string
	 */
	public static function admin_col_clinic() {
		return __( 'کلینیک', 'drplus-child' );
	}

	/**
	 * Admin table column: capacity (spec 5.3).
	 *
	 * @return string
	 */
	public static function admin_col_capacity() {
		return __( 'ظرفیت هر ساعت (تعداد متخصص)', 'drplus-child' );
	}

	/**
	 * Admin save button (spec 5.9).
	 *
	 * @return string
	 */
	public static function admin_save_button() {
		return __( 'ذخیره', 'drplus-child' );
	}

	/**
	 * Admin success notice (spec 5.9).
	 *
	 * @return string
	 */
	public static function admin_save_success() {
		return __( 'تنظیمات با موفقیت ذخیره شد.', 'drplus-child' );
	}

	/**
	 * Admin error notice for invalid values (spec 5.6).
	 *
	 * @return string
	 */
	public static function admin_save_error() {
		return __( 'برخی مقادیر نامعتبر هستند (بازه مجاز ۱ تا ۹۹) و ذخیره نشدند.', 'drplus-child' );
	}

	/**
	 * Admin empty-clinics notice (spec 5.3).
	 *
	 * @return string
	 */
	public static function admin_no_clinics() {
		return __( 'هیچ کلینیکی یافت نشد.', 'drplus-child' );
	}
}
