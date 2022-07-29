<?php
ini_set('precision', 15); 
require_once('includes/autoload.php');
use \Firebase\JWT\JWT;



class ControllerPaymentZainCash extends Controller {
	protected function index() {
		$this->language->load('payment/zaincash');
		$this->data['button_confirm'] = $this->language->get('button_confirm');
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
		$this->data['text_merchantsecret'] ,'HS256'
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
		$data_to_post['lang'] = "ar";
		$options = array(
		'http' => array(
		'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
		'method'  => 'POST',
		'content' => http_build_query($data_to_post),
		),
		);
		$context  = stream_context_create($options);
		$response= file_get_contents($tUrl, false, $context);
		
		

		//Parsing response
		$array = json_decode($response, true);
		$id = $array['id'];

		
		
		
		
		$this->data['action'] = 'https://api.zaincash.iq/transaction/pay?id='.$id;

		if ($order_info) {
			$this->data['orderdate'] = date('YmdHis');
			$this->data['currency'] = $order_info['currency_code'];
			$this->data['orderamount'] = $this->currency->format($order_info['total'], $this->data['currency'] , false, false);
			
			

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/zaincash.tpl')){
				$this->template = $this->config->get('config_template') . '/template/payment/zaincash.tpl';
			} else {
				$this->template = 'default/template/payment/zaincash.tpl';
			}

			$this->render();
		}
	}

	public function callback() {
		
		
		if (isset($this->request->get['ampt'])) {

			$tokeno=str_replace('?token=','',$this->request->get['ampt']);
			$orderjson= JWT::decode($tokeno, $this->config->get('text_merchantsecret'), array('HS256'));
			$orderjson=(array) $orderjson;
			
			$orderid=$orderjson['orderid'];
			
			$order_id = trim(substr(($orderid), 6));


			
			$this->load->model('checkout/order');
			$order_info = $this->model_checkout_order->getOrder($order_id);

			
			if ($orderjson['status']=='success'){
				$this->model_checkout_order->confirm($order_id, 2, 'Processing', true);
				header('Location: '.$this->url->link('checkout/success'));
			} 
			
			if ($orderjson['status']=='failed'){
				$this->data['text_failure']='فشل في الدفع';
				$this->data['text_failure_msg']=$orderjson['msg'];
				$this->template = 'default/template/payment/zaincash_failed.tpl';
				$this->response->setOutput($this->render());
			} 
			
			
			
		}
		


	}
}
?>