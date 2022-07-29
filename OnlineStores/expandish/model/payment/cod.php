<?php
class ModelPaymentCOD extends Model {
  	public function getMethod($address, $total) {
		$this->language->load_json('payment/cod');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('cod_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		$this->load->model('module/minimum_deposit/settings');
		if ($this->model_module_minimum_deposit_settings->isActive() && !$this->config->get('minimum_deposit')['md_allow_cash_on_delivery'] ) {
			$status = false;

		}else{
			if ($this->config->get('cod_total') > $total) {
				$status = false;
			} elseif (!$this->config->get('cod_geo_zone_id')) {
				$status = true;
			} elseif ($query->num_rows) {
				$status = true;
			} else {
				$status = false;
			}
		}
		//check if order has knawat products then disable offline payments
		$isKnwatProduct = 0;
		$this->load->model('module/knawat');
		if ($this->model_module_knawat->isInstalled()) {
			$products_id = array_column($this->cart->getProducts(), 'product_id');
			$isKnwatProduct = $this->model_module_knawat->countKnawatProducts($products_id);
		}

		// skip COD if there is knawat product in the cart
		if (!empty($isKnwatProduct))
			$status = false;


		$method_data = array();
		if ($status) {
      		$method_data = array(
        		'code'       => 'cod',
        		'title'      => $this->config->get('cod_title')[$this->config->get('config_language_id')] ?: $this->language->get('text_title'),
				'sort_order' => $this->config->get('cod_sort_order')
      		);
    	}

    	return $method_data;
  	}
}
?>
