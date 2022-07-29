<?php
class ModelShippingPostaPlus extends Model {

  	/**
  	* @const strings API URLs.
  	*/
    const BASE_API_TESTING_URL  = 'https://staging.postaplus.net/APIService/PostaWebClient.svc?wsdl';
    const BASE_API_LIVE_URL     = 'https://www.postaplus.net/APIServices/ShippingClient.svc?Wsdl';


	function getQuote($address){

		$this->language->load_json('shipping/postaplus');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('postaplus_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

        $status = (!$this->config->get('postaplus_geo_zone_id') || $query->num_rows) ? TRUE : FALSE;
		$title = $this->config->get('wagon_display_name')[$current_lang_id] ?: $this->language->get('title');
		$cost  = $this->_calculateCost($this->cart->getWeight(), $this->_getCountryCode($this->config->get('config_country_id')) , $this->_getCountryCode($address['country_id']));

		$method_data = [];

		if ($status) {
			$quote_data = [];

			$quote_data['postaplus'] = [
				'code'         => 'postaplus.postaplus',
                'title'        => $title,
				'cost'         => $cost,
				'tax_class_id' => $this->config->get('postaplus_tax_class_id'),
                'text'         => $this->currency->format($this->tax->calculate($cost, $this->config->get('postaplus_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])

			];

			$method_data = [
				'code'       => 'postaplus.postaplus',
				'title'      => $title,
				'quote'      => $quote_data,
				'sort_order' => 0,
				'error'      => false
			];
		}

		return $method_data;
	}




  	/**
  	* [POST]Create new shipment Order.
    * @param Array   $order data to be shipped.
  	* @return Response Object contains newly created order details
  	*/
    private function _calculateCost($weight, $from_country_code, $to_country_code)
    {
      try{
          $client = new SoapClient($this->_getBaseUrl());  // The trace param will show you errors stack

          $response = $client->ShipmentCostCalculationInfo([
          	'CI' => [
          		'CodeStation'    => $this->config->get('postaplus_station_code'),
                'Password'       => $this->config->get('postaplus_password'),
                'ShipperAccount' => $this->config->get('postaplus_shipper_account_id'),
                'UserName'       => $this->config->get('postaplus_username'),
          	],

          	'SI' => [
          	'Length' => 0,
            'Height' => 0,
            'Width'  => 0,
            'DestinationCountryCode' => $to_country_code,
            'OriginCountryCode'      => $from_country_code,
            'ScaleWeight' => $weight,
            ],
          ]);
          // var_dump($response);die();

          return $response->ShipmentCostCalculationInfoResult->Amount;
      }
      catch (Exception $e){
        return 0;
      }

    }

    /*  Helper Methods */
    private function _getBaseUrl(){
    	//Check if API is in Debugging Mode..
    	$is_debugging_mode = $this->config->get('postaplus_debugging_mode');

    	return ( isset($is_debugging_mode) && $is_debugging_mode == 1 ) ? self::BASE_API_TESTING_URL : self::BASE_API_LIVE_URL;
    }


    private function _getCountryCode($country_id){
    	$this->load->model('localisation/country');
    	return $this->model_localisation_country->getCountry($country_id)['iso_code_2'];
    }
}
