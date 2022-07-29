<?php

class ModelShippingAsphalt extends Model {

	public function getQuote($address)
	{
		$this->language->load_json('shipping/asphalt');

		$settings = $this->config->get('asphalt');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$settings['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		$method_data = []; $quote_data  = [];

    $status = (!$settings['geo_zone_id'] || $query->num_rows) ? TRUE : FALSE;

		if ($status) {
			$cost = $this->getCost($this->cart->getWeight(), $address, $settings, $error);

			$quote_data = [];

			$quote_data['asphalt'] = [
				'code'         => 'asphalt.asphalt',
				'title'        => $this->_getTitle(),
				'cost'         => $cost,
			  'tax_class_id' => $settings['tax_class_id'],
				'text'         => $this->currency->format($cost)
			];

			$method_data = array(
				'code'       => 'asphalt.asphalt',
				'title'      => $this->_getTitle(),
				'quote'      => $quote_data,
				'sort_order' => 0,
				'error'      => $error
			);
		}
		return $method_data;
	}

	private function _getTitle()
	{
		return $this->config->get('asphalt')['display_name'][$this->config->get('config_language_id')] ?: $this->language->get('asphalt_title');
	}

	private function getCost($heavy = 1, $address, $settings, &$error = false)
	{
		$governments = $this->config->get('asphalt_governments');
		$url = 'https://asphalt-eg.com/asphalt_v2_api_shipment_calculate_price?' . http_build_query([
			'gov_from' => $governments[$this->config->get('config_zone_id')],
			'gov_to'   => $governments[$address['zone_id']],
			'heavy'    => $heavy ?: 1,
			'amount'   => $this->currency->convertUsingRatesAPI($this->cart->getTotal(),$this->config->get('config_currency'), 'EGP'),
			'api_key'  => $settings['api_key']
		]);

		$response = $this->curl($url);

		if($response['status_code'] == 200){
			return $response['ship_price'];
		}else{
			$error = $response['status_message'] . ' - '. $response['Description'];
			return -1;
		}	
	}


	private function curl($url, $method = 'GET', $data = [], $headers = [])
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => $method,
          CURLOPT_POSTFIELDS => $data,
          CURLOPT_HTTPHEADER => $headers       
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }
}
