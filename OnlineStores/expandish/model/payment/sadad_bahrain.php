<?php 

class ModelPaymentSadadBahrain extends Model {

  	public function getMethod($address) 
  	{
		$this->language->load_json("payment/sadad_bahrain");
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get("sadad_bahrain_geo_zone_id") . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

 		$status = !$this->config->get("sadad_bahrain_geo_zone_id") || $query->num_rows ? TRUE : FALSE;
      
        $method_data = [];

        if ($status)
        {
            $method_data = [
                'code'       => 'sadad_bahrain',
                'title'      => $this->config->get('sadad_bahrain')['display_name'][$this->config->get('config_language_id')] ?: $this->language->get('text_title'),
                'sort_order' => 0,
            ];
        }

        return $method_data;
	}
}


