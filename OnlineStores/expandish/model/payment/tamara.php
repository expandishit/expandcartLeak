<?php
use Firebase\JWT\JWT;

class ModelPaymentTamara extends Model {
    /**
     * @var constant
     */
    const GATEWAY_NAME = 'tamara';
    const PAYMENT_TYPE = 'PAY_BY_LATER';

    const BASE_API_TESTING_URL    = 'https://api-sandbox.tamara.co';
    const BASE_API_PRODUCTION_URL = 'https://api.tamara.co';

    public function getMethod($address, $total) {

        //check if pay option is available for merchant account is available
        if(!$this->checkPayOptionAvailability())
            return [];

        $settings = $this->config->get('tamara');

        $this->language->load_json("payment/" . self::GATEWAY_NAME);

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$settings['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
        $method_data = [];

        if( !$settings['geo_zone_id'] || $query->num_rows ){
            if($this->_areCurrencyCountryMatched($this->session->data['order_id'])){
                $converted_total    = $this->_getTotalWithCurrentCurrency($total);
                $method_data = [
                    'code'       => self::GATEWAY_NAME,
                    'title'      => $this->_getTitle($converted_total),
                    'sort_order' => 0
                ];
            }
        }

        return $method_data;
    }

    public function getOrderItems($order_id, $from_currency_code, $to_currency_code){
        $products = $this->_getOrderitemsQuery($order_id);
        $items = [];

        foreach ($products as $key => $product) {
            $item = [];
            $item['reference_id'] = (string)$product['product_id'];
            $item['type']         = $product['category_name'] ?? '__';
            $item['name']         = $product['product_name'];
            $item['sku']          = (string)$product['sku'];
            $item['quantity']     = (int)$product['quantity'];
            $item['image_url']     = HTTP_ECD . '/image/'.(string)$product['image'];
            $item['tax_amount']['amount']     = $this->currency->convert($product['tax'], $from_currency_code, $to_currency_code);
            $item['tax_amount']['currency']   = $to_currency_code;
            $item['total_amount']['amount']   = $this->currency->convert($product['total'], $from_currency_code, $to_currency_code);
            $item['total_amount']['currency'] = $to_currency_code;
            $items[] = $item;
        }
        return $items;
    }

    public function changeTamaraOrderStatus($order_id, $order_status_id){
        if($order_id && $order_status_id){
            $current_date_time = $this->ecdatetime->get_current_date_time_in_mysql_format();
            $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$order_status_id . "', date_modified = '" . $current_date_time . "' WHERE order_id = '" . (int)$order_id . "'");
        }
    }

    public function authenticate($key){
        $authorization_header = apache_request_headers()['Authorization'];
        $token = $authorization_header ? $this->_getBearerToken($authorization_header): null;

        if(!$token){
            return false;
        }
        else{
            $result = false;
            try {
                $result = JWT::decode($token, $key, ['HS256']);
            } catch (Exception $ex) {
                $log = new Log('ex.' . time() . '.Tamara-authentication.json');
                $log->write(json_encode($ex->getMessage()));
            }
            return $result;
        }
    }

    /* helper functions */
    private function _getTitle($total){
        $this->language->load_json('payment/tamara');

        $warning_msg = $this->_getLimitWarning($total);

        switch ($this->config->get('config_language')) {
            case 'ar':
                $title = '<div style=" display: flex; align-items: center; "><img style="float: unset;margin: 0;max-width: 50px;" src="/expandish/view/theme/default/image/TamaraAr.png"/><span style="margin: 0 5px;">' .$this->language->get('tamara_text_title'). '</span><br>'.$warning_msg.'</div>';
                break;
            case 'en':
                $title = '<div style=" display: flex; align-items: center; "><img style="float: unset;margin: 0;max-width: 50px;" src="/expandish/view/theme/default/image/TamaraEn.png"/><span style="margin: 0 5px;">' .$this->language->get('tamara_text_title'). '</span><br>'.$warning_msg.'</div>';
                break;
        }
        return $title;
    }

    private function _areCurrencyCountryMatched($order_id){
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);

        $country_iso_code2 = $this->_getCountryIsoCode2($order_info['payment_country_id']);

        return ($country_iso_code2 === 'AE' && $order_info['currency_code'] === 'AED') || ($country_iso_code2 === 'SA' && $order_info['currency_code'] === 'SAR') || ($country_iso_code2 === 'KW' && $order_info['currency_code'] === 'KWD') ;
    }

    private function _getBearerToken(string $authorizationHeader) {
        if (!empty($authorizationHeader) && preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function _getOrderitemsQuery($order_id){
        $lang_id = $this->config->get('config_language_id');

        return $this->db->query("
          SELECT op.order_id, p.image, op.product_id,op.quantity, op.tax, op.total, op.price,
        (SELECT `name` from category_description where language_id = " . $lang_id . " and category_id = (
          select category_id from `product_to_category` pc where pc.product_id = op.product_id LIMIT 1
        )) as category_name, 
        pd.name as product_name, 
        IF(p.sku is null or p.sku = '', p.product_id, p.sku) as sku
         
        FROM `" . DB_PREFIX . "order_product` op 
        join `" . DB_PREFIX . "product` p on p.product_id = op.product_id
        join `" . DB_PREFIX . "product_description` pd on pd.product_id = op.product_id

        where order_id=" . (int)$order_id . " and pd.language_id = " . $lang_id . ";")->rows;
    }

    public function getPaymentTypes(){
        $order_id = $this->session->data['order_id'];
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);
        $api_token  = $this->config->get('tamara')['api_token'];
        $url = $this->_getBaseUrl() . '/checkout/payment-types?country=' . $this->_getCountryIsoCode2($order_info['payment_country_id']);

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $api_token"
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true); //convert to Array...
    }

    private function _getCountryIsoCode2($country_id){
        $this->load->model('localisation/country');
        return $this->model_localisation_country->getCountry($country_id)['iso_code_2'];
    }

    //Check if the order total within the tamara allowed max&min amount...
    private function _checkIfTotalValid($total,$limits){
        return $total >= $limits['min_limit'] && $total <= $limits['max_limit'];
    }

    private function _getBaseUrl(){
        //Check current API Mode..
        $debugging_mode = $this->config->get('tamara')['debugging_mode'];
        return ( isset($debugging_mode) && $debugging_mode == 1 ) ? self::BASE_API_TESTING_URL : self::BASE_API_PRODUCTION_URL;
    }

    private function _getTotalWithCurrentCurrency($total){
        return (float)$this->currency->convert($total, $this->config->get('config_currency'),$this->currency->getCode());
    }

    private function _getLimits(){
        $payment_types = $this->getPaymentTypes();

        $limits = array_filter($payment_types, function($value){ return $value['name'] == self::PAYMENT_TYPE;} );
        $min_limit = array_column(array_column($limits, 'min_limit'), 'amount');
        $max_limit = array_column(array_column($limits, 'max_limit'), 'amount');
        return['min_limit'=>min($min_limit), 'max_limit'=>max($max_limit)];
    }

    private function _getLimitWarning($total){
        $this->language->load_json('payment/tamara_installment');

        $limits = $this->_getLimits();
        if($total< $limits['min_limit'] || $total > $limits['max_limit'])
            return '<br><span style="color: #ffa500">'.$this->language->get('order_amount_range'). $limits['min_limit'] .' - ' . $limits['max_limit'].$this->language->get('total_amount_invalid_min_limit2').'</span>';
        return '';
    }

    private function checkPayOptionAvailability(){
        $payment_types = $this->getPaymentTypes();
        return array_filter($payment_types, function($value){ return $value['name'] == self::PAYMENT_TYPE;} );
    }

}



