<?php
class ModelShippingAbsher extends Model {

	function getQuote($address){

		//Get Title
		$this->language->load_json('shipping/absher');
		$title = $this->config->get('absher_display_name')[$this->config->get('config_language_id')] ?: $this->language->get('title');


		//Calculate Price
		$settings['absher_price'] = $this->config->get('absher_price');
		$geo_zones = $this->db->query("SELECT * FROM " . DB_PREFIX . "geo_zone ORDER BY name");

		$status = FALSE;
		$method_data = [];
		$quote_data  = [];

        $cart_weight = $this->cart->getWeight();

		//Check client in which geo_zones (user-defined zones group), get first match
		foreach ($geo_zones->rows as $geo_zone) {

			if ($settings['absher_price']['absher_weight_' . $geo_zone['geo_zone_id'] . '_status']) {

				$client_geo_zones = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$geo_zone['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

				//client geo-zone found..
				if($client_geo_zones->num_rows){
					$status = TRUE;

					//calculate price
					$cost = '';
                	$rates = explode(',', $settings['absher_price']['absher_weight_' . $geo_zone['geo_zone_id'] . '_rate']);

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
						$total_shipping_cost = $settings['absher_price']['absher_weight_general_rate'];
					}
					else{ //cost&wright are found..
						if($cart_weight){
							$total_shipping_cost =  $cart_weight * $right_cost / $right_weight;
						}else{
							$total_shipping_cost = $right_cost;
						}
					}

					$quote_data['absher'] = array(
            'code'         => 'absher.absher_weight_' . $geo_zone['geo_zone_id'],
            'title'        => $title . ' (' . $this->language->get('text_weight') . ' ' . $this->weight->format($cart_weight, $this->config->get('config_weight_class_id')) . ')',
						'cost'         => $total_shipping_cost,
						'tax_class_id' => $this->config->get('absher_tax_class_id'),
						'text'         => $this->currency->format($total_shipping_cost)
					);
				}
			}
		}



		if ($status) {
			$method_data = array(
				'code'       => 'absher.absher',
				'title'      => $title,
				'quote'      => $quote_data,
				'sort_order' => 0,
				'error'      => false
			);
		}
		else{//no zone found
			$quote_data['absher'] = array(
				'code'         => 'absher.absher',
				'title'        => $title,
				'cost'         => $settings['absher_price']['absher_weight_general_rate'],
				'tax_class_id' => $this->config->get('absher_tax_class_id'),
				'text'         => $this->currency->format($settings['absher_price']['absher_weight_general_rate'])
			);

			$method_data = array(
				'code'       => 'absher.absher',
				'title'      => $title,
				'quote'      => $quote_data,
				'sort_order' => 0,
				'error'      => false
			);
		}


		return $method_data;
	}



}
