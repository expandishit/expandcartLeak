<?php 
class ModelPaymentFawry extends Model {
  	public function getMethod($address, $total) {
		$this->language->load_json('payment/fawry');
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('fawry_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
	
		if (!$this->config->get('fawry_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}
		
		$method_data = array();
		if ($status) {  
      		$method_data = array( 
        		'code'       => 'fawry',
        		'title'      => $this->_getTitle(),
				'sort_order' => $this->config->get('fawry_sort_order')
      		);
    	}
   
    	return $method_data;
	}

	public function getSettings()
	{
		# code...
		$settings = [];
		$fields = [ 'completed_order_status_id', 'failed_order_status_id','expiry',  'merchant','test_mode', 'security_key' ];
		foreach ($fields as $field)
        {
            $settings['fawry_' . $field] = $this->config->get('fawry_' . $field);
        }
		return $settings;
	}
	  

	private function _getTitle(){
		$this->language->load_json('payment/fawry');		
		$title_presentation_type = $this->config->get('fawry_presentation_type');

		if($title_presentation_type === 'image')
			return '<img src="' . HTTP_SERVER . 'expandish/view/theme/default/image/fawry.png" style="float:none"/>';

		return $this->language->get('text_title');
	}
}
?>
