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
	 * Missing-time warning. Raised when a booking's hour cannot be resolved, so capacity can
	 * never be verified — the checkout must not proceed silently.
	 *
	 * Implements: spec 3.6, 7.2, 7.8
	 *
	 * @return string
	 */
	public static function missing_time() {
		return __( 'لطفاً ابتدا ساعت مراجعه را انتخاب کنید.', 'drplus-child' );
	}

	/**
	 * Missing-time warning for the time step, where the user can still pick an hour.
	 * Same meaning as missing_time(), phrased as a step instruction rather than a
	 * checkout rejection.
	 *
	 * Implements: spec 7.2
	 *
	 * @return string
	 */
	public static function select_time() {
		return __( 'برای ادامه، یکی از ساعت‌های مراجعه را انتخاب کنید.', 'drplus-child' );
	}

	/**
	 * Specialist double-booking warning.
	 *
	 * Raised when the SAME specialist already holds this date + hour, regardless of room
	 * capacity: a specialist cannot be in two rooms at once, and distinct-specialist counting
	 * cannot detect it (two bookings by one specialist leave the room count at 1).
	 *
	 * Implements: business rule 1, Bug C
	 *
	 * @return string
	 */
	public static function specialist_conflict() {
		return __( 'این متخصص در این ساعت قبلاً رزرو دارد. لطفاً ساعت دیگری را انتخاب کنید.', 'drplus-child' );
	}

	/**
	 * Specialist is busy at this hour at a DIFFERENT clinic.
	 *
	 * Refused for the same reason — one person, one hour — but the cause is not visible on the
	 * screen the customer is looking at, because each clinic's grid only reflects its own
	 * bookings. Saying "already booked in this hour" without naming the clinic reads as a bug:
	 * the slot looked free. Naming the clinic turns it into an explanation.
	 *
	 * Implements: Task 2 (slot visibility vs. checkout refusal)
	 *
	 * @return string
	 */
	public static function specialist_conflict_other_clinic() {
		return __( 'این متخصص در این ساعت در مرکز درمانی دیگری رزرو دارد. لطفاً ساعت یا مرکز دیگری را انتخاب کنید.', 'drplus-child' );
	}

	/**
	 * The page could not determine WHICH specialist is being booked.
	 *
	 * MUST NEVER say "this specialist already has a booking". There is no booking — the id is
	 * simply missing, so every hour of every day would be refused, and the customer would keep
	 * trying different times against a phantom appointment. The actionable instruction is to
	 * go back and re-select the specialist.
	 *
	 * @return string
	 */
	public static function missing_specialist() {
		return __( 'متخصص این نوبت مشخص نشد. لطفاً به مرحله قبل برگردید و دوباره متخصص را انتخاب کنید. اگر تکرار شد، از مرورگر دیگری وارد شوید.', 'drplus-child' );
	}

	/**
	 * A general "cannot verify this slot" refusal, used when the date or hour is unusable.
	 *
	 * Distinct from missing_date()/missing_time(), which are raised earlier by the date and
	 * time guards. This one means the value was present but could not be interpreted.
	 *
	 * @return string
	 */
	public static function unverifiable_slot() {
		return __( 'زمان یا تاریخ این نوبت قابل بررسی نیست. لطفاً مرحله انتخاب تاریخ و ساعت را دوباره انجام دهید.', 'drplus-child' );
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
		if ( empty( $hours ) || ! is_array( $hours ) ) {
			return $message;
		}

		// Rule 5: lead with the FIRST available alternative so the customer has an actionable
		// next step instead of a wall of options.
		$first = reset( $hours );
		if ( is_array( $first ) && ! empty( $first['startTime'] ) ) {
			$message .= ' ' . sprintf(
				/* translators: %s: time of the nearest available slot, e.g. "17:00". */
				__( 'نزدیک‌ترین ساعت آزاد: %s', 'drplus-child' ),
				$first['startTime']
			);
		}

		$times = array();
		foreach ( $hours as $hour ) {
			$times[] = $hour['startTime'] . ' - ' . $hour['endTime'];
		}
		$message .= ' ' . sprintf(
			/* translators: %s: comma-separated list of available time ranges. */
			__( 'ساعت‌های آزاد: %s', 'drplus-child' ),
			implode( '، ', $times )
		);

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
	 * Column header for the clinic alias field.
	 *
	 * The parent stores custom offices under a per-specialist UUID with no link back to the
	 * clinic post, so the operator supplies that link once per clinic.
	 *
	 * @return string
	 */
	public static function admin_col_alias() {
		return __( 'شناسه اتاق سفارشی (UUID)', 'drplus-child' );
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
	 * Section heading for the "order status agreement" panel.
	 *
	 * @return string
	 */
	public static function admin_sync_title() {
		return __( 'هم‌خوانی وضعیت رزروها با ووکامرس', 'drplus-child' );
	}

	/**
	 * Explanation of what the panel checks and why it matters.
	 *
	 * @return string
	 */
	public static function admin_sync_description() {
		return __( 'وضعیت رزرو در جدول رزروها یک «کپی» از وضعیت سفارش ووکامرس است. اگر این کپی به‌روز نشود، ساعتی که سفارشش لغو شده برای همیشه بسته می‌ماند و هیچ خطایی هم نمایش داده نمی‌شود. این بخش همان کپی را با سفارش اصلی مقایسه می‌کند.', 'drplus-child' );
	}

	/**
	 * Button that writes the order status back into every stale booking row.
	 *
	 * @return string
	 */
	public static function admin_sync_repair_button() {
		return __( 'هم‌خوان‌سازی رزروهای ناهم‌خوان', 'drplus-child' );
	}

	/**
	 * Result line when the table and WooCommerce agree.
	 *
	 * @return string
	 */
	public static function admin_sync_clean() {
		return __( 'همه‌ی رزروها با سفارش ووکامرس هم‌خوان هستند.', 'drplus-child' );
	}

	/**
	 * Result line after a repair run. `%d` is the number of rows corrected.
	 *
	 * @return string
	 */
	public static function admin_sync_repaired() {
		return __( '%d رزرو ناهم‌خوان اصلاح شد. ساعت‌هایی که بی‌دلیل بسته مانده بودند آزاد شدند.', 'drplus-child' );
	}

	/**
	 * Result line when nothing needed repairing.
	 *
	 * @return string
	 */
	public static function admin_sync_nothing_to_repair() {
		return __( 'همه‌ی رزروها از قبل هم‌خوان بودند؛ چیزی برای اصلاح نبود.', 'drplus-child' );
	}

	/**
	 * Admin empty-clinics notice (spec 5.3).
	 *
	 * @return string
	 */
	public static function admin_no_clinics() {
		return __( 'هیچ کلینیکی یافت نشد.', 'drplus-child' );
	}

	/**
	 * System-busy message when the slot advisory lock times out (spec 4.13).
	 *
	 * Implements: spec 4.13, 4.6
	 *
	 * @return string
	 */
	public static function system_busy() {
		return __( 'سیستم مشغول است. لطفاً چند لحظه بعد دوباره تلاش کنید.', 'drplus-child' );
	}

	/**
	 * Compensating-rollback message when Layer 2 cancels an over-capacity booking (spec 4.15).
	 *
	 * Implements: spec 4.15
	 *
	 * @return string
	 */
	public static function compensating_rollback() {
		return __( 'ظرفیت این ساعت هم‌زنان پر شد. لطفاً ساعت دیگری انتخاب کنید.', 'drplus-child' );
	}
}
