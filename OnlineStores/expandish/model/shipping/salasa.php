<?php

class ModelShippingSalasa extends Model
{
    /**
     * The settings key string.
     *
     * @var string
     */
    protected $settingsGroup = 'salasa';

    /**
     * Get method data array to parse it in checkout page.
     *
     * @param string $address
     * @param int $total
     *
     * @return array
     */
    public function getQuote($address)
    {
        $this->language->load_json('shipping/salasa');

        $settings = $this->getSettings();
        $current_lang = $this->session->data['language'];
		$this->load->model('localisation/language');
		$language = $this->model_localisation_language->getLanguageByCode($current_lang);
		$current_lang = $language['language_id'];
		if ( !empty($settings['field_name_'.$current_lang]) )
		{
			$title = $settings['field_name_'.$current_lang];
		}
		else
		{
			$title = $this->language->get('text_title');
		}
        $status = true;

        if (!isset($settings['status']) || $settings['status'] != 1) {
            $status = false;
        }
        $quote_data = array();
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "geo_zone ORDER BY name");

        $status = false;

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
                $rates = explode(',', $settings['salasa_weight_' . $result['geo_zone_id'] . '_rate']);

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
                    $quote_data['salasa_weight_' . $result['geo_zone_id']] = array(
                        'code'         => 'salasa.salasa_weight_' . $result['geo_zone_id'],
                        'title'        => $result['name'] . '  (' . $this->language->get('text_weight') . ' ' . $this->weight->format($weight, $this->config->get('config_weight_class_id')) . ')',
                        'cost'         => $cost,
                        'text'         => $this->currency->format($cost)
                    );
                }
                break;
            }
        }
        if($status == false){
            $cost = $settings['salasa_weight_rate_class_id'];
            $quote_data['salasa'] = array(
                'code'         => 'salasa.salasa',
                'title'        => $title, //$this->language->get('text_title'),
                'cost'         => $cost,
                'text'         => $this->currency->format($cost)
            );
        }

        $method_data = array();

        if ($quote_data) {
            // $quote_data['salasa'] = [
            //     'code' => 'salasa.salasa',
            //     'title' => $this->language->get('text_description'),
            //     'cost' => 'n\a',
            //     'tax_class_id' => 0,
            //     'text' => 'n\a'
            // ];

            $method_data = [
                'code' => 'salasa',
                'title' => $title,//$this->language->get('text_title'),
                'quote' => $quote_data,
                'sort_order' => 1,
                'error' => false
            ];
        }

        return $method_data;
    }

    /**
     * Get all geo zones by array of zones ids.
     *
     * @param array $zones
     * @param array $address
     *
     * return array|bool
     */
    public function getGeoZone($zones, $address)
    {
        if (is_array($zones) == false) {
            $zones = [$zones];
        }

        $query = [];
        $query[] = 'SELECT * FROM zone_to_geo_zone';
        $query[] = 'WHERE geo_zone_id IN (' . implode(',', $zones) . ')';
        $query[] = 'AND country_id = "' . (int)$address['country_id'] . '"';
        $query[] = 'AND (zone_id = "' . (int)$address['zone_id'] . '" OR zone_id = "0")';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->rows;
        }

        return false;
    }

    /**
     * Return shipping settings group using the key string.
     *
     * @return array|bool
     */
    public function getSettings()
    {
        return $this->config->get($this->settingsGroup);
    }

    public function create($order_id){
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);
        
        $is_cod = (stripos($order_info['payment_code'], 'cod') !== false) ? true : false;

        $settings = $this->getSettings();

        $this->load->model('account/order');
        $order_products = $this->model_account_order->getOrderProducts($order_id);
        $products = array();
        $total_no_shipment = 0;
        $total_weight = 0;
        //$this->load->model('catalog/product');
        foreach ($order_products as $product) {
            // $product_info = $this->model_catalog_product->getProduct($product['product_id']);
            // if ($product_info['sku'] != '') {
            //     $products[] = array(
            //         'Sku' => $product_info['sku'],
            //         'Qty' => $product['quantity'],
            //         'SoldPrice' => $product['price'],
            //         'Description' => $product['name'],
            //     );
            // } else {
            //     $query = $this->db->query("SELECT o.product_option_value_id FROM `" . DB_PREFIX . "order_option` o WHERE o.order_id =" . $order_id . " AND o.order_product_id = '" . $product['order_product_id'] . "' ORDER BY o.order_id DESC");
            //     $option_id = $query->row['product_option_value_id'];
            //     $option_query = $this->db->query("SELECT o.option_sku FROM `" . DB_PREFIX . "product_option_value` o WHERE o.product_option_value_id =" . $option_id);

            //     if ($option_query) {
            //         $option_sku = $option_query->row['option_sku'];
            //         $products[] = array(
            //             'Sku' => $option_sku,
            //             'Qty' => $product['quantity'],
            //             'SoldPrice' => $product['price'],
            //             'Description' => $product['name'],
            //         );
            //     }
            // }
            $total_no_shipment += $product['total'];
            $total_weight += $product['weight'];
        }
        $total_weight = $total_weight>0 ? $total_weight : 1;
        //$checkout_date = explode(' ', $order_info['date_added']);

        $lang_id = 1;
        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();
        if($languages['en']['language_id'])
            $lang_id = $languages['en']['language_id'];

        $this->load->model('localisation/country');
        $order_info['payment_country']  = $this->model_localisation_country->getCountry($order_info['payment_country_id'], $lang_id);
        $order_info['shipping_country'] = $this->model_localisation_country->getCountry($order_info['shipping_country_id'], $lang_id);

        $data = array(
            'api_token' => $settings['api_key'],
            'orders' => [
                [
                    'merchant_id' => $settings['account_id'],
                    'merchant_key' => $settings['account_key'],
                    'customer_name' => $order_info['firstname'].' '.$this->orderInfo['lastname'],
                    'customer_telephone' =>  $order_info['telephone'],
                    'customer_email' => $order_info['email'],
                    'pickup_location_alias' => $settings['pickup_location_alias'],
                    'pickup_city' => $order_info['payment_city'],
                    'pickup_country' => $order_info['payment_country']['locale_name'], 
                    'pickup_address_1' =>$order_info['payment_address_1'], 
                    'pickup_address_2' => $order_info['payment_address_2'], 
                    'shipping_country' => $order_info['shipping_country'],
                    'shipping_city' => $order_info['shipping_city'],
                    'shipping_address_1' => $order_info['shipping_address_1'],
                    'shipping_address_2' => $order_info['shipping_address_2'],
                    'Shipping_address_3' => null,
                    'payment_type' => ($is_cod) ? 'cod' : 'paid',
                    'cod_amount' =>  $order_info['total'],
                    'declared_value' => 111,
                    'currency' => $order_info['currency_code'],
                    'weight' => $total_weight,
                    'dimension' => 10,
                    'preferred_courier' => null,
                    'service_type' => 'SDD',
                    'reference_number' => $settings['name'],
                    'reference_number_2' => null,
                    'reference_number_3' => null,
                    'shipping_zip' =>$this->orderInfo['shipping_postcode'],
                    'description' => 'new order',
                    'numberOfBoxes' => 1
                ]
            ]
        );

        $curl = curl_init('http://delivery.salasa.co/api/createShipment');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        $options = array(
            CURLOPT_RETURNTRANSFER => true,   // return web page
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'version:1.1',
                'Authorization:Bearer R0FWQCFAsOJKRo91PBQKQ5Za2Ovc9avkTz8fgLFKD8Yn5cpW114yXsU9tB6y',
                'Accept: application/json',
            ],
            CURLOPT_FOLLOWLOCATION => true,   // follow redirects
            CURLOPT_MAXREDIRS => 10,     // stop after 10 redirects
            CURLOPT_ENCODING => "",     // handle compressed
            CURLOPT_AUTOREFERER => true,   // set referrer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
            CURLOPT_TIMEOUT => 120,    // time-out on response
            CURLOPT_CUSTOMREQUEST => 'POST',
        );
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        $response = json_decode($response); 
        curl_close($curl);
        if ($response->data->orders && count($response->data->orders) > 0) {
            $query = $fields = [];
                    $query[] = 'INSERT INTO `shipments_details` SET';
                    $fields[] = 'order_id="' . $this->orderInfo['order_id'] . '"';
                    $fields[] = 'details=\'' . json_encode($response->data->orders[0]) . '\'';
                    $fields[] = 'shipment_status="1"';
                    $fields[] = 'shipment_operator="salasa"';
                    $query[] = implode(', ', $fields);
            //        $query[] = 'ON DUPLICATE KEY UPDATE details="' . json_encode($details) . '"';
            $this->db->query(implode(' ', $query));
        }
    //     $data = array(
    //         'AccountID' => $settings['account_id'],
    //         'Key' => $settings['api_key'],
    //         'Warehouse' => 'sal1',
    //         'orders' => array(
    //             array(
    //                 'orderNum' => '117-' . $order_info['order_id'],
    //                 'orderShipMethod' => 'DLV',
    //                 'CustComments' => $order_info['comment'],
    //                 'custEmail' => $order_info['email'],
    //                 'custPhone' => $order_info['telephone'],
    //                 'custFName' => $order_info['firstname'],
    //                 'custLName' => $order_info['lastname'],
    //                 'custAddress1' => $order_info['shipping_address_1'],
    //                 'custAddress2' => $order_info['shipping_address_2'],
    //                 'custCity' => $order_info['shipping_city'],
    //                 'custState' => $order_info['shipping_zone'],
    //                 'custZip' => $order_info['shipping_postcode'],
    //                 'custCountry' => $order_info['shipping_country']['name'],
    //                 'custBillFName' => $order_info['payment_firstname'],
    //                 'custBillLName' => $order_info['payment_lastname'],
    //                 'custBillCompany' => $order_info['payment_company'],
    //                 'custBillAddress1' => $order_info['payment_address_1'],
    //                 'custBillAddress2' => $order_info['payment_address_2'],
    //                 'custBillCity' => $order_info['payment_city'],
    //                 'custBillState' => $order_info['payment_zone'],
    //                 'custBillZip' => $order_info['payment_postcode'],
    //                 'custBillCountry' => $order_info['payment_country']['name'],
    //                 'orderCOD' => ($is_cod) ? $order_info['total'] : '', // check if COD
    //                 'CheckoutDate' => (count($checkout_date)) ? $checkout_date[0] : date('Y-m-d'),
    //                 'CartID' => $order_info['order_id'],
    //                 'ShipmentSubtotal' => $total_no_shipment,
    //                 'ShippingCharge' => $order_info['total'] - $total_no_shipment,
    //                 'TotalCharge' => $order_info['total'],
    //                 'items' => count($order_products),//
    //                 'Detaill' => $products
    //             )

    //         )
    //     );

    //     $postdata = http_build_query(array(
    //         'api_token' => $settings['token'],
    //         'data' => $data
    //     ));

    //     $opts = array('http' =>
    //         array(
    //             'method' => 'POST',
    //             'header' => 'Content-type: application/x-www-form-urlencoded',
    //             'content' => $postdata
    //         )
    //     );

    //     $context = stream_context_create($opts);
    //     $result = json_decode(file_get_contents('http://www.app.salasa.co/api/v1/order/create', false, $context), true);
        
    //     $details = $result['addListOrders']['allow'];

    //     if (isset($result['addListOrders']['allow']) && count($result['addListOrders']['allow']) > 0) {

    //         $query = $fields = [];
    //         $query[] = 'INSERT INTO `shipments_details` SET';
    //         $fields[] = 'order_id="' . $order_id . '"';
    //         $fields[] = 'details=\'' . json_encode($details) . '\'';
    //         $fields[] = 'shipment_status="1"';
    //         $fields[] = 'shipment_operator="salasa"';
    //         $query[] = implode(', ', $fields);
    // //        $query[] = 'ON DUPLICATE KEY UPDATE details="' . json_encode($details) . '"';

    //         $this->db->query(implode(' ', $query));
    //     }
    }
}
