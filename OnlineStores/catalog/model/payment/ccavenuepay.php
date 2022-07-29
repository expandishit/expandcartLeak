<?php

class ModelPaymentCcavenuepay extends Model {

  	public function getMethod($address, $total) {

		$this->load->language('payment/ccavenuepay');		

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('ccavenuepay_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");		

		if ($this->config->get('ccavenuepay_total') > $total) {

			$status = false;

		} elseif (!$this->config->get('ccavenuepay_geo_zone_id')) {

			$status = true;

		} elseif ($query->num_rows) {

			$status = true;

		} else {

			$status = false;

		}	
		
		
		$status = TRUE;
		
		$method_data = array();	

		if ($status) {  

      		$method_data = array( 

        		'code'       => 'ccavenuepay',

        		//'title'      => $this->language->get('text_title').'<img src="catalog/view/theme/default/image/payment/ccavenue.png" style="display:block; float:right; margin-right:67%;"/>',						
        		'title'      => $this->language->get('text_title'),	
				
				'sort_order' => $this->config->get('ccavenuepay_sort_order')

      		);

    	}  

    	return $method_data;

  	}
	
}
?>