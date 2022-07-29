<?php

/**
 * Model for handling Kashier payment data
 * 
 * @author MohamedHassanWD <hassan.mohamed.sf@gmail.com>
 * @copyright 2019 ExpandCart
 */
class ModelPaymentKashier extends Model
{

	/**
	 * Get the method data for the payment method
	 * 
	 * @param array $address 
	 * @return mixed
	 */
	public function getMethod($address)
	{
		$this->language->load_json('payment/kashier');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int) $this->config->get('kashier_geo_zone_id') . "' AND country_id = '" . (int) $address['country_id'] . "' AND (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('kashier_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();
		if ($status) {
			$method_data = array(
				'code'       => 'kashier',
				'title'      => $this->language->get('text_title'),
				'sort_order' => $this->config->get('kashier_sort_order')
			);
		}

		return $method_data;
	}

	/**
	 * Get the settings for Kashier payment
	 *
	 * @return array settings
	 */
	public function getSettings()
	{
		$settings = [];
		$fields = [
			'completed_order_status_id', 
			'failed_order_status_id',  
			'iframe_api_key', 
			'test_mode',
			'currency'
		];
		foreach ($fields as $field) {
			$settings['kashier_' . $field] = $this->config->get('kashier_' . $field);
		}
		return $settings;
	}
}
