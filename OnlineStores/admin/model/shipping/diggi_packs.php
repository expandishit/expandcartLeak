<?php

class ModelShippingDiggiPacks extends Model{


  /**
   * @const strings API URLs.
   */
  const BASE_API_URL    = 'https://app.diggipacks.com/API/';
  /**
	* [POST]Create new shipment Order.
	*
  * @param Array   $order data to be shipped.
	*
	* @return Response Object contains newly created order details
	*/
  public function createShipment($sign,$param){
    	$url = self::BASE_API_URL . '/createOrder';
      $data = array(
        "sign"       => $sign,
        "format"     =>"json",
        "signMethod" => "md5",
        "param"      =>$param,
        "customerId" =>$this->config->get('diggi_packs_uid_number'),
        "method"     => "createOrder"
      );
  		$curl = curl_init($url);
  		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  		curl_setopt($ch, CURLOPT_HEADER, true);
  		curl_setopt($curl, CURLOPT_POST, true);
  		curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($data));
  		curl_setopt($curl, CURLOPT_HTTPHEADER, [
  		  'Content-Type: application/json',
  		]);

  		// Execute cURL request with all previous settings
  		$response = curl_exec($curl);
  		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

  		// Close cURL session
  		curl_close($curl);

  		return [
  			'status_code' => $httpcode,
  			'result'      => json_decode($response, true)
  		];
  }

  // will be used when diggipacks provide us with getCities endpoint from their side
  /*
  public function getCities($country_code){
    $url  = self::BASE_API_URL .'/cities/?country_code='.$country_code;
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    dd($response);
    return json_decode($response, true);
  }
  */

}
?>
