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
        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/'.$file.'expand')) {
            $template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/'.$file.'.expand';
        }
        else {
            $template = 'default/template/payment/'.$file.'.expand';
        }
		return $template;
	}

	private function _hiddenField($k,$v) {
		$field="<input type=\"hidden\" name=\"".htmlspecialchars($k)."\" value=\"".htmlspecialchars($v)."\">";
		return $field;
	}

	protected function index() {
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		// get test mode (return 0 or 1)
		$testmode = $this->config->get('innovatepayments_test');

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
			'ivp_method' => 'create',
			'ivp_authkey'=> $this->config->get('innovatepayments_secret'),
			'ivp_store'	=> $this->config->get('innovatepayments_store'),
			'ivp_lang'		=> $this->config->get('config_language'),
			'ivp_cart'	=> time().'_'.trim($order_info['order_id']), // add time to ivp cart to avoid the transaction duplication error
			'ivp_amount'	=> $amount,
			'ivp_currency'	=> trim($order_info['currency_code']),
			'ivp_test'	=> $testmode,
			'ivp_timestamp'	=> '0',
			'ivp_desc'	=> trim($cart_desc),
			'ivp_source'	=> 'ExpandCart',
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
			'return_auth'	=> $this->url->link('payment/innovatepayments/callback', '', true),
			'return_can'	=> $this->url->link('checkout/checkout', ''),
			'return_decl'	=> $this->url->link('payment/innovatepayments/callback', '', true),
			'ivp_update_url'=>  $this->url->link('payment/innovatepayments/ivpcallback', array('cart_id' => trim($order_info['order_id'])), true),
		
		);

		$post_data['ivp_framed']='0';

		$returnData = $this->_requestGateway($post_data);

		$jobj='';
		$redirurl='';
		if(isset($returnData['order'])) {
			$jobj = $returnData['order'];
			$this->session->data['telr_orderref']=$jobj['ref'];
			$redirurl=$jobj['url'];
		} elseif(isset($returnData['error'])) {
			$jobj = $returnData['error'];
			$data['error'] = '<div class="alert alert-danger">InnovatePayments: '.$jobj['message'].' :: '.$jobj['note'].'</div>';
		}

		$data['action'] = $redirurl;
	
		$this->data=$data;
		// $this->template=$this->_whichTemplate('innovatepayments');
        $this->template = 'default/template/payment/innovatepayments.expand';
        
		return $this->render_ecwig();
	}

	private function _isHttps() {
		$url = $this->url->link('checkout/checkout', '', true);

		if (!isset($url)) { return false; }
		
		if (preg_match('#^https:#i', $url) === 1) {
			return true;
		}
		
		return false;
	}

	private function _requestGateway($post_data)
	{
		$url='https://secure.telr.com/gateway/order.json';
		$fields='';
		foreach ($post_data as $k => $v) {
			$fields.=$k .'='.$v . '&';
		}
		$fields = rtrim($fields, '&');

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($fields)));
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch,CURLOPT_POST, count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS,$fields);
		//curl_setopt($ch,CURLOPT_CONNECTTIMEOUT ,10);
		curl_setopt($ch,CURLOPT_TIMEOUT, 30);
		$returnData = json_decode(curl_exec($ch),true);
		//$returnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		return $returnData;
	}

	public function payment_authorised($order_id,$txref) {
		
		$this->load->model('checkout/order');
		$message='Payment Completed: '.$txref;
		$order_status = $cart_desc=trim($this->config->get('innovatepayments_order_status_id'));
		
		if (empty($order_status)) {
			$order_status='2'; // Order status 2 = Processing
		}
		$this->model_checkout_order->confirm(
			$order_id,		// Order ID
			$order_status,		// New order status
			$message,		// Message text to add to order history
			true);			// Notify customer
	}

	public function payment_refund($order_id,$txref,$currency,$amount) {
		$this->load->model('checkout/order');
		$message='Refund of '.$currency.' '.$amount.': '.$txref;
		$order_info = $this->model_checkout_order->getOrder($order_id);
		$order_status='11';

		$this->model_checkout_order->confirm(
			$order_id,		// Order ID
			$order_status,		// New order status
			$message,		// Message text to add to order history
			true);			// Notify customer
	}

	public function payment_cancelled($order_id,$txref) {
		$this->load->model('checkout/order');
		$message='Payment Cancelled: '.$txref;
		$order_status = $cart_desc=trim($this->config->get('payment_telr_void_status_id'));
		if (empty($order_status)) {
			$order_status='7'; // Order status 2 = Cancelled
		}
		$this->model_checkout_order->confirm(
			$order_id,		// Order ID
			$order_status,		// New order status
			$message,		// Message text to add to order history
			true);			// Notify customer
	}

	public function payment_pending($order_id,$txref) {
		$this->load->model('checkout/order');
		$message='Payment Pending: '.$txref;
		$order_status = $cart_desc=trim($this->config->get('payment_telr_pend_status_id'));
		if (empty($order_status)) {
			$order_status='1'; // Order status 1 = Pending
		}
		$this->model_checkout_order->confirm(
			$order_id,		// Order ID
			$order_status,		// New order status
			$message,		// Message text to add to order history
			true);			// Notify customer
	}

	public function check_payment($order_ref) {
		$post_data = Array(
			'ivp_method'	=> 'check',
			'ivp_authkey'	=> $this->config->get('innovatepayments_secret'),
			'ivp_store'	=>  $this->config->get('innovatepayments_store'),
			'order_ref'	=> $order_ref,
		);
		$returnData = $this->_requestGateway($post_data);
		$objOrder='';
		$objError='';
		if (isset($returnData['order'])) { $objOrder = $returnData['order']; }
		if (isset($returnData['error'])) { $objError = $returnData['error']; }
		if (is_array($objError)) { // Failed
			return false;
		}
		if (!isset(
			$objOrder['cartid'],
			$objOrder['status']['code'],
			$objOrder['transaction']['status'],
			$objOrder['transaction']['ref'])) {
			// Missing fields
			return false;
		}
		$new_tx=$objOrder['transaction']['ref'];
		$ordStatus=$objOrder['status']['code'];
		$txStatus=$objOrder['transaction']['status'];
		$cart_id=$objOrder['cartid'];
		$parts=explode('_', $cart_id, 2);
		$order_id=$parts[1];
		if (($ordStatus==-1) || ($ordStatus==-2)) {
			// Order status EXPIRED (-1) or CANCELLED (-2)
			$this->payment_cancelled($order_id,$new_tx);
			return false;
		}
		if (($ordStatus==2) || ($ordStatus==4)) {
			// Order status AUTH (2) or PAYMENT_REQUESTED (4)
			$this->payment_pending($order_id,$new_tx);
			return true;
		}
		if ($ordStatus==3) {
			// Order status PAID (3)
			if (($txStatus=='P') || ($txStatus=='H')) {
				// Transaction status of pending or held
				$this->payment_pending($order_id,$new_tx);
				return true;
			}
			if ($txStatus=='A') {
				// Transaction status = authorised
				$this->payment_authorised($order_id,$new_tx);
				return true;
			}
		}
		return false;
	}

	public function callback_return() {
		if ($this->check_payment($this->session->data['telr_orderref'])) {
			// Authorised
			$this->response->redirect($this->url->link('checkout/success'));
		} else {
			// Declined
			$this->response->redirect($this->url->link('checkout/error'));
		}
	}

	public function callback() {
		if(isset($this->session->data['telr_orderref'])) {
			$this->callback_return();
			return;
		}
		$this->response->redirect($this->url->link('checkout/checkout'));
	}
	public function ivpcallback() {
		$check = $this->_signature(
			$this->request->post,
			$this->config->get('payment_telr_authkey'),
			'tran_store,tran_cartid,tran_order,tran_ref,tran_type,tran_authstatus,tran_authcode,tran_authmessage,tran_currency,tran_amount');
		if (strcasecmp($check,$this->request->post['tran_check'])!=0) {
			$this->response->setOutput("OK");
			return;
		}
		
		$new_tx=$this->request->post['tran_ref'];
		$url_order_id=$this->request->get['cart_id'];
		$txType=intval($this->request->post['tran_type']);
		$txStatus=$this->request->post['tran_authstatus'];
		$cart_id=$this->request->post['tran_cartid'];
		$parts=explode('~', $cart_id, 2);
		$order_id=$parts[0];

		if ($txStatus=='A') {
			switch ($txType) {
				case 2:	// Void
				case 6:	// Cancel
				case 8:	// Capture Reversal
					$this->payment_cancelled($order_id,$new_tx);
					break;
				case 1:	// Sale
				case 7:	// Capture
					$this->payment_authorised($order_id,$new_tx);
					break;
				case 3:	// Refund
					$this->payment_refund($order_id,$new_tx,$this->request->post['tran_currency'],$this->request->post['tran_amount']);
					break;
			}
		}
		$this->response->setOutput("OK");
	}
}
?>
