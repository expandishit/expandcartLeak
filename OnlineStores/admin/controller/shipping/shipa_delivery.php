<?php

use ExpandCart\Foundation\Support\Facades\Url;

class ControllerShippingShipaDelivery extends Controller
{
    protected $errors = [];

    const DEFAULT_VERSION = 1;
    /**
     * Mapping urls by versions
     */
    const URLs_MAP = [
        1 => [
            'testUrl'       => 'https://sandbox-api.shipadelivery.com/',
            'productionUrl' => 'https://api.shipadelivery.com/',
        ],
        2 => [
            'testUrl'       => 'https://sandbox-api.shipadelivery.com/v2/',
            'productionUrl' => 'https://api.shipadelivery.com/v2/',
        ],

    ];

    private function resolveURL($version)
    {
        $config = $this->config->get('shipa_delivery');
        $testingMode = $config['environment'] ? 'testUrl' : $config['environment'];
        $key =  $testingMode ?'testUrl' : 'productionUrl';
        $version =  $version ?: self::DEFAULT_VERSION;
        return array_key_exists($version, self::URLs_MAP)
            ? self::URLs_MAP[$version][$key]
            : self::URLs_MAP[self::DEFAULT_VERSION][$key];
    }

    public function index()
    {
        $this->language->load('shipping/shipa_delivery');
        $this->document->setTitle($this->language->get('heading_title_shipa_delivery'));
        $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

        $this->initializer([
            'shipping/shipa_delivery',
            'localisation/geo_zone',
        ]);

        $this->data['data'] = $this->shipa_delivery ->getSettings();
        $this->data['geo_zones'] = $this->geo_zone->getGeoZones();
        $this->data['cities'] = $this->shipa_delivery->getAllCities();
        $this->data['countries'] = $this->getCountriesList();

        $this->template = 'shipping/shipa_delivery/shipment/shipa.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function updateSettings()
    {
        $this->language->load('shipping/shipa_delivery');
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
            'shipping/shipa_delivery'
        ]);

        $shipa_delivery = $this->request->post['shipa'];
        if ( ! $this->settings_validate() )
        {
            $result_json['success'] = '0';
            $result_json['errors'] = $this->errors;
            $this->response->setOutput(json_encode($result_json));
            return;
        }
        $this->load->model('setting/setting');
        $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'shipa_delivery', true);
        $this->model_setting_setting->editGuideValue('GETTING_STARTED', 'SHIPPING', '1');


            $this->tracking->updateGuideValue('SHIPPING');

        $this->shipa_delivery->updateSettings($shipa_delivery);
        $this->response->setOutput(json_encode([
            'success' => 1,
            'success_msg' => $this->language->get('text_settings_success')
        ]));
        return;
    }

    public function createShipment()
    {
        $this->language->load('shipping/shipa_delivery');
        $this->document->setTitle($this->language->get('heading_title_shipa_delivery'));
        $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

        $this->initializer([
            'shipping/shipa_delivery'
        ]);
        $this->data['version']  = $this->config->get('shipa_delivery')['version'];
        $this->loadVersionRequiredData($this->data['version']);

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->submitCreateShipment($this->request->post);
        }else{
          $this->getShipmentDetails($this->request->get['order_id']);
        }
    }

    public function submitCreateShipment($formData)
    {
         $api_key = $this->config->get('shipa_delivery')['api_key'];
         $curl_url = $this->resolveURL($this->config->get('shipa_delivery')['version'])."orders?apikey=".$api_key;
        if ($this->validate())
        {
            $this->load->model('setting/setting');
            $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'shipa_delivery', true);
            $config = $this->config->get('shipa_delivery');
            // preparing curl data
            $curl_data=$this->mapShipmentRequest($formData);
            // send curl request
            $result = $this->sendCurlRequest($curl_url, json_encode($curl_data),'POST');
            $version= $this->config->get('shipa_delivery')['version'];

            if($version==1)
            $responseCode=$result['response'][0]->code;
            if($version==2)
            $responseCode=$result['response']->code;
            //$result['response']->message
            if ($responseCode==0)
            {
                $resultData=$this->mapShipmentResponse($curl_data,$result);

                $this->load->model("shipping/shipa_delivery");
                $this->model_shipping_shipa_delivery->addShipmentDetails($formData['order_id'], $resultData, "pre-pair");
                $this->response->setOutput(json_encode([
                    'success' => 1,
                    'success_msg' => $this->language->get('shipa_delivery_success_message'),
                    'redirect' => 1,
                    'to' => $this->url->link('shipping/shipa_delivery/shipmentDetails', 'order_id=' . $formData['order_id'], 'SSL')->format()
                ]));
            } else
            {
                $result_json['success'] = '0';
                if (isset($result['response']->fault->faultstring) && !empty($result['response']->fault->faultstring)){
                    $result_json['errors'] = [$result['response']->fault->faultstring];
                }else{
                    // the error structure not the same it sometimes return object and
                    // sometimes return array of objects
                    if (is_array($result['response'])){
                        foreach ($result['response'] as $error){
                            if (isset($error->info) && !empty($error->info)){
                                $result_json['errors'][]=$error->info;
                            }
                        }
                    }else{
                        if (isset($result['response']->info)){
                            $result_json['errors'] = [$result['response']->info];
                        }
                        elseif (isset($result['response']->message))
                        {
                                $result_json['errors'] = $result['response']->message;
                        }
                    }
                }
                $this->response->setOutput(json_encode($result_json));
            }
        } else
        {
            $result_json['success'] = '0';
            $result_json['errors'] = $this->errors;
            $this->response->setOutput(json_encode($result_json));
            return;
        }
    }
    private function mapShipmentRequest($formData)
    {
        $config = $this->config->get('shipa_delivery');
        $version = $this->config->get('shipa_delivery')['version'];
        if($version==1)
        {
            $curl_data = [
                        [
                        "id"=>$this->_generateUniqueMerchantReference("{$formData['order_id']}-"),
                        "amount"=>(float)$formData['shipa_delivery_entry_amount'],
                        "paymentMethod"=>$formData['shipa_deleviry_entry_payment_method'],
                        "description"=>$formData['shipa_delivery_entry_description'],
                        "typeDelivery"=>$formData['shipa_delivery_entry_type_delivery'],
                        "sender"=>[
                                "email"=>$config['email'],
                                "phone"=>$config['mobile'] ,
                                "address"=>isset($config['address']) ? $config['address'] : " " ,
                                "name"=>isset($config['name']) ? $config['name'] : " "
                        ],
                            "recipient"=>[
                                "city"=>$formData['shipa_deleviry_entry_recipient_city'],
                                "phone"=>$formData['shipa_deleviry_entry_recipient_phone'],
                                "address"=>$formData['shipa_deleviry_entry_recipient_address'],
                                "name"=>$formData['shipa_deleviry_entry_recipient_name']
                            ],
                            "goodsValue"=>$formData['shipa_deleviry_entry_goods_value']
                        ]

                        ];
         }
         if($version==2)
        {
            $this->load->model('sale/order');
            $products = $this->model_sale_order->getOrderProducts($formData['order_id']);
            $package_items=[];
            foreach ($products as $product)
            {
                $packages=[
                "customerRef"=>$this->_generateUniqueMerchantReference("{$formData['order_id']}-"),
                "name"=>$product['name'],
                "quantity"=>(int)$product['quantity'],
                "description"=>$product['description']
                ];
                array_push($package_items,$packages);
            }
            $curl_data = [
                        "customerRef"=>$this->_generateUniqueMerchantReference("{$formData['order_id']}-"),
                        "type"=>$formData['shipa_delivery_entry_type'],
                        "category"=>$formData['shipa_delivery_entry_category'],
                        "origin"=>
                          [
                            "contactName"=>$config['name'],
                            "email"=>isset($config['email']) ? $config['email'] : " ",
                            "contactNo"=>$config['mobile'] ,
                            "city"=>$config['city_value'] ,
                            "country"=>$config['country_value'] ,
                            "address"=>isset($config['address']) ? $config['address'] : " "
                          ],
                        "destination"=>
                          [
                            "contactName"=>$formData['shipa_deleviry_entry_recipient_name'],
                            "city"=>$formData['shipa_deleviry_entry_recipient_city'],
                            "country"=>$formData['shipa_deleviry_entry_recipient_country'],
                            "contactNo"=>$formData['shipa_deleviry_entry_recipient_phone'],
                            "address"=>$formData['shipa_deleviry_entry_recipient_address'],
                            "options"=>[
                                "amountToCollect"=>(float)$formData['shipa_delivery_entry_amountToCollect'],
                                "specialInstructions"=>$formData['shipa_delivery_entry_specialInstructions']
                                        ]
                           ],
                        "packages"=> $package_items
                        ];
         }

        return $curl_data;
    }
    private function mapShipmentResponse($curl_data,$result)
    {
        $version= $this->config->get('shipa_delivery')['version'];
            $resultData['version']= $version;
            if($version==1)
            {
                $curl_data = array_shift($curl_data);
                $resultData['orderData'] = [
                    "amount"=>$curl_data['amount'],
                    "paymentMethod"=>$curl_data['paymentMethod'],
                    "description"=>$curl_data['description'],
                    "typeDelivery"=>$curl_data['typeDelivery'],
                    "goodsValue"=>$curl_data['goodsValue']
                ];
                $resultData['recipient'] = [
                    "city"=>$curl_data['recipient']['city'],
                    "phone"=>$curl_data['recipient']['phone'],
                    "address"=>$curl_data['recipient']['address'],
                    "name"=>$curl_data['recipient']['name'],
                ];
                $resultData['response'] = [
                    "reference"=>$result['response'][0]->deliveryInfo->reference,
                    "codeStatus"=>$result['response'][0]->deliveryInfo->codeStatus,
                    "startTime"=>$result['response'][0]->deliveryInfo->startTime,
                    "endTime"=>$result['response'][0]->deliveryInfo->endTime,
                    "id"=>$result['response'][0]->id
                ];
            }
            if($version==2)
            {
                $resultData['orderData'] = [
                    "amount"=>$curl_data['destination']['options']['amountToCollect'],
                    "description"=>$curl_data['destination']['options']['specialInstructions'],
                    "type"=>$curl_data['type'],
                    "category"=>$curl_data['category'],
                ];
                $resultData['recipient'] = [
                    "city"=>$curl_data['destination']['city'],
                    "country"=>$curl_data['destination']['country'],
                    "phone"=>$curl_data['destination']['contactNo'],
                    "address"=>$curl_data['destination']['address'],
                    "name"=>$curl_data['destination']['contactName'],
                ];
                $resultData['response'] = [
                    "reference"=>$result['response']->shipaRef,
                    "codeStatus"=>$result['response']->orderStatus,
                ];
            }
        return  $resultData;
    }
    private function loadVersionRequiredData($version)
    {
        if($version==1)
        {
            $this->data['paymentMethods'] =
                [
                    ['value' => 'Prepaid', 'text'  => $this->language->get('paymentMethod_prepaid')],
                    ['value' => 'CashOnDelivery', 'text'  => $this->language->get('paymentMethod_CashOnDelivery')],
                    ['value' => 'CCOD', 'text'  => $this->language->get('paymentMethod_ccod')]
                ];

            $this->data['delivery_types'] =
            [
                ['value' => 'forward', 'text'  => $this->language->get('typeDelivery_forward')],
                ['value' => 'reverse', 'text'  => $this->language->get('typeDelivery_reverse')]
            ];
            $this->data['cities'] = $this->shipa_delivery->getAllCities();

        }
        if($version==2)
        {
            $this->data['types'] =
            [
                ['value' => 'Delivery', 'text'  => $this->language->get('type_Delivery')],
                ['value' => 'Pickup', 'text'  => $this->language->get('type_Pickup')]
            ];
            $this->data['categories'] =
            [
                ['value' => 'On Demand', 'text'  => $this->language->get('category_onDemand')],
                ['value' => 'Same Day', 'text'  => $this->language->get('category_sameDay')],
                ['value' => 'Next Day', 'text'  => $this->language->get('category_nextDay')],
                ['value' => 'Cross Border','text' => $this->language->get('category_crossBorder')]
            ];

            $this->data['countries'] = $this->getCountriesList();
            $this->data['cities'] = $this->getCitiesList("AER");

        }

        return  $this->data;

    }
    public function getShipmentDetails($orderId)
    {
        $this->load->model("shipping/shipa_delivery");
        $this->load->model('sale/order');
        // get shipping details for check if customer make shipment before
        $shipping_details = $this->model_shipping_shipa_delivery->getShipmentDetails($orderId);

        if(count($shipping_details) > 0)
        {
            $this->redirect($this->url->link('shipping/shipa_delivery/shipmentDetails', 'order_id=' . $orderId, 'SSL'));
        }

        $orderData = $this->model_sale_order->getOrder($this->request->get['order_id']);

        $this->data['shipa_delivery_entry_name'] = $orderData['shipping_firstname'] . $orderData['shipping_lastname'];
        $this->data['shipa_delivery_entry_email'] = $orderData['email'];
        $this->data['shipa_delivery_entry_mobile'] = $orderData['telephone'];
        $this->data['shipa_delivery_entry_address'] = $orderData['shipping_address_1'];
        $this->data['shipa_delivery_city'] = $orderData['shipping_country'];
        $this->data['shipa_delivery_entry_cod'] = ($orderData['payment_code'] == 'cod') ? $orderData['total'] : 0;
        $this->data['order_id'] = $this->request->get['order_id'];

        $this->template = 'shipping/shipa_delivery/shipment/create.expand';
        $this->children = array(
            'common/footer',
            'common/header'
        );
        $this->response->setOutput($this->render_ecwig());
        return;
    }
    public function shipmentDetails()
    {
        // load Language File
        $this->language->load('shipping/shipa_delivery');
        // set Page Title
        $this->document->setTitle($this->language->get('heading_title_shipa_delivery'));
        $this->load->model('sale/order');

        $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

        // get order id
        $orderId = $this->request->get['order_id'];

        $this->load->model("shipping/shipa_delivery");
        // get order data
        $orderData = $this->model_shipping_shipa_delivery->getShipmentDetails($orderId);

        $shipment_details = json_decode($orderData['details'],true, 512, JSON_UNESCAPED_UNICODE);


        $this->data['shipa_delivery_version'] = $shipment_details['version'] ?$shipment_details['version'] : 1;
        $this->data['shipa_delivery_entry_name'] = $shipment_details['recipient']['name'];
        $this->data['shipa_delivery_entry_mobile'] = $shipment_details['recipient']['phone'];
        $this->data['shipa_delivery_entry_address'] = $shipment_details['recipient']['address'];
        $this->data['shipa_delivery_city'] = $shipment_details['recipient']['city'];
        $this->data['shipa_delivery_country'] = $shipment_details['recipient']['country'];
        $this->data['shipa_delivery_entry_category'] = $shipment_details['orderData']['category'];
        $this->data['shipa_delivery_entry_type'] = $shipment_details['orderData']['type'];
        $this->data['shipa_delivery_entry_amount'] = $shipment_details['orderData']['amount'];
        $this->data['shipa_delivery_entry_payment_method'] = $shipment_details['orderData']['paymentMethod'];
        $this->data['shipa_delivery_entry_description'] = $shipment_details['orderData']['description'];
        $this->data['shipa_delivery_entry_type_delivery'] = $shipment_details['orderData']['typeDelivery'];
        $this->data['shipa_delivery_entry_goods_value'] = $shipment_details['orderData']['goodsValue'];
        $this->data['shipa_delivery_entry_reference'] = $shipment_details['response']['reference'];
        $this->data['shipa_delivery_entry_code_status'] = $shipment_details['response']['codeStatus'];
        $this->data['shipa_delivery_entry_start_time'] = $shipment_details['response']['startTime'];
        $this->data['shipa_delivery_entry_end_time'] = $shipment_details['response']['endTime'];
        $this->data['shipa_delivery_entry_reference'] = $shipment_details['response']['reference'];
        $this->data['order_id'] = $orderId;

        $this->template = 'shipping/shipa_delivery/shipment/details.expand';
        $this->children = array(
            'common/footer',
            'common/header'
        );
        $this->response->setOutput($this->render_ecwig());
        return;
    }

    function printSticker()
    {
        // get order id
        $orderId = $this->request->get['order_id'];
        $this->load->model("shipping/shipa_delivery");
        $orderData = $this->model_shipping_shipa_delivery->getShipmentDetails($orderId);
        if (isset($orderData['details']["id"]))
            unset($orderData['details']["id"]);

        $shipment_details = json_decode($orderData['details'],true);
        $version=$shipment_details['version'] ?$shipment_details['version'] : 1;
        // get api key
        $api_key = $this->config->get('shipa_delivery')['api_key'];

        $url = $this->resolveURL($version)."orders/".$shipment_details['response']['reference']."/pdf?apikey=".$api_key."&template=multipackage";
        $this->response->redirect($url);
        return;
    }
    function trackShipment()
    {
        // get order id
        $orderId = $this->request->get['order_id'];
        $this->load->model("shipping/shipa_delivery");
        $orderData = $this->model_shipping_shipa_delivery->getShipmentDetails($orderId);
        if (isset($orderData['details']["id"]))
            unset($orderData['details']["id"]);

        $shipment_details = json_decode($orderData['details'],true);
        $version=$shipment_details['version'] ?$shipment_details['version'] : 1;
        // get api key
        $api_key = $this->config->get('shipa_delivery')['api_key'];
         if($version==1){
            $trackShipmentLink = $this->resolveURL($version)."orders/".$shipment_details['response']['reference']."/history?apikey=".$api_key;
         }
         if($version==2){
          $trackShipmentLink = $this->resolveURL($version)."orders/".$shipment_details['response']['reference']."/story?apikey=".$api_key;
         }
        $result = json_decode(file_get_contents($trackShipmentLink));
        if($result)
        {
            $history = "";
            if($version==1)
            {
                foreach ($result->history as $history)
                {
                    $history .= $history->info." At ".$history->time;
                }
            }
          if($version==2)
          {
                foreach ($result as $history)
                {
                    $history .= $history->details." Status: ".$history->status;
                }

          }
            $this->response->setOutput(json_encode([
                'success' => 1,
                'success_msg' => $history,
            ]));

        }
        else {
            $this->response->setOutput(json_encode([
                'success' => 0,
                'success_msg' => 'Something Went Wrong, Please check the ApI mode or contact the support',
            ]));
        }
        return;
    }

    function getLastStatus()
    {
        // get order id
        $orderId = $this->request->get['order_id'];
        $this->load->model("shipping/shipa_delivery");
        $orderData = $this->model_shipping_shipa_delivery->getShipmentDetails($orderId);

        if (isset($orderData['details']["id"]))
            unset($orderData['details']["id"]);

        $shipment_details = json_decode($orderData['details'],true);
        $version=$shipment_details['version'] ?$shipment_details['version'] : 1;

        // get api key
        $api_key = $this->config->get('shipa_delivery')['api_key'];

        $trackShipmentLink = $this->resolveURL($version)."orders/".$shipment_details['response']['reference']."?apikey=".$api_key;

        $result = json_decode(file_get_contents($trackShipmentLink));

        if(is_object($result))
        {
            if($version==1)
                {
                    $status=$result->deliveryInfo->codeStatus;
                    $info=$result->deliveryInfo->infoStatus;
                }
            if($version==2)
                {
                    $status=$result->status;
                    $info=$result->shipaRef;
                }
            $status = "Status Code : ".$status." - Information : ".$info;

            $this->response->setOutput(json_encode([
                'success' => 1,
                'success_msg' => $status,
            ]));

        }
        else {
            $this->response->setOutput(json_encode([
                'success' => 0,
                'success_msg' => 'Something Went Wrong, Please check the ApI mode or contact the support',
            ]));
        }

        return;

    }

    public function getCountriesList()
    {
        $api_key = $this->config->get('shipa_delivery')['api_key'];
        $version=$this->config->get('shipa_delivery')['version'] ? $this->config->get('shipa_delivery')['version'] : 2;
        $curl_url = $this->resolveURL($version)."countries?apikey=".$api_key;

        $countries = $this->sendCurlRequest($curl_url,[],'GET')['response'];

        $countriesList=[];
        if (is_array($countries) && $countries[0]->code == 0)
        {
            foreach ($countries as $country)
            {
                $countriesArray=array(
                    'name' => $country->name,
                    'country_value' => $country->isoAlpha3,
                );
                array_push($countriesList,$countriesArray);
            }
        }

        return $countriesList;
    }
     function getCitiesList()
    {

        $country = $this->request->post['country_id'];
        $api_key = $this->config->get('shipa_delivery')['api_key'];
        $version=$this->config->get('shipa_delivery')['version'] ? $this->config->get('shipa_delivery')['version'] : 2;
        $curl_url = $this->resolveURL($version)."countries/".$country."?apikey=".$api_key;

        $cities = $this->sendCurlRequest($curl_url,[],'GET')['response']->cities;

        $citiesList=[];
        if (is_array($cities))
        {
            foreach ($cities as $city)
            {
                array_push($citiesList,$city->name);
            }
        }

        $this->response->setOutput(json_encode($citiesList));
    }

    private function sendCurlRequest($_url, $data=[],$method)
    {
        $curl = curl_init($_url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $options = array(
            CURLOPT_RETURNTRANSFER => true,   // return web page
            CURLOPT_HEADER => false,  // don't return headers
            CURLOPT_FOLLOWLOCATION => true,   // follow redirects
            CURLOPT_MAXREDIRS => 10,     // stop after 10 redirects
            CURLOPT_ENCODING => "",     // handle compressed
            CURLOPT_AUTOREFERER => true,   // set referrer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
            CURLOPT_TIMEOUT => 120,    // time-out on response
            CURLOPT_CUSTOMREQUEST => $method,

        );
        curl_setopt_array($curl, $options);


        $response = curl_exec($curl);
        $response = json_decode($response);
        curl_close($curl);
        $result = ['response' => $response,
            'data' => $data
        ];
        return $result;
    }

    public function install() {

        $this->load->model("shipping/shipa_delivery");
        $this->model_shipping_shipa_delivery->install();
    }

    public function uninstall() {

        $this->load->model("shipping/shipa_delivery");
        $this->model_shipping_shipa_delivery->uninstall();
    }

    private function settings_validate()
    {
        $postData = $this->request->post['shipa'];

        if ( ! $postData['api_key'] || empty($postData['api_key']) )
        {
            $this->errors['api_key'] = $this->language->get('error_api_key_required');
        }

        if ( ! $postData['mobile'] || empty($postData['mobile']) )
        {
            $this->errors['mobile'] = $this->language->get('error_shipa_delivery_entry_mobile');
        }
        if($this->request->post['shipa']['version']==1)
        {
            if ( ! $postData['email'] || empty($postData['email']) )
            {
                $this->errors['email'] = $this->language->get('error_shipa_delivery_entry_email');
            }
            if (( ! $postData['shipping_cost'] || empty($postData['shipping_cost'])) )
            {
                $this->errors['shipping_cost'] = $this->language->get('error_shipa_delivery_entry_shipping_cost');
            }
        }
        if($this->request->post['shipa']['version']==2)
        {
            if ( ! $postData['name'] || empty($postData['name']) )
            {
                $this->errors['name'] = $this->language->get('error_shipa_delivery_entry_name');
            }
            if (( ! $postData['city_value'] || empty($postData['city_value'])) )
            {
                $this->errors['city'] = $this->language->get('error_shipa_delivery_entry_city');
            }
        }

        if ( $this->errors && !isset($this->errors['error']) )
        {
            $this->errors['warning'] = $this->language->get('error_warning');
        }

        return $this->errors ? false : true;
    }

    private function validate()
    {
        $version=$this->config->get('shipa_delivery')['version'];
        if ((int)$this->request->post['order_id'] == 0) {
            $this->errors['order_id'] = $this->language->get('error_shipa_delivery_entry_order_id');
        }

        if (empty($this->request->post['shipa_deleviry_entry_recipient_name'])) {
            $this->errors['shipa_deleviry_entry_recipient_name'] = $this->language->get('error_shipa_delivery_entry_name');
        }

        if (empty($this->request->post['shipa_deleviry_entry_recipient_phone'])) {
            $this->errors['shipa_deleviry_entry_recipient_phone'] = $this->language->get('error_shipa_delivery_entry_mobile');
        }

        if (empty($this->request->post['shipa_deleviry_entry_recipient_address'])) {
            $this->errors['shipa_deleviry_entry_recipient_address'] = $this->language->get('error_shipa_delivery_entry_address');
        }

        if (empty($this->request->post['shipa_deleviry_entry_recipient_city']) || !($this->request->post['shipa_deleviry_entry_recipient_city'][0])) {
            $this->errors['shipa_deleviry_entry_recipient_city'] = $this->language->get('error_shipa_delivery_entry_city');
        }
        if($version==2)
        {
            if (empty($this->request->post['shipa_deleviry_entry_recipient_country']) || !($this->request->post['shipa_deleviry_entry_recipient_country'][0])) {
                $this->errors['shipa_deleviry_entry_recipient_country'] = $this->language->get('error_shipa_delivery_entry_country');
            }
            if (empty($this->request->post['shipa_delivery_entry_type'])) {
                $this->errors['shipa_delivery_entry_type'] = $this->language->get('error_shipa_delivery_entry_type');
            }
        }
        if($version==1)
        {
            if (empty($this->request->post['shipa_deleviry_entry_payment_method']) || !($this->request->post['shipa_deleviry_entry_payment_method'][0])) {
                $this->errors['shipa_deleviry_entry_payment_method'] = $this->language->get('error_shipa_delivery_entry_payment_method');
            }

            if ($this->request->post['shipa_deleviry_entry_payment_method'] == "Prepaid" && ($this->request->post['shipa_delivery_entry_amount'] > 0) ) {
                $this->errors['shipa_delivery_entry_amount'] = $this->language->get('error_shipa_delivery_amount');
            }

            if (empty($this->request->post['shipa_delivery_entry_type_delivery'])) {
                $this->errors['shipa_delivery_entry_type_delivery'] = $this->language->get('error_shipa_delivery_entry_delivery_type');
            }

            if (empty($this->request->post['shipa_delivery_entry_description'])) {
                $this->errors['shipa_delivery_entry_description'] = $this->language->get('error_shipa_delivery_entry_description');
            }
        }

        if ($this->errors && !isset($this->errors['error'])) {
            $this->errors['warning'] = $this->language->get('error_warning');
        }
        return $this->errors ? false : true;
    }
    private function _createBreadcrumbs(){

        $breadcrumbs = [
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
                'text' => $this->language->get('heading_title_shipa_delivery'),
                'href' => Url::addPath(['shipping', 'smsa'])->format(),
                'separator' => ' :: '
            ],
        ];

        return $breadcrumbs;
    }

    private function _generateUniqueMerchantReference($prefix , $length = 4){
        return strtoupper($prefix  . substr(md5(microtime(true).mt_Rand()) , 0 , $length));
    }

}