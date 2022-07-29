<?php
class ModelShippingBarq extends Model {
  const BASE_API_URL    = 'https://live.barqfleet.com/api/v1/merchants';
  const Staging_API_URL    = 'https://staging.barqfleet.com/api/v1/merchants';

	function getQuote($address){
		$this->language->load_json('shipping/barq');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('barq_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
        $status = (!$this->config->get('barq_geo_zone_id') || $query->num_rows) ? TRUE : FALSE;

		$method_data = [];

		if ($status) {
	        $user_cordinates = $this->getGeocode($address['address_1']);
	        // get address geo zone to check its related hub
	        $user_geo_zone_id = $query->row['geo_zone_id'];
	        // get related hub to user_geo_zone
	        $user_hub_data = $this->config->get('barq_hubs');
	        $hub_cords['lat'] = $user_hub_data['barq_hubs_'.$user_geo_zone_id.'_lat'];
	        $hub_cords['lng'] = $user_hub_data['barq_hubs_'.$user_geo_zone_id.'_lng'];
	        $shipping_price = $this->getPrice($user_cordinates['lat'] , $user_cordinates['lng'], $hub_cords['lat'], $hub_cords['lng']);
			$quote_data = [];

			$quote_data['barq'] = [
				'code'         => 'barq.barq',
				'title'        => $this->language->get('barq_title'),
				'cost'         => $shipping_price['price'],
				// 'tax_class_id' => '',
				'text'         => $this->currency->format($shipping_price['price'])
			];

			$method_data = array(
				'code'       => 'barq.barq',
				'title'      => $this->language->get('barq_title'),
				'quote'      => $quote_data,
				'sort_order' => 0,
				'error'      => false
			);
		}

		return $method_data;
	}

    public function getPrice($lat1, $lng1,$lat2,$lng2 ){

	    /*
	    response example
	      (
	        [distance] => 71.99
	        [price] => 147.98
	        [multiplier] => 1.75
	        [duration] => 96
	        [street_name1] => 
	        [street_name2] => الحسن الهمام
	        [distance_unit] => km
	        [currency] => SAR
	        [duration_unit] => minutes
	      )
	    */ 
	    $test_mode = $this->config->get('barq_test_mode');
	    $lang = $this->config->get('config_admin_language');
	    if($test_mode == 0)
	      $url = self::BASE_API_URL."/distance?lat1=$lat1&lng1=$lng1&lat2=$lat2&lng2=$lng2";
	    else
	      $url = self::Staging_API_URL."/distance?lat1=$lat1&lng1=$lng1&lat2=$lat2&lng2=$lng2";

	    $curl = curl_init();
	    curl_setopt_array($curl, array(
	      CURLOPT_URL => $url,
	      CURLOPT_RETURNTRANSFER => true,
	      CURLOPT_ENCODING => "",
	      CURLOPT_MAXREDIRS => 10,
	      CURLOPT_TIMEOUT => 0,
	      CURLOPT_FOLLOWLOCATION => true,
	      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	      CURLOPT_CUSTOMREQUEST => "GET",
	    ));

	    $response = json_decode(curl_exec($curl), true);
	    curl_close($curl);
	    return $response;
    }
    //coordinates
    public function getGeocode($address){
    $coordinatesData = [
        'lat' => 0,
        'lng' => 0
    ];

    $url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($address).'&key='.$this->config->get('barq_google_api_key');

    $ch = curl_init($url); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', curl_exec($ch)), true);
    if (!curl_errno($ch)) {
      if (!empty($result['results'])) {
        $coordinatesData['lat'] = $result['results'][0]['geometry']['location']['lat'];
        $coordinatesData['lng'] = $result['results'][0]['geometry']['location']['lng'];
      }
    }
    curl_close($ch);
    return $coordinatesData;
  }
}
