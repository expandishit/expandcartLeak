<?php
class ModelShippingWagon extends Model {

	/**
	* @const strings API URLs.
	*/
    const BASE_API_TESTING_URL  = 'http://go-wagon.com/wagon_backendV2/public/thirdparty/api/';
    const BASE_API_LIVE_URL     = 'http://go-wagon.com/wagon_backendV2/public/thirdparty/api/';


	/**
	* [POST]Create new shipment Order.
    * @param Array   $order data to be shipped.
	* @return Response Object contains newly created order details
	*/
    public function createShipment($order){

    	$url     = $this->_getBaseUrl() . 'create_shipment';

    	// Initializes a new cURL session
  		$curl = curl_init($url);

  		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  		curl_setopt($curl, CURLOPT_HEADER, false);
  		curl_setopt($curl, CURLOPT_POST, true);
  		curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($order));
  		curl_setopt($curl, CURLOPT_HTTPHEADER, [
  		  'Content-Type: application/json'
  		]);

  		$response = curl_exec($curl);
  		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

  		curl_close($curl);

  		return [
  			'status_code' => $httpcode,
  			'result'      => json_decode($response, true)
  		];
    }



	/**
	* [POST]GET shipment Order Details (For tracking)
    * @param Array   $order data to be shipped.
	* @return Response Object contains newly created order details
	*/
    // public function getShipmentDetails($order){

    // 	$wagon_email      = $this->config->get('wagon_email');
    // 	$wagon_password   = $this->config->get('wagon_password');
    // 	$wagon_api_secret = $this->config->get('wagon_api_secret');

    // 	$url     = $this->_getBaseUrl() . 'get_shipment_details';

    // 	// Initializes a new cURL session
  		// $curl = curl_init($url);

  		// curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  		// curl_setopt($ch, CURLOPT_HEADER, true);
  		// curl_setopt($curl, CURLOPT_POST, true);
  		// curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($order));
  		// // curl_setopt($curl, CURLOPT_HTTPHEADER, [
  		// //   'Content-Type: application/json',
  		// //   "Authorization: $api_key"
  		// // ]);

  		// $response = curl_exec($curl);
  		// $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

  		// curl_close($curl);

  		// return [
  		// 	'status_code' => $httpcode,
  		// 	'result'      => json_decode($response)
  		// ];
    // }




    /*  Helper Methods */
    private function _getBaseUrl(){
    	//Check if API is in Debugging Mode..
    	$is_debugging_mode = $this->config->get('wagon_debugging_mode');

    	return ( isset($is_debugging_mode) && $is_debugging_mode == 1 ) ? self::BASE_API_TESTING_URL : self::BASE_API_LIVE_URL;
    }
}
