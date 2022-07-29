<?php
class ModelPaymentPayWavesIC extends Model {
    /**
    * @var constant
    */
    const GATEWAY_NAME = 'paywaves_ic';
    

    public function getMethod($address, $total) {
      $settings = $this->config->get(self::GATEWAY_NAME);

      $this->language->load_json("payment/paywaves");

      $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$settings['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

      $status  = !$settings['geo_zone_id'] || $query->num_rows ? true : false;
      $enabled = $settings['payment_networks']['international_card_scheme']['status'] == 1 ? true : false;

      $method_data = [];

      if ($status & $enabled) {
          $method_data = [
            'code'       => self::GATEWAY_NAME,
            'title'      => $settings['payment_networks']['international_card_scheme']['name'][$this->config->get('config_language_id')] ?: $this->language->get('text_title_international_card'),            
            'sort_order' => 0
          ];
        }

        return $method_data;
    }

}


