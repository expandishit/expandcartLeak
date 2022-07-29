<?php
class ModelShippingskynet extends Model {

	/**
	 * @const strings API URLs.
	 */
    const BASE_API_URL    = 'https://api.postshipping.com/api2';
    
    /**
	* [POST]Create new shipment Order.
	*
    * @param Array   $order data to be shipped.
	*
	* @return Response Object contains newly created order details
	*/
    public function createShipment($order){

    	$skynet_token = $this->config->get('skynet_token');
    	$url = self::BASE_API_URL . '/shipments';

    	// Initializes a new cURL session
  		$curl = curl_init($url);

  		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  		// curl_setopt($ch, CURLOPT_HEADER, true);
  		curl_setopt($curl, CURLOPT_POST, true);
  		curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($order));
  		curl_setopt($curl, CURLOPT_HTTPHEADER, [
  		  'Content-Type: application/json',
  		  "Token: {$skynet_token}"
  		]);

  		$response = curl_exec($curl);

  		curl_close($curl);
      
  		return json_decode($response, true);
    }
    
}
