<?php
class ModelShippingLabaih extends Model {

	/**
	* @const strings API URLs.
	*/
    const BASE_API_TESTING_URL    = 'https://dev.mylabaih.com/partners/api/';
    const BASE_API_PRODUCTION_URL = 'https://dev.mylabaih.com/partners/api/';


	/**
	* [POST]Create new shipment Order.
    * @param Array   $order data to be shipped.
	* @return Response Object contains newly created order details
	*/
    public function createShipment($data){
      $url = $this->_getBaseUrl() . 'order/create';
      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_HTTPHEADER => array(
          "Cache-Control: no-cache",
          "Content-Type: application/x-www-form-urlencoded",
          "cache-control: no-cache"
        ),
      ));

      $response = curl_exec($curl);

      return json_decode($response, true);//convert to array  
    }


    /*  Helper Methods */
    private function _getBaseUrl(){
      //Check if API is in Debugging Mode..
      $is_debugging_mode = $this->config->get('labaih_debugging_mode');

      return ( isset($is_debugging_mode) && $is_debugging_mode == 1 ) ? self::BASE_API_TESTING_URL : self::BASE_API_PRODUCTION_URL;
    }
}
