<?php

use ExpandCart\Foundation\PaymentController;

class ControllerPaymentTap2 extends PaymentController
{
    protected static $isExternalPayment = true;
	
    public function __construct($registry)
    {
        parent::__construct($registry);
        
        $this->data['is_external'] = $this->data['is_external'] && $this->config->get('tab2_checkout_mode') === 'page'; 
    }
    
	public function index() {
        $this->language->load_json('payment/tap2');

        $this->data['pay'] = $this->language->get('pay');
        $this->data['pay_hint'] = $this->language->get('pay_hint');
        
        $this->load->model('payment/tap2');        
        $this->data['ask_customer_to_save_his_cart'] = $this->customer->getId() && $this->model_payment_tap2->getTap2CustomerId($this->customer->getId()) == '' && $this->config->get('tap2_kfast_enabled');

        unset($this->session->data['tap2_success']);
        unset($this->session->data['tap2_cancelled']);

		// if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/tap2.expand')) {
		// 	$this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/tap2.expand';
		// } else {
		// 	$this->template = 'default/template/payment/tap2.expand';
		// }
        $this->template = 'default/template/payment/tap2.expand';
		$this->render_ecwig();

	}

    public function getOrder() {
        
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $order_info['tap2_api_publishable_key'] = $this->config->get('tap2_api_publishable_key');
        $order_info['tap2_checkout_mode'] = $this->config->get('tab2_checkout_mode') ?: 'page'; // popup
        $order_info['language_code']      = $this->config->get('config_language');
        $order_info['phone_number']       = str_replace('' . $order_info['payment_phonecode'], '', $order_info['payment_telephone']);
        $order_info['redirect_url']       = $this->url->link('payment/tap2/callback', '', 'SSL');
        $order_info['tap2_kfast_enabled'] = $this->config->get('tap2_kfast_enabled');
        if($order_info['total'] && $order_info['currency_code'])
            $order_info['total'] = $this->currency->convert(
                                    $order_info['total'] ,
                                    $this->config->get('config_currency') ,
                                    $order_info['currency_code']);

        //if user logged in, get his tap2 customer id or empty string..
		$this->load->model('payment/tap2');
        $order_info['tap2_customer_id']   = $this->model_payment_tap2->getTap2CustomerId($this->customer->getId());
        $this->response->setOutput(json_encode($order_info));
    }

	public function callback() {

		$this->language->load_json('payment/tap2');

		if (isset($this->session->data['tap2_success'])) {
			$this->response->redirect($this->url->link('checkout/success'));
			return;
		}

		/*
		 * This commented because it always set to true
		 *
		 * if (isset($this->session->data['tap2_fail'])) {
			$this->data['text_message'] = $this->language->get('text_failed');
			$this->response->redirect($this->url->link('checkout/error'));
			return;
		}*/

		if (isset($this->session->data['tap2_cancelled'])) {
			$this->data['text_message'] = $this->language->get('text_cancelled');
			$this->response->redirect($this->url->link('checkout/error'));
			return;
		}

		$this->load->model('payment/tap2');
		$this->load->model('checkout/order');
		$this->initializer([
            'paymentTransaction' => 'extension/payment_transaction',
			'paymentMethod' => 'extension/payment_method',
		]);

		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => 'https://api.tap.company/v2/charges/' . $this->request->get['tap_id'],
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_HTTPHEADER => [
				'Authorization: Bearer ' . $this->config->get('tap2_api_secret_key')
			],
		]);

		$jsonResponse = curl_exec($curl);

		$response = json_decode($jsonResponse, true);
		$err = curl_error($curl);

		curl_close($curl);

		if (isset($response['errors'])) {
			$this->session->data['tap2_cancelled'] = true;
			$this->response->redirect($this->url->link('checkout/error'));
			return;
		}

		if (!$err) {
			$statues = $this->model_payment_tap2->getStatuses();
			$order_id = $response['metadata']['order_id'];
			if (in_array($response['status'], $statues['success'])) {

                $paymentMethod = isset($response['source']['payment_method']) ? $response['source']['payment_method'] : '';

                $this->paymentTransaction->insert([
                    'order_id' => $order_id,
                    'transaction_id' => $response['id'],
                    'payment_gateway_id' => $this->paymentMethod->selectByCode('tap2')['id'],
                    'payment_method' => $paymentMethod,
                    'status' => 'Success',
                    'amount' => $response['amount'],
                    'currency' => $response['currency'],
                    'details' => $jsonResponse,
                ]);

                //save card
                if( $this->session->data['tap2_save_card_option'] && isset($response['customer']['id']) ){
                    $this->model_payment_tap2->saveCustomerId($response['customer']['id']);
                }

				$this->model_checkout_order->confirm($order_id, $this->config->get('tap2_complete_status_id'), 'Processing', true);
				$this->session->data['tap2_success'] = true;
				$this->response->redirect($this->url->link('checkout/success'));
			} elseif (in_array($response['status'], $statues['fail'])) {

                $paymentMethod = isset($response['source']['payment_method']) ? $response['source']['payment_method'] : '';

                $this->paymentTransaction->insert([
                    'order_id' => $order_id,
                    'transaction_id' => '',
                    'payment_gateway_id' => $this->paymentMethod->selectByCode('tap2')['id'],
                    'payment_method' => $paymentMethod,
                    'status' => 'Failed',
                    'amount' => $response['amount'],
                    'currency' => $response['currency'],
                    'details' => $jsonResponse,
                ]);

				$this->model_checkout_order->confirm($order_id, $this->config->get('tap2_denied_status_id'), 'Failed', true);
				$this->session->data['tap2_fail'] = true;
				$this->response->redirect($this->url->link('checkout/error'));
			}
			return;
		}
		$this->data['error_warning'] = $this->language->get('text_failed');
		$this->response->redirect($this->url->link('checkout/checkout'));
	}

    public function setSaveCardOption(){
        $this->session->data['tap2_save_card_option'] = $this->request->post['save_card'];
    } 
}
