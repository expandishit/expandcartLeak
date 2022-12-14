<?php
class ModelPaymentOneglobal extends Model {
  	public function getMethod($address, $total) {
		$this->language->load_json('payment/oneglobal');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('cod_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$settings = $this->config->get('oneglobal');

		$method_data = array();
		if ($status) {
      		$method_data = array(
        		'code'       => 'oneglobal',
        		'title'      => $settings['title'][$this->config->get('config_language_id')] ?: $this->language->get('text_title'),
				'sort_order' => $settings['sort_order']
      		);
    	}

    	return $method_data;
  	}
}
?>
