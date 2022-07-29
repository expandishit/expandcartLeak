<?php 
class ModelPaymentMoamalat extends Model {

    private $paymentName = 'moamalat';

	/**
	 * get method metadata code, title, and sort order
	 */
  	public function getMethod($address, $total) {
		$this->language->load_json("payment/{$this->paymentName}");
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get("{$this->paymentName}_geo_zone_id") . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
		
		if (!$this->config->get("{$this->paymentName}_geo_zone_id")) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}	
		
		$method_data = array();
		$language_id = $this->config->get('config_language_id');
	
		if ($status) {  
      		$method_data = array( 
        		'code'       => "{$this->paymentName}",
        		'title'      => $this->config->get("{$this->paymentName}_field_name_{$language_id}") ?? $this->language->get('text_title'),
				'sort_order' => $this->config->get("{$this->paymentName}_sort_order")
      		);
    	}

    	return $method_data;
  	}
}
?>