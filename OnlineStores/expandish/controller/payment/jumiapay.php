<?php

class ControllerPaymentJumiaPay extends Controller{

	/**
	* @const strings Gateway Name.
	*/
    const GATEWAY_NAME = 'jumiapay';
    const BASE_API_TESTING_URL    = 'https://api-sandbox-pay.jumia.com.eg';
    const BASE_API_PRODUCTION_URL = 'https://api-pay.jumia.com.eg';


	public function index(){
		$this->language->load_json('payment/' . self::GATEWAY_NAME);
		$this->data['action'] = 'index.php?route=payment/' . self::GATEWAY_NAME . '/pay';

		if (isset($this->session->data['error_jumiapay'])) {
		  $this->data['error_jumiapay'] = $this->session->data['error_jumiapay'];
		  unset($this->session->data['error_jumiapay']);
		}

		$this->template = 'default/template/payment/' . self::GATEWAY_NAME . '.expand';
		$this->render_ecwig();
    }


	public function pay(){
        unset($this->session->data['error_jumiapay']);

		$order_id = $this->session->data['order_id'];
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($order_id);

		$this->language->load_json('payment/' . self::GATEWAY_NAME);

		$settings = $this->config->get('jumiapay');

		$reference_id = $this->session->data['jumiapay_reference_id'] = "#order-3$order_id";

		$this->load->model('localisation/country');

		$request_body = '{
		    "description": "JumiaPay order from ExpandCart",
		    "amount": {
		        "value": ' . $this->currency->convertUsingRatesAPI($order_info['total'],$order_info['currency_code'], $settings['account_currency'] ) .',
		        "currency": "' . $order_info['currency_code'] . '"
		    },
		    "merchant": {
		        "referenceId": "' . $reference_id . '",
		        "callbackUrl": "'. $this->url->link('payment/jumiapay/callback') .'",
		        "returnUrl":  "'. $this->url->link('payment/jumiapay/return') .'"
		    },
		    "consumer": {
		        "emailAddress": "' . $order_info['email'] . '",
		        "ipAddress": "' . $_SERVER['REMOTE_ADDR'] . '",
				"country": "' . $this->model_localisation_country->getCountry($order_info['payment_country_id'])['iso_code_2'] . '",
				"mobilePhoneNumber": "' . $order_info['payment_telephone'] . '",
		        "language": "' . $this->config->get('config_language') . '",
		        "firstName": "' . $order_info['firstname'] . '",
		        "lastName": "' . $order_info['lastname'] . '",
		        "name": "' . $order_info['firstname'] . ' ' . $order_info['lastname'] . '"		       
		    },
		    "basket": {
		        "items": ' . json_encode($this->getItems($order_id)) . ',
		        "shippingAmount": "0"
		    }
		}';

		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => $this->_getBaseUrl().'/v2/merchants/'. $settings['shop_config_id'] .'/purchases',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS => $request_body,
		  CURLOPT_HTTPHEADER => array(
		    'apikey: '.$settings['api_key'],
		    'Content-Type: application/json'
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		
		$response = json_decode($response, true);

		if ( $response['status'] == 'PENDING' && !isset($response['details']['error']) ) {
			$this->session->data['jumiapay_purchase_id'] = $response['purchaseId'];
		    $result_json['url'] = $response['links'][0]['href'];
        	$result_json['success'] = '1';
		}
		else if ( $response['status'] != 'PENDING' && !empty($response['details'][0]['error']) ) {
		    $result_json['error_jumiapay'] = self::GATEWAY_NAME . ": " . $response['details'][0]['error'];
        	$result_json['success'] = '0';
		}
		else{
			$result_json['error_jumiapay'] = "An error occured while processing jumiapay gateway, please contact us for more details.";
        	$result_json['success'] = '0';
		}

	    $this->response->setOutput(json_encode($result_json));
    }


    public function callback()
    {
        $log = new Log('jumiapay-callback'.time().'.json');
        $log->write('test callback');

		$log->write(json_encode($_REQUEST));
		$log->write(file_get_contents('php://input'));
	}

	public function return()
	{
		//paymentStatus=success
		//paymentType=secureCreditCard
		//paymentMethod=Visa
        $log = new Log('jumiapay-return'.time().'.json');
        $log->write('test return');
		$log->write(json_encode($_REQUEST));

		#Add record in payment_transactions table

		//Get Order info
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);    
		
		//Add a transaction
		$this->initializer([
            'paymentTransaction' => 'extension/payment_transaction',
            'paymentMethod'      => 'extension/payment_method',
        ]);

        $this->paymentTransaction->insert([
            'order_id'           => $this->session->data['order_id'], 
            'transaction_id'     => $this->session->data['jumiapay_purchase_id'],
            'payment_gateway_id' => $this->paymentMethod->selectByCode('jumiapay')['id'],
            'payment_method'     => 'JumiaPay',
            'status'             => $this->request->get['paymentStatus'],
            'amount'             => number_format($order_info['total'], 2, '.', ''),
            // 'amount'             => number_format((float)$this->currency->convert($order_info['total'], $this->config->get('config_currency'), $order_info['currency_code']), 2, '.', ''),
            'currency'           => $order_info['currency_code'],
            'details'            => json_encode([
            	'jumiapay_reference_id' => $this->session->data['jumiapay_reference_id'],
            	'jumiapay_purchase_id'  => $this->session->data['jumiapay_purchase_id'],
            	'request' => json_encode($this->request->get)
            ])
        ]);

		if($this->request->get['paymentStatus'] == 'success'){
			$this->success();
		}
		else{
			$this->failure();
		}
	}


    public function success(){
        unset($this->session->data['error_jumiapay']);

        $this->load->model('checkout/order');
        $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('jumiapay')['complete_status_id']);
        //redirect to success page...
        $this->response->redirect($this->url->link('checkout/success', '', true));
    }

    public function failure(){
        $this->session->data['error_jumiapay'] = var_export($this->request->get, 1);
        $this->response->redirect($this->url->link('checkout/checkout', '', true));
    }

    private function getItems($order_id)
    {
    	$items = [];
    	$this->load->model('checkout/order');
        $products = $this->model_checkout_order->getOrderProducts($order_id);    
		foreach($products as $product){
			$items[] = [
				'name'     => $product['name'],
				'amount'   => $product['price'],
				'quantity' => $product['quantity']
			];
		}
		return $items;
    }
	private function _getBaseUrl(){
    	//Check current API Mode..
    	$debugging_mode = $this->config->get('jumiapay')['debugging_mode'];
    	return ( isset($debugging_mode) && $debugging_mode == 1 ) ? self::BASE_API_TESTING_URL : self::BASE_API_PRODUCTION_URL;
    }
}
