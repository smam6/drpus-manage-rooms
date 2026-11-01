<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPMH_Admin_Settings {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_settings_page' ), 9 ); 
        add_action( 'admin_init', array( $this, 'save_plugin_settings' ), 1 ); 
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) ); // هوک فراخوانی استایل خارجی
    }

    public function add_settings_page() {
        add_menu_page(
            'تنظیمات AWP Menu Hider',
            'AWP Menu Hider',
            'manage_options',
            'aliwebpress-menu-hider', // اسلاگ سیستم بدون تغییر باقی ماند تا ساختار آدرس‌ها خراب نشود
            array( $this, 'render_settings_page' ),
            'dashicons-visibility', // بازگشت به آیکون چشم پیش‌فرض وردپرس
            2 
        );
    }

    // متد لود کردن استاندارد فایل CSS در پیشخوان
    public function enqueue_admin_styles( $hook ) {
        // فایل CSS فقط در صفحه تنظیمات خود افزونه لود می‌شود تا با بقیه صفحات تداخل نداشته باشد
        if ( $hook === 'toplevel_page_aliwebpress-menu-hider' ) {
            wp_enqueue_style( 'awp-admin-css', WPMH_URL . 'assets/css/admin-style.css', array(), '1.1.0' );
        }
    }

    public function save_plugin_settings() {
        if ( isset($_POST['wpmh_nonce']) && wp_verify_nonce($_POST['wpmh_nonce'], 'wpmh_save_action') ) {
            if ( current_user_can('manage_options') ) {
                $current_role = isset( $_POST['wpmh_current_role'] ) ? sanitize_text_field( $_POST['wpmh_current_role'] ) : 'administrator';
                
                $submitted_menus = isset( $_POST['menus'] ) ? array_map('sanitize_text_field', $_POST['menus']) : array();
                $all_detected_menus = isset($_POST['all_detected_menus']) ? array_map('sanitize_text_field', $_POST['all_detected_menus']) : array();
                
                $saved_settings = get_option( 'wpmh_hidden_menus', array() );
                
                $menus_to_hide = array();
                foreach ( $all_detected_menus as $slug ) {
                    if ( ! in_array( $slug, $submitted_menus ) ) {
                        $menus_to_hide[] = $slug;
                    }
                }
                
                $saved_settings[$current_role] = $menus_to_hide;
                update_option( 'wpmh_hidden_menus', $saved_settings );
                
                wp_redirect( admin_url( 'admin.php?page=aliwebpress-menu-hider&role=' . $current_role . '&settings-updated=true' ) );
                exit;
            }
        }
    }

    public function render_settings_page() {
        global $wp_roles, $menu;
        $roles = $wp_roles->get_names();
        
        $current_role = isset( $_GET['role'] ) ? sanitize_text_field( $_GET['role'] ) : 'administrator';
        $saved_settings = get_option( 'wpmh_hidden_menus', array() );
        $hidden_menus = isset( $saved_settings[$current_role] ) ? $saved_settings[$current_role] : array();

        if ( isset($_GET['settings-updated']) ) {
            echo '<div class="updated notice is-dismissible" style="margin: 15px 0 0 0;"><p>✔️ تغییرات با موفقیت ذخیره و به صورت آنی اعمال شدند.</p></div>';
        }

        $stored_menus = get_option( 'awp_all_registered_menus', array() );
        $new_menus_found = false;

        if ( ! empty( $menu ) ) {
            foreach ( $menu as $menu_item ) {
                if ( ! empty( $menu_item[0] ) && ! empty( $menu_item[2] ) ) {
                    $menu_name = wp_strip_all_tags( $menu_item[0] );
                    $menu_slug = $menu_item[2];
                    
                    if ( strpos($menu_slug, 'separator') === false && $menu_slug !== 'aliwebpress-menu-hider' ) {
                        if ( ! isset( $stored_menus[$menu_slug] ) || $stored_menus[$menu_slug] !== $menu_name ) {
                            $stored_menus[$menu_slug] = $menu_name;
                            $new_menus_found = true;
                        }
                    }
                }
            }
            if ( $new_menus_found ) {
                update_option( 'awp_all_registered_menus', $stored_menus );
            }
        }

        $dynamic_menus = ! empty( $stored_menus ) ? $stored_menus : array();
        ?>
        
        <div class="awp-container">
            <div class="awp-header">
                <div>
                    <h1>AWP Menu Hider</h1>
                    <p>مدیریت هوشمند و خلوت‌سازی زبانه‌های پیشخوان کاربران توسط : علی قربانی</p>
                    <p class="awp-contact-info">جهت سفارش طراحی سایت با من در تماس باشید : 09395520411</p>
                </div>
                <a href="https://aliwebpress.com" target="_blank" class="awp-brand-link">Aliwebpress.com</a>
            </div>

            <div class="awp-body">
                <div class="awp-card">
                    <form method="get" action="">
                        <input type="hidden" name="page" value="aliwebpress-menu-hider">
                        <label class="awp-step-label" for="role_select">۱. نقش کاربری را انتخاب کنید:</label>
                        <select id="role_select" name="role" onchange="this.form.submit()" class="awp-select">
                            <?php foreach ( $roles as $role_slug => $role_name ) : ?>
                                <option value="<?php echo esc_attr( $role_slug ); ?>" <?php selected( $current_role, $role_slug ); ?>>
                                    <?php echo esc_html( translate_user_role($role_name) ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <form method="post" action="">
                    <?php wp_nonce_field( 'wpmh_save_action', 'wpmh_nonce' ); ?>
                    <input type="hidden" name="wpmh_current_role" value="<?php echo esc_attr( $current_role ); ?>">
                    
                    <label class="awp-step-label">۲. زبانه‌های مجاز برای نقش «<?php echo esc_html( translate_user_role($roles[$current_role]) ); ?>»:</label>
                    <span class="awp-notice-info">💡 برداشتن تیک هر گزینه, آن زبانه را فوراً از دید این نقش مخفی می‌کند.</span>
                    
                    <table class="awp-table">
                        <thead>
                            <tr>
                                <th style="width: 100px;">وضعیت نمایش</th>
                                <th>نام زبانه در پیشخوان</th>
                                <th style="width: 220px;">اسلاگ سیستم</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $dynamic_menus as $slug => $label ) : 
                                $is_checked = ! in_array( $slug, $hidden_menus );
                            ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="menus[]" value="<?php echo esc_attr( $slug ); ?>" class="awp-checkbox" <?php checked( $is_checked, true ); ?>>
                                        <input type="hidden" name="all_detected_menus[]" value="<?php echo esc_attr( $slug ); ?>">
                                    </td>
                                    <td><strong><?php echo esc_html( $label ); ?></strong></td>
                                    <td><span class="awp-badge"><?php echo esc_html( $slug ); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="awp-submit-wrapper">
                        <?php submit_button( 'ذخیره و اعمال تغییرات', 'awp-submit-btn', 'submit', false ); ?>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
}