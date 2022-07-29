<?php
class ModelShippingFedexDomestic extends Model {

	private $shipping_key = 'fedex_domestic';
    private $baseUrl = "http://82.129.197.84:8080/api/";
    private $cities = [];

	function getQuote($address) {
		$this->language->load_json('shipping/fedex_domestic');

        $status = true;

        $cost = (float)$this->calculate($address);

		$method_data = array();

		if ($status) {
			$quote_data = array();

      		$quote_data['fedex_domestic'] = array(
        		'code'         => 'fedex_domestic.fedex_domestic',
        		'title'        => $this->language->get('text_description'),
        		'cost'         => $cost,
        		'tax_class_id' => '',
				'text'         => $this->currency->format($this->currency->convert($cost, 'EGP', $this->config->get('config_currency')))
      		);

      		$method_data = array(
        		'code'       => 'fedex_domestic',
        		'title'      => $this->language->get('text_title'),
        		'quote'      => $quote_data,
				'sort_order' => $this->config->get('flat_sort_order'),
        		'error'      => ''
      		);
		}

		return $method_data;
	}

    /**
     * Return shipping cost using the address.
     *
     * @return array|bool
     */
    public function calculate($address){
        $cost = 0;
        $source = 0;
        $destination = 0;

        $weight_code = strtoupper($this->weight->getUnit($this->config->get('config_weight_class_id'))) == 'KG' ? 2 : 1;
        $total_weight = $this->cart->getWeight() ?? 0;

        $shipper_city = $this->config->get('fedex_domestic_shipper_city');
        $reciever_city = $address['zone'];

        //////// get cities
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,$this->baseUrl.'shippingCities' );
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($curl);

        curl_close($curl);
        $cities = json_decode($result, true);

        if(count($cities)){
            foreach ($cities['cities'] as $city){
               if($city['code'] == $shipper_city) {
                   $source = $city['id'];
               }else if($city['city_en'] == $reciever_city || $city['city_ar'] == $reciever_city) {
                   $destination = $city['id'];
               }
            }
        }
        //////////////////

        $data = array(
            'source'         => $source,
            'destination'    => $destination,
            'weight_unit'    => $weight_code,
            'weight'         => $total_weight
        );

        $curl = curl_init();
	    curl_setopt($curl, CURLOPT_URL,$this->baseUrl.'shippingCalculator' );
	    curl_setopt($curl, CURLOPT_POST, true);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	    $result = curl_exec($curl);

	    curl_close($curl);
	    $shippresult = json_decode($result, true);

        if (isset($shippresult['total_price'])) {
            $cost = $shippresult['total_price'];
        }

        return  $cost;
    }
}
?>