<?php

class ModelShippingSmsa extends Model
{
    public function getQuote($address)
    {
        $this->language->load('shipping/smsa');

        $queryString = [];
        $queryString[] = "SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone";
        $queryString[] = "WHERE geo_zone_id = '" . (int)$this->config->get('smsa_geo_zone_id') . "'";
        $queryString[] = "AND country_id = '" . (int)$address['country_id'] . "'";
        $queryString[] = "AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')";

        $query = $this->db->query(implode(' ', $queryString));

        $weight = $this->cart->getWeight();

        $status = false;

        if ($weight > 0) {
            $status = true;
        }

        if (!$this->config->get('smsa_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = [];

        if ($status) {

            $weightAfter15 = 0;

            if ($weight > 15) {
                $weightAfter15 = $weight - 15;
            }

            $cost = $this->config->get('smsa_first15') + ($weightAfter15 * $this->config->get('smsa_after15'));

            $quote_data['smsa'] = [
                'code'         => 'smsa.smsa',
                'title'        => $this->language->get('text_description'),
                'cost'         => $cost,
                'tax_class_id' => 0,
                'text'         => $this->currency->format($cost)
            ];

            $method_data = [
                'code'       => 'smsa',
                'title'      => $this->language->get('text_title'),
                'quote'      => $quote_data,
                'sort_order' => $this->config->get('smsa_sort_order'),
                'error'      => false
            ];
        }

        return $method_data;
    }
}
