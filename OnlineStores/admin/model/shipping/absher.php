<?php
class ModelShippingAbsher extends Model {

	/**
	* @const strings API URLs.
	*/
    const BASE_API_URL     = 'https://absher.fastcoo-solutions.com/lm/shipmentBookingApi_lm.php';


	/**
	* [POST]Create new shipment Order.
    * @param Array   $order data to be shipped.
	* @return Response Object contains newly created order details
	*/
    public function createShipment($data){

    	$url = self::BASE_API_URL . '?' . rawurldecode(http_build_query($data));

  		$curl = curl_init($url);

  		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  		$response = curl_exec($curl);

  		curl_close($curl);

  		return json_decode($response, true);//convert to array  		
    }

}
