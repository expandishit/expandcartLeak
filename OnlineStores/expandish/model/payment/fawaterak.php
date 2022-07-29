<?php 
class ModelPaymentFawaterak extends Model {
  	public function getMethod($address, $total) {
		$this->language->load_json('payment/fawaterak');
				
		$method_data = array();
		
		$status = true;

		if ($status) {  
      		$method_data = array( 
        		'code'       => 'fawaterak',
        		'title'      => $this->language->get('text_title'),
        		'terms'      => '',
				'sort_order' => $this->config->get('cod_sort_order')
      		);
    	}
   
    	return $method_data;
  	}
	
	public function insertpaymentTransaction($response) {

		$query = "INSERT INTO ". DB_PREFIX . "payment_transactions SET 
				  `order_id` ='".$response->order_id."' , 
				  `payment_gateway_id` = '".$response->payment_gateway_id."' , 
				  `payment_method` = '".$response->data->payment_method."',
				  `transaction_id` = '".$response->data->id."',
				  `status` = '".$response->status."',
				  `amount` = '".$response->data->total."',
				  `currency` = '".$response->data->currency."',
				  `created_at` = '".$response->data->created_at."'";
		 $this->db->query($query);
				  
	}   

	public function getOrderIdByInvoiceId($invoice_id){
		$query = "SELECT `order_id` FROM ". DB_PREFIX . "`payment_transactions` WHERE `transaction_id` = ".$invoice_id."";
		$res = $this->db->query($query);
		return $res->row['order_id'];

	}
}
?>