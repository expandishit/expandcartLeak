<?php
class ModelPaymentBookeey extends Model {
    /**
    * @var constant
    */
    const GATEWAY_NAME = 'bookeey';

    const BASE_API_TESTING_URL    = 'https://demo.bookeey.com/portal/mobileBookeeyPg?data=';
    const BASE_API_PRODUCTION_URL = 'https://www.bookeey.com/portal/mobileBookeeyPg?data=';

    public function getMethod($address, $total) {
      $this->language->load_json("payment/" . self::GATEWAY_NAME);

      $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get( self::GATEWAY_NAME ."_geo_zone_id") . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

      $status = !$this->config->get(self::GATEWAY_NAME . "_geo_zone_id") || $query->num_rows ? true : false;

      $method_data = [];

      if ($status) {
          $method_data = [
            'code'       => self::GATEWAY_NAME,
            'title'      => $this->language->get('text_title'),
            'sort_order' => 0
          ];
        }

        return $method_data;
    }

    public function pay($order_id, $total, $mode){
      $data = [];
      $data['price']   = $total;
      $data['merchantId'] = $this->config->get('bookeey_merchant_id');
      $data['secreatKey'] = $this->config->get('bookeey_secret_key');
      $data['surl']    = $this->url->link('payment/bookeey/success');
      $data['furl']    = $this->url->link('payment/bookeey/fail');
      $data['tranid']  = $order_id.'-'.time();
      $data['txntime'] = time();
      $data['hashMac'] = $this->getHashMac($data);
      $data['paymentOptions'] = $mode;

      $url = $this->_getBaseUrl() . json_encode($data, JSON_UNESCAPED_SLASHES);
      $response = file_get_contents($url);
      return json_decode($response , true);
    }


    private function getHashMac($data){
      $crossCat = "GEN";

      $hstring  = $data['merchantId'] . "|" .  $data['tranid'] . "|" .  $data['surl'] . "|" . $data['furl'] . "|" . $data['price'] . "|" . $data['txntime'] .
       "|" . $crossCat . "|" . $data['secreatKey'];

      return hash('sha512', $hstring);
    }

    private function _getBaseUrl(){
    	//Check current API Mode..
    	$mode = $this->config->get('bookeey_live_mode');
    	return ( isset($mode) && $mode == 1 ) ? self::BASE_API_PRODUCTION_URL : self::BASE_API_TESTING_URL;
    }
}
