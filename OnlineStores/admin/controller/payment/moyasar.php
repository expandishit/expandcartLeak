<?php

use ExpandCart\Foundation\Support\Facades\Url;

class ControllerPaymentMoyasar extends Controller
{
    public function index()
    {
        $this->language->load('payment/moyasar');

        $this->document->setTitle($this->language->get('heading_title_moyasar'));

        $this->data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => Url::addPath(['common', 'home'])->format(),
                'separator' => false
            ],
            [
                'text' => $this->language->get('mn_setting_menu_payment'),
                'href' => Url::addPath(['extension', 'payment'])->format(),
                'separator' => ' :: '
            ],
            [
                'text' => $this->language->get('heading_title_moyasar'),
                'href' => Url::addPath(['payment', 'moyasar'])->format(),
                'separator' => ' :: '
            ],
        ];

        $this->initializer([
            'geo' => 'localisation/geo_zone',
            'statuses' => 'localisation/order_status',
            'moyasar' => 'payment/moyasar/settings'
        ]);

        $this->data['geo_zones'] = $this->geo->getGeoZones();

        $this->data['order_statuses'] = $this->statuses->getOrderStatuses();

        $this->data['moyasar'] = $this->moyasar->getSettings();

        $this->template = 'payment/moyasar/settings.expand';
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
            'moyasar' => 'payment/moyasar/settings'
        ]);

        $moyasar = $this->request->post['moyasar'];

        if ($this->moyasar->validate($moyasar) == false) {
            $this->response->setOutput(json_encode([
                'success' => 0,
                'errors' => $this->moyasar->getErrors()
            ]));
            return;
        }

        $this->load->model('setting/setting');

        $this->model_setting_setting->checkIfExtensionIsExists('payment', 'moyasar', true);

        $this->tracking->updateGuideValue('PAYMENT');

        $this->moyasar->updateSettings($moyasar);

        $this->response->setOutput(json_encode([
            'success' => 1,
            'success_msg' => $this->language->get('text_settings_success')
        ]));
        return;
    }

    public function createShipment()
    {
        if (
            isset($this->request->get['order_id']) == false ||
            filter_var($this->request->get['order_id'], FILTER_VALIDATE_INT) == false
        ) {
            throw new \Exception('Invalid id');
        }

        $this->language->load('payment/moyasar');

        $this->document->setTitle($this->language->get('create_shipment_heading_title'));

        $this->data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => Url::addPath(['common', 'home'])->format(),
                'separator' => false
            ],
            [
                'text' => $this->language->get('text_payment'),
                'href' => Url::addPath(['extension', 'payment'])->format(),
                'separator' => ' :: '
            ],
            [
                'text' => $this->language->get('heading_title_moyasar'),
                'href' => Url::addPath(['payment', 'moyasar'])->format(),
                'separator' => ' :: '
            ],
            [
                'text' => $this->language->get('create_shipment_heading_title'),
                'href' => Url::addPath(['payment', 'smsa'])->format(),
                'separator' => ' :: '
            ],
        ];

        $orderId = $this->request->get['order_id'];

        $this->initializer([
            'sale/order',
            'moyasar' => 'payment/moyasar/shipment',
            'setting' => 'payment/moyasar/settings',
        ]);

        $this->load->model('localisation/country');
        $this->data['countries'] = $this->model_localisation_country->getAllCountriesLocale();

        $orderInfo = $this->order->getOrder($orderId);

        $this->data['currency_code'] = $orderInfo['currency_code'] ? $orderInfo['currency_code'] : '';

        $orderProducts = $this->model_sale_order->getOrderProducts($orderId);

        if (isset($this->request->post['weight_unit']) && !empty($this->request->post['weight_unit'])) {
            $getunit_classid = $this->model_sale_aramex->getWeightClassId($this->request->post['weight_unit']);
            $this->data['weight_unit'] = $getunit_classid->row['unit'];
            $config_weight_class_id = $getunit_classid->row['weight_class_id'];
        } else {
            $this->data['weight_unit'] = $this->weight->getUnit($this->config->get('config_weight_class_id'));
            $config_weight_class_id = $this->config->get('config_weight_class_id');
        }

        $this->data['order_products'] = array();
        $weighttot = 0;
        foreach ($orderProducts as $order_product) {
            if (isset($order_product['order_option'])) {
                $order_option = $order_product['order_option'];
            } elseif (isset($this->request->get['order_id'])) {
                $order_option = $this->model_sale_order->getOrderOptions($this->request->get['order_id'], $order_product['order_product_id']);
            } else {
                $order_option = array();
            }

            $product_weight_query = $this->db->query("SELECT weight, weight_class_id FROM " . DB_PREFIX . "product WHERE product_id = '" . $order_product['product_id'] . "'");
            $weight_class_query = $this->db->query("SELECT wcd.unit FROM " . DB_PREFIX . "weight_class wc LEFT JOIN " . DB_PREFIX . "weight_class_description wcd ON (wc.weight_class_id = wcd.weight_class_id) WHERE wcd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND wc.weight_class_id = '" . $product_weight_query->row['weight_class_id'] . "'");

            $prodweight = $this->weight->convert($product_weight_query->row['weight'], $product_weight_query->row['weight_class_id'], $config_weight_class_id);
            $prodweight = ($prodweight * $order_product['quantity']);
            $weighttot = ($weighttot + $prodweight);
            $this->data['product_arr'][] = $order_product['name'];
            $this->data['order_products'][] = array(
                'order_product_id' => $order_product['order_product_id'],
                'product_id' => $order_product['product_id'],
                'name' => $order_product['name'],
                'model' => $order_product['model'],
                'option' => $order_option,
                'quantity' => $order_product['quantity'],
                'weight' => number_format($this->weight->convert($product_weight_query->row['weight'], $product_weight_query->row['weight_class_id'], $config_weight_class_id), 2),
                'weight_class' => $weight_class_query->row['unit'],
                'price' => number_format($order_product['price'], 2),
                'total' => $order_product['total'],
                'tax' => $order_product['tax'],
                'reward' => $order_product['reward']
            );
        }
        $this->data['total_weight'] = number_format($weighttot, 2);
        $this->data['total_price'] = number_format($orderInfo['total'], 2);

        $this->data['order'] = $orderInfo;
        $this->data['orderId'] = $orderInfo['order_id'];

        $this->data['sender'] = $this->setting->getSenderData();
        $this->data['receiver'] = $this->setting->getReceiverData($orderInfo);

        $this->data['info'] = [
            'total_weight' => $this->data['total_weight']
        ];

        $this->data['shipment'] = $this->setting->checkShipmentByOrderId($orderInfo['order_id']);

        $this->template = 'payment/moyasar/shipment/create.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function submitShipment()
    {
        $this->initializer([
            'payment/moyasar/shipment',
            'payment/moyasar/settings',
        ]);

        if (
            isset($this->request->post['order_id']) == false ||
            filter_var($this->request->post['order_id'], FILTER_VALIDATE_INT) == false
        ) {
            throw new \Exception('Invalid id');
        }

        $postBody = $this->request->post;

        $shipment = $postBody['shipment'];

        $orderId = $postBody['order_id'];

        $shipmentInfo = $this->settings->checkShipmentByOrderId($orderId);

        if ($shipmentInfo) {
            throw new \Exception('Shipment is exists');
        }

        $this->shipment->setEnv($this->settings->getEnvironment());
        $this->shipment->setCredentials($this->settings->getUserCredentials());
        $this->shipment->setSender($shipment['sender']);
        $this->shipment->setReceiver($shipment['receiver']);
        $this->shipment->setPaymentType(
            $shipment['info']['payment_type']
        );
        $this->shipment->setShippmentDetails($shipment['info']);
        $this->shipment->setWsdl(implode('/', [
            HTTP_CATALOG, 'services/moyasar/moyasar.wsdl'
        ]));


        if (!$this->shipment->create()) {
            $this->response->setOutput(json_encode([
                'success' => 0,
                'errors' => $this->shipment->getErrors()
            ]));
            return;
        }

        $response = $this->shipment->getResponse();

        if (
            strtolower($response->getStatusText()) == 'ok' &&
            $response->getStatusCode() === 0
        ) {
            $this->settings->newShipment([
                'order_id' => $orderId,
                'shipment_number' => $response->getShipmentNumber(),
                'label' => $response->getLabel()
            ]);

            $this->response->setOutput(json_encode([
                'success' => 1,
                'success_msg' => $this->language->get('text_success'),
                'redirect' => '1',
                'to' => $this->url->link('sale/order/info', 'order_id=' . $orderId, 'SSL')->format()
            ]));
            return;
        }

        $this->response->setOutput(json_encode([
            'success' => 0,
            'errors' => [$response->getStatusText()]
        ]));
        return;
    }

    public function getZones()
    {
        $json = array();

        $this->load->model('localisation/country');

        $country_info = $this->model_localisation_country->getCountryByLanguageId($this->request->get['country_id'], 1);

        if ($country_info) {
            $this->load->model('localisation/zone');

            $json = array(
                'country_id' => $country_info['country_id'],
                'name' => $country_info['name'],
                'iso_code_2' => $country_info['iso_code_2'],
                'iso_code_3' => $country_info['iso_code_3'],
                'address_format' => $country_info['address_format'],
                'postcode_required' => $country_info['postcode_required'],
                'zone' => $this->model_localisation_zone->getZonesByCountryIdAndLanguageId(
                    $this->request->get['country_id'],
                    1
                ),
                'status' => $country_info['status']
            );
        }

        $this->response->setOutput(json_encode($json));
        return;
    }

    public function shipmentStatus()
    {
        if (
            isset($this->request->post['order_id']) == false ||
            filter_var($this->request->post['order_id'], FILTER_VALIDATE_INT) == false
        ) {
            throw new \Exception('Invalid id');
        }

        $this->initializer([
            'payment/moyasar/shipment',
            'payment/moyasar/settings',
        ]);

        $orderId = $this->request->post['order_id'];

        $shipment = $this->settings->checkShipmentByOrderId($orderId);

        if (!$shipment) {
            throw new \Exception('Invalid shipment');
        }

        $this->shipment->setEnv($this->settings->getEnvironment());
        $this->shipment->setCredentials($this->settings->getUserCredentials());
        $this->shipment->setShipmentNumber($shipment['shipment_number']);
        $this->shipment->setWsdl(implode('/', [
            HTTP_CATALOG, 'services/moyasar/moyasar.wsdl'
        ]));

        if (!$this->shipment->getShipment()) {
            $this->response->setOutput(json_encode([
                'success' => 0,
                'errors' => $this->shipment->getErrors()
            ]));
            return;
        }

        $response = $this->shipment->getResponse();

        if (
            strtolower($response->getStatusText()) == 'ok' &&
            $response->getStatusCode() === 0
        ) {
            $this->response->setOutput(json_encode([
                'status' => 'success',
                'details' => [
                    'label' => $response->getLabel(),
                ],
            ]));
            return;
        }
        $this->response->setOutput(json_encode([
            'success' => 0,
            'errors' => $this->shipment->getErrors()
        ]));
        return;
    }
}
