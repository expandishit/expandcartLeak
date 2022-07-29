<?php

class ModelShippingNyazik extends Model {

  	/**
  	 * @const strings API URLs.
  	 */
    const BASE_API_TESTING_URL = 'https://www.nyazik.com/v3/shipment/';
    const BASE_API_LIVE_URL    = 'https://www.nyazik.com/v3/shipment/';

    public function install()
    {
        //
    }

    public function uninstall()
    {
        //
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
        $settings = $this->config->get('nyazik');
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $this->_getBaseUrl().'create',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => json_encode($order),
          CURLOPT_HTTPHEADER => array(
            'access-token: '.$settings['access_token'],
            'charset: utf-8',
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);        
        return json_decode($response, true);
    }

    /*  Helper Methods */
    private function _getBaseUrl(){
      //Check if API is in Debugging Mode..
      $is_debugging_mode = $this->config->get('nyazik')['debugging_mode'];
      return ( isset($is_debugging_mode) && $is_debugging_mode == 1 ) ? self::BASE_API_TESTING_URL : self::BASE_API_LIVE_URL;
    }
}
