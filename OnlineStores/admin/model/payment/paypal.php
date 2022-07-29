<?php

Class ModelPaymentPaypal extends Model {
    
	private $expandClientId 	= PAYPAL_MERCHANT_CLIENTID,//'Acqck9HX5JN36kU-O63Opr1mrtcZib6i83Q3-GRM5U4a7_YY5AqUr8oftBSgExDcAfMiEuIF4VegrLW7',
			$expandSecret 		= PAYPAL_MERCHANT_SECRET, //'ECOpqQK1udkc6_TB4pksIq5JkZFCPyk3BWYdMY3-_msgtu5yrKGrbQOqavW-VuGBy-fndFpEuXXhkuo5',
			$expandMerchantId 	= PAYPAL_MERCHANT_MERCHANTID, //'6SMXGVHE3FCF4',
			$baseUrl 			= PAYPAL_MERCHANT_BASEURL, //'https://api.sandbox.paypal.com',
			$tokenTable			= 'paypal_tokens';

	private $storesPaypalAccountsTable = 'stores_paypal_accounts';
 
 	public function __construct ($registry){
		parent::__construct($registry);
		
		if(defined("STAGING_MODE") && STAGING_MODE == 1){
			$this->tokenTable =  'paypal_sandbox_tokens';
		}
	}
	
    public function createTracking($order_id, $orderStatus, $shipping, $trackId) {

        $result =  $this->getTrackingAndTransactionId($order_id);

        $trackingStatuses = $this->getTrackingStatuses();

        $status = "ON_HOLD";

        if(in_array($orderStatus, array_keys($trackingStatuses))) {


            $status = $this->getPaypalSelectedStatus($orderStatus);//strtoupper($trackingStatuses[$orderStatus]);



            $curl = curl_init();

            $token = $this->createTokent()['access_token'];

            $bytes = random_bytes(20);

            $data = [];
            $data["trackers"] = [];
            $data["trackers"][] = [
                "transaction_id" => $result["transaction_id"],
                "tracking_number" => $trackId,
                "status" => $status,
                "carrier" => strtoupper($shipping)

            ];



            $header = base64_encode(json_encode(['alg' => 'none']));
            $body = base64_encode(json_encode([ 'iss' => $this->expandClientId, 'email' => $this->config->get("paypal_email")]));
            $authHeader = $header . "." . $body . ".";


            curl_setopt_array($curl, array(
                CURLOPT_URL =>  $this->baseUrl . "/v1/shipping/trackers-batch",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    "Authorization: Bearer $token",
                    "PayPal-Partner-Attribution-Id: ExpandCart_Cart_MEA",
                    "PayPal-Auth-Assertion: $authHeader"

                ),
            ));

            $resposeJson = curl_exec($curl);
            curl_close($curl);
            if(json_decode($resposeJson, true)["errors"][0]["details"][0]["issue"] == "INVALID_CARRIER") {

                $this->createTracking($order_id, $orderStatus, "OTHER", $trackId);
            }

        }




    }

    public function getTrackingStatuses() {

        $query = $this->db->query("SELECT order_status_id, name FROM order_status where language_id = 1 AND order_status_id in (select value from setting where `key` in ('paypal_order_status_shipping', 'paypal_order_status_pending', 'paypal_order_status_failed', 'paypal_order_status_id'))");

        $statuses = $query->rows;

        $result = [];

        foreach ($statuses as $stat) {

            $result[$stat["order_status_id"]] = $stat["name"];

        }


        return $result;
    }

    public function getPaypalSelectedStatus($stat_id) {

        $query = $this->db->query("SELECT `key` from setting where `group` = 'paypal' AND `key` != 'paypal_status' AND value = " . $stat_id);

        $result =  $query->row;


        if(strstr($result['key'], "shipping")) {
            $keyName = "SHIPPED";
        } else if($result["key"] == "paypal_order_status_pending") {
            $keyName = "ON_HOLD";
        } else if(strstr($result["key"], "id")) {
            $keyName = "DELIVERED";
        } else if($result["key"] == "paypal_order_status_failed") {
            $keyName = "CANCELLED";
        }

        return $keyName;


    }

    public function checkIfTrackingExist($order_id) {

        $result =  $this->getTrackingAndTransactionId($order_id);

        $token = $this->createTokent()['access_token'];

        $url = $this->baseUrl . "/v1/shipping/trackers/" . $result["transaction_id"] . "-" . $result["tracking_id"];

        $curl = curl_init();

        $header = base64_encode(json_encode(['alg' => 'none']));
        $body = base64_encode(json_encode([ 'iss' => $this->expandClientId, 'email' => $this->config->get("paypal_email")]));
        $authHeader = $header . "." . $body . ".";

        curl_setopt_array($curl, array(
            CURLOPT_URL =>  $url ,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Bearer $token",
                "PayPal-Partner-Attribution-Id: ExpandCart_Cart_MEA",
                "PayPal-Auth-Assertion: $authHeader"

            ),
        ));

        $resposeJson = curl_exec($curl);
        curl_close($curl);

        $result = json_decode($resposeJson, true);


        if(isset($result["transaction_id"])) {
            return true;
        }

        return false;
    }

    public function handelRefund($data) {

        $header = base64_encode(json_encode(['alg' => 'none']));
        $body = base64_encode(json_encode([ 'iss' => $this->expandClientId, 'email' => $this->config->get("paypal_email")]));
        $authHeader = $header . "." . $body . ".";


        // 1- get trasaction data

        $query = "SELECT details FROM " . DB_PREFIX . "payment_transactions WHERE order_id = '$data[order_id]';";

        $trasactionData = json_decode($this->db->query($query)->row['details'], true);

        // 2- get refund end point

        $refundEndPoint = $trasactionData['purchase_units'][0]['payments']['captures'][0]['links'][1]['href'];

        // 3- build request body

        $requestBody = $this->buildRefundRequestBody($data);

        // 4- get auth token

        $token = $this->createTokent()['access_token'];

        $bytes = time() . rand(10, 1000);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "$refundEndPoint",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($requestBody),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Bearer $token",
                "PayPal-Partner-Attribution-Id: ExpandCart_Cart_MEA",
                "PayPal-Auth-Assertion: $authHeader",
                "PayPal-Request-Id: $bytes"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function addAccountRecord($data) {
		$email 				= $data['email']??'';
		$merchant_id 		= $data['merchant_id']??'';

		$query = [];
        $query[] = 'INSERT INTO ' . $this->storesPaypalAccountsTable . ' SET';
        $query[] = ' paypal_email  = "' . $this->ecusersdb->escape($email) . '",';
        $query[] = ' paypal_merchant_id  = "' . $this->ecusersdb->escape($merchant_id) . '",';
        $query[] = ' storecode  = "' . STORECODE . '",';
        $query[] = ' created_at  = NOW()';
        //$query[] = ' ON DUPLICATE KEY UPDATE';
		//$query[] = ' email  = "' . $this->ecusersdb->escape($email) . '",';
        //$query[] = ' merchant_client_id  = "' . $this->ecusersdb->escape($merchant_client_id) . '",';
        //$query[] = ' updated_at  = NOW()';
        
		$this->ecusersdb->query(implode(' ', $query));
        $requestId = $this->ecusersdb->getLastId();
		
        return  $requestId;
    }

	public function enableBeforeOnboardingFlow(){
		
		$this->load->model('setting/setting');
		
		$this->model_setting_setting->insertUpdateSetting('paypal', [
						"paypal_payment_before_onboarding"	=> 1,
						"paypal_status" 					=> 1,
						"paypal_view_checkout"				=> 1,
						"paypal_order_status_id"			=> 2,
						"paypal_order_status_pending"		=> 1,
						"paypal_order_status_failed"		=> 7,
						"paypal_order_status_shipping"		=> 3,
						"paypal_account_connected"			=> 0	
					]);
		return;	
	}
	
	public function checkIfPaypalZeroCountries($merchantCountry) {

        $payZeroCountries = array("EG", "DZ", "SC", "LS", "MW");

        return in_array(strtoupper($merchantCountry), $payZeroCountries);
    }
	
	public function uninstall(){
		
		if($this->config->has('paypal_account_id')){
			$paypal_account_id = $this->config->get('paypal_account_id');
			$this->updateAccountStatus($paypal_account_id,"uninstalled");
		}
	}
	
	//update the account status at ecusers
	public function updateAccountStatus($id, $status="uninstalled"){
		$query = [];
        $query[] = 'UPDATE ' . $this->storesPaypalAccountsTable . ' SET';
        $query[] = ' status  = "' . $this->ecusersdb->escape($status). '",' ;
        $query[] = ' updated_at  = NOW()';
        $query[] = ' where id = ' . (int)$id;
		
		$this->ecusersdb->query(implode(' ', $query));
        $requestId = $this->ecusersdb->getLastId();
		
        return  $requestId;
	}
	
    /* ===================================================================== */

    private function buildRefundRequestBody($data) {

        $refundArray = [
            'amount' => [
                "value" => number_format($data['amount'], 2),
                "currency_code" => $data['currency_code']
            ],
        ];

        return $refundArray;
    }

	public function checkMerchantstatus($payPalMerchantId, $token = "") {

		if(empty($token)){
			$token = $this->createTokent()['access_token'];
		}
		
		
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->baseUrl . "/v1/customer/partners/{$this->expandMerchantId}/merchant-integrations/{$payPalMerchantId}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Bearer $token"
            ),
        ));

        $responseArray = json_decode(curl_exec($curl), true);
        curl_close($curl);

        return $responseArray;
    }

	public function orderData($order_id, $token = "") {

		if(empty($token)){
			$token = $this->createTokent()['access_token'];
		}
		
		$bytes = time() . rand(10, 1000);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->baseUrl . "/v2/checkout/orders/{$order_id}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "PayPal-Request-Id: $bytes",
                "Authorization: Bearer $token"
            ),
        ));

        $responseArray = json_decode(curl_exec($curl), true);
        curl_close($curl);

        return $responseArray;
    }

    /* ===================================================================== */

    public function createTokent() {

        $query = $this->ecusersdb->query("SELECT * from " . $this->tokenTable );
        $result = $query->row;
        if(count($result) > 0) {

            $tokenDatetime 	= $result["paypal_token_time"];
			$timezone  		= $result["timezone"]; 
            $expiresIn 		= (int) $result["paypal_token_expire"];

			if(isset($result["paypal_token"]) 
				&& !$this->_tokenExpired($tokenDatetime,$timezone,$expiresIn)
			) {
				return [
						"access_token" => $result["paypal_token"] 
						];
			} 
        }


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->baseUrl . '/v1/oauth2/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_USERPWD => $this->expandClientId . ":" . $this->expandSecret,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
                'Accept-Language: en_US',

            ),
        ));

        $tokenArray = json_decode(curl_exec($curl), true);
        curl_close($curl);


        $curdate = new DateTime("now",new DateTimeZone("UTC"));
        $curdate = $curdate->format('Y-m-d H:i:s');

        if(count($result) == 0) {
            $this->ecusersdb->query("INSERT INTO " . $this->tokenTable. " SET paypal_token = '" . $tokenArray["access_token"] . "', `paypal_token_time` = '" . $curdate . "', `paypal_token_expire` = '" . $tokenArray["expires_in"] . "', `timezone` = 'UTC'");
        } else {
            $this->ecusersdb->query("UPDATE " . $this->tokenTable . " SET paypal_token = '" . $tokenArray["access_token"] . "', `paypal_token_time` = '" . $curdate . "', `paypal_token_expire` = '" . $tokenArray["expires_in"] . "', `timezone` = 'UTC'");
        }

        return $tokenArray;
    }

	private function _tokenExpired ($tokenDatetime,$timezone,$expiresIn){
		
		$tokenTime = new DateTime($tokenDatetime);
		$currentDt = new DateTime("now",new DateTimeZone($timezone));
			
		$tokenTime->setTimezone(new DateTimeZone($timezone));

		$diff = $currentDt->diff($tokenTime);
		
		$daysInSecs  = $diff->format('%r%a') * 24 * 60 * 60;
		$hoursInSecs = $diff->h * 60 * 60;
		$minsInSecs  = $diff->i * 60;

		$seconds = $daysInSecs + $hoursInSecs + $minsInSecs + $diff->s;

		return  ($seconds < 0) ||  ( $seconds > ($expiresIn - 300));
	}
	
    public function getTrackingAndTransactionId($order_id) {

        $query = "SELECT transaction_id, details from payment_transactions where order_id = " . (int) $order_id;

        $result = $this->db->query($query);

        $details = json_decode($result->row["details"]);

        return ["transaction_id" => $result->row["transaction_id"], "tracking_id" => $details->tracking_id ];


    }

}
