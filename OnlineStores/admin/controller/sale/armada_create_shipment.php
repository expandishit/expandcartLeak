<?php

class ControllerSaleArmadaCreateShipment extends Controller {

    private $errors = [];

    public function index() {
        
        $this->load->model('sale/order');
        $this->load->model('shipping/armada');
        $this->language->load('shipping/armada');
        $this->load->model('localisation/zone');

        if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}
		
		$order_info = $this->model_sale_order->getOrder($order_id);

        if ($order_info) {

            $shipmentDetails = $this->model_shipping_armada->getShipmentDetails($order_id);

            if (empty($shipmentDetails)) {

                if ($this->request->post) {
    
                    if (!$this->validate()) 
                    {
                        $errorsData = ['warning' => $this->language->get('armada_error_api')];
                        $errorsData = array_merge($errorsData, $this->errors);
                        $this->response->setOutput(json_encode(['errors' => $errorsData]));
                        return;
                    }
    
                    foreach($this->request->post as $key => $value) {
                        $this->request->post[$key] = trim($value);
                    }
    
                    $this->APIRequest($this->createShipmentObject($order_info['order_id']), $order_info['order_id'], $order_info['order_status_id']);
    
                    return;
                }

            }


            $order_info['total'] = (float) $order_info['total'];

            $this->document->setTitle($this->language->get('armada_title'));

            $this->data['order_info'] = $order_info;
            $this->data['products'] = $this->model_sale_order->getOrderProducts($this->request->get['order_id']);

            if (!empty($shipmentDetails)) {
                $this->data['shipment_details'] = $shipmentDetails;
            } else {
                $this->data['order_zone_id'] = $order_info['shipping_zone_id']; 
                $this->data['order_area_id'] = $order_info['shipping_area_id']; 
                $this->data['language_code'] = $this->config->get('config_admin_language');
                $this->data['zones'] = $this->model_localisation_zone->getZonesByCountryId($order_info['shipping_country_id']);
            }

            $this->data['breadcrumbs'] = array();
    
            $this->data['breadcrumbs'][] = array(
                'text'      => $this->language->get('text_home'),
                'href'      => $this->url->link('common/home', '', 'SSL'),
                'separator' => false
            );
    
            $this->data['breadcrumbs'][] = array(
                'text'      => $this->language->get('armada_create_shipment'),
                'href'      => $this->url->link('sale/order', '', 'SSL'),
                'separator' => ' :: '
            );
    
            $this->base = 'common/base';
            $this->template = 'sale/armada_create_shipment.expand';
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

    private function createShipmentObject($order_id)
    {
        return json_encode([
            'platformName' => $this->config->get('armada_platform_name'),
            'platformData' => [
                'orderId'           => $order_id,
                'name'              => $this->request->post['armada_customer_name'],
                'phone'             => $this->request->post['armada_customer_phone'],
                'area'              => $this->request->post['armada_customer_area'],
                'block'             => $this->request->post['armada_customer_block'],
                'street'            => $this->request->post['armada_customer_street'],
                'buildingNumber'    => $this->request->post['armada_customer_building'],
                'amount'            => $this->request->post['armada_payment_amount'],
                'paymentType'       => 'paid'
            ]
        ]);
    }

    private function APIRequest($data, $order_id, $order_status)
    {
        $url = 'https://api.armadadelivery.com/v0/deliveries';

        if ($this->config->get('armada_test_mode') == 1) {
            $url = 'https://api-simulation-env.herokuapp.com/v0/deliveries';
        }

        $ch = curl_init($url);      

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Key ' . $this->config->get('armada_api_key')
        ]);
        
        $result = json_decode(curl_exec($ch), true);

        if (curl_errno($ch)) {
            $messages['errors'] = [
                'warning' => $this->language->get('armada_error_api'),
                'API' => $this->language->get('armada_error_generic')
            ];
        } else {
            $messages = [];

            $statusCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

            if ($statusCode == 400 || $statusCode == 500) {
                $messages['errors'] = [
                    'warning' => $this->language->get('armada_error_api'),
                    'API' => isset($result['internalCode']) ? $result['internalCode'] : $this->language->get('armada_error_generic')
                ];
            } else if ($statusCode == 401 || $statusCode == 403) {
                $messages['errors'] = [
                    'warning' => $this->language->get('armada_error_api'),
                    'API' => $this->language->get('armada_error_auth')
                ];
            } else {
                $this->model_shipping_armada->addShipmentDetails($order_id, $result, $order_status);
                $messages['success'] = 'success';
                $messages['success_msg'] = '';
                $messages['redirect'] = 1;
                $messages['to'] = $this->request->server['HTTP_REFERER'];
            }
    
        }

        curl_close($ch);

        $this->response->setOutput(json_encode($messages));
    }

    private function validate() {

        if (empty($this->request->post['armada_customer_name'])) {
            $this->errors['name'] = $this->language->get('armada_customer_name') . ' ' . $this->language->get('armada_required');
        }

        if (empty($this->request->post['armada_customer_phone'])) {
            $this->errors['phone'] = $this->language->get('armada_customer_phone') . ' ' . $this->language->get('armada_required');
        }

        if (!preg_match('/^\+?[0-9]+$/', $this->request->post['armada_customer_phone'])) {
            $this->errors['phone-num'] = $this->language->get('armada_customer_phone_should_be_number');
        }

        if (empty($this->request->post['armada_customer_area'])) {
            $this->errors['address'] = $this->language->get('armada_customer_area') . ' ' . $this->language->get('armada_required');
        }

        if (empty($this->request->post['armada_customer_block'])) {
            $this->errors['city'] = $this->language->get('armada_customer_block') . ' ' . $this->language->get('armada_required');
        }

        if (empty($this->request->post['armada_customer_street'])) {
            $this->errors['country'] = $this->language->get('armada_customer_street') . ' ' . $this->language->get('armada_required');
        }

        if (empty($this->request->post['armada_customer_building'])) {
            $this->errors['payment-method'] = $this->language->get('armada_customer_building') . ' ' . $this->language->get('armada_required');
        }

        if (empty($this->request->post['armada_payment_amount']) || !is_numeric($this->request->post['armada_payment_amount'])) {
            $this->errors['payment-amount'] = $this->language->get('armada_payment_amount') . ' ' . $this->language->get('armada_required');
        }

        return empty($this->errors);
    }
    
    
    public function getZoneAreas() {
        $this->load->model('localisation/area');
        $data = $this->model_localisation_area->getAreasMuilteLangBasedOnZone($this->request->get['zone_id']);
        $this->response->setOutput(json_encode($data));
    }

}
