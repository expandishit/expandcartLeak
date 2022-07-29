<?php
class ModelShippingVanex extends Model {

	public function getQuote($address){
		$this->language->load_json('shipping/vanex');
        $title = $this->config->get('vanex_display_name')[$this->config->get('config_language_id')] ?: $this->language->get('vanex_title');
		$settings['vanex_price'] = $this->config->get('vanex_price');
		$geo_zones = $this->db->query("SELECT * FROM " . DB_PREFIX . "geo_zone ORDER BY name");

		$status = FALSE;
		$method_data = [];
		$quote_data  = [];
        $disable_zone=0;
		//Check client in which geo_zones (user-defined zones group), get first match
		foreach ($geo_zones->rows as $geo_zone) {
            if ($settings['vanex_price']['vanex_weight_' . $geo_zone['geo_zone_id'] . '_status']) {
                $client_geo_zones = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$geo_zone['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
                //client geo-zone found..
                if ($client_geo_zones->num_rows) {
                    $status = TRUE;

                    //calculate price
                    $cost = '';
                    $cart_weight = $this->cart->getWeight();
                    $rates = explode(',', $settings['vanex_price']['vanex_weight_' . $geo_zone['geo_zone_id'] . '_rate']);

                    //Weight&cost rate to be used in cart-shipping-cost-calculation
                    $right_weight = $right_cost = null;
                    //Get the right weight&cost..
                    foreach ($rates as $rate) {         // 0     :  1
                        $data = explode(':', $rate); //(weight:price)
                        $right_weight = $data[0];
                        if ($right_weight >= $cart_weight) { // cart weight in weight range
                            if (isset($data[1])) {
                                $right_cost = $data[1];
                                break;
                            }
                        } else if ($right_weight < $cart_weight) { // in case zone found but cart weight is greater than weight range
                            if (isset($data[1])) {
                                $right_cost = ($cart_weight / $right_weight) * $data[1];
                            }
                        }
                    }

                    // //if non of all rates is matched, use general rate
                    // if($right_weight == null || $right_cost == null){
                    // 	$total_shipping_cost = $settings['vanex_price']['vanex_weight_rate_class_id'];
                    // }
                    // else{ //cost&wright are found..
                    // 	if($cart_weight){
                    // 		$total_shipping_cost =  $cart_weight * $right_cost / $right_weight;
                    // 	}else{
                    // 		$total_shipping_cost = $right_cost;
                    // 	}

                    // }

                    $quote_data['vanex'] = array(
                        'code' => 'vanex.vanex_weight_' . $geo_zone['geo_zone_id'],
                        'title' => $title . ' (' . $this->language->get('text_weight') . ' ' . $this->weight->format($cart_weight, $this->config->get('config_weight_class_id')) . ')',
                        'cost' => $right_cost,
                        // 'tax_class_id' => 0,
                        'text' => $this->currency->format($right_cost)
                    );

                    // break;
                }
            }else{
                $disable_zone=1;
            }
		}
		if ($status) {

			$method_data = array(
				'code'       => 'vanex.vanex',
				'title'      => $title,
				'quote'      => $quote_data,
				'sort_order' => 0,
				'error'      => false
			);
		}
		else{
		    if($disable_zone==1)
            {
                //to exclude disable zone from work with vanex shipping menthod
                $method_data=array();
            }else {
                //no zone found
                $quote_data['vanex'] = array(
                    'code' => 'vanex.vanex',
                    'title' => $title,
                    'cost' => $settings['vanex_price']['vanex_weight_rate_class_id'],
                    // 'tax_class_id' => 0,
                    'text' => $this->currency->format($settings['vanex_price']['vanex_weight_rate_class_id'])
                );

                $method_data = array(
                    'code' => 'vanex.vanex',
                    'title' => $title,
                    'quote' => $quote_data,
                    'sort_order' => 0,
                    'error' => false
                );
            }
		}

		return $method_data;
	}


}
