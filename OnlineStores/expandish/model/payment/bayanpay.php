<?php

/**
 * Bayanpay Payments Model Class
 * @author Mohamed Hassan
 */
class ModelPaymentBayanpay extends Model
{
	/**
	 * @var lastError last error has been initialized
	 */
	private $lastError;



	public function getMethod($address, $total)
	{
		$method_data = array();

		$this->language->load_json('payment/bayanpay');

		$geo_zone_id = $this->config->get('bayanpay_geo_zone_id');

		if ($this->getGeoZone($geo_zone_id, $address)) {
			$method_data = array(
				'code'       => 'bayanpay',
				'title'      => $this->language->get('text_title'),
			);
		} else {
			$method_data = [];
		}

		return $method_data;
	}

	public function getLastError()
	{
		return $this->lastError;
	}

	private function errorParser($array)
	{
		$result = '';
		if (is_array($array)) {
			foreach ($array as $key => $value) {
				if ($key !== 0) {
					$result .= $key . ': ';
				}
				if (is_array($value)) {
					$result .= $this->errorParser($value) . "\n";
				} else {
					$result .= $value;
				}
			}
		} else {
			$result = $array;
		}
		return $result;
	}

	/**
	 * Get all geo zones by array of zones ids.
	 *
	 * @param array $zones
	 * @param array $address
	 *
	 * return array|bool
	 */
	public function getGeoZone($zones, $address)
	{
		if (!is_array($zones)) {
			$zones = [$zones];
		}

		$query = [];
		$query[] = 'SELECT * FROM zone_to_geo_zone';
		$query[] = 'WHERE geo_zone_id IN (' . implode(',', $zones) . ')';
		$query[] = 'AND country_id = "' . (int) $address['country_id'] . '"';
		$query[] = 'AND (zone_id = "' . (int) $address['zone_id'] . '") OR zone_id = "0"';

		$data = $this->db->query(implode(' ', $query));

		if ($data->num_rows) {
			return $data->rows;
		}

		return false;
	}
}
