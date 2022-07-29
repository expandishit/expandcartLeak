<?php

class ModelPaymentGate2play extends Model {

    public function getMethod($address, $total) {
        $this->language->load('payment/gate2play');

        $method_data = array(
            'code'       => 'gate2play',
            'terms'      => '',
            'title'      => $this->language->get('text_title'),
            'sort_order' => $this->config->get('gate2play_sort_order')
        );

        return $method_data;        
    }

}

?>