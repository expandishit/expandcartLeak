<?php

/**
 * Store front for Bayanpay Online
 * 
 * @author Mohamed Hassan
 * @category Payment Integration
 * @copyright 2020 ExpandCart
 */
class ControllerPaymentBayanpay extends Controller
{
	/**
	 * The payment gateway url to submit the form
	 *
	 * @var string
	 */
	private $bayanpay_url;

	/**
	 * The callback success URL
	 *
	 * @var string
	 */
	private $return_url_success;

	/**
	 * The callback fail URL
	 *
	 * @var string
	 */
	private $return_url_failure;

	/**
	 * The encription type used to encrypt order id
	 *
	 * @var string
	 */
	private $cipher = "AES-256-CBC";

	/**
	 * Unique string Initialization Vector used in encryption
	 *
	 * @var string
	 */
	private $iv = "9CF7E8873B7B3E4D4A654E9F9AB60677";

	/**
	 * The mode of handling the callback, session or url_order_id
	 *
	 * @var string
	 */
	private $mode   = "session";

	/**
	 * Main handle function to show the form
	 */
	public function index()
	{
		$this->load->model('checkout/order');
		$this->language->load_json('payment/bayanpay');

		$orderInfo = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		if (!$orderInfo) {
			return false;
		}

		$this->return_url_success = $this->url->link('payment/bayanpay/callback_success');
		$this->return_url_failure = $this->url->link('payment/bayanpay/callback_failure');

		if($this->mode === 'url_order_id'){
			$this->return_url_success .= "&oi=" . $this->encode_order_id($orderInfo['order_id']);
			$this->return_url_failure .= "&oi=" . $this->encode_order_id($orderInfo['order_id']);
		}

		if ($this->config->get('bayanpay_debug_mode') == 1) {
			$this->bayanpay_url = 'https://sandbox.bayanpay.sa/agcore/payment';
		} else {
			$this->bayanpay_url = 'https://pg.bayanpay.sa/agcore/payment';
		}

		if ($this->request->post) {
			// collect data 
			$mid = $this->config->get('bayanpay_merchant_id');
			$enc_key = $this->config->get('bayanpay_encription_key');

			$currency = $orderInfo['currency_code'];

			$allowed = ['SAR'];

			if (!in_array($currency, $allowed)) {
				echo '<p class="alert alert-danger">' . $this->language->get('error_currency_not_supported') . '</p>';
				die();
			}

            //Set collaborator id
            if($this->config->get('bayanpay_debug_mode') == 1){
                $CollaboratorID = 'BAYANPAY';
            }else{
                $CollaboratorID = 'BayanPay';
            }

            $MerchantID = $mid;

			$txnid = $orderInfo['order_id'];
			$amount = round($orderInfo['total'] * $orderInfo['currency_value'], 2);

			$fname = $orderInfo['payment_firstname'];
			$lname = empty($orderInfo['payment_lastname']) ? $orderInfo['payment_firstname'] : $orderInfo['payment_lastname'];

			$addr1 = $orderInfo['payment_address_1'];
			$addr2 = empty($orderInfo['payment_address_2']) ? $orderInfo['payment_address_1'] : $orderInfo['payment_address_2'];
			$city = $orderInfo['payment_city'];
			$state = $orderInfo['payment_zone'];
			$country = $orderInfo['payment_iso_code_3'];
			$postcode = empty($orderInfo['payment_postcode']) ? '11111' : $orderInfo['payment_postcode'];
			$email = $orderInfo['email'];
			$mobile = empty($orderInfo['payment_telephone']) ? '1234567890' : $orderInfo['payment_telephone'];

			$text = $CollaboratorID . '|' . $MerchantID . '|' . $txnid .'|'. $amount . '|' . $currency .'|' . $country . '|SALE|' . $this->return_url_success . '|' . $this->return_url_failure . '|WEB|';
            $pg_details= '|||';
            $cust_details= '||||Y';
            $bill_details= '||||';
            $ship_details= '||||||';
            $item_details= '||';
            $card_details= '||||';
            $other_details= '||||';

			$iv = "0123456789abcdef";
			$size = openssl_cipher_iv_length('AES-256-CBC');
			$pad = $size - (strlen($text) % $size);
			$padtext = $text . str_repeat(chr($pad), $pad);
			$crypt = base64_encode(openssl_encrypt($padtext, 'AES-256-CBC', base64_decode($enc_key), OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv));

			$action = $this->bayanpay_url;
			$requestParameter = $crypt;



			$form = '
			<form action="' . $action . '" method="post" id="ecom_form">
				<input type="hidden" name="me_id" value="' . $MerchantID . '" />
				<input type="hidden" name="txn_details" value="' . $requestParameter . '" />
				<script type="text/javascript">
					$("#qc_confirm_order").click(function(){
						document.getElementById("ecom_form").submit();
					});
				</script>
			</form>
			';

			$this->data['form'] = $form;


			// if (file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/bayanpay.expand')) {
			// 	$this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/bayanpay.expand';
			// } else {
			// 	$this->template = 'default/template/payment/bayanpay.expand';
			// }
            $this->template = 'default/template/payment/bayanpay.expand';
			$this->render_ecwig();
		}
	}

	/**
	 * The success callback function
	 *
	 * @return void
	 */
	public function callback_success()
	{
		if($this->mode === 'url_order_id'){
			if(!$this->request->get['oi']){
				$this->redirect($this->url->link('/'));
			}

			$order_id = $this->decode_order_id($this->request->get['oi']);
		}else{
			$order_id = $this->session->data['order_id'];
		}

		$this->load->model('checkout/order');
		$this->model_checkout_order->confirm($order_id, $this->config->get('bayanpay_completed_order_status_id'));
		$this->redirect($this->url->link('checkout/success'));
	}

	/**
	 * The fail callback function
	 *
	 * @return void
	 */
	public function callback_failure()
	{
		if($this->mode === 'url_order_id'){
			if(!$this->request->get['oi']){
				$this->redirect($this->url->link('/'));
			}

			$order_id = $this->decode_order_id($this->request->get['oi']);
		}else{
			$order_id = $this->session->data['order_id'];
		}

		$this->load->model('checkout/order');
		$this->model_checkout_order->confirm($order_id, $this->config->get('bayanpay_failed_order_status_id'));
		$this->redirect($this->url->link('checkout/error'));
	}

	/**
	 * Encrypt the order id 
	 *
	 * @param integer $order_id
	 * @return string Encrypted order id
	 */
	private function encode_order_id($order_id = 0)
	{
		return openssl_encrypt($order_id, $this->cipher, $this->config->get('bayanpay_encription_key'), $options = 0, $this->iv);
	}

	/**
	 * Decrypt the order id encrypted string
	 *
	 * @param string $encrypted_text
	 * @return int order id
	 */
	private function decode_order_id($encrypted_text)
	{
		return openssl_decrypt($encrypted_text, $this->cipher, $this->config->get('bayanpay_encription_key'), $options = 0, $this->iv);
	}
}
