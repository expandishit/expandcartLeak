<?php
class ModelPaymentEButler extends Model {
    /**
    * @var constant
    */
    const GATEWAY_NAME = 'ebutler';
    //
    const BASE_API_BASE_URL    = 'https://saas-api.e-butler.com';

    public function getMethod($address, $total) {
      $this->language->load_json("payment/" . self::GATEWAY_NAME);

      $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get( self::GATEWAY_NAME ."_geo_zone_id") . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

      $status = !$this->config->get(self::GATEWAY_NAME . "_geo_zone_id") || $query->num_rows ? true : false;

      $method_data = [];

      if ($status) {
          $method_data = [
            'code'       => self::GATEWAY_NAME,
            'title'      => $this->_getTitle(),
            'sort_order' => 0
          ];
        }
        // var_dump($method_data);die();
        return $method_data;
    }

    private function _getTitle(){
        $this->language->load_json('payment/ebutler');
        $title_presentation_type = $this->config->get('ebutler_presentation_type');
        $title = $this->language->get('text_title');

        switch ($title_presentation_type) {
            case 'image':
                $title = '<img src="' . HTTPS_ECD . '/image/' . $this->config->get('ebutler_display_image')  .'" style="float:none"/>';
                break;
            case 'text':
                $title = $this->config->get('ebutler_display_text')[$this->config->get('config_language_id')] ?: $this->language->get('text_title');
                break;
        }
        return $title;
    }

    private function _getOrderAmount($order_id, $amount, $currency_code){
    		$currency_code = strtoupper($currency_code);
        $account_currency_code = $this->config->get('ebutler_account_currency');

    		if( $currency_code == $account_currency_code ){
    			return round($amount, 2);
    		}
        elseif ( $currency_code !== 'USD' ) {
            $currenty_rate     = $this->currency->gatUSDRate($currency_code);
            $amount_in_dollars = $currenty_rate * $amount;

            $target_currency_rate = $this->currency->gatUSDRate($account_currency_code);
            $amount_in_account_currency = $amount_in_dollars/$target_currency_rate;
            return round($amount_in_account_currency, 2);
  		  }
  		  //If USD convert it directly to BHD
  		  else{
  			    $target_currency_rate = $this->currency->gatUSDRate($account_currency_code);
            $amount_in_account_currency  = $amount/$target_currency_rate;
            return round($amount_in_account_currency, 2);
        }
    }

    public function pay($order_id){
      $this->load->model('checkout/order');
      $order_info = $this->model_checkout_order->getOrder($order_id);

      if (!$order_info) return false;

      $order_total = $this->_getOrderAmount($order_id, $order_info['total'], $order_info['currency_code']);

      $data = [
        'customer_name' => $order_info['firstname'] . ' ' . $order_info['lastname'],
        'customer_phone'=> $order_info['payment_telephone'],
        "country_code"  => $order_info['payment_phonecode'] ?: $order_info['shipping_phonecode'],
        'amount'        => $order_total,
        'language'      => 'english',
        'payment_method'=> 'credit_cards',
        'source'        => 'expand_cart',
        'callback_url'  =>  $this->url->link('payment/ebutler/response')
      ];

      if( $this->_isDebuggingMode() ){
        $data['is_test'] = true;
      }

      $curl = curl_init(self::BASE_API_BASE_URL . "/payment");
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($data));
      curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'saasapikey: ' . $this->config->get('ebutler_api_key'),
        "Content-Type: application/json",
        "cache-control: no-cache"
      ]);

      //Just for local use, should be deleted on server..
      // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

      $response = curl_exec($curl);
      $err = curl_error($curl);


      if($err) return ['error' => $err];

      return json_decode($response , true);
    }


    private function _isDebuggingMode(){
    	//Check current API Mode..
    	$mode = $this->config->get('ebutler_debugging_mode');
    	return ( isset($mode) && $mode == 1 ) ? true : false;
    }
}
