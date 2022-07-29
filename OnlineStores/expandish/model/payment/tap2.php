<?php 
class ModelPaymentTap2 extends Model {
	public function getMethod($address, $total) {
		// check first for table existance
		$this->create_tap2_customer_if_not_exists();

		if (!$this->config->get('tap2_api_publishable_key') && !$this->config->get('tap2_api_secret_key')) {
			return;
		}

		$this->language->load_json('payment/tap2');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('tap2_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		$tap2Total = $this->config->get('tap2_total');

		if ($tap2Total > 0 && $tap2Total > $total) {
			$status = false;
		} elseif (!$this->config->get('tap2_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$currencies = $this->getSupportedCurrencies();

		if (!in_array(strtoupper($this->currency->getCode()), $currencies)) {
			$status = false;
		}

		$method_data = [];

		if ($status) {  
			$method_data = [
				'code'       => 'tap2',
				'title'      => $this->language->get('text_title'),
				'sort_order' => $this->config->get('tap2_sort_order')
			];
		}

		return $method_data;
	}

	public function getStatuses()
	{
		return [
			'success' => [
				'INITIATED',
				'IN_PROGRESS', 
				'CAPTURED'
			],
			'fail' => [
				'VOID', 
				'TIMEDOUT', 
				'UNKNOWN',
				'ABANDONED', 
				'CANCELLED',
				'FAILED', 
				'DECLINED', 
				'RESTRICTED'
			]
		];
	}

	private function getSupportedCurrencies()
	{
		return  [
			'KWD',
			'AED',
			'BHD',
			'EGP',
			'EUR',
			'GBP',
			'OMR',
			'QAR',
			'SAR',
			'USD'
		];
	}
	public function create_tap2_customer_if_not_exists(){
		$exist_table = $this->db->query(" SHOW TABLES LIKE '".DB_PREFIX."tap2_customer'");
		if(!$exist_table->num_rows){
			$this->db->query("
			  CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "tap2_customer` (
				`expandcart_customer_id` INT(11) NOT NULL,
				`tap2_customer_id` varchar(50) NOT NULL,
				PRIMARY KEY (`expandcart_customer_id`),
				FOREIGN KEY (expandcart_customer_id) REFERENCES customer(customer_id)
			  );");
		}
	}
	public function getTap2CustomerId($customer_id = 0){
		$this->create_tap2_customer_if_not_exists();
		return $this->db->query("
			SELECT tap2_customer_id 
			FROM `" . DB_PREFIX . "tap2_customer`
			WHERE expandcart_customer_id = " . (int) $customer_id)->row['tap2_customer_id'] ?: null;
	}

    public function saveCustomerId($customer_id){
		$this->create_tap2_customer_if_not_exists();
        if($this->customer->getId() > 0 && !empty($customer_id) ){
               $this->db->query("INSERT INTO `" . DB_PREFIX . "tap2_customer` VALUES (" . (int)$this->customer->getId() .", '" . $this->db->escape($customer_id) . "') ON DUPLICATE KEY UPDATE expandcart_customer_id=".(int)$this->customer->getId());
        }
    }	
}
?>
