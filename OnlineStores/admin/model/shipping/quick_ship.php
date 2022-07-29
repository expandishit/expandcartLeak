<?php
class ModelShippingQuickShip extends Model {

	/**
	 * @const strings API URLs.
	 */
    const BASE_API_LOGIN_URL = 'https://c.quick.sa.com/API/Login/';
    const BASE_API_GENERAL_URL = 'https://c.quick.sa.com/API/V3/';
    
    public function getStatusListIds($ready_status_not_included = FALSE){

    	$sql = "SELECT order_status_id FROM `" . DB_PREFIX  . "quick_ship`";

    	if($ready_status_not_included){
    		$sql .= " WHERE quick_ship_status_id <> 0 ";
    	}

        $query = $this->db->query( $sql);
    	
    	return array_column($query->rows , 'order_status_id');
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
    	$url = ModelShippingQuickShip::BASE_API_LOGIN_URL . 'GetAccessToken';

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
	* [POST]Get a new access token without resending your credentials.
	*
	* @param string  $old_refresh_token.
	*
	* @return Response Object contains new access token & new refresh token 
	*/
    public function refreshToken($old_refresh_token){
    	// API URL
    	$url = ModelShippingQuickShip::BASE_API_LOGIN_URL . 'RefreshToken';

		// Request data
    	$data = "refresh_token=$old_refresh_token";

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
	* [POST]Create new shipment Order.
	*
    * @param string  $access_token
    * @param Array   $data shipment order details
	*
	* @return Response Object contains newly created order details
	*/
    public function createShipment($access_token, $data){
    	//API URL
    	$url = ModelShippingQuickShip::BASE_API_GENERAL_URL . 'Store/Shipment';

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
	* [GET]Get shipment data.
	*
    * @param string    $access_token
    * @param string    $shipment_id
	*
	* @return Response Object contains shipment order details
	*/
    public function getShipment($access_token, $shipment_id){
    	//API URL
    	$url = ModelShippingQuickShip::BASE_API_GENERAL_URL . 'Store/Shipment/' . $shipment_id;

		// Initializes a new cURL session
		$curl = curl_init($url);

		// Set the CURL options
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
		  "Authorization: Bearer $access_token"
		]);

		// Execute cURL request with all previous settings
		$response = curl_exec($curl);

		// Close cURL session
		curl_close($curl);

		return json_decode($response);
    }

  	/**
	* [GET]Get a set of shipments details according to $start & $length parameters (like paging) 
	*
    * @param string  $access_token
    * @param int     $start shipment index to start from
    * @param int     $length how many shipment to get from the start point, MAX is 50 per one call.
	*
	* @return Array of shipments objects.
	*/
    public function getShipments($access_token, $start, $length){
    	//API URL
    	$url = ModelShippingQuickShip::BASE_API_GENERAL_URL . "Store/Shipment/$start/$length";

		// Initializes a new cURL session
		$curl = curl_init($url);

		// Set the CURL options
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
		  "Authorization: Bearer $access_token"
		]);

		// Execute cURL request with all previous settings
		$response = curl_exec($curl);

		// Close cURL session
		curl_close($curl);

		return json_decode($response);
    }

  	/**
	* [GET]Get shipment label data.
	*
    * @param string  $access_token
    * @param string  $shipment_id
	*
	* @return Response Object contains shipment label details
	*/
    public function getShippingLabelInfo($access_token, $shipment_id){
    	//API URL
    	$url = ModelShippingQuickShip::BASE_API_GENERAL_URL . 'Store/Shipment/ShippingLabelInfo/' . $shipment_id;

		// Initializes a new cURL session
		$curl = curl_init($url);

		// Set the CURL options
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
		  "Authorization: Bearer $access_token"
		]);

		// Execute cURL request with all previous settings
		$response = curl_exec($curl);

		// Close cURL session
		curl_close($curl);

		return json_decode($response);
    }

  	/**
	* [GET]Download a PDF file contains the shipping label for a shipment.
	*
    * @param string    $access_token
    * @param string    $shipment_id
	*
	* @return Response Object contains file data is included in response data in string base64 format that you can decode and convert to binary file
	*/
    public function getShippingLabelAsPDF($access_token, $shipment_id){
    	//API URL
    	$url = ModelShippingQuickShip::BASE_API_GENERAL_URL . 'Store/Shipment/ShippingLabelPDF/' . $shipment_id;

		// Initializes a new cURL session
		$curl = curl_init($url);

		// Set the CURL options
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
		  "Authorization: Bearer $access_token"
		]);

		// Execute cURL request with all previous settings
		$response = curl_exec($curl);

		// Close cURL session
		curl_close($curl);

		// Download pdf file
		$response = json_decode($response);
		if($response->httpStatusCode == 200){
			$this->_downloadShippingLabelPDF($response->resultData, $shipment_id);
			return true;
		}
		return false;
    }

	/**
	* Decode base64 content to pdf and set the appropriate http headers to download the file.
	*
    * @param string    $base64pdf
    * @param string    $shipment_id
	*
	* @return void
	*/
    private function _downloadShippingLabelPDF($base64pdf , $shipment_id){
		//Decode pdf content
		$pdf_decoded = base64_decode ($base64pdf);
		header('Content-Type: application/pdf');
		header("Content-Disposition:attachment;filename=shipment-$shipment_id-label.pdf");
		echo $pdf_decoded;
    }

  	/**
	* [GET]Get the Ids of the consistent data in quicksa system.
	*
    * @param string    $access_token
    * @param string    $shipment_id
	*
	* @return Response Object contains countryCityLis, paymentMethodList, servicesList, shipmentContentTypeList, shippingMethodsList
	*/
    public function getConsistentData($access_token){
    	//API URL
    	$url = ModelShippingQuickShip::BASE_API_GENERAL_URL . 'GetConsistentData';

		// Initializes a new cURL session
		$curl = curl_init($url);

		// Set the CURL options
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
		  "Authorization: Bearer $access_token"
		]);

		// Execute cURL request with all previous settings
		$response = curl_exec($curl);

		// Close cURL session
		curl_close($curl);

		return json_decode($response);
    }

  	/**
	* [Delete]Delete shipment order.
	*
    * @param string   $access_token
    * @param string   $shipment_id
	*
	* @return Response Object contains operation status
	*/
    public function cancelShipment($access_token, $shipment_id){
    	//API URL
    	$url = ModelShippingQuickShip::BASE_API_GENERAL_URL . 'Shipment/' . $shipment_id;

		// Initializes a new cURL session
		$curl = curl_init($url);

		// Set the CURL options
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');

		curl_setopt($curl, CURLOPT_HTTPHEADER, [
		  "Authorization: Bearer $access_token"
		]);

		// Execute cURL request with all previous settings
		$response = curl_exec($curl);

		// Close cURL session
		curl_close($curl);

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
    	$url = ModelShippingQuickShip::BASE_API_GENERAL_URL . 'Store/Shipment/GetShippingCost';

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
	* [POST] Track one or more shipments.
	*
    * @param string  $access_token
    * @param Array   $shipments_ids 
	*
	* @return Response Object contains shipments tracking info.
	*/
    public function getTrackingInfo($access_token, $shipments_ids){
    	//API URL
    	$url = ModelShippingQuickShip::BASE_API_GENERAL_URL . 'Store/Shipment/Track';

		// Collection object
		$data = [
		  'ShipmentsIds'  => $shipments_ids 
		];

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
    	$url = ModelShippingQuickShip::BASE_API_GENERAL_URL . 'GetCityIdByName';

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

    /**
	* Get ONE Order info from DB to push.
	*
	* @return Array order info .
	*/
	public function getOrderToPush($order_id){
		$query = $this->db->query("
			SELECT order_id, store_id, firstname, lastname, email, telephone, 
			payment_address_1, payment_address_2 ,payment_city, currency_code, total, payment_method, payment_code, comment, customer_id, 
			shipping_country_id, shipping_address_1, shipping_address_2, shipping_city, shipping_zone as zone_name,
			country.`iso_code_2` as country_code

			FROM `" . DB_PREFIX . "order` 
        	LEFT JOIN country ON order.shipping_country_id = country.country_id

			WHERE order_id='".$order_id."';");

        return $query->row;
    }

    /**
	* Get All Orders from DB which Quick shipping eligible.
	*
	* @return Array orders that have quick_shipping status.
	*/
	public function getOrdersToPush(){
		$query = $this->db->query("
			SELECT order_id, store_id, firstname, lastname, email, telephone, 
			payment_address_1, payment_address_2 ,payment_city, currency_code, total, payment_method, payment_code, comment, customer_id, 
			shipping_country_id, shipping_address_1, shipping_address_2, shipping_city, shipping_zone as zone_name,
			country.`iso_code_2` as country_code

			FROM `" . DB_PREFIX . "order` 
        	LEFT JOIN order_status ON order.order_status_id = order_status.order_status_id
        	LEFT JOIN country ON order.shipping_country_id = country.country_id

			WHERE order_status.`order_status_id` = '" . $this->config->get('quick_ship_ready_shipping_status') . "' AND order_status.`language_id` = '" . $this->config->get('config_language_id') . "'");

        return $query->rows;
    }

	public function getOrdersToTrack(){
		$query = $this->db->query("SELECT order_id, tracking, order.order_status_id , order_status.name FROM `" . DB_PREFIX . "order` 
        	LEFT JOIN order_status ON order.order_status_id = order_status.order_status_id
			WHERE order_status.`order_status_id` IN ('" .   implode("', '", $this->getStatusListIds())    . "') AND order_status.`language_id` = '" . $this->config->get('config_language_id') . "'");
		// print_r($query->rows);die();
        return $query->rows;
    }

    public function getSupportedCountries(){
    	//get username & password from DB, setting table
	    $username = htmlspecialchars_decode($this->config->get('quick_ship_username'));
	    $password = htmlspecialchars_decode($this->config->get('quick_ship_password'));

	    //Get an access token
	    $response      = $this->getAccessToken($username, $password);
	    $access_token  = $response->resultData->access_token;
	  
	    //Call get Consistent data API to get QuickGateway server data
	    $response = $this->getConsistentData($access_token);

	    //Convert array of stdclass object to normal php array
	    $arr = json_decode(json_encode($response->resultData->countryCityList), true);

	    //Extract contries iso-code-2/alpha2code
		return array_column($arr, 'id','alpha2Code');
    }

    public function install(){

	    //Create new table to store statues ids
	    $this->db->query("
	      CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "quick_ship` (
	        `id` int(11) NOT NULL AUTO_INCREMENT,
	        `order_status_id` int(11) NOT NULL,
	        `quick_ship_status_id` int(11) NOT NULL,
	         PRIMARY KEY (`id`)
	      ) ENGINE=InnoDB DEFAULT COLLATE=utf8_general_ci;");


		//Add Quick statuses in DB
		$quick_statuses = [
		  [1 => ["name" => "Quick Shipping", 'quick_ship_id' => 0] ,2 => ["name" => "شحن كويك"] ],
		  [1 => ["name" => "Quick New Order", 'quick_ship_id' => 8] ,2 => ["name" => "طلب كويك جديد"]],
		  [1 => ["name" => "Quick Picked Up From Store", 'quick_ship_id' => 30] ,2 => ["name" => " كويك تم الاستلام من المتجر"]],
		  [1 => ["name" => "Quick In Riyadh Warehouse", 'quick_ship_id' => 7  ] ,2 => ["name" => "في مستودع الرياض كويك" ]],
		  [1 => ["name" => "Quick In Jeddah Warehouse", 'quick_ship_id' => 27  ] ,2 => ["name" => "في مستودع جدة كويك" ]],
		  [1 => ["name" => "Quick In Dammam Warehouse", 'quick_ship_id' => 28  ] ,2 => ["name" => "في مستودع الدمام كويك" ]],
		  [1 => ["name" => "Quick Package Left", 'quick_ship_id' => 33 ] ,2 => ["name" => "غادرت الشحنة مركز الفرز كويك" ] ],
		  [1 => ["name" => "Quick Package Received", 'quick_ship_id' => 2] ,2 => ["name" => "جاري التوصيل كويك"] ],
		  [1 => ["name" => "Held at Quick", 'quick_ship_id' => 10] ,2 => ["name" => "تم تسليم الطلب لشركة الشحن كويك"] ],
		  [1 => ["name" => "Quick Delivered", 'quick_ship_id' => 3 ] ,2 => ["name" => "تم تسليم الطلب بنجاح كويك" ] ],
		  [1 => ["name" => "Quick Rejected By Customer", 'quick_ship_id' => 4 ] ,2 => ["name" => "رفض الاستلام من العميل كويك" ] ],
		  [1 => ["name" => "Quick Rescheduled Delivery", 'quick_ship_id' => 29 ] ,2 => ["name" => "اعادة جدولة وقت التسليم كويك" ] ],
		  [1 => ["name" => "Quick Returning To Store", 'quick_ship_id' => 14 ] ,2 => ["name" => "جاري الارجاع للمتجر كويك" ] ],
		  [1 => ["name" => "Quick Returned To Store", 'quick_ship_id' => 6 ] ,2 => ["name" => "تم الارجاع للمتجر كويك" ]]
		];

		// $quick_ship_statuses_ids = [8, 30, 7, 27, 28, 33, 2, 10, 3, 4, 29, 14, 6];

		//Check if exist
		$exist = $this->db->query("
		  SELECT * FROM `" . DB_PREFIX  . "order_status` WHERE name IN ('Quick Shipping');
		");

		//Add them if doesn't exist
		if ($exist->num_rows == 0) {
		  $this->load->model('localisation/order_status');
		  $this->load->model('setting/setting');

		  foreach($quick_statuses as $status){
		      $inserted_status_id = $this->model_localisation_order_status->addOrderStatus(["order_status" => $status]);
		      
		      $this->db->query("INSERT INTO `" . DB_PREFIX  . "quick_ship` (order_status_id, quick_ship_status_id) VALUES (" . $inserted_status_id .", " . $status[1]['quick_ship_id'] .");");

		      if($status[1]['name'] === 'Quick Shipping'){
		        $this->model_setting_setting->insertUpdateSetting( 'quick_ship', ['quick_ship_ready_shipping_status' => $inserted_status_id] );
		      }
		      else if($status[1]['name'] === 'Quick New Order'){
		          $this->model_setting_setting->insertUpdateSetting( 'quick_ship', ['quick_ship_after_creation_status' => $inserted_status_id] );
		      }
		  }
		}

		//Add Tracking column in Order table if it doesn't exist
		$query = $this->db->query("SELECT COUNT(*) colcount
		                          FROM INFORMATION_SCHEMA.COLUMNS
		                          WHERE  table_name = 'order'
		                          AND table_schema = DATABASE()
		                          AND column_name = 'tracking'");
		$result = $query->row;
		$colcount = $result['colcount'];

		if($colcount <=0 ) {
		  $this->db->query("ALTER TABLE `order` ADD COLUMN `tracking`  varchar(3000) NULL");
		}
    }

    public function uninstall(){
    	//Delete quick statues from DB
	    $order_statuses_ids = $this->getStatusListIds();

	    $this->db->query( "DELETE FROM `" . DB_PREFIX  . "order_status` WHERE order_status_id IN ('" . implode("', '" , $order_statuses_ids) . "')" );

	    $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "quick_ship`;");
    }

    public function getOrderStatusId($quick_ship_status_id){
    	$result = $this->db->query("SELECT order_status_id FROM `" . DB_PREFIX  . "quick_ship` WHERE quick_ship_status_id = " . $quick_ship_status_id . " ;");
        $order_status_id = $result->row['order_status_id'];
        
        return $order_status_id;
    }

    public function saveTrackingURL($tracking_url, $tracking_number, $order_id){
        
        $this->db->query("UPDATE " . DB_PREFIX . "`order` 
        	SET 
        	shipping_tracking_url = '" . $this->db->escape($tracking_url) . "' , 
        	tracking = ". $this->db->escape($tracking_number) ." 

        	WHERE order_id = '" . $order_id . "'");
    }
}
