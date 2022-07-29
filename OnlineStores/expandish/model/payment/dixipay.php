<?php

class ModelPaymentDixipay extends Model {

    public function getMethod() {
        $this->language->load_json('payment/dixipay');

        $method_data = array(
            'code' => 'dixipay',
            'title' => $this->language->get('text_title'),
            'sort_order' => $this->config->get('dixipay_sort_order')
        );

        return $method_data;
    }

}

?>