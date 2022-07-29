<?php
class ModelPaymentCCOD extends Model {
  	public function getMethod($address, $total) {
        
        $minTotal = $this->config->get('ccod_total');
        
        if ($minTotal > 0 && $minTotal > $total) {
            return [];
        }
        $supportedZone = (int)$this->config->get('ccod_geo_zone_id');
        
        if ($supportedZone) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . $supportedZone 
                . "' AND country_id = '" . (int)$address['country_id'] 
                . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
            if (!$query->num_rows) {
                return [];
            }
        }
        
		$this->language->load_json('payment/ccod');


		// if ($this->config->get('ccod_total') > 0 && $this->config->get('ccod_total') > $total) {
		// 	$status = false;
		// } elseif (!$this->config->get('ccod_geo_zone_id')) {
		// 	$status = true;
		// } elseif ($query->num_rows) {
		// 	$status = true;
		// } else {
		// 	$status = false;
		// }

        //check if order has knawat products then disable offline payments
        $isKnwatProduct = 0;
        $this->load->model('module/knawat');
        if ($this->model_module_knawat->isInstalled()) {
            $products_id = array_column($this->cart->getProducts(), 'product_id');
            $isKnwatProduct = $this->model_module_knawat->countKnawatProducts($products_id);
        }

        // skip COD if there is knawat product in the cart
        if (!empty($isKnwatProduct))
            return [];

		// if ($status) {
      		$method_data = array(
        		'code'       => 'ccod',
        		'title'      => $this->config->get('ccod_title')[$this->config->get('config_language_id')] ?: $this->language->get('text_title'),
				'sort_order' => $this->config->get('ccod_sort_order')
      		);
    	// }

    	return $method_data;
  	}
}
?>
