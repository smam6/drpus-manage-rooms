<?php
/**
 * Plugin Name: Aliwebpress Menu Hider
 * Plugin URI:  https://aliwebpress.com
 * Description: یک راهکار حرفه‌ای و استاندارد برای <strong>مخفی‌سازی زبانه‌های پیشخوان وردپرس</strong> بر اساس نقش‌های کاربری، توسعه یافته توسط <strong>علی قربانی | Aliwebpress</strong>
 * Version:     1.1.0
 * Author:      Aliwebpress
 * Author URI:  https://aliwebpress.com
 * Text Domain: aliwebpress-menu-hider
 * License:     GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

// تعریف ثوابت آدرس و مسیر افزونه
define( 'WPMH_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPMH_URL', plugin_dir_url( __FILE__ ) ); 

// فراخوانی فایل‌های کلاس ادمین و اصلاح‌کننده منوها
require_once WPMH_PATH . 'includes/class-admin-settings.php';
require_once WPMH_PATH . 'includes/class-menu-modifier.php';

// راه‌اندازی کلاس‌های افزونه پس از بارگذاری کامل سیستم
add_action( 'plugins_loaded', 'awp_init_menu_hider' );
function awp_init_menu_hider() {
    new WPMH_Admin_Settings();
    new WPMH_Menu_Modifier();
}