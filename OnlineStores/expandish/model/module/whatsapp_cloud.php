<?php
class ModelModuleWhatsappCloud extends Model {
    
	
	private static 	$config_prefix  = 'whatsapp_cloud_';

	
	/**
     *
     */
	public function checkoutNotifications ($order_info, $order_totals=false){
		//if(DEV_MODE) return;   

		$log_text = " ". __function__ ." order_info : ". json_encode($order_info)." order_totals : ". json_encode($order_totals);
		WhatappCloudHelper::clientLog($log_text);
		
		//updated to suitable the variable names used at templates 
		$order_info['order_date']	= $order_info['date_added'];
		
		$this->_customerCheckoutNotifications($order_info);
		$this->_adminCheckoutNotifications($order_info,$order_totals);
	}
	
    /**
     * 
     */
    public function registrationNotification ($data){
		//if(DEV_MODE) return; 
		
		$log_text = " ". __function__ ." data : ". json_encode($data);
		WhatappCloudHelper::clientLog($log_text);
		
		
		$this->_customerRegistrationNotification($data);
		$this->_adminRegistrationNotification($data);
    }
	
    /**
     *
     */
    public function verifyCodeNotification ($data){

		//if(DEV_MODE) return; 
        
		$notify_key   = self::$config_prefix.'config_notify_customer_phone_confirm';
		
        if ($this->config->get($notify_key)) {
            
			$template_to 	= $data['telephone'];
			$lang_code 		= $this->language->get('code'); 
			$template_key 	= 'customer_phone_confirm';
			$fullname		= $data['firstname'] . " " . $data['lastname'];

			$template_variables = [
									'firstname'    => $data['firstname'],
									'lastname'     => $data['lastname'],
									'fullname'     => $fullname,
									'phonenumber'  => $data['telephone'],
									'confirm_code' => $data['confirm_code']
								];
			
			return $this->_sendDefaultTemplate([
												'template_to'			=> $template_to,
												'lang_code'			 	=> $lang_code,
												'template_variables'	=> $template_variables,
												'template_key' 	     	=> $template_key,
												'receiver_country_code'	=> $data['country_code']
												]);
		}
		
		return ;
    }

	/**
     * 
     */
	private function _customerCheckoutNotifications($order_info){

        $whatsapp_patterns   = $this->config->get(self::$config_prefix.'config_number_filtering'); //TO:DO | need edits here 

        if(!WhatappCloudHelper::isNumberAuthorized($order_info['telephone']??"", $whatsapp_patterns)) {
			return ;
		}

        $payment_code  			= strtolower($order_info['payment_code']);
		$template_to   			= $order_info['telephone'];
		$lang_code 		 		= $this->language->get('code');
		$template_variables		= $order_info;
        $template_key 			= 'customer_checkout';
		$notify_key   			= self::$config_prefix.'config_notify_customer_on_checkout';
		$receiver_country_code  = $order_info['shipping_iso_code_2'] ?? "";
		
		if ( $this->_isFailedPayment($payment_code,$order_info['check_if_failed'])){
					
			
			$observer_key  = $order_info['check_if_failed'];
			$observer_name = 'customer_order_observers';

			return $this->_sendObserverTemplate([
												'template_to'			=> $template_to,
												'lang_code'			 	=> $lang_code,
												'template_variables'	=> $template_variables, 
												'receiver_country_code' => $receiver_country_code,
												'observer_key' 			=> $observer_key,
												'observer_name' 		=> $observer_name,
												]);
												
		} else if($this->config->get($notify_key)){

			return $this->_sendDefaultTemplate([
												'template_to'			=> $template_to,
												'lang_code'			 	=> $lang_code,
												'template_variables'	=> $template_variables,
												'receiver_country_code' => $receiver_country_code,
												'template_key' 	     	=> $template_key, 
												]);
	   }
		
		return false;
	}

	/**
     * 
     */
    private function _adminCheckoutNotifications($order_info, $order_totals){
      
		$admins = $this->_getAdminsNumbers();
			
		if (empty($admins )) {
			WhatappCloudHelper::clientLog('[whatsapp] No admins phone number and there are no extra admin phone numbers. No SMS will be sent. Abort!');
			return;
		}
		
		$template_to = $admins;
		$lang_code	 = $this->language->get('code'); 
		 
		if(!empty($order_info['order_status_id'])){
			
			$this->load->model('localisation/order_status');
			$lang_id = (int)$order_info['language_id'];
			$status  = $this->model_localisation_order_status->getOrderStatus($order_info['order_status_id'],$lang_id);
			$order_info['status_name'] = $status["name"]??"";
		}
			
		$notify_key = self::$config_prefix.'config_notify_admin_on_checkout';

		$template_variables 				= $order_info;
		$template_variables['order_date']	= $order_info['date_added'];
		$template_variables['total'] 		= '';
		
		if($order_totals){
			$template_variables['total'] 	= WhatappCloudHelper::getOrderTotal ($order_totals,$this->config );
		}
		
		$payment_code = strtolower($order_info['payment_code']);
		
		if ($this->_isFailedPayment($payment_code,$order_info['check_if_failed'])){
			
			$observer_key  = $order_info['check_if_failed'];
			$observer_name = 'admin_order_observers';

			return $this->_sendObserverTemplate([
												'template_to'			=> $template_to,
												'lang_code'			 	=> $lang_code,
												'template_variables'	=> $template_variables, 
												'observer_key' 			=> $observer_key,
												'observer_name' 		=> $observer_name,
												]);

        } else if($this->config->get($notify_key)){
				 
			WhatappCloudHelper::clientLog('[whatsapp] Notify (or not) admin(s) on checkout..');

			$template_key 	= 'admin_checkout';
			return $this->_sendDefaultTemplate([
												'template_to'			=> $template_to,
												'lang_code'			 	=> $lang_code,
												'template_variables'	=> $template_variables,
												'template_key' 	     	=> $template_key,
												]);
	    } else {
		   return false;
		}													
		
    }

	/**
     * 
     */
	private function _customerRegistrationNotification($data){
		
		WhatappCloudHelper::clientLog( __function__ . "  data : ". json_encode($data));

        $whatsapp_patterns  = $this->config->get(self::$config_prefix.'config_number_filtering'); //TO:DO | need edits here 
	    $lang_code 			= $this->language->get('code'); 				
		
		$fullname = $data['firstname'] . " " . $data['lastname'];
		
		$template_variables = [
								"firstname"  => $data['firstname'],
								"lastname"   => $data['lastname'] ,
								"fullname"   => $fullname,
								"telephone"  => $data['telephone'],
								"password"   => $data['password']
								];
								
		$template_key		= 'customer_account_registration';	
		$notify_key   		= self::$config_prefix.'config_notify_customer_on_registration';
	
        if(
           $this->config->get($notify_key) &&
           WhatappCloudHelper::isNumberAuthorized($data['telephone'], $whatsapp_patterns) 
        ) {
            $template_to = $data['telephone'];
			
			return $this->_sendDefaultTemplate([
												'template_to'			=> $template_to,
												'lang_code'			 	=> $lang_code,
												'template_variables'	=> $template_variables,
												'template_key' 	     	=> $template_key,
												]);
        }
		
 
		WhatappCloudHelper::clientLog("[whatsapp] Check if store owner wants SMS on registration");
	}
   
    /**
     * 
     */
	private function _adminRegistrationNotification($data){
	
		$notify_key   = self::$config_prefix.'config_notify_admin_on_registration';
		
        if ( $this->config->get($notify_key) ) {
        	
        	WhatappCloudHelper::clientLog("[whatsapp] Store owner wants sms on registration");
			
			$template_key		= 'admin_account_registration';
			$template_to 		= $this->config->get('config_telephone');		
			$lang_code 			= $this->language->get('code'); 	
					
			$fullname = $data['firstname'] . " " . $data['lastname'];
		
			$template_variables = [
								"firstname"  => $data['firstname'],
								"lastname"   => $data['lastname'] ,
								"fullname"   => $fullname,
								"telephone"  => $data['telephone'],
								"password"   => $data['password']
								];
								
			return $this->_sendDefaultTemplate([
												'template_to'			=> $template_to,
												'lang_code'			 	=> $lang_code,
												'template_variables'	=> $template_variables,
												'template_key' 	     	=> $template_key,
												]);
			
		}
        
	}
   
	/**
	 * # currently not used  | not enable it some other edits required 
     * Send activation Message 
     *
     * @param array $data
     * @param string $activationToken
     *
     */
	public function sendActivationMessage($data, $activationToken){
		
		//if(DEV_MODE) return;
    
        $whatsapp_patterns  = $this->config->get(self::$config_prefix.'config_number_filtering');

        if(
           WhatappCloudHelper::isNumberAuthorized($data['telephone'], $whatsapp_patterns) 
        ) {
			
			$template_to 		= $data['telephone'];
			$lang_code 			= $this->language->get('code'); 
			$template_key 		= 'customer_activation_message';
			$template_variables = ['activationToken'  => $activationToken];
			
			return $this->_sendDefaultTemplate([
												'template_to'			=> $template_to,
												'lang_code'			 	=> $lang_code,
												'template_variables'	=> $template_variables,
												'template_key' 	     	=> $template_key,
												]);
			
	   }
    }
		
	
	/*
	 *  to handle failed orders check at payment methods has custom failed status 
	 * @parm string $payment_code 
	 * @parm int $order_status
	 *
	 * @return bool 
	 */
	public function _isFailedPayment($payment_code,$order_status){
		
		if(!array_key_exists('check_if_failed',$order_info) )
			return false;
		
		$payments_custom_failed_status = [
				'urway'		=> 'payment_urway_failed_status_id',
				'expandpay'	=> 'expandpay_denied_order_status_id'
				];

		if(isset($payments_custom_failed_status[$payment_code])){
			return $order_status == $this->config->get($payments_custom_failed_status[$payment_code]);
		}

		//default paymment methods failed status names 
		return $order_status == $this->config->get($payment_code.'_failed_order_status_id');

	}

	/*
	 * @return array 
	 */
	private function _getAdminsNumbers(){
        
		$config_telephone 	 = $this->config->get('config_telephone');
		$all_phones 	  	 = explode(",", str_replace(' ', '', $config_telephone));
       
	   $admin_extra_numbers = $this->config->get(self::$config_prefix.'extra_checkout_phone_numbers');
    
	   if(!empty($admin_extra_numbers)){
           
		   $extra_phones_arr = explode(",", $admin_extra_numbers);
           $all_phones 		 = array_merge($all_phones, $extra_phones_arr);
		   
        }

        return $all_phones;
    }
	
	/*
	 * this method calling 'sendObserverTemplate' method 
	 * from admin/model/module/whatsapp_cloud/template_message.php 
	 *
	 * @parm array $data 
	 * 
	 */
	private function _sendObserverTemplate($data){
		
		$path = DIR_ONLINESTORES . '/admin/';
		
		if (STAGING_MODE)
			$path = '/var/www/rising-comercio/team_'.strtolower(STORECODE).'/admin/';
		
		
		$this->load->model('module/whatsapp_cloud/template_message',false,$path);
				
		return  $this->model_module_whatsapp_cloud_template_message->sendObserverTemplate($data);
	}
	
	/*
	 * this method calling 'sendDefaultTemplate' method 
	 * from admin/model/module/whatsapp_cloud/template_message.php 
	 *
	 * @parm array $data 
	 * 
	 */
	private function _sendDefaultTemplate($data){
		
		$path = DIR_ONLINESTORES . '/admin/';
		
		if (STAGING_MODE)
			$path = '/var/www/rising-comercio/team_'.strtolower(STORECODE).'/admin/';
		
		
		$this->load->model('module/whatsapp_cloud/template_message',false,$path);
		
		return  $this->model_module_whatsapp_cloud_template_message->sendDefaultTemplate($data);
	}

	
}