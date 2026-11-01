<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPMH_Menu_Modifier {

    public function __construct() {
        // اجرای فرآیند مخفی‌سازی با اولویت بالا در هسته وردپرس
        add_action( 'admin_init', array( $this, 'hide_menus_for_current_user' ), 9999 );
    }

    public function hide_menus_for_current_user() {
        if ( ! is_user_logged_in() ) {
            return;
        }

        $current_user = wp_get_current_user();
        $user_roles   = $current_user->roles;
        
        if ( empty( $user_roles ) ) {
            return;
        }
        
        $primary_role = array_shift( $user_roles );

        /* -------------------------------------------------------------------------
         * 🔒 گارد امنیتی اختصاصی: مخفی‌سازی کامل افزونه برای تمام نقش‌ها بجز مدیر اصلی (Administrator)
         * ------------------------------------------------------------------------- */
        if ( $primary_role !== 'administrator' ) {
            remove_menu_page( 'aliwebpress-menu-hider' );
        }

        // دریافت لیست تنظیمات منوهای مخفی از دیتابیس
        $saved_settings = get_option( 'wpmh_hidden_menus', array() );
        
        if ( isset( $saved_settings[$primary_role] ) && ! empty( $saved_settings[$primary_role] ) ) {
            $menus_to_hide = $saved_settings[$primary_role];

            foreach ( $menus_to_hide as $menu_slug ) {
                // سوپاپ اطمینان: حتی اگر مدیر کل اشتباهاً تیک منوی افزونه را برداشت، سیستم برای خودش مخفی نکند تا دسترسی قطع نشود.
                if ( $primary_role === 'administrator' && $menu_slug === 'aliwebpress-menu-hider' ) {
                    continue;
                }
                
                remove_menu_page( $menu_slug );
            }
        }
    }
}