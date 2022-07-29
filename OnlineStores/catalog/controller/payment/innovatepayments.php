<?php
class ControllerPaymentInnovatePayments extends Controller {

	private function _signData($post_data,$fieldList) {
		$signatureParams = explode(',', $fieldList);
		$signatureString = $this->config->get('innovatepayments_secret');
		foreach ($signatureParams as $param) {
			if (array_key_exists($param, $post_data)) {
				$signatureString .= ':' . $post_data[$param];
			} else {
				$signatureString .= ':';
			}
		}
		return sha1($signatureString);
	}

	private function _buildUrl($action,$params) {
		$url=str_replace('&amp;','&',$this->url->link($action,$params,'SSL'));
		return $url;
	}

	private function _whichTemplate($file) {
		if (file_exists(DIR_TEMPLATE.$this->config->get('config_template').'/template/payment/'.$file)) {
			$template = $this->config->get('config_template').'/template/payment/'.$file;
		} else {
			$template = 'default/template/payment/'.$file;
		}
		return $template;
	}

	private function _hiddenField($k,$v) {
		$field="<input type=\"hidden\" name=\"".htmlspecialchars($k)."\" value=\"".htmlspecialchars($v)."\">";
		return $field;
	}

	protected function index() {
		$this->data['button_confirm'] = $this->language->get('button_confirm');

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		if (strcmp($this->config->get('innovatepayments_test'),'live')==0) {
			$testmode='0';
		} else {
			$testmode='1';
		}
		$amount= $this->currency->format(
			$order_info['total'],
			$order_info['currency_code'],
			$order_info['currency_value'],
			false);
		$cart_desc=trim($this->config->get('innovatepayments_cartdesc'));
		if (empty($cart_desc)) {
			$cart_desc='Your order from '.$this->config->get('config_name');
		}
		$cart_desc=str_replace('{order}', $order_info['order_id'], $cart_desc);

		$this->load->library('encryption');

		$post_data = Array(
			'ivp_store'	=> $this->config->get('innovatepayments_store'),
			'ivp_cart'	=> trim($order_info['order_id']),
			'ivp_amount'	=> $amount,
			'ivp_currency'	=> trim($order_info['currency_code']),
			'ivp_test'	=> $testmode,
			'ivp_timestamp'	=> '0',
			'ivp_desc'	=> trim($cart_desc),
			'ivp_source'	=> 'OpenCart',
			'ivp_extra'	=> 'bill,delv,return',
			'bill_fname'	=> trim($order_info['payment_firstname']),
			'bill_sname'	=> trim($order_info['payment_lastname']),
			'bill_addr1'	=> trim($order_info['payment_address_1']),
			'bill_addr2'	=> trim($order_info['payment_address_2']),
			'bill_addr3'	=> '',
			'bill_city'	=> trim($order_info['payment_city']),
			'bill_region'	=> trim($order_info['payment_zone']),
			'bill_zip'	=> trim($order_info['payment_postcode']),
			'bill_country'	=> trim($order_info['payment_iso_code_2']),
			'bill_email'	=> trim($order_info['email']),
			'bill_phone1'	=> trim($order_info['telephone']),
			'return_cb_auth'=> $this->_buildUrl('payment/innovatepayments/callback', ''),
			'return_cb_can'	=> $this->_buildUrl('payment/innovatepayments/callback', ''),
			'return_cb_decl'=> $this->_buildUrl('payment/innovatepayments/callback', ''),
			'return_auth'	=> 'auto:'.$this->_buildUrl('checkout/success', ''),
			'return_can'	=> 'auto:'.$this->_buildUrl('checkout/checkout', ''),
			'return_decl'	=> 'auto:'.$this->_buildUrl('checkout/checkout', '')
		);
		if ($this->cart->hasShipping()) {
			// Shipping data is available
			$post_data['delv_fname'] = trim($order_info['shipping_firstname']);
			$post_data['delv_sname'] = trim($order_info['shipping_lastname']);
			$post_data['delv_addr1'] = trim($order_info['shipping_address_1']);
			$post_data['delv_addr2'] = trim($order_info['shipping_address_2']);
			$post_data['delv_addr3'] = '';
			$post_data['delv_city'] = trim($order_info['shipping_city']);
			$post_data['delv_region'] = trim($order_info['shipping_zone']);
			$post_data['delv_zip'] = trim($order_info['shipping_postcode']);
			$post_data['delv_country'] = trim($order_info['shipping_iso_code_2']);
			$post_data['delv_phone1'] = '';
		} else {
			// No shipping data - use billing data as shipping data
			$post_data['delv_fname'] = $post_data['bill_fname'];
			$post_data['delv_sname'] = $post_data['bill_sname'];
			$post_data['delv_addr1'] = $post_data['bill_addr1'];
			$post_data['delv_addr2'] = $post_data['bill_addr2'];
			$post_data['delv_addr3'] = $post_data['bill_addr3'];
			$post_data['delv_city'] = $post_data['bill_city'];
			$post_data['delv_region'] = $post_data['bill_region'];
			$post_data['delv_zip'] = $post_data['bill_zip'];
			$post_data['delv_country'] = $post_data['bill_country'];
			$post_data['delv_phone1'] = $post_data['bill_phone1'];
		}
		// First create the signature for the main purchase details, used in other signatures.
		$post_data['ivp_signature'] = $this->_signData($post_data,'ivp_store,ivp_source,ivp_amount,ivp_currency,ivp_test,ivp_timestamp,ivp_cart,ivp_desc,ivp_extra');
		// Create signature for billing details
		$post_data['bill_signature'] = $this->_signData($post_data,'bill_title,bill_fname,bill_sname,bill_addr1,bill_addr2,bill_addr3,bill_city,bill_region,bill_country,bill_zip,ivp_signature');
		// Create signature for delivery details
		$post_data['delv_signature'] = $this->_signData($post_data,'delv_title,delv_fname,delv_sname,delv_addr1,delv_addr2,delv_addr3,delv_city,delv_region,delv_country,delv_zip,ivp_signature');
		// Create signature for return/callback URLs
		$post_data['return_signature'] = $this->_signData($post_data,'return_cb_auth,return_cb_decl,return_cb_can,return_auth,return_decl,return_can,ivp_signature');
		$fields='';
		foreach ($post_data as $k => $v) {
			$fields.=$this->_hiddenField($k,$v);
		}
		$this->data['action'] = 'https://secure.innovatepayments.com/gateway/index.html';
		$this->data['fields'] = $fields;
		$this->template=$this->_whichTemplate('innovatepayments.tpl');
		$this->render();
	}

	public function callback() {
		$remoteAddr = $_SERVER['REMOTE_ADDR'];
		$hash_check=$this->_signData($this->request->post,'auth_status,auth_code,auth_message,auth_tranref,auth_cvv,auth_avs,card_code,card_desc,cart_id,cart_desc,cart_currency,cart_amount,tran_currency,tran_amount,tran_cust_ip');
		if (strcasecmp($this->request->post['auth_hash'],$hash_check)!=0) {
			// Hash check does not match. Data may of been tampered with.
			$this->response->setOutput('Connection refused');
			return;
		}

		//$this->language->load('payment/innovatepayments');
		if ($this->request->post['auth_status']=='A' || $this->request->post['auth_status']=='H') {
			// Transaction was authorised
			$this->load->model('checkout/order');
			$this->model_checkout_order->confirm(
				$this->request->post['cart_id'],
				$this->config->get('innovatepayments_order_status_id'));
			if (strcmp($this->config->get('innovatepayments_test'),'live')==0) {
				$message="";
			} else {
				$message="TEST TRANSACTION\n";
			}
			if (isset($this->request->post['auth_tranref'])) {
				$message .= 'Transaction ID: ' . $this->request->post['auth_tranref'] . "\n";
			}
			if (isset($this->request->post['auth_code'])) {
				$message .= 'Authorisation Code: ' . $this->request->post['auth_code'] . "\n";
			}
			if (isset($this->request->post['card_desc'])) {
				$message .= 'Card Used: ' . $this->request->post['card_desc'] . "\n";
			}
			$this->model_checkout_order->update(
				$this->request->post['cart_id'],
				$this->config->get('innovatepayments_order_status_id'),
				$message,
				false);
		}
		// content is not seen by customer, the customer will be sent directly to the
		// checkout/sucess or checkout/checkout page by the gateway depending on the
		// transaction status.
		$this->response->setOutput('callback done');
	}
}
?>
