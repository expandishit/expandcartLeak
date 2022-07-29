<?php

class ControllerSaleOtoCreateShipment extends Controller {

    private $errors = [];

    public function index() {
        
        $this->load->model('sale/oto');
        $this->load->model('sale/order');
        $this->load->model('localisation/country');

        $this->language->load('shipping/oto');

        if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}
		
		$order_info = $this->model_sale_order->getOrder($order_id);

        if ($order_info) {

            $orderProducts = $this->model_sale_order->getOrderProducts($this->request->get['order_id']);

            if ($this->request->post) {

                if (!$this->validate()) 
                {
                    $errorsData = ['warning' => $this->language->get('oto_api_error')];
                    $errorsData = array_merge($errorsData, $this->errors);
                    $this->response->setOutput(json_encode(['errors' => $errorsData]));
                    return;
                }

                $otoItems = [];

                foreach ($orderProducts as $orderProduct) {
                    $otoItems[] = [
                        'name'      => $orderProduct['name'],
                        'price'     => $orderProduct['price'],
                        'quantity'  => $orderProduct['quantity'],
                        'sku'       => $orderProduct['original_id'],
                        'image'     => \Filesystem::getUrl('image/' . $orderProduct['image'])
                    ];
                }

                $otoShipment = [
                    'orderId' => $order_info['order_id'],
                    'customer' => [
                        'name'      => $this->request->post['oto_customer_name'],
                        'email'     => $this->request->post['oto_customer_email'],
                        'mobile'    => $this->request->post['oto_customer_phone'],
                        'address'   => $this->request->post['oto_customer_address'],
                        'city'      => $this->request->post['oto_customer_city'],
                        'country'   => $this->request->post['oto_customer_country'],
                    ],
                    'items'             => $otoItems,
                    'payment_method'    => $this->request->post['oto_payment_method'],
                    'amount'            => (float) $this->request->post['oto_payment_amount'],
                    'amount_due'        => $this->request->post['oto_payment_method'] === 'cod' ? (float) $this->request->post['oto_payment_amount'] : 0,
                    'currency'          => $this->request->post['oto_payment_currency'],
                    'maxPackageSize'    => '',
                    'packageCount'      => '',
                    'shippingNotes'     => '',
                    'retailerId'        => $this->config->get('oto_retailer_id'),
                    'retailerToken'     => $this->config->get('oto_retailer_token')
                ];

                $this->APIRequest($otoShipment, 'createOrder');

                return;
            }

            $order_info['total'] = (float) $order_info['total'];

            $this->document->setTitle($this->language->get('oto_title'));

            $this->data['oto_id'] = $this->model_sale_oto->getOtoId($order_id);

            $this->data['order_info'] = $order_info;

            $this->data['countries'] = $this->model_localisation_country->getCountries();

            $this->data['products'] = $orderProducts;

            $this->data['oto_cancel_order'] = $this->url->link('sale/oto_create_shipment/cancelOrder', null, 'SSL');

            $this->data['oto_track_order'] = $this->url->link('sale/oto_create_shipment/trackOrder', null, 'SSL');

            $this->data['breadcrumbs'] = array();
    
            $this->data['breadcrumbs'][] = array(
                'text'      => $this->language->get('text_home'),
                'href'      => $this->url->link('common/home', '', 'SSL'),
                'separator' => false
            );
    
            $this->data['breadcrumbs'][] = array(
                'text'      => $this->language->get('oto_create_shipment'),
                'href'      => $this->url->link('sale/order', '', 'SSL'),
                'separator' => ' :: '
            );
    
            $this->base = 'common/base';
            $this->template = 'sale/oto_create_shipment.expand';
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

    public function cancelOrder()
    {
        $this->APIRequest($this->prepareOtoObject(), 'cancelOrder');
    }

    public function trackOrder()
    {
        $this->APIRequest($this->prepareOtoObject(), 'orderStatus');
    }

    private function prepareOtoObject() {
        return [
            'orderId'       => $this->request->post['order_id'],
            'retailerId'    => $this->config->get('oto_retailer_id'),
            'retailerToken' => $this->config->get('oto_retailer_token')
        ];
    }

    private function APIRequest($data, $endpoint)
    {
        $this->load->model('sale/oto');

        $this->language->load('shipping/oto');

        $order_id = $data['orderId'];

        $data = json_encode($data);

        $ch = curl_init('https://login.tryoto.com/service/rest/v1/' . $endpoint);      

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);                                                                  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);          
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);         

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            $messages['errors'] = [
                'warning' => $this->language->get('oto_api_error'),
                'API' => $this->language->get('oto_generic_error')
            ];
        } else {
            $result = json_decode($result, true);

            $messages = [];
    
            if (!$result['success']) {
                $apiError = '';
                switch ($result['errorCode']) {
                    case 1:
                        $apiError = $this->language->get('oto_retailer_id_and_token_empty');
                    break;
                    case 2:
                        $apiError = $this->language->get('oto_retailer_id_and_token_not_matching');
                    break;
                    case 3:
                        $apiError = $this->language->get('oto_retailer_not_active');
                    break;
                    case 4:
                        $apiError = $this->language->get('oto_shipment_already_exists');
                    break;
                    default:
                        $apiError = $this->language->get('oto_generic_error');
                    break;
                }
                $messages['errors'] = [
                    'warning' => $this->language->get('oto_api_error'),
                    'API' => $apiError
                ];
            } else {
                $messages['success'] = 'success';
                $messages['success_msg'] = '';
                if ($endpoint == 'createOrder' && !empty($result['otoId'])) {
                    $this->model_sale_oto->insertOtoId($order_id, $result['otoId']);
                } else if ($endpoint == 'cancelOrder') {
                    $this->model_sale_oto->deleteOrder($order_id);
                } else if ($endpoint == 'orderStatus') {
                    $messages['success_msg'] = $this->language->get('oto_order_status_' . strtolower(implode('_', preg_split('/(?=[A-Z])/', $result['status']))));
                }
            }
    
        }

        curl_close($ch);

        $this->response->setOutput(json_encode($messages));
    }

    private function validate() {

        if (empty($this->request->post['oto_customer_name'])) {
            $this->errors['name'] = $this->language->get('oto_customer_name') . ' ' . $this->language->get('oto_required');
        }

        if (empty($this->request->post['oto_customer_phone'])) {
            $this->errors['phone'] = $this->language->get('oto_customer_phone') . ' ' . $this->language->get('oto_required');
        }

        if (!preg_match('/^\+?[0-9]+$/', $this->request->post['oto_customer_phone'])) {
            $this->errors['phone-num'] = $this->language->get('oto_customer_phone_should_be_number');
        }

        if (empty($this->request->post['oto_customer_address'])) {
            $this->errors['address'] = $this->language->get('oto_customer_address') . ' ' . $this->language->get('oto_required');
        }

        if (empty($this->request->post['oto_customer_city'])) {
            $this->errors['city'] = $this->language->get('oto_customer_city') . ' ' . $this->language->get('oto_required');
        }

        if (empty($this->request->post['oto_customer_country'])) {
            $this->errors['country'] = $this->language->get('oto_customer_country') . ' ' . $this->language->get('oto_required');
        }

        if (empty($this->request->post['oto_payment_method'])) {
            $this->errors['payment-method'] = $this->language->get('oto_payment_method') . ' ' . $this->language->get('oto_required');
        }

        if (empty($this->request->post['oto_payment_amount']) || !is_numeric($this->request->post['oto_payment_amount'])) {
            $this->errors['payment-amount'] = $this->language->get('oto_payment_amount') . ' ' . $this->language->get('oto_required');
        }

        if (empty($this->request->post['oto_payment_currency'])) {
            $this->errors['payment-currency'] = $this->language->get('oto_payment_currency') . ' ' . $this->language->get('oto_required');
        }
        return empty($this->errors);
    }

}
