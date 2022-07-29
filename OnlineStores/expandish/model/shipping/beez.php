
<?php
class ModelShippingBeez extends Model {

	function getQuote($address){

		$this->language->load_json('shipping/beez');

		//Calculate Price
		$settings = $this->config->get('beez');

		$geo_zones = $this->db->query("SELECT * FROM " . DB_PREFIX . "geo_zone ORDER BY name");

		$method_data = []; $quote_data  = [];

        $cart_weight = $this->cart->getWeight();

		//Check client in which geo_zones (user-defined zones group), get first match
		foreach ($geo_zones->rows as $geo_zone) {

			if ($settings['price']['beez_geo_zone_id_' . $geo_zone['geo_zone_id'] . '_status']) {

				$client_geo_zones = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$geo_zone['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

				//client geo-zone found..
				if($client_geo_zones->num_rows){
					//calculate price
                	$rates = explode(',', $settings['price']['beez_geo_zone_id_' . $geo_zone['geo_zone_id'] . '_rate']);

                	//Weight&cost rate to be used in cart-shipping-cost-calculation
                	$right_weight = $right_cost = null;

                	//Get the right weight&cost..
		            foreach ($rates as $rate) {		 // 0     :  1
		                $data = explode(':', $rate); //(weight:price)
						if ($data[0] >= $cart_weight) {
							$right_weight = $data[0];
						    if (isset($data[1])) {
						        $right_cost = $data[1];
						    }
						    break;
						}
					}

					//if non of all rates is matched, use general rate
					if($right_weight == null || $right_cost == null){
						$total_shipping_cost = $settings['price']['beez_general_rate'];
					}
					else{ //cost&wright are found..
						$total_shipping_cost = $right_cost;
					}

					$quote_data['beez_weight_' . $geo_zone['geo_zone_id']] = array(
                        'code'         => 'beez.beez_weight_' . $geo_zone['geo_zone_id'],
                        'title'        => $this->_getTitle() . ' (' . $this->language->get('text_weight') . ' ' . $this->weight->format($cart_weight, $this->config->get('config_weight_class_id')) . ')',
						'cost'         => $total_shipping_cost,
						'tax_class_id' => $this->config->get('beez_tax_class_id'),
						'text'         => $this->currency->format($total_shipping_cost)
					);
				}
			}
		}

		if ($quote_data) {
			$method_data = array(
				'code'       => 'beez.beez',
				'title'      => $this->_getTitle(),
				'quote'      => $quote_data,
				'sort_order' => 0,
				'error'      => false
			);
		}		

		return $method_data;
	}

	private function _getTitle(){
		return $this->config->get('beez')['display_name'][$this->config->get('config_language_id')] ?: $this->language->get('beez_title');
	}

}
