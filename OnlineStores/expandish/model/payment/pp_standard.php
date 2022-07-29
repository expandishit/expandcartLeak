<?php 
class ModelPaymentPPStandard extends Model {
  	public function getMethod($address, $total) {
		$this->language->load_json('payment/pp_standard');
		
		$sql="SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE ";

		if($this->config->get('pp_standard_geo_zone_id')){
			$sql.=" geo_zone_id = '" . (int)$this->config->get('pp_standard_geo_zone_id') . "' AND ";
		}
		$sql.=" country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')";
		
		$query = $this->db->query($sql);

		if ($this->config->get('pp_standard_total') > 0 && $this->config->get('pp_standard_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('pp_standard_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}	

		$currencies = array(
			'AUD',
			'CAD',
			'EUR',
			'GBP',
			'JPY',
			'USD',
			'NZD',
			'CHF',
			'HKD',
			'SGD',
			'SEK',
			'DKK',
			'PLN',
			'NOK',
			'HUF',
			'CZK',
			'ILS',
			'MXN',
			'MYR',
			'BRL',
			'PHP',
			'TWD',
			'THB',
			'TRY'
		);
		
		$method_data = array();
	
		if ($status) {  
      		$method_data = array( 
        		'code'       => 'pp_standard',
        		'title'      => $this->language->get('text_title'),
				'sort_order' => $this->config->get('pp_standard_sort_order')
      		);
    	}
   
    	return $method_data;
  	}
}
?>