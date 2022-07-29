<?php

class ModelShippingMydhl extends Model {

  	/**
  	 * @const strings API URLs.
  	 */
    const BASE_API_TESTING_URL = 'https://express.api.dhl.com/mydhlapi/test/';
    const BASE_API_LIVE_URL    = 'https://express.api.dhl.com/mydhlapi/';

    public function install()
    {
        //Remove the old API, so it will appeared in the order info page shipping methods dropdown
        $this->load->model('setting/extension');
        $this->model_setting_extension->uninstall('shipping', 'dhl_express'); 
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('dhl_express');
    }

    public function uninstall()
    {
        
    }

  	/**
  	* [POST]Create new shipment Order.
  	*
    * @param Array   $order data to be shipped.
  	*
  	* @return Response Object contains newly created order details
  	*/
    public function createShipment($order)
    {
        $settings = $this->config->get('mydhl');

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $this->_getBaseUrl().'shipments',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>json_encode($order),
          CURLOPT_HTTPHEADER => array(
            'Authorization: Basic '.base64_encode($settings['username'] . ':' . $settings['password']),
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err      = curl_error($curl);
        curl_close($curl);

        if ($err) {
         return ['error' => $err];
        } else {
            return [
                'status_code'  => $httpcode,
                'result'       => json_decode($response, true)
            ];
        }
    }

    /*  Helper Methods */
    private function _getBaseUrl(){
      //Check if API is in Debugging Mode..
      $is_debugging_mode = $this->config->get('mydhl')['debugging_mode'];
      return ( isset($is_debugging_mode) && $is_debugging_mode == 1 ) ? self::BASE_API_TESTING_URL : self::BASE_API_LIVE_URL;
    }
}
