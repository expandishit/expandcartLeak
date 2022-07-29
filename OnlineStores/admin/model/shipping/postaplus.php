<?php
class ModelShippingPostaPlus extends Model {

  	/**
  	* @const strings API URLs.
  	*/
    const BASE_API_TESTING_URL  = 'https://staging.postaplus.net/APIService/PostaWebClient.svc?wsdl';
    const BASE_API_LIVE_URL     = 'https://www.postaplus.net/APIServices/ShippingClient.svc?Wsdl';


  	/**
  	* [POST]Create new shipment Order.
    * @param Array   $order data to be shipped.
  	* @return Response Object contains newly created order details
  	*/
    public function createShipment($order){
      $client = new SoapClient($this->_getBaseUrl());  // The trace param will show you errors stack

      try{
          $response = $client->Shipment_Creation($order);
          return $response;          
      } 
      catch (Exception $e){ 
          return ['error' => $e->getMessage()];
      }

    }


    /*  Helper Methods */
    private function _getBaseUrl(){
    	//Check if API is in Debugging Mode..
    	$is_debugging_mode = $this->config->get('postaplus_debugging_mode');

    	return ( isset($is_debugging_mode) && $is_debugging_mode == 1 ) ? self::BASE_API_TESTING_URL : self::BASE_API_LIVE_URL;
    }
}
