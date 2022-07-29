<?php 
class ModelPaymentHalalah extends Model {
  	public function getMethod($address, $total) {
		$this->language->load_json('payment/halalah');
		
		$method_data = array( 
        		'code'       => 'halalah',
        		'title'      => $this->language->get('text_title'),
				    'sort_order' => $this->config->get('halalah_sort_order')
      		);
   
    	return $method_data;
  	}
}
?>