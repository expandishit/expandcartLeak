<?php


use ExpandCart\Foundation\Support\Facades\Url;

class ControllerShippingtookan extends Controller
{
    protected $errors = [];
    // define test url
    private $testUrl = "https://private-anon-06469dfd21-tookanapi.apiary-mock.com";
    // define production url
    private $productionUrl = "https://api.tookanapp.com";


    public function index()
    {

        $this->language->load('shipping/tookan');

        $this->document->setTitle($this->language->get('heading_title'));

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
                'text' => $this->language->get('heading_title_tookan'),
                'href' => Url::addPath(['shipping', 'tookan'])->format(),
                'separator' => ' :: '
            ],
        ];

        $this->data['tookan_api_key'] = $this->config->get('tookan_api_key');

        $this->data['tookan_map_api_key'] = $this->config->get('tookan_map_api_key');

        $this->data['tookan_shipping_cost'] = $this->config->get('tookan_shipping_cost');

        $this->data['tookan_status'] = $this->config->get('tookan_status');

        $this->data['tookan_debug_mode_status'] = $this->config->get('tookan_debug_mode_status');

        $this->data['tookan_geo_zone_id'] = $this->config->get('tookan_geo_zone_id');

        $this->load->model('localisation/geo_zone');

        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();


        $this->template = 'shipping/tookan/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function updateSettings()
    {
        // load Language File
        $this->language->load('shipping/tookan');

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
            'shipping/tookan'
        ]);

        $tookan = $this->request->post;

        if (!$this->settings_validate()) {
            $result_json['success'] = '0';
            $result_json['errors'] = $this->errors;

            $this->response->setOutput(json_encode($result_json));

            return;
        }

        $this->load->model('setting/setting');

        $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'tookan', true);

        $this->model_setting_setting->editSetting('tookan', $this->request->post);

        
            $this->tracking->updateGuideValue('SHIPPING');

        $this->response->setOutput(json_encode([
            'success' => 1,
            'success_msg' => $this->language->get('text_settings_success')
        ]));
        return;
    }

    /**
     * This function responsable for Fill the Form with data
     */
    public function createShipment()
    {
        // get order id
        $orderId = $this->request->get['order_id'];

        $this->language->load('shipping/tookan');
        $this->load->model("shipping/tookan");
        $this->load->model("sale/order");

        $orderData = $this->model_sale_order->getOrder($this->request->get['order_id']);

        $this->data['customer_username'] = $orderData['shipping_firstname'] . $orderData['shipping_lastname'];
        $this->data['customer_email'] = $orderData['email'];
        $this->data['customer_phone'] = $orderData['telephone'];
        $this->data['customer_address'] = $orderData['shipping_address_1'];
        $this->data['customer_country'] = $orderData['shipping_country'];
        $this->data['customer_cod'] = $orderData['total'];
        $this->data['order_id'] = $this->request->get['order_id'];
        $this->data['job_pickup_phone'] = $this->config->get('config_telephone');
        $this->data['teams'] = $this->getAllTeams();
        $this->data['agents'] = $this->getAllAgents();
        $shipmentDetails = $this->model_shipping_tookan->getShipmentDetails($orderId);
        $this->data['trackingLink'] = $shipmentDetails ? json_decode($shipmentDetails['details'])->tracking_link : "";
        $this->data['job_id'] = $shipmentDetails ? json_decode($shipmentDetails['details'])->job_id : "";
        $this->data['isShipping'] = $shipmentDetails ? true : false;
        $this->data['tookan_map_api_key'] = $this->config->get('tookan_map_api_key');
        $this->data['job_description'] = $orderData['comment'];

        $shippingAddressLat = 0;
        $shippingAddressLng = 0;
        
        if (!empty($orderData['shipping_address_location'])) {
            $locationToArray = explode(',', $orderData['shipping_address_location']);
            $shippingAddressLat = is_numeric($locationToArray[0]) ? $locationToArray[0] : 0;
            $shippingAddressLng = is_numeric($locationToArray[1]) ? $locationToArray[1] : 0;
        }

        $this->data['shipping_address_location'] = ['lat' => $shippingAddressLat, 'lng' => $shippingAddressLng];

        $this->template = 'shipping/tookan/shipment/create.expand';
        $this->children = array(
            'common/footer',
            'common/header'
        );
        $this->response->setOutput($this->render_ecwig());

    }

    /**
     * THIS FUNCTION RESPONSABLE FOR CREATING PICKUP TASK
     */
    public function createPickupTask()
    {
        /**
         * PREPARE TOOKAN ENVIRONMENT
         */
        $testingMode = $this->config->get('tookan_debug_mode_status');
        if ($testingMode) {
            $curl_url = $this->testUrl . "/v2/create_task";
        } else {
            $curl_url = $this->productionUrl . "/v2/create_task";
        }

        $api_key = $this->config->get('tookan_api_key');

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if ($this->validate()) {
                $this->load->model('setting/setting');
                $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'tookan', true);

                // PREPATE CURL DATA
                $curl_data = array(
                    array(
                        "api_key" => $this->config->get('tookan_api_key'),
                        "order_id" => $this->request->post['order_id'],
                        "amount" => (float)$this->request->post['tookan_entry_amount'],
                        "job_description" => $this->request->post['tookan_entry_description'],
                        "job_pickup_phone" => $this->request->post['tookan_entry_pickup_phone'],
                        "job_pickup_name" => $this->request->post['tookan_entry_pickup_name'],
                        "job_pickup_email" => $this->request->post['tookan_entry_pickup_email'],
                        "job_pickup_address" => $this->request->post['tookan_entry_pickup_address'],
                        "job_pickup_latitude" => $this->request->post['tookan_entry_pickup_latitude'],
                        "job_pickup_longitude" => $this->request->post['tookan_entry_pickup_longitude'],
                        "job_pickup_datetime" => $this->request->post['tookan_entry_pickup_datetime'],
                        "has_pickup" => $this->request->post['tookan_entry_has_pickup'],
                        "has_delivery" => $this->request->post['tookan_entry_has_delivery'],
                        "layout_type" => $this->request->post['tookan_entry_layout_type'],
                        "tracking_link" => $this->request->post['tookan_entry_tracking_link'],
                        "timezone" => $this->request->post['tookan_entry_timezone'],
                        "pickup_custom_field_template" => $this->request->post['tookan_entry_pickup_custom_field_template'],
                        "pickup_meta_data" => $this->request->post['tookan_entry_pickup_meta_data'],
                        "team_id" => $this->request->post['tookan_entry_team_id'],
                        "auto_assignment" => $this->request->post['tookan_entry_auto_assignment'],
                        "fleet_id" => $this->request->post['tookan_entry_fleet_id'],
                        "p_ref_images" => $this->request->post['tookan_entry_p_ref_images'],
                        "notify" => $this->request->post['tookan_entry_notify'],
                        "geofence" => $this->request->post['tookan_entry_geofence'],
                        "tags" => $this->request->post['tookan_entry_tags'],
                    )
                );

                // SEND CURL REQUEST
                $result = $this->sendCurlRequest($curl_url, json_encode($curl_data));

                if (is_array($result['response']) && $result['response'][0]->code == 0) {
                    $curl_data = array_shift($curl_data);
                    $resultData['orderData'] = [
                        "amount" => $curl_data['amount'],
                        "paymentMethod" => $curl_data['paymentMethod'],
                        "description" => $curl_data['description'],
                        "typeDelivery" => $curl_data['typeDelivery'],
                        "goodsValue" => $curl_data['goodsValue']
                    ];
                    $resultData['recipient'] = [
                        "city" => $curl_data['recipient']['city'],
                        "phone" => $curl_data['recipient']['phone'],
                        "address" => $curl_data['recipient']['address'],
                        "name" => $curl_data['recipient']['name'],
                    ];
                    $resultData['response'] = [
                        "reference" => $result['response'][0]->deliveryInfo->reference,
                        "codeStatus" => $result['response'][0]->deliveryInfo->codeStatus,
                        "startTime" => $result['response'][0]->deliveryInfo->startTime,
                        "endTime" => $result['response'][0]->deliveryInfo->endTime,
                    ];
                    $this->load->model("shipping/tookan");
                    $this->model_shipping_tookan->addShipmentDetails($this->request->post['order_id'], $resultData, "pre-pair");
                    $this->response->setOutput(json_encode([
                        'success' => 1,
                        'success_msg' => $this->language->get('tookan_success_message'),
                        'redirect' => 1,
                        'to' => $this->url->link('shipping/tookan/shipmentDetails', 'order_id=' . $this->request->post['order_id'], 'SSL')->format()
                    ]));
                } else {
                    $result_json['success'] = '0';
                    $result_json['errors'] = [$result['response']->fault->faultstring];
                    $result_json['errors']['warning'] = $this->language->get('tookan_error_warning');
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

            $this->load->model("shipping/tookan");
            // get shipping details for check if customer make shipment before
            $shipping_details = $this->model_shipping_tookan->getShipmentDetails($orderId);

            if (count($shipping_details) > 0) {
                $this->redirect($this->url->link('shipping/tookan/shipmentDetails', 'order_id=' . $orderId, 'SSL'));
            }

            $orderData = $this->model_sale_order->getOrder($this->request->get['order_id']);

            $this->data['tookan_entry_name'] = $orderData['shipping_firstname'] . $orderData['shipping_lastname'];
            $this->data['tookan_entry_email'] = $orderData['email'];
            $this->data['tookan_entry_mobile'] = $orderData['telephone'];
            $this->data['tookan_entry_address'] = $orderData['shipping_address_1'];
            $this->data['tookan_city'] = $orderData['shipping_country'];
            $this->data['tookan_entry_cod'] = $orderData['total'];
            $this->data['order_id'] = $this->request->get['order_id'];

            $this->template = 'shipping/tookan/shipment/create.expand';
            $this->children = array(
                'common/footer',
                'common/header'
            );
            $this->response->setOutput($this->render_ecwig());
            return;
        }
    }


    public function createDeliveryTask()
    {
        $this->language->load('shipping/tookan');
        // get data from setting table
        $testingMode = $this->config->get('tookan_debug_mode_status');
        // get api key
        $api_key = $this->config->get('tookan_api_key');
        // check Test Mode
        if ($testingMode) {
            $curl_url = $this->testUrl . "/v2/create_task";
        } else {
            $curl_url = $this->productionUrl . "/v2/create_task";
        }


        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            // REQUEST VALID
            if ($this->validate()) {
                $this->load->model('setting/setting');
                $this->load->model('sale/order');

                $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'tookan', true);
                $orderData  = $this->model_sale_order->getOrderProducts($this->request->post['order_id']);

                $orderImages = array();
                foreach ($orderData as $result) {
                    $orderImages[] = \Filesystem::getUrl('image/' . $result['image']);
                }

                // GET THE TIME ZONE OFFSET
                $currentTimezone = timezone_open($this->config->get("config_timezone"));
                $timeZoneUTC = date_create("now", timezone_open("UTC"));
                $timeOffset = timezone_offset_get($currentTimezone, $timeZoneUTC) / 60;

                // PREPARE CURL DATA
                $curl_data = array(
                    "api_key" => $api_key,
                    "order_id" => $this->request->post['order_id'],
                    "job_description" => $this->request->post['job_description'],
                    "job_pickup_phone" => $this->request->post['job_pickup_phone'],
                    "customer_email" => $this->request->post['customer_email'],
                    "customer_username" => $this->request->post['customer_username'],
                    "customer_phone" => $this->request->post['customer_phone'],
                    "customer_address" => $this->request->post['customer_address'],
                    "job_delivery_datetime" => $this->request->post['job_delivery_datetime'],
                    "custom_field_template" => $this->request->post['custom_field_template'],
                    "team_id" => $this->request->post['team_id'],
                    "auto_assignment" => $this->request->post['auto_assignment'],
                    "latitude" => $this->request->post['latitude'],
                    "longitude" => $this->request->post['longitude'],
                    "has_pickup" => 0,
                    "has_delivery" => 1,
                    "layout_type" => 0,
                    "tracking_link" => 1,
                    "timezone" => $timeOffset,
                    "fleet_id" => $this->request->post['fleet_id'],
                    "meta_data" => [
                        [
                            "label" => 'COD',
                            "data" => $this->request->post['customer_cod']
                        ],
                        [
                            "label" => 'DeliveryCountry',
                            "data" => $this->request->post['customer_country']
                        ]
                    ],
                    "ref_images" => $orderImages,
                    "notify" => $this->request->post['notify'],
                    "tags" => $this->request->post['tags'],
                    "geofence" => $this->request->post['geofence']
                );

                // SEND CURL REQUEST
                $result = $this->sendCurlRequest($curl_url, json_encode($curl_data));

                // REQUEST SUCCESS
                if ($result['response']->status == 200) {
                    // ADD SHIPPMENT HISTORY
                    $this->load->model("shipping/tookan");
                    $this->model_shipping_tookan->addShipmentDetails($this->request->post['order_id'], $result['response']->data, "pre-pair");

                    //RETURN SUCCESS
                    $this->response->setOutput(json_encode([
                        'success' => 1,
                        'success_msg' => $this->language->get('success_message'),
                        'redirect' => 1,
                        'to' => $this->url->link('shipping/tookan/createShipment', 'order_id=' . $this->request->post['order_id'], 'SSL')->format()
                    ]));

                } else { // REQUEST FAILED
                    $result_json['success'] = '0';
                    $result_json['errors'] = [$result['response']->message];
                    $result_json['errors']['warning'] = [$result['response']->status];
                    $this->response->setOutput(json_encode($result_json));
                }

            } else { // REQUEST NOT VALID
                $result_json['success'] = '0';
                $result_json['errors'] = $this->errors;
                $this->response->setOutput(json_encode($result_json));
                return;
            }

        } else { // THE REUQEST IS GET

            //GET ORDER SHIPMENT'S DETAILS
            $orderId = $this->request->get['order_id'];
            $this->load->model("shipping/tookan");
            $shipping_details = $this->model_shipping_tookan->getShipmentDetails($orderId);

            if (count($shipping_details) > 0) {
                $this->redirect($this->url->link('shipping/tookan/shipmentDetails', 'order_id=' . $orderId, 'SSL'));
            }

            $orderData = $this->model_sale_order->getOrder($this->request->get['order_id']);
            $this->data['tookan_entry_name'] = $orderData['shipping_firstname'] . $orderData['shipping_lastname'];
            $this->data['tookan_entry_email'] = $orderData['email'];
            $this->data['tookan_entry_mobile'] = $orderData['telephone'];
            $this->data['tookan_entry_address'] = $orderData['shipping_address_1'];
            $this->data['tookan_city'] = $orderData['shipping_country'];
            $this->data['tookan_entry_cod'] = $orderData['total'];
            $this->data['order_id'] = $this->request->get['order_id'];

            $this->template = 'shipping/tookan/shipment/create.expand';
            $this->children = array(
                'common/footer',
                'common/header'
            );
            $this->response->setOutput($this->render_ecwig());
            return;
        }
    }

    /**
     *  GET ALL TOOKAN TEAMS
     * @return array|bool
     */
    public function getAllTeams()
    {

        /**
         *  PREPARE TOOKAN ENVIRONMENT
         */
        $testingMode = $this->config->get('tookan_debug_mode_status');
        if ($testingMode) {
            $curl_url = $this->testUrl . "/v2/view_all_team_only";
        } else {
            $curl_url = $this->productionUrl . "/v2/view_all_team_only";
        }

        $api_key = $this->config->get('tookan_api_key');

        // PREPARE CURL DATA
        $curl_data = array(
            "api_key" => $api_key
        );

        //SEND CURL REQUEST
        $result = $this->sendCurlRequest($curl_url, json_encode($curl_data));

        $teams = array();
        if ($result['response']->status == 200) {
            // GET TEAMS DATA [id,name]
            foreach ($result['response']->data as $team) {
                $teams[] = [
                    "id" => $team->team_id,
                    "name" => $team->team_name,
                ];
            }
            return $teams;
        }
        return false;
    }

    /**
     * CANCEL CREATED TOOKAN TASK
     * @return bool
     */
    public function cancelTask()
    {

        /**
         * PREPARE TOOKAN ENVIRONMENT
         */
        $testingMode = $this->config->get('tookan_debug_mode_status');
        if ($testingMode) {
            $curl_url = $this->testUrl . "/v2/cancel_task";
        } else {
            $curl_url = $this->productionUrl . "/v2/cancel_task";
        }

        //PREPARE CURL DATA
        $api_key = $this->config->get('tookan_api_key');
        $curl_data = array(
            "api_key" => $api_key,
            "job_id" => $this->request->post['job_id'],
            "job_status" => 9
        );

        //SEND CURL REQUEST
        $result = $this->sendCurlRequest($curl_url, json_encode($curl_data));
        $orderId = $this->request->post['order_id'];

        // TASK DELETED SUCCESSFULLY
        if ($result['response']->status == 200) {
            // DELETE SHIPMENT DETAILS
            $this->load->model("shipping/tookan");
            $this->model_shipping_tookan->deleteShipment($orderId);
            $this->response->redirect($this->url->link('sale/order/info', 'order_id=' . $orderId, 'SSL'));
        } else { // DELETING TASK FAILED
            $result_json['success'] = '0';
            $result_json['errors'] = [$result['response']->message];
            $result_json['errors']['warning'] = [$result['response']->status];
            $this->response->setOutput(json_encode($result_json));
        }
        return false;
    }

    /**
     * GET ALL TOOKAN AGENTS
     *  CAPATIVE | FREELANCER
     * @return array|bool
     */
    public function getAllAgents()
    {
        /**
         * PREPARE TOOKAN ENVIRONMENT
         */
        $testingMode = $this->config->get('tookan_debug_mode_status');
        if ($testingMode) {
            $curl_url = $this->testUrl . "/v2/get_all_fleets";
        } else {
            $curl_url = $this->productionUrl . "/v2/get_all_fleets";
        }

        // PREPARE CURL DATA
        $api_key = $this->config->get('tookan_api_key');
        $agents = array();

        // GET CAPATIVE AGENTS
        $curl_data = array(
            "api_key" => $api_key,
            "fleet_type" => 1
        );
        $capativeAgents = $this->sendCurlRequest($curl_url, json_encode($curl_data));

        // GET FREELANCER AGENTS
        $curl_data = array(
            "api_key" => $api_key,
            "fleet_type" => 2
        );
        $freeLancerAgents = $this->sendCurlRequest($curl_url, json_encode($curl_data));

        // MERGE THE AGENTS DATA
        $result = array_merge_recursive($capativeAgents, $freeLancerAgents);
        if ($result['response']['status'][0] == 200) {
            //RETURN AGENT DATA [id,name,available,type]
            foreach ($result['response']['data'] as $agent) {
                $agents[] = [
                    "id" => $agent->fleet_id,
                    "name" => $agent->username,
                    "available" => $agent->is_available,
                    "type" => $agent->fleet_type == 1 ? 'capative' : 'freelancer',
                ];
            }

            return $agents;
        }
        return false;
    }

    private function sendCurlRequest($_url, $data)
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
            CURLOPT_CUSTOMREQUEST => 'POST',

        );
        curl_setopt_array($curl, $options);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json"
        ));

        $response = curl_exec($curl);
        $response = json_decode($response);
        curl_close($curl);
        $result = ['response' => $response,
            'data' => $data
        ];
        return $result;
    }

    private function settings_validate()
    {
        $postData = $this->request->post;

        if (!$postData['tookan_api_key'] || empty($postData['tookan_api_key'])) {
            $this->errors['tookan_api_key'] = $this->language->get('error_api_key_required');
        }

        if (!$postData['tookan_shipping_cost'] || empty($postData['tookan_shipping_cost'])) {
            $this->errors['tookan_shipping_cost'] = $this->language->get('error_shipping_cost_required');
        }

        if (!$postData['tookan_map_api_key'] || empty($postData['tookan_map_api_key'])) {
            $this->errors['tookan_map_api_key'] = $this->language->get('error_map_api_key_required');
        }

        if ($this->errors && !isset($this->errors['error'])) {
            $this->errors['warning'] = $this->language->get('error_warning');
        }

        return $this->errors ? false : true;
    }

    /**
     * VALIDATE TOOKAN REQUEST
     * @return bool
     */
    private function validate()
    {
        $this->language->load('shipping/tookan');

        if (!$this->request->post['order_id']) {
            $this->errors['order_id'] = $this->language->get('error_tookan_entry_order_id');
        }

        if (!$this->request->post['job_description']) {
            $this->errors['order_id'] = $this->language->get('error_tookan_entry_job_description');
        }
        if (!$this->request->post['job_pickup_phone']) {
            $this->errors['job_pickup_phone'] = $this->language->get('error_tookan_entry_job_pickup_phone');
        }
        if (!$this->request->post['customer_phone']) {
            $this->errors['customer_phone'] = $this->language->get('error_tookan_entry_customer_phone');
        }
        if (!$this->request->post['customer_address']) {
            $this->errors['customer_address'] = $this->language->get('error_tookan_entry_customer_address');
        }
        if (!$this->request->post['job_delivery_datetime']) {
            $this->errors['job_delivery_datetime'] = $this->language->get('error_tookan_entry_delivery_datetime');
        }
        if (!$this->request->post['team_id']) {
            $this->errors['team_id'] = $this->language->get('error_tookan_entry_team_id');
        }
        if (!$this->request->post['fleet_id']) {
            $this->errors['fleet_id'] = $this->language->get('error_tookan_entry_fleet_id');
        }
        if (!$this->request->post['latitude'] || !$this->request->post['longitude']) {
            $this->errors['map'] = $this->language->get('error_tookan_entry_map');
        }

        if ($this->errors && !isset($this->errors['error'])) {
            $this->errors['warning'] = $this->language->get('error_warning');
        }
        return $this->errors ? false : true;
    }


    /**
     * GET TOOKAN ERRORS
     * @param $code
     * @return mixed
     */
    public function getErrorMessage($code)
    {

        // define error codes
        $error_codes = array(
            "-1" => "UNKOWN TOOKAN ERROR",
            "100" => "PARAMETER_MISSING",
            "101" => "INVALID_KEY",
            "200" => "ACTION_COMPLETE",
            "201" => "SHOW_ERROR_MESSAGE",
            "404" => "ERROR_IN_EXECUTION"
        );
        if (array_key_exists($code, $error_codes))
            return $error_codes[$code];

        return $error_codes[-1];
    }

    /**
     * GET TOOKAN TASK STATUSES
     * @param $status_id
     * @return mixed
     */
    public function getTaskStatus($status_id)
    {
        $task_statuses = array(
            -1 => array(
                "text" => "Unkown",
                "description" => "The status is Unkown"
            ),
            0 => array(
                "text" => "Assigned",
                "description" => "The task has been assigned to a agent."
            ),
            1 => array(
                "text" => "Started",
                "description" => "The task has been started and the agent is on the way. This will appear as a light-blue pin on the map and as a rectangle in the assigned category on the left."
            ),
            2 => array(
                "text" => "Successful",
                "description" => "The task has been completed successfully and will appear as a green pin on the map and as a rectangle in the completed category on the left."
            ),
            3 => array(
                "text" => "Failed",
                "description" => "The task has been completed unsuccessfully and will appear as red pin on the map and as a rectangle in the completed category on the left."
            ),
            4 => array(
                "text" => "InProgress",
                "description" => "The task is being performed and the agent has reached the destination. This will appear as a dark-blue pin on the map and as a rectangle in the assigned category on the left."
            ),
            6 => array(
                "text" => "Unassigned",
                "description" => "The task has not been assigned to any agent and will appear as a grey pin on the map and as a rectangle in the unassigned category on the left."
            ),
            7 => array(
                "text" => "Accepted",
                "description" => "The task has been accepted by the agent which is assigned to him."
            ),
            8 => array(
                "text" => "Decline",
                "description" => "The task has been declined by the agent which is assigned to him."
            ),
            9 => array(
                "text" => "Cancel",
                "description" => "The task has been cancelled by the agent which is accepted by him."
            ),
            10 => array(
                "text" => "Deleted",
                "description" => "When the task is deleted from the Dashboard."
            )
        );
        if (array_key_exists($status_id, $task_statuses))
            return $task_statuses[$status_id];

        return $task_statuses[-1];
    }
}