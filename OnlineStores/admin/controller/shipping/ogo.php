<?php

use ExpandCart\Foundation\Support\Facades\Url;

class ControllerShippingOgo extends Controller {

    protected $errors = [];
    private $url = "https://ogo.delivery:1000/api/user/push_order_from_cavaraty_to_ogo";

    public function install() {
        $this->load->model('shipping/ogo');
        $this->model_shipping_ogo->install();
    }

    public function uninstall() {
        $this->load->model('shipping/ogo');
        $this->model_shipping_ogo->uninstall();
    }

    public function index() {

        $this->load->language('shipping/ogo');

        $this->initializer([
            'shipping/ogo',
            'setting/setting',
            'localisation/geo_zone'
        ]);


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
                'text' => $this->language->get('heading_title'),
                'href' => Url::addPath(['shipping', 'esnad'])->format(),
                'separator' => ' :: '
            ],
        ];

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            if (!$this->validate()) {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));

                return;
            }

            $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'ogo', true);

            $this->model_setting_setting->insertUpdateSetting('ogo', $this->request->post);

            $this->tracking->updateGuideValue('SHIPPING');

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';

            $this->response->setOutput(json_encode($result_json));

            return;
        }

        $this->data['ogo_status'] = $this->config->get('ogo_status');

        $this->data['email'] = $this->config->get('email');

        $this->data['password'] = $this->config->get('password');

        $this->data['br_email'] = $this->config->get('br_email');

        $this->data['geo_zone_id'] = $this->config->get('geo_zone_id');

        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $this->template = 'shipping/ogo/shipment/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'shipping/ogo')) {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if ($this->error && !isset($this->error['error'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return $this->error ? false : true;
    }

    public function createShipment() {

        // get order id
        $orderId = $this->request->get['order_id'];

        $this->load->language('shipping/ogo');

        $this->initializer([
            'shipping/ogo',
            'catalog/product',
            'sale/order'
        ]);

        // get shipping details for check if customer make shipment before
        $shipping_details = $this->model_shipping_ogo->getShipmentDetails($orderId);
        if (count($shipping_details) > 0) {
            $this->redirect(Url::addPath(['shipping', 'ogo', 'shipmentDetails', "?order_id=$orderId"])->format());
        }



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
                'text' => $this->language->get('heading_title'),
                'href' => Url::addPath(['shipping', 'OGO'])->format(),
                'separator' => ' :: '
            ],
        ];


        $orderData = $this->model_sale_order->getOrder($orderId);

        $customDataArray = ['house_id', 'block_id', 'street_id'];

        $ogoFields = (!empty($this->model_shipping_ogo->getOgoFields($orderId)['shipping_address'])) ? $this->model_shipping_ogo->getOgoFields($orderId)['shipping_address'] : $this->model_shipping_ogo->getOgoFields($orderId)['payment_address'];

        $customFilteredData = array_filter($ogoFields, function ($var) use ($customDataArray) {

            if (in_array($var['field_type_name'], $customDataArray)) {
                return $data[] = $var;
            }
        });

        $this->data['customer_name'] = $orderData['shipping_firstname'] . ' ' . $orderData['shipping_lastname'];
        $this->data['order_id'] = $orderData['order_id'];
        $this->data['phone'] = $orderData['payment_telephone'];
        $this->data['phone'] = $orderData['payment_telephone'];
        $this->data['amount'] = number_format($orderData['total'], 2);
        $this->data['cities'] = $this->model_shipping_ogo->getOgoAreas();
        $this->data['custom_fields'] = $customFilteredData;
        $this->data['payment_methods'] = $this->model_shipping_ogo->getOgoPaymentType();
        $this->data['delivery_vehicles'] = $this->model_shipping_ogo->getOgoDeliveryVehicles();

        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->addScript('view/javascript/popupwindow/timepicker.js');

        $this->template = 'shipping/ogo/shipment/create.expand';
        $this->children = array(
            'common/footer',
            'common/header'
        );
        $this->response->setOutput($this->render_ecwig());
        return;
    }

    public function sendShipmentRequest() {

        $this->initializer([
            'shipping/ogo',
            'sale/order'
        ]);

        $orderData = $this->request->post;

        if ($this->validateShipmentRequest($orderData)) {

            $requestArray = [
                'email' => $this->config->get('email'),
                'password' => $this->config->get('password'),
                'br_email' => $this->config->get('br_email'),
                'paymentType' => $orderData['payment_method'],
                'area_name' => $orderData['area_name'],
                'Phone' => $orderData['phone'],
                'customerName' => $orderData['customerName'],
                'block' => $orderData['block_id'],
                'order_id' => $orderData['order_id'],
                'amount' => $orderData['amount'],
                'pick_up_date' => $orderData['pick_up_date'],
                'pick_up_time' => $orderData['time_start'] . ' - ' . $orderData['time_end'],
                'street' => $orderData['street_id'],
                'house' => $orderData['house_id'],
                'delivery_vehicle' => $orderData['delivery_vehicle']
            ];

            $responseData = json_decode($this->sendCurlRequest($this->url, json_encode($requestArray)), true);

            if (!empty($responseData['status'])) {
                $this->model_shipping_ogo->addShipmentDetails($orderData['order_id'], $responseData, '1');
                $this->response->setOutput(json_encode([
                    'success' => 1,
                    'success_msg' => $this->language->get('success_message'),
                    'redirect' => 1,
                    'to' => Url::addPath(['shipping', 'ogo', 'shipmentDetails', "?order_id=$orderData[order_id]"])->format()
                ]));
            } else {
                $result_json['success'] = '0';
                $result_json['errors'] = $responseData['message'];
                $this->response->setOutput(json_encode($result_json));
            }
        } else {
            $result_json['success'] = '0';
            $result_json['errors'] = $this->errors;
            $this->response->setOutput(json_encode($result_json));
        }
    }

    public function shipmentDetails() {
        $this->language->load('shipping/ogo');
        // get order id
        $orderId = $this->request->get['order_id'];
        $this->load->model("shipping/ogo");
        // get shipping details for check if customer make shipment before
        $shippingDetails = json_decode($this->model_shipping_ogo->getShipmentDetails($orderId)['details'], true)['data'];
        
        $this->data['order_id'] = $orderId;
        $this->data['name'] = $shippingDetails['receiver_name'];
        $this->data['phone'] = $shippingDetails['receiver_phone'];
        $this->data['location'] = $shippingDetails['receiver_location'];
        $this->data['latitude'] = $shippingDetails['receiver_latitude'];
        $this->data['longitute'] = $shippingDetails['receiver_longitute'];
        $this->data['delivery_vehicle'] = $shippingDetails['delivery_vehicle'];
        $this->data['pick_up_date'] = $shippingDetails['pick_up_date'];
        $this->data['pick_up_time'] = $shippingDetails['pick_up_time'];
        
        $this->template = 'shipping/ogo/shipment/details.expand';
        $this->children = array(
            'common/footer',
            'common/header'
        );
        $this->response->setOutput($this->render_ecwig());
        return;
    }

    private function sendCurlRequest($_url, $data) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = curl_exec($ch);

        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);

        return $responseData;
    }

    protected function validateShipmentRequest($postData) {

        $this->load->language('shipping/ogo');

        foreach ($postData as $key => $value) {
            if (empty($value)) {
                $this->errors['entry_' . $key] = $this->language->get('error_' . $key);
            }
        }

        if ($this->errors && !isset($this->errors['error'])) {
            $this->errors['warning'] = $this->language->get('error_warning');
        }
        return $this->errors ? false : true;
    }

}
