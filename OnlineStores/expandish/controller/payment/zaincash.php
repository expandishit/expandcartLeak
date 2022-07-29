<?php
ini_set('precision', 15); 
require_once('includes/autoload.php');
use \Firebase\JWT\JWT;



class ControllerPaymentZainCash extends Controller {
	protected function index() {
		$this->language->load_json('payment/zaincash');
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		
		$this->data['text_merchantid'] = trim($this->config->get('text_merchantid')); 
		$this->data['text_merchantsecret'] = trim($this->config->get('text_merchantsecret')); 
		$this->data['text_merchantmsisdn'] = trim($this->config->get('text_merchantmsisdn')); 
		$this->data['text_isdollar'] = trim($this->config->get('text_isdollar')); 
		$this->data['text_dollarprice'] = trim($this->config->get('text_dollarprice')); 
		$this->data['text_testcred'] = trim($this->config->get('text_testcred')); 
		$this->data['orderid'] = date('His') . $this->session->data['order_id'];
		$this->data['callbackurl'] = $this->url->link('payment/zaincash/callback');

		
		//Checking dollar or dinar SETTING
		if($this->data['text_isdollar']=='1'){$factor=intval($this->data['text_dollarprice']);}else{$factor=1;}
		
		//Building redirect url
		$redirect_url = $this->data['callbackurl'].'&t=';

		//building token
		$iaaa=time();
		$iexp=$iaaa+60*60*4;

		$data = array(
		'amount'  => intval($order_info['total'])*$factor,        
		'serviceType'  => "WordPress Cart",          
		'msisdn'  => (double) $this->data['text_merchantmsisdn'],  
		'orderId'  => intval($this->data['orderid']),
		'redirectUrl'  => $redirect_url,
		'iat'  => $iaaa,
		'exp'  => $iexp
        );

		
		
		//Encoding Token
		$token_re = JWT::encode(
			$data,      //Data to be encoded in the JWT
			$this->data['text_merchantsecret']
		);
		
		//Check if test or production mode
		if($this->data['text_testcred'] == '1'){
			$tUrl = 'https://test.zaincash.iq/transaction/init';
			$rUrl = 'https://test.zaincash.iq/transaction/pay?id=';
		}else{
			$tUrl = 'https://api.zaincash.iq/transaction/init';
			$rUrl = 'https://api.zaincash.iq/transaction/pay?id=';
		}
		
		
		//POSTing data to API
		$data_to_post = array();
		$data_to_post['token'] = urlencode($token_re);
		$data_to_post['merchantId'] = $this->data['text_merchantid'];
		$data_to_post['lang'] = $this->session->data['language'];

		$data_to_post = http_build_query($data_to_post);

		// Michael-> I have no idea what that is so I just commented it and used cURL instead.
		// $options = array(
		// 	'http' => array(
		// 		'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
		// 		'method'  => 'POST',
		// 		'content' => $data_to_post,
		// 	),
		// );
		// $context  = stream_context_create($options);
		// $response= file_get_contents($tUrl, false, $context);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL,$tUrl);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_to_post);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// ========= Enable only in DEBUGGING MODE.==================
		// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		// ========= /Enable only in DEBUGGING MODE.==================

		curl_setopt($ch, CURLOPT_HTTPHEADER, "Content-type: application/x-www-form-urlencoded\r\n");

		$response = curl_exec($ch);

		// ========== DEBUG RESPONSE ============
		// $this->log->forceWrite("Zaincash Response => \n {$response} \n ============ \n\n");
		// ========== /DEBUG RESPONSE ============

		if ( $response == false ) {
			$response = curl_error($ch);

			// ========== DEBUG ZAINCASH ERROR =============
			// $this->log->forceWrite("Error in zaincash! => \n Error is: {$response} \n ============ \n\n");
			// ========== /DEBUG ZAINCASH ERROR =============
		}

		curl_close($ch);
		
		//Parsing response
		$array = json_decode($response, true);
		$id = $array['id'];

		$this->data['action'] = $rUrl.$id;

		if ($order_info) {
			$this->data['orderdate'] = date('YmdHis');
			$this->data['currency'] = $order_info['currency_code'];
			$this->data['orderamount'] = $this->currency->format($order_info['total'], $this->data['currency'] , false, false);
			
			

            // if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/zaincash.expand')) {
            //     $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/zaincash.expand';
            // }
            // else {
            //     $this->template = $this->config->get('config_template') . '/template/payment/zaincash.expand';
            // }
            
            $this->template = 'default/template/payment/zaincash.expand';

			$this->render_ecwig();
		}
	}

	public function callback() {
		 \Firebase\JWT\JWT::$leeway = 20;
	   $this->language->load_json('payment/zaincash');
	   
	   $this->load->model('checkout/order');

		if (isset($this->request->get['token']) && ! empty($this->request->get['token'])) {
			$tokeno=str_replace('?token=','',$this->request->get['token']);
			$orderjson= JWT::decode($tokeno, $this->config->get('text_merchantsecret'), array('HS256'));
			$orderjson=(array) $orderjson;

			$orderid=$orderjson['orderid'];

			$order_id = trim(substr(($orderid), 6));

			$order_info = $this->model_checkout_order->getOrder($order_id);

			
			if ($orderjson['status']=='success'){
				$this->model_checkout_order->confirm($order_id, $this->config->get('config_order_status_id'), 'Processing', true);
				header('Location: '.$this->url->link('checkout/success'));
			} 

			if ($orderjson['status']=='failed'){

				if ( array_key_exists("msg", $orderjson) ) {
					$this->data['text_failure_msg'] = $orderjson['msg'];
				} else {
					$this->data['text_failure_msg'] = $this->language->get("text_payment_failure_message");
				}
				$failed_order_status_id= $this->config->get('config_order_status_id');
                $this->model_checkout_order->confirm($order_id, $failed_order_status_id, 'Failed', true);
				//$this->template = 'clearion/template/payment/zaincash_failed.tpl';
                // if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/zaincash_failed.expand')) {
                //     $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/zaincash_failed.expand';
                // }
                // else {
                //     $this->template = $this->config->get('config_template') . '/template/payment/zaincash_failed.expand';
                // }

				header('Location: '.$this->url->link('checkout/error'));
			} 
			
			
			
		}
		else{
            // change 2 to be order_status_id in case of something is wrong
		   // $this->model_checkout_order->confirm($order_id, 2, 'Processing', true);

            $failed_order_status_id= $this->config->get('config_order_status_id');
            $order_id= $this->session->data['order_id'];
			$this->model_checkout_order->confirm($order_id, $failed_order_status_id, 'Failed', false);
			
			$this->data['text_failure'] = $this->language->get("text_failure");
			$this->data['text_failure_msg'] = $this->language->get("text_payment_failure_message");

            //$this->template = 'clearion/template/payment/zaincash_failed.tpl';
            // if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/zaincash_failed.expand')) {
            //     $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/zaincash_failed.expand';
            // }
            // else {
            //     $this->template = $this->config->get('config_template') . '/template/payment/zaincash_failed.expand';
            // }
            
            $this->template = 'default/template/payment/zaincash_failed.expand';
            
            $this->response->setOutput($this->render_ecwig());
        }


	}
}
?>
