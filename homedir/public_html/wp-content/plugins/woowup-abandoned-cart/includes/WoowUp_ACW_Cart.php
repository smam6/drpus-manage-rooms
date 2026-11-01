<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
class WoowUp_ACW_Cart{
    const MINUTE_IN_SECONDS = 60;
    private static $instance;

    public static function getInstance()
    {
        if(!isset(self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('woocommerce_checkout_update_order_review',array($this, 'updateCart'));
        add_action('woocommerce_add_to_cart', [ $this, 'updateCart' ]);
        add_action('woocommerce_cart_item_removed', [ $this, 'updateCart' ]);
        add_action('woocommerce_cart_item_restored', [ $this, 'updateCart' ]);
        add_action('woocommerce_after_cart_item_quantity_update', [ $this, 'updateCart' ]);

        add_action('woocommerce_new_order',array($this, 'removeCart'));
        add_action('woocommerce_checkout_order_created',array($this, 'removeCart')); 
        add_action('woocommerce_thankyou',array($this, 'removeCart'));

        add_filter('cron_schedules',array( $this, 'sendCartTime' ) );

        if (!wp_next_scheduled( 'woowup_abandonedcart_send_cart') ){
            wp_schedule_event( time(), 'every_ten_minutes', 'woowup_abandonedcart_send_cart' );
        }
        add_action('woowup_abandonedcart_send_cart' ,array( $this,'sendCart') );
        if (!wp_next_scheduled( 'woowup_abandonedcart_purge') ){
            wp_schedule_event( time(), 'daily', 'woowup_abandonedcart_purge' );
        }
        add_action('woowup_abandonedcart_purge', array( $this, 'purgeExpiredCarts') );
        add_filter('wp', array( $this, 'recoverCart' ), 10 );
    }

    public function recoverCart($fields = array()){
        global $wpdb;
        $tableName = $wpdb->prefix . WOOWUP_ABANDONEDCART_RECOBERY_TABLE;
        $cartId = filter_input( INPUT_GET, 'abandoned_cart', FILTER_SANITIZE_STRING );
        if(isset($cartId) && !empty($cartId)){
            $cartId = base64_decode($cartId);
            $cart = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tableName} WHERE external_id = %s", $cartId));
            if(isset($cart) && !empty($cart)){
                WC()->session->set('woowupSessionId',null);
                WC()->cart->empty_cart();
                wc_clear_notices();
                $products = json_decode($cart->products,true);
                foreach ( $products as $product ) {
                    $id             = $product['product_id'];
                    $qty            = $product['quantity'];
                    $variationId = $product['variation_id'];
                    $variation = $product['variation'];
                    WC()->cart->add_to_cart( $id, $qty,$variationId,$variation);
                }
            }
        }
        return $fields;
    }

    public function sendCartTime($schedules)
    {
		$schedules['every_ten_minutes'] = array(
			'interval' => 10 * self::MINUTE_IN_SECONDS,
			'display'  => 'Cada 10 minutos',
		);
		return $schedules;
    }

    private function isValidToSend($cartDate){
        $old = new DateTime($cartDate);
        $now = new DateTime();
        $minutes = ($now->getTimestamp() - $old->getTimestamp()) / 60;
        return $minutes >= WOOWUP_ABANDONEDCART_MINUTES_TO_SEND_CART;
    }


    public function purgeExpiredCarts()
    {
        WoowUp_ACW_Logger::info('Purgando carritos expirados');
        global $wpdb;
        $tableName = $wpdb->prefix . WOOWUP_ABANDONEDCART_TABLE;
        $expiry = date('Y-m-d H:i:s', strtotime('-48 hours'));
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$tableName} WHERE createtime < %s",
            $expiry
        ));
    }


    public function sendCart()
    {
        global $wpdb;
        $tableName = $wpdb->prefix . WOOWUP_ABANDONEDCART_TABLE;
        $recoverTableName = $wpdb->prefix . WOOWUP_ABANDONEDCART_RECOBERY_TABLE;

        $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tableName} ORDER BY createtime ASC LIMIT %d", 50));
        foreach($rows as $row){
            if(!$this->isValidToSend($row->createtime)){
                continue;
            }
            $customer = $row->customer;
            $existCustomer = WoowUp_ACW_WoowUpApi::existCustomer($customer);
            if (is_null($existCustomer)) {
                continue;
            } elseif (!$existCustomer) {
                $customerWasCreated = WoowUp_ACW_WoowUpApi::createCustomer($customer);
                if (!$customerWasCreated) {
                    continue;
                }
            }
            $isCartCreated = WoowUp_ACW_WoowUpApi::createAbandonedCart($row);
            if(!is_null($isCartCreated) && $isCartCreated){
                $external_id = $row->external_id;
                $products_info = $row->products_info;
                $wpdb->delete($tableName,array('external_id'=>$external_id));
                $wpdb->replace(
                    $recoverTableName,
                    array(
                        'external_id' => $external_id,
                        'customer'  => $customer,
                        'products'  => $products_info
                    )
                    );
            }
        }
    }

    public function getCustomers()
    {
        $currentUser = wp_get_current_user();
        $currentUserId = $currentUser->ID;
        $customer = [];
        $customer['email'] = sanitize_email($currentUser->user_email);
        $customer['first_name'] =  sanitize_text_field($currentUser->user_firstname);
        $customer['last_name'] = sanitize_text_field($currentUser->user_lastname);
        $customer['telephone'] = sanitize_text_field(get_user_meta( $currentUserId, 'billing_phone', true ));
        $customer['document'] = sanitize_text_field(get_user_meta( $currentUserId, 'billing_document', true ));
        foreach($customer as $key => $value){
            if(empty($value)){
                unset($customer[$key]);
            }
        }
        if(!isset($customer['email']) && !isset($customer['telephone']) && !isset($customer['document'])){
            return [];
        }
        return $customer;
    }

    /**
     * Removes an abandoned cart linked to the current user's session.
     * @return void
     */
    public function removeCart()
    {
        global $wpdb;
        if (!WC()->session) {
            return;
        }
        $sessionId= WC()->session->get( 'woowupSessionId' );
        if(isset($sessionId)){
            $external_id = $sessionId;
            $tableName = $wpdb->prefix . WOOWUP_ABANDONEDCART_TABLE;
            $wpdb->delete($tableName,array('external_id'=>$external_id));
            WC()->session->set( 'woowupSessionId',null);
        }
    }

    private function validSessionId($sessionId){
        if(!isset($sessionId)){
            $currentUser = wp_get_current_user();
            $currentUserId = $currentUser->ID;
            $sessionId = md5($currentUserId);
            WC()->session->set( 'woowupSessionId', $sessionId );
            return $sessionId;
        } else {
            return $sessionId;
        }
    }

    /**
     * Updates the current cart session by saving or updating the cart data in the database.
     * If the cart is empty, it removes the cart data.
     * Processes customer information, cart item and variations
     *
     * @return void
     */
    public function updateCart()
    {
        $sessionId= WC()->session->get('woowupSessionId');
        $sessionId = $this->validSessionId($sessionId);
        $createtime = date(DATE_ISO8601);
        if (WC()->cart->is_empty()) {
            $this->removeCart();
            return;
        }
        $total = WC()->cart->total;
        $external_id = $sessionId;
        $items = WC()->cart->get_cart();
        $recoverUrl = wc_get_checkout_url().'?abandoned_cart='.base64_encode($external_id);
        $productsToRecover = [];
        $products = [];
        foreach($items as $item => $value){
            $product_data = $value['data'];
            $id = $value['product_id'];
            $variation_id = $value['variation_id'];
            $quantity = $value['quantity'];
            $variation = [];
            if(isset($value['variation']) && !empty($value['variation'])){
                foreach ( $value['variation']  as $key => $value ) {
                    $variation[ $key ] = $value;
                }
            }
            $sku = $product_data->get_sku();
            $unit_price = $product_data->get_price();
            $productsToRecover[] = [
                'product_id' => $id,
                'variation_id' => $variation_id,
                'variation' => $variation,
                'quantity' => $quantity
            ];
            $products[] = [
                'sku' => $sku,
                'quantity' => (int)$quantity,
                'unit_price' => (float)$unit_price
            ];
        }
        $customer = $this->getCustomers();
        if(empty($products) || empty($customer)){
            return;
        }
        global $wpdb;
        $tableName = $wpdb->prefix . WOOWUP_ABANDONEDCART_TABLE;
        $wpdb->replace(
            $tableName,
            array(
                'external_id'    => $external_id,
                'customer'  => json_encode($customer),
                'products'    => json_encode($products),
                'products_info' => json_encode($productsToRecover),
                'total_price' => $total,
                'recover_url' => $recoverUrl,
                'createtime' => $createtime,
            )
        );
    }
}
WoowUp_ACW_Cart::getInstance();