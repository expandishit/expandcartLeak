<?php 
class ModelPaymentHesabe extends Model {


	/**
     * payment name
     * 
     */
    private $paymentName = 'hesabe';


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

	public function insertpaymentTransaction($response) {
		$payment_method = $response['response']['method'] == 1 ? 'KNET' : ($response['response']['method'] == 2 ? 'MPGS' : 'HESABE') ;
		$query = "INSERT INTO ". DB_PREFIX . "payment_transactions SET 
				  `order_id` ='".$response['response']['orderReferenceNumber']."' , 
				  `transaction_id` = '".$response['response']['paymentId']."' ,
				  `payment_gateway_id` = '".$response['payment_gateway_id']."' , 
				  `payment_method` = '".$payment_method."',
				  `status` = '".$response['status']."',
				  `amount` = '".$response['response']['amount']."',
				  `created_at` = '".$response['response']['paidOn']."'";
		 $this->db->query($query);
				  
	} 
}
?>