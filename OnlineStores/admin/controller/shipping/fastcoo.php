<?php

use ExpandCart\Foundation\Support\Facades\Url;

class ControllerShippingFastcoo extends Controller
{
    protected $errors = [];

    // define production url
    private $productionUrl = "https://api.fastcoo-tech.com/API/";


    public function index()
    {

        $this->language->load('shipping/fastcoo');

        $this->document->setTitle($this->language->get('heading_title_fastcoo'));

        $this->data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => Url::addPath(['common', 'home'])->format(),
                'separator' => false
            ],
            [
                'text' => $this->language->get('text_shipping'),
                'href' => Url::addPath(['extension', 'shipping'])->format(),
                'separator' => ' :: '
            ],
            [
                'text' => $this->language->get('heading_title_fastcoo'),
                'href' => Url::addPath(['shipping', 'fastcoo'])->format(),
                'separator' => ' :: '
            ],
        ];
        $this->initializer([
            'shipping/fastcoo',
            'localisation/geo_zone',
        ]);

        $this->load->model('localisation/language');
		$this->data['languages'] = $this->model_localisation_language->getLanguages();

        $this->data['data'] = $this->fastcoo->getSettings();
        $this->data['geo_zones'] = $this->geo_zone->getGeoZones();

        foreach ( $this->data['geo_zones'] as $geo_zone )
        {

            $this->data['fastcoo_weight_' . $geo_zone['geo_zone_id'] . '_rate'] = $this->config->get('fastcoo_weight_' . $geo_zone['geo_zone_id'] . '_rate');

            $this->data['fastcoo_weight_' . $geo_zone['geo_zone_id'] . '_status'] = $this->config->get('fastcoo_weight_' . $geo_zone['geo_zone_id'] . '_status');
        }

        $this->data['links'] = [
            'action' => $this->url->link('shipping/fastcoo/updateSettings', '', 'SSL'),
            'cancel' => $this->url->link('extension/shipping', '', 'SSL'),
        ];

        $this->template = 'shipping/fastcoo/shipment/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function updateSettings()
    {
        // load Language File
        $this->language->load('shipping/fastcoo');

        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            $this->response->setOutput(json_encode([
                'success' => 0,
                'errors' => [
                    'invalid request'
                ]
            ]));
            return;
        }


        $this->initializer([
            'shipping/fastcoo'
        ]);

        $fastcoo = $this->request->post['fastcoo'];

        if ( ! $this->settings_validate() )
        {
            $result_json['success'] = '0';
            $result_json['errors'] = $this->errors;

            $this->response->setOutput(json_encode($result_json));

            return;
        }

        $this->load->model('setting/setting');

        $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'fastcoo', true);

        
            $this->tracking->updateGuideValue('SHIPPING');

        $this->fastcoo->updateSettings($fastcoo);

        $this->response->setOutput(json_encode([
            'success' => 1,
            'success_msg' => $this->language->get('text_settings_success')
        ]));
        return;
    }


    public function createShipment()
    {

        // load Language File
        $this->language->load('shipping/fastcoo');

        // set Page Title
        $this->document->setTitle($this->language->get('heading_title_fastcoo'));

        // get api key
        $api_key = $this->config->get('fastcoo')['api_key'];

        $this->initializer([
            'shipping/fastcoo',
            'catalog/product',
            'sale/order',
            'localisation/weight_class',
            'localisation/country',
            'localisation/zone'
        ]);

        $settings = $this->fastcoo->getSettings();

        $curl_url = $this->productionUrl."createOrder";




        $this->data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => Url::addPath(['common', 'home'])->format(),
                'separator' => false
            ],
            [
                'text' => $this->language->get('text_shipping'),
                'href' => Url::addPath(['extension', 'shipping'])->format(),
                'separator' => ' :: '
            ],
            [
                'text' => $this->language->get('heading_title_fastcoo'),
                'href' => Url::addPath(['shipping', 'fastcoo'])->format(),
                'separator' => ' :: '
            ],
        ];



        // define allowed service type
        $this->data['service_types'] =
            [
                ['value' => '3', 'text'  => 'Express Service'],
                ['value' => '4', 'text'  => 'Advance Service']
            ];

        // define allowed bookingMode
        $this->data['bookingModes'] =
            [
                ['value' => 'COD', 'text'  => 'COD'],
                ['value' => 'CC', 'text'  => 'CC']
            ];


        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            if ($this->validate()) {
                $this->load->model('setting/setting');
                $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'fastcoo', true);
                // define sku details array
                $skuDetails = array();
                // get order products
                $orderProducts = $this->order->getOrderProducts($this->request->post['order_id']);

                foreach ($orderProducts as $key=> $orderProduct) {
                    $product_info = $this->product->getProduct($orderProduct['product_id']);
                    $weightClass = $this->weight_class->getWeightClass( $productInfo['weight_class_id'] );

                    if ( $weightClass['unit'] == 'kg' ) {
                        $weight = $productInfo['weight'];
                    } else {
                        $weight = $productInfo['weight'] * $weightClass['value'];
                    }

                    //$skuDetails[$key]['sku'] = 'testsku';
                    $skuDetails[$key]['sku'] = $product_info['sku'] ?: 'SKU-' . $product_info['product_id'];
                    $skuDetails[$key]['description'] = $product_info['name'];
                    $skuDetails[$key]['wieght'] = $weight;
                    $skuDetails[$key]['piece'] = $orderProduct['quantity'];
                    $skuDetails[$key]['cod'] = (float)$orderProduct['total'];

                }
                // check knawat order
                $is_knawat_active = $this->config->get('module_knawat_dropshipping_status');
                if($is_knawat_active != null){
                    $this->load->model('module/knawat_dropshipping');
                    $knawat_order_id = $this->model_module_knawat_dropshipping->get_knawat_meta( $order_id, 'knawat_order_id', 'order' );
                    if($knawat_order_id == false){
                        $knawat_order_id = "";
                    }
                }else{
                    $knawat_order_id = "";
                }

                $param = array(
                    "sender_name"=>$settings['name'],
                    "sender_email"=>$settings['email'],
                    "origin"=>$settings['city'],
                    "sender_phone"=>(string)$settings['mobile'],
                    "sender_address"=>$settings['address'],
                    "receiver_name"=>$this->request->post['fastcoo_entry_recipient_name'],
                    "receiver_phone"=>(string)$this->request->post['fastcoo_entry_recipient_phone'],
                    "destination"=>$this->request->post['fastcoo_entry_recipient_city'],
                    "BookingMode"=>(string)$this->request->post['fastcoo_entry_booking_mode'],
                    "receiver_address"=>$this->request->post['fastcoo_entry_recipient_address'],
                    "receiver_email"=>$this->request->post['fastcoo_entry_recipient_email'],
                    "description"=>(string)$this->request->post['fastcoo_entry_description'],
                    "reference_id"=>(string)$this->request->post['order_id'],
                    "codValue"=>(float)$this->request->post['fastcoo_entry_amount'],
                    "productType"=>"parcel",
                    "service"=>(int)$this->request->post['fastcoo_entry_service_type'],
                    "shippers_reference"=>$knawat_order_id,
                    "skudetails"=>$skuDetails
                );


                $format = "json";
                $method = "createOrder";
                $signMethod = "md5";
                $jsonDataArray = json_encode($param);
                $var="customerId".$settings['customerId']."format".$format."method".$method."signMethod".$signMethod."";
                $all_var_concatinated  = $settings['api_key'].$var.$jsonDataArray.$settings['api_key'];
                $sign = strtoupper(md5($all_var_concatinated));

                // preparing curl data

                $curl_data = array(
                    "sign"=> $sign,
                    "format"=>"json",
                    "signMethod"=>"md5",
                    "param"=>$param,
                    "customerId"=> $settings['customerId'],
                    "method"=> "createOrder"
                );

                // send curl request
                $result = $this->sendCurlRequest($curl_url, json_encode($curl_data));

                $response = json_decode($result);

                $response_erors_array = [
                    123 => "Sender Name is missing",
                    128 => "Sender Email is missing",
                    120 => "Sender City (Origin) is missing",
                    118 => "Sender City (Origin) spelling is incorrect",
                    119 => "Sender Number is missing",
                    121 => "Sender Address is missing",
                    124 => "Receiver Name is missing",
                    126 => "ReceiverAddress is missing",
                    127 => "Receiver Phone Number is missing",
                    129 => "Receiver City (Destination) spelling is incorrect",
                    133 => "Invalid or Required Mode of Payment",
                    134 => "Cod Value is required",
                    135 => "Reference ID means Booking ID is required",
                    136 => "Product type is required",
                    137 => "Service ID is missing",
                    138 => "SKU detail is missing",
                    139 => "SKU is not present or Stock is Empty",
                    140 => "Shipment with same AWB # Exist",
                    401 => "auth error"
                ];

                if(is_object($response )&& $response->status == 200) {

                    $resultData['orderData'] = [
                        "amount"=>$param['codValue'],
                        "paymentMethod"=>$param['BookingMode'],
                        "description"=>$param['description'],
                        "productType"=>$param['productType'],
                        "service"=>$param['service']
                    ];
                    $resultData['receiver'] = [
                        "city"=>$param['destination'],
                        "phone"=>$param['receiver_phone'],
                        "address"=>$param['receiver_address'],
                        "name"=>$param['receiver_name'],
                        "email"=>$param['receiver_email'],
                    ];
                    $resultData['response'] = [
                        "awb_no"=>$response->awb_no
                    ];
                    $this->fastcoo->addShipmentDetails($this->request->post['order_id'], $resultData, "pre-pair");
                    $this->response->setOutput(json_encode([
                        'success' => 1,
                        'success_msg' => $this->language->get('fastcoo_success_message'),
                        'redirect' => 1,
                        'to' => $this->url->link('shipping/fastcoo/shipmentDetails', 'order_id=' . $this->request->post['order_id'], 'SSL')->format()
                    ]));
                } else {
                    $result_json['success'] = '0';
                    $result_json['errors'] = [isset($response->Failed) ? $response->Failed : $response_erors_array[$response->status]];
                    $result_json['errors']['warning'] = $this->language->get('fastcoo_error_warning');
                    $this->response->setOutput(json_encode($result_json));
                }

            } else {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->errors;
                $this->response->setOutput(json_encode($result_json));
                return;
            }

        } else {
            // get order id
            $orderId = $this->request->get['order_id'];

            // get shipping details for check if customer make shipment before
            $shipping_details = $this->fastcoo->getShipmentDetails($orderId);

            if(count($shipping_details) > 0)
            {
                $this->redirect($this->url->link('shipping/fastcoo/shipmentDetails', 'order_id=' . $orderId, 'SSL'));
            }

            $orderData = $this->model_sale_order->getOrder($this->request->get['order_id']);

            $this->data['fastcoo_entry_name'] = $orderData['shipping_firstname']." ". $orderData['shipping_lastname'];
            $this->data['fastcoo_entry_email'] = $orderData['email'];
            $this->data['fastcoo_entry_mobile'] = $orderData['telephone'];
            $this->data['fastcoo_entry_address'] = $orderData['shipping_address_1'];
            $this->data['fastcoo_entry_country_id'] = $orderData['payment_country_id'];
            $this->data['fastcoo_entry_zone_id'] = $order_info['payment_zone_id'];
            $this->data['fastcoo_entry_city_id'] = $orderData['payment_zone_id'];
            $this->data['fastcoo_entry_city'] = $orderData['shipping_city'];
            $this->data['fastcoo_entry_cod'] = $orderData['total'];
            $this->data['order_id'] = $this->request->get['order_id'];

            $this->data['countries'] = $this->country->getCountries();
            $this->data['zones'] = $this->zone->getZonesByCountryIdAndLanguageId($this->data['fastcoo_entry_country_id'],1);

            $this->template = 'shipping/fastcoo/shipment/create.expand';
            $this->children = array(
                'common/footer',
                'common/header'
            );
            $this->response->setOutput($this->render_ecwig());
            return;
        }
    }
    public function shipmentDetails()
    {
        // load Language File
        $this->language->load('shipping/fastcoo');
        // set Page Title
        $this->document->setTitle($this->language->get('heading_title_fastcoo'));

        $this->initializer([
            'shipping/fastcoo',
            'localisation/country',
            'localisation/zone'
        ]);

        $this->data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => Url::addPath(['common', 'home'])->format(),
                'separator' => false
            ],
            [
                'text' => $this->language->get('text_shipping'),
                'href' => Url::addPath(['extension', 'shipping'])->format(),
                'separator' => ' :: '
            ],
            [
                'text' => $this->language->get('heading_title_fastcoo'),
                'href' => Url::addPath(['shipping', 'fastcoo'])->format(),
                'separator' => ' :: '
            ],
        ];

        // get order id
        $orderId = $this->request->get['order_id'];

        // get shipment data
        $orderData = $this->fastcoo->getShipmentDetails($orderId);

        $shipment_details = json_decode($orderData['details'],true, 512, JSON_UNESCAPED_UNICODE);


        $this->data['fastcoo_entry_name'] = $shipment_details['receiver']['name'];
        $this->data['fastcoo_entry_mobile'] = $shipment_details['receiver']['phone'];
        $this->data['fastcoo_entry_email'] = $shipment_details['receiver']['email'];
        $this->data['fastcoo_entry_address'] = $shipment_details['receiver']['address'];
        $this->data['fastcoo_city'] = $shipment_details['receiver']['city'];
        $this->data['fastcoo_entry_amount'] = $shipment_details['orderData']['amount'];
        $this->data['fastcoo_entry_service'] = $shipment_details['orderData']['service'];
        $this->data['fastcoo_entry_payment_method'] = $shipment_details['orderData']['paymentMethod'];
        $this->data['fastcoo_entry_description'] = $shipment_details['orderData']['description'];
        $this->data['fastcoo_entry_type_delivery'] = $shipment_details['orderData']['typeDelivery'];
        $this->data['fastcoo_entry_goods_value'] = $shipment_details['orderData']['goodsValue'];
        $this->data['fastcoo_entry_awb_no'] = $shipment_details['response']['awb_no'];
        $this->data['order_id'] = $orderId;



        $this->template = 'shipping/fastcoo/shipment/details.expand';
        $this->children = array(
            'common/footer',
            'common/header'
        );
        $this->response->setOutput($this->render_ecwig());
        return;
    }

    function trackShipment()
    {
        // load Language File
        $this->language->load('shipping/fastcoo');
        // set Page Title
        $this->document->setTitle($this->language->get('heading_title_fastcoo'));

        $this->initializer([
            'shipping/fastcoo'
        ]);

        $settings = $this->fastcoo->getSettings();

        $curl_url = $this->productionUrl."trackShipmentFm";


        $this->data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => Url::addPath(['common', 'home'])->format(),
                'separator' => false
            ],
            [
                'text' => $this->language->get('text_shipping'),
                'href' => Url::addPath(['extension', 'shipping'])->format(),
                'separator' => ' :: '
            ],
            [
                'text' => $this->language->get('heading_title_fastcoo'),
                'href' => Url::addPath(['shipping', 'fastcoo'])->format(),
                'separator' => ' :: '
            ],
        ];

        // get order id
        $orderId = $this->request->get['order_id'];

        $this->data['order_id'] = $orderId;

        // get shipment data
        $orderData = $this->fastcoo->getShipmentDetails($orderId);

        $shipment_details = json_decode($orderData['details'],true, 512, JSON_UNESCAPED_UNICODE);

        // preparing curl data

        $curl_data = array(
            "awb"=> $shipment_details['response']['awb_no']
        );

        // send curl request
        $result = $this->sendCurlRequest($curl_url, json_encode($curl_data));
        $response = json_decode($result,true);

        if(is_array($response) && $response['status'] == 'success'){
            $this->data['trackingDetails'] = $response['travel_history'];
        }else{
            $result_json['success'] = '0';
            $result_json['errors'] = [$result['response']->message];
            $result_json['errors']['warning'] = $this->language->get('fastcoo_error_warning');
            $this->response->setOutput(json_encode($result_json));
        }


        $this->template = 'shipping/fastcoo/shipment/track.expand';
        $this->children = array(
            'common/footer',
            'common/header'
        );
        $this->response->setOutput($this->render_ecwig());
        return;

    }

    function cancelShipment()
    {
        // load Language File
        $this->language->load('shipping/fastcoo');


        $this->initializer([
            'shipping/fastcoo'
        ]);

        $settings = $this->fastcoo->getSettings();

        $curl_url = $this->productionUrl."cancelShipfulfill";


        // get order id
        $orderId = $this->request->get['order_id'];

        $this->data['order_id'] = $orderId;

        // get shipment data
        $orderData = $this->fastcoo->getShipmentDetails($orderId);

        $shipment_details = json_decode($orderData['details'],true, 512, JSON_UNESCAPED_UNICODE);


        // preparing curl data

        $curl_data = array(
            "awb"=> $shipment_details['response']['awb_no'],
            "customerId"=> $settings['customerId'],
            "secret_key"=> $settings['api_key']
        );

        // send curl request
        $result = $this->sendCurlRequest($curl_url, json_encode($curl_data));
        $response = json_decode($result);

        if(is_object($response) && $response->code == 1){
            $this->fastcoo->deleteShipment($orderId);
            $this->response->setOutput(json_encode([
                'success' => 1,
                'success_msg' => $response->Message,
                'redirect'=>1,
                'to'=>(string)$this->url->link('sale/order/info', 'order_id=' . $orderId, 'SSL')->format()
            ]));
        }else{
            $this->response->setOutput(json_encode([
                'success' => 0,
                'success_msg' => $response->Message,
                'redirect'=> 0,
            ]));
        }
        return;


    }


    private function sendCurlRequest($_url, $data)
    {
        $curl = curl_init($_url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $header = array("Content-type:application/json");
        $options = array(
            CURLOPT_RETURNTRANSFER => true,   // return web page
            CURLOPT_HEADER => false,  // don't return headers
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_FOLLOWLOCATION => true,   // follow redirects
            CURLOPT_MAXREDIRS => 10,     // stop after 10 redirects
            CURLOPT_ENCODING => "",     // handle compressed
            CURLOPT_AUTOREFERER => true,   // set referrer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
            CURLOPT_TIMEOUT => 120,    // time-out on response
            CURLOPT_POST => 1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_RETURNTRANSFER => true,

        );
        curl_setopt_array($curl, $options);


        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function country() {
        $json = array();

        $this->load->model('localisation/country');

        $country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

        if ($country_info) {
            $this->load->model('localisation/zone');

            $json = array(
                'zone'              => $this->model_localisation_zone->getZonesByCountryIdAndLanguageId($this->request->get['country_id'],1),
            );
        }

        $this->response->setOutput(json_encode($json));
    }


    private function settings_validate()
    {
        $postData = $this->request->post['fastcoo'];

        if ( ! $postData['customerId'] || empty($postData['customerId']) )
        {
            $this->errors['customerId'] = $this->language->get('error_customerId_required');
        }
        if ( ! $postData['api_key'] || empty($postData['api_key']) )
        {
            $this->errors['api_key'] = $this->language->get('error_api_key_required');
        }
        if ( ! $postData['mobile'] || empty($postData['mobile']) )
        {
            $this->errors['mobile'] = $this->language->get('error_fastcoo_entry_mobile');
        }
        if ( ! $postData['name'] || empty($postData['name']) )
        {
            $this->errors['name'] = $this->language->get('error_fastcoo_entry_name');
        }
        if ( ! $postData['city'] || empty($postData['city']) )
        {
            $this->errors['city'] = $this->language->get('error_fastcoo_entry_address');
        }
        if ( ! $postData['address'] || empty($postData['address']) )
        {
            $this->errors['address'] = $this->language->get('error_fastcoo_entry_address');
        }

        if ( ! $postData['fastcoo_weight_rate_class_id'] || empty($postData['fastcoo_weight_rate_class_id']) )
        {
            $this->error['fastcoo_weight_rate_class_id'] = $this->language->get('error_entry_weight_rate_class_id_required');
        }

        if ( $this->errors && !isset($this->errors['error']) )
        {
            $this->errors['warning'] = $this->language->get('error_warning');
        }

        return $this->errors ? false : true;
    }

    private function validate()
    {
        if ((int)$this->request->post['order_id'] == 0) {
            $this->errors['order_id'] = $this->language->get('error_fastcoo_entry_order_id');
        }

        if (empty($this->request->post['fastcoo_entry_recipient_name'])) {
            $this->errors['fastcoo_entry_recipient_name'] = $this->language->get('error_fastcoo_entry_name');
        }

        if (empty($this->request->post['fastcoo_entry_recipient_phone'])) {
            $this->errors['fastcoo_entry_recipient_phone'] = $this->language->get('error_fastcoo_entry_mobile');
        }

        if (empty($this->request->post['fastcoo_entry_recipient_city']) || !($this->request->post['fastcoo_entry_recipient_city'][0])) {
            $this->errors['fastcoo_entry_recipient_city'] = $this->language->get('error_fastcoo_entry_city');
        }

        if (empty($this->request->post['fastcoo_entry_recipient_address'])) {
            $this->errors['fastcoo_entry_recipient_address'] = $this->language->get('error_fastcoo_entry_address');
        }

        if (empty($this->request->post['fastcoo_entry_service_type']) || !($this->request->post['fastcoo_entry_service_type'][0])) {
            $this->errors['fastcoo_entry_service_type'] = $this->language->get('error_fastcoo_entry_service_type');
        }

        if (empty($this->request->post['fastcoo_entry_booking_mode']) || !($this->request->post['fastcoo_entry_booking_mode'][0])) {
            $this->errors['fastcoo_entry_booking_mode'] = $this->language->get('error_fastcoo_entry_payment_method');
        }

        if (empty($this->request->post['fastcoo_entry_amount'])) {
            $this->errors['fastcoo_entry_amount'] = $this->language->get('error_ffastcoo_entry_amount');
        }

        if (empty($this->request->post['fastcoo_entry_description'])) {
            $this->errors['fastcoo_entry_description'] = $this->language->get('error_fastcoo_entry_description');
        }

        if ($this->errors && !isset($this->errors['error'])) {
            $this->errors['warning'] = $this->language->get('error_warning');
        }
        return $this->errors ? false : true;
    }

}