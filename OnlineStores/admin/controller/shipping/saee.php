<?php

use ExpandCart\Foundation\Support\Facades\Url;

class ControllerShippingSaee extends Controller
{
    protected $errors = [];

    public function index()
    {
        $this->language->load('shipping/saee');

        $this->document->setTitle($this->language->get('heading_title_saee'));

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
                'text' => $this->language->get('heading_title_saee'),
                'href' => Url::addPath(['shipping', 'smsa'])->format(),
                'separator' => ' :: '
            ],
        ];

        $this->initializer([
            'localisation/geo_zone',
            'shipping/saee'
        ]);

        $this->data['data'] = $this->saee->getSettings();

        $this->data['geo_zones'] = $this->geo_zone->getGeoZones();

        $this->template = 'shipping/saee.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function updateSettings()
    {
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
            'shipping/saee'
        ]);

        $saee = $this->request->post['saee'];

        if ($this->saee->validate($saee) == false) {
            $this->response->setOutput(json_encode([
                'success' => 0,
                'errors' => $this->saee->getErrors()
            ]));
            return;
        }

        $this->load->model('setting/setting');

        $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'saee', true);

        
            $this->tracking->updateGuideValue('SHIPPING');

        $this->saee->updateSettings($saee);

        $this->response->setOutput(json_encode([
            'success' => 1,
            'success_msg' => $this->language->get('text_settings_success')
        ]));
        return;
    }


    public function createShipment()
    {
        $testingMode = $this->config->get('saee')['environment'];

        if ($testingMode) {
            $citiesUrl = "https://www.k-w-h.com/deliveryrequest/getallcities";
            $districtsUrl = "https://www.k-w-h.com/deliveryrequest/getalldistricts";
            $curl_url = "https://www.k-w-h.com/deliveryrequest/new";
        } else {
            $citiesUrl = "https://corporate.saeex.com/deliveryrequest/getallcities";
            $districtsUrl = "https://corporate.saeex.com/deliveryrequest/getalldistricts";
            $curl_url = "https://corporate.saeex.com/deliveryrequest/new";
        }
        $allCities = json_decode(file_get_contents($citiesUrl));
        $allDistricts = json_decode(file_get_contents($districtsUrl));

        if ($allCities->success === true) {
            foreach ($allCities->cities as $city)
                $this->data['cities'][$city->name] = $city->name_ar;
        }
        if ($allDistricts->success === true) {
            foreach ($allDistricts->districts as $district)
                $this->data['districts'][$district->district] = $district->district_ar;
        }

        $this->language->load('shipping/saee');

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
                'text' => $this->language->get('heading_title_saee'),
                'href' => Url::addPath(['shipping', 'smsa'])->format(),
                'separator' => ' :: '
            ],
        ];

        $this->initializer([
            'localisation/geo_zone',
            'localisation/country',
            'shipping/saee'
        ]);

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            if ($this->validate()) {
                $this->load->model('setting/setting');
                $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'saee', true);
                $config = $this->config->get('saee');

                $curl_data = array(
                    'secret' => $config['api_key'],
                    'ordernumber' => $this->request->post['order_id'],
                    'name' => $this->request->post['saee_entry_name'],
                    'email' => $this->request->post['saee_entry_email'],
                    'mobile' => $this->request->post['saee_entry_mobile'],
                    'streetaddress' => $this->request->post['saee_entry_streetaddress'],
                    'city' => $this->request->post['saee_city'],
                    'state' => 'SA',
                    'district' => $this->request->post['saee_district'],
                    'weight' => $this->request->post['saee_entry_weight'],
                    'quantity' => $this->request->post['saee_entry_quantity'],
                    'cashondelivery' => $this->request->post['saee_entry_cod'],
                    'description' => str_replace(array("", "\r"), ' ', $this->request->post['saee_entry_description'])

                );



                $result = $this->sendCurlRequest($curl_url, $curl_data);

                if ($result['response']->success) {
                    $this->load->model("shipping/saee");
                    $this->model_shipping_saee->addShipmentDetails($result['data']['ordernumber'], $result, "pre-pair");
                    $this->response->setOutput(json_encode([
                        'success' => 1,
                        'success_msg' => $result['response']->message,
                        'redirect' => 1,
                        'to' => $_SERVER['HTTP_REFERER']
                    ]));
                } else {
                    $result_json['success'] = '0';
                    $result_json['errors'] = [$result['response']->error];
                    $result_json['errors']['warning'] = $this->language->get('saee_error_warning');
                    $this->response->setOutput(json_encode($result_json));
                }

            } else {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->errors;
                $this->response->setOutput(json_encode($result_json));
                return;
            }

        } else {
            $orderData = $this->model_sale_order->getOrder($this->request->get['order_id']);

            $this->data['saee_entry_name'] = $orderData['shipping_firstname'] . $orderData['shipping_lastname'];
            $this->data['saee_entry_email'] = $orderData['email'];
            $this->data['saee_entry_mobile'] = $orderData['telephone'];
            $this->data['saee_entry_streetaddress'] = $orderData['shipping_address_1'];
            $this->data['saee_city'] = $orderData['shipping_country'];
            $this->data['saee_district'] = $orderData['shipping_state'];
            $this->data['saee_entry_weight'] = "0";
            $this->data['saee_entry_quantity'] = "1";
            $this->data['saee_entry_cod'] = $orderData['total'];
            $this->data['order_id'] = $this->request->get['order_id'];

            $orderId = $this->request->get['order_id'];
            $this->load->model("shipping/saee");
            $orderData = $this->model_shipping_saee->getShipmentDetails($orderId);

            $this->data['isShipping'] = $orderData ? true : false;

            $this->template = 'shipping/saee/shipment/create.expand';
            $this->children = array(
                'common/footer',
                'common/header'
            );
            $this->response->setOutput($this->render_ecwig());
            return;
        }
    }

    function printSticker()
    {
        $testingMode = $this->config->get('saee')['environment'];

        if ($testingMode) {
            $stickerLink = "https://www.k-w-h.com/deliveryrequest/printsticker/";
        } else {
            $stickerLink = "https://corporate.saeex.com/deliveryrequest/printsticker/";
        }

        $orderId = $this->request->get['order_id'];


        $this->load->model("shipping/saee");
        $orderData = $this->model_shipping_saee->getShipmentDetails($orderId);
        if ($orderData) {
            $details = json_decode($orderData['details']);
            $waybillId = $details->response->waybill;
            $stickerLink .= $waybillId;
            $this->response->redirect($stickerLink);
        }
        return;
    }


    function trackShipment()
    {
        $testingMode = $this->config->get('saee')['environment'];

        if ($testingMode) {
            $trackShipmentLink = "https://www.k-w-h.com/tracking?trackingnum=";
        } else {
            $trackShipmentLink = "https://corporate.saeex.com/tracking?trackingnum=";
        }

        $orderId = $this->request->get['order_id'];

        $this->load->model("shipping/saee");
        $orderData = $this->model_shipping_saee->getShipmentDetails($orderId);
        if ($orderData) {
            $details = json_decode($orderData['details']);
            $waybillId = $details->response->waybill;
            $trackShipmentLink .= $waybillId;
            $result = json_decode(file_get_contents($trackShipmentLink));
            $this->response->setOutput(json_encode([
                'success' => 1,
                'success_msg' => $result->details[0]->notes,
            ]));
        }
        return ;
    }

    function cancelShipment()
    {
        $testingMode = $this->config->get('saee')['environment'];

        if ($testingMode) {
            $cancelationLink = "https://www.k-w-h.com/deliveryrequest/cancel";
        } else {
            $cancelationLink = "https://corporate.saeex.com/deliveryrequest/cancel";
        }

        $orderId = $this->request->post['order_id'];

        $this->load->model("shipping/saee");
        $config = $this->config->get('saee');
        $orderData = $this->model_shipping_saee->getShipmentDetails($orderId);
        if ($orderData) {
            $details = json_decode($orderData['details']);
            $waybillId = $details->response->waybill;
            $curl_data = [
                'secret' => $config['api_key'],
                'waybill' => $waybillId
            ];
            $result = $this->sendCurlRequest($cancelationLink, $curl_data);
            if ($result['response']) {
                $this->model_shipping_saee->deleteShipment($orderId);
                $this->response->redirect($this->url->link('sale/order/info', 'order_id=' . $orderId, 'SSL'));
            }
        }
        return;
    }

    private function sendCurlRequest($_url, $data)
    {
        $curl = curl_init($_url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        $options = array(
            CURLOPT_RETURNTRANSFER => true,   // return web page
            CURLOPT_HEADER => false,  // don't return headers
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json; charset=utf-8'
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

    private function validate()
    {

        if (empty($this->request->post['saee_entry_name'])) {
            $this->errors['saee_entry_name'] = $this->language->get('error_entry_name');
        }

        if (empty($this->request->post['saee_entry_email'])) {
            $this->errors['saee_entry_email'] = $this->language->get('error_saee_entry_email');
        }

        if (empty($this->request->post['saee_entry_mobile'])) {
            $this->errors['saee_entry_mobile'] = $this->language->get('saee_entry_mobile');
        }

        if (empty($this->request->post['saee_entry_streetaddress'])) {
            $this->errors['saee_entry_streetaddress'] = $this->language->get('error_saee_entry_streetaddress');
        }

        if (empty($this->request->post['saee_city']) || !($this->request->post['saee_city'][0])) {
            $this->errors['saee_city'] = $this->language->get('error_saee_city');
        }
        if (empty($this->request->post['saee_district']) || !($this->request->post['saee_district'][0])) {
            $this->errors['saee_district'] = $this->language->get('error_saee_district');
        }

        if (empty($this->request->post['saee_entry_weight'])) {
            $this->errors['saee_entry_weight'] = $this->language->get('error_saee_entry_weight');
        }

        if (empty($this->request->post['saee_entry_quantity'])) {
            $this->errors['saee_entry_quantity'] = $this->language->get('error_saee_entry_quantity');
        }

        if (empty($this->request->post['saee_entry_cod'])) {
            $this->errors['saee_entry_cod'] = $this->language->get('error_saee_entry_cod');
        }

        if ($this->errors && !isset($this->errors['error'])) {
            $this->errors['warning'] = $this->language->get('error_warning');
        }
        return $this->errors ? false : true;
    }

}