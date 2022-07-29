<?php
require_once(str_replace("system","knet",DIR_SYSTEM).'e24PaymentPipe.inc.php');
class ControllerPaymentKNET extends Controller {
	private $supporetd_curr = array('KWD', 'INR');
	private $apitest = 'https://www.kpaytest.com.kw';
	private $apilive = 'https://www.kpay.com.kw';

	protected function index() {
		
		$this->language->load_json('payment/knet');
		
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		
        $this->data['action'] = $this->url->link('payment/knet/makepayment', '', 'SSL');
        
        $order_currency = $order_info['currency_code'];
		
		if(!in_array($order_currency, $this->supporetd_curr)){
		
			$this->data['error_knet'] = 'Supported Currencies: '. implode('/', $supporetd_curr);
		
		}else if(isset($this->session->data['error_knet'])){
		
			$data['error_knet'] = $this->session->data['error_knet']['resp_msg'];  
		
		}

        /*$this->data['order_id'] = $this->session->data['order_id'];
        $this->data['description'] = $this->config->get('config_name');

		$this->data['billing_fullname'] = $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
        
		if ($order_info['payment_address_2']) {
            $this->data['billing_address']  = $order_info['payment_address_1'] . "\r\n" . $order_info['payment_address_2'] . "\r\n" . $order_info['payment_city'] . "\r\n" . $order_info['payment_zone'] . "\r\n";
        } else {
            $this->data['billing_address']  = $order_info['payment_address_1'] . "\r\n" . $order_info['payment_city'] . "\r\n" . $order_info['payment_zone'] . "\r\n";
        }
		
        $this->data['billing_postcode'] = $order_info['payment_postcode'];

		if ($this->cart->hasShipping()) {
			$this->data['delivery_fullname'] = $order_info['shipping_firstname'] . ' ' . $order_info['shipping_lastname'];
			
			if ($order_info['shipping_address_2']) {
				$this->data['delivery_address'] = $order_info['shipping_address_1'] . "\r\n" . $order_info['shipping_address_2'] . "\r\n" . $order_info['shipping_city'] . "\r\n" . $order_info['shipping_zone'] . "\r\n";
			} else {
				$this->data['delivery_address'] = $order_info['shipping_address_1'] . "\r\n" . $order_info['shipping_city'] . "\r\n" . $order_info['shipping_zone'] . "\r\n";
			}
		
        	$this->data['delivery_postcode'] = $order_info['shipping_postcode'];
		} else {
			$this->data['delivery_fullname'] = $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
			
			if ($order_info['payment_address_2']) {
				$this->data['delivery_address'] = $order_info['payment_address_1'] . "\r\n" . $order_info['payment_address_2'] . "\r\n" . $order_info['payment_city'] . "\r\n" . $order_info['payment_zone'] . "\r\n";
			} else {
				$this->data['delivery_address'] = $order_info['shipping_address_1'] . "\r\n" . $order_info['payment_city'] . "\r\n" . $order_info['payment_zone'] . "\r\n";
			}
		
        	$this->data['delivery_postcode'] = $order_info['payment_postcode'];			
		}
		
        $this->data['email_address'] = $order_info['email'];
        $this->data['customer_phone_number']= $order_info['telephone'];
        $this->data['success_url'] = $this->url->link('checkout/success', '', 'SSL');
        $this->data['cancel_url'] = $this->url->link('checkout/payment', '', 'SSL');*/

        // if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/knet.expand')) {
        //     $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/knet.expand';
        // }
        // else {
        //     $this->template = 'default/template/payment/knet.expand';
        // }
        $this->template = 'default/template/payment/knet.expand';
		$this->render_ecwig();
	}
	
	public function makepayment()
	{
		unset($this->session->data['error_knet']);

		$curr_codes = array(/*'USD' => 840, */'KWD' => 414, 'INR' => 356);

		$this->language->load_json('payment/knet');
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$order_currency = $order_info['currency_code'];
        if(!in_array($order_currency, $this->supporetd_curr)){
        	$this->session->data['error_knet']['resp_msg'] = 'Supported Currencies:'. implode('/', $this->supporetd_curr);
            $this->response->redirect($this->url->link('checkout/checkout', '', $server_conn_slug));
        }

		$TranTrackid = date('YmdHis');
		$ReqTrackId="trackid=".$TranTrackid;
		$TranAmount = floatval($order_info['total']);
		$ReqAmount="amt=".$TranAmount;
		$TranportalId = $this->config->get('knet_tranportalid');
		$ReqTranportalId="id=".$TranportalId;
		$ReqTranportalPassword="password=".$this->config->get('knet_tranportalpass');
		$ReqCurrency="currencycode=".$curr_codes[$order_currency];
		$ReqLangid="langid=USA";
		$ReqAction="action=1";

		if ($this->request->server['HTTPS']) {
			$site_url = HTTPS_SERVER;
		} else {
			$site_url = HTTP_SERVER;
		}

		$ResponseUrl = $site_url . 'knet_callback.php';
		$ReqResponseUrl="responseURL=".$ResponseUrl;
		$ErrorUrl = $site_url . 'knet_callback.php';
		$ReqErrorUrl="errorURL=".$ErrorUrl;

		$param=$ReqTranportalId."&".$ReqTranportalPassword."&".$ReqAction."&".$ReqLangid."&".$ReqCurrency."&".$ReqAmount."&".$ReqResponseUrl."&".$ReqErrorUrl."&".$ReqTrackId;
		
		$termResourceKey = $this->config->get('knet_terminalreskey');
  		$param=$this->encryptAES($param,$termResourceKey)."&tranportalId=".$TranportalId."&responseURL=".$ResponseUrl."&errorURL=".$ErrorUrl;

  		$api = $this->apitest;
  		if($this->config->get('knet_live') == 1){
  			$api = $this->apilive;
  		}
  		header("Location: $api/kpg/PaymentHTTP.htm?param=paymentInit"."&trandata=".$param); /* Redirect browser */
		exit();

		/*$host = "https://" . $_SERVER['HTTP_HOST'];
		$successUrl = $host . '/success.php';
		$errorUrl = $host . '/error.php';
		$payment = new e24PaymentPipe;
		$payment->setErrorUrl($errorUrl);
		$payment->setResponseURL($successUrl);
		$payment->setLanguage($this->language->get('lang_code'));
		$payment->setCurrency($this->language->get('currency_code')); //USD
		$payment->setResourcePath(str_replace("system","knet",DIR_SYSTEM). STORECODE . '/');
		$payment->setAlias($this->config->get('knet_alias'));
		$payment->setAction("1"); // 1 = Purchase
		$payment->setAmt($order_info['total']);
		$payment->setTrackId($trackID);
		$payment->performPaymentInitialization();
        
		if (strlen($payment->getErrorMsg()) > 0) 
		{
			$this->session->data['error'] = $payment->getErrorMsg();
		}
		else
		{					
			$message = 'PaymentID: ' . $payment->paymentId . "\n";
			$message .= 'TrackID: ' . $trackID . "\n";
			$message .= 'Amount: ' . $order_info['total'] . "\n";
			$message .= 'Time: ' . date('d-m-Y H:i:s') . "\n";
			//Save details in DB before redirecting user to KNET. Else redirect to cart.
			if($order_info)
			{
				$this->load->model('payment/knet');
				$voidOrderStatus = 16;
				$this->model_payment_knet->change_order_status($order_info, $voidOrderStatus, $message);
			
				header('Location: ' . $payment_id = $payment->paymentPage . '?PaymentID=' . $payment->paymentId);
			}
			else
			{
				$this->redirect($this->url->link('checkout/cart'));
			}
		}*/
	}

	
	public function error()
	{           

		// dd($_GET, 'iam get here from error');

		$error = $_GET['ErrorText'];

		$this->session->data['error_knet']['resp_msg'] = $error;
		
		$this->response->redirect($this->url->link('checkout/checkout', '', $server_conn_slug));


	}

	function ATHSetPaymentDetailsToDisplay($order_info, $checkOutType = 'checkout/success'){

		$url =  $this->url->link( $checkOutType , 
		
			 'orderID=' . $this->session->data['order_id']
			 
			 .'&paymentID='.$_REQUEST['paymentid']
			 
			 .'&trackid='.$_REQUEST['trackid']
			 
			 .'&tranid='.$_REQUEST['tranid']

			 .'&total='.$order_info['total'].$order_info['currency_code']

			 .'&datetime='.$order_info['date_added']



			  );


	    return $url;



	}

	
	public function success() {


		// dd($_GET, 'iam get from response knet');
            $this->initializer([
                        'paymentTransaction' => 'extension/payment_transaction',
			'paymentMethod' => 'extension/payment_method',
		]);

		
		
		$this->language->load_json('payment/knet');
		
		if (isset($this->session->data['order_id'])) {
			
			$order_id = $this->session->data['order_id'];
			
		} else {
			
			$order_id = 0;
			
		}

		$this->load->model('checkout/order');
		
		$order_info = $this->model_checkout_order->getOrder($order_id);
		
		// dd($order_info, 'order_info');

		if (!$order_info) {
			$this->session->data['error'] = $this->language->get('error_no_order');
			
			$this->redirect($this->url->link('checkout/cart'));
		}

		



		


		$successUrl = $this->ATHSetPaymentDetailsToDisplay($order_info);

		$failureUrl = $this->ATHSetPaymentDetailsToDisplay($order_info, 'checkout/error');
		
		if (isset($this->session->data['order_id'])) {
		
			$this->cart->clear();

            unset($this->session->data['stock_forecasting_cart']);			
			unset($this->session->data['shipping_method']);
		
			unset($this->session->data['shipping_methods']);
		
			unset($this->session->data['payment_method']);
		
			unset($this->session->data['payment_methods']);
		
			unset($this->session->data['guest']);
		
			unset($this->session->data['comment']);
		
			unset($this->session->data['order_id']);	
		
			unset($this->session->data['coupon']);
		
			unset($this->session->data['reward']);
		
			unset($this->session->data['voucher']);
		
			unset($this->session->data['vouchers']);
		
		}	


		$ResErrorText = $_REQUEST['ErrorText']; 	  	//Error Text/message
		
		$paymentID    = $_REQUEST['paymentid'];		//Payment Id
		
		$ResErrorNo   = $_REQUEST['Error'];           //Error Number
		
		$tranid       = $_REQUEST['tranid'];           //Transaction ID			
		
		//Below Terminal resource Key is used to decrypt the response sent from Payment Gateway.
		$terminalResKey = $this->config->get('knet_terminalreskey');

		$orderStatusID = 0;
                
              $ResTranData= $_REQUEST['trandata'];
                        
              //Decryption logice starts

              $decrytedData=$this->decrypt($ResTranData,$terminalResKey);

               // here we expolod the decryted data from knet response 
               // to create an array with all information that we need to save payment transaction data 

               $str = substr($decrytedData, strpos($str, '=') + 1);
               $arr = explode('&', $decrytedData);
               $data = array();
               foreach ($arr as $item) {
                   $tokens = explode('=', $item);
                   $key = trim($tokens[0]);
                   $val = trim($tokens[1]);
                   $data[$key] = $val;
               }

               if( $ResErrorText == null && $ResErrorNo == null && $data['result'] == 'CAPTURED'){
		
                                $orderStatusID = $this->config->get('knet_order_status_id');	//Set order_status_id = 'as custoemr set in backend'
                                
//				$this->model_checkout_order->confirm($order_id, $this->config->get('config_order_status_id'));
				$this->model_checkout_order->confirm($order_id, $orderStatusID);
		
				//$paymentMessage = 'Your order has been successfully processed! Thanks for shopping with us.';
		
//				$orderStatusID = $this->config->get('config_order_status_id');	//Set order_status_id = 'Pending'				
		
				$message = 'PaymentID: ' . $data['paymentid'] . "\n";		
				$message .= 'TransactionID: ' . $data['tranid'] . "\n";
				$message .= 'TrackID: ' . $data['trackid'] . "\n";
                                
		                $this->paymentTransaction->insert([
                                'order_id' => $order_id,
                                'transaction_id' => $data['ref'],
                                'payment_gateway_id' => $this->paymentMethod->selectByCode('knet')['id'],
                                'payment_method' => 'KNET',
                                'status' => 'Success',
                                'amount' => $data['amt'],
                                'currency' => $order_info['currency_code'],
                                'details' => $ResTranData,
                            ]);

				$this->model_checkout_order->update($order_id, $orderStatusID, $message, false);
                                $this->session->data['order_id'] = $order_id;
                                if($this->customer->isLogged())
                                {
                                    $successMessage = sprintf(
                                        $this->language->get('text_customer'),
                                        $order_id,
                                        $data['amt'].' '.$order_info['currency_code'],
                                        $data['paymentid'],
                                        $data['tranid'],
                                        $data['trackid'],
                                        $order_info['date_added'],
                                        $this->url->link('account/account', '', 'SSL'),
                                        $this->url->link('account/order', '', 'SSL'),
                                        $this->url->link('account/download', '', 'SSL'), $this->url->link('information/contact')
                                    );
                                }else{
                                        $successMessage = sprintf(
                                        $this->language->get('text_guest'),
                                        $order_id,
                                        $data['amt'].' '.$order_info['currency_code'],
                                        $data['paymentid'],
                                        $data['tranid'],
                                        $data['trackid'],
                                        $order_info['date_added'],
                                        $this->url->link('information/contact')
                                    );
                                }
                                
                                
                                $this->session->data['customPaymentDetails']['pg_success_msg'] = $successMessage;
                                
                                $message .= 'Date: ' . $order_info['date_added'] . "\n";
                                
                                $message .= 'Order ID: ' . $order_id . "\n";
                                
                                $message .= 'Amount: ' . $data['amt'].' '.$order_info['currency_code'] . "\n";

                                $this->sendNotificationEmail($order_info['email'], "Knet Tasnaction Notification", $message);

				$this->response->redirect(htmlspecialchars_decode($successUrl));
				
				exit;
		}
		else{
			//$this->load->model('payment/knet');
//			$failedOrderStatus = 10;
			$failedOrderStatus = $this->config->get('entry_order_status_failed');
			//$this->model_payment_knet->change_order_status($order_info, $failedOrderStatus, 'Order failed. Manually check the transaction.');
		
			$paymentMessage = $ResErrorText;
		
			$this->model_checkout_order->confirm($order_id, $failedOrderStatus, $paymentMessage, true);
		
                        $message = 'PaymentID: ' . $data['paymentid'] . "\n";		
                        $message .= 'TransactionID: ' . $data['tranid'] . "\n";
                        $message .= 'TrackID: ' . $data['trackid'] . "\n";
                        
                         $this->paymentTransaction->insert([
                                'order_id' => $order_id,
                                'transaction_id' => $data['ref'],
                                'payment_gateway_id' => $this->paymentMethod->selectByCode('knet')['id'],
                                'payment_method' => 'KNET',
                                'status' => 'Failed',
                                'amount' => $data['amt'],
                                'currency' => $order_info['currency_code'],
                                'details' => $ResTranData,
                         ]);
                        
                        
			$this->model_checkout_order->update($order_id, $failedOrderStatus, $message, false);
		
			// $this->response->redirect( $this->url->link('checkout/error', '', $server_conn_slug) );
			$failMessage = sprintf(
                        $this->language->get('failure_msg'),
                        $order_id,
                        $data['amt'].' '.$order_info['currency_code'],
                        $data['paymentid'],
                        $data['tranid'],
                        $data['trackid'],
                        $order_info['date_added'],
                        $this->url->link('information/contact')
                       );
		        $this->session->data['customPaymentDetails']['pg_fail_msg'] = $failMessage;

			
			$this->response->redirect(htmlspecialchars_decode($failureUrl));
		
			exit;
		}
		
		//Read URL params
		/*$paymentID = $_GET['PaymentID'];
		$presult = $_GET['Result'];
		$postdate = $_GET['PostDate'];
		$tranid = $_GET['TranID'];
		$auth = $_GET['Auth'];
		$ref = $_GET['Ref'];
		$trackid = $_GET['TrackID'];
		// $udf1 = $_GET['UDF1'];
		// $udf2 = $_GET['UDF2'];
		// $udf3 = $_GET['UDF3'];
		// $udf4 = $_GET['UDF4'];
		// $udf5 = $_GET['UDF5'];		
		
		$orderStatusID = 0;
		if ($presult == 'CAPTURED') {
			$this->model_checkout_order->confirm($order_id, $this->config->get('config_order_status_id'));
			$paymentMessage = 'Your order has been successfully processed! Thanks for shopping with us.';
			$orderStatusID = $this->config->get('config_order_status_id');	//Set order_status_id = 'Pending'			
		} else {			
			//$this->load->model('payment/knet');
			$failedOrderStatus = 10;
			//$this->model_payment_knet->change_order_status($order_info, $failedOrderStatus, 'Order failed. Manually check the transaction.');
			$paymentMessage = 'Error processing payment. Order failed.';
			$this->model_checkout_order->confirm($order_id, $failedOrderStatus, $paymentMessage, true);
			$orderStatusID = $failedOrderStatus;	////Set order_status_id = 'Failed'
		}		
		
		$this->data['paymentID'] = $paymentID;
        $this->data['result']= $presult;
		$this->data['paymentDate'] = $postdate;
        $this->data['transID'] = $tranid;
        $this->data['trackID'] = $trackid;
		$this->data['refID'] = $ref;
		$this->data['paymentTime'] = date('H:i:s');
		$this->data['payment_message'] = $paymentMessage;
		
		$message = 'PaymentID: ' . $paymentID . "\n";
		$message .= 'Result: ' . $presult . "\n";
		$message .= 'PostDate: ' . $postdate . "\n";
		$message .= 'TranID: ' . $tranid . "\n";
		$message .= 'Auth: ' . $auth . "\n";
		$message .= 'Ref: ' . $ref . "\n";
		$message .= 'TrackID: ' . $trackid . "\n";
		$message .= 'Time: ' . date('H:i:s') . "\n";
		
		$this->model_checkout_order->update($order_id, $orderStatusID, $message, false);

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/knet_success.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/knet_success.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/payment/knet_success.expand';
        }
		
		$this->children = array(
				'common/footer',
				'common/header'
			);
		
		$this->response->setOutput($this->render_ecwig());*/
	}

	//AES Encryption Method Starts
	private function encryptAES($str,$key) { 
  		$str = $this->pkcs5_pad($str); 
    	$iv = $key;     
    
	    $method = 'AES-128-CBC';
	    $encrypted = openssl_encrypt($str, $method, $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);

	    $encrypted=unpack('C*', ($encrypted));
	    $encrypted=$this->byteArray2Hex($encrypted);
	    $encrypted = urlencode($encrypted);
	    return $encrypted;
    }

	 private function pkcs5_pad ($text) {
	    $blocksize = 16;
	    $pad = $blocksize - (strlen($text) % $blocksize);
	    return $text . str_repeat(chr($pad), $pad);
	    }
	private function byteArray2Hex($byteArray) {
	  $chars = array_map("chr", $byteArray);
	  $bin = join($chars);
	  return bin2hex($bin);
	}
  //AES Encryption Method Ends


	//Decryption Method for AES Algorithm Starts
 private function decrypt($code,$key) { 

 	/*$ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
	$iv = substr($c, 0, $ivlen);
	$hmac = substr($c, $ivlen, $sha2len=32);
	$code = substr($c, $ivlen+$sha2len);
	$original_plaintext = openssl_decrypt($code, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
	$calcmac = hash_hmac('sha256', $code, $key, $as_binary=true);
	if (hash_equals($hmac, $calcmac))//PHP 5.6+ timing attack safe comparison
	{
	    return $original_plaintext;
	}
	return 0;*/

	  $code =  $this->hex2ByteArray(trim($code));
	  $code=$this->byteArray2String($code);
	  $iv = $key; 

	  $method = 'AES-128-CBC';
	  $decrypted = openssl_decrypt($code, $method, $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);


	  /*$td = mcrypt_module_open('rijndael-128', '', 'cbc', $iv); 
	  mcrypt_generic_init($td, $key, $iv);
	  $decrypted = mdecrypt_generic($td, $code); 
	  mcrypt_generic_deinit($td);
	  mcrypt_module_close($td); */
	   return $this->pkcs5_unpad($decrypted);
    }
    
    
  private  function hex2ByteArray($hexString) {
  $string = hex2bin($hexString);
  return unpack('C*', $string);
}


private function byteArray2String($byteArray) {
  $chars = array_map("chr", $byteArray);
  return join($chars);
}


 private function pkcs5_unpad($text) {
	  $pad = ord($text{strlen($text)-1});
	  if ($pad > strlen($text)) {
	      return false;	
	  }
	  if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
	      return false;
	  }
	  return substr($text, 0, -1 * $pad);
    }
	
	//Decryption Method for AES Algorithm Ends
    
     public function sendNotificationEmail($toEmail, $subject, $message) {
        $this->load->model('setting/store');

        $store_name = $this->config->get('config_name');

		if (!empty($toEmail)){
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->hostname = $this->config->get('config_smtp_host');
			$mail->username = $this->config->get('config_smtp_username');
			$mail->password = $this->config->get('config_smtp_password');
			$mail->port = $this->config->get('config_smtp_port');
			$mail->timeout = $this->config->get('config_smtp_timeout');
			$mail->setReplyTo(
				$this->config->get('config_mail_reply_to'),
				$this->config->get('config_name') ? $this->config->get('config_name')[0] : 'ExpandCart',
				$this->config->get('config_email')
			);
			$mail->setTo($toEmail);
			$mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
			$mail->setSender($store_name);
			$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
			$mail->setText($message);
			$mail->send();
		}
    }   
    
    
}
?>
