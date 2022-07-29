<?php
class ModelPaymentZainCash extends Model {
  public function getMethod($address, $total) {
    $this->load->language('payment/zaincash');
  
    $method_data = array(
      'code'     => 'zaincash',
      'title'    => $this->language->get('text_title'),
      'sort_order' => $this->config->get('zaincash_sort_order')
    );
  
    return $method_data;
  }
}