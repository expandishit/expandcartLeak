<?php
class ModelShippingQuickShip extends Model {


	/**
	 * @const strings API URLs.
	 */
    const BASE_API_LOGIN_URL = 'https://c.quick.sa.com/API/Login/';
    const BASE_API_GENERAL_URL = 'https://c.quick.sa.com/API/V3/';
  	

	function getQuote($address){
		
		$this->language->load_json('shipping/quick_ship');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('quick_ship_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('quick_ship_geo_zone_id')) {
			$status = true;
		} elseif ( $query->num_rows ) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();

		if ($status) {
			$quote_data = array();
			
			$order_cost = $this->_calculateOrderCost((int)$address['zone_id']);
			
			$quote_data['quick_ship'] = array(
				'code'         => 'quick_ship.quick_ship',
				'title'        => $this->language->get('quick_ship_title'),
				'cost'         => $order_cost,
				'tax_class_id' => $this->config->get('quick_ship_tax_class_id'),
				'text'         => $this->currency->format($this->tax->calculate($order_cost, $this->config->get('quick_ship_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
			);

			$method_data = array(
				'code'       => 'quick_ship.quick_ship',
				'title'      => $this->language->get('quick_ship_title'),
				'quote'      => $quote_data,
				'sort_order' => 0,
				'error'      => false
			);
		}

		return $method_data;
	}


	private function _calculateOrderCost($zone_id){
		//get zone name in Arabic
		$query = $this->db->query("SELECT name FROM `zones_locale` WHERE zone_id = '" . $this->db->escape($zone_id) . "' AND lang_id = 2");
		$zone_arabic_name = $query->row['name'];
		
		if(!$zone_arabic_name)
			return 0;

		//Get zone id in Quick System, GetCityByName API
		$username = htmlspecialchars_decode($this->config->get('quick_ship_username'));
		$password = htmlspecialchars_decode($this->config->get('quick_ship_password'));

		//Get an access token
		$response      = $this->getAccessToken($username, $password);
		$access_token  = $response->resultData->access_token;

		$result = $this->getCityIdByName($access_token, $zone_arabic_name);
		$quick_city_id = $result->resultData->id;
		$quick_city_name = $result->resultData->name;

		//Call getShippingCost API
		$data_cost = [
		  'CityId'            => $quick_city_id,
		  'CityAsString'      => $quick_city_name,
		  'PaymentMethodId'   => $this->config->get('quick_ship_payment_method_id')?:1, //prepaid is default
		  'AddedServicesIds'  => $this->config->get('quick_ship_added_services_ids')?:[]
		];

		$response   = $this->getShippingCost($access_token , $data_cost);

		//return cost
		return $response->resultData->totalCost?:0;
	}


	  	/**
	* [POST]Authenticate the user then get “access token” and “refresh token” in the result.
	*
    * @param string  $username
    * @param string  $password
	*
	* @return Response Object contains access token & refresh token 
	*/
    public function getAccessToken($username, $password){
    	//API URL
    	$url = self::BASE_API_LOGIN_URL . 'GetAccessToken';

		// Request data
    	$data = "UserName=". urlencode($username) ."&Password=".urlencode($password);
		
		// Initializes a new cURL session
		$curl = curl_init($url);

		// Set the CURLOPT_RETURNTRANSFER option to true to return response in a variable
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		// Set the CURLOPT_POST option to true for POST request
		curl_setopt($curl, CURLOPT_POST, true);

		// Set the request data as JSON using json_encode function
		curl_setopt($curl, CURLOPT_POSTFIELDS,  $data);
		// curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($data));

		// Set custom headers for RapidAPI Auth and Content-Type header
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
		  'Content-Type: application/x-www-form-urlencoded'
		]);

		// Execute cURL request with all previous settings
		$response = curl_exec($curl);

		// Close cURL session
		curl_close($curl);

		// var_dump(json_decode($response));
		return json_decode($response);
    }


      	/**
	* [POST]Get estimated shipping cost for a shipping order before create it.
	*
    * @param string  $access_token
    * @param Array  $data contains $city_id, $payment_method_id, $added_services_ids
	*
	* @return Response Object contains cost
	*/
    public function getShippingCost($access_token, $data){
    	//API URL
    	$url = self::BASE_API_GENERAL_URL . 'Store/Shipment/GetShippingCost';

		// Initializes a new cURL session
		$curl = curl_init($url);

		// Set the CURLOPT_RETURNTRANSFER option to true to return response in a variable
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		// Set the CURLOPT_POST option to true for POST request
		curl_setopt($curl, CURLOPT_POST, true);

		// Set the request data as JSON using json_encode function
		curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($data));

		// Set custom headers for RapidAPI Auth and Content-Type header
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
		  'Content-Type: application/json',
		  "Authorization: Bearer $access_token"
		]);

		// Execute cURL request with all previous settings
		$response = curl_exec($curl);

		// Close cURL session
		curl_close($curl);

		return json_decode($response);
    }


        /**
	* [POST]Get city id in quicksa system by it's name.
	*
    * @param string  $access_token
    * @param Array   $city_name ARABIC ONLY
	*
	* @return Response Object contains city id, http status code, response message in Arabic & English.
	*/
    public function getCityIdByName($access_token, $city_name){
    	//API URL
    	$url = self::BASE_API_GENERAL_URL . 'GetCityIdByName';

    	$data = [ 'CityName' => $city_name ];

		// Initializes a new cURL session
		$curl = curl_init($url);

		// Set the CURLOPT_RETURNTRANSFER option to true to return response in a variable
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		// Set the CURLOPT_POST option to true for POST request
		curl_setopt($curl, CURLOPT_POST, true);

		// Set the request data as JSON using json_encode function
		curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($data));

		// Set custom headers for RapidAPI Auth and Content-Type header
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
		  'Content-Type: application/json',
		  "Authorization: Bearer $access_token"
		]);

		// Execute cURL request with all previous settings
		$response = curl_exec($curl);

		// Close cURL session
		curl_close($curl);

		return json_decode($response);
    }

	
}
