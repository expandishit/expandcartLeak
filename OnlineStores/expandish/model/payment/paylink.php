<?php

/**
 * Model for handling paylink payment data
 * 
 * @author MohamedHassanWD <hassan.mohamed.sf@gmail.com>
 * @copyright 2020 ExpandCart
 */
class ModelPaymentPaylink extends Model
{

	/**
	 * Get the method data for the payment method
	 * 
	 * @param array $address 
	 * @return mixed
	 */
  	const PRODUCTION_URL= 'https://restapi.paylink.sa/api/';
  	const TEST_URL      = 'https://restpilot.paylink.sa/api/';

	public function getMethod($address)
	{
		$this->language->load_json('payment/paylink');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int) $this->config->get('paylink_geo_zone_id') . "' AND country_id = '" . (int) $address['country_id'] . "' AND (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('paylink_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();
		if ($status) {
            $title = $this->getFieldName('paylink');
			$method_data = array(
				'code'       => 'paylink',
				'title'      => $title,
				'sort_order' => $this->config->get('paylink_sort_order')
			);
		}

		return $method_data;
	}

	/**
	 * Get the settings for paylink payment
	 *
	 * @return array settings
	 */
	public function getSettings()
	{
		$settings = [];
		$fields = [
			'completed_order_status_id', 
			'failed_order_status_id',  
			'api_key', 
			'public_key', 
			'test_mode',
		];
		foreach ($fields as $field) {
			$settings['paylink_' . $field] = $this->config->get('paylink_' . $field);
		}
		return $settings;
	}

    protected function getFieldName($paymentMethodCode)
    {
        $this->load->model('localisation/language');
        $this->load->model('setting/setting');

        $current_lang = $this->session->data['language'];
        $paymentMethodData = $this->model_setting_setting->getSetting($paymentMethodCode);
        $language = $this->model_localisation_language->getLanguageByCode($current_lang);
        $current_lang = $language['language_id'];

        if (!empty($paymentMethodData[$paymentMethodCode.'_field_name_' . $current_lang])) {
            $title = $paymentMethodData[$paymentMethodCode.'_field_name_' . $current_lang];
        } else {
            $title = $this->language->get('text_title');
        }
        return $title;
    }

 	public function login(){
		// Set the request URL base on the environment
		if($this->config->get('paylink_test_mode') == '1'){
			$url = self::TEST_URL."auth";
		}else{
			$url = self::PRODUCTION_URL."auth";
		}
		$credentials =  array('apiId' => $this->config->get('paylink_app_id'), "persistToken"=> false ,'secretKey' => $this->config->get('paylink_secret_key') );
		// cURL request
		$curl = curl_init();
		curl_setopt_array($curl, [
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS =>json_encode($credentials),
		  CURLOPT_HTTPHEADER => [
		    "Content-Type: application/json",
		  ],
		]);
		$result = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		// in case of cURL error, set the error into array
        $result = $this->errorHandler($curl, $result, $http_status);
		// return values base on the return type parameters
		return json_decode($result);
	} 

	public function createInvoice($token,$data){
		// Set the request URL base on the environment
		if($this->config->get('paylink_test_mode') == '1'){
			$url = self::TEST_URL."addInvoice";
		}else{
			$url = self::PRODUCTION_URL."addInvoice";
		}
		// cURL request
		$curl = curl_init();
		curl_setopt_array($curl, [
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS =>json_encode($data),
		  CURLOPT_HTTPHEADER => [
		    "Content-Type: application/json",
		    "Authorization: Bearer ".$token
		  ],
		]);
		$result = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		// in case of cURL error, set the error into array
        $result = $this->errorHandler($curl, $result, $http_status);
		// return values base on the return type parameters
		return json_decode($result);
	}

	public function getInvoice($token,$transactionNo){
		// Set the request URL base on the environment
		if($this->config->get('paylink_test_mode') == '1'){
			$url = self::TEST_URL."getInvoice/".$transactionNo;
		}else{
			$url = self::PRODUCTION_URL."getInvoice/".$transactionNo;
		}
		// cURL request
		$curl = curl_init();
		curl_setopt_array($curl, [
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => [
		    "Content-Type: application/json",
		    "Authorization: Bearer ".$token
		  ],
		]);
		$result = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		// in case of cURL error, set the error into array
        $result = $this->errorHandler($curl, $result, $http_status);
		// return values base on the return type parameters
		return json_decode($result);
	}

	private function errorHandler($curlConnection, $result, $httpCode){
        $err = curl_error($curlConnection);
        if($err){
            $resultClass  = new stdClass;
            $resultClass->error = 'curl';
            $resultClass->message = $err;
            return json_encode($resultClass);
        }elseif($httpCode != 200){
            $resultClass  = new stdClass;
            $resultClass->error = $httpCode;
            $resultClass->message = $result;
            return json_encode($resultClass);
        }

        return $result;
    }

}

