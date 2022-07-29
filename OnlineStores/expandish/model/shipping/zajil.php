<?php
class ModelShippingZajil extends Model {
	function getQuote($address) {
		

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('zajil_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('zajil_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();

		if ($status) {
			$quote_data = array();

			$quote_data['zajil'] = array(
				'code'         => 'zajil.zajil',
				'title'        => $this->config->get('zajil_title'),
				'cost'         => $this->tax->calculate($this->config->get('zajil_cost'), $this->config->get('zajil_tax_class_id'), $this->config->get('config_tax')),
				'text'         => $this->currency->format($this->tax->calculate($this->config->get('zajil_cost'), $this->config->get('zajil_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
			);

			$method_data = array(
				'code'       => 'zajil',
				'title'      => $this->config->get('zajil_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('zajil_sort_order'),
				'error'      => false
			);
		}

		return $method_data;
	}
	protected function get_currency($from_Currency, $to_Currency, $amount) {

		$to_Currency = 'SAR';
		$amount = urlencode($amount);
		$from_Currency = urlencode($from_Currency);
		$to_Currency = urlencode($to_Currency);

		$url = "http://www.google.com/finance/converter?a=$amount&from=$from_Currency&to=$to_Currency";

		$ch = curl_init();
		$timeout = 0;
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$rawdata = curl_exec($ch);
		curl_close($ch);
		$data = explode('bld>', $rawdata);
		$data = explode($to_Currency, $data[1]);
		return round($data[0], 2);
	}
}