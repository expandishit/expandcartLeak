<?php
class ModelShippingSkynet extends Model {

    const BASE_API_URL   = 'https://api.postshipping.com/api2';

    function getQuote($address){

      $this->language->load_json('shipping/skynet');

      $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('skynet_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

      $status = !$this->config->get('skynet_geo_zone_id') ||  $query->num_rows ? TRUE : FALSE;

      $method_data = [];

      if ($status) {
        $quote_data = [];

        $order_cost = $this->_calculateOrderCost($address)['PricingResponseDetails'][0]['TotalAmount'];

        $quote_data['skynet'] = array(
          'code'         => 'skynet.skynet',
          'title'        => $this->language->get('skynet_title'),
          'cost'         => $order_cost,
          'text'         => $this->currency->format($order_cost, $this->session->data['currency'])
        );

        $method_data = array(
          'code'       => 'skynet.skynet',
          'title'      => $this->language->get('skynet_title'),
          'quote'      => $quote_data,
          'sort_order' => 0,
          'error'      => false
        );
      }

      return $method_data;
    }

    private function _calculateOrderCost($address){

      $pickup_country = $this->_getPickupCountry();

      $data =[
      	"DepartureCountryCode"      => $pickup_country['iso_code_2'] , //store country
      	"DeparturePostcode"         => "",
      	"DepartureLocation"         => $pickup_country['name'],

      	"ArrivalCountryCode"        => $address['iso_code_2'],
      	"ArrivalPostcode"           => $address['postcode']?:"",
      	"ArrivalLocation"           => $address['country'],

        "PaymentCurrencyCode"       => $this->session->data['currency'],
      	"WeightMeasure"             => "KG",
      	"Weight"                    => $this->_getWeightInKg(),
      	"NumofItem"                 => $this->cart->countProducts(),

        "ServiceType"               => "ED",
      	"CustomCurrencyCode"        => $this->session->data['currency'],
      	"CustomAmount"              => $this->cart->getTotal()
      ];

      $url = self::BASE_API_URL . '/rates';
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data) );
      curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        "Token: " . $this->config->get('skynet_token')
      ]);
      $response = curl_exec($curl);
      curl_close($curl);
      return json_decode($response, true);
    }

    private function _getWeightInKg(){
      $w = $this->cart->getWeight();

      if( $this->config->get('config_weight_class_id') != 1 ){ //KG class id
        return $this->weight->convert($w, $this->config->get('config_weight_class_id'), 1);
      }

      return $w;
    }

    private function _getPickupCountry(){
      $store_country_id = $this->config->get('config_country_id');
      $country = $this->db->query("SELECT * FROM `". DB_PREFIX ."country` WHERE `country_id` = ".$store_country_id)->row;
      return $country;
    }


}
