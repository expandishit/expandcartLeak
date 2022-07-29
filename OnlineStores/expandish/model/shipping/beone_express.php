<?php

class ModelShippingBeoneExpress extends Model
{
	public function getQuote($address)
	{
		$this->language->load_json('shipping/beone_express');

		$settings = $this->config->get('beone_express');


		$quote_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "geo_zone ORDER BY name");

		$status = false;

		foreach ($query->rows as $result) {
			if ($settings['weight_' . $result['geo_zone_id'] . '_status']) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$result['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

				if ($query->num_rows) {
					$status = true;
				} else {
					$status = false;
				}
			} else {
				$status = false;
			}

			if ($status) {
				$cost = '';
				$weight = $this->cart->getWeight();
				$rates = explode(',', $settings['beone_express_weight_' . $result['geo_zone_id'] . '_rate']);

				foreach ($rates as $rate) {
					$data = explode(':', $rate);

					if ($data[0] >= $weight) {
						if (isset($data[1])) {
							$cost = $data[1];
						}

						break;
					}
				}

				if ((string)$cost != '') {
					$quote_data['beone_express_weight_' . $result['geo_zone_id']] = array(
						'code'         => 'beone_express.beone_express_weight_' . $result['geo_zone_id'],
						'title'        => $result['name'] . '  (' . $this->language->get('text_weight') . ' ' . $this->weight->format($weight, $this->config->get('config_weight_class_id')) . ')',
						'cost'         => $cost,
						'text'         => $this->currency->format($cost)
					);
				}
				break;
			}
		}

		if($status == false){
			$cost = $this->getGeneralRate();
			$quote_data['beone_express'] = array(
				'code'         => 'beone_express.beone_express',
				'title'        => $this->language->get('text_title'),
				'cost'         => $cost,
				'text'         => $this->currency->format($cost)
			);
		}

		$method_data = array();

		if ($quote_data) {
			$method_data = array(
				'code'       => 'beone_express',
				'title'      => $this->language->get('text_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('beone_express_sort_order'),
				'error'      => false
			);
		}

		return $method_data;

	}

	public function getGeneralRate()
	{
		$settings =  $this->config->get('beone_express');
		return $settings['beone_express_weight_rate_class_id'];
	}
}
