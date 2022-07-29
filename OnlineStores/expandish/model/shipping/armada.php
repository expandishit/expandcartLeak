<?php

class ModelShippingArmada extends Model {
    public function getQuote($address) {
        $armada_rate = (!empty($address['area_id'])) ? $this->db->query("SELECT price FROM ".DB_PREFIX." armada_shipping_cost where area_id=$address[area_id]")->row['price'] : $this->config->get('armada_shipping_rate');
        return [
            'code' => 'armada',
            'title' => 'Armada',
            'quote' => [
                'armada' => [
                    'code' => 'armada.armada',
                    'title' => 'Armada',
                    'cost' => $armada_rate,
                    'tax_class_id' => 0,
                    'text' => $this->currency->format($armada_rate, $this->currency->getCode())
                ]
            ],
            'sort_order' => 2,
            'error' => false
        ];
    }
    }
