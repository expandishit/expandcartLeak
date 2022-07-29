<?php
class ControllerPaymentOneCard extends Controller {

	protected $errors = array();

	protected function index() {

		$classname = str_replace('vq2-catalog_controller_payment_', '', basename(__FILE__, '.php'));
		$store_url = ($this->config->get('config_ssl') ? (is_numeric($this->config->get('config_ssl'))) ? str_replace('http', 'https', $this->config->get('config_url')) : $this->config->get('config_ssl') : $this->config->get('config_url'));
		$this->data = array_merge($this->data, $this->load->language('payment/' . $classname));

		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		// v14x Backwards Compatibility
		if (isset($order_info['currency_code'])) { $order_info['currency'] = $order_info['currency_code']; }
		if (isset($order_info['currency_value'])) { $order_info['value'] = $order_info['currency_value']; }
		

		$this->data['error'] = (isset($this->session->data['error'])) ? $this->session->data['error'] : NULL;
		unset($this->session->data['error']);

		// Check for supported currency, otherwise convert to default.
		$supported_currencies = array('EGP','SAR','USD','EUR','AED','KWD','SYP');
		if (in_array($order_info['currency'], $supported_currencies)) {
			$currency = $order_info['currency'];
		} else {
			$currency = 'USD';
		}

		$amount = $this->currency->format($order_info['total'], $currency, FALSE, FALSE);
		$successurl = ($store_url . 'index.php?route=payment/' . $classname . '/callback');
		$storename = ($this->config->get('config_name')) ? $this->config->get('config_name') : $this->config->get('config_store');

		$this->data['hidden_fields'] = array();
		$this->data['hidden_fields']['OneCard_MerchID'] = trim($this->config->get($classname . '_mid'));
		$this->data['hidden_fields']['OneCard_Amount'] = $amount;
		$this->data['hidden_fields']['OneCard_Timein'] = time();
		$this->data['hidden_fields']['OneCard_MProd'] = $storename;
		$this->data['hidden_fields']['OneCard_TransID']  = $order_info['order_id'];
		$this->data['hidden_fields']['OneCard_Currency'] = $currency;
		$this->data['hidden_fields']['OneCard_ReturnURL'] = $successurl;

		// Generate MD5 Hash
		// MD5 (OneCard_MerchID + OneCard_TransID + OneCard_Amount + OneCard_Currency + OneCard_Timein + OneCard_TransKey)
		$hash  = $this->data['hidden_fields']['OneCard_MerchID'];
		$hash .= $this->data['hidden_fields']['OneCard_TransID'];
		$hash .= $this->data['hidden_fields']['OneCard_Amount'];
		$hash .= $this->data['hidden_fields']['OneCard_Currency'];
		$hash .= $this->data['hidden_fields']['OneCard_Timein'];
		$hash .= trim($this->config->get($classname . '_key'));

		$md5hash = md5($hash);

		$this->data['hidden_fields']['OneCard_HashKey']  = $md5hash;

		$this->data['testmode'] = $this->config->get($classname . '_test');

		if ($this->config->get($classname . '_test')) {
			$this->data['action'] = 'http://onecard.n2vsb.com/customer/integratedPayment.html';
		} else {
			$this->data['action'] = 'https://www.onecard.net/customer/integratedPayment.html';
		}

		$this->id       = 'payment';

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/' . $classname . '.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/' . $classname . '.tpl';
		} elseif (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/payment/' . $classname . '.tpl')) {
			$this->template = $this->config->get('config_template') . '/payment/' . $classname . '.tpl';
		} else {
			$this->template = 'default/template/payment/' . $classname . '.tpl';
		}
		$this->render();
	}

	public function confirm() {
		return;
	}

	private function fail($msg = false) {
		$classname = str_replace('vq2-catalog_controller_payment_', '', basename(__FILE__, '.php'));
		$store_url = ($this->config->get('config_ssl') ? (is_numeric($this->config->get('config_ssl'))) ? str_replace('http', 'https', $this->config->get('config_url')) : $this->config->get('config_ssl') : $this->config->get('config_url'));
		if (!$msg) { $msg = (!empty($this->session->data['error']) ? $this->session->data['error'] : 'Unknown Error'); }
		// Debug
		if ($this->config->get($classname . '_debug')) { $this->log->write($msg); }
		if (method_exists($this->document, 'addBreadcrumb')) { //1.4.x
			$this->redirect((isset($this->session->data['guest'])) ? ($store_url . 'index.php?route=checkout/guest_step_3') : ($store_url . 'index.php?route=checkout/confirm'));
		} else {
			echo '<html><head><script type="text/javascript">';
			echo 'alert("'.addslashes($msg).'");';
			echo 'parent.location="' . ($store_url  . 'index.php?route=checkout/checkout') . '";';
			echo '</script></head></html>';
		}
		exit;
	}

	public function callback() {
		$classname = str_replace('vq2-catalog_controller_payment_', '', basename(__FILE__, '.php'));
		$store_url = ($this->config->get('config_ssl') ? (is_numeric($this->config->get('config_ssl'))) ? str_replace('http', 'https', $this->config->get('config_url')) : $this->config->get('config_ssl') : $this->config->get('config_url'));
		$this->data = array_merge($this->data, $this->load->language('payment/' . $classname));

		$this->load->model('checkout/order');

		// Debug
		if ($this->config->get($classname . '_debug')) { file_put_contents(DIR_LOGS . $classname . '_debug.txt', __FUNCTION__ . "\r\n$classname GET: " . print_r($_GET,1) . "\r\n" . "$classname POST: " . print_r($_POST,1) . "\r\n", FILE_APPEND); }

		if (isset($_REQUEST['OneCard_TransID'])) {
			$order_id = $_REQUEST['OneCard_TransID'];
		} else {
			$order_id = 0;
		}

		$order_info = $this->model_checkout_order->getOrder($order_id);

		// If there is no order info then fail.
		if (!$order_info) {
			$this->session->data['error'] = $this->language->get('error_no_order');
			$this->fail();
		}

		// Calculate md5hash to compare
		$my_downloadmd5  = trim($this->config->get($classname . '_mid'));
		$my_downloadmd5 .= $_REQUEST['OneCard_TransID'];
		$my_downloadmd5 .= $_REQUEST['OneCard_Amount'];
		$my_downloadmd5 .= $_REQUEST['OneCard_Currency'];
		$my_downloadmd5 .= $_REQUEST['OneCard_RTime'];
		$my_downloadmd5 .= trim($this->config->get($classname . '_key2'));
		$my_downloadmd5 .= $_REQUEST['OneCard_Code'];
		$my_downloadmd5 = md5($my_downloadmd5);


		// If we get a successful response back...
		if ($_REQUEST['OneCard_Code'] == '00' || $_REQUEST['OneCard_Code'] == '18') {
    		// ...and the md5 matches
        	if ($my_downloadmd5 == $_REQUEST['OneCard_RHashKey']) {
 				// ...and the order was not pre-confirmed, then confirm
 				if ($order_info['order_status_id'] == '0') {
	        		$this->model_checkout_order->confirm($order_info['order_id'], $this->config->get($classname . '_order_status_id'), $order_info['comment']);
				} else {// ...or if it is pre-confirmed, just update
					$this->model_checkout_order->update($order_info['order_id'], $this->config->get($classname . '_order_status_id'), FALSE, FALSE);
				}
				$this->redirect($store_url . 'index.php?route=checkout/success');
			} else { // MD5 doesn't match, set to pending state
				$this->model_checkout_order->confirm($order_info['order_id'], $this->config->get('config_order_status_id'), $order_info['comment']);
				mail($this->config->get('config_email'), 'ATTN: Unverified Order', "Order ID: $order_id needs manual review. \r\n\r\n $my_downloadmd5 != " . $_REQUEST['OneCard_RHashKey']);
			}
        } else { // assume they clicked cancel
	        if (isset($_REQUEST['OneCard_Description'])) {
				$this->session->data['error'] = $_REQUEST['OneCard_Code'] . ' :: ' . $_REQUEST['OneCard_Description'];
			} else {
				$this->session->data['error'] = $this->language->get('error_declined');
			}
		}
		$this->session->data['error'] = $this->language->get('error_declined');
	    $this->fail();
	}
}
?>
