<?php
class ModelPaymentTamara extends Model {

    const BASE_API_TESTING_URL    = 'https://api-sandbox.tamara.co';
    const BASE_API_PRODUCTION_URL = 'https://api.tamara.co';

    public function install(){
		    $this->load->model('setting/setting');

        //Add statuses
        $tamara_statuses = [
          // 'new'        => ['code' => 'new' ,'name_en' => 'New', 'name_ar' => 'New'],
          'approved'   => ['code' => 'approved', 'name_en' => 'Approved', 'name_ar' => 'Approved'],
          'authorized' => ['code' => 'authorized', 'name_en' => 'Authorized', 'name_ar' => 'Authorized'],
          'canceled'   => ['code' => 'canceled' , 'name_en' => 'Canceled', 'name_ar' => 'Canceled'],
          'captured'   => ['code' => 'captured' , 'name_en' => 'Captured', 'name_ar' => 'Captured'],
          'refunded'   => ['code' => 'refunded', 'name_en' => 'Refunded', 'name_ar' => 'Refunded'],
        ];

        $this->model_setting_setting->insertUpdateSetting( 'tamara', ['tamara_statuses' => $tamara_statuses]);

        $this->load->model('setting/extension');
        $this->model_setting_extension->install('payment', 'tamara_installment'); 
    }

    public function uninstall(){
        $this->load->model('setting/extension');
        $this->model_setting_extension->uninstall('payment', 'tamara_installment'); 

        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('tamara_installment');
  	}

    public function capturePayment($order_id, $order_info, $shipping_cost, $tax_amount){
      
        $settings   = $this->config->get('tamara');
        $api_token  = $settings['api_token'];

        $this->load->model('extension/payment_transaction');
        $paymentTransaction = $this->model_extension_payment_transaction->selectByOrderId($order_id);

        $data = [
          'order_id' => (string) $paymentTransaction['transaction_id'],
          'total_amount' => 
          [
            'amount'   => $paymentTransaction['amount'],
            'currency' => $paymentTransaction['currency'],
          ],
          'tax_amount' => 
          [
            'amount'   => round(floatval($this->currency->convertUsingRatesAPI($tax_amount, $this->config->get('config_currency'), $order_info['currency_code'])), 2) ?: 0,
            'currency' => $order_info['currency_code'],
          ],
          'shipping_amount' => 
          [
            'amount'   => round(floatval($this->currency->convertUsingRatesAPI($shipping_cost, $this->config->get('config_currency'), $order_info['currency_code'])), 2) ?: 0,
            'currency' => $order_info['currency_code'],
          ],
          'discount_amount' => 
          [
            'amount' => '0',
            'currency' => $order_info['currency_code'],
          ],
          'items' => $this->getItems($order_id, $order_info['currency_code']),
          'shipping_info' => 
          [
            'shipped_at'       => '',
            'shipping_company' => $order_info['shipping_method'],
            'tracking_number'  => $order_info['tracking'],
            'tracking_url'     => $order_info['shipping_tracking_url'],
          ],
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $this->_getBaseUrl() . "/payments/capture",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => json_encode($data),
          CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "Authorization: Bearer $api_token"
          ),
        ));

        $response = json_decode(curl_exec($curl), true);

        $err = curl_error($curl);

        curl_close($curl);

        if( $response['capture_id'] ){
            $this->db->query("UPDATE `" . DB_PREFIX . "payment_transactions` SET details = '" . serialize($response) . "' WHERE id = " . (int)$paymentTransaction['id'] . ";");
        }
        $log = new Log('ex.' . time() . '.capture-tamara.json');
        $log->write(json_encode($response));
    }

    public function refundPayment($order_id, $order_info, $shipping_cost, $tax_amount){
        $settings   = $this->config->get('tamara');
        $api_token  = $settings['api_token'];

        $this->load->model('extension/payment_transaction');
        $paymentTransaction = $this->model_extension_payment_transaction->selectByOrderId($order_id);

        $data = [
          'order_id' => (string) $paymentTransaction['transaction_id'],
          'refunds' => [
            [
              'capture_id' => (string) unserialize($paymentTransaction['details'])['capture_id'],
              'total_amount' => 
              [
                'amount'   => $paymentTransaction['amount'],
                'currency' => $paymentTransaction['currency'],
              ],
              'tax_amount' => 
              [
                'amount'   => round(floatval($this->currency->convertUsingRatesAPI($tax_amount, $this->config->get('config_currency'), $order_info['currency_code'])), 2) ?: 0,
                'currency' => $order_info['currency_code'],
              ],
              'shipping_amount' => 
              [
                'amount'   => round(floatval($this->currency->convertUsingRatesAPI($shipping_cost, $this->config->get('config_currency'), $order_info['currency_code'])), 2) ?: 0,
                'currency' => $order_info['currency_code'],
              ],
              'discount_amount' => 
              [
                'amount' => '0',
                'currency' => $order_info['currency_code'],
              ],
              'items' => $this->getItems($order_id, $order_info['currency_code'])  
            ]            
          ]
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $this->_getBaseUrl() . "/payments/refund",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => json_encode($data),
          CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "Authorization: Bearer $api_token"
          ),
        ));

        $response = json_decode(curl_exec($curl), true);
        $err = curl_error($curl);

        curl_close($curl);

        if( !empty($response['refunds']) ){
            $this->db->query("UPDATE `" . DB_PREFIX . "payment_transactions` SET details = '" . serialize($response) . "' WHERE id = " . (int)$paymentTransaction['id'] . ";");
        }

        $log = new Log('ex.' . time() . '.refund-tamara.json');
        $log->write(json_encode($response));
    }

    public function cancelPayment($order_id, $order_info, $shipping_cost, $tax_amount){
        $settings   = $this->config->get('tamara');
        $api_token  = $settings['api_token'];

        $this->load->model('extension/payment_transaction');
        $paymentTransaction = $this->model_extension_payment_transaction->selectByOrderId($order_id);

        $data = [
          'total_amount' => 
          [
            'amount'   => $paymentTransaction['amount'],
            'currency' => $paymentTransaction['currency'],
          ],
          'tax_amount' => 
          [
            'amount'   => round(floatval($this->currency->convertUsingRatesAPI($tax_amount, $this->config->get('config_currency'), $order_info['currency_code'])), 2) ?: 0,
            'currency' => $order_info['currency_code'],
          ],
          'shipping_amount' => 
          [
            'amount'   => round(floatval($this->currency->convertUsingRatesAPI($shipping_cost, $this->config->get('config_currency'), $order_info['currency_code'])), 2) ?: 0,
            'currency' => $order_info['currency_code'],
          ],
          'discount_amount' => 
          [
            'amount'   => '0',
            'currency' => $order_info['currency_code'],
          ],
          'items'      => $this->getItems($order_id, $order_info['currency_code']),
          'shipping_info' => 
          [
            'shipped_at'       => '',
            'shipping_company' => $order_info['shipping_method'],
            'tracking_number'  => $order_info['tracking'],
            'tracking_url'     => $order_info['shipping_tracking_url'],
          ],
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $this->_getBaseUrl() . "/orders/".(string) $paymentTransaction['transaction_id']."/cancel",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => json_encode($data),
          CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "Authorization: Bearer $api_token"
          ),
        ));

        $response = json_decode(curl_exec($curl), true);
        $err = curl_error($curl);

        curl_close($curl);

        if( $response['cancel_id'] ){
            $this->db->query("UPDATE `" . DB_PREFIX . "payment_transactions` SET details = '" . serialize($response) . "' WHERE id = " . (int)$paymentTransaction['id'] . ";");
        }

        $log = new Log('ex.' . time() . '.cancel-tamara.json');
        $log->write(json_encode($response));
    }

    public function _getBaseUrl(){
      //Check current API Mode..
      $debugging_mode = $this->config->get('tamara')['debugging_mode'];
      return ( isset($debugging_mode) && $debugging_mode == 1 ) ? self::BASE_API_TESTING_URL : self::BASE_API_PRODUCTION_URL;
    }

    public function getItems($order_id, $to_currency_code){
        $from_currency = $this->config->get('config_currency');
        $products = $this->getOrderProducts($order_id);

        $items = [];

        foreach ($products as $key => $product) {
          $item = [];
          $item['reference_id'] = (string)$product['product_id'];
          $item['type']         = $product['category_name'] ?? '__';
          $item['name']         = $product['product_name'];
          $item['sku']          = (string)$product['sku'];
          $item['quantity']     = $product['quantity'];
          
          $item['unit_price']['amount']     = round(floatval($this->currency->convertUsingRatesAPI($product['price'], $from_currency, $to_currency_code)), 2);
          $item['unit_price']['currency']   = $to_currency_code;
          
          $item['discount_amount']['amount']   = '0';
          $item['discount_amount']['currency'] = $to_currency_code;
          
          $item['tax_amount']['amount']     = round(floatval($this->currency->convertUsingRatesAPI($product['tax'], $from_currency, $to_currency_code)), 2) ?: 0;
          $item['tax_amount']['currency']   = $to_currency_code;
          
          $item['total_amount']['amount']   = round(floatval($this->currency->convertUsingRatesAPI($product['total'], $from_currency, $to_currency_code)), 2);
          $item['total_amount']['currency'] = $to_currency_code;
          
          $items[] = $item;
        }
        return $items;
    }

    public function getOrderProducts($order_id){
        return $this->db->query("
          SELECT op.order_id, op.product_id,op.quantity, op.tax, op.total, op.price,
        (SELECT `name` from category_description where language_id = 1 and category_id = (
          select category_id from `product_to_category` pc where pc.product_id = op.product_id LIMIT 1
        )) as category_name, 
        pd.name as product_name, 
        IF(p.sku is null or p.sku = '', p.product_id, p.sku) as sku
         
        FROM `" . DB_PREFIX . "order_product` op 
        join `" . DB_PREFIX . "product` p on p.product_id = op.product_id
        join `" . DB_PREFIX . "product_description` pd on pd.product_id = op.product_id

        where order_id=" . (int)$order_id . " and pd.language_id = 1;")->rows;
    }
}
