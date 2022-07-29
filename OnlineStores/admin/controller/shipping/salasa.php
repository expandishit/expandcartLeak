<?php

use ExpandCart\Foundation\Support\Facades\Url;

class ControllerShippingSalasa extends Controller
{
    protected $errors = [];
    public function index()
    {
        $this->language->load('shipping/salasa');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('mn_setting_menu_shipping'),
            'href'      => $this->url->link('extension/payment', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('payment/payza', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->initializer([
            'geo' => 'localisation/geo_zone',
            'localisation/currency',
            'statuses' => 'localisation/order_status',
            'salasa' => 'shipping/salasa/settings',
            'loclanguage' => 'localisation/language'
        ]);

		$this->data['languages'] = $this->loclanguage->getLanguages();

        $this->data['geo_zones'] = $this->geo->getGeoZones();

        $this->data['order_statuses'] = $this->statuses->getOrderStatuses();

        $this->data['salasa'] = $this->salasa->getSettings();

        $this->template = 'shipping/salasa/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function updateSettings()
    {
        if (
            $this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)
        ) {
            return 'error';
        }

        $data = $this->request->post['salasa'];

        if (!isset($data['geo_zone_id'])) {
            $data['geo_zone_id'] = '0';
        }

        // Set is_shipping to be always zero
        $data['is_shipping'] = '0';

        $this->initializer([
            'salasa' => 'shipping/salasa/settings',
        ]);

        $this->language->load('shipping/salasa');

        if (!$this->salasa->validate($data)) {
            $response['success'] = '0';
            $response['title'] = $this->language->get('error_title');
            $response['errors'] = $this->salasa->getErrors();

            $this->response->setOutput(json_encode($response));

            return;
        }

        $this->load->model('setting/setting');

        $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'salasa', true);

        $this->tracking->updateGuideValue('SHIPPING');

        $this->salasa->updateSettings( $data);

        $response['success_msg'] = $this->language->get('message_settings_updated');

        $response['success'] = '1';

        $this->response->setOutput(json_encode($response));

        return;
    }

    public function createShipment()
    {
        $this->language->load('shipping/salasa');
        $this->document->setTitle($this->language->get('create_shipment_heading_title'));
        
        $this->data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => Url::addPath(['common', 'home'])->format(),
                'separator' => false
            ],
            [
                'text' => $this->language->get('mn_setting_menu_shipping'),
                'href' => Url::addPath(['extension', 'shipping'])->format(),
                'separator' => ' :: '
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => Url::addPath(['shipping', 'salasa'])->format(),
                'separator' => ' :: '
            ],
            [
                'text' => $this->language->get('create_shipment_heading_title'),
                'href' => Url::addPath(['shipping', 'smsa'])->format(),
                'separator' => ' :: '
            ],
        ];
        
        $this->initializer([
            'sale/order',
            'shipping/salasa/shipment',
            'salasa' => 'shipping/salasa/settings',
            'localisation/country',
        ]);

        $orderId = $this->data['orderId'] = $this->request->get['order_id'];

        $orderInfo = $this->order->getOrder($orderId);
        $this->data['orderInfo'] = $orderInfo;   
        $settings = $this->salasa->getSettings();
        
        // $this->load->model('localisation/language');
        // $languages = $this->model_localisation_language->getLanguages();
        // if($languages['en']['language_id'])
        //     $lang_id = $languages['en']['language_id'];

        $this->data['bookingModes'] =
        [
            ['value' => 'COD', 'text'  => 'COD'],
        ];   
        
        if ($this->request->post) {
            if ($this->validate()) {
                $shipment_data = $this->request->post;
                $orderProducts = $this->order->getOrderProducts($shipment_data['orderId']);
                $param_products = array();
                foreach ($orderProducts as $orderProduct) {
                    $param_products[] = array(
                        'Sku' => "BOX1",
                        'Qty' => $orderProduct['quantity'],
                        'SoldPrice' => $orderProduct['price'],
                        'Description' => $orderProduct['name']
                    );
                }
                
                $param = array(
                    "AccountID" => $settings['account_id'],
                    "Key"  => $settings['account_key'],
                    "Warehouse" => $settings['warehouse'],
                    "orders" => array(
                        0 => [ 
                            "orderNum" => $shipment_data['orderId'],
                            "orderShipMethod" => "AutoShipMethod" ,
                            "custEmail" => $shipment_data["salasa_email"],
                            "custPhone" =>$shipment_data["salasa_telephone"],
                            "custFName" => $shipment_data["salasa_first_name"],
                            "custLName" => $shipment_data["salasa_last_name"] ,
                            "custAddress1" => $shipment_data["salasa_address"],
                            "custAddress2" => "" ,
                            "custCity" => $shipment_data["salasa_city"],
                            "custState" => $shipment_data["salasa_state"],
                            "custZip" => "" ,
                            "custCountry" => $shipment_data["salasa_country"],
                            "orderCOD" => $shipment_data["salasa_entry_booking_mode"] == 'COD' ? $shipment_data["salasa_total"]: "",
                            "custBillFName" => "",
                            "custBillLName" => "",
                            "custBillAddress1" => "",
                            "custBillAddress2" => "",
                            "custBillCity" => "",
                            "custBillState" => "",
                            "custBillZip" => "",
                            "custBillCountry" => "" ,
                            "Detaill" => $param_products
                        ]
                    )
                );
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'http://salintegration.shipedge.com/API/Rest/Orders/addOrdersAccount',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS =>json_encode($param),
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json'
                    ),
                ));
                $response = curl_exec($curl);
                $response = json_decode($response); 
                curl_close($curl);
                echo $response;

                if(!empty($response->addListOrders->allow)){// shipment created
                    $resultData = array(
                        "orderId" => $shipment_data['orderId'] ,
                        "salasa_email" => $shipment_data['salasa_email'] ,
                        "salasa_telephone" =>$shipment_data['salasa_telephone'] ,
                        "salasa_first_name" => $shipment_data['salasa_first_name'] ,
                        "salasa_last_name" => $shipment_data['salasa_last_name'] ,
                        "salasa_address" => $shipment_data['salasa_address'] ,
                        "salasa_city" => $shipment_data['salasa_city'] ,
                        "salasa_state" => $shipment_data['salasa_state'] ,
                        "salasa_country" => $shipment_data['salasa_country'] ,
                        "salasa_total" => $shipment_data['salasa_total'] ,
                        "shipment_refrence_id" =>$response->addListOrders->allow[0]->OrderID
                    );
                    
                    $this->shipment->addShipmentDetails($shipment_data['orderId'] , $resultData , 0 );
                    $this->response->setOutput(json_encode([
                        'success' => 1,
                        'success_msg' => $this->language->get('salasa_success_message'),
                        'redirect' => 1,
                        'to' => $this->url->link('shipping/salasa/shipmentDetails', 'order_id=' . $this->request->post['orderId'], 'SSL')->format()
                    ]));
                    return;
                }else if(!empty($response->addListOrders->denied)){//error occured
                    $result_json['success'] = '0';
                    $result_json['errors'] = $response->addListOrders->denied[0]->CommentAPI;
                    $result_json['title'] = $this->language->get('error_title');
                    $this->response->setOutput(json_encode($result_json));
                    return;
                }
            }else{
                $result_json['success'] = '0';
                $result_json['title'] = $this->language->get('error_title');
                $result_json['errors'] = $this->errors;
                $this->response->setOutput(json_encode($result_json));
                return;
            }
        }else{
            $shipment_details = $this->shipment->getShipmentDetails($orderId);
            if(! empty($shipment_details))
            {
                $this->redirect($this->url->link('shipping/salasa/shipmentDetails', 'order_id=' . $orderId, 'SSL'));
            }
        }
 
        $this->template = 'shipping/salasa/shipment/create.expand';
        $this->children = array(
            'common/footer',
            'common/header'
        );
        $this->response->setOutput($this->render_ecwig());
        return;
    }

    private function validate(){
        if(!$this->request->post['salasa_first_name']){
            $this->errors['salasa_first_name_error'] = $this->language->get('entry_first_name_error');
        }
        if(!$this->request->post['salasa_last_name']){
            $this->errors['salasa_last_name_error'] = $this->language->get('entry_last_name_error');
        }
        if(!$this->request->post['salasa_city']){
            $this->errors['salasa_city_error'] = $this->language->get('entry_city_error');
        }
        if(!$this->request->post['salasa_state']){
            $this->errors['salasa_state_error'] = $this->language->get('entry_state_error');
        }
        if(!$this->request->post['salasa_country']){
            $this->errors['salasa_country_error'] = $this->language->get('entry_country_error');
        }
        if(!$this->request->post['salasa_address']){
            $this->errors['salasa_address_error'] = $this->language->get('entry_address_error');
        }
        if($this->request->post['salasa_entry_booking_mode'] == 'COD' && !$this->request->post['salasa_total']){
            $this->errors['salasa_total_error'] = $this->language->get('entry_total_error');
        }
        return $this->errors ? false : true;
    }

    public function shipmentDetails(){
        $this->language->load('shipping/salasa');
        $this->document->setTitle($this->language->get('heading_shipment_salasa'));
        
        $this->data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => Url::addPath(['common', 'home'])->format(),
                'separator' => false
            ],
            [
                'text' => $this->language->get('mn_setting_menu_shipping'),
                'href' => Url::addPath(['extension', 'shipping'])->format(),
                'separator' => ' :: '
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => Url::addPath(['shipping', 'salasa'])->format(),
                'separator' => ' :: '
            ],
            [
                'text' => $this->language->get('heading_shipment_salasa'),
                'href' => Url::addPath(['shipping', 'smsa'])->format(),
                'separator' => ' :: '
            ],
        ];

        $orderId = $this->data['orderId'] = $this->request->get['order_id'];

        $this->initializer([
            'shipping/salasa/shipment',
        ]);

        $orderData = $this->shipment->getShipmentDetails($orderId);
        $shipment_details = json_decode($orderData['details'],true, 512, JSON_UNESCAPED_UNICODE);
        $this->data['shipment_details'] = $shipment_details;
        $this->template = 'shipping/salasa/shipment/details.expand';
        $this->children = array(
            'common/footer',
            'common/header'
        );
        $this->response->setOutput($this->render_ecwig());
        return;
    }
}
