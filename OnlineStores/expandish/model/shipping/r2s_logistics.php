<?php

class ModelShippingR2sLogistics extends Model
{
    public function getQuote($address)
    {
        $this->language->load_json('shipping/r2s_logistics');

        $settings = $this->config->get('r2s_logistics');


        $quote_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "geo_zone ORDER BY name");

        foreach ($query->rows as $result) {
            if ($settings['weight_' . $result['geo_zone_id'] . '_status']) {
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$result['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

                if ($query->num_rows) {
                    $status = true;
                } else {
                    $status = false;
                }
            } else {
                $status = false;
            }

            if ($status) {
                $cost = '';
                $weight = $this->cart->getWeight();
                $rates = explode(',', $settings['r2s_weight_' . $result['geo_zone_id'] . '_rate']);

                foreach ($rates as $rate) {
                    $data = explode(':', $rate);

                    if ($data[0] >= $weight) {
                        if (isset($data[1])) {
                            $cost = $data[1];
                        }

                        break;
                    }
                }

                if ((string)$cost != '') {
                    $quote_data['r2s_weight_' . $result['geo_zone_id']] = array(
                        'code'         => 'r2s_logistics.r2s_weight_' . $result['geo_zone_id'],
                        'title'        => $result['name'] . '  (' . $this->language->get('text_weight') . ' ' . $this->weight->format($weight, $this->config->get('config_weight_class_id')) . ')',
                        'cost'         => $cost,
                        'text'         => $this->currency->format($cost)
                    );
                }
            }
        }

        $method_data = array();

        if ($quote_data) {
            $method_data = array(
                'code'       => 'r2s_logistics',
                'title'      => $this->language->get('text_title'),
                'quote'      => $quote_data,
                'sort_order' => $this->config->get('weight_sort_order'),
                'error'      => false
            );
        }

        return $method_data;

    }
}
