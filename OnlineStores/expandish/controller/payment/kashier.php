<?php

/**
 * Kashier payment handler for the store front
 * 
 * @author MohamedHassanWD <hassan.mohamed.sf@gmail.com>
 * @copyright 2019 ExpandCart
 */
class ControllerPaymentKashier extends Controller
{
	/**
	 * Handle the call back to confirm the order and redirect
	 *
	 * @return void
	 */
	public function callback()
	{
		if($this->request->get['paymentStatus'] == 'SUCCESS'){
			$this->confirm();
			$this->redirect($this->url->link('checkout/success'));
		}else{
			$this->redirect($this->url->link('checkout/error'));
		}
	}

	/**
	 * Show the default payment view
	 *
	 * @return void
	 */
	protected function index()
	{
		$this->initializer([
			'kashier' => 'payment/kashier',
			'checkout/order'
		]);

		$orderInfo = $this->order->getOrder($this->session->data['order_id']);

		if (!$orderInfo) {
			return false;
		}

		$settings = $this->model_payment_kashier->getSettings();

		$this->data['script_link'] = 'https://checkout.kashier.io/kashier-checkout.js';
		$this->data['order_id'] = $orderInfo['order_id'];
		$this->data['merchant_id'] = $this->config->get('kashier_merchant_id');
		$this->data['redirect_url'] = $this->config->get('config_url').'/kashier_callback.php';
		$this->data['store_name'] = $this->config->get('config_name');
		$this->data['amount'] = $orderInfo['total'] = (int) $this->currency->convert($orderInfo['total'],$this->config->get('config_currency'),$settings['kashier_currency']);
		$this->data['frontend_language'] = $this->config->get('config_language');
		$this->data['currency'] = $orderInfo['currency_code'];
		$this->data['data_mode'] = $settings['kashier_test_mode'] ? 'test' : 'live';
		
		if($orderInfo['currency_code'] != $settings['kashier_currency']){
			$this->data['currency'] = $orderInfo['currency_code'] = $settings['kashier_currency'];
		}

		$this->data['hash'] = $this->generateKashierOrderHash($orderInfo);

		// if (file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/default/template/payment/kashier.expand')) {
		// 	$this->template = 'customtemplates/' . STORECODE . '/default/template/payment/kashier.expand';
		// } else {
		// 	$this->template =  'default/template/payment/kashier.expand';
		// }
        $this->template = 'default/template/payment/kashier.expand';

		$this->render_ecwig();
	}

	/**
	 * Confirm the orer
	 * 
	 * @return void
	 */
	public function confirm()
	{
		$this->load->model('checkout/order');
		$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('kashier_completed_order_status_id'));
	}

	/**
	 * Generate the required hmac encryption hack for Kashier
	 *
	 * @param array $order
	 * @return string Calculated hash
	 */
	private function generateKashierOrderHash($order)
	{
		$mid = $this->config->get('kashier_merchant_id'); //your merchant id
		$amount = (int) $order['total']; //eg: 100
		$currency = $order['currency_code']; //eg: "EGP"
		$orderId = $order['order_id']; //eg: 99, your system order ID
		$secret = $this->config->get('kashier_iframe_api_key'); //eg: "1f06575c-edc5-4508-94ae-68b3a99728db"

		$path = "/?payment=" . $mid . "." . $orderId . "." . $amount . "." . $currency;

		$hash = hash_hmac('sha256', $path, $secret, false);
		return $hash;
	}
}
