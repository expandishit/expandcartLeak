<?php

class ModelPaymentPaypal extends Model {

    private $integrationPayPalTable = DB_PREFIX . 'paypal_merchants';

    private $expandClientId = PAYPAL_MERCHANT_CLIENTID, //'Acqck9HX5JN36kU-O63Opr1mrtcZib6i83Q3-GRM5U4a7_YY5AqUr8oftBSgExDcAfMiEuIF4VegrLW7',
        $expandSecret 		= PAYPAL_MERCHANT_SECRET, // 'ECOpqQK1udkc6_TB4pksIq5JkZFCPyk3BWYdMY3-_msgtu5yrKGrbQOqavW-VuGBy-fndFpEuXXhkuo5',
        $expandPartnerCode	= PAYPAL_MERCHANT_PARTNERCODE,//'ExpandCart_Cart_MEA',
        $baseUrl			= PAYPAL_MERCHANT_BASEURL,//'https://api.sandbox.paypal.com',
        $expandMerchantId	= PAYPAL_MERCHANT_MERCHANTID,//'6SMXGVHE3FCF4',
		$tokenTable			= 'paypal_tokens';

    /* ===================================================================== */
	
	public function __construct ($registry){
		parent::__construct($registry);
		
		if(defined("STAGING_MODE") && STAGING_MODE == 1){
			$this->tokenTable =  'paypal_sandbox_tokens';
		}
	}
	
    public function getMethod($address, $total) {

        $settings = $this->config->get('paypal');
        
        $payPalSettings = $this->model_setting_setting->getSetting('paypal');
        $current_lang = $this->config->get('config_language_id');

        $this->language->load_json('payment/paypal');
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int) $settings['geo_zone_id'] . "' AND `country_id` = '" . (int) $address['country_id'] . "' AND (`zone_id` = '" . (int) $address['zone_id'] . "' OR `zone_id` = '0')");
        if ($settings['total'] > $total) {
            $status = false;
        } elseif (!$settings['geo_zone_id']) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = array();
        if ($status) {
            $method_data = array(
                'code' => 'paypal',
                'title' => (!empty($payPalSettings['paypal_field_name_' . $current_lang])) ? $payPalSettings['paypal_field_name_' . $current_lang] : $this->language->get('text_title'),
                'terms' => '',
                'sort_order' => $settings['sort_order']
            );
        }
        return $method_data;
    }

    private function createToken() { //any transaction done with PayPal is need token to auth

        $query = $this->ecusersdb->query("SELECT * from " . $this->tokenTable );
        $result = $query->row;
        
		if(count($result) > 0) {

            $tokenDatetime 	= $result["paypal_token_time"];
			$timezone  		= $result["timezone"]; 
            $expiresIn 		= (int) $result["paypal_token_expire"];

			if(isset($result["paypal_token"]) 
				&& !$this->_tokenExpired ($tokenDatetime,$timezone,$expiresIn)
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

	public function createOrder($data){

		$token = $this->createToken()['access_token'];
        $bytes = time() . rand(10, 1000);

		$headers = array(
            'Content-Type: application/json',
            "Authorization: Bearer $token",
            "PayPal-Partner-Attribution-Id: ExpandCart_Cart_MEA",
            "PayPal-Request-Id: $bytes"
        );
		
		$curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL				=> $this->baseUrl . '/v2/checkout/orders',
            CURLOPT_RETURNTRANSFER	=> true,
            CURLOPT_MAXREDIRS		=> 10,
            CURLOPT_TIMEOUT			=> 0,
            CURLOPT_FOLLOWLOCATION	=> true,
            CURLOPT_HTTP_VERSION	=> CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST	=> 'POST',
            CURLOPT_POSTFIELDS		=> json_encode($data , JSON_INVALID_UTF8_IGNORE),
            CURLOPT_HTTPHEADER		=> $headers,
        ));

        $response = curl_exec($curl);
        curl_close($curl);
		
		return $response;	
	}
	
	public function orderCaptureData($order_id){

        $token = $this->createToken()['access_token'];
        $bytes = $order_id;

        $headers = array(
            'Content-Type: application/json',
            "Authorization: Bearer $token",
            "PayPal-Partner-Attribution-Id: ExpandCart_Cart_MEA",
            "PayPal-Request-Id: $bytes"
        );
		
		$curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL 			=> $this->baseUrl . "/v2/checkout/orders/{$order_id}/capture",
            CURLOPT_RETURNTRANSFER 	=> true,
            CURLOPT_MAXREDIRS 		=> 10,
            CURLOPT_TIMEOUT 		=> 0,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_HTTP_VERSION 	=> CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST 	=> "POST",
            CURLOPT_HTTPHEADER 		=> $headers ,
        ));
        
		$response = curl_exec($curl);
        curl_close($curl);
        
		return $response;
		
	}
	
}
