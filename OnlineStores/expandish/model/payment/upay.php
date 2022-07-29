<?php 
class ModelPaymentUpay extends Model {
  	public function getMethod($address, $total) {
		$this->language->load_json('payment/upay');
		
		$method_data = array( 
        		'code'             => 'upay',
        		'title'            => 'upay',
				    'sort_order'       => $this->config->get('upay_sort_order'),
            'confirm_btn_type' => 'continue'
      		);
   
    	return $method_data;
  	}
}
?>