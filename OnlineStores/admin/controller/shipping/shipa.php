<?php

/**
 * ShipA Delivery Integration Dashboard Controller
 * 
 * @author MohamedHassanWD <hassan.mohamed.sf@gmail.com>
 * @copyright 2020 ExpandCart.com
 * @category Shipping Integrations
 */
class ControllerShippingShipa extends Controller
{
    /**
     * Available shipping products
     * For reference
     *
     * @var array
     */
    private $shipa_products = [
        'ATD' => [
            'supported_routes' => ['CD:AE']
        ],
        'ATA' => [
            'supported_routes' => ['CN:SA', 'CN:AE']
        ]
    ];
    
    /**
     * Supported shipping weights
     * For reference
     *
     * @var array
     */
    private $shipa_weights = ['KG','LB'];

    /**
     * Any errors
     *
     * @var string
     */
    private $error = array();

    /**
     * handle Home for shipping method settings admin
     *
     * @return void
     */
    public function index()
    {
        $this->language->load('shipping/shipa');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');
        $this->load->model('shipping/shipa');
        $this->load->model('localisation/weight_class');
        $this->load->model('localisation/tax_class');
        $this->load->model('localisation/geo_zone');

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            if (!$this->validate()) {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));

                return;
            }

            $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'shipa', true);
            
            $this->tracking->updateGuideValue('SHIPPING');

            $this->model_setting_setting->editSetting('shipa', $this->request->post);

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';

            $this->response->setOutput(json_encode($result_json));

            return;
        }

        // ======================== breadcrumbs =========================

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_shipping'),
            'href' => $this->url->link('extension/shipping', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('shipping/shipa', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['links'] = [
            'action' => $this->url->link('shipping/shipa', '', 'SSL'),
            'cancel' => $this->url->link('extension/shipping', '', 'SSL'),
        ];

        $this->data['cancel'] = $this->url->link('shipping/shipa', '', 'SSL');

        // ======================== breadcrumbs =========================

        $this->data['shipa_api_key'] = $this->config->get('shipa_api_key');

        $this->data['shipa_account_number'] = $this->config->get('shipa_account_number');

        $this->data['shipa_iata_code'] = $this->config->get('shipa_iata_code');

        $this->data['shipa_weight_class_id'] = $this->config->get('shipa_weight_class_id');

        $this->data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();

        $this->data['shipa_tax_class_id'] = $this->config->get('shipa_tax_class_id');

        $this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

        $this->data['shipa_geo_zone_id'] = $this->config->get('shipa_geo_zone_id');

        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $this->data['shipa_status'] = $this->config->get('shipa_status');

        $this->data['shipa_test'] = $this->config->get('shipa_test');

        $this->data['shipa_sort_order'] = $this->config->get('shipa_sort_order');

        $this->data['shipa_shipper_name'] = $this->config->get('shipa_shipper_name');

        $this->data['shipa_shipper_email'] = $this->config->get('shipa_shipper_email');

        $this->data['shipa_shipper_address'] = $this->config->get('shipa_shipper_address');

        $this->data['shipa_shipper_country_code'] = $this->config->get('shipa_shipper_country_code');

        $this->data['shipa_shipper_city'] = $this->config->get('shipa_shipper_city');

        $this->data['shipa_shipper_postal_code'] = $this->config->get('shipa_shipper_postal_code');

        $this->data['shipa_shipper_state'] = $this->config->get('shipa_shipper_state');

        $this->data['shipa_shipper_phone'] = $this->config->get('shipa_shipper_phone');

        $this->data['shipa_default_rate'] = $this->config->get('shipa_default_rate');

        $admin_language = $this->config->get('config_admin_language');

        $this->data['contactUrl'] = ($admin_language == 'ar') ? "https://ecommerce.shipa.com/ar/get-in-touch" : "https://ecommerce.shipa.com/get-in-touch";


        $this->template = 'shipping/shipa.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    /**
     * Validate the admin settings
     * 
     * @return bool
     */
    private function validate()
    {

        if (!$this->user->hasPermission('modify', 'shipping/shipa')) {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['shipa_api_key']) {
            $this->error['shipa_api_key'] = $this->language->get('error_api_key');
        }

        if (!$this->request->post['shipa_account_number']) {
            $this->error['shipa_account_number'] = $this->language->get('error_account_number');
        }

        if (!$this->request->post['shipa_iata_code']) {
            $this->error['shipa_iata_code'] = $this->language->get('error_iata_code');
        }

        #########   Shipper validation #############

        if (!$this->request->post['shipa_shipper_name']) {
            $this->error['shipa_shipper_name'] = $this->language->get('error_shipper_name');
        }

        if (!$this->request->post['shipa_shipper_email']) {
            $this->error['shipa_shipper_email'] = $this->language->get('error_shipper_email');
        }

        if (!$this->request->post['shipa_shipper_address']) {
            $this->error['shipa_shipper_address'] = $this->language->get('error_shipper_address');
        }

        if (!$this->request->post['shipa_shipper_country_code']) {
            $this->error['shipa_shipper_country_code'] = $this->language->get('error_shipper_country_code');
        }

        if (!$this->request->post['shipa_shipper_city']) {
            $this->error['shipa_shipper_city'] = $this->language->get('error_shipper_city');
        }

        if (!$this->request->post['shipa_shipper_postal_code']) {
            $this->error['shipa_shipper_postal_code'] = $this->language->get('error_shipper_postal_code');
        }

        if (!$this->request->post['shipa_shipper_state']) {
            $this->error['shipa_shipper_state'] = $this->language->get('error_shipper_state');
        }

        if (!$this->request->post['shipa_shipper_phone']) {
            $this->error['shipa_shipper_phone'] = $this->language->get('error_shipper_phone');
        }

        if ($this->error && !isset($this->error['error'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return $this->error ? false : true;
    }

    /**
     * Create a new parecl
     *
     * @return void
     */
    public function shipa_create_shipment()
    {
        //Load language & models
        $this->language->load('shipping/shipa');
        $this->load->model("shipping/shipa");
        $this->load->model("sale/order");
        $this->load->model("localisation/weight_class");

        //Get order information
        $order_id = $this->request->get['order_id'];
        $order = $this->model_sale_order->getOrder($order_id);
        $order_products = $this->model_sale_order->getOrderProducts($order_id);

        $this->data['order_id'] = $order_id;
        $this->data['order'] = $order;
        $this->data['weight_total'] = 0;

        foreach ($order_products as $product) {
            //Update total weight
            $this->data['weight_total'] += $product['weight'];
        }

        //Handle shipment creation
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            //Validate data
            if (!$this->validate_shipment()) {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));

                return;
            }

            //Get order information
            $order_id = $this->request->post['order_id'];
            $order = $this->model_sale_order->getOrder($order_id);
            $order_products = $this->model_sale_order->getOrderProducts($order_id);
            
            //Create parcel number automatically
            $shipa_data["number"] = [
                "scheme" => "Default",
                "value" => ""
            ];

            //Set parcel reference to order number
            // $shipa_data["references"] = [
            //     [
            //         "type" => "order_number",
            //         "value" => $order_id
            //     ]
            // ];

            //Set shipping product 
            $shipa_data['product'] = $this->request->post['shipping_product'];

            //One or more value-added (extra) shipping services for the parcel:
            //- COD—Cash on delivery (consignee pays the cost of goods and shipping when collecting the parcel).
            //- INS—Insurance coverage for cases when a parcel is damaged or lost.
            if($this->request->post['services_cod'] == 1 || $this->request->post['services_ins'] == 1){
                if($this->request->post['services_cod'] == 1){
                    $shipa_data['services'][] = "COD";
                    $shipa_data['cod'] = [
                        "amount" => $this->request->post['amount'],
                        "currency" => $this->request->post['currency']
                    ];

                }
                if($this->request->post['services_ins'] == 1){
                    $shipa_data['services'][] = "INS";
                }
            }



            //1. Shipper
            $shipa_data["shipper"] = [
                "name" => $this->config->get('shipa_shipper_name'),
                "phones" => [
                    $this->config->get('shipa_shipper_phone')
                ],
                "emails" => [
                    $this->config->get('shipa_shipper_email')
                ],
                "address" => [
                    "country" => $this->config->get('shipa_shipper_country_code'),
                    "city" => $this->config->get('shipa_shipper_city'),
                    "state" => $this->config->get('shipa_shipper_state'),
                    "street" => [
                        $this->config->get('shipa_shipper_address')
                    ],
                    "post_code" => $this->config->get('shipa_shipper_postal_code'),
                ],
            ];

            //2. Consignee
            $shipa_data["consignee"] = [
                "name" => $this->request->post['customer_name'],
                "phones" => [
                    $this->request->post['customer_phone']
                ],
                "emails" => [
                    $this->request->post['customer_email']
                ],
                "address" => [
                    "country" => $this->request->post['customer_country'],
                    "city" => $this->request->post['customer_city'],
                    "state" => $this->request->post['customer_state'],
                    "street" => [
                        $this->request->post['customer_address'],
                    ],
                    "post_code" => $this->request->post['customer_postcode'],
                ],
            ];

            //03. Account
            $shipa_data["account"] = [
                "number" => $this->config->get('shipa_account_number'),
                "entity" => $this->config->get('shipa_iata_code'),
            ];

            //04. Weight
            $shipa_data["weight"] = [
                "value" => $this->request->post['weight_total'],
                "unit" => $this->request->post['weight_unit'],
            ];

            //05. Dimensions
            $shipa_data["dimensions"] = [
                "length" => $this->request->post['length'],
                "width" => $this->request->post['width'],
                "height" => $this->request->post['height'],
                "unit" => $this->request->post['dimensions_unit'],
            ];

            //06. Items
            if($this->request->post['include_items'] == 1){
                $shipa_data['items'] = [];

                foreach ($order_products as $product) {
                    //Update total weight
                    $shipa_data['items'][] = [
                        "weight" => [
                            "value" => $product['weight'] < 1 ? "1" : $product['weight'],
                            "unit" => $this->model_localisation_weight_class->getWeightClass($product['weight_class_id'])['unit']
                        ],
                        "customs_value" => [
                            "amount" => $product['price'],
                            "currency" => $order['currency_code']
                        ],
                        "origin_country" => $this->config->get('shipa_shipper_country_code'),
                        "description" => $product['name'],
                        "quantity" => $product['quantity'],
                    ];
                }
            }

            //Send request to ShipA url
            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://sb.ecommerceapi.shipa.com/api/Parcels",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($shipa_data),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: ApiKey ".$this->config->get('shipa_api_key'),
                "Content-Type: text/plain"
                ),
            ));

            $response = curl_exec($curl);
            $response_info = curl_getinfo($curl);
            curl_close($curl);
            
            $response = json_decode($response);

            //check if http request failed
            if($response_info['http_code'] !== 200){
                $this->error['warning'] = $this->language->get('error_bad_request');
                $this->error['bad_request'] = $response->title;
                
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));
                return;
            }
            
            //check for bad routes error
            if(is_array($response) && isset($response[0]->code) && $response[0]->code == 69){
                $this->error['error_code_69'] = $this->language->get('error_code_69');

                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));

                return;
            }

            //If request success
            if(isset($response->scheme) && isset($response->value)){
                $data['order_id'] = $order_id;
                $data['response'] = $response;

                $this->model_shipping_shipa->create_shipment($data);
            }
            
            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_success_create');
            $result_json['redirect'] = 1;
            $result_json['to'] = $this->url->link('sale/order/info','order_id='.$order_id,true)->format();
            
            $this->response->setOutput(json_encode($result_json));

            return;
        }

        //Check if the order already has a ShipA parcel
        $this->data['shipments'] = $this->model_shipping_shipa->getOrderShipments($order_id);
        foreach($this->data['shipments'] as $key=>$val){
            $this->data['shipments'][$key]['details_decoded'] = json_decode($val['details'],1);
        }

        $this->template = 'shipping/shipa/create_shipment.expand';
        $this->children = array(
            'common/footer',
            'common/header'
        );
        $this->response->setOutput($this->render_ecwig());
    }

    function printLabel()
    {

        $parcel_id = $this->request->get['parcel_id'];
        //Send request to ShipA url
        $curl = curl_init();

        $shipa_data = ["output"=>"url",
            "template_name"=> "standard"
        ];
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://sb.ecommerceapi.shipa.com/api/Parcels/"."default_".$parcel_id."/Label",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($shipa_data),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: ApiKey ".$this->config->get('shipa_api_key'),
                "Content-Type: text/plain"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $this->response->redirect($response);
        return;

    }

    /**
     * Validate the user inputs for creating parcel
     * 
     * @return bool
     */
    private function validate_shipment()
    {

        if (!$this->request->post['shipping_product']) {
            $this->error['shipping_product'] = $this->language->get('error_shipping_product');
        }

        if (!$this->request->post['weight_total']) {
            $this->error['weight_total'] = $this->language->get('error_weight_total');
        }

        if (!$this->request->post['weight_unit']) {
            $this->error['weight_unit'] = $this->language->get('error_weight_unit');
        }

        if (!$this->request->post['length']) {
            $this->error['length'] = $this->language->get('error_length');
        }

        if (!$this->request->post['width']) {
            $this->error['width'] = $this->language->get('error_width');
        }

        if (!$this->request->post['height']) {
            $this->error['height'] = $this->language->get('error_height');
        }

        if (!$this->request->post['dimensions_unit']) {
            $this->error['dimensions_unit'] = $this->language->get('error_dimensions_unit');
        }

        if (!$this->request->post['customer_name']) {
            $this->error['customer_name'] = $this->language->get('error_customer_name');
        }

        if (!$this->request->post['customer_phone']) {
            $this->error['customer_phone'] = $this->language->get('error_customer_phone');
        }

        if (!$this->request->post['customer_email']) {
            $this->error['customer_email'] = $this->language->get('error_customer_email');
        }

        if (!$this->request->post['customer_country']) {
            $this->error['customer_country'] = $this->language->get('error_customer_country');
        }

        if (!$this->request->post['customer_state']) {
            $this->error['customer_state'] = $this->language->get('error_customer_state');
        }

        if (!$this->request->post['customer_city']) {
            $this->error['customer_city'] = $this->language->get('error_customer_city');
        }

        if (!$this->request->post['customer_address']) {
            $this->error['customer_address'] = $this->language->get('error_customer_address');
        }

        if (!$this->request->post['customer_postcode']) {
            $this->error['customer_postcode'] = $this->language->get('error_customer_postcode');
        }

        if ($this->request->post['services_cod'] != 0 && !$this->request->post['amount']) {
            $this->error['amount'] = $this->language->get('error_amount');
        }

        if ($this->request->post['services_cod'] != 0 && !$this->request->post['currency']) {
            $this->error['currency'] = $this->language->get('error_currency');
        }

        if ($this->error && !isset($this->error['error'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return $this->error ? false : true;
    }

    /**
     * View parcel track
     *
     * @return void
     */
    public function shipment()
    {
        $parcel_id = $this->request->get['parcel_id'];

        $this->language->load('shipping/shipa');

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://sb.ecommerceapi.shipa.com/api/Parcels/Track/".$parcel_id,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "Authorization: ApiKey ".$this->config->get('shipa_api_key')
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        if(count(json_decode($response,1)) < 2){
            $this->data['error'] = $this->language->get('error_parcel_not_found');
        }else{
            $this->data['response'] = json_decode($response);
        }

        $this->template = 'shipping/shipa/shipment.expand';
        $this->children = array(
            'common/footer',
            'common/header'
        );
        $this->response->setOutput($this->render_ecwig());
    }
}
