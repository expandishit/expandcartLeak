<?php 
class ModelPaymentMyfatoorah extends Model {
  	public function getMethod($address, $total) {
		$this->language->load_json('payment/my_fatoorah');
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('my_fatoorah_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
	
		// if ($this->config->get('my_fatoorah_total') > 0 && $this->config->get('my_fatoorah_total') > $total) {
		// 	$status = false;
		// } else
		if (!$this->config->get('my_fatoorah_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}
		
			
		$method_data = array();
		$current_lang = $this->session->data['language'];
		$this->load->model('localisation/language');
		$language = $this->model_localisation_language->getLanguageByCode($current_lang);
		$current_lang = $language['language_id'];
		if ( !empty($this->config->get('myfatoorah_field_name_'.$current_lang)) )
		{
			$title = $this->config->get('myfatoorah_field_name_'.$current_lang);
		}
		else
		{
			$title = $this->language->get('text_title');
		}
	
		if ($status) {  
      		$method_data = array( 
        		'code'       => 'my_fatoorah',
        		'title'      => $title,
        		'terms'      => '',
				'sort_order' => $this->config->get('my_fatoorah_sort_order')
      		);
    	}
   
    	return $method_data;
  	}
}
?>