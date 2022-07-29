<?php 

class ModelPaymentVapulus extends Model {

  	public function getMethod($address) {
		$this->language->load_json("payment/vapulus");
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get("vapulus_geo_zone_id") . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

 		$status = !$this->config->get("vapulus_geo_zone_id") || $query->num_rows ? TRUE : FALSE;
      
        $method_data = [];

        if ($status)
        {
            $method_data = [
                'code'       => 'vapulus',
                'title'      => $this->config->get('vapulus_gateway_display_name')[$this->config->get('config_language_id')] ?: $this->language->get('text_title'),
                'sort_order' => $this->config->get('vapulus_sort_order'),
            ];
        }

        return $method_data;
	}
}
