<?php

/**
 * RTL Smart Manager Plugin
 *
 * @package           RTL-SmartManager
 * @author            RTL-Theme
 * @copyright         RTL-Theme
 * @license           RTL-Theme
 *
 * @wordpress-plugin
 *
 * Plugin Name: افزونه مدیریت هوشمند راست چین
 * Plugin URI: https://www.rtl-theme.com/
 * Description: افزونه ای جهت مدیریت و نگهداری محصولات وردپرسی
 * Version: 2.8.10
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * Author: RTL-Theme
 * Author URI: https://www.rtl-theme.com/
 * License: RTL-Theme License
 * Text Domain: rsm
 * Domain Path: /languages
 *
 */

use RTL\RSM\bootstrap;
use RTL\RSM\Core\ServiceContainer;

if ( !defined('ABSPATH') ) {
    return;
}

class RtlRequired {
    public static function checkRequirements () {

        // Set Max Execution Time
        $maxExecutionTime = function_exists('ini_get') ? ((int) ini_get('max_execution_time')) : 0;

        if ( $maxExecutionTime < 120 ) {
            if ( function_exists('set_time_limit') ) {
                set_time_limit(120);
            }

            if ( function_exists('ini_set') ) {
                ini_set('max_execution_time', '120');
            }
        }

        // Set Direct Download Method
        add_filter('filesystem_method', static function () {
            return 'direct';
        });

        // Change WP Directory Permissions
        defined('FS_CHMOD_DIR') || define('FS_CHMOD_DIR', 0755);

        // Tell PHP That We're Using UTF-8 Strings
        if ( function_exists('mb_internal_encoding') ) {
            mb_internal_encoding('UTF-8');
        }

        // Tell PHP That We'll be Outputting UTF-8 To The Browser
        if ( function_exists('mb_http_output') ) {
            mb_http_output('UTF-8');
        }

        $extension = get_loaded_extensions() ?? [];
        $diff      = array_diff(['mbstring', 'curl', 'ionCube Loader'], $extension);

        if ( !empty($diff) || version_compare(phpversion(), '7.4', '<') || !extension_loaded('ionCube Loader') || !function_exists('ioncube_loader_version') || version_compare(ioncube_loader_version(), '10.2', '<') ) {
            add_action('admin_notices', [__CLASS__, 'requirementsErrorMessage']);

            return false;
        }

        return true;
    }

    public static function requirementsErrorMessage () {
        $extension = get_loaded_extensions() ?? [];

        $mbstring = !in_array('mbstring', $extension);
        $curl     = !in_array('curl', $extension);
        $intl     = !in_array('intl', $extension);

        ?>
        <style>
            .rtl-theme-notice {
                padding: 10px;
            }

            .rtl-theme-notice img {
                float: right;
                max-width: 64px;
                margin: 15px 0 0 15px;
            }

            .rtl-theme-notice .rtl-theme-notice-content {
                font-size: 14px;
                overflow: hidden;
                line-height: 2;
                font-family: 'IRANYekan';
            }

            .rtl-theme-notice .rtl-theme-notice-content .rtl-notice-title {
                font-size: 16px;
                display: block;
                color: #d00808;
            }

            .rtl-theme-notice .rtl-theme-notice-content span {
                font-size: 12px;
                color: #db641d;
                padding-top: 10px;
            }
        </style>
        <div class="notice notice-error rtl-theme-notice">
            <img src="https://content-marketing.rtlcdn.com/image/2024/07/a0c3ba74-c9c2-42ef-bd78-d658b3746a28.png"/>
            <div class="rtl-theme-notice-content">
                <div class="rtl-notice-title">خطای افزونه مدیریت هوشمند راست چین</div>
                <p>برای اجرای افزونه <strong> مدیریت هوشمند راست چین </strong> موارد زیر الزامی باشد.در غیر اینصورت
                    افزونه قادر به ارائه خدمات نخواهد بود</p>
                <?php if ( version_compare(phpversion(), '7.4', '<') ) { ?>
                    <p>PHP نسخه 7.4 به بالا</p>
                    <span>نسخه فعلی php در هاست شما: <?= PHP_VERSION; ?></span>
                <?php }
                if ( !extension_loaded('ionCube Loader') || !function_exists('ioncube_loader_version') || version_compare(ioncube_loader_version(), '10.2', '<') ) {
                    ?>
                    <p>آخرین نسخه ionCube Loader روی سرور شما نصب و فعال باشد.</p>
                    <span>در این زمینه می بایست با پشتیبانی هاست خود در تماس باشید.</span>
                    <?php
                    if ( function_exists('ioncube_loader_version') ) {
                        $ionVersion = ioncube_loader_version();
                        $limVersion = '10.2';
                        echo " <p>ورژن ioncube شما $ionVersion است و کمترین باید بیشتر از $limVersion باشد </p>";
                        ?>

                        <?PHP
                    }

                }
                if ( $mbstring ) { ?>
                    <p>mbstring extension</p>
                <?php }
                if ( $curl ) { ?>
                    <p>curl extension</p>
                <?php } ?>
            </div>
        </div>
        <?php
    }
}

if ( RtlRequired::checkRequirements() ) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
    $container = ServiceContainer::getInstance();
    $boot      = $container->make(Bootstrap::class);
    $boot->boot();
    register_activation_hook(__FILE__, [Bootstrap::class, 'installPlugin']);
}



