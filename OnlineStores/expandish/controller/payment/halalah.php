<?php
require_once(DIR_SYSTEM . 'library/phpqrcode/qrlib.php');
require_once(DIR_SYSTEM . 'library/halalah/Qrcode.php');

class ControllerPaymentHalalah extends Controller {
	private $refPrefix = 'LFFC123456';

	protected function index() {
		$this->data = array_merge($this->data, $this->language->load_json('payment/halalah'));

		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$this->data['currency'] =$order_info['currency'];

		$this->data['address'] = nl2br($this->config->get('config_address'));
		$this->data['continue'] = $this->url->link('checkout/success');
		$this->data['deeplink'] = $this->getqrcode(1);

        $this->template = 'default/template/payment/halalah.expand';
		
		$this->render_ecwig();
	}
	
	public function h_login() {
		$client = new \GuzzleHttp\Client();

		try{
			$res = $client->request('POST', 'https://login.halalah.sa/connect/token', [
			  'form_params' => [
								 "grant_type" => "client_credentials",
								 "client_id" => $this->config->get('halalah_client_id'),
								 "client_secret" => $this->config->get('halalah_client_secret'),
								 "scope" => "scope_ext_order_gw_api"
							   ],
			  'headers' => ['Content-Type' => 'application/x-www-form-urlencoded']
			]);

			$response = json_decode($res->getBody(), true);
			$token = $response['access_token'];
			$this->session->data['halalah_token'] = $token;
		}catch (Exception $e) {
		    $token = '';
		}

		$this->response->setOutput(json_encode(['access_token' => $token]));
	}

	public function billstatus() {
		$client = new \GuzzleHttp\Client();

		$ref = $this->refPrefix.$this->session->data['order_id'];

		try{
			$res = $client->request('GET', 'https://apigw.halalah.sa/Orders/v2/Order/'.$this->config->get('halalah_mterminal').'/'.$ref, [
			  'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'Bearer '.$this->session->data['halalah_token']]
			]);
			
			$response = json_decode($res->getBody(), true);
		}catch (Exception $e) {
		    //
		}
		
		$this->response->setOutput(json_encode($response));
	}

	public function getqrcode($deepLink = 0) {
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$amount = $this->currency->format($order_info['total'], 'SAR', FALSE, FALSE);

		$bill = $this->session->data['order_id'];
		if($order_info['invoice_no']){
			$bill = $order_info['invoice_no'];
		}

		$ref = $this->refPrefix.$this->session->data['order_id'];

		if($deepLink){
			$link = 'HalalaHeWallet://Transaction?terminal='.$this->config->get('halalah_mterminal').'&amount='.$amount.'&referenceNo='.$ref.'&bil
lNo='.$bill.'&memo=memo&callback=';
			return $link;
		}else{
			$inputs = array(
			    "merchant_category_code" => $this->config->get('halalah_mccode'),
			    "merchant_name"          => $this->config->get('halalah_mname'),
			    "merchant_city"          => $this->config->get('halalah_mcity'),
			    "merchant_name_ar"       => $this->config->get('halalah_mname_ar'),
			    "merchant_city_ar"       => $this->config->get('halalah_mcity_ar'),
			    "amount"                 => $amount,
			    "bill"                   => $bill,
			    "reference"              => $ref,
			    "terminal"               => $this->config->get('halalah_mterminal')
			);

			if($this->config->get('halalah_postal_code')){
				$inputs['postal_code'] = $this->config->get('halalah_postal_code');
			}
			
			$HalalahQrcode = new HQrcode($inputs);
			QRcode::png($HalalahQrcode->output());
		}
		
	}

	public function confirm() {
		$this->load->model('checkout/order');
		unset($this->session->data['halalah_token']);
		$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('halalah_order_status_id'));
	}
}
?>