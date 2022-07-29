<?php
class ModelShippingExpandship extends Model {

    public function getQuote($address){

        $this->language->load_json('shipping/expandship');

        $settings =  $this->config->get('expandship')??[];

	    if(empty($settings))
	        return [];

	    $current_language = in_array($this->config->get('config_language'),['ar','en']) ? $this->config->get('config_language') : 'en';
//	    $current_language = in_array($this->config->get('config_language'),['ar','en']) ? $this->config->get('config_language') : 'en';
	    $title =  $this->language->get('text_title');

        $geo_zones = $this->db->query("SELECT * FROM " . DB_PREFIX . "geo_zone ORDER BY name");

        $status = FALSE;
        $method_data = [];
        $quote_data  = [];
        $disable_zone=0;
        //Check client in which geo_zones (user-defined zones group), get first match
        foreach ($geo_zones->rows as $geo_zone) {
            if ($settings['price']['expandship_weight_' . $geo_zone['geo_zone_id'] . '_status']) {

                $client_geo_zones = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$geo_zone['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");


                //client geo-zone found..
                if ($client_geo_zones->num_rows) {
                    $status = TRUE;

                    //calculate price
                    $cost = '';
                    $cart_weight = $this->cart->getWeight();
                    $rates = explode(',', $settings['price']['expandship_weight_' . $geo_zone['geo_zone_id'] . '_rate']);

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

                     //if none of all rates is matched, use general rate
                     if($right_weight == null || $right_cost == null){
                         $right_cost = $settings['price']['expandship_weight_rate_class_id'];
                     }
                     else{ //cost&wright are found
                     	if($cart_weight){
                            $right_cost =  $cart_weight * $right_cost / $right_weight;
                     	}

                     }

                    $quote_data['expandship_weight_' . $geo_zone['geo_zone_id']] = array(
                        'code' => 'expandship.expandship_weight_' . $geo_zone['geo_zone_id'],
                        'title' => $title . ' (' . $this->language->get('text_weight') . ' ' . $this->weight->format($cart_weight, $this->config->get('config_weight_class_id')) . ')',
                        'cost' => $right_cost,
//                         'tax_class_id' => 0,
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
                'code'       => 'expandship.expandship',
                'title'      => $title,
                'quote'      => $quote_data,
                'sort_order' => 0,
                'error'      => false
            );
        }
        else{
            if($disable_zone !=1)
            {
                //no zone found
                $quote_data['expandship'] = array(
                    'code' => 'expandship.expandship',
                    'title' => $title,
                    'cost' => $settings['price']['expandship_weight_rate_class_id'],
//                     'tax_class_id' => 0,
                    'text' => $this->currency->format($settings['price']['expandship_weight_rate_class_id'])
                );

                $method_data = array(
                    'code' => 'expandship.expandship',
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
