<?php

class ControllerPaymentCcavenuepay extends Controller {
	protected function index() {
        $this->name = basename(__FILE__, '.php');

		$this->language->load_json('payment/ccavenuepay');
		$this->data['button_confirm'] = $this->language->get('button_confirm');
		$this->data['action'] = 'https://secure.ccavenue.ae/transaction/transaction.do?command=initiateTransaction';
		$this->load->model('checkout/order');
		$action = $this->data['action'];
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
					 
		if ($order_info) {
			$currencies = array(
				'AUD','CAD','EUR','GBP','JPY','USD','NZD','CHF','HKD','SGD',
				'SEK','DKK','PLN','NOK','HUF','CZK','ILS','MXN','MYR','BRL',
				'PHP','TWD','THB','TRY','INR'
			);
			if (in_array($order_info['currency_code'], $currencies)) {
				$currency = $order_info['currency_code'];
			} else {
				$currency = 'USD';
			}
			//$currency='INR';
			$language='EN';
			$shipping_total = 0;
			if ($this->cart->hasShipping()) 
			{
				$taxes = $this->cart->getTaxes();
				$tax_total = 0;
				foreach ($taxes as $key => $value) 
				{
					$tax_total += $value;
				}
				$this->data['tax'] 	= $this->currency->format($tax_total, $currency, false, false);
				$shipping_total 	= $this->session->data['shipping_method']['cost'];
				$this->data['shipping'] = $this->currency->format($shipping_total, $currency, false, false);
				foreach ($this->cart->getProducts() as $product) 
				{
					$price = $product['price'];
					$order_info['total'] += ($price * $product['quantity'] + $this->data['shipping'] + $this->data['tax']);
				
				}
				
			}						
			else
			{
				$taxes = $this->cart->getTaxes();
				$tax_total = 0;
				foreach ($taxes as $key => $value) 
				{
					$tax_total += $value;
				}

				$this->data['tax'] = $this->currency->format($tax_total, $currency, false, false);
				foreach ($this->cart->getProducts() as $product)
				{
					$price = $product['price'];
					$order_info['total'] += ($price * $product['quantity'] + $this->data['tax'] );
				}
				
			}
			// Get the final order total 
			$results = $this->model_setting_extension->getExtensions('total');
			foreach ($results as $key => $value) {
						$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
					}

			array_multisort($sort_order, SORT_ASC, $results);
			$order_final_total=0;
			$total_data = array();
			$total	= 0;
			foreach ($results as $result) {
				
				if ($this->config->get($result['code'] . '_status')) {
					$this->load->model('total/' . $result['code']);
					 
					$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
				}
				$sort_order = array(); 				
				foreach ($total_data as $key => $value) {
					if(isset($value['code']) &&($value['code']=='total'))
					{
						if(isset($value['value'])) $order_final_total =$value['value'];
						 
					}
												 
				}
					
			}
			$total = $this->currency->format($order_info['total'] - $this->cart->getSubTotal(), $order_info['currency_code'], false, false);
			$total = $this->currency->format($order_info['total'] - $this->cart->getSubTotal(), $order_info['currency_code'], false, false);
			
			$Amount = $this->currency->format($order_info['total'],$currency, false, false);
			$this->data['Merchant_Id'] 		= $this->config->get('ccavenuepay_merchant_id');
			$this->data['Order_Id'] 		= $this->session->data['order_id'];
			$this->data['currency_code'] 	= $currency;
			$this->data['Amount'] 			= $Amount;
			$Merchant_Id 	= $this->config->get('ccavenuepay_merchant_id');
			$access_code 	= $this->config->get('ccavenuepay_access_code');
			$Order_Id 		= $this->session->data['order_id'];
			$Amount  		= $order_final_total;//$this->data['Amount'];//$Amount;
			$Url 			= $this->url->link('payment/ccavenuepay/callback');
			$EncryptionKey 	= $this->config->get('ccavenuepay_encryption_key');
			$pattern 		= '#http://www.#';
			preg_match($pattern, $Url, $matches);
			if(count($matches)==0)
			{
				$find_pattern   = '#http://#';
				$replace_string  = 'http://www.';
				$Url = preg_replace($find_pattern,$replace_string,$Url);
			}
			$Url 		= $this->url->link('payment/ccavenuepay/callback');
			$billing_name = '';
			$billing_address = '';
			$billing_city	= '';
			$billing_state	='';
			$billing_tel	= '';
			$billing_zip	='';
			$billing_country ='';
			$billing_email	= '';
			if($order_info['payment_firstname'])
			{
				$customer_firstname 				=  html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8');	
				$customer_lastname 				    =  html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');	
				$this->data['billing_cust_name'] 	= $customer_firstname." ".$customer_lastname;
				$billing_name						= $this->data['billing_cust_name'] ;
				$address1  							= html_entity_decode($order_info['payment_address_1'], ENT_QUOTES, 'UTF-8');	
				$address2  							= html_entity_decode($order_info['payment_address_2'], ENT_QUOTES, 'UTF-8');	
				$this->data['billing_cust_address'] = $address1." ".$address2;
				$billing_address 					= $this->data['billing_cust_address'];
				$this->data['billing_cust_city'] 	= html_entity_decode($order_info['payment_city'], ENT_QUOTES, 'UTF-8');
				$billing_city 						= $this->data['billing_cust_city'];
				$this->load->model('localisation/zone');
				$zone 								= $this->model_localisation_zone->getZone($order_info['payment_zone_id']);
				
				if (isset($zone['code'])) {
					$this->data['billing_cust_state'] 	= html_entity_decode($zone['code'], ENT_QUOTES, 'UTF-8');
					$billing_state						= $this->data['billing_cust_state'];
				}
				$this->data['billing_cust_tel'] 		= html_entity_decode($order_info['telephone'], ENT_QUOTES, 'UTF-8');		
				$billing_tel							= $this->data['billing_cust_tel'] ;
				$this->data['billing_zip_code'] 		= html_entity_decode($order_info['payment_postcode'], ENT_QUOTES, 'UTF-8');
				$billing_zip							= $this->data['billing_zip_code']; 
				$billing_country_iso_code_3 			= $order_info['payment_iso_code_3'];
				$billing_country_query 					= $this->db->query("SELECT name FROM " . DB_PREFIX . "country where iso_code_3='".$billing_country_iso_code_3."'");
				$billing_country_name 					= $billing_country_query->row['name'];
				$billing_country						= $billing_country_query->row['name'];
				$this->data['billing_cust_country'] 	= $billing_country_name;
				$billing_country						=$this->data['billing_cust_country'];
			}
			$delivery_name	='';
			$delivery_address ='';
			$delivery_city	='';
			$delivery_state	='';
			$delivery_tel	='';
			$delivery_zip	='';
			$delivery_country ='';
			$merchant_param1 ='';
			
			if($order_info['shipping_firstname'])
			{
				$customer_firstname 				= html_entity_decode($order_info['shipping_firstname'], ENT_QUOTES, 'UTF-8');	
				$customer_lastname  				= html_entity_decode($order_info['shipping_lastname'], ENT_QUOTES, 'UTF-8');	
				$this->data['delivery_cust_name'] 	= $customer_firstname." ".$customer_lastname;
				$delivery_name						= $this->data['delivery_cust_name'] ;
				$address1  							= html_entity_decode($order_info['shipping_address_1'], ENT_QUOTES, 'UTF-8');	
				$address2  							= html_entity_decode($order_info['shipping_address_2'], ENT_QUOTES, 'UTF-8');	
				$this->data['delivery_cust_address']= $address1." ".$address2;
				$delivery_address					= $this->data['delivery_cust_address'];									
				$this->data['delivery_cust_city'] 	= html_entity_decode($order_info['shipping_city'], ENT_QUOTES, 'UTF-8');
				$delivery_city						= $this->data['delivery_cust_city'];
				$this->load->model('localisation/zone');
				$zone = $this->model_localisation_zone->getZone($order_info['shipping_zone_id']);
				if (isset($zone['code'])) {
					$this->data['delivery_cust_state'] 	= html_entity_decode($zone['code'], ENT_QUOTES, 'UTF-8');
					$delivery_state						= $this->data['delivery_cust_state'];
				}
				$this->data['delivery_cust_tel'] 		= html_entity_decode($order_info['telephone'], ENT_QUOTES, 'UTF-8');		
				$delivery_tel							= $this->data['delivery_cust_tel'];
				$this->data['delivery_zip_code'] 		= html_entity_decode($order_info['shipping_postcode'], ENT_QUOTES, 'UTF-8');
				$delivery_zip							= $this->data['delivery_zip_code'];
				$delivery_country_iso_code_3 			= $order_info['shipping_iso_code_3'];
				$delivery_country_query 				= $this->db->query("SELECT name FROM " . DB_PREFIX . "country where iso_code_3='".$billing_country_iso_code_3."'");
				$delivery_country_name 					= $delivery_country_query->row['name'];
				$this->data['delivery_cust_country'] 	= $delivery_country_name;
				$delivery_country						=$delivery_country_query->row['name'];
				$delivery_country						= $this->data['delivery_cust_country'];
			}
			$this->data['billing_cust_email']   = $order_info['email'];
			$billing_email						=$this->data['billing_cust_email'];
			$this->data['billing_cust_notes']   = $this->session->data['comment'];
			$merchant_param1					= $this->data['billing_cust_notes'];
			$this->data['redirect_url']         = $this->url->link('payment/ccavenuepay/callback');
			$redirect_url						= $this->data['redirect_url'];
			$this->data['cancel_url']         	= $this->url->link('payment/ccavenuepay/callback');
			$cancel_url							= $this->data['cancel_url'];
			$this->load->library('encryption');
			$encryption 			= new Encryption($this->config->get('config_encryption'));
			$this->data['custom'] 	= $encryption->encrypt($this->session->data['order_id']);
			
			$merchant_data_array = array();
			$merchant_data_array['merchant_id'] = $Merchant_Id;
			$merchant_data_array['order_id']= $Order_Id;
			$merchant_data_array['currency']=$this->currency->getCode();
		//	$merchant_data_array['currency']= 'AED';
			$merchant_data_array['amount'] = $Amount;
			$merchant_data_array[ 'redirect_url']= $redirect_url;
			$merchant_data_array['cancel_url='] = $cancel_url;
			$merchant_data_array['language']= $language;
			$merchant_data_array['billing_name']= $billing_name;
			$merchant_data_array['billing_address=']= $billing_address;
			$merchant_data_array['billing_city']= $billing_city;
			$merchant_data_array['billing_state']= $billing_state;
			$merchant_data_array['billing_zip']= $billing_zip;
			$merchant_data_array['billing_country']= $billing_country;
			$merchant_data_array['billing_tel']= $billing_tel;
			$merchant_data_array['billing_email']=$billing_email;
			$merchant_data_array[ 'delivery_name']=$delivery_name;
			$merchant_data_array['delivery_address']= $delivery_address;
			$merchant_data_array[ 'delivery_city']= $delivery_city;
			$merchant_data_array['delivery_state']= $delivery_state;
			$merchant_data_array['delivery_zip']= $delivery_zip;
			$merchant_data_array[ 'delivery_country']=$delivery_country;
			$merchant_data_array[ 'delivery_tel']=$delivery_tel;
			$merchant_data_array[ 'merchant_param1']=$merchant_param1;
			$merchant_data = implode("&",$merchant_data_array);
			$customer_info = '';
			foreach ($merchant_data_array as $key => $value)
			{
				$customer_info.=$key.'='.urlencode($value).'&';
			}					
			$this->data['encrypted_data'] = $this->encrypt($customer_info,$EncryptionKey);
			$this->data['access_code']	  = $access_code ;

            // if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/' . $this->name . '.expand')) {
            //     $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/' . $this->name . '.expand';
            // }
            // else {
            //     $this->template = $this->config->get('config_template') . '/template/payment/' . $this->name . '.expand';
            // }
            $this->template = 'default/template/payment/' . $this->name . '.expand';
            $this->render_ecwig();
		}
	}
	 
	public function callback() 
	{
        $this->name = basename(__FILE__, '.php');
		$EncryptionKey 	= $this->config->get('ccavenuepay_encryption_key');
		$encResponse	= $_REQUEST["encResp"];		//This is the response sent by the CCAvenue Server
		$rcvdString=$this->decrypt($encResponse,$EncryptionKey);	//Crypto Decryption used as per the specified working key.
		$decryptValues=explode('&', $rcvdString);
		$dataSize=sizeof($decryptValues);
		$response_array		= array();
		
		for($i = 0; $i < count($decryptValues); $i++) 
		{
	  		$information	= explode('=',$decryptValues[$i]);
			if(count($information)==2)
			{
				$response_array[$information[0]] = urldecode($information[1]);
			}
			  
		}
		$order_status		= '';
		$order_id    		= '';
		$tracking_id		= '';
		$bank_ref_no 		= '';
		$failure_message 	= '';
		$payment_mode 		= '';
		$card_name    		= '';
		$status_code  		= '';
		$status_message 	= '';
		$currency       	= '';
		$amount				= '';
		$payment_status_message='';
		
		if(isset($response_array['order_id'])) $order_id 		= $response_array['order_id'];
		if(isset($response_array['tracking_id'])) $tracking_id 	= $response_array['tracking_id'];
		if(isset($response_array['bank_ref_no'])) $bank_ref_no 	= $response_array['bank_ref_no'];
		if(isset($response_array['order_status'])) $order_status = $response_array['order_status'];
		if(isset($response_array['failure_message'])) $failure_message = $response_array['failure_message'];
		if(isset($response_array['payment_mode'])) $payment_mode = $response_array['payment_mode'];
		if(isset($response_array['card_name'])) $card_name 		= $response_array['card_name'];
		if(isset($response_array['status_code'])) $status_code 	= $response_array['status_code'];
		if(isset($response_array['status_message'])) $status_message 	= $response_array['status_message'];
		if(isset($response_array['currency'])) $currency 		= $response_array['currency'];
		if(isset($response_array['amount'])) $amount 			= $response_array['amount'];
		
		$this->load->language('payment/ccavenuepay');
		$this->load->library('encryption');
		$this->load->model('checkout/order');
		$order_info 	= $this->model_checkout_order->getOrder($order_id);
		if($order_info)
		{
			$this->language->load_json('payment/ccavenuepay');
			$data = array(
					//"EncryptionKey"	=> $EncryptionKey,
					//"Merchant_Id"	=> $Merchant_Id,
					"order_id"			=> $order_id,
					"tracking_id"		=> $tracking_id,
					"bank_ref_no"		=> $bank_ref_no,
					"order_status"		=> $order_status,
					"failure_message"	=> $failure_message,
					"payment_mode"		=> $payment_mode,
					"card_name"			=> $card_name,
					"status_code"		=> $status_code,
					"status_message"	=> $status_message,
					"currency"			=> $currency,
					"amount"			=> $amount
				
				);
			
			if (isset($order_info['order_id'])) {
				$order_id = $order_info['order_id'];
			} else {
				$order_id = $data['order_id'];
			
			}
			//$order_status=""; // only for testing purpose
			if($order_status=="Success")
			{	
				$payment_status_message = $this->language->get('success_comment');
				$payment_status  = true;
				$order_status_id = $this->config->get('ccavenuepay_completed_status_id');
				if (!$order_info['order_status_id']) {
					$this->model_checkout_order->confirm($order_id, $order_status_id, $payment_status_message);
				} else {
					$this->model_checkout_order->update($order_id, $order_status_id, $payment_status_message, FALSE);
				}
				$payment_confirmation_mail =  $this->config->get('ccavenuepay_payment_confirmation_mail');
				if($payment_confirmation_mail)
				{	
					
					$subject  = 'CCAvenue MCPG Payment Status';
					$text     = "Dear ".$order_info['firstname']." ".$order_info['lastname']. "\n\n";
					$text    .= "We have received your order, Thanks for your Ccavenue payment.The transaction was successful.Your payment is authorized.". "\n";
					$text    .= "The details of the order are below:". "\n\n";
					$text    .= "Order ID:  ".$order_id. "\n";
					$text    .= "Date Ordered:  ".$order_info['date_added']. "\n";
					$text    .= "Payment Method:  ".$order_info['payment_method']. "\n";
					$text    .= "Shipping Method:  ".$order_info['shipping_method']. "\n";
					$text    .= "Order Total:  ".$order_info['total']. "\n\n";				
					$to 	  = array(1=>$order_info['email'],2=>(!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
					$mail 	  = new Mail();
					$mail->setReplyTo(
		                $this->config->get('config_mail_reply_to'),
		                $this->config->get('config_name') ? $this->config->get('config_name')[0] : 'ExpandCart',
		                $this->config->get('config_email')
		            );
					$mail->setTo($to);
					$mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
					$mail->setSender($this->config->get('config_name'));
					$mail->setSubject($subject);
					$mail->setText(html_entity_decode($text, ENT_QUOTES, 'UTF-8'));
					$mail->send();
					mail($order_info['email'],'Ccavenue Payment Status','Your payment is authorized.');
				}
			
			}
			else if($order_status=="Aborted")
			{
				$payment_status_message = $this->language->get('pending_comment');
				$payment_status = false;
				$order_status_id = $this->config->get('ccavenuepay_pending_status_id');
				if (!$order_info['order_status_id']) {
					$this->model_checkout_order->confirm($order_id, $order_status_id, $payment_status_message);
				} else {
					$this->model_checkout_order->update($order_id, $order_status_id, $payment_status_message, FALSE);
					
				}
				
			}
			else if($order_status=="Failure")
			{
				$payment_status_message =$this->language->get('declined_comment');
				$payment_status = false;
				$order_status_id = $this->config->get('ccavenuepay_failed_status_id');
				
				if (!$order_info['order_status_id']) {
					$this->model_checkout_order->confirm($order_id, $order_status_id, $payment_status_message);
					
				} else {
					$this->model_checkout_order->update($order_id, $order_status_id, $payment_status_message, FALSE);
				}	
			}
			
			else
			{
				$payment_status_message = $this->language->get('failed_comment');							
				$payment_status = false;				
				$order_status_id = $this->config->get('ccavenuepay_failed_status_id');				
				if (!$order_info['order_status_id']) {
					$this->model_checkout_order->confirm($order_id, $order_status_id, $payment_status_message);
				} else {
					$this->model_checkout_order->update($order_id, $order_status_id, $payment_status_message, FALSE);
				}

			}
			

			/**************** Start pay failure logic ************/
			$this->data['title'] 	= sprintf($this->language->get('ccavenuepay_heading_title'), $this->config->get('config_name'));
			if (!isset($this->request->server['HTTPS']) || ($this->request->server['HTTPS'] != 'on')) {
				$this->data['base'] = HTTP_SERVER;
			} else {
				$this->data['base'] = HTTPS_SERVER;
			}
			$this->data['language'] 		= $this->language->get('code');
			$this->data['direction'] 		= $this->language->get('direction');
			$this->data['heading_title'] 	= sprintf($this->language->get('ccavenuepay_heading_title'), $this->config->get('config_name'));
			$this->data['text_response'] 	= $this->language->get('text_response');
			$this->data['payment_status_message'] = $payment_status_message;				
			
			if($payment_status)
			{
				$this->data['text_payment_wait'] = sprintf($this->language->get('text_payment_wait'), $this->url->link('checkout/success'));
				$this->data['continue'] = $this->url->link('checkout/success');
			}
			else
			{
				$this->data['text_payment_wait'] = sprintf($this->language->get('text_payment_wait'), $this->url->link('checkout/cart'));
				$this->data['continue'] = $this->url->link('checkout/cart');
				
			}

            // if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/' . $this->name . '.expand')) {
            //     $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/' . $this->name . '.expand';
            // }
            // else {
            //     $this->template = $this->config->get('config_template') . '/template/payment/' . $this->name . '.expand';
            // }
            $this->template = 'default/template/payment/' . $this->name . '.expand';
            $this->response->setOutput($this->render_ecwig());
		}
	}
	
/* Encrypt and Decrypt functions*/
	
	function encrypt($plainText,$key)
	{
		$secretKey = $this->hextobin(md5($key));
		$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
	  	$openMode = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '','cbc', '');
	  	$blockSize = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, 'cbc');
		$plainPad = $this->pkcs5_pad($plainText, $blockSize);
	  	if (mcrypt_generic_init($openMode, $secretKey, $initVector) != -1) 
		{
		      $encryptedText = mcrypt_generic($openMode, $plainPad);
	      	      mcrypt_generic_deinit($openMode);
		      			
		} 
		return bin2hex($encryptedText);
	}

	function decrypt($encryptedText,$key)
	{
		$secretKey = $this->hextobin(md5($key));
		$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
		$encryptedText=$this->hextobin($encryptedText);
	  	$openMode = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '','cbc', '');
		mcrypt_generic_init($openMode, $secretKey, $initVector);
		$decryptedText = mdecrypt_generic($openMode, $encryptedText);
		$decryptedText = rtrim($decryptedText, "\0");
	 	mcrypt_generic_deinit($openMode);
		return $decryptedText;
		
	}
	//*********** Padding Function *********************

	 function pkcs5_pad ($plainText, $blockSize)
	{
	    $pad = $blockSize - (strlen($plainText) % $blockSize);
	    return $plainText . str_repeat(chr($pad), $pad);
	}

	//********** Hexadecimal to Binary function for php 4.0 version ********

	function hextobin($hexString) 
   	 { 
        	$length = strlen($hexString); 
        	$binString="";   
        	$count=0; 
        	while($count<$length) 
        	{       
        	    $subString =substr($hexString,$count,2);           
        	    $packedString = pack("H*",$subString); 
        	    if ($count==0)
		    {
				$binString=$packedString;
		    } 
        	    
		    else 
		    {
				$binString.=$packedString;
		    } 
        	    
		    $count+=2; 
        	} 
  	        return $binString; 
    	  }
		  
		
}
?>
