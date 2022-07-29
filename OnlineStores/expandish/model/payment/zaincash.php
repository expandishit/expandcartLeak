<?php
class ModelPaymentZainCash extends Model {
  public function getMethod($address, $total) {
    $this->language->load_json('payment/zaincash');
  	
  	if($address['iso_code_3'] ==  'IRQ' || $address['iso_code_2'] ==  'IQ'){
  		$method_data = array(
	      'code'     => 'zaincash',
	      'title'    => $this->language->get('text_title'),
	      'sort_order' => $this->config->get('zaincash_sort_order')
	    );
  	}else{
  		$method_data = array();
  	}

    return $method_data;
  }
}