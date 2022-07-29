<?php
class ModelShippingErsal extends Model {


	function getQuote($address){

		$this->language->load_json('shipping/ersal');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('ersal_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

        $status = (!$this->config->get('ersal_geo_zone_id') || $query->num_rows) ? TRUE : FALSE;

		$method_data = [];

		if ($status) {
			$quote_data = [];

			$quote_data['ersal'] = [
				'code'         => 'ersal.ersal',
				'title'        => $this->language->get('ersal_title'),
				'cost'         => 0,
				// 'tax_class_id' => '',
				'text'         => $this->currency->format(0)
			];

			$method_data = array(
				'code'       => 'ersal.ersal',
				'title'      => $this->language->get('ersal_title'),
				'quote'      => $quote_data,
				'sort_order' => 0,
				'error'      => false
			);
		}

		return $method_data;
	}



}
