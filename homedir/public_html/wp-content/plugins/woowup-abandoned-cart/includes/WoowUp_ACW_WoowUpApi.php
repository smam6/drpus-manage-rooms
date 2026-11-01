<?php
class WoowUp_ACW_WoowUpApi{
    private static $url = 'https://api.woowup.com/apiv3';

    const MAX_ATTEMPTS  = 25;
    const MAX_SLEEP_SEC = 60;

    private static $retryResponses = [429, 502, 503];

    private static function request($verb, $url, $args) {
        $attempts = 0;

        while ($attempts < self::MAX_ATTEMPTS) {
            $response = $verb === 'GET'
                ? wp_remote_get($url, $args)
                : wp_remote_post($url, $args);

            if (is_wp_error($response)) {
                return null;
            }

            $code = wp_remote_retrieve_response_code($response);

            if (!in_array($code, self::$retryResponses)) {
                return $response;
            }

            if ($attempts >= self::MAX_ATTEMPTS - 1) {
                WoowUp_ACW_Logger::info('[ERROR] HTTP ' . $code . ' on attempt ' . ($attempts + 1) . '/' . self::MAX_ATTEMPTS . '. Max attempts reached.');
                return null;
            }

            WoowUp_ACW_Logger::info('[WARNING] HTTP ' . $code . ' on attempt ' . ($attempts + 1) . '/' . self::MAX_ATTEMPTS . '. Retrying...');
            sleep(self::calculateSleep($response, $attempts));
            $attempts++;
        }

        return null;
    }

    private static function calculateSleep($response, $attempts) {
        $code = wp_remote_retrieve_response_code($response);
        if ($code === 429) {
            $retryAfter = (int) wp_remote_retrieve_header($response, 'retry-after');
            if ($retryAfter > 0) {
                return $retryAfter;
            }
        }

        return min((int) pow(2, $attempts), self::MAX_SLEEP_SEC);
    }

    public static function existCustomer($customer){
        $customer = json_decode($customer,true);
        $body = [];
        if(isset($customer['email']) && !empty($customer['email'])){
            $body['email'] = $customer['email'];
        }
        if(isset($customer['telephone']) && !empty($customer['telephone'])){
            $body['telephone'] = $customer['telephone'];
        }
        if(empty($body)){
            return null;
        }
        $args = array(
            'headers' => array(
                'Authorization' => 'Basic '. get_option('woowupApikey'),
                'Content-Type'  => 'application/json',
                'Accept' => 'application/json'),
            'body' => $body);
        $response = self::request('GET', self::$url.'/multiusers/exist', $args);
        if(is_null($response)){
            return null;
        }
        $body = wp_remote_retrieve_body($response);
        $body = json_decode($body);
        return $body->payload->exist ?? null;
    }

    public static function createCustomer($customer){
        if(empty($customer)){
            return null;
        }
        $args = array('headers' => array(
            'Authorization' => 'Basic '. get_option('woowupApikey'),
            'Content-Type'  => 'application/json',
            'Accept' => 'application/json'),
            'body' => $customer);
        $response = self::request('POST', self::$url.'/users', $args);
        if(is_null($response)){
            return null;
        }
        $code = wp_remote_retrieve_response_code($response);
        return $code == 201;
    }

    public static function createAbandonedCart($cart){
        if(empty($cart)){
            return null;
        }
        $abandonedCart = [];
        $customer = json_decode($cart->customer,true);
        if (empty($customer['email'])) {
            return null;
        }
        $abandonedCart['email'] = $customer['email'];
        $abandonedCart['total_price'] = (float)$cart->total_price;
        $abandonedCart['external_id'] = $cart->external_id;
        $abandonedCart['source'] = 'web';
        $abandonedCart['recover_url'] = $cart->recover_url;
        //$abandonedCart['createtime'] = $cart->createtime;
        $abandonedCart['products'] = [];
        $products = json_decode($cart->products,true);
        foreach($products as $product){
            $abandonedCart['products'][] =[
                'sku' => $product['sku'],
                'quantity' => (int)$product['quantity'],
                'unit_price' => (float)$product['unit_price']
            ];
        }
        $abandonedCart = json_encode($abandonedCart);
        $args = array('headers' => array(
            'Authorization' => 'Basic '. get_option('woowupApikey'),
            'Content-Type'  => 'application/json',
            'Accept' => 'application/json'),
            'body' => $abandonedCart);
        $response = self::request('POST', self::$url.'/multiusers/abandoned-cart', $args);
        if(is_null($response)){
            return null;
        }
        $code = wp_remote_retrieve_response_code($response);
        return $code == 201;
    }
}
