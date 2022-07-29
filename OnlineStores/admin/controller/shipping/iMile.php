<?php

use ExpandCart\Foundation\Support\Facades\Url;

/**
 * Class ControllerShippingiMile
 * Resonsable for Create/Delete/Track shipment
 */
class ControllerShippingiMile extends Controller
{
    /**
     * @var array iMile Available countries
     */
    private $availabeCountires = [
        "KSA" => "SAR",
        "CHN" => "CNY",
        "UAE" => "AED"
    ];

    protected $errors = [];

    public function index()
    {
        $this->language->load('shipping/iMile');

        $this->document->setTitle($this->language->get('heading_title_iMile'));

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
                'text' => $this->language->get('heading_title_iMile'),
                'href' => Url::addPath(['shipping', 'iMile'])->format(),
                'separator' => ' :: '
            ],
        ];

        $this->initializer([
            'localisation/geo_zone',
            'shipping/iMile'
        ]);

        $this->data['data'] = $this->iMile->getSettings();

        $this->template = 'shipping/iMile.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    /**
     * Update Admin Profile Settings
     */
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
            'shipping/iMile'
        ]);

        $iMile = $this->request->post['iMile'];

        if ($this->iMile->validate($iMile) == false) {
            $this->response->setOutput(json_encode([
                'success' => 0,
                'errors' => $this->iMile->getErrors()
            ]));
            return;
        }

        $this->load->model('setting/setting');

        $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'iMile', true);

        
            $this->tracking->updateGuideValue('SHIPPING');

        $this->iMile->updateSettings($iMile);

        $this->response->setOutput(json_encode([
            'success' => 1,
            'success_msg' => $this->language->get('text_settings_success')
        ]));
        return;
    }

    /**
     * Create shipment
     */
    public function createShipment()
    {
        $this->load->model('setting/setting');
        $this->load->model("shipping/iMile");

        $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'iMile', true);
        $curl_url = "";
        $iMileConfig = $this->config->get('iMile');

        if ($iMileConfig['environment']) {
            $curl_url = "https://hkpre-cloud.52imile.cn/oms/openApi/service";
        } else {
            $curl_url = "https://cloud.imile.com/oms/openApi/service";
        }

        $this->language->load('shipping/iMile');

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
                'text' => $this->language->get('heading_title_iMile'),
                'href' => Url::addPath(['shipping', 'iMile'])->format(),
                'separator' => ' :: '
            ],
        ];

        $this->initializer([
            'setting/setting',
            'shipping/iMile'
        ]);

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if ($this->validate()) {
                /**
                 * Prepare the request
                 */
                $params = [
                    "dispatchDate" => date("Y-m-d"),
                    "orderCode" => $this->request->post['order_id'],
                    "orderType" => "100", // 100: delivery order ,200: return orders, 300: exchange orders, 400: refund orders, 500: B2B orders
                    "oldExpressNo" => "",
                    "deliveryType" => "Delivery",
                    "consignor" => $iMileConfig['consignor'], // BY IMILE
                    "consigneeContact" => $this->request->post['entry_receiverName'],
                    "consigneeMobile" => $this->request->post['entry_receiverMobile'] ?? "",
                    "consigneePhone" => $this->request->post['entry_receiverPhone'],
                    "serviceTime" => "",
                    "consigneeCountry" => $this->request->post['entry_receiverCounty'], // KSA,CHN,UAE ONLY
                    "consigneeProvince" => "",
                    "consigneeCity" => $this->request->post['entry_receiverCity'] ?? "",
                    "consigneeArea" => $this->request->post['entry_receiverArea'] ?? "",
                    "consigneeAddress" => $this->request->post['entry_receiverAddress'] ?? "",
                    "paymentMethod" => $this->request->post['entry_receiverPaymentMethod'], //100:PPD(Prepaid) , 200:COD(Cash On Delivery)
                    "goodsValue" => $this->request->post['entry_goodsValue'],
                    "collectingMoney" => $this->request->post['entry_collectingMoney'],
                    "totalCount" => $this->request->post['entry_pieceNumber'],
                    "totalWeight" => $this->request->post['entry_packageWeight'],
                    "totalVolume" => $this->request->post['entry_totalVolume'],
                    "skuTotal" => $this->request->post['entry_SKUTotal'],
                    "skuName" => $this->request->post['entry_SKUName'],
                    "deliveryRequirements" => $this->request->post['entry_deliveryRequirements'] ?? "",
                    "orderDescription" => $this->request->post['entry_orderDescription'] ?? "",
                    "buyerId" => $this->request->post['entry_buyerId'] ?? "",
                    "currency_code" => $this->request->post['currency_code']
                ];

                $curl_data = [
                    "sign" => strtoupper(md5($iMileConfig['api_secret_key'] . "customerId" . $iMileConfig['customer_id'] . "formatjsonmethodcreateOrdersignMethodmd5" . json_encode($params) . $iMileConfig['api_secret_key'])),
                    "format" => "json",
                    "signMethod" => "md5",
                    "param" => $params,
                    "customerId" => $iMileConfig['customer_id'], // BY IMILE
                    "method" => "createOrder"
                ];


                /**
                 * Handle The Response
                 */
                $result = $this->sendCurlRequest($curl_url, json_encode($curl_data));
                if ($result['response']->status == 200) {
                    $this->model_shipping_iMile->addShipmentDetails($this->request->post['order_id'], json_decode($result['data']), "pre-pair");
                    $this->response->setOutput(json_encode([
                        'success' => 1,
                        'success_msg' => $result['response']->description,
                        'redirect' => 1,
                        'to' => $_SERVER['HTTP_REFERER']
                    ]));
                } else {
                    $result_json['success'] = '0';
                    $result_json['errors'] = [$result['response']->description];
                    $result_json['errors']['warning'] = $this->language->get('iMile_error_warning');
                    $this->response->setOutput(json_encode($result_json));
                }

            } else {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->errors;
                $this->response->setOutput(json_encode($result_json));
                return;
            }

        } else {
            $orderId = $this->request->get['order_id'];
            $orderData = $this->model_shipping_iMile->getShipmentDetails($orderId);
            if ($orderData) {
                $this->data['isShipping'] = true;
                $data = json_decode($orderData['details'], true)['param'];
                $this->data['entry_receiverCounty'] = $data['consigneeCountry'];
                $this->data['entry_receiverCity'] = $data['consigneeCity'];
                $this->data['entry_receiverName'] = $data['consigneeContact'];
                $this->data['entry_receiverMobile'] = $data['consigneeMobile'];
                $this->data['entry_receiverPhone'] = $data['consigneePhone'];
                $this->data['entry_receiverAddress'] = $data['consigneeAddress'];
                $this->data['entry_receiverProvince'] = $data['consigneeProvince'];
                $this->data['entry_receiverPaymentMethod'] = $data['paymentMethod'];
                $this->data['entry_goodsValue'] = $data['goodsValue'];
                $this->data['entry_collectingMoney'] = $data['collectingMoney'];
                $this->data['entry_pieceNumber'] = $data['totalCount'];
                $this->data['entry_packageWeight'] = $data['totalWeight'];
                $this->data['entry_totalVolume'] = $data['totalVolume'];
                $this->data['entry_SKUTotal'] = $data['skuTotal'];
                $this->data['entry_SKUName'] = $data['skuName'];
                $this->data['entry_deliveryRequirements'] = $data['deliveryRequirements'];
                $this->data['entry_orderDescription'] = $data['orderDescription'];
                $this->data['entry_buyerId'] = $data['buyerId'];
                $this->data['currency_code'] = $data['currency_code'];
                $this->data['order_id'] = $this->request->get['order_id'];

            } else {
                $this->data['isShipping'] = false;
                $orderData = $this->model_sale_order->getOrder($this->request->get['order_id']);
                $store_info = $this->model_setting_setting->getSetting('config', $orderData['store_id']);
                $this->data['entry_receiverCounty'] = (in_array($orderData['payment_iso_code_3'],array_keys($this->availabeCountires))) ? $orderData['payment_iso_code_3'] : "";
                $this->data['entry_receiverCity'] = $orderData['shipping_city'];
                $this->data['entry_receiverName'] = $orderData['shipping_firstname'] . $orderData['shipping_lastname'];
                $this->data['entry_receiverMobile'] = $orderData['telephone'];
                $this->data['entry_receiverPhone'] = $orderData['telephone'];
                $this->data['entry_receiverAddress'] = $orderData['shipping_address_1'];
                $this->data['entry_receiverProvince'] = $orderData['shipping_city'];
                $this->data['entry_goodsValue'] = $orderData['total'];
                $this->data['entry_collectingMoney'] = $orderData['total'];
                $this->data['entry_orderSource'] = $orderData['store_name'];
                $this->data['currency_code'] = $orderData['currency_code'];
                $this->data['order_id'] = $this->request->get['order_id'];

            }

            $this->template = 'shipping/iMile/shipment/create.expand';
            $this->children = array(
                'common/footer',
                'common/header'
            );
            $this->response->setOutput($this->render_ecwig());
            return;
        }
    }

    /**
     * Track Shipment
     */
    function trackShipment()
    {
        $this->language->load('shipping/iMile');

        $this->load->model("shipping/iMile");
        $iMileConfig = $this->config->get('iMile');

        $curl_url = "";
        if ($iMileConfig['environment']) {
            $curl_url = "https://hkpre-cloud.52imile.cn/oms/openApi/service";
        } else {
            $curl_url = "https://cloud.imile.com/oms/openApi/service";
        }

        $orderData = $this->model_shipping_iMile->getShipmentDetails($this->request->get['order_id']);
        if ($orderData) {
            /**
             * Prepare the request
             */
            $params = [
                "orderType" => "1",
                "language" => "2",
                "orderNo" => $this->request->get['order_id']
            ];

            $curl_data = [
                "sign" => md5($iMileConfig['api_key'] . "customerId" . $iMileConfig['customer_id'] . "formatjsonmethodtrackOrderOneByOnesignMethodmd5" . json_encode($params) . $iMileConfig['api_key']),
                "format" => "json",
                "signMethod" => "md5",
                "param" => $params,
                "customerId" => $iMileConfig['customer_id'], // BY IMILE
                "method" => "trackOrderOneByOne"
            ];
            /**
             * Handle the Response
             */
            $result = $this->sendCurlRequest($curl_url, json_encode($curl_data));
            if ($result['response']->status == 200) {
                $message = "";
                foreach ($result['response']->locusDetailed as $locusDetail){
                    $message .= $locusDetail['latestLocus'];
                }
                $this->response->setOutput(json_encode([
                    'success' => 1,
                    'success_msg' => !empty($message) ? $message : $this->language->get("iMile_data_not_available_now"),
                ]));
            } else {
                $result_json['success'] = '0';
                $result_json['errors'] = [$result['response']->description];
                $result_json['errors']['warning'] = $this->language->get('iMile_error_warning');
                $this->response->setOutput(json_encode($result_json));
            }
        }
        return;
    }

    /**
     * Cancel Shipment
     */
    function cancelShipment()
    {
        $this->load->model("shipping/iMile");
        $iMileConfig = $this->config->get('iMile');

        $curl_url = "";
        if ($iMileConfig['environment']) {
            $curl_url = "https://hkpre-cloud.52imile.cn/oms/openApi/service";
        } else {
            $curl_url = "https://cloud.imile.com/oms/openApi/service";
        }

        $orderData = $this->model_shipping_iMile->getShipmentDetails($this->request->post['order_id']);
        if ($orderData) {
            /**
             * Prepare the request
             */
            $params = [
                "orderCode" => $this->request->post['order_id']
            ];

            $curl_data = [
                "sign" => md5($iMileConfig['api_key'] . "customerId" . $iMileConfig['customer_id'] . "formatjsonmethoddeleteOrdersignMethodmd5" . json_encode($params) . $iMileConfig['api_key']),
                "format" => "json",
                "signMethod" => "md5",
                "param" => $params,
                "customerId" => $iMileConfig['customer_id'], // BY IMILE
                "method" => "deleteOrder"
            ];
            /**
             * Handle the Response
             */
            $result = $this->sendCurlRequest($curl_url, json_encode($curl_data));
            if ($result['response']->status == 200) {
                $this->model_shipping_iMile->deleteShipment($this->request->post['order_id']);
                $this->response->setOutput(json_encode([
                    'success' => 1,
                    'success_msg' => $result['response']->description,
                ]));
                $this->response->redirect($this->url->link('sale/order/info', 'order_id=' . $this->request->post['order_id'], 'SSL'));
            } else {
                $result_json['success'] = '0';
                $result_json['errors'] = [$result['response']->description];
                $result_json['errors']['warning'] = $this->language->get('iMile_error_warning');
                $this->response->setOutput(json_encode($result_json));
            }
        }
        return;
    }

    private function sendCurlRequest($_url, $data)
    {
        $curl = curl_init($_url);
        $options = array(
            CURLOPT_RETURNTRANSFER => true,   // return web page
            CURLOPT_HTTPHEADER => array('Content-type: application/json'),
            CURLOPT_FOLLOWLOCATION => true,   // follow redirects
            CURLOPT_MAXREDIRS => 10,     // stop after 10 redirects
            CURLOPT_ENCODING => "",     // handle compressed
            CURLOPT_AUTOREFERER => true,   // set referrer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
            CURLOPT_TIMEOUT => 120,    // time-out on response
        );
        curl_setopt_array($curl, $options);

        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, ($data));
        }

        $response = curl_exec($curl);
        $response = json_decode($response);
        curl_close($curl);
        $result = ['response' => $response,
            'data' => $data
        ];
        return $result;
    }

    function convertCurrency(){
        $result = ['success'=>false];

        $countryCode = $this->request->get['entry_receiverCounty'];
        if(in_array($countryCode,array_keys($this->availabeCountires)) && $this->currency->has($this->availabeCountires[$countryCode])){
            $result['success'] = true;
            $result['goodsValue'] = $this->currency->convert($this->request->get['entry_goodsValue'],$this->request->get['currency_code'],$this->availabeCountires[$countryCode]);
            $result['collectingMoney'] = $this->currency->convert($this->request->get['entry_collectingMoney'],$this->request->get['currency_code'],$this->availabeCountires[$countryCode]);
            $result['currency_code'] = $this->availabeCountires[$countryCode];
        }
        $this->response->setOutput(json_encode($result));
    }
    /**
     * @return bool
     */
    private function validate()
    {
        $postData = $this->request->post;

        if(!in_array($postData['currency_code'],array_values($this->availabeCountires)) && !$this->currency->has($this->availabeCountires[$postData['currency_code']])){
            $this->errors['currency_code'] = $this->language->get('currency_code_error');
        }

        if (!$postData['entry_receiverName'] || empty($postData['entry_receiverName'])) {
            $this->errors['entry_receiverName'] = $this->language->get('entry_receiverName');
        }

        if (!$postData['entry_receiverName'] || empty($postData['entry_receiverName'])) {
            $this->errors['entry_receiverName'] = $this->language->get('entry_receiverName');
        }

        if (!$postData['entry_receiverPhone'] || empty($postData['entry_receiverPhone'])) {
            $this->errors['entry_receiverPhone'] = $this->language->get('entry_receiverPhone');
        }

        if (!$postData['entry_receiverCounty'] || empty($postData['entry_receiverCounty']) || !in_array($postData['entry_receiverCounty'],array_keys($this->availabeCountires))) {
            $this->errors['entry_receiverCounty'] = $this->language->get('entry_receiverCounty');
        }

        if (!$postData['entry_receiverCity'] || empty($postData['entry_receiverCity'])) {
            $this->errors['entry_receiverCity'] = $this->language->get('entry_receiverCity');
        }

        if (!$postData['entry_receiverAddress'] || empty($postData['entry_receiverAddress'])) {
            $this->errors['entry_receiverAddress'] = $this->language->get('entry_receiverAddress');
        }

        if (!$postData['entry_receiverPaymentMethod'] || empty($postData['entry_receiverPaymentMethod'])) {
            $this->errors['entry_receiverPaymentMethod'] = $this->language->get('entry_receiverPaymentMethod');
        }

        if (!$postData['entry_pieceNumber'] || empty($postData['entry_pieceNumber'])) {
            $this->errors['entry_pieceNumber'] = $this->language->get('entry_pieceNumber');
        }

        if (!$postData['entry_packageWeight'] || empty($postData['entry_packageWeight'])) {
            $this->errors['entry_packageWeight'] = $this->language->get('entry_packageWeight');
        }

        if (!$postData['entry_goodsValue'] || empty($postData['entry_goodsValue'])) {
            $this->errors['entry_goodsValue'] = $this->language->get('entry_goodsValue');
        }

        if (!$postData['entry_collectingMoney'] || empty($postData['entry_collectingMoney'])) {
            $this->errors['entry_collectingMoney'] = $this->language->get('entry_collectingMoney');
        }

        if (!$postData['entry_totalVolume'] || empty($postData['entry_totalVolume'])) {
            $this->errors['entry_totalVolume'] = $this->language->get('entry_totalVolume');
        }

        if (!$postData['entry_SKUTotal'] || empty($postData['entry_SKUTotal'])) {
            $this->errors['entry_SKUTotal'] = $this->language->get('entry_SKUTotal');
        }

        if (!$postData['entry_SKUName'] || empty($postData['entry_SKUName'])) {
            $this->errors['entry_SKUName'] = $this->language->get('entry_SKUName');
        }
        if ($this->errors && !isset($this->errors['error'])) {
            $this->errors['warning'] = $this->language->get('error_warning');
        }
        return $this->errors ? false : true;
    }

}