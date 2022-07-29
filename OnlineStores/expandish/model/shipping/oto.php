<?php

class ModelShippingOto extends Model {
    public function getQuote($address) {
        $oto_rate = $this->config->get('oto_shipping_rate');
        return [
            'code' => 'oto',
            'title' => 'OTO',
            'quote' => [
                'oto' => [
                    'code' => 'oto.oto',
                    'title' => 'OTO',
                    'cost' => $oto_rate,
                    'tax_class_id' => 0,
                    'text' => $this->currency->format($oto_rate, $this->currency->getCode())
                ]
            ],
            'sort_order' => 2,
            'error' => false
        ];
    }
}
