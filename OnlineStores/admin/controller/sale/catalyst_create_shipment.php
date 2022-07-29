<?php

class ControllerSaleCatalystCreateShipment extends Controller {

    /**
     * Errors holder array
     */
    private $errors = [];

    /**
     * Acquire access token url
     */
    private $acquireTokenURL = 'https://api.catalyst.com.sa/client-auth/acquire-token';

    /**
     * Place order api endpoint
     */
    private $placeOrderURL = 'https://api.catalyst.com.sa/orders';

    /**
     * Order status api endpoint
     */
    private $orderStatusURL = 'https://api.catalyst.com.sa/orders/%s/status';

    /**
     * Cancel order status api endpoint
     */
    private $cancelOrderStatusURL = 'https://api.catalyst.com.sa/orders/%s/cancel';

    /**
     * Track order status api endpoint
     */
    private $trackOrderStatusURL = 'https://api.catalyst.com.sa/orders/%s/status-ext';

    /**
     * Create catalyst shipment entry point
     */
    public function index() {
        $this->load->model('sale/order');
        $this->load->model('shipping/catalyst');
        $this->load->model('localisation/language');
        $this->language->load('shipping/catalyst');

        if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}
		
		$order_info = $this->model_sale_order->getOrder($order_id);

        if ($order_info) {

            $shipmentDetails = $this->model_shipping_catalyst->getShipmentDetails($order_id);

            $catalyst_address = [
                                    $order_info['shipping_address_1'] ?? $order_info['payment_address_1'],
                                    $order_info['shipping_address_2'] ?? $order_info['payment_address_2'],
                                    $order_info['shipping_zone'] ?? $order_info['payment_zone'],
                                    $order_info['shipping_city'] ?? $order_info['payment_city'],
                                    $order_info['shipping_country'] ?? $order_info['payment_country']
                                ];
            $this->data['catalys_address'] = implode(', ' , array_filter($catalyst_address));

            $order_info['total'] = (float) $order_info['total'];

            $this->document->setTitle($this->language->get('catalyst_title'));

            $this->data['create_shipment_link'] = $this->url->link('sale/catalyst_create_shipment/createShipment', '', 'SSL');
            $this->data['cancel_shipment_link'] = $this->url->link('sale/catalyst_create_shipment/cancelShipment', '', 'SSL');
            $this->data['order_status_update_link'] = $this->url->link('sale/catalyst_create_shipment/updateOrderStatus', '', 'SSL');
            $this->data['order_status_track_link'] = $this->trackOrderStatusURL;
            $this->data['catalyst_auth_token'] = $this->getCatalystAuthToken();
            $this->data['catalyst_google_api_key'] = $this->config->get('catalyst_google_api_key');
            $this->data['order_info'] = $order_info;
            $this->data['products'] = $this->model_sale_order->getOrderProducts($this->request->get['order_id']);
            $this->data['languages'] = $this->model_localisation_language->getLanguages();
            $this->data['catalyst_payment_methods'] = $this->model_shipping_catalyst->getPaymentMethods();
            // $this->data['catalyst_order_status'] = $this->model_shipping_catalyst->getOrderStatus();

            if (!empty($shipmentDetails)) {
                $this->data['shipment_details'] = $shipmentDetails;
            }

            $this->data['breadcrumbs'] = array();
    
            $this->data['breadcrumbs'][] = array(
                'text'      => $this->language->get('text_home'),
                'href'      => $this->url->link('common/home', '', 'SSL'),
                'separator' => false
            );
    
            $this->data['breadcrumbs'][] = array(
                'text'      => $this->language->get('catalyst_create_shipment'),
                'href'      => $this->url->link('sale/order', '', 'SSL'),
                'separator' => ' :: '
            );
    
            $this->base = 'common/base';
            $this->template = 'sale/catalyst_create_shipment.expand';
        } else {
            $this->language->load('error/not_found');
			$this->document->setTitle($this->language->get('heading_title'));
			$this->data['heading_title'] = $this->language->get('heading_title');
			$this->data['text_not_found'] = $this->language->get('text_not_found');
			$this->data['breadcrumbs'] = array();
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', '', 'SSL'),
				'separator' => false
			);
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('error/not_found', '', 'SSL'),
				'separator' => ' :: '
			);

			$this->template = 'error/not_found.expand';
            $this->base = "common/base";
        }

        $this->response->setOutput($this->render());
    }

    /**
     * Create shipment order
     */
    public function createShipment()
    {

        $this->createShipmentProccess($this->request->post);
    }

    /**
     * Auto Create shipment order
     */
    public function autoCreateShipment()
    {
        if(!$this->request->post || !$this->request->post['cty_data'])
            return;
        
        return $this->createShipmentProccess($this->request->post['cty_data']);
    }

    /**
     * Auto Create shipment order
     */
    public function createShipmentProccess($data)
    {
        $this->language->load('shipping/catalyst');

        if (!$this->validate($data))
        {
            $errorsData = ['warning' => $this->language->get('catalyst_error_api')];
            $errorsData = array_merge($errorsData, $this->errors);
            $this->response->setOutput(json_encode(['errors' => $errorsData]));
            return;
        }

        $addressGeoCode = $this->getGeocode($data['catalyst_address']['address'], $data['coordinates']);
        $data['lat'] = $addressGeoCode['lat'];
        $data['lng'] = $addressGeoCode['lng'];

        $requestObject = $this->createShipmentObject($data);

        $this->APIRequest($requestObject, $this->placeOrderURL, function ($request_object, $result, $order_id) {
            $request_object['catalyst_id'] = $result['id'];
            $this->load->model('shipping/catalyst');
            $this->model_shipping_catalyst->addShipmentDetails($request_object);
        });
    }
    /**
     * Cancel catalyst order
     */
    public function cancelShipment()
    {
        $this->language->load('shipping/catalyst');

        $requestObject = [
            'orderId' => $this->request->post['catalyst_id'],
            'notes' => $this->request->post['catalyst_cancel_notes']
        ];
        $url = sprintf($this->cancelOrderStatusURL, $this->request->post['catalyst_id']);
        $this->APIRequest($requestObject, $url, function ($request_object, $result, $order_id) {
            $this->load->model('shipping/catalyst');
            $this->model_shipping_catalyst->deleteShipment($order_id);
        });
    }

    /**
     * Update shipment order status
     */
    public function updateOrderStatus($order_id, $order_status, $catalyst_id)
    {
        $this->language->load('shipping/catalyst');
        $requestObject = [
            'status' => $order_status
        ];
        $url = sprintf($this->orderStatusURL, $catalyst_id);
        $this->APIRequest($requestObject, $url, function ($request_object, $result, $order_id) {
            $this->load->model('shipping/catalyst');
            $this->model_shipping_catalyst->updateShipmentStatus($order_id, $request_object['status']);
        });
    }

    /**
     * Get geocode from literal address
     */
    private function getGeocode($address, $coordinates)
    {
        $coordinatesData = [
            'lat' => 0,
            'lng' => 0
        ];

        $coordinates = explode(',', $coordinates);

        if (sizeof($coordinates) === 2) {
            $coordinatesData['lat'] = trim($coordinates[0]);
            $coordinatesData['lng'] = trim($coordinates[1]);
            return $coordinatesData;
        }

        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($address).'&key='.$this->config->get('catalyst_google_api_key');

        $ch = curl_init($url); 

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $result = json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', curl_exec($ch)), true);

        if (!curl_errno($ch)) {
            if (!empty($result['results'])) {
                $coordinatesData['lat'] = $result['results'][0]['geometry']['location']['lat'];
                $coordinatesData['lng'] = $result['results'][0]['geometry']['location']['lng'];
            }
        }

        curl_close($ch);

        return $coordinatesData;
    }

    /**
     * Forming the order json object that will be sent to catalyst
     */
    private function createShipmentObject($order)
    {
        return [
            'number' => $order['order_id'],
            'customerName' => $order['catalyst_customer_name'],
            'customerLang' => $order['catalyst_customer_language'],
            'customerPhone' => $order['catalyst_customer_phone'],
            'promiseTime' => (int) $this->config->get('catalyst_promise_time'),
            'preparationTime' => (int) $this->config->get('catalyst_preparation_time'),
            'totalPrice' => (float) $order['catalyst_total_price'],
            'branchId' => $this->config->get('catalyst_branch_id'),
            'timeOfOrder' => date('Y-m-d\TH:i:s'),
            'paymentMethod' => (int) $order['catalyst_payment_method'],
            'address' => [
                'coordinates' => [
                    'lat' => $order['lat'],
                    'lng' => $order['lng']
                ],
                'name' => $order['catalyst_address']['name'],
                'notes' => $order['catalyst_address']['notes'],
                'address' => $order['catalyst_address']['address']
            ],
            'notes' => $order['catalyst_notes']
        ];
    }

    /**
     * Get authorization token for catalyst api request
     */
    private function getCatalystAuthToken()
    {
        $token = $this->config->get('catalyst_auth_object');
        if (!empty($token)) {
            if (time() < $token['expire']) {
                return $token['access_token'];
            }
        }

        $ch = curl_init($this->acquireTokenURL);      

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'client_id' => $this->config->get('catalyst_client_id'),
            'client_secret' => $this->config->get('catalyst_client_secret')
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $result = json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', curl_exec($ch)), true);

        if (curl_errno($ch)) {
            $messages['errors'] = [
                'warning' => $this->language->get('catalyst_error_api'),
                'API' => $this->language->get('catalyst_error_generic')
            ];
        } else {
            curl_close($ch);
            if (isset($result['access_token'])) {
                $this->load->model('setting/setting');
                $result['expire'] = time() + (int) $result['expires_in'];
                $this->model_setting_setting->editSetting('catalyst_auth', ['catalyst_auth_object' => $result]);
                return $result['access_token'];
            } else {
                $messages['errors'] = [
                    'warning' => $this->language->get('catalyst_error_api'),
                    'API' => $this->language->get('catalyst_error_client_id_or_secret')
                ];
            }
        }
        $this->response->setOutput(json_encode($messages));
        return false;
    }

    /**
     * Perform catalyst api call
     */
    private function APIRequest($request_object, $url, $callback)
    {
        $token = $this->getCatalystAuthToken();

        if (!$token) {
            return;
        }

        $ch = curl_init($url);      

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_object));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]);

        $result = json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', curl_exec($ch)), true);

        if (curl_errno($ch)) {
            $messages['errors'] = [
                'warning' => $this->language->get('catalyst_error_api'),
                'API' => $this->language->get('catalyst_error_generic')
            ];
        } else {
            if (!isset($result['isSuccess'])) {
                $messages['errors'] = [
                    'warning' => $this->language->get('catalyst_error_api'),
                    'API' => $result['innerException']['exceptionMessage'] ?? $result['message']
                ];
            } else {
                if (!$result['isSuccess']) {
                    $messages['errors'] = [
                        'warning' => $this->language->get('catalyst_error_api'),
                        'API' => $result['error']
                    ];
                } else {
                    $callback($request_object, $result, $this->request->request['order_id']);
                    $messages['success'] = '1';
                    $messages['success_msg'] = '';
                    $messages['redirect'] = 1;
                    $messages['to'] = $this->request->server['HTTP_REFERER'];
                }
            }
        }

        curl_close($ch);

        $this->response->setOutput(json_encode($messages));
    }

    /**
     * Validate order input data
     */
    private function validate($data) {

        if (empty($data['catalyst_customer_name'])) {
            $this->errors['name'] = $this->language->get('catalyst_customer_name') . ' ' . $this->language->get('catalyst_required');
        }

        if (empty($data['catalyst_customer_phone'])) {
            $this->errors['phone'] = $this->language->get('catalyst_customer_phone') . ' ' . $this->language->get('catalyst_required');
        }

        if (!preg_match('/^\+?[0-9]+$/', $data['catalyst_customer_phone'])) {
            $this->errors['phone-num'] = $this->language->get('catalyst_customer_phone_should_be_number');
        }

        if ($data['catalyst_payment_method'] == -1) {
            $this->errors['payment-method'] = $this->language->get('catalyst_payment_method') . ' ' . $this->language->get('catalyst_required');
        }

        if (empty($data['catalyst_total_price'])) {
            $this->errors['payment-total'] = $this->language->get('catalyst_total_price') . ' ' . $this->language->get('catalyst_required');
        }

        return empty($this->errors);
    }

    /**
     * Validate order status code input
     */
    private function validateOrderStatus() {

        if (!in_array($this->request->post['catalyst_order_status'], range(0, 6))) {
            $this->errors['order-status'] = $this->language->get('catalyst_order_status_not_valid');
        }

        return empty($this->errors);
    }

}
