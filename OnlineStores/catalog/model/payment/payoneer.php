<?php 
class ModelPaymentPayoneer extends Model {
  	public function getMethod($address, $total) {
		$this->language->load('payment/payoneer');
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payoneer_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
		
		if ($this->config->get('payoneer_total') > 0 && $this->config->get('payoneer_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('payoneer_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}	
		
		$method_data = array();
	
		if ($status) {  
      		$method_data = array( 
        		'code'       => 'payoneer',
        		'title'      => $this->language->get('text_title'),
				'sort_order' => $this->config->get('payoneer_sort_order')
      		);
    	}
   
    	return $method_data;
  	}
}
?>