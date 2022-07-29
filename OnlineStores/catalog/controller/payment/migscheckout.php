<?php
class ControllerPaymentMigsCheckout extends Controller {
	protected function index() {
		$this->language->load('payment/migscheckout');
    	$this->data['button_confirm'] = $this->language->get('button_confirm');
		
		$this->data['migscheckout_type'] = $this->config->get('migscheckout_type');
		
		if($this->data['migscheckout_type'] == 1){

			$this->load->model('checkout/order');
			$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
			
			$vpc_url =  $this->config->get('migscheckout_url').'?';
			
			$callback_url=$this->url->link('payment/migscheckout/callback', '', 'SSL');
			$amount=$this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
				
			$merchantId =$this->config->get('migscheckout_merchant'); //"TESTCBAMOTO1";// get_option('commweb_merchantid');
			$accessCode =$this->config->get('migscheckout_accesscode'); // "E600DB6B";//get_option('commweb_accesscode');
			//$testmode = get_option('commweb_testmode');
			$vpcURL =$this->config->get('migscheckout_url'); // "https://migs-mtf.mastercard.com.au/vpcpay";//"get_option('commweb_form');
			$returnURL = $callback_url; //"http://localhost/2108_wordpress/?page_id=31"; //"http://www.migssupport.com/Testing/migsvpcphp/vpc_php_serverhost_dr.php";//get_option('commweb_return'); // "localhost/2065_carpub/migsvpcphp/vpc_php_serverhost_dr.php"; //get_option('commweb_return');
			$SECURE_SECRET =$this->config->get('migscheckout_secret'); // "934CBB87E23C6E76F37DD111F99C45E2";  //get_option('commweb_secure');
			//$amount =100;// floatval($data['price']['total'])*100;
			if(!$this->config->get('migscheckout_test')){
			$amount=$this->currency->format($order_info['total'], $order_info['currency_code'], 1.00000, false);
			$amount=round($amount*100,2);
			}else{
				$amount=100;
			}
			$order_id = $this->session->data['order_id'];
			//$unique_id = rand(999999,8988888888);
			$SECRET_MODE =$this->config->get('migscheckout_secret_mode');
			$postdata = array(
						"vpc_Version" => '1',
						"vpc_Command" => 'pay',
						"vpc_AccessCode" => $accessCode,
						"vpc_MerchTxnRef" =>$order_id,
						"vpc_Merchant" => $merchantId,
						"vpc_OrderInfo" =>'Payment Order '.$order_id,
						"vpc_Amount" => $amount,
						"vpc_Locale" => 'en',
						"vpc_ReturnURL" => $returnURL);
				$postdata['vpc_Currency']=$order_info['currency_code'];
				ksort ($postdata);
				$vpcURL=$vpcURL.'?';
				$md5Hash=$SECURE_SECRET;
				$appendAmp = 0;
				if($SECRET_MODE == 'MD5')
				{
					foreach ($postdata as $key => $value) {
						if (strlen($value) > 0) {
							if ($appendAmp == 0) {
								$vpcURL .= urlencode($key) . '=' . urlencode($value);
								$appendAmp = 1;
							} else {
								$vpcURL .= '&' . urlencode($key) . "=" . urlencode($value);
							}
							$md5Hash .= $value;
						}
					}
					
					if (strlen($SECURE_SECRET) > 0) {
						$vpcURL .= "&vpc_SecureHash=" . strtoupper(md5($md5Hash));
					}
				}else{ // SHA256
					$hashinput = "";
					foreach($postdata as $key => $value) {
						// create the hash input and URL leaving out any fields that have no value
						 if (strlen($value) > 0) {
							 
							if ($appendAmp == 0) {
								$vpcURL .= urlencode($key) . '=' . urlencode($value);
								$appendAmp = 1;
							} else {
								$vpcURL .= '&' . urlencode($key) . "=" . urlencode($value);
							}
							
							if ((strlen($value) > 0) && ((substr($key, 0,4)=="vpc_") || (substr($key,0,5) =="user_"))) {
							$hashinput .= $key . "=" . $value . "&";
							}
						}
					}
						
					$hashinput = rtrim($hashinput, "&");
					if (strlen($SECURE_SECRET) > 0) {
						$hash_sha256=(strtoupper(hash_hmac('SHA256', $hashinput, pack('H*',$SECURE_SECRET))));
						$vpcURL .= "&vpc_SecureHash=" . $hash_sha256;
						$vpcURL .= "&vpc_SecureHashType=SHA256";
					}
				}
			
			
			$this->data['migscheckout_action']=$vpcURL;
			
		  if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/migscheckout.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/payment/migscheckout.tpl';
			} else {
				$this->template = 'default/template/payment/migscheckout.tpl';
			}	
		}else{		
			$this->data['text_credit_card'] = $this->language->get('text_credit_card');
			$this->data['text_wait'] = $this->language->get('text_wait');
			
			$this->data['entry_cc_owner'] = $this->language->get('entry_cc_owner');
			$this->data['entry_cc_number'] = $this->language->get('entry_cc_number');
			$this->data['entry_cc_expire_date'] = $this->language->get('entry_cc_expire_date');
			$this->data['entry_cc_cvv2'] = $this->language->get('entry_cc_cvv2');
			
			$this->data['months'] = array();
			
			for ($i = 1; $i <= 12; $i++) {
				$this->data['months'][] = array(
					'text'  => strftime('%B', mktime(0, 0, 0, $i, 1, 2000)), 
					'value' => sprintf('%02d', $i)
				);
			}
			
			$today = getdate();

			$this->data['year_expire'] = array();

			for ($i = $today['year']; $i < $today['year'] + 11; $i++) {
				$this->data['year_expire'][] = array(
					'text'  => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)),
					'value' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)) 
				);
			}
			
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/migscheckout.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/payment/migscheckout.tpl';
			} else {
				$this->template = 'default/template/payment/migscheckout.tpl';
			}	
		
		}
		$this->render();
	}
	
	public function send()
	{	
		if ($this->config->get('migscheckout_url')) {
    		$url = $this->config->get('migscheckout_url');
		} else{
			$url = 'https://migs.mastercard.com.au/vpcdps';		
		}	
		$this->load->model('checkout/order');
		
		
		
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		if(!$this->config->get('migscheckout_test')){
			$amount=$this->currency->format($order_info['total'], $order_info['currency_code'], 1.00000, false);
			$amount=round($amount*100,2);
		}else{
			$amount=100;
		}
		$request_fields = array(
						'vpc_Version'			=> "1", 
						'vpc_Command'			=> "pay",
						'vpc_AccessCode'		=> $this->config->get('migscheckout_accesscode'),
						'vpc_MerchTxnRef' 		=> $this->session->data['order_id'],
						'vpc_Merchant' 			=> $this->config->get('migscheckout_merchant'),												  

						'vpc_OrderInfo'			=>$this->session->data['order_id'],
						'vpc_Amount' 			=>$amount,  // Force amount into cents. 
						'vpc_CardNum'			=>str_replace(' ', '', $this->request->post['cc_number']),
						'vpc_cardExp'			=>substr($this->request->post['cc_expire_date_year'],2,4).$this->request->post['cc_expire_date_month'],
						'vpc_CardSecurityCode'  =>$this->request->post['cc_cvv2']
		);
		if(!$this->config->get('migscheckout_test'))  {
					  $request_fields['vpc_Locale']				= $this->config->get('migscheckout_locale');		
  					  $request_fields['vpc_SecureHash']	= $this->config->get('migscheckout_secret');
		}	
		ksort($request_fields);					
		$md5HashData  = "";		
		foreach( $request_fields as $k => $v ) { $md5HashData .= $v; }
		$post="";
		if (!empty($request_fields)) {
			foreach($request_fields AS $key => $val){
				$post .= urlencode($key) . "=" . urlencode($val) . "&";
			}
			$post_data = substr($post, 0, -1);
		}else {
			$post_data = '';
		}
		
		if(!function_exists('curl_init')) 	$json['error'] ='CURL extension is not loaded.';
		//var_dump($post_data);
		//$url = 'https://migs.mastercard.com.au/vpcdps';		
		//$post_data='vpc_AccessCode=69EA7D7D&vpc_Amount=100&vpc_CardNum=4987654321098769&vpc_CardSecurityCode=123&vpc_Command=pay&vpc_MerchTxnRef=129&vpc_Merchant=TEST736975&vpc_OrderInfo=129&vpc_Version=1&vpc_cardExp=1705';
		
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_PORT, 443);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
		$response = curl_exec($curl);
		//var_dump($response); die("@");
		
		$json = array();
		
		if (curl_error($curl)) {
			$json['error'] = 'CURL ERROR: ' . curl_errno($curl) . '::' . curl_error($curl);
			
			$this->log->write('MIGS API CURL ERROR: ' . curl_errno($curl) . '::' . curl_error($curl));	
		} elseif ($response) {
		
			$output = explode(chr (28), $response); // The ASCII field seperator character is the delimiter
				
			if( is_array($output)	)	 

			foreach ($output as $key_value) {
							
				 $value = explode("&", $key_value);   		 
				 foreach ($value as $_key  => $_value) {
				 
				  $v = explode("=", $_value);

					$result[ $v[0] ] = str_replace("+", " " , $v[1] );
				
				}

			}
			//var_dump($result); die("@1");
					
			if (isset($result['vpc_TxnResponseCode']) && $result['vpc_TxnResponseCode'] == '0') {
				
					$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('migscheckout_complete_order_status_id'));
					
					$message = '';
					
					if (isset($result['vpc_Message'])) {
						$message .= 'MIGS Message: ' . $result['vpc_Message'] . "\n";
					}
					
					if (isset($result['vpc_ReceiptNo'])) {
						$message .= 'ReceiptNo: ' . $result['vpc_ReceiptNo'] . "\n";
					}
			
					if (isset($result['vpc_TransactionNo'])) {
						$message .= 'TransactionNo: ' . $result['vpc_TransactionNo'] . "\n";
					}
	
					$this->model_checkout_order->update($this->session->data['order_id'], $this->config->get('migscheckout_complete_order_status_id'), $message, false);				
					
				$json['success'] = $this->url->link('checkout/success', '', 'SSL');
			} else {
				$json['error'] =$result['vpc_Message'];
			}
		} else {
			$json['error'] = 'Empty Gateway Response';
			
			$this->log->write('MIGS AIM CURL ERROR: Empty Gateway Response');
		}
		
		curl_close($curl);
		
		$this->response->setOutput(json_encode($json));
		
	}
	
	public function callback() {
		if (isset($this->request->get['vpc_MerchTxnRef'])) {
			$order_id = $this->request->get['vpc_MerchTxnRef'];
		} else {
			$order_id = 0;
		}	
		
		$this->load->model('checkout/order');
		
		$order_info = $this->model_checkout_order->getOrder($order_id);
		
		
		$this->data['x_response_reason_text'] = $this->request->get['vpc_Message'];
		$this->data['x_response_code'] =  $this->request->get['vpc_TxnResponseCode'];
		if($this->request->get['vpc_TxnResponseCode']=='0'){
		$this->data['vpc_ReceiptNo'] =  $this->request->get['vpc_ReceiptNo'];
		$this->data['vpc_TransactionNo'] = $this->request->get['vpc_TransactionNo'];
		}
			
		$this->data['order_id'] =  $this->request->get['vpc_MerchTxnRef'];
		
		$this->data['button_confirm'] = $this->language->get('button_continue');
		$this->data['confirm'] = $this->url->link('checkout/success', '', 'SSL');
		$this->data['back'] = $this->url->link('checkout/checkout', '', 'SSL');
		$this->data['button_back'] =$this->language->get('button_back');
		
		if($this->request->get['vpc_TxnResponseCode']=='0') {
				$order_status_id = $this->config->get('migscheckout_complete_order_status_id');
		}else{
				$order_status_id = $this->config->get('migscheckout_denied_order_status_id');
		}
		
		if ($order_info) {
			$this->model_checkout_order->confirm($order_id,$order_status_id);
		}
			
		
		
		$this->document->breadcrumbs = array(); 

      	$this->document->breadcrumbs[] = array(
        	'href'      => $this->url->link('common/home','','SSL'),
        	'text'      => $this->language->get('text_home'),
        	'separator' => false
      	); 

		
      	$this->document->breadcrumbs[] = array(
        	'href'      => $this->url->link('checkout/cart','','SSL'),
        	'text'      => $this->language->get('text_basket'),
        	'separator' => $this->language->get('text_separator')
      	);	
      

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/migscheckout_callback.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/payment/migscheckout_callback.tpl';
		} else {
				$this->template = 'default/template/payment/migscheckout_callback.tpl';
		}			
		$this->children = array(
            'common/column_left',
            'common/column_right',
            'common/content_top',
            'common/content_bottom',
            'common/footer',
            'common/header'
        );
		//$this->render();
		$this->response->setOutput($this->render(true));
		
	}
}
?>