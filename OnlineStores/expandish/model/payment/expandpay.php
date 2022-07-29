<?php

class ModelPaymentExpandPay extends Model{
    public function getMethod($address, $total) {
		$this->language->load_json('payment/expandpay');
		$expandpaySettings = $this->model_setting_setting->getSetting('expandpay');
		$current_lang = $this->config->get('config_language_id');
      		$method_data = array( 
        		'code'       => 'expandpay',
				'title' => (!empty($expandpaySettings['expandpay_field_name_' . $current_lang])) ? $expandpaySettings['expandpay_field_name_' . $current_lang] : $this->language->get('text_title'),
        		'terms'      => '',
      		);
    	return $method_data;
  	}

}


?>