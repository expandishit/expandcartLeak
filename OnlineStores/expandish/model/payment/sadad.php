<?php 
class ModelPaymentSadad extends Model {


	/**
     * payment name
     * 
     */
    private $paymentName = 'sadad';


	/**
	 * get method metadata code, title, and sort order
	 */
  	public function getMethod($address, $total) {
		$this->language->load_json("payment/{$this->paymentName}");
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get("{$this->paymentName}_geo_zone_id") . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
		
		if (!$this->config->get("{$this->paymentName}_geo_zone_id")) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}	
		
		$method_data = array();
		$language_id = $this->config->get('config_language_id');
	
		if ($status) {  
      		$method_data = array( 
        		'code'       => "{$this->paymentName}",
        		'title'      => $this->config->get("{$this->paymentName}_field_name_{$language_id}") ?? $this->language->get('text_title'),
				'sort_order' => $this->config->get("{$this->paymentName}_sort_order")
      		);
    	}

    	return $method_data;
  	}

    public function insertpaymentTransaction($data) {
        $query = "INSERT INTO ". DB_PREFIX . "payment_transactions SET 
				  `order_id` ='".$data['order_id']."' , 
				  `payment_gateway_id` = '".$data['payment_gateway_id']."' , 
				  `payment_method` = '".$data['payment_method']."',
				  `transaction_id` = '".$data['transaction_id']."',
				  `status` = '".$data['status']."',
				  `amount` = '".$data['amount']."',
				  `currency` = '".$data['currency']."'";
        $this->db->query($query);
    }

}
?>