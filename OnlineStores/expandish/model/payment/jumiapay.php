<?php

class ModelPaymentJumiaPay extends Model {


	public function getMethod($address, $total) {

        $settings = $this->config->get('jumiapay');

        $this->language->load_json("payment/jumiapay");

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$settings['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
		
		$status = false;
        
        $method_data = [];

        if( !$settings['geo_zone_id'] || $query->num_rows ){
			$status = true;
		}

        if($status){
            $method_data = [
                'code'       => 'jumiapay',
                'title'      => $this->getTitle($settings),
                'sort_order' => 0
            ];
        }
        return $method_data;
    }

    /* helper functions */
    private function getTitle($settings){       
        return $settings['display_name'][$this->config->get('config_language_id')] ?: $this->language->get('text_title');
    }

}
