<?php
class ModelShippingDiggiPacks extends Model {


	function getQuote($address){

		$this->language->load_json('shipping/diggi_packs');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('diggi_packs_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

        $status = (!$this->config->get('diggi_packs_geo_zone_id') || $query->num_rows) ? TRUE : FALSE;

		$method_data = [];

		if ($status) {
			$quote_data = [];

			$quote_data['diggi_packs'] = [
				'code'         => 'diggi_packs.diggi_packs',
				'title'        => $this->language->get('diggi_packs_title'),
				'cost'         => $this->config->get('diggi_packs_cost'),
				// 'tax_class_id' => '',
				'text'         => $this->currency->format($this->config->get('diggi_packs_cost'))
			];

			$method_data = array(
				'code'       => 'diggi_packs.diggi_packs',
				'title'      => $this->language->get('diggi_packs_title'),
				'quote'      => $quote_data,
				'sort_order' => 0,
				'error'      => false
			);
		}

		return $method_data;
	}



}
