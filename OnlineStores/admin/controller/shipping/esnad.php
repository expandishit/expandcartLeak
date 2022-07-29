<?php

use ExpandCart\Foundation\Support\Facades\Url;

class ControllerShippingEsnad extends Controller
{
    protected $errors = [];

    // define production url
    private $productionUrl = "http://itms.rock-express.com/";
    // define test url
    private $testUrl = "http://123.157.159.62:9011/";

    private $route = "shipping/esnad";


    public function index()
    {


        $this->language->load('shipping/esnad');

        $this->document->setTitle($this->language->get('heading_title_esnad'));

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
                'text' => $this->language->get('heading_title_esnad'),
                'href' => Url::addPath(['shipping', 'esnad'])->format(),
                'separator' => ' :: '
            ],
        ];
        $this->initializer([
            'shipping/esnad',
            'localisation/geo_zone',
        ]);

        $this->data['data'] = $this->esnad ->getSettings();

        $this->data['links'] = [
            'action' => $this->url->link('shipping/esnad/updateSettings', '', 'SSL'),
            'cancel' => $this->url->link('extension/shipping', '', 'SSL'),
        ];
        $this->data['geo_zones'] = $this->geo_zone->getGeoZones();
        $this->data['cities'] = $this->esnad->getAllCities();


        $this->template = 'shipping/esnad/shipment/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function updateSettings()
    {
        // load Language File
        $this->language->load('shipping/esnad');

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
            'shipping/esnad'
        ]);

        $esnad = $this->request->post['esnad'];

        if ( ! $this->settings_validate() )
        {
            $result_json['success'] = '0';
            $result_json['errors'] = $this->errors;

            $this->response->setOutput(json_encode($result_json));

            return;
        }

        $this->load->model('setting/setting');

        $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'esnad', true);

        
            $this->tracking->updateGuideValue('SHIPPING');

        $this->esnad->updateSettings($esnad);

        $this->response->setOutput(json_encode([
            'success' => 1,
            'success_msg' => $this->language->get('text_settings_success')
        ]));
        return;
    }


    public function createShipment()
    {

        // load Language File
        $this->language->load('shipping/esnad');

        // set Page Title
        $this->document->setTitle($this->language->get('heading_title_esnad'));

        // get data from setting table
        $testingMode = $this->config->get('esnad')['environment'];
        // check Test Mode
        if ($testingMode) {
            $curl_url = $this->testUrl."tms/api/createOrder";
        } else {
            $curl_url = $this->productionUrl."tms/api/createOrder";
        }

        $this->load->model('sale/order');

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
                'text' => $this->language->get('heading_title_esnad'),
                'href' => Url::addPath(['shipping', 'esnad'])->format(),
                'separator' => ' :: '
            ],
        ];

        $this->initializer([
            'shipping/esnad',
            'catalog/product',
            'sale/order',
        ]);

        // get settings
        $config = $this->esnad->getSettings();

        // get esnad supported cities
        $this->data['cities'] = $this->esnad->getAllCities();

        // get order products
        $order_products = $this->order->getOrderProducts($this->request->get['order_id']);

        // define total wight
        $weighttot = 0;

        $config_weight_class_id = $this->config->get('config_weight_class_id');

        $this->data['net_quantity'] = 0;

        // calculate order products weight
        foreach ($order_products as $order_product) {

            $this->data['net_quantity'] += $order_product['quantity'];

            if (isset($order_product['order_option'])) {
                $order_option = $order_product['order_option'];
            } elseif (isset($this->request->get['order_id'])) {
                $order_option = $this->order->getOrderOptions($this->request->get['order_id'], $order_product['order_product_id']);
                // get option value for add product option weight to product actual weight
                if(count($order_option) > 0) {
                    $product_option_value = $this->order->getProductOptionValue((int)$order_option[0]['product_option_value_id']);
                }

                $product_weight_query = $this->esnad->getProductWeight($order_product['product_id']);
            } else {
                $order_option = array();
            }
            $prodweight = $this->weight->convert($product_weight_query['weight'], $product_weight_query['weight_class_id'], $config_weight_class_id);
            // check if product has option
            if(isset($product_option_value) && is_array($product_option_value) && count($product_option_value) > 0)
            {
                $prodOptionWeight = $this->weight->convert($product_option_value[0]['weight'], $product_weight_query['weight_class_id'], $config_weight_class_id);
                if($product_option_value[0]['weight_prefix'] == '+'){
                    $prodweight = ($prodweight + $prodOptionWeight);
                }else{
                    $prodweight = ($prodweight - $prodOptionWeight);
                }
            }
            $prodweight = ($prodweight * $order_product['quantity']);

            $weighttot = ($weighttot + $prodweight);
        }
        $this->data['weighttot'] = number_format($weighttot,2);


        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            if ($this->validate()) {
                $this->load->model('setting/setting');
                $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'esnad', true);
                // get settings
                $config = $this->esnad->getSettings();

                $orderData = $this->order->getOrder($this->request->post['order_id']);


                // preparing curl data
                $curl_data = array(
                                "codAmount" => $this->request->post['esnad_entry_amount'],
                                "currency" => "SAR",
                                "customerCode" => $config['customer_code'],
                                "customerNo" => $this->request->post['esnad_entry_ref_num'],
                                "token" => $config['token'],
                                "isCod" => $this->request->post['esnad_entry_is_cod'],
                                "orderAmount" => $this->request->post['esnad_entry_amount'],

                                "packageList" => array([
                                    "packageCode" => $this->request->post['esnad_entry_package_code'],
                                    "packageVolume" => $this->request->post['esnad_entry_package_volume'],
                                    "packageWeight" => $this->request->post['esnad_entry_package_weight'],
                                    "packageHeight" => 0,
                                    "packageLength" => 0,
                                    "packageWidth" => 0
                                ]),

                                "receiver" => [
                                    "address" => $this->request->post['esnad_entry_recipient_address'],
                                    "cityId" => $this->request->post['esnad_entry_recipient_city'],
                                    "cityName" => $this->esnad->getEsnadCity((int)$this->request->post['esnad_entry_recipient_city'])['name'],
                                    "countryId" => 1876,
                                    "countryName" => "SaudiArabia",
                                    "name" => $this->request->post['esnad_entry_recipient_name'],
                                    "phone" => $this->request->post['esnad_entry_recipient_phone']
                                ],

                                "sender" => [
                                     "address" => $config['address'],
                                     "cityId" => $config['esnad_city_id'],
                                     "cityName" => $this->esnad->getEsnadCity((int)$config['esnad_city_id'])['name'],
                                     "countryId" => 1876,
                                     "countryName" => "SaudiArabia",
                                     "name" => $config['name'],
                                     "phone" => $config['mobile']
                                 ],

                                "totalInnerCount" =>  $this->request->post['esnad_entry_totalInnerCount'],
                                "totalPackageCount" =>  1,
                                "totalWeight" =>  $this->request->post['esnad_entry_package_weight'],
                                "totalVolume" => $this->request->post['esnad_entry_package_volume']
                );


                // send curl request
                $result = $this->sendCurlRequest($curl_url, json_encode($curl_data));

                if ($result['response']->success == true) {
                    $resultData['orderData'] = $curl_data;
                    $resultData['response'] = [
                        "label_url"=>$result['response']->dataObj->labelUrl,
                        "trackingNo" => $result['response']->dataObj->trackingNo
                    ];

                    $this->load->model("shipping/esnad");
                    $this->model_shipping_esnad->addShipmentDetails($this->request->post['order_id'], $resultData);

                    $this->response->setOutput(json_encode([
                        'success' => 1,
                        'success_msg' => $this->language->get('esnad_success_message'),
                        'redirect' => 1,
                        'to' => $this->url->link('shipping/esnad/shipmentDetails', 'order_id=' . $this->request->post['order_id'], 'SSL')->format()
                    ]));
                } else {
                    $result_json['success'] = '0';
                    $result_json['errors'] = [$result['response']->errorMsg];
                    $result_json['errors']['warning'] = $this->language->get('esnad_error_warning');
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

            $this->load->model("shipping/esnad");
            // get shipping details for check if customer make shipment before
            $shipping_details = $this->model_shipping_esnad->getShipmentDetails($orderId);

            if(count($shipping_details) > 0)
            {
                $this->redirect($this->url->link('shipping/esnad/shipmentDetails', 'order_id=' . $orderId, 'SSL'));
            }

            $orderData = $this->model_sale_order->getOrder($this->request->get['order_id']);

            $this->data['esnad_entry_name'] = $orderData['shipping_firstname'] . $orderData['shipping_lastname'];
            $this->data['esnad_entry_email'] = $orderData['email'];
            $this->data['esnad_entry_mobile'] = $orderData['telephone'];
            $this->data['esnad_entry_address'] = $orderData['shipping_address_1'];
            $this->data['esnad_city'] = $orderData['shipping_country'];
            $this->data['esnad_entry_cod'] = $orderData['total'];
            $this->data['esnad_entry_currency_code'] = $orderData['currency_code'];
            $this->data['order_id'] = $this->request->get['order_id'];

            $this->template = 'shipping/esnad/shipment/create.expand';
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
        $this->language->load('shipping/esnad');
        // set Page Title
        $this->document->setTitle($this->language->get('heading_title_esnad'));
        $this->load->model('sale/order');

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
                'text' => $this->language->get('heading_title_esnad'),
                'href' => Url::addPath(['shipping', 'esnad'])->format(),
                'separator' => ' :: '
            ],
        ];

        // get order id
        $orderId = $this->request->get['order_id'];

        $this->load->model("shipping/esnad");
        // get order data
        $orderData = $this->model_shipping_esnad->getShipmentDetails($orderId);

        $this->data['shipment_details'] = json_decode($orderData['details'],true, 512, JSON_UNESCAPED_UNICODE);

        $this->data['esnad_sticker_pdf'] = $this->data['shipment_details']['response']['label_url'];

        $this->data['order_id'] = $orderId;


        $this->template = 'shipping/esnad/shipment/details.expand';
        $this->children = array(
            'common/footer',
            'common/header'
        );
        $this->response->setOutput($this->render_ecwig());
        return;
    }

    private function sendCurlRequest($_url, $data)
    {

        $curl = curl_init($_url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $options = array(
            CURLOPT_RETURNTRANSFER => true,   // return web page
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
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
        $result = ['response' => $response];
        return $result;
    }

    public function install() {

        $this->load->model("shipping/esnad");
        $this->model_shipping_esnad->install();
    }

    public function uninstall() {

        $this->load->model("shipping/esnad");
        $this->model_shipping_esnad->uninstall();
    }

    private function settings_validate()
    {
        $postData = $this->request->post['esnad'];


        if ( ! $postData['token'] || empty($postData['token']) )
        {
            $this->errors['token'] = $this->language->get('error_esnad_entry_token');
        }

        if (! $postData['customer_code'] || empty($postData['customer_code'])) {
            $this->errors['customer_code'] = $this->language->get('error_esnad_customer_code');
        }

        if ( ! $postData['mobile'] || empty($postData['mobile']) )
        {
            $this->errors['mobile'] = $this->language->get('error_esnad_entry_mobile');
        }

        if ( ! $postData['name'] || empty($postData['name']) )
        {
            $this->errors['name'] = $this->language->get('error_esnad_entry_name');
        }


        if ( ! $postData['esnad_city_id'] || empty($postData['esnad_city_id']) )
        {
            $this->errors['city'] = $this->language->get('error_esnad_entry_city');
        }

        if ( ! $postData['address'] || empty($postData['address']) )
        {
            $this->errors['address'] = $this->language->get('error_esnad_entry_address');
        }


        if ( ! $postData['shipping_cost'] || empty($postData['shipping_cost']) )
        {
            $this->errors['shipping_cost'] = $this->language->get('error_esnad_entry_shipping_cost');
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
            $this->errors['order_id'] = $this->language->get('error_esnad_entry_order_id');
        }

        if (empty($this->request->post['esnad_entry_recipient_name'])) {
            $this->errors['esnad_entry_recipient_name'] = $this->language->get('error_esnad_entry_name');
        }

        if (empty($this->request->post['esnad_entry_recipient_phone'])) {
            $this->errors['esnad_entry_recipient_phone'] = $this->language->get('error_esnad_entry_mobile');
        }

        if (empty($this->request->post['esnad_entry_recipient_city'])) {
            $this->errors['esnad_entry_recipient_city'] = $this->language->get('error_esnad_entry_city');
        }

        if (empty($this->request->post['esnad_entry_recipient_address'])) {
            $this->errors['esnad_entry_recipient_address'] = $this->language->get('error_esnad_entry_address');
        }

        if (empty($this->request->post['esnad_entry_ref_num'])) {
            $this->errors['esnad_entry_ref_num'] = $this->language->get('error_esnad_entry_ref_num');
        }

        if (empty($this->request->post['esnad_entry_is_cod'])) {
            $this->errors['esnad_entry_is_cod'] = $this->language->get('error_esnad_entry_is_cod');
        }

        if (empty($this->request->post['esnad_entry_package_code'])) {
            $this->errors['esnad_entry_package_code'] = $this->language->get('error_esnad_entry_package_code');
        }

        if (empty($this->request->post['esnad_entry_package_weight'])) {
            $this->errors['esnad_entry_package_weight'] = $this->language->get('error_esnad_entry_package_weight');
        }

        if (empty($this->request->post['esnad_entry_package_volume']) ) {
            $this->errors['esnad_entry_package_volume'] = $this->language->get('error_esnad_entry_package_volume');
        }

        if (empty($this->request->post['esnad_entry_totalInnerCount'])) {
            $this->errors['esnad_entry_totalInnerCount'] = $this->language->get('error_esnad_entry_pcs');
        }

        if ($this->errors && !isset($this->errors['error'])) {
            $this->errors['warning'] = $this->language->get('error_warning');
        }
        return $this->errors ? false : true;
    }

}