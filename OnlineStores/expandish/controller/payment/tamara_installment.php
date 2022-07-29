<?php
/*
 *  Tamara - Pay By Installment ..
 */

class ControllerPaymentTamaraInstallment extends Controller {
    /**
    * @var constant
    */
    const GATEWAY_NAME = 'tamara_installment';

    const BASE_API_TESTING_URL    = 'https://api-sandbox.tamara.co';
    const BASE_API_PRODUCTION_URL = 'https://api.tamara.co';

    public function __construct($registry) {
        parent::__construct($registry);
        $this->initializer([
            'model_payment_tamara' => 'payment/tamara',
        ]);
    }

    //Pre-checkout
    public function index(){
      $this->language->load_json('payment/' . self::GATEWAY_NAME);

	    $this->data['action'] = $this->url->link('payment/tamara_installment/pay');

      if (isset($this->session->data['error_tamara'])) {
          $this->data['error_tamara'] = $this->session->data['error_tamara'];
      }

      $this->template = 'default/template/payment/tamara.expand';
      $this->render_ecwig();
    }

    public function pay(){

        $this->language->load_json('payment/' . self::GATEWAY_NAME);

        //Create Tamara Checkout session            
        $settings   = $this->config->get('tamara_installment');
        $api_token  = $settings['api_token'];

        //Get order info
        $order_id = $this->session->data['order_id'];
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);

        //validate phone number for SAU and UAE
        if(!$this->validatePhoneNumber($order_info['payment_iso_code_3'], $order_info['payment_telephone'])){
            $result_json['message'] = $this->language->get('invalid_'.strtolower($order_info['payment_iso_code_3']).'_phone_format');
            $result_json['success'] = '0';
            $this->response->setOutput(json_encode($result_json));
            return;
        }

        $this->load->model('localisation/country');
        $payment_country_code = $this->model_localisation_country->getCountry($order_info['payment_country_id'])['iso_code_2'];

        $shipping_cost = [];
        $taxes =  $this->cart->getTaxes();
        $this->load->model('total/shipping');
        $this->model_total_shipping->getTotal($shipping_cost, $temp_total, $taxes);

        $data = [
  			  'order_reference_id' => (string)$order_id,
  			  'total_amount' => [
            'amount'     => number_format((float)$this->currency->convert($order_info['total'], $this->config->get('config_currency'), $order_info['currency_code']), 2, '.', ''),
            'currency'   => $order_info['currency_code'],
  			  ],
  			  'description'  => 'Order description',
          'country_code' => $payment_country_code,
  			  'locale'       => $this->config->get('config_language') == 'ar' ? 'ar_SA' : 'en_US',
  			  'payment_type' => 'PAY_BY_INSTALMENTS',
          'items'        => $this->model_payment_tamara->getOrderItems($order_id, $this->config->get('config_currency'), $order_info['currency_code']),
  			  'consumer'     => [
  			    'first_name'    => $order_info['firstname'],
  			    'last_name'     => $order_info['lastname'],
  			    'phone_number'  => $order_info['payment_telephone'],
  			    'email'         => $order_info['email'] ?: 'test@test.com',
  			  ],
  			  'billing_address' => [
  			    'first_name'    => $order_info['payment_firstname'] ?: $order_info['firstname'],
  			    'last_name'     => $order_info['payment_lastname'] ?: $order_info['lastname'],
  			    'line1'         => $order_info['payment_address_1'] ?: '__',
                'country_code'  => $payment_country_code,
                'city'          => $order_info['payment_city'] ?: $order_info['payment_zone'] ?: $order_info['shipping_city'] ?: $order_info['shipping_zone'] ,
                'region'        => $order_info['payment_zone'] ?: $order_info['shipping_zone'],            
  			  ],
  			  'shipping_address' => [
                'first_name'     => $order_info['shipping_firstname'] ?: $order_info['firstname'],
                'last_name'      => $order_info['shipping_lastname'] ?: $order_info['lastname'],  			    
                'line1'          => $order_info['shipping_address_1'] ?: '__',
                'city'           => $order_info['shipping_city'] != '' ? $order_info['shipping_city'] : $order_info['payment_city'] ?: $order_info['payment_zone'] ?: $order_info['shipping_zone'] ,
                'country_code'   => $order_info['shipping_country_id'] ? $this->model_localisation_country->getCountry($order_info['shipping_country_id'])['iso_code_2'] : $payment_country_code,
                'region'         => $order_info['shipping_zone'] ?: $order_info['payment_zone'],
  			  ],
  			  'tax_amount'       => [
  			    'amount'         => 0,
            'currency'       => $order_info['currency_code'],
  			  ],
  			  'shipping_amount'  => [
            'amount'         => number_format((float)$this->currency->convert($shipping_cost[0]['value'], $this->config->get('config_currency'), $order_info['currency_code']), 2, '.', ''),
            'currency'       => $order_info['currency_code'],
  			  ],
  			  'merchant_url'     => [
  			    'success'        => $this->url->link('payment/tamara_installment/success'),
  			    'failure'        => $this->url->link('payment/tamara_installment/failure'),
  			    'cancel'         => $this->url->link('payment/tamara_installment/cancel'),
  			    'notification'   => $this->url->link('payment/tamara_installment/notification'), //a callback for order status changing
  			  ],
		    ];

        $url = $this->_getBaseUrl() . '/checkout';

    		$curl = curl_init($url);
    		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    		curl_setopt($curl, CURLOPT_POST, true);
    		curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($data));
    		curl_setopt($curl, CURLOPT_HTTPHEADER, [
    		  "Content-Type: application/json",
    		  "Authorization: Bearer $api_token"
    		]);
    		$response = curl_exec($curl);
    		curl_close($curl);
    		$response = json_decode($response, true);

    		//Payment Faild..
    		if( !empty($response['checkout_url']) ) { //Payment Success..
    			$this->session->data['tamara_order_id']    = $response['order_id'];
    			$this->session->data['tamara_checkout_id'] = $response['checkout_id'];
    	        $result_json['url']     = $response['checkout_url'];
    	        $result_json['success'] = '1';
    		}
       	else{
       		$error_message = $response['errors'][0]['error_code'] ? $this->language->get($response['errors'][0]['error_code']) : $response['message'];
       		$result_json['success'] = '0';
          $result_json['message'] = $this->language->get('text_tamara_error') . $error_message;
       	}
        $this->response->setOutput(json_encode($result_json));
    }
    
    //Authrized
    public function notification(){
        $settings   = $this->config->get('tamara');

        //Authenticate request with JWT Token & notification..
        if( !$this->model_payment_tamara->authenticate($settings['notification_token']) ){
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
              'message' => "Access denied!"
            ]));
            return;
        }
        
        //Check if Method not post
        if( $this->request->server['REQUEST_METHOD'] != 'POST' ) {
            //Send a 405 Method Not Allowed header.
            header($_SERVER["SERVER_PROTOCOL"]." 405 Method Not Allowed", true, 405);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
              'message' => "No route found - Method Not Allowed (Only POST)"
            ]));
            return;
        }

        //if post, get body json parameters...
        $request = json_decode(file_get_contents('php://input'), true);
        
        //if the payment is approved, Change our order status to approved and add new Payment Transaction
        if( strtolower($request['order_status']) == 'approved'){
            //Change Order status to APPROVED
            $tamara_statuses = $this->config->get('tamara_statuses');
            $this->model_payment_tamara->changeTamaraOrderStatus($request['order_reference_id'], $tamara_statuses['approved']['expandcartid']);

            //Add new transaction..    
            $this->load->model('checkout/order');
            $order_info = $this->model_checkout_order->getOrder($request['order_reference_id']);    

            $this->initializer([
                'paymentTransaction' => 'extension/payment_transaction',
                'paymentMethod'      => 'extension/payment_method',
            ]);

            $this->paymentTransaction->insert([
                'order_id'           => $request['order_reference_id'], //ExpandCart Order ID
                'transaction_id'     => $request['order_id'], //Tamara Order ID
                'payment_gateway_id' => $this->paymentMethod->selectByCode('tamara')['id'],
                'payment_method'     => 'Tamara',
                'status'             => 'Success',
                'amount'             => number_format((float)$this->currency->convert($order_info['total'], $this->config->get('config_currency'), $order_info['currency_code']), 2, '.', ''),
                'currency'           => $order_info['currency_code'],
                'details'            => json_encode([
                  'tamara_order_id'    => $request['order_reference_id'], //ExpandCart Order ID
                  'tamara_checkout_id' => $request['order_id'], //Tamara Order ID
                  'payment_type'       => "PAY_BY_INSTALMENTS"                  
                ]),
            ]);


            $api_token  = $settings['api_token'];

            //call Tamara authorise API
            $url = $this->_getBaseUrl() .'/orders/'. $request['order_id'] .'/authorise';
            $curl = curl_init();
            curl_setopt_array($curl, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $api_token"
              ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response, true);

            if( $response['status'] == "authorised" ){
                //Change Order status to Authorized..    
                $this->model_payment_tamara->changeTamaraOrderStatus($request['order_reference_id'], $tamara_statuses['authorized']['expandcartid']);
                
                $result_json['success'] = '1';
                $result_json['message'] = "Order status has been changed to authorized successfully";
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($result_json));
            }
            else{
                $result_json['success'] = '0';
                $result_json['message'] = "Tamara response status is not authorised";
                $this->response->addHeader('Content-Type: application/json');                
                $this->response->setOutput(json_encode($result_json));
            }
        }  
    }

    //Approved
    public function success(){
        unset($this->session->data['error_tamara']);
        $this->session->data['order_id_to_be_authorized'] = $this->session->data['order_id'];

        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        //Confirm order but keep order status as it is...
        $this->model_checkout_order->confirm($this->session->data['order_id'], $order_info['order_status_id']);        

        //redirect to success page...
        $this->response->redirect($this->url->link('checkout/success', '', true));
    }

    public function failure(){
        $this->session->data['error_tamara'] = 'Tamara ERROR: payment failed';
        $this->response->redirect($this->url->link('checkout/checkout', '', true));
    }

    public function cancel(){
        $this->session->data['error_tamara'] = 'Tamara ERROR: Transaction is canceled';
        $this->response->redirect($this->url->link('checkout/checkout', '', true));
    }

    /* helper functions */
    private function _getBaseUrl(){
    	//Check current API Mode..
    	$debugging_mode = $this->config->get('tamara_installment')['debugging_mode'];
    	return ( isset($debugging_mode) && $debugging_mode == 1 ) ? self::BASE_API_TESTING_URL : self::BASE_API_PRODUCTION_URL;
    }

    function validatePhoneNumber($country, $phone){
        if($country == 'SAU')
            return preg_match('/^(009665|9665|\+9665|05|5)(5|0|3|6|4|9|1|8|7)([0-9]{7})$/', $phone);
        elseif ($country == 'ARE')
            return preg_match('/^(?:\+971|971|00971|0)?(?:50|51|52|55|56|58|2|3|4|6|7|9)\d{7}$/', $phone);
        elseif ($country == 'KWT')
            return preg_match('/^(?:\+965|965|00965)?[569]\d{7}$/', $phone);
        else
            return false;
    }
}
