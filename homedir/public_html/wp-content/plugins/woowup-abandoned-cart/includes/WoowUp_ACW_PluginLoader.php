<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
include 'WoowUp_ACW_Logger.php';
include 'WoowUp_ACW_WoowUpApi.php';
if(!class_exists('WoowUp_ACW_PluginLoader')){
    final class WoowUp_ACW_PluginLoader{
        private static $instance = null;
        public static function getInstance()
        {
            if(is_null(self::$instance))
            {
                self::$instance = new self();
            }
            return self::$instance;
        }
        private function __construct()
        {
            //CONSTANTS
            define('WOOWUP_ABANDONEDCART_TABLE', 'woowup_abandonedcart_woocommerce_table');
            define('WOOWUP_ABANDONEDCART_RECOBERY_TABLE', 'woowup_abandonedcartrecovery_woocommerce_table');
            define('WOOWUP_ABANDONEDCART_MINUTES_TO_SEND_CART', 30);
            define('WOOWUP_ABANDONEDCART_DIR', plugin_dir_path( WOOWUP_ABANDONEDCART_PLUGIN_FILE ) );
            define('WOOWUP_ABANDONEDCART_BASE', plugin_basename( WOOWUP_ABANDONEDCART_PLUGIN_FILE ) );
            define('WOOWUP_ABANDONEDCART_MENU','woowup-abandonedcart-woocommerce');
            define('WOOWUP_ABANDONEDCART_MENU_CONFIGURATION','woowup-abandonedcart-woocommerce-configuration');
            define('WOOWUP_ABANDONEDCART_MENU_ABANDONED_CART','woowup-abandonedcart-woocommerce-abandoned_cart');
            define('WOOWUP_ABANDONEDCART_MENU_ALMOST_ABANDONED_CART','woowup-abandonedcart-woocommerce-almost-abandoned_cart');
            define('WOOWUP_ABANDONEDCART_MENU_CONFIGURATION_GROUP','woowup-abandonedcart-woocommerce-configuration-group');
            define('WOOWUP_ABANDONEDCART_DEFAULT_APIKEY','Sin-Datos');
            
            //LOAD CORE FUNCTIONS
            register_activation_hook(WOOWUP_ABANDONEDCART_PLUGIN_FILE, array( $this, 'pluginActivation'));
            register_deactivation_hook(WOOWUP_ABANDONEDCART_PLUGIN_FILE, array( $this, 'pluginDeactivation'));
            add_action('plugins_loaded', array($this, 'pluginLoad'),99);
        }

        public function pluginLoad(){
            require_once WOOWUP_ABANDONEDCART_DIR.'includes/WoowUp_ACW_AbandonedCart.php';
        }

        public function pluginActivation()
        {
            $this->updateAbandonedCartTable();
            $this->updateAbandonedCartRecoveryTable();
            $this->updateSettingsValues();
        }

        public function pluginDeactivation()
        {
            wp_clear_scheduled_hook( 'woowup_abandonedcart_send_cart' );
        }

        public function updateSettingsValues()
        {
            if ( ! get_option( 'woowupApikey' ) ) {
                update_option( 'woowupApikey', WOOWUP_ABANDONEDCART_DEFAULT_APIKEY);
            }
        }
        
        public function updateAbandonedCartRecoveryTable()
        {
            global $wpdb;
            $abandonedCartTable = $wpdb->prefix . WOOWUP_ABANDONEDCART_RECOBERY_TABLE;
            $charset_collate     = $wpdb->get_charset_collate();
            //SQL COMMAND
            $sql = "CREATE TABLE IF NOT EXISTS $abandonedCartTable (
			external_id VARCHAR(100) NOT NULL,
			customer LONGTEXT,
			products LONGTEXT,
			PRIMARY KEY  (external_id)
		) $charset_collate;\n";

		include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

        }

        public function updateAbandonedCartTable()
        {
            global $wpdb;
            $abandonedCartTable = $wpdb->prefix . WOOWUP_ABANDONEDCART_TABLE;
            $charset_collate     = $wpdb->get_charset_collate();
            //SQL COMMAND
            $sql = "CREATE TABLE IF NOT EXISTS $abandonedCartTable (
			external_id VARCHAR(100) NOT NULL,
			customer LONGTEXT,
			products LONGTEXT,
            products_info LONGTEXT,
			total_price DECIMAL(20,4),
			recover_url LONGTEXT,
            createtime DATETIME DEFAULT NULL,
			PRIMARY KEY  (external_id)
		) $charset_collate;\n";

		include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
        }
    }
    WoowUp_ACW_PluginLoader::getInstance();
}


