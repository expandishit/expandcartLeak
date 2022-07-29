<?php

class ModelShippingShipaDelivery extends Model
{
    public function getQuote($address)
    {
        $this->language->load_json('shipping/shipa_delivery');

        $settings = $this->config->get('shipa_delivery');

        $queryString = [];
        $queryString[] = "SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone";
        $queryString[] = "WHERE geo_zone_id = '" . (int)$settings['geo_zone_id'] . "'";
        $queryString[] = "AND country_id = '" . (int)$address['country_id'] . "'";
        $queryString[] = "AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')";

        $query = $this->db->query(implode(' ', $queryString));

        $status = false;

        if (!$settings['geo_zone_id']) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $cost = $settings['shipping_cost'];

        $method_data = [];

        if ($status && $settings['status'] == 1) {

            $quote_data['shipa_delivery'] = [
                'code'         => 'shipa_delivery.shipa_delivery',
                'title'        => $this->language->get('text_description'),
                'cost'         => $cost,
                'tax_class_id' => 0,
                'text'         => $this->currency->format($cost)
            ];

            $method_data = [
                'code'       => 'shipa_delivery',
                'title'      => $this->language->get('text_title'),
                'quote'      => $quote_data,
                'sort_order' => 1,
                'error'      => false
            ];
        }

        return $method_data;
    }
}
