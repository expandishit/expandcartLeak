<?php

/**
 * Shipa Ecommerce StoreFront Model
 * 
 * @author MohamedHassanWD <hassan.mohamed.sf@gmail.com>
 * @copyright 2020 ExpandCart
 */
class ModelShippingShipa extends Model
{
    /**
     * Get Shipping method's default rate
     *
     * @param array $address
     * @return array Shipping method data including rate
    */ 
    public function getQuote($address)
    {
        $this->language->load_json('shipping/shipa');

        $geo_zone_id = $this->config->get('shipa_geo_zone_id');

        $queryString = [];
        $queryString[] = "SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone";
        $queryString[] = "WHERE geo_zone_id = '" . (int) $geo_zone_id . "'";
        $queryString[] = "AND country_id = '" . (int) $address['country_id'] . "'";
        $queryString[] = "AND (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')";

        $query = $this->db->query(implode(' ', $queryString));

        $status = false;

        if (!$this->config->get('shipa_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $cost = (float) $this->config->get('shipa_default_rate');

        $method_data = [];

        if ($status) {

            $quote_data['shipa'] = [
                'code' => 'shipa.shipa',
                'title' => $this->language->get('text_description'),
                'cost' => $cost,
                'tax_class_id' => 0,
                'text' => $this->currency->format($cost)
            ];

            $method_data = [
                'code' => 'shipa',
                'title' => $this->language->get('text_title'),
                'quote' => $quote_data,
                'sort_order' => $this->config->get('shipa_sort_order'),
                'error' => false
            ];
        }

        return $method_data;
    }
}
