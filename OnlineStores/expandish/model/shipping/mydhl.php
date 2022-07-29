<?php

class ModelShippingMydhl extends Model {

  	/**
  	 * @const strings API URLs.
  	 */
    const BASE_API_TESTING_URL = 'https://express.api.dhl.com/mydhlapi/test/';
    const BASE_API_LIVE_URL    = 'https://express.api.dhl.com/mydhlapi/';

	public function getQuote($address){
    	$this->load->model('localisation/country');
	    $this->load->model('localisation/zone');

		$this->language->load_json('shipping/mydhl');

		$settings = $this->config->get('mydhl');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$settings['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

        $status = (!$settings['geo_zone_id'] || $query->num_rows) ? TRUE : FALSE;

		$method_data = [];
        
        $products = $this->getProductsAndRates($address, $settings);
        // print_r($products); die();
		if ($status && !empty($products)) {
			$quote_data = [];

            foreach($products as $product){
                $cost = $this->currency->convertUsingRatesAPI(
                    $product['totalPrice'][0]['price'],
                    $product['totalPrice'][0]['priceCurrency'],
                    // $this->currency->getCode()
                    $this->config->get('config_currency')
                );
                $quote_data[$product['productCode']] = [
                    'code'         => 'mydhl.'.$product['productCode'],
                    'title'        => $product['productName'],
                    'cost'         => $cost,
                    'text'         => $this->currency->format($cost)
                    // 'text'         => $product['deliveryCapabilities']['estimatedDeliveryDateAndTime'] . '  ' . $this->currency->format($cost)
                ];
            }
			$method_data = array(
				'code'       => 'mydhl.mydhl',
				'title'      => $this->_getTitle(),
				'quote'      => $quote_data,
				'sort_order' => 0,
				'error'      => false
			);
		}
		return $method_data;
	}

    public function getProductsAndRates($address , $settings)
    {
        $request_body = [
            'customerDetails' => [
                'shipperDetails' => [
                    'postalCode' => $this->config->get('config_postal_code') ?: '',
                    'cityName'   => $this->model_localisation_zone->getZone($this->config->get('config_zone_id'))['name'],
                    'countryCode'=> $this->model_localisation_country->getCountry($this->config->get('config_country_id'))['iso_code_2']
                ],
                'receiverDetails' => [
                    'postalCode' => $address['postcode'],
                    'cityName'   => $address['zone'],
                    'countryCode'=> $address['iso_code_2']
                ]
            ],
            'accounts' => [
                [
                    'typeCode' => 'shipper',
                    'number'   => $settings['account_number']
                ]
            ],
            'plannedShippingDateAndTime' => (new DateTime('tomorrow'))->format('Y-m-d\TH:i:s'. "\G\M\T" .'P'),
            'unitOfMeasurement'          => $settings['unit_of_measurement'] ?: 'metric',
            'isCustomsDeclarable'        => (bool)$settings['is_customs_declarable'],
            'packages' => $this->getPackages($settings)
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $this->_getBaseUrl().'rates',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>json_encode($request_body),
          CURLOPT_HTTPHEADER => array(
            'Authorization: Basic '.base64_encode($settings['username'] . ':' . $settings['password']),
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true)['products'];
    }
   
	/*  Helper Methods */
	private function _getTitle(){
		return $this->config->get('mydhl')['display_name'][$this->config->get('config_language_id')] ?: $this->language->get('title');
	}

    private function _getBaseUrl(){
      //Check if API is in Debugging Mode..
      $is_debugging_mode = $this->config->get('mydhl')['debugging_mode'];
      return ( isset($is_debugging_mode) && $is_debugging_mode == 1 ) ? self::BASE_API_TESTING_URL : self::BASE_API_LIVE_URL;
    }

    public function getPackages($settings)
    {
        if($settings['unit_of_measurement'] == 'metric'){
            $to_weight_class_id = $settings['kg_class_id'];
            $to_length_class_id = $settings['cm_class_id'];
        }else{//imperial
            $to_weight_class_id = $settings['lb_class_id'];
            $to_length_class_id = $settings['in_class_id'];
        }
        $packages = [];

        foreach ($this->cart->getProducts() as $product) {

            if ($product['shipping']) {
               $packages[] = [
                'weight' => (float)$this->weight->convert($product['weight'], $product['weight_class_id'], $to_weight_class_id) ?: 1,
                'dimensions' => [
                    'length' => (float)$this->length->convert($product['length'], $product['length_class_id'], $to_length_class_id) ?: $settings['min_length'],
                    'width'  => (float)$this->length->convert($product['width'],  $product['length_class_id'], $to_length_class_id) ?: $settings['min_width'],
                    'height' => (float)$this->length->convert($product['height'], $product['length_class_id'], $to_length_class_id) ?: $settings['min_height']
                ]
               ];
            }
        }
    
        return $packages;
    }
}
