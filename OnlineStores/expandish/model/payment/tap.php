<?php
class ModelPaymentTap extends Model {
	
	public function getMethod($address, $total) {
		
		$this->language->load_json('payment/tap');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('tap_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if ($this->config->get('tap_total') > 0 && $this->config->get('tap_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('tap_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$tapPaymentTitle = $this->config->get('tap_payment_title');
		$paymentTitle = $this->language->get('text_title');

		if (isset($tapPaymentTitle[$this->config->get('config_language_id')])) {
			if (!empty($tapPaymentTitle[$this->config->get('config_language_id')])) {
				$paymentTitle = $tapPaymentTitle[$this->config->get('config_language_id')];
			}
		}

		$method_data = array();

		if ($status) {
			$method_data = array(
				'code'       => 'tap',
				'terms'      => '',
				'title'      => $paymentTitle,
				'str_title'	 => $paymentTitle,
				'sort_order' => $this->config->get('tap_sort_order')
			);
		}

		return $method_data;
	}
}
