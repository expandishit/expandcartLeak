<?php
class ModelPaymentPayWaves extends Model {
    /**
    * @var constant
    */
    const GATEWAY_NAME = 'paywaves';
    

    public function getMethod($address, $total) {
      $settings = $this->config->get('paywaves');

      $this->language->load_json("payment/" . self::GATEWAY_NAME);

      $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$settings['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

      $status = !$settings['geo_zone_id'] || $query->num_rows ? true : false;
      //check if digital & national scheme 
      $enabled = $settings['payment_networks']['digital_national_payment_scheme']['status'] == 1 ? true : false;

      $method_data = [];

      if ($status & $enabled) {
          $method_data = [
            'code'       => self::GATEWAY_NAME,
            'title'      => $settings['payment_networks']['digital_national_payment_scheme']['name'][$this->config->get('config_language_id')] ?: $this->language->get('text_title_digital_national_card'),
            'sort_order' => 0
          ];
        }

        return $method_data;
    }
}


