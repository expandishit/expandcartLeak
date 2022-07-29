<?php

/**
 * Model for handling Edfali payment data
 * 
 * @author MohamedHassanWD <hassan.mohamed.sf@gmail.com>
 * @copyright 2020 ExpandCart
 */
class ModelPaymentEdfali extends Model
{

	/**
	 * Get the method data for the payment method
	 * 
	 * @param array $address 
	 * @return mixed
	 */
	public function getMethod($address)
	{
		$this->language->load_json('payment/edfali');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int) $this->config->get('edfali_geo_zone_id') . "' AND country_id = '" . (int) $address['country_id'] . "' AND (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('edfali_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();
		if ($status) {
			$method_data = array(
				'code'       => 'edfali',
				'title'      => $this->language->get('text_title'),
				'sort_order' => $this->config->get('edfali_sort_order')
			);
		}

		return $method_data;
	}

	/**
	 * Get the settings for edfali payment
	 *
	 * @return array settings
	 */
	public function getSettings()
	{
		$settings = [];
		$fields = [
			'completed_order_status_id', 
			'failed_order_status_id',
			'merchant_mobile',
			'merchant_pin',
		];
		foreach ($fields as $field) {
			$settings['edfali_' . $field] = $this->config->get('edfali_' . $field);
		}
		return $settings;
	}
}
