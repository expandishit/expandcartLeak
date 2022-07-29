<?php

class ModelShippingErsal extends Model{


  /**
   * @const strings API URLs.
   */
  const BASE_API_URL    = 'https://apis.fetchr.us/v1/public';


  /**
	* [POST]Create new shipment Order.
	*
  * @param Array   $order data to be shipped.
	*
	* @return Response Object contains newly created order details
	*/
  public function createShipment($order){

      $api_key   = htmlspecialchars_decode($this->config->get('ersal_api_key'));
    	$client_id = htmlspecialchars_decode($this->config->get('ersal_client_id'));
    	$url       = self::BASE_API_URL . '/orders';

  		$curl = curl_init($url);
  		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  		curl_setopt($ch, CURLOPT_HEADER, true);
  		curl_setopt($curl, CURLOPT_POST, true);
  		curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($order));
  		curl_setopt($curl, CURLOPT_HTTPHEADER, [
  		  'Content-Type: application/json',
        "x-api-key: $api_key",
  		  "x-client-id: $client_id"
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



  /**
  * [GET]List all cities for specific country.
  *
  * @param country_code  The coutries code in ISO 2 format in which cities list will be retrieved.
  *
  * @return Response array of cities
  */
  public function getCities($country_code){
    $url  = self::BASE_API_URL .'/cities/?country_code='.$country_code;
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    return json_decode($response, true);
  }



  public function getOrderAmountInSpecificCurrency($price, $from_currency_code, $to_currency_code){
   

    if( strcasecmp($from_currency_code , $to_currency_code) === 0){
      return round($price, 2);
    }
    elseif ( $to_currency_code !== 'USD' ) {
      return $this->_convertAmountTo($price, $from_currency_code );
    }
    //If USD convert it directly to to_currency
    else{
      $target_currency_rate = $this->currency->gatUSDRate($to_currency_code);
      $amount_final        = $price/$target_currency_rate;
      return round($amount_final, 2);
    }
  }

  private function _convertAmountTo($amount, $currency_code){
        $currenty_rate     = $this->currency->gatUSDRate($currency_code);
        $amount_in_dollars = $currenty_rate * $amount;

        $target_currency_rate = $this->currency->gatUSDRate($this->allowed_currencies[0]);
        $amount_final        = $amount_in_dollars/$target_currency_rate;
        return round($amount_final, 2);
  }



}
?>
