<?php
class ControllerPaymentUpay extends Controller {
	private $testurl = "https://api.upayments.com/test-payment";
	private $liveapi = "https://api.upayments.com/payment-request";
	private $allowedCurrencies = [
		'KWD', 'SAR', 'USD', 'BHD', 'EUR', 'OMR', 'QAR', 'AED'
	];

	protected function index(): void
    {
		$this->data = array_merge($this->data, $this->language->load_json('payment/upay'));

		$this->data['action'] = 'index.php?route=payment/upay/confirm';

		if(isset($this->session->data['error_upay'])){
          $this->data['error_upay'] = $this->session->data['error_upay']['resp_msg'];
		}

		$this->data['knet_img'] = '<img src="expandish/view/theme/default/image/knet.png" style="float:none"/>';
		$this->data['cc_img'] = '<img src="expandish/view/theme/default/image/cccc.png" style="float:none"/>';

        $this->template = 'default/template/payment/upay.expand';

		$this->render_ecwig();
	}

	public function confirm(): void
    {
    	$settings = $this->config->get('upay');
		unset($this->session->data['error_upay']);
		$url = $this->testurl;
		$api_key = $settings['api_key'];
		if (! (bool)$settings['test_mode']) {
			$url = $this->liveapi;
			$api_key = password_hash($settings['api_key'],PASSWORD_BCRYPT);
		}

		$payment_gateway = $this->request->post['payment_gateway'];

		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		// decide currency and total
		$decided_currency_and_total = $this->decideCurrencyAndAmount($order_info['total'], $order_info['currency_code']);

		$fname = $this->customer->getFirstName();
		$lname = $this->customer->getLastName();
		$name  = isset($fname, $lname) ? $fname . ' ' . $lname : $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];

		$email  = $this->customer->getEmail() ?? $order_info['email']; //"harbourspace@gmail.com";

		$telephone  = $this->customer->getTelephone() ?? $order_info['telephone']; //"1234567890";

		$products    = $this->cart->getProducts();
        if (isset($this->session->data['del_charges'])) {
            $del_charges = $this->session->data['del_charges'];
        }
		unset($this->session->data['del_charges']);

      	foreach ($products as $product) {

			$price = $this->decideCurrencyAndAmount($product['price'], $order_info['currency_code'])['amount'];

            $products_name[]  = $product['name'];
            $products_qty[]   = $product['quantity'];
            $products_price[] = $price;

            if ($product === end($products) && isset($del_charges)) {
				foreach ($del_charges as $prod_del_charges) {//comment if want to display delivery charge only once
					$products_name[]  = $prod_del_charges['title'];
		            $products_qty[]   = $product['quantity'];
		            $products_price[] = $this->decideCurrencyAndAmount($prod_del_charges['value'], $order_info['currency_code'])['amount'];
              }
            }
        }

        if ($this->cart->hasShipping())
        {
            $shipping = $this->session->data['shipping_method']['cost'];
            $products_name[]  = 'Shiping Cost';
            $products_qty[]   = $product['quantity'];
            $products_price[] = $this->decideCurrencyAndAmount($shipping, $order_info['currency_code'])['amount'];
		}

        $fields = [
			'merchant_id'=> $settings['merchant_id'],
			'username' => $settings['uname'],
			'password'=> stripslashes($settings['password']),
			'api_key' => $api_key,
			'order_id'=> $order_info['order_id'],
			'total_price'=> $decided_currency_and_total['amount'],
			'CstFName' => $name,
			'CstEmail' => $email,
			'CstMobile' => $telephone,
			'success_url'=> $this->url->link('payment/upay/success', '', 'SSL'),
			'error_url'=> $this->url->link('payment/upay/error', '', 'SSL'),
			'test_mode'=> (bool)$settings['test_mode'],
			'whitelabled'=> false,
			'payment_gateway'=> $payment_gateway ?: 'cc',
			'ProductName'=>json_encode($products_name),
			'ProductQty'=>json_encode($products_qty),
			'ProductPrice'=>json_encode($products_price),
			'reference' => 'Ref'.$order_info['order_id']
		];

       $soap_do     = curl_init();
       curl_setopt($soap_do, CURLOPT_URL, $url);
       curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
       curl_setopt($soap_do, CURLOPT_TIMEOUT, 10);
       curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
       curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
       curl_setopt($soap_do, CURLOPT_POST, true);
       curl_setopt($soap_do, CURLOPT_POSTFIELDS, http_build_query($fields));
       curl_setopt($soap_do, CURLOPT_HTTPHEADER, array('x-Authorization: hWFfEkzkYE1X691J4qmcuZHAoet7Ds7ADhL'));
       $result = curl_exec($soap_do);
       $err    = curl_error($soap_do);

	  $result_data= json_decode($result,true);

      if($result_data['status'] === 'success') {
         $RedirectUrl= $result_data['paymentURL'];
         $this->redirect($RedirectUrl);
      } else {

      	$array_errors = [
			'not_test_credentials' => 'Wrong Test Credentials!',
			'not_live_credentials' => 'Wrong Live Credentials!',
			'merchant_id_missing' => 'Merchant ID Empty!',
			'username_missing' => 'Merchant Username Empty!',
			'password_missing' => 'Merchant Password Empty!',
			'api_key_missing' => 'Merchant API Key Empty!',
			'total_price_missing' => 'Total Price Empty!',
			'total_price_greater_zero' => 'Total Price not Greater than 0!',
			'order_id_missing' => 'No Order Found!',
			'not_authorised_user' => 'Merchant not Found!',
			'password_wrong' => 'Merchant Wrong Password!',
			'invalid_api_key' => 'Merchant Wrong API Key!'
		];

        $this->session->data['error_upay']['resp_msg'] = $array_errors[$result_data['error_code']] ?: 'Payment Request Failed!';
        $this->response->redirect($this->url->link('checkout/checkout', '', $server_conn_slug));
      }
    }

	public function success(): void
    {
		$settings = $this->config->get('upay');
		$this->load->model('checkout/order');
		$this->model_checkout_order->confirm($this->session->data['order_id'], (int)$settings['order_status_id']);
    	$this->redirect($this->url->link('checkout/success'));
	}

	public function error() {
		$this->session->data['error_upay']['resp_msg'] = 'Payment Proccess Failed!';
		$this->response->redirect($this->url->link('checkout/checkout', '', $server_conn_slug));
	}

	 /**
     * do need operation upon amount and currency
     * decide the correct currency
     * round and convert the amount based on the currency
     * multiply the amount in 100 (ex: convert cents to dollars)
     *
     * @param float $amount
     * @param string $currency_code
     *
     * @return array [altered amount, altered currency code]
     */
    private function decideCurrencyAndAmount($amount, $currency_code)
    {

        // remove uninstalled currencies 
        $allowedCurrencies = $this->filterAllowdCurrencires();

        $check_if_in_array = (in_array(
            strtoupper($currency_code),
            $allowedCurrencies
        )) ? true : false;

        if ($check_if_in_array && count($allowedCurrencies) > 0) {
            //$amount = round($amount); commented to allow price fractions appear
            return compact('amount', 'currency_code');
        }

        if (!$this->currency->has('USD') && count($allowedCurrencies) > 0) {
            $amount = $this->currency->convert($amount, $currency_code, $this->allowedCurrencies[0]);
            $amount = round($amount);
            $currency_code = $this->allowedCurrencies[0];

            return compact('amount', 'currency_code');
        }


        $currency_code = strtoupper($currency_code) !== 'USD' ? 'USD' : $currency_code;

        $dollar_rate = $this->currency->gatUSDRate($this->config->get('config_currency'));

        $amount = $this->currency->format($amount, $currency_code, false, false);

        $amount = round($dollar_rate * $amount);

        return compact('amount', 'currency_code');
    }


    /**
     * remove uninstalled currencies
     *
     * @return array allowed currencies
     */
    private function filterAllowdCurrencires()
    {
        return array_filter($this->allowedCurrencies, function ($crncy) {
            return $this->currency->has($crncy);
        });
    }
}
?>