<?php

use ExpandCart\Foundation\Support\Facades\Url;

class ControllerShippingR2sLogistics extends Controller
{
    protected $errors = [];

    // define production url
    private $productionUrl = "https://webservice.logixerp.com/webservice/v2/";


    public function index()
    {

        $this->language->load('shipping/r2s_logistics');

        $this->document->setTitle($this->language->get('heading_title_r2s_logistics'));

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
                'text' => $this->language->get('heading_title_r2s_logistics'),
                'href' => Url::addPath(['shipping', 'smsa'])->format(),
                'separator' => ' :: '
            ],
        ];
        $this->initializer([
            'shipping/r2s_logistics',
            'localisation/geo_zone',
        ]);

        $this->data['data'] = $this->r2s_logistics ->getSettings();
        $this->data['geo_zones'] = $this->geo_zone->getGeoZones();

        foreach ( $this->data['geo_zones'] as $geo_zone )
        {

            $this->data['r2s_weight_' . $geo_zone['geo_zone_id'] . '_rate'] = $this->config->get('r2s_weight_' . $geo_zone['geo_zone_id'] . '_rate');

            $this->data['r2s_weight_' . $geo_zone['geo_zone_id'] . '_status'] = $this->config->get('r2s_weight_' . $geo_zone['geo_zone_id'] . '_status');
        }

        // define allowed hubs
        $this->data['hubs'] =
            [
                ['value' => 'CAINAS', 'text'  => "Nasr City Hub"],
                ['value' => 'CAINEWC', 'text'  => "New Cairo Hub"],
                ['value' => 'GIZDOK', 'text'  => "Dokki Hub"],
                ['value' => 'ALXSMO', 'text'  => "Alexandria Smouha Hub"],
                ['value' => 'DELTAHUB', 'text'  => "Delta Hub"],
                ['value' => 'GCAHUB', 'text'  => "Greater Cairo Hub"],
                ['value' => 'CAIRO', 'text'  => "Cairo Head Office"],
                ['value' => 'MOHAFAZAT', 'text'  => "Mohafazat Hub"],
                ['value' => 'CANALHUB', 'text'  => "Canal Hub"],
                ['value' => 'NUEGYPTHUB', 'text'  => "North Upper Egypt Hub"],
                ['value' => 'ZONE7HUB', 'text'  => "Zone 7 Hub"],
                ['value' => 'UEGYPTHUB', 'text'  => "Upper Egypt Hub"],
                ['value' => 'SAHEL', 'text'  => "Sahel Hub"]
            ];


        $this->template = 'shipping/r2s_logistics/shipment/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function updateSettings()
    {
        // load Language File
        $this->language->load('shipping/r2s_logistics');

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
            'shipping/r2s_logistics'
        ]);

        $r2s_logistics = $this->request->post['r2s_logistics'];


        if ( ! $this->settings_validate() )
        {
            $result_json['success'] = '0';
            $result_json['errors'] = $this->errors;

            $this->response->setOutput(json_encode($result_json));

            return;
        }

        $this->load->model('setting/setting');

        $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'r2s_logistics', true);

        $this->tracking->updateGuideValue('SHIPPING');

        $this->r2s_logistics->updateSettings($r2s_logistics);

        $this->response->setOutput(json_encode([
            'success' => 1,
            'success_msg' => $this->language->get('text_settings_success')
        ]));
        return;
    }


    public function createShipment()
    {

        // load Language File
        $this->language->load('shipping/r2s_logistics');

        // set Page Title
        $this->document->setTitle($this->language->get('heading_title_r2s_logistics'));

        // get data from setting table
        $testingMode = $this->config->get('r2s_logistics')['environment'];
        // check Test Mode
        if ($testingMode) {
            // r2s logistics test secure key
            $secure_key = "A3604E505DB24D118B9A2D48BDC336B3";
        } else {
            $secure_key = "9BA05777B57441AA9DCFCA33781332B8";
        }

         $curl_url = $this->productionUrl."CreateWaybill?secureKey=".$secure_key;

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
                'text' => $this->language->get('heading_title_r2s_logistics'),
                'href' => Url::addPath(['shipping', 'smsa'])->format(),
                'separator' => ' :: '
            ],
        ];

        $this->initializer([
            'shipping/r2s_logistics'
        ]);
        // define allowed Services
        $this->data['services'] =
            [
                ['value' => 'PUD', 'text'  => $this->language->get('service_pud')],
                ['value' => 'PAYCOL', 'text'  => $this->language->get('service_paycol')],
                ['value' => 'REFUND', 'text'  => $this->language->get('service_refund')],
                ['value' => 'EXCHANGE', 'text'  => $this->language->get('service_exchange')],
                ['value' => 'DROPD', 'text'  => $this->language->get('service_dropd')],
                ['value' => 'R2S+', 'text'  => $this->language->get('service_r2s')],
                ['value' => 'INTEXP', 'text'  => $this->language->get('service_intexp')],
                ['value' => 'XPUD', 'text'  => $this->language->get('service_xpud')]
            ];
        // define allowed payment methods
        $this->data['paymentMethods'] =
            [
                ['value' => 'TBB', 'text'  => $this->language->get('paymentMethod_tbb')],
                ['value' => 'FOD', 'text'  => $this->language->get('paymentMethod_fod')]
            ];
        // define allowed cod payment methods
        $this->data['codPaymentMethods'] =
            [
                ['value' => 'Cash', 'text'  => $this->language->get('codPaymentMethods_cash')],
                ['value' => 'Cheque', 'text'  => $this->language->get('codPaymentMethods_cheque')],
                ['value' => 'PayMob', 'text'  => $this->language->get('codPaymentMethods_paymob')],
                ['value' => 'MPesa', 'text'  => $this->language->get('codPaymentMethods_mpesa')]
            ];
        // define allowed weight units
        $this->data['weight_units'] =
            [
                ['value' => 'KILOGRAM', 'text'  => $this->language->get('weightUnits_kilogram')],
                ['value' => 'GRAM', 'text'  => $this->language->get('weightUnits_gram')],
                ['value' => 'TONNE', 'text'  => $this->language->get('weightUnits_tonne')],
                ['value' => 'POUND', 'text'  => $this->language->get('weightUnits_pound')],
            ];

        $this->data['states'] = $this->model_shipping_r2s_logistics->getAllStates();



        $this->data['cities'] =
            [
                ['value' => 'Agouza', 'text'  => 'Agouza'],
                ['value' => 'Al Rehab', 'text'  => 'Al Rehab']
            ];

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            if ($this->validate()) {
                $this->load->model('setting/setting');
                $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'r2s_logistics', true);
                $config = $this->config->get('r2s_logistics');
                // preparing curl data
                $curl_data = array(
                     "waybillRequestData" => array(
                            "consigneeGeoLocation"=>"",
                            "FromOU"=>$config['hub'],
                            "DeliveryDate"=>"",
                            "WaybillNumber"=>"",
                            "CustomerCode"=>$config['customer_code'],
                            "CustomerName"=>$config['name'],
                            "CustomerAddress"=>$config['address'],
                            "CustomerCity"=>"",
                            "CustomerCountry"=>"",
                            "CustomerPhone"=>$config['mobile'],
                            "CustomerState"=>"",
                            "CustomerPincode"=>"",
                            "ConsigneeCode"=>$this->request->post['r2s_logistics_entry_consignee_code'],
                            "ConsigneeName"=>$this->request->post['r2s_logistics_entry_recipient_name'],
                            "ConsigneePhone"=>$this->request->post['r2s_logistics_entry_recipient_phone'],
                            "ConsigneeAddress"=>$this->request->post['r2s_logistics_entry_recipient_address'],
                            "ConsigneeCountry"=>$this->request->post['r2s_logistics_entry_recipient_country'],
                            "ConsigneeState"=>$this->request->post['r2s_logistics_entry_recipient_state'],
                            "ConsigneeCity"=>$this->request->post['r2s_logistics_entry_recipient_city'],
                            "ConsigneePincode"=>"",
                            "ConsigneeWhat3Words"=>"word.exact.replace",
                            "StartLocation"=>"",
    	                    "EndLocation"=>"",
                            "ClientCode"=>$this->request->post['r2s_logistics_entry_client_code'],
                            "NumberOfPackages"=>$this->request->post['r2s_logistics_entry_package_number'],
                            "ActualWeight"=>(string)$this->request->post['r2s_logistics_entry_actual_weight'],
                            "ChargedWeight"=>"",
                            "CargoValue"=>!empty($this->request->post['r2s_logistics_entry_amount']) ? $this->request->post['r2s_logistics_entry_amount'] : "",
                            "ReferenceNumber"=>(string)$this->request->post['order_id'],
                            "InvoiceNumber"=>"",
                            "PaymentMode"=>$this->request->post['r2s_logistics_entry_payment_method'],
                            "ServiceCode"=>$this->request->post['r2s_logistics_entry_service'],
                            "WeightUnitType"=>$this->request->post['r2s_logistics_entry_weight_unit'],
                            "Description"=>$this->request->post['r2s_logistics_entry_description'],
                            "COD"=>!empty($this->request->post['r2s_logistics_entry_amount']) ? $this->request->post['r2s_logistics_entry_amount'] : "",
                            "CODPaymentMode"=>(string)$this->request->post['r2s_logistics_entry_cod_payment_method'],
                            "Currency"=>(string)$this->request->post['r2s_logistics_entry_currency'],
                            "PackageDetails"=>""
                     )
                );

                // send curl request
                $result = $this->sendCurlRequest($curl_url, json_encode($curl_data));
                if (is_object($result['response']) && $result['response']->messageType == "Success") {
                    $resultData['shipment_data'] = $curl_data['waybillRequestData'];
                    $resultData['shipment_data']['labelURL'] = $result['response']->labelURL;
                    $resultData['shipment_data']['packageStickerURL'] = $result['response']->packageStickerURL;
                    $resultData['shipment_data']['status'] = $result['response']->status;
                    $resultData['shipment_data']['waybillNumber'] = $result['response']->waybillNumber;
                    $this->load->model("shipping/r2s_logistics");
                    $this->model_shipping_r2s_logistics->addShipmentDetails($this->request->post['order_id'], $resultData, "pre-pair");
                    $this->response->setOutput(json_encode([
                        'success' => 1,
                        'success_msg' => $this->language->get('r2s_logistics_success_message'),
                        'redirect' => 1,
                        'to' => $this->url->link('shipping/r2s_logistics/shipmentDetails', 'order_id=' . $this->request->post['order_id'], 'SSL')->format()
                    ]));
                } else {
                    $result_json['success'] = '0';
                    $result_json['errors'] = [$result['response']->message];
                    $result_json['errors']['warning'] = $this->language->get('r2s_logistics_error_warning');
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

            $this->load->model("shipping/r2s_logistics");
            // get shipping details for check if customer make shipment before
            $shipping_details = $this->model_shipping_r2s_logistics->getShipmentDetails($orderId);

            if(count($shipping_details) > 0)
            {
                $this->redirect($this->url->link('shipping/r2s_logistics/shipmentDetails', 'order_id=' . $orderId, 'SSL'));
            }

            $orderData = $this->model_sale_order->getOrder($this->request->get['order_id']);
            // get order products for calculate order weight
            $order_products = $this->model_sale_order->getOrderProducts($this->request->get['order_id']);
            // define total wight
            $weighttot = 0;

            $config_weight_class_id = $this->config->get('config_weight_class_id');

            foreach ($order_products as $order_product) {
                if (isset($order_product['order_option'])) {
                    $order_option = $order_product['order_option'];
                } elseif (isset($this->request->get['order_id'])) {
                    $order_option = $this->model_sale_order->getOrderOptions($this->request->get['order_id'], $order_product['order_product_id']);
                    // get option value for add product option weight to product actual weight
                    if(count($order_option) > 0) {
                        $product_option_value = $this->model_sale_order->getProductOptionValue((int)$order_option[0]['product_option_value_id']);
                    }

                    $product_weight_query = $this->model_shipping_r2s_logistics->getProductWeight($order_product['product_id']);
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


            $this->data['r2s_logistics_entry_name'] = $orderData['shipping_firstname'] . $orderData['shipping_lastname'];
            $this->data['r2s_logistics_entry_email'] = $orderData['email'];
            $this->data['r2s_logistics_entry_mobile'] = $orderData['telephone'];
            $this->data['r2s_logistics_entry_address'] = $orderData['shipping_address_1'];
            $this->data['r2s_logistics_city'] = $orderData['shipping_country'];
            $this->data['r2s_logistics_entry_cod'] = $orderData['total'];
            $this->data['r2s_logistics_entry_currency_code'] = $orderData['currency_code'];
            $this->data['country_code'] = $orderData['payment_iso_code_2'];
            $this->data['order_id'] = $this->request->get['order_id'];

            $this->template = 'shipping/r2s_logistics/shipment/create.expand';
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
        $this->language->load('shipping/r2s_logistics');
        // set Page Title
        $this->document->setTitle($this->language->get('heading_title_r2s_logistics'));
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
                'text' => $this->language->get('heading_title_r2s_logistics'),
                'href' => Url::addPath(['shipping', 'r2s'])->format(),
                'separator' => ' :: '
            ],
        ];

        // get order id
        $orderId = $this->request->get['order_id'];

        $this->load->model("shipping/r2s_logistics");
        // get order data
        $orderData = $this->model_shipping_r2s_logistics->getShipmentDetails($orderId);


        $shipment_details = json_decode($orderData['details'],true, 512, JSON_UNESCAPED_UNICODE);


        $this->data['r2s_logistics_label_url'] = $shipment_details['shipment_data']['labelURL'];
        $this->data['r2s_logistics_sticker_url'] = $shipment_details['shipment_data']['packageStickerURL'];
        $this->data['r2s_logistics_entry_name'] = $shipment_details['shipment_data']['ConsigneeName'];
        $this->data['r2s_logistics_entry_consignee_code'] = $shipment_details['shipment_data']['ConsigneeCode'];
        $this->data['r2s_logistics_entry_mobile'] = $shipment_details['shipment_data']['ConsigneePhone'];
        $this->data['r2s_logistics_entry_address'] = $shipment_details['shipment_data']['ConsigneeAddress'];
        $this->data['r2s_logistics_entry_country'] = $shipment_details['shipment_data']['ConsigneeCountry'];
        $this->data['r2s_logistics_state'] = $shipment_details['shipment_data']['ConsigneeState'];
        $this->data['r2s_logistics_city'] = $shipment_details['shipment_data']['ConsigneeCity'];
        $this->data['r2s_logistics_entry_amount'] = $shipment_details['shipment_data']['COD'];
        $this->data['r2s_logistics_entry_payment_method'] = $shipment_details['shipment_data']['PaymentMode'];
        $this->data['r2s_logistics_entry_description'] = $shipment_details['shipment_data']['Description'];
        $this->data['r2s_logistics_entry_service'] = $shipment_details['shipment_data']['ServiceCode'];
        $this->data['r2s_logistics_entry_actual_weight'] = $shipment_details['shipment_data']['ActualWeight'];
        $this->data['r2s_logistics_entry_weight_unit'] = $shipment_details['shipment_data']['WeightUnitType'];
        $this->data['r2s_logistics_entry_client_code'] = $shipment_details['shipment_data']['ClientCode'];
        $this->data['r2s_logistics_entry_cod_payment_method'] = $shipment_details['shipment_data']['CODPaymentMode'];
        $this->data['r2s_logistics_entry_currency'] = $shipment_details['shipment_data']['Currency'];
        $this->data['r2s_logistics_entry_package_number'] = $shipment_details['shipment_data']['NumberOfPackages'];
        $this->data['r2s_logistics_entry_reference_number'] = $shipment_details['shipment_data']['ReferenceNumber'];
        $this->data['r2s_logistics_entry_waybill_number'] = $shipment_details['shipment_data']['waybillNumber'];
        $this->data['order_id'] = $orderId;


        $this->template = 'shipping/r2s_logistics/shipment/details.expand';
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
        $this->language->load('shipping/r2s_logistics');
        // set Page Title
        $this->document->setTitle($this->language->get('heading_title_r2s_logistics'));
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
                'text' => $this->language->get('heading_title_r2s_logistics'),
                'href' => Url::addPath(['shipping', 'r2s'])->format(),
                'separator' => ' :: '
            ],
        ];


        // get order id
        $orderId = $this->request->get['order_id'];

        $this->data['order_id'] = $orderId;

        $this->load->model("shipping/r2s_logistics");
        // get order data
        $orderData = $this->model_shipping_r2s_logistics->getShipmentDetails($orderId);

        $shipment_details = json_decode($orderData['details'],true, 512, JSON_UNESCAPED_UNICODE);

        $this->data['r2s_logistics_label_url'] = $shipment_details['shipment_data']['labelURL'];

        $this->data['r2s_logistics_sticker_url'] = $shipment_details['shipment_data']['packageStickerURL'];

        $this->load->model("shipping/r2s_logistics");
        // get data from setting table
        $testingMode = $this->config->get('r2s_logistics')['environment'];
        // check Test Mode
        if ($testingMode) {
            // r2s logistics test secure key
            $secure_key = "A3604E505DB24D118B9A2D48BDC336B3";
        } else {
            $secure_key = "9BA05777B57441AA9DCFCA33781332B8";
        }

        $curl_url = $this->productionUrl."GetTrackingByReferenceOrCarrierWayBillNumber?secureKey=".$secure_key."&referenceNumber=".$orderId;

        $result = $this->CurlRequestWithGet($curl_url);

        if(is_array($result) && $result['response']->messageType == "Success"){
            $trackData = json_decode($result['response']->waybillJson);
            $trackingDetails = $trackData->wayBillNumberTrackDetailList[0]->wayBillTrackingDetail;
            $this->data['trackingDetails'] = $trackingDetails;
        }else{
            $result_json['success'] = '0';
            $result_json['errors'] = [$result['response']->message];
            $result_json['errors']['warning'] = $this->language->get('r2s_logistics_error_warning');
            $this->response->setOutput(json_encode($result_json));
        }


        $this->template = 'shipping/r2s_logistics/shipment/track.expand';
        $this->children = array(
            'common/footer',
            'common/header'
        );
        $this->response->setOutput($this->render_ecwig());
        return;

    }

    function cancelShipment()
    {
        // get order id
        $orderId = $this->request->get['order_id'];

        $this->load->model("shipping/r2s_logistics");
        // get data from setting table
        $testingMode = $this->config->get('r2s_logistics')['environment'];
        // check Test Mode
        if ($testingMode) {
            // r2s logistics test secure key
            $secure_key = "A3604E505DB24D118B9A2D48BDC336B3";
        } else {
            $secure_key = "9BA05777B57441AA9DCFCA33781332B8";
        }

        // load Language File
        $this->language->load('shipping/r2s_logistics');

        $orderData = $this->model_shipping_r2s_logistics->getShipmentDetails($orderId);

        $shipment_details = json_decode($orderData['details'],true, 512, JSON_UNESCAPED_UNICODE);

        $waybillNumber = $shipment_details['shipment_data']['waybillNumber'];

        $curl_url = $this->productionUrl."UpdateWaybill?secureKey=".$secure_key."&waybillNumber=".$waybillNumber."&waybillStatus=Cancelled";

        $result = $this->CurlRequestWithGet($curl_url);

        if(is_array($result) && $result['response']->messageType == "Success"){
            $this->model_shipping_r2s_logistics->deleteShipment($orderId);
            $this->response->setOutput(json_encode([
                'success' => 1,
                'success_msg' => $result['response']->message,
                'redirect'=>1,
                'to'=>(string)$this->url->link('sale/order/info', 'order_id=' . $orderId, 'SSL')->format()
            ]));
        }else{
            $this->response->setOutput(json_encode([
                'success' => 0,
                'success_msg' => $result['response']->message,
                'redirect'=> 0,
            ]));
        }
        return;
    }

    private function sendCurlRequest($_url, $data)
    {
        $curl = curl_init($_url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $options = array(
            CURLOPT_RETURNTRANSFER => true,   // return web page
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'AccessKey: logixerp',
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
        $result = ['response' => $response,
            'data' => $data
        ];
        return $result;
    }

    private function CurlRequestWithGet($_url)
    {
        $curl = curl_init($_url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $options = array(
            CURLOPT_RETURNTRANSFER => true,   // return web page
            CURLOPT_HTTPHEADER => [
                'AccessKey: logixerp',
            ],
            CURLOPT_FOLLOWLOCATION => true,   // follow redirects
            CURLOPT_MAXREDIRS => 10,     // stop after 10 redirects
            CURLOPT_ENCODING => "",     // handle compressed
            CURLOPT_AUTOREFERER => true,   // set referrer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
            CURLOPT_TIMEOUT => 120,    // time-out on response
            CURLOPT_CUSTOMREQUEST => 'GET',

        );
        curl_setopt_array($curl, $options);


        $response = curl_exec($curl);
        $response = json_decode($response);
        curl_close($curl);
        $result = ['response' => $response];
        return $result;
    }

    public function install() {

        $this->load->model("shipping/r2s_logistics");
        $this->model_shipping_r2s_logistics->install();
    }

    public function uninstall() {

        $this->load->model("shipping/r2s_logistics");
        $this->model_shipping_r2s_logistics->uninstall();
    }
    public function get_cities_by_state()
    {
        if ($this->request->server['REQUEST_METHOD'] != 'POST') {

            $this->response->setOutput(json_encode(['status' => 'fail']));
            return;

        }
        $state = (int)$this->request->post['state'];

        $this->load->model('shipping/r2s_logistics');

        $cities = $this->model_shipping_r2s_logistics->getCitiesByStateId($state);

        $response['status'] = 'success';
        $response['cities'] = $cities;

        $this->response->setOutput(json_encode($response));
        return;
    }
    private function settings_validate()
    {
        $postData = $this->request->post['r2s_logistics'];


        if ( ! $postData['customer_code'] || empty($postData['customer_code']) )
        {
            $this->errors['customer_code'] = $this->language->get('error_r2s_logistics_entry_customer_code');
        }

        if ( ! $postData['r2s_weight_rate_class_id'] || empty($postData['r2s_weight_rate_class_id']) )
        {
            $this->errors['r2s_weight_rate_class_id'] = $this->language->get('error_r2s_weight_rate_class_id_required');
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
            $this->errors['order_id'] = $this->language->get('error_r2s_logistics_entry_order_id');
        }

        if (empty($this->request->post['r2s_logistics_entry_recipient_name'])) {
            $this->errors['r2s_logistics_entry_recipient_name'] = $this->language->get('error_r2s_logistics_entry_name');
        }

        if (empty($this->request->post['r2s_logistics_entry_recipient_phone'])) {
            $this->errors['r2s_logistics_entry_recipient_phone'] = $this->language->get('error_r2s_logistics_entry_mobile');
        }

        if (empty($this->request->post['r2s_logistics_entry_consignee_code'])) {
            $this->errors['r2s_logistics_entry_consignee_code'] = $this->language->get('error_r2s_logistics_entry_consignee_code');
        }

        if (empty($this->request->post['r2s_logistics_entry_consignee_code'])) {
            $this->errors['r2s_logistics_entry_consignee_code'] = $this->language->get('error_r2s_logistics_entry_consignee_code');
        }

        if (empty($this->request->post['r2s_logistics_entry_client_code'])) {
            $this->errors['r2s_logistics_entry_client_code'] = $this->language->get('error_r2s_logistics_entry_client_code');
        }

        if (empty($this->request->post['r2s_logistics_entry_recipient_address'])) {
            $this->errors['r2s_logistics_entry_recipient_address'] = $this->language->get('error_r2s_logistics_entry_address');
        }

        if (empty($this->request->post['r2s_logistics_entry_recipient_country']) || strlen($this->request->post['r2s_logistics_entry_recipient_country']) != 2) {
            $this->errors['r2s_logistics_entry_recipient_country'] = $this->language->get('error_r2s_logistics_entry_country');
        }

        if (empty($this->request->post['r2s_logistics_entry_recipient_state'])) {
            $this->errors['r2s_logistics_entry_recipient_state'] = $this->language->get('error_r2s_logistics_recipient_state');
        }

        if (empty($this->request->post['r2s_logistics_entry_recipient_city'])) {
            $this->errors['r2s_logistics_entry_recipient_city'] = $this->language->get('error_r2s_logistics_entry_city');
        }
        if (empty($this->request->post['r2s_logistics_entry_service']) ) {
            $this->errors['r2s_logistics_entry_service'] = $this->language->get('error_r2s_logistics_entry_service');
        }

        if (empty($this->request->post['r2s_logistics_entry_payment_method']) || !($this->request->post['r2s_logistics_entry_payment_method'][0])) {
            $this->errors['r2s_logistics_entry_payment_method'] = $this->language->get('error_r2s_logistics_entry_payment_method');
        }

        if (!empty($this->request->post['r2s_logistics_entry_amount']) && !($this->request->post['r2s_logistics_entry_payment_method'][0]) ) {
            $this->errors['r2s_logistics_entry_amount'] = $this->language->get('error_r2s_logistics_cod_payment_method');
        }

        if (empty($this->request->post['r2s_logistics_entry_package_number']) || (int)$this->request->post['r2s_logistics_entry_package_number'] == 0) {
            $this->errors['r2s_logistics_entry_package_number'] = $this->language->get('error_r2s_logistics_entry_package_number');
        }

        if (empty($this->request->post['r2s_logistics_entry_actual_weight'])) {
            $this->errors['r2s_logistics_entry_actual_weight'] = $this->language->get('error_r2s_logistics_entry_actual_weight');
        }

        if (empty($this->request->post['r2s_logistics_entry_weight_unit'])) {
            $this->errors['r2s_logistics_entry_weight_unit'] = $this->language->get('error_r2s_logistics_entry_weight_unit');
        }

        if ($this->errors && !isset($this->errors['error'])) {
            $this->errors['warning'] = $this->language->get('error_warning');
        }
        return $this->errors ? false : true;
    }

}