<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
class WoowUp_ACW_AbandonedCart{
    private static $instance;
    private function __construct()
    {
        //LOAD MAIN MENU
        add_action( 'admin_menu', array( $this, 'abandonedCartMenuButton' ),99);
        add_action('admin_init', array($this,'abandonedCartMenuConfiguration'));
        add_filter( 'plugin_action_links_' . WOOWUP_ABANDONEDCART_BASE, array( $this, 'abandonedCartSettingButton' ), 999 );
        require_once WOOWUP_ABANDONEDCART_DIR.'includes/WoowUp_ACW_Cart.php';
    }

    public function abandonedCartSettingButton( $links ) {
		$myLinks = array(
			'<a href="' . admin_url( 'admin.php?page=' . WOOWUP_ABANDONEDCART_MENU ) . '">Ajustes</a>',
		);

		return array_merge( $myLinks, $links );
	}
    
    public static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function abandonedCartMenuConfiguration(){
        add_settings_section(
			WOOWUP_ABANDONEDCART_MENU_ALMOST_ABANDONED_CART,
			'WoowUp Carritos No Enviados',
			array( $this, 'abandonedCartMenuAlmostAbandonedCartCallback' ),
			WOOWUP_ABANDONEDCART_MENU
		);

        add_settings_section(
			WOOWUP_ABANDONEDCART_MENU_ABANDONED_CART,
			'WoowUp Carritos Enviados',
			array( $this, 'abandonedCartMenuAbandonedCartCallback' ),
			WOOWUP_ABANDONEDCART_MENU
		);
        add_settings_section(
			WOOWUP_ABANDONEDCART_MENU_CONFIGURATION,
			'WoowUp Ajuste',
			array( $this, 'abandonedCartMenuConfigurationCallback' ),
			WOOWUP_ABANDONEDCART_MENU
		);
        add_settings_field(
			'woowupApikey',
			'Api Key',
			array( $this, 'apikeyItemMenuCallback'),
			WOOWUP_ABANDONEDCART_MENU,
			WOOWUP_ABANDONEDCART_MENU_CONFIGURATION,
			array('')
		);
        register_setting(
			WOOWUP_ABANDONEDCART_MENU_CONFIGURATION_GROUP,
			'woowupApikey'
		);
    }
    public function abandonedCartMenuButton(){
        $capability = current_user_can( 'manage_woocommerce' ) ? 'manage_woocommerce' : 'manage_options';
        add_submenu_page(
			'woocommerce',
            'WoowUp',
			'WoowUp',
			$capability,
			WOOWUP_ABANDONEDCART_MENU,
            array($this,'abandonedCartMenuPage')
		);
    }
    public function abandonedCartMenuPage(){
        ?>
        <form method="post" action="options.php">
		<?php settings_fields( WOOWUP_ABANDONEDCART_MENU_CONFIGURATION_GROUP ); ?>
		<?php do_settings_sections( WOOWUP_ABANDONEDCART_MENU ); ?>
		<?php submit_button(); ?>
		</form>
        <?php
    }
    public function abandonedCartMenuConfigurationCallback(){      
        echo '<hr/>';
    }
    public function abandonedCartMenuAbandonedCartCallback(){
        global $wpdb;
        $recoverTableName = $wpdb->prefix . WOOWUP_ABANDONEDCART_RECOBERY_TABLE;
        $rows = $wpdb->get_results("SELECT * FROM {$recoverTableName}");
        $tr = '';
        foreach ($rows as $row) {
            $id = !empty($row->external_id)? $row->external_id:'-';
            $email = '-';
            $customer  = json_decode($row->customer);
            if ($customer){
                $email = $customer->email ?? '-';
            }
            $tr = $tr . '<tr>
						   <td>' . $id . '</td>
                           <td>' . $email . '</td>
                        </tr>';

        }
        $abandonedCartTable = '
        <hr/>
        <table align="center" class="widefat striped fixed">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                    '.$tr.'
                    </tbody>
                </table>';
        echo wp_kses_post($abandonedCartTable);
    }
    public function abandonedCartMenuAlmostAbandonedCartCallback(){     
        global $wpdb;
        $tableName = $wpdb->prefix . WOOWUP_ABANDONEDCART_TABLE;
        $rows = $wpdb->get_results("SELECT * FROM {$tableName}");
        $tr = '';
        foreach ($rows as $row) {
            $id = !empty($row->external_id)? $row->external_id:'-';
            $createtime = !empty($row->createtime)? $row->createtime:'-';
            $products = json_decode($row->products);
            $skus = '-';
            if ($products) {
                $skus = array_map(function($product){
                    return $product->sku ?? '-';
                },$products);
                $skus = implode(',', $skus);
            }
            $email = '-';
            $customer  = json_decode($row->customer);
            if ($customer){
                $email = $customer->email ?? '-';
            }
            $tr = $tr . '<tr>
						   <td>' . $id . '</td>
                           <td>' . $skus . '</td>
                           <td>' . $createtime . '</td>
                           <td>' . $email . '</td>
                        </tr>';

        }
        $abandonedCartTable = '
        <hr/>
        <table align="center" class="widefat striped fixed">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Productos</th>
                            <th>Fecha de Creacion</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                    '.$tr.'
                    </tbody>
                </table>';
        echo wp_kses_post($abandonedCartTable);
    }
    public function apikeyItemMenuCallback($args){      
        $apikeyValue = get_option( 'woowupApikey' );
		printf(
			'<input type="text" id="woowupApikey" name="woowupApikey" value="%s" />',
			isset( $apikeyValue ) ? esc_attr( $apikeyValue ) : ''
		);
		$html = '<label for="woowupApikey">' . $args[0] . '</label>';
		echo wp_kses_post( $html );
    }
}
WoowUp_ACW_AbandonedCart::getInstance();
