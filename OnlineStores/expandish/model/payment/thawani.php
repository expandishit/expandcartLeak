<?php

/**
 * Model for handling Thawani payment data
 * 
 * @author MohamedHassanWD <hassan.mohamed.sf@gmail.com>
 * @copyright 2020 ExpandCart
 */
class ModelpaymentThawani extends Model
{

	/**
	 * Get the method data for the payment method
	 * 
	 * @param array $address 
	 * @return mixed
	 */
  	const PRODUCTION_URL= 'https://checkout.thawani.om/api/v1/';
  	const TEST_URL      = 'https://uatcheckout.thawani.om/api/v1/';

	public function getMethod($address)
	{
		$this->language->load_json('payment/thawani');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int) $this->config->get('thawani_geo_zone_id') . "' AND country_id = '" . (int) $address['country_id'] . "' AND (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('thawani_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();
		if ($status) {
            $title = $this->getFieldName('thawani');
			$method_data = array(
				'code'       => 'thawani',
				'title'      => $title,
				'sort_order' => $this->config->get('thawani_sort_order')
			);
		}

		return $method_data;
	}

	/**
	 * Get the settings for thawani payment
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
			$settings['thawani_' . $field] = $this->config->get('thawani_' . $field);
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
    
	public function addCustomer($data){
		// Set the request URL base on the environment
		if($this->config->get('thawani_test_mode') == '1'){
			$url = self::TEST_URL."customers";
		}else{
			$url = self::PRODUCTION_URL."customers";
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
		    "thawani-api-key: ".$this->config->get('thawani_api_key')
		  ],
		]);
		$result = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		
		// in case of cURL error, set the error into array
		if($err){ $result = array("curl_error" => $err); }
		// return values base on the return type parameters
		return json_decode($result);
	}
	public function createSession($data){
		// Set the request URL base on the environment
		if($this->config->get('thawani_test_mode') == '1'){
			$url = self::TEST_URL."checkout/session";
		}else{
			$url = self::PRODUCTION_URL."checkout/session";
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
		    "thawani-api-key: ".$this->config->get('thawani_api_key')
		  ],
		]);
		$result = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		// in case of cURL error, set the error into array
		if($err){ $result = array("curl_error" => $err); }
		// return values base on the return type parameters
		return json_decode($result);
	}

	public function getSession($session_id){
		// Set the request URL base on the environment
		if($this->config->get('thawani_test_mode') == '1'){
			$url = self::TEST_URL."checkout/session/".$session_id;
		}else{
			$url = self::PRODUCTION_URL."checkout/session/".$session_id;
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
		    "thawani-api-key: ".$this->config->get('thawani_api_key')
		  ],
		]);
		$result = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		// in case of cURL error, set the error into array
		if($err){ $result = array("curl_error" => $err); }
		// return values base on the return type parameters
		return json_decode($result);
	}

}
