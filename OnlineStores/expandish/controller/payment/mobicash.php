<?php

class ControllerPaymentMobiCash extends Controller{

	/**
	* @const strings Gateway Name.
	*/
    const GATEWAY_NAME = 'mobicash';
    const BASE_API_URL = 'http://160.19.101.26:8888/OnlinePayment/';

	public function index(){
		$this->language->load_json('payment/' . self::GATEWAY_NAME);
		$order_id = $this->session->data['order_id'];
		
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($order_id);

		$this->data['action']   = 'index.php?route=payment/' . self::GATEWAY_NAME . '/pay';
		$this->data['order_id'] = "#" . $this->session->data['order_id'];
		$this->data['total']    = $this->currency->format($order_info['total']);
		$this->data['store_name'] = $this->config->get('config_name');

		$this->template = 'default/template/payment/' . self::GATEWAY_NAME . '.expand';
		$this->render_ecwig();
    }


	public function pay(){
    	unset($this->session->data['error_mobicash']);
		$this->language->load_json('payment/' . self::GATEWAY_NAME);

		$identity_card = $this->request->post['identity_card'];
		
		if( empty($identity_card) ){
			$result_json['error_mobicash'] = $this->language->get('error_customer_id_card');
        	$result_json['success'] = '0';
	    	$this->response->setOutput(json_encode($result_json));
			return;
		}
        
        $settings = $this->config->get('mobicash');

		$order_id = $this->session->data['order_id'];
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($order_id);
		
		//Pay
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => self::BASE_API_URL . 'api/OnlinePaymentServices/OpenSession',
		  //For testing purpose only
		  // CURLOPT_PROXY => '165.16.27.35:1981',
		  CURLOPT_PROXY => '165.16.60.1:8080',
		  CURLOPT_SSL_VERIFYPEER => false,
		  CURLOPT_SSL_VERIFYHOST => false,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS => json_encode([
		        'merchantId' => $settings['merchant_id'],
		        'providerId' => $settings['provider_id'],
		        'amount'     => $this->currency->convertUsingRatesAPI($order_info['total'], $this->config->get('config_currency'), $settings['account_currency']),
		        // 'amount'     => 1.0,
		        'identityCard'  => $identity_card,
		        'transactionId' => $order_id,
		        'posId' => 0,
		        'onlineOperation' => 1
		  ]),		  
		  CURLOPT_HTTPHEADER => array(
		    'Content-Type: application/json',
		  ),
		));

		$response = curl_exec($curl);
		if(curl_errno($curl)){
		    throw new Exception(curl_error($curl));
		}
		$log = new Log('mobicash-'.time().'.json');
		$log->write($response);
		$response = json_decode($response, true);

		curl_close($curl);

		if( $response['operationState'] == 1){
			$result_json['message'] = self::GATEWAY_NAME . ': Payment Succeed';
			$result_json['url']     = $this->url->link('payment/mobicash/success', 'transaction_id='.$response['mitfTransactionId'], true);
        	$result_json['success'] = '1';
		}
		else{
			$result_json['error_mobicash'] = $this->language->get('error_payment_failed');
        	$result_json['success'] = '0';
		}

	    $this->response->setOutput(json_encode($result_json));
    }

    public function success(){
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);    

		$this->initializer([
            'paymentTransaction' => 'extension/payment_transaction',
            'paymentMethod'      => 'extension/payment_method',
        ]);

        $this->paymentTransaction->insert([
            'order_id'           => $this->session->data['order_id'],
            'transaction_id'     => $this->request->get['transaction_id'],
            'payment_gateway_id' => $this->paymentMethod->selectByCode('mobicash')['id'],
            'payment_method'     => 'mobicash',
            'status'             => 'Success',
            'amount'             => number_format((float)$this->currency->convert($order_info['total'], $this->config->get('config_currency'), $order_info['currency_code']), 2, '.', ''),
            'currency'           => $order_info['currency_code'],
            'details'            => json_encode([]),
        ]);

    	$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get(self::GATEWAY_NAME)['complete_status_id']);
    	$this->response->redirect($this->url->link('checkout/success', '', true));
    }    
}


