<?php
class ControllerPaymentCashu extends Controller {

	private $name = '';
	protected $errors = array();
	
	protected function index() {
		
		$this->name = basename(__FILE__, '.php');

		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		// v14x Backwards Compatibility
		if (isset($order_info['currency_code'])) { $order_info['currency'] = $order_info['currency_code']; }
		if (isset($order_info['currency_value'])) { $order_info['value'] = $order_info['currency_value']; }

		$this->data = array_merge($this->data, $this->load->language('payment/' . $this->name));

		$this->data['error'] = (isset($this->session->data['error'])) ? $this->session->data['error'] : NULL;
		unset($this->session->data['error']);

		// Check for supported currency, otherwise convert to ISK.
		$supported_currencies = array('EUR','USD','AED','JOD','EGP','SAR','DZD','LBP','MAD','QAR','TRY');
		if (in_array($order_info['currency'], $supported_currencies)) {
			$currency = $order_info['currency'];
		} else {
			$currency = 'USD';
		}

		$amount = $this->currency->format($order_info['total'], $currency, FALSE, FALSE);
		$returnurl = (((HTTPS_SERVER) ? HTTPS_SERVER : HTTP_SERVER) . 'index.php?route=payment/' . $this->name . '/callback');

		$this->data['fields'] = array();
		$this->data['fields']['merchant_id'] = $this->config->get($this->name . '_mid');
		$this->data['fields']['amount'] = $amount;
		$this->data['fields']['currency'] = $currency;
		$this->data['fields']['language'] = 'ar'; // en, ar, ir
		$this->data['fields']['display_text'] = ($this->config->get('config_name')) ? $this->config->get('config_name') : $this->config->get('config_store');
		$this->data['fields']['txt1'] = ($this->config->get('config_name')) ? $this->config->get('config_name') : $this->config->get('config_store');
		$this->data['fields']['session_id']  = $order_info['order_id'];

		// Generate MD5 Hash
		$hash = (strtolower($this->config->get($this->name . '_mid') . ':' . $amount . ':' . $currency) . ':' . $this->config->get($this->name . '_key'));

		$md5hash = md5($hash);

		$this->data['fields']['token']  = $md5hash;

		$this->data['testmode'] = $this->config->get($this->name . '_test');

		if ($this->config->get($this->name . '_test')) {
			$this->data['fields']['test_mode'] = '1';
			$this->data['action'] = 'https://www.cashu.com/cgi-bin/pcashu.cgi';
		} else {
			$this->data['fields']['test_mode'] = '0';
			$this->data['action'] = 'https://www.cashu.com/cgi-bin/pcashu.cgi';
		}
		$this->data['back'] = (((HTTPS_SERVER) ? HTTPS_SERVER : HTTP_SERVER) . 'index.php?route=checkout/payment');

		$this->id       = 'payment';

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/' . $this->name . '.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/' . $this->name . '.tpl';
		} elseif (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/payment/' . $this->name . '.tpl')) {
			$this->template = $this->config->get('config_template') . '/payment/' . $this->name . '.tpl';
		} else {
			$this->template = 'default/template/payment/' . $this->name . '.tpl';
		}
		$this->render();
	}

	public function confirm() {
		$this->name = basename(__FILE__, '.php');
		if ($this->config->get($this->name . '_ajax')) {
			$this->load->model('checkout/order');
			$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('config_order_status_id'));
		}
	}

	public function fail($msg) {
		echo '<html><head><script type="text/javascript">';
		echo 'alert("'.$msg.'");';
		echo 'window.location="' . (HTTPS_SERVER  . 'index.php?route=checkout/checkout') . '";';
		echo '</script></head></html>';
	}

	public function callback() {
		$this->name = basename(__FILE__, '.php');
		$this->data = array_merge($this->data, $this->load->language('payment/' . $this->name));

		$this->load->model('checkout/order');

		// Debug
		if ($this->config->get($this->name . '_debug')) {
			if (isset($_POST)) {
				$p_msg = "DEBUG POST VARS:\n"; foreach($_POST as $k=>$v) { $p_msg .= $k."=".$v."\n"; }
			}
			if (isset($_GET)) {
				$g_msg = "DEBUG GET VARS:\n"; foreach($_GET as $k=>$v) { $g_msg .= $k."=".$v."\n"; }
			}
			$msg = ($p_msg . "\r\n" . $g_msg);
			mail($this->config->get('config_email'), $this->name . '_debug', $msg);
			if (is_writable(getcwd())) {
				file_put_contents($this->name . '_debug.txt', $msg);
			}
		}//

		if (isset($_POST['session_id'])) {
			$order_id = $_POST['session_id'];
		} else {
			$order_id = 0;
		}

		$order_info = $this->model_checkout_order->getOrder($order_id);

		// If there is no order info then fail.
		if (!$order_info) {
			$this->session->data['error'] = $this->language->get('error_no_order');
			if (method_exists($this->document, 'addBreadcrumb')) { //1.4.x
				$this->redirect((isset($this->session->data['guest'])) ? (((HTTPS_SERVER) ? HTTPS_SERVER : HTTP_SERVER) . 'index.php?route=checkout/guest_step_3') : (((HTTPS_SERVER) ? HTTPS_SERVER : HTTP_SERVER) . 'index.php?route=checkout/confirm'));
			} else {
				$this->fail($this->session->data['error']);
			}
		}

		// If we get a failure response back...
		if (isset($_POST['errorCode'])) {
			if (is_numeric($_POST['errorCode'])) {
				$this->session->data['error'] = $this->language->get('error_' . $_POST['errorCode']);
			} else {
				$this->session->data['error'] = $_POST['errorCode'];
			}
			if (method_exists($this->document, 'addBreadcrumb')) { //1.4.x
				$this->redirect((isset($this->session->data['guest'])) ? (((HTTPS_SERVER) ? HTTPS_SERVER : HTTP_SERVER) . 'index.php?route=checkout/guest_step_3') : (((HTTPS_SERVER) ? HTTPS_SERVER : HTTP_SERVER) . 'index.php?route=checkout/confirm'));
			} else {
				$this->fail($this->session->data['error']);
			}
		}

		// If we get a successful response back...
		if ($order_info['order_status_id'] == '0') {
			$this->model_checkout_order->confirm($order_info['order_id'], $this->config->get($this->name . '_order_status_id'), $order_info['comment']);
		} else {// ...or if it is pre-confirmed, just update
			$this->model_checkout_order->update($order_info['order_id'], $this->config->get($this->name . '_order_status_id'), FALSE, FALSE);
		}
		$this->redirect((((HTTPS_SERVER) ? HTTPS_SERVER : HTTP_SERVER) . 'index.php?route=checkout/success'));
	}
}
?>
