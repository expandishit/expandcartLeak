<?php
class ModelModuleWhatsapp extends Model {
    
    private function get_admins_numbers(){
        $admins = "";
         
        if($this->config->get('whatsapp_config_notify_admin_by_WhatsApp_on_checkout')){    //coded this way because we can send to additional numbers even if the notify admin setting is off.
            $admins = $this->config->get('config_telephone');
        }else{
           // if (WhatAppCommons::log($this->config)) $this->log->write('[whatsapp] Notify admin setting is off.. Checking the additional numbers..');
            WhatAppCommons::clientLog('[whatsapp] Notify admin setting is off.. Checking the additional numbers..',$this->config);
        }
        
        $admin_extra_numbers = $this->config->get('whatsapp_config_notify_extra_by_WhatsApp_on_checkout');
        if(!empty($admin_extra_numbers)){
            if(! empty($admins)){
                $admins .= ',';
            }
            $admins .= $admin_extra_numbers ;
        }
        
        return $admins;
    }
   
     /**
     * 
     */
    public function notify_or_not_customer_on_checkout($order_info, $order_total_query){

		if(DEV_MODE) return;   
        WhatAppCommons::clientLog('[whatsapp] checking notify or not customer on checkout',$this->config);
        // check if customer allow receive messages
        $customerAllowReceiveMsgs = (int)($this->config->get('whatsapp_config_customer_allow_receive_messages') ?? 0);
        if (!$customerAllowReceiveMsgs) {
            return;
        }
        
        $WhatAppCommons = new WhatAppCommons($this->config);
        
        $keyword_found = false;
        
        //
        // Get coupon
        //
        $donotsend_coupon_keywords = $this->config->get('whatsapp_cfg_donotsend_WhatsApp_on_checkout_coupon_keywords');
       
        if (! $WhatAppCommons->isNullOrEmptyString($donotsend_coupon_keywords) && $order_total_query) {
            
            foreach ( $order_total_query->rows as $result ) {
                $code = $result ['code'];
                if ($code == 'coupon') {
                    if (WhatAppCommons::log($this->config)) $this->log->write('[whatsapp] Customer used coupon so handle coupon');
                    
                    $title = $result ['title'];
                    
                    // foreach whatsapp do-not-send message keyword
                    foreach ( preg_split ( "/((\r?\n)|(\r\n?))/", $donotsend_coupon_keywords ) as $donotsend_coupon_keyword ) {
                        // if coupon contains one of these keywords, then set the do not send SMS boolean to false.
                        $pos = strpos ( $title, $donotsend_coupon_keyword );
                        if ($pos !== false) {
                            $keyword_found = true;
                            if (WhatAppCommons::log($this->config)) $this->log->write('[whatsapp] keyword found, we should not send message to customer on checkout.');
                            break;
                        }
                    }
                    break;
                }
            }
        }
        
				
        if($keyword_found === false) {
            
            $whatsapp_patterns   = $this->config->get('whatsapp_config_number_filtering');

            if(WhatsAppPhonenumberFilter::isNumberAuthorized($order_info['telephone'], $whatsapp_patterns)) {

				$msg_salt 	   = $this->config->get('whats_cfg_msg_salt');
                $payment_code  = strtolower($order_info['payment_code']);
				$template_to   = WhatsAppPhonenumberFilter::rewritePhoneNumber($order_info['telephone'], $this->config);
                
				if (
					array_key_exists('check_if_failed',$order_info) 
					&&$WhatAppCommons->paymentFailed($payment_code,$order_info['check_if_failed'])
					){
					
					$whatsapp_template  = $WhatAppCommons->getObserverTemplate($order_info['check_if_failed'], 'whatsapp_cfg_odr_observers');
					
					//if not template for this status at  do nothing 
					if(empty($whatsapp_template)){
						WhatAppCommons::clientLog('[whatsapp] no template exists at observer with this status :'. $order_info['check_if_failed'],$this->config);//for test 
						return;
					}
					
					$lang_code 			= $this->language->get('code');
					$template_name 		= 'whatsapp_cfg_odr_observers_'.$order_info['check_if_failed'].'_'.$msg_salt;
					$template_header 	= $whatsapp_template['header_'.$lang_code];
					$template_body   	= $whatsapp_template['body_'.$lang_code];
		
                } else if($this->config->get('whatsapp_config_notify_customer_by_WhatsApp_on_checkout')){
					$template_name 	 = 'customer_checkout_'.$msg_salt;
					$template_header = $this->config->get('whatsapp_msg_customer_checkout_'.$order_info['language_code'].'_HEADER');
					$template_body 	 = $this->config->get('whatsapp_msg_customer_checkout_'.$order_info['language_code'].'_BODY');
                }else {
					//do nothing 
					return ;
				}
				
				$template_to = WhatsAppPhonenumberFilter::rewritePhoneNumber($order_info['telephone'], $this->config);
                $msg_salt 	 = $this->config->get('whats_cfg_msg_salt');

				$template_language			= [
												"policy"=>"deterministic",
												"code"=> $order_info['language_code']
												];

				$header_parameters = $WhatAppCommons->get_whatsapp_order_variables( $template_header,$order_info,'');
				$body_parameters   = $WhatAppCommons->get_whatsapp_order_variables( $template_body,$order_info,'');
				
				$components = [];
				if (!empty($header_parameters)){
					$components[] = [
									"type"		=> "header",
									"parameters"=> $header_parameters
								];
				}
				
				if (!empty($body_parameters)){
					$components[] = [
									"type"		=> "body",
									"parameters"=> $body_parameters
								];
				}
				
				 WhatAppCommons::clientLog('[whatsapp] should sent template ',$this->config);
				
				$res= $WhatAppCommons->sendTemplateMessage($template_to,$template_name,$template_language,$components);
				$this->track_event('notification', $template_to);
			}
        }
        
    }

	/**
     * 
     */ 
    public function notify_or_not_admin_on_checkout($order_info, $order_total_query, $order_status, $products, $vouchers){
       
		 if(!$this->config->get('whatsapp_config_notify_admin_by_WhatsApp_on_checkout'))    
		 {
			return;
		 }
		 
        if (WhatAppCommons::log($this->config)) $this->log->write('[whatsapp] Notify (or not) admin(s) on checkout..');
        
        $WhatAppCommons = new WhatAppCommons ( $this->config);
        $order_id = $order_info ['order_id'];
        $language = new Language ( $order_info ['language_directory'] );
        $language->load ( $order_info ['language_filename'] );
        $language->load ( 'mail/order' );
        
        $admins = $this->get_admins_numbers();
        
        if (empty ( $admins )) {
            if (WhatAppCommons::log ( $this->config ))
                $this->log->write ( '[whatsapp] No admins phone number and there are no extra admin phone numbers. No SMS will be sent. Abort!' );
            return;
        }
        
        // retrieve the template:
       // $sms_template = $this->config->get ( 'smshare_config_sms_template_for_storeowner_notif_on_checkout' );
        
        $whatsapp_order_total = '';
        if($order_total_query){
            $whatsapp_order_total = $WhatAppCommons->doGetOrderTotal ( $order_total_query->rows,$this->config );
        }
          
        if(!empty($order_info['order_status_id']))
            {
                $order_status_query = $this->db->query("SELECT name FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_info['order_status_id'] . "' AND language_id = '" . (int)$order_info['language_id'] . "'");
                $order_info['status_name'] = $order_status_query->row['name'];
            }
				
        
		$msg_salt = $this->config->get('whats_cfg_msg_salt');
		
		$template_name = 'admin_checkout_'.$msg_salt;
		$template_language	= [
								"policy"=>"deterministic",
								"code"=> $order_info['language_code']
							];
		
		$header_template = $this->config->get('whatsapp_msg_admin_checkout_'.$order_info['language_code'].'_HEADER');
		$body_template   = $this->config->get('whatsapp_msg_admin_checkout_'.$order_info['language_code'].'_BODY');
		$header_parameters = $WhatAppCommons->get_whatsapp_order_variables( $header_template,$order_info,$whatsapp_order_total);
		$body_parameters   = $WhatAppCommons->get_whatsapp_order_variables( $body_template,$order_info,$whatsapp_order_total);
		
		$components = [];
		if (!empty($header_parameters)){
			$components[] = [
							"type"		=> "header",
							"parameters"=> $header_parameters
						];
			}
		if (!empty($body_parameters)){
			$components[] = [
							"type"		=> "body",
							"parameters"=> $body_parameters
						];
		}
				
		$res= $WhatAppCommons->sendTemplateMessage($admins,$template_name,$template_language,$components);
		foreach ($admins as $template_to) {
			$this->track_event('notification', $template_to);
		}
    }
    
    /**
     * 
     */
    public function notify_or_not_customer_or_admins_on_registration($data){
        
       $WhatAppCommons     = new WhatAppCommons($this->config);
       $whatsapp_patterns   = $this->config->get('whatsapp_config_number_filtering');
    //    $number_size_filter = $this->config->get('whatsapp_cfg_num_filt_by_size');
       
        // check if customer allow receive messages
        $customerAllowReceiveMsgs = (int)($this->config->get('whatsapp_config_customer_allow_receive_messages') ?? 0);
        $adminAllowNotifyCustomer = (int)($this->config->get('whatsapp_config_notify_customer_by_WhatsApp_on_registration') ?? 0);
        // customer sms

        if(
            $customerAllowReceiveMsgs && 
            $adminAllowNotifyCustomer &&
           WhatsAppPhonenumberFilter::isNumberAuthorized($data['telephone'], $whatsapp_patterns) 
        //    && WhatsAppPhonenumberFilter::passTheNumberSizeFilter($data['telephone'], $number_size_filter)
        ) {
            $template_to 	= WhatsAppPhonenumberFilter::rewritePhoneNumber($data['telephone'], $this->config);
			$msg_salt 		= $this->config->get('whats_cfg_msg_salt');
			$lang_code 		= $this->language->get('code'); 
			
			$template_name = 'account_registration_customer_'.$msg_salt;
			$template_language	= [
									"policy"=>"deterministic",
									"code"=> $lang_code
								];
			
			$header_template = $this->config->get('whatsapp_msg_account_registration_customer_'.$lang_code.'_HEADER');
			$body_template   = $this->config->get('whatsapp_msg_account_registration_customer_'.$lang_code.'_BODY');
			
			$values_array = array(
				'firstname'   	=> $data['firstname'],
				'lastname'    	=> $data['lastname'] ,
				'telephone' 	=> $data['telephone'],
				'password'    	=> $data['password']
			);
			
			$header_parameters = $WhatAppCommons->get_whatsapp_variables( $header_template,$values_array);
			$body_parameters   = $WhatAppCommons->get_whatsapp_variables( $body_template,$values_array);
			
			$components = [];
			if (!empty($header_parameters)){
				$components[] = [
								"type"		=> "header",
								"parameters"=> $header_parameters
							];
			}
			if (!empty($body_parameters)){
				$components[] = [
								"type"		=> "body",
								"parameters"=> $body_parameters
							];
			}
			$res= $WhatAppCommons->sendTemplateMessage($template_to,$template_name,$template_language,$components);
			$this->track_event('notification', $template_to);
			//need to add log here 
        }
        // end customer sms
        
        /*
         * Send sms to store owner on registration
         */
		 
       // if ($this->config->get('config_error_log')) $this->log->write("[whatsapp] Check if store owner wants SMS on registration");
		WhatAppCommons::clientLog("[whatsapp] Check if store owner wants SMS on registration",$this->config);
        
        if ( $this->config->get('whatsapp_cfg_ntfy_admin_by_WhatsApp_on_reg') ) {
        	
        	//if ($this->config->get('config_error_log')) $this->log->write("[whatsapp] Store owner wants sms on registration");
        	WhatAppCommons::clientLog("[whatsapp] Store owner wants sms on registration",$this->config);
        	
			
			$template_to 	= $this->config->get('config_telephone');
			$msg_salt 		= $this->config->get('whats_cfg_msg_salt');
			$lang_code 		= $this->language->get('code'); 
			
			$template_name = 'account_registration_admin_'.$msg_salt;
			$template_language	= [
									"policy"=>"deterministic",
									"code"=> $lang_code
								];
			
			$header_template = $this->config->get('whatsapp_msg_account_registration_admin_'.$lang_code.'_HEADER');
			$body_template   = $this->config->get('whatsapp_msg_account_registration_admin_'.$lang_code.'_BODY');
			
			$values_array = array(
				'firstname'   	=> $data['firstname'],
				'lastname'    	=> $data['lastname'] ,
				'telephone' 	=> $data['telephone'],
				'password'    	=> $data['password']
			);
			
			$header_parameters = $WhatAppCommons->get_whatsapp_variables( $header_template,$values_array);
			$body_parameters   = $WhatAppCommons->get_whatsapp_variables( $body_template,$values_array);
			$components = [];
			if (!empty($header_parameters)){
				$components[] = [
								"type"		=> "header",
								"parameters"=> $header_parameters
							];
			}
			if (!empty($body_parameters)){
				$components[] = [
								"type"		=> "body",
								"parameters"=> $body_parameters
							];
			}
					
			$res= $WhatAppCommons->sendTemplateMessage($template_to,$template_name,$template_language,$components);
			$this->track_event('notification', $template_to);
			//need to add log here 
		
		}
        
    }

    /**
     *
     */
    public function send_conf_sms_to_customer_on_new_order($data){
        // check if customer allow receive messages
        $customerAllowReceiveMsgs = (int)($this->config->get('whatsapp_config_customer_allow_receive_messages') ?? 0);
        if(!$customerAllowReceiveMsgs) return;
        
        if ($this->config->get('whatsapp_config_WhatsApp_confirm')) {
            $WhatAppCommons     = new WhatAppCommons($this->config);
            $template_to 	= WhatsAppPhonenumberFilter::rewritePhoneNumber($data['telephone'], $this->config);
			 
			$msg_salt 		= $this->config->get('whats_cfg_msg_salt');
			$lang_code 		= $this->language->get('code'); 
			//whatsapp_msg_customer_phone_confirm_en_BODY
			$template_name = 'customer_phone_confirm_'.$msg_salt;
			$template_language	= [
									"policy"=>"deterministic",
									"code"=> $lang_code
								];
			
			$header_template = $this->config->get('whatsapp_msg_customer_phone_confirm_'.$lang_code.'_HEADER');
			$body_template   = $this->config->get('whatsapp_msg_customer_phone_confirm_'.$lang_code.'_BODY');
			
			$values_array = array(
				'firstname'    => $data['firstname'],
				'lastname'     => $data['lastname'] ,
				'phonenumber'  => $data['telephone'],
				'confirm_code' => $data['confirm_code']
			);
			
			$header_parameters = $WhatAppCommons->get_whatsapp_variables( $header_template,$values_array);
			$body_parameters   = $WhatAppCommons->get_whatsapp_variables( $body_template,$values_array);
			$components = [];
			if (!empty($header_parameters)){
				$components[] = [
								"type"		=> "header",
								"parameters"=> $header_parameters
							];
			}
			if (!empty($body_parameters)){
				$components[] = [
								"type"		=> "body",
								"parameters"=> $body_parameters
							];
			}
			//return $template_to;		
			 $res= $WhatAppCommons->sendTemplateMessage($template_to,$template_name,$template_language,$components);
			 $this->track_event('notification', $template_to);
			//need to add log here 
			return;
		}
		//need to add log here
		//not sent as whatsapp_config_WhatsApp_confirm is disabled 
		
    }

	/**
     * Send activation Message 
     *
     * @param array $data
     * @param string $smsToken
     *
     * @return void
     */
    public function sendActivationMessage($data, $smsToken)
    {
        // check if customer allow receive messages
        $customerAllowReceiveMsgs = (int)($this->config->get('whatsapp_config_customer_allow_receive_messages') ?? 0);
        if(!$customerAllowReceiveMsgs) return;
        
        $whatsapp_patterns   = $this->config->get('whatsapp_config_number_filtering');
        // $number_size_filter = $this->config->get('whatsapp_cfg_num_filt_by_size');
        
		
        if(
            WhatsAppPhonenumberFilter::isNumberAuthorized($data['telephone'], $whatsapp_patterns) 
                // && WhatsAppPhonenumberFilter::passTheNumberSizeFilter($data['telephone'], $number_size_filter)
        ) {
            $WhatAppCommons = new WhatAppCommons($this->config);
			$template_to 	= WhatsAppPhonenumberFilter::rewritePhoneNumber($data['telephone'], $this->config);
			$msg_salt 		= $this->config->get('whats_cfg_msg_salt');
			$lang_code 		= $this->language->get('code'); 
			$template_name  = 'activation_message_'.$msg_salt;
			$template_language	= [
									"policy"=>"deterministic",
									"code"=> $lang_code
								];
			$header_template = $this->config->get('whatsapp_msg_activation_message_'.$lang_code.'_HEADER');
			$body_template   = $this->config->get('whatsapp_msg_activation_message_'.$lang_code.'_BODY');
			
			$values_array = array(
				'activationToken'    => $smsToken
			);
			
			$header_parameters = $WhatAppCommons->get_whatsapp_variables( $header_template,$values_array);
			$body_parameters   = $WhatAppCommons->get_whatsapp_variables( $body_template,$values_array);
			$components = [];
			if (!empty($header_parameters)){
				$components[] = [
								"type"		=> "header",
								"parameters"=> $header_parameters
							];
			}
			if (!empty($body_parameters)){
				$components[] = [
								"type"		=> "body",
								"parameters"=> $body_parameters
							];
			}
					
			$res= $WhatAppCommons->sendTemplateMessage($template_to,$template_name,$template_language,$components);
			$this->track_event('notification', $template_to);
			//need to add log here 
	   }
    }

    public function track_event($type, $to){
    	$this->ecusersdb->query("INSERT INTO `" . DB_PREFIX . "whatsapp_messages_tracking` (type, store_code, to) VALUES ('$type', '" . STORECODE ."', '$to')");
    }
}
