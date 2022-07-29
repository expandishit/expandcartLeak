<?php

class ControllerModuleWhatsapp extends Controller {
    private $error = array(); 
	
	//facebook app id used in embedded signup flow 
    //private $facebook_app_id 		= '375274270510086';
    private $facebook_app_id 		= '329928231042768';
    private $redirect_page 			='https://auth.expandcart.com/whatsapp_signup.php'; 

	private $errors = [];

	public function notificationNewSetting() {
		$this->load->language('module/whatsapp');
		$this->template = 'module/whatsapp/notificationNewSetting.expand';
		$this->children = array(
			'common/header',    
			'common/footer'    
		);
		$this->response->setOutput($this->render());
	}
	
	public function newpage() {
		$this->load->language('module/whatsapp');
        $this->template = 'module/whatsapp/newpage.expand';
        $this->children = array(
            'common/header',    
            'common/footer'    
        );
        $this->response->setOutput($this->render());
	}

	public function index() {

		$integration_step = $this->config->get('whatsapp_integration_step');
		$whatsapp_chat_connected = $this->config->get('whatsapp_chat_connected');
		$notification_integrated 	  = !((int)$integration_step <5) ;
		
		$this->data['integration_step']			= (int)$integration_step;
		$this->data['notification_integrated']	= $notification_integrated;
		$this->data['whatsapp_chat_connected']	= $whatsapp_chat_connected;
		$this->data['facebook_app_id']			= $this->facebook_app_id;
		$this->data['show_embeded_signup']		= false;  //should be true in publishing case 
		
		//for test 
		$this->data['show_embeded_signup']=false;
		$whitelist = ['qaz123','omartammam'];
		if(in_array(strtolower(STORECODE),$whitelist)){
			$this->data['show_embeded_signup']=true;
		}
		//#test
		
		$this->load->language('module/whatsapp');
        $this->template = 'module/whatsapp/start.expand';
        $this->children = array(
            'common/header',    
            'common/footer'    
        );

		$this->document->setTitle($this->language->get('heading_title_whatsapp'));
        $this->response->setOutput($this->render());
	}

	public function chat_connect() {
		$integration_step = $this->config->get('whatsapp_integration_step');
		$notification_integrated  = !((int)$integration_step <5);
		$whatsapp_chat_connected = $this->config->get('whatsapp_chat_connected');
		
		//var_dump($chat_integration);die();
		
		if(!$notification_integrated){
			return $this->index();
		}
		if($whatsapp_chat_connected){
			$this->redirect($this->url->link('module/whatsapp/setting', '', 'SSL'));
		}
		
		
		 if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {
			$whatsapp_phone_number = $this->config->get('whatsapp_phone_number') ;
			$WhatAppCommons= new WhatAppCommons($this->config);
			$waid= $WhatAppCommons->IScontactValid($whatsapp_phone_number);

			$whatsapp_chat_url = 'https://api.whatsapp.com/send?phone=' . $waid;
			$default_settings = [
								'whatsapp_chat_connected'=>true,
								'whatsapp_chat_applied_on'=>'all',
								'whatsapp_chat_url'=> $whatsapp_chat_url
								];
			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting('whatsapp_chat',$default_settings);
			
			//enable auto garbage for DB healthy 
			$WhatAppCommons= new WhatAppCommons($this->config);
		    $WhatAppCommons->updateSetting(['db_garbagecollector_enable'=> true]);
			
			$this->redirect($this->url->link('module/whatsapp/setting', '', 'SSL'));
		}
		
		$this->data['whatsapp_phone_number']=$this->config->get('whatsapp_phone_number');
		$this->data['whatsapp_business_account_id']=$this->config->get('whatsapp_business_account_id');
		//$this->data['customer_groups']=$customer_groups;
		 $this->data['back']   = $this->url->link('module/whatsapp', '', 'SSL');
		$this->load->language('module/whatsapp');
		$this->document->setTitle($this->language->get('heading_title_whatsapp'));
        $this->template = 'module/whatsapp/chat_connect.expand';
        $this->children = array(
            'common/header',    
            'common/footer'    
        );
        $this->response->setOutput($this->render());
	}
	
	public function setting() {
		$this->load->language('module/whatsapp');
		$this->document->setTitle($this->language->get('heading_title_settings'));
		
		$whatsapp_chat_connected = (bool)$this->config->get('whatsapp_chat_connected');
		if(!$whatsapp_chat_connected){
			$this->redirect($this->url->link('module/whatsapp/chat_connect', '', 'SSL'));
		}
		
		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {
			
			
            if ( ! $this->validateChatSetting() )
            {
                $result_json['success']	= '0';
                $result_json['errors'] 	= $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting('whatsapp_chat', $this->request->post);
            $result_json['success'] = '1';
           // $result_json['success_msg'] =  $this->language->get('whatsapp_chat_setting_updated_successfully');
            $result_json['success_msg'] =   $this->language->get('text_chat_success');

            
            $this->response->setOutput(json_encode($result_json));
            
            return;
		}
		
		$this->load->model('sale/customer_group');
		$customer_groups = $this->model_sale_customer_group->getCustomerGroups();
		
		$this->load->model('module/whatsapp');
		$new_messages_count =  $this->model_module_whatsapp->unreaded_messages();

		$this->data['customer_groups']					= $customer_groups;
		$this->data['whatsapp_chat_selected_groups']	= $this->config->get('whatsapp_chat_selected_groups');
		$this->data['whatsapp_chat_applied_on']	  		= $this->config->get('whatsapp_chat_applied_on');
		$this->data['whatsapp_chat_show']	  			= $this->config->get('whatsapp_chat_show');
		$this->data['new_messages_count']	  			= $new_messages_count;
		
		
		$this->template = 'module/whatsapp/setting.expand';
        $this->children = array(
            'common/header',    
            'common/footer'    
        );
        $this->response->setOutput($this->render());
	}
    
	private function validateChatSetting(){
		
		if($this->request->post['whatsapp_chat_applied_on'] =='specific' && empty($this->request->post['whatsapp_chat_selected_groups'])){
				$this->error['whatsapp_chat_selected_groups'] = $this->language->get('error_whatsapp_chat_selected_groups');
			}	
		
		
		if (!$this->error) {
      		return true;
    	} else {
      		return false;
    	}
	}
    
	public function install() {
        
		//default setting 
		//for POC Only this should be dynamic from installation process 
        $defaultSettings = array(
            "whatsapp_cfg_log" 				=> false,
            "whatsapp_phone_number" 		=> '01202570360',
            "whatsapp_business_account_id"  => '628449804419498',
            "whatsapp_template_namespaces"  => '4a98f78d_b82c_4d60_9ad8_13920a3b2132',
            "whatsapp_phone_certificate"  	=> 'CmsKJwignqO05OHjAxIGZW50OndhIg5FeHBhbmRjYXJ0IERldlDP3cj8BRpAueEMTKldCRIwo1sZ6gk/IBjU2o0ncOmdpDzHDhETXPPTBGz6dXqAenMtWWK7zsH7ULcQO7/YOPeRHcQlFefwChIvbTQw4bD++b/zWrKwmK1vKZdf4+Vfw/AFiRFJtIgc/IjLmKfIsctQgJDjFnwPzh0=',
            "whatsapp_api_url"  			=> 'https://35.198.187.26:9090',
            "whatsapp_api_username"  		=> 'admin',
            "whatsapp_api_password"  		=> '#rr;Xk?4j*2HTv`uX9<x',
            "whatsapp_integration_step"  	=> 0
        );
        
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('whatsapp_config', $defaultSettings);
		
		//default app logs setting for tracking 
		$defaultLogsSettings = array(
            "whatsapp_cfg_log_client" 			=> true,
            "whatsapp_client_log_filename" 		=> 'whats_client_logs',
            "whatsapp_cfg_log_developer" 		=> true,
            "whatsapp_developer_log_filename" 	=> 'whats_developer_logs'
        );
        
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('whatsapp_logs', $defaultLogsSettings);
    }
    
    public function templateMessages() {
		
		$this->load->model('marketplace/common');
		//enable after publishing of app 
        /*$market_appservice_status = $this->model_marketplace_common->hasApp('whatsapp');
        if (!$market_appservice_status['hasapp']) {
            $this->redirect($this->url->link('marketplace/app', 'id=' . $market_appservice_status['appserviceid'], 'SSL'));
            return;
        }*/
		
		$this->load->model('module/whatsapp');
		$template_messages= $this->model_module_whatsapp->getTemplateMessage ();
		//var_dump($template_messages[0]->components);die();
		$this->data['template_messages']=$template_messages;
		
        //Seems to be available in a "default" language object. See controller/common/header.php @0ce36
        $this->data['direction'] = $this->language->get('direction');
        
        //Load the language file for this module
        $this->load->language('module/whatsapp');

        $this->document->setTitle('whatsApp templates');
        
        $this->load->model('setting/setting');
        
       
        $this->data['breadcrumbs'] = array();

           $this->data['breadcrumbs'][] = array(
                   
               'href'      => $this->url->link('common/home', '', 'SSL'),
               'text'      => $this->language->get('text_home'),
               'separator' => FALSE
           );

           $this->data['breadcrumbs'][] = array(
               'href'      => $this->url->link('marketplace/home', '', 'SSL'),
               'text'      => $this->language->get('text_module'),
               'separator' => ' :: '
           );
			 $this->data['breadcrumbs'][] = array(
               'href'      => $this->url->link('module/whatsapp', '', 'SSL'),
               'text'      => $this->language->get('heading_title'),
               'separator' => ' :: '
           );
           $this->data['breadcrumbs'][] = array(
               'href'      => $this->url->link('module/whatsapp', '', 'SSL'),
               'text'      => $this->language->get('text_whatsApp_template_messages'),
               'separator' => ' :: '
           );
        

	   // $this->data['action'] = $this->url->link('module/whatsapp/messages?id='.$current_chat, '', 'SSL');
       // $this->data['cancel'] = $this->url->link('marketplace/home', '', 'SSL');

        
        
        //Choose which template file will be used to display this request, and send the output.
        $this->template = 'module/whatsapp/templates.expand';

        $this->children = array(
            'common/header',    
            'common/footer'    
        );
        
        $this->response->setOutput($this->render(TRUE));
	}
    
	public function notifications() {
		//check integration 
		$integration_step 	= (int)$this->config->get('whatsapp_integration_step');
		$embedded_signup 	= $this->config->get('whatsapp_embedded_signup');
		$embedded_status 	= $this->config->get('whatsapp_sandbox_status');
		
		//if the merchant registered via embeded signup 
		if($embedded_signup == 1){
			if($integration_step == 3){
				return $this->_verifyPhonePage();
			}
			else if($embedded_status == 'CONFIRMED'){
				return $this->_notificationsSettings();
			}else {
				return $this->_sandboxPage();
			}
		}
		
		//if the merchant regitered before embedded signup but not continue 
		else if ($integration_step > 4){
			return $this->_notificationsSettings();
		}
		else if ($integration_step > 1){
			return $this->_stepsPage();
		}
		
		return $this->_notificationSetupPage();
		
    }
    
	private function _notificationsSettings() {
		
		
		$WhatAppCommons = new WhatAppCommons($this->config);
		$health_status = $WhatAppCommons->checkHealth();
		//var_dump($health_status);die();
		$errors_alerts = [];
		$api_status = 'connection_failed';
		if($health_status){
			$health_status=json_decode($health_status);
			if(isset($health_status->health) && isset($health_status->health->gateway_status)){
				$api_status =$health_status->health->gateway_status;
			}else if (isset($health_status->errors)){
				$errors_alerts=array_merge($errors_alerts,$health_status->errors);
			}
		}
		$this->data['multiseller_isInstalled']=\Extension::isInstalled('multiseller');
		$this->data['api_status']=$api_status;
		$this->data['errors_alerts']=$errors_alerts;
		
		$this->load->model('marketplace/common');
        
		//Load the language file for this module
        $this->load->language('module/whatsapp');
		//Seems to be available in a "default" language object. See controller/common/header.php @0ce36
        $this->data['direction'] = $this->language->get('direction');
        $this->document->setTitle($this->language->get('heading_title'));
       
	    $this->load->model('module/whatsapp');
		$this->load->model('setting/setting');
		$this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();
		
		$this->data['languages'] = $languages;
		

		$default_template_names = array(
						'account_registration_customer',
						'account_registration_admin',
						'activation_message',
						'customer_phone_confirm',
						'customer_checkout',
						'admin_checkout'
						);
						
		$msg_salt = $this->config->get('whats_cfg_msg_salt');
		$old_msg_salt = $msg_salt;
		
        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {
            //Data cleaning.
            if ( ! $this->validate() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                $this->response->setOutput(json_encode($result_json));
                return;
            }
			
						
			
			
			//generate new message salt 
			/*##
			as Facebook has its restriction 
				on adding template with the same name of deleted template 
				'30 day peroid' to use the same name again
				so every time we make it unique by generating random salt 
				added to our default names 
			*/
			$msg_salt = $this->model_module_whatsapp->generateRandomString();
			$components = array('HEADER','BODY','FOOTER');
			
			//add template Messages  here 
			$logs=[];
			$logs['requests']=[];
			foreach ($default_template_names as $template){
				foreach ($languages as $lang){
					$template_name = $template.'_'.$msg_salt;
					
					$msg_components =[]; 
					$input_name= '';
					$should_send = false;
					for($i=0;$i<count($components);$i++ ){
					$input_name='whatsapp_msg_'.$template.'_'.$lang['code'] . '_' . $components[$i];
						if(isset($_POST[$input_name]) && !empty($_POST[$input_name])  ){
							if($components[$i] == 'BODY'){
								$should_send = true;
							}
							$facebook_text_format = $this->model_module_whatsapp->facebook_text_format ($_POST[$input_name]);
							if($components[$i] != 'HEADER'){
							$msg_components[]=["type"=>$components[$i],"text"=>$facebook_text_format];
							}else{
							$msg_components[]=["type"=>$components[$i],"text"=>$facebook_text_format,"format"=>'TEXT'];	
							}
							
							
						}
					}
					
					if($should_send){
						
						$result= $this->model_module_whatsapp->requestTemplate($template_name,$lang['code'],$msg_components);
					/*$logs['requests'][] = [
												'template_name'=>$template_name,
												'lang'=>$lang['code'],
												'msg_components'=>$msg_components,
												'type'=>'adding default templates',
												'result'=>$result
												
												];*/
					//need to add log here 
					}else{
					//need to add log here 
					$logs[]=['adding not sent',$template_name,$should_send];
					}
				}
			}
			
			//add observers templates 
			$observers = [
					'whatsapp_cfg_odr_observers',
					'whatsapp_cfg_odr_observers_for_admin',
					'whatsapp_cfg_seller_observers'
				];
				
			foreach ($observers as $observer ){
				 if ( isset( $_POST[$observer] ) )
				{
					foreach ( $_POST[$observer] as $index => $api_kv )
                {
                    if( $api_kv['key'] == "0" )
                    {
                        unset( $_POST[$observer][$index] );
                    }
                }
					//update post array after cleaning it 
					$this->request->post[$observer] = $_POST[$observer];
				
					//saving observers  templates
					foreach ($this->request->post[$observer] as $template){
						
						$template_name = $observer.'_'.$template['key'].'_'.$msg_salt;
						foreach ($languages as $lang){
							
							$msg_components =[]; 
							$input_name= '';
							$should_send = false;
							$components = array('header','body','footer');
							for($i=0;$i<count($components);$i++ ){
								//whatsapp_msg_account_registration_customer_ar_BODY
								$index=$components[$i].'_'.$lang['code'];
								if(isset($template[$index]) && $template[$index] != null){
									if($components[$i] == 'body'){
										$should_send = true;
									}
									$facebook_text_format = $this->model_module_whatsapp->facebook_text_format ($template[$index]);
									if($components[$i] != 'header'){
									$msg_components[]=["type"=>$components[$i],"text"=>$facebook_text_format];
									}else{
									$msg_components[]=["type"=>$components[$i],"text"=>$facebook_text_format,"format"=>'TEXT'];	
									}
								}
							}
							
								if($should_send){
									
									$result= $this->model_module_whatsapp->requestTemplate($template_name,$lang['code'],$msg_components);
									
									/*$logs['requests'][] = [
												'template_name'=>$template_name,
												'lang'=>$lang['code'],
												'msg_components'=>$msg_components,
												'type'=>'adding observer',
												'result'=>$result
												];*/
								//need to add log here 
								}else{
								//need to add log here 
									$logs[]=['adding',$template_name,$should_send];
								}
							}
					}
					
				}
			}
			
			
			//delete old template message 
			//we delete after as we have limited requests per second 
			/*
			we can change this part after POC 
			to loop on all templates at business manager & delete all of them 
			*/
			foreach ($default_template_names as $template){
				$result = $this->model_module_whatsapp->deleteTemplate($template.'_'.$old_msg_salt);
				//need to add log here 
				$logs[]=['deleting',$template.'_'.$old_msg_salt,$result];
			}
			
			 /*
             * Clean observers with status==0
             * 
             * No need. we do it JS side.
             */
			 
			$this->request->post['whats_cfg_msg_salt']=$msg_salt;
            $this->model_setting_setting->editSetting('whatsapp', $this->request->post);
            
            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['logs'] = $logs;
            $this->response->setOutput( json_encode($result_json) );

            return;
        }

        /*
          ____                     _                           _
         |  _ \                   | |                         | |
         | |_) |_ __ ___  __ _  __| | ___ _ __ _   _ _ __ ___ | |__  ___
         |  _ <| '__/ _ \/ _` |/ _` |/ __| '__| | | | '_ ` _ \| '_ \/ __|
         | |_) | | |  __/ (_| | (_| | (__| |  | |_| | | | | | | |_) \__ \
         |____/|_|  \___|\__,_|\__,_|\___|_|   \__,_|_| |_| |_|_.__/|___/
          
         */

        $this->data['breadcrumbs'] = array();

           $this->data['breadcrumbs'][] = array(
                   
               'href'      => $this->url->link('common/home', '', 'SSL'),
               'text'      => $this->language->get('text_home'),
               'separator' => FALSE
           );

           $this->data['breadcrumbs'][] = array(
               'href'      => $this->url->link('marketplace/home', '', 'SSL'),
               'text'      => $this->language->get('text_module'),
               'separator' => ' :: '
           );
        
           $this->data['breadcrumbs'][] = array(
               'href'      => $this->url->link('module/whatsapp', '', 'SSL'),
               'text'      => $this->language->get('heading_title'),
               'separator' => ' :: '
           );
        
        $this->data['action'] = $this->url->link('module/whatsapp/notifications', '', 'SSL');
        
        $this->data['cancel'] = $this->url->link('marketplace/home', '', 'SSL');

        /*
         * order statuses
         */
        $this->load->model('localisation/order_status');
        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        
        // init seller statuses list
        $msSeller = new ReflectionClass('MsSeller');
        $this->data['seller_statuses'] = [];
        foreach ($msSeller->getConstants() as $constant => $value) {
            if (strpos($constant, 'STATUS_') !== FALSE) {
                $this->data['seller_statuses'][] = $value;
            }
        }

        //The following code pulls in the required data from either config files or user
        //submitted data (when the user presses save in admin). Add any extra config data
        // you want to store.
        //
        // NOTE: These must have the same names as the form data in your whatsapp.tpl file
        
		
		 //★ Inject data from config into the data (to be displayed)
        
		$config_data = array(
		/**
		this data set from ectools and has different group name 'whatsapp_config'
		*/
			
            "whatsapp_phone_number",
            "whatsapp_business_account_id",
			/*
            "whatsapp_template_namespaces",
            "whatsapp_api_url",
            "whatsapp_api_username",
            "whatsapp_api_password",
			*/ 
            'whatsapp_config_notify_customer_by_WhatsApp_on_registration',
            'whatsapp_config_notify_seller_on_status_change',
            'whatsapp_config_notify_customer_by_WhatsApp_on_checkout',
            'whatsapp_cfg_donotsend_whatsapp_on_checkout_coupon_keywords',
            'whatsapp_cfg_ntfy_admin_by_WhatsApp_on_reg'          ,
            'whatsapp_config_notify_admin_by_WhatsApp_on_checkout',
            'whatsapp_config_notify_extra_by_WhatsApp_on_checkout',
            
			/* hook_8dd90 optin */
            'whatsapp_cfg_odr_observers',
            'whatsapp_cfg_odr_observers_for_admin',
            'whatsapp_cfg_seller_observers',
            

            'whatsapp_config_WhatsApp_template_for_customer_notif_on_checkout',
            'whatsapp_config_WhatsApp_template_for_storeowner_notif_on_checkout',
            
            'whatsapp_cfg_number_rewriting_search',
            'whatsapp_cfg_number_rewriting_replace',

            'whatsapp_config_number_filtering',
            // 'whatsapp_cfg_num_filt_by_size',
            'whatsapp_cfg_log',

            'whatsapp_config_WhatsApp_confirm',
            'whatsapp_config_WhatsApp_confirm_per_order',
            'whatsapp_config_WhatsApp_trials',
            //'whatsapp_config_WhatsApp_template',
            
            'whatsapp_cfg_code_length',
            'whatsapp_cfg_code_type',
        );
       
	   $components = array('HEADER','BODY','FOOTER','STATUS');
	   
		
		//adding all default templates with languages to config
		foreach ($languages as $lang){
			for($i=0;$i<count($components);$i++ ){
				for($ii=0;$ii<count($default_template_names);$ii++ ){
					$config_data[]='whatsapp_msg_'.$default_template_names[$ii].'_'.$lang['code'] . '_' . $components[$i];
				}
			}
		}
		
		//get all template messages from business Manager  & add this at config 
		$template_messages= $this->model_module_whatsapp->getTemplateMessage();
		foreach($template_messages as $template_message ){
			$temp_name = str_replace("_".$msg_salt,"",$template_message->name);
			$this->config->set('whatsapp_msg_'. $temp_name . '_' . $template_message->language . '_STATUS',$template_message->status); 
			 $this->data['whatsapp_msg_'. $temp_name . '_' . $template_message->language . '_STATUS'] = $template_message->status;
			foreach($template_message->components as $message_component){
				if($message_component->type != 'BODY' && $message_component->type != 'HEADER' ){
				$this->config->set('whatsapp_msg_'. $temp_name . '_' . $template_message->language . '_'.$message_component->type,$message_component->text);  
				}
			 }
		}

		//★ Inject data from config into the data (to be displayed)
        foreach ($config_data as $conf) {
            if (isset($this->request->post[$conf])) {
                $this->data[$conf] = $this->request->post[$conf];
            } else {

                $this->data[$conf] = $this->config->get($conf);
                
				//default values 
                if(empty($this->data[$conf])){
                   
				   //--registration customer 
				    if (strpos($conf, 'whatsapp_msg_account_registration_customer') !== false) {
					   //header
						if($conf == 'whatsapp_msg_account_registration_customer_en_HEADER') {
							$this->data[$conf] = "account Register successfully";
						}
						else if($conf == 'whatsapp_msg_account_registration_customer_ar_HEADER') {
							$this->data[$conf] = "تم تسجيل الحساب بنجاح";
						}
						//body
						else if($conf == 'whatsapp_msg_account_registration_customer_en_BODY') {
							$this->data[$conf] = "Hi {firstname} {lastname} ,
Thanks For Your Register.";
						}
						else if($conf == 'whatsapp_msg_account_registration_customer_ar_BODY') {
							$this->data[$conf] = "مرحبا {firstname} {lastname} 
شكرا لتسجيلك معنا .";
						}
					}
				   //--activation_message
				   //header
					else if (strpos($conf, 'whatsapp_msg_activation_message_') !== false) {
						if($conf == 'whatsapp_msg_activation_message_en_HEADER') {
							$this->data[$conf] = "activation Message";
						}
						
						else if($conf == 'whatsapp_msg_activation_message_ar_HEADER') {
							$this->data[$conf] = "رسالة التفعيل";
						}
						
						//body
						else if($conf == 'whatsapp_msg_activation_message_en_BODY') {
							$this->data[$conf] = "your activation Token {activationToken}";
						}
						else if($conf == 'whatsapp_msg_activation_message_ar_BODY') {
							$this->data[$conf] = "رمز التفعيل الخاص بك {activationToken}";
						}
					
					}
				   //--customer_phone_confirm_en_HEADER
				   //header
					else if (strpos($conf, 'whatsapp_msg_customer_phone_confirm_') !== false) {
						if($conf == 'whatsapp_msg_customer_phone_confirm_en_HEADER') {
							$this->data[$conf] = "phone confirmation";
						}
						
						else if($conf == 'whatsapp_msg_customer_phone_confirm_ar_HEADER') {
							$this->data[$conf] = "تاكيد الموبايل";
						}
					
						//body
						else if($conf == 'whatsapp_msg_customer_phone_confirm_en_BODY') {
							$this->data[$conf] = "Hi  {firstname} ,
your confirmation code is :  {confirm_code}";
						}
						else if($conf == 'whatsapp_msg_customer_phone_confirm_ar_BODY') {
							$this->data[$conf] = "مرحبا {firstname}
رمز تاكيد الموبايل الخاص بك   {confirm_code}";
						}
					}
				   //--whatsapp_msg_customer_checkout_en_HEADER
				   //header
					else if (strpos($conf, 'whatsapp_msg_customer_checkout_') !== false) {
						if($conf == 'whatsapp_msg_customer_checkout_en_HEADER') {
							$this->data[$conf] = "Order Confirmation";
						}
						
						else if($conf == 'whatsapp_msg_customer_checkout_ar_HEADER') {
							$this->data[$conf] = "تآكيد الطلب";
						}
						
						//body
						if($conf == 'whatsapp_msg_customer_checkout_en_BODY') {
							$this->data[$conf] = "Hi Sir,
Your Order# {order_id} is Confirmed , expected date : {order_date}";
						}
						else if($conf == 'whatsapp_msg_customer_checkout_ar_BODY') {
							$this->data[$conf] = "اهلا بحضرتك
تم تأكيد طلبك # {order_id} 
تاريخ التسليم المتوقع : {order_date}";
						}
					}
				   //--whatsapp_msg_account_registration_admin_en_HEADER
				   //header
					else if (strpos($conf, 'whatsapp_msg_account_registration_admin_') !== false) {
						if($conf == 'whatsapp_msg_account_registration_admin_en_HEADER') {
							$this->data[$conf] = "New Customer";
						}
						
						else if($conf == 'whatsapp_msg_account_registration_admin_ar_HEADER') {
							$this->data[$conf] = "تم تسجيل عميل جديد";
						}
					
						//body
						if($conf == 'whatsapp_msg_account_registration_admin_en_BODY') {
							$this->data[$conf] = "Hi Sir , 
New Customer Registered :  {firstname} {lastname}
phone number   {telephone}";
						}
						else if($conf == 'whatsapp_msg_account_registration_admin_ar_BODY') {
							$this->data[$conf] = "مرحبا ,
تم تسجيل عميل جديد  :  {firstname} {lastname}
رقم الموبايل  : {telephone}";
						}
					
					}
				   //--whatsapp_msg_admin_checkout_en_HEADER
				   //header
					else if (strpos($conf, 'whatsapp_msg_admin_checkout_') !== false) {
						if($conf == 'whatsapp_msg_admin_checkout_en_HEADER') {
                        $this->data[$conf] = "New Order Placed";
                    }
					
                    else if($conf == 'whatsapp_msg_admin_checkout_ar_HEADER') {
                        $this->data[$conf] = "طلب جديد";
                    }
					
					//body
					if($conf == 'whatsapp_msg_admin_checkout_en_BODY') {
                        $this->data[$conf] = "Hi Sir , 
New Order Placed 
# {order_id}";
                    }
                    else if($conf == 'whatsapp_msg_admin_checkout_ar_BODY') {
                        $this->data[$conf] = "مرحبا,
هناك طلب جديد # {order_id}";
                    }
					
					
					}
					
					//---all english footers
					 if (strpos($conf, '_en_FOOTER') !== false) {
						$this->data[$conf] ='Expandcart';
					}
					//all arabic footers
					else if (strpos($conf, '_ar_FOOTER') !== false) {
						$this->data[$conf] ='اكسباند كارت';
					}
					//all english status 
					else if (strpos($conf, '_en_STATUS') !== false) {
						$this->data[$conf] ='Not  Sent';
					}
					//all arabic status 
					else if (strpos($conf, '_ar_STATUS') !== false) {
						$this->data[$conf] ='لم ترسل';
					}
					//observers
					else if($conf === 'whatsapp_cfg_odr_observers'){
                        $this->data[$conf] = array();
                    }else if($conf === 'whatsapp_cfg_odr_observers_for_admin'){
                        $this->data[$conf] = array();
                    }
                    else if ($conf === 'whatsapp_cfg_seller_observers') {
                        $this->data[$conf] = array();
                    }
                }else {

				}

            }
        }


        /*
         * Add template to observers
         */
        $tmpl_arr = array("header" => "", "body" => "", "footer" => "", "is_tmpl" => true);
        
        array_push($this->data['whatsapp_cfg_odr_observers'],           $tmpl_arr);
        array_push($this->data['whatsapp_cfg_odr_observers_for_customer'],   $tmpl_arr);
        array_push($this->data['whatsapp_cfg_odr_observers_for_admin'], $tmpl_arr);
        array_push($this->data['whatsapp_cfg_seller_observers'], $tmpl_arr);
   
        //Choose which template file will be used to display this request, and send the output.
        $this->template = 'module/whatsapp.expand';
        $this->children = array(
            'common/header',    
            'common/footer'    
        );
        
        $this->response->setOutput($this->render(TRUE));
    }
    
	//notification_setup
	private function _notificationSetupPage(){
		
		//check integration 
		$integration_step 	= $this->config->get('whatsapp_integration_step');
		$embedded_signup 	= $this->config->get('whatsapp_embeded_signup');
		$embedded_status 	= $this->config->get('whatsapp_sandbox_status');
		
		$this->load->language('module/whatsapp');
		$embedded_status 	= $this->config->get('whatsapp_sandbox_status');
		$this->data['facebook_app_id'] 	= $this->facebook_app_id;
		$this->data['domain'] 			= DOMAINNAME;
        $this->data['store_code'] 		= STORECODE;
		$this->data['redirect_page'] 	= $this->redirect_page;
		$this->data['embedded_status'] 	= $embedded_status;
		
		$this->data['has_errors']		= $this->request->get['has_errors'];

       
        $this->template = 'module/whatsapp/notification_setup.expand';
        $this->children = array(
            'common/header',    
            'common/footer'    
        );
        $this->response->setOutput($this->render());
	}
	
	private function _verifyPhonePage (){
			
		$this->load->language('module/whatsapp');
		$embedded_status 	= $this->config->get('whatsapp_sandbox_status');
		$integration_step 	= (int)$this->config->get('whatsapp_integration_step');
		$phone_cc 			= $this->config->get('whatsapp_phone_cc');
		$phone_number 		= $this->config->get('whatsapp_phone_number');

		$this->data['whatsapp_phone_number']=$phone_cc.$phone_number;
		$this->data['integration_step'] = $integration_step;
		$this->data['embedded_status'] = $embedded_status;

        $this->template = 'module/whatsapp/verifyphone.expand';
        $this->children = array(
            'common/header',    
            'common/footer'    
        );
        $this->response->setOutput($this->render());
	}
	
	private function _sandboxPage (){
			
		$this->load->language('module/whatsapp');
		$this->load->model('module/whatsapp');
		$WABA_ID= $this->config->get("whatsapp_business_account_id");
		$business_info	  = $this->model_module_whatsapp->WABABusinessInfo($WABA_ID);
		$embedded_status 	= $this->config->get('whatsapp_sandbox_status');
		$integration_step 	= (int)$this->config->get('whatsapp_integration_step');
		
		$this->data['review_status'] 	= $business_info->account_review_status ?? '';
		$this->data['integration_step'] = $integration_step;
		$this->data['embedded_status'] 	= $embedded_status;
        $this->template = 'module/whatsapp/sandbox.expand';
        $this->children = array(
            'common/header',    
            'common/footer'    
        );
        $this->response->setOutput($this->render());
	}
	
	public function send_trial_message() {
		
		$result_json = [];
		
		if ( $this->request->server['REQUEST_METHOD'] == 'POST'  )
        {
			$this->load->model('module/whatsapp');
			
			if (isset($this->request->post['to']) && $this->request->post['to'] != '' ){
				$to 			= $this->request->post['to'];
				$template_name	= 'sample_shipping_confirmation';
				$language		= [ 
									"policy"	=> "deterministic",
									"code"		=> "en_US"
								];
				$components 	= [];
				$components[] 	= [
									'type' 		 => 'body',
									'parameters' => [
														[
														'type' => 'text',
														'text' => '2'
														]
													]
									];

				$result_json['success']	= '0';
				$result_json['errors'] 	= [];
				$message_errors = [];
				$WhatAppCommons     = new WhatAppCommons($this->config);
				$result= $WhatAppCommons->sendTemplateMessage($to,$template_name,$language,$components);
				if(is_array($result) && $result[0] != false ){
					$messages_arr = json_decode($result[0],true);
					$message_sent = (array_key_exists('messages',$messages_arr));
					$message_errors = (array_key_exists('errors',$messages_arr))? $messages_arr['errors'] : [];
					if($message_sent){
						$result_json['success']	= '1';
						$this->model_module_whatsapp->track_event('notification', $to);
					}
					
					$result_json['errors']	= $message_errors;
				}
			}
	
		}
		$this->response->setOutput(json_encode($result_json));
         return;
	}
	
	public function confirm() {
		
		$result_json = [];
		$result_json['success']	= '0';
		$result_json['errors'] 	= [];
		
		if ( $this->request->server['REQUEST_METHOD'] == 'POST'  )
        {
			if($this->config->get('whatsapp_sandbox_status') == 'VERIFIED_ACCOUNT'){
				$this->initializer([
				'setting/setting'
				]);
			$this->setting->editSettingValue('whatsapp_config','whatsapp_sandbox_status','CONFIRMED');
			$result_json['success']	= '1';
			}
		}
		$this->response->setOutput(json_encode($result_json));
         return;
	}
	
	
	/**
	 *  #  whatsapp embedded signup
	 *
	 */
	
	 public function storeToken()
    {
        if ($this->request->server['REQUEST_METHOD'] == 'GET' && isset($this->request->get['accessToken']) && isset($this->request->get['userID'])) {
            $accessToken = $this->request->get['accessToken'];
            $user_id = $this->request->get['userID'];
			$this-> _signup_integration ($accessToken);
			$has_errors = empty($this->errors)? 0 : 1;
			$this->redirect($this->url->link('module/whatsapp/notifications?has_errors='.$has_errors));
        }
    }
/*	
	//--signup integration 
	public function signup_integration (){
		
		$result_json			= [];
		$result_json['success']	= '0';
		
		if ( $this->request->server['REQUEST_METHOD'] == 'GET' )
        {
			if ( ! $this->_signupValidate() )
            {
                $result_json['success']	= '0';
                $result_json['errors'] 	= $this->error;
                $this->response->setOutput(json_encode($result_json));
                return;
            }
			$this->initializer([
				'module/whatsapp',
				'setting/setting'
			]);
			
			$token = $this->request->get['token'];

			//step1 : fetch WABA ID From token
			$WABA_ID = $this->_fetchWABA($token);
			
			//step2 : add Admin system user to WABA 
			$step2_Status = $this->whatsapp->assignUserToWABA ($WABA_ID,'admin',['MANAGE','DEVELOP']);
				
			//step3 : add Employee system user to WABA 
			$step3_Status = $this->whatsapp->assignUserToWABA ($WABA_ID,'employee',['MANAGE','DEVELOP']);
			
			//step4: share line of credit with client WABA_ID 
			$step4_result = $this->whatsapp->shareLineOfCredit ($WABA_ID);
			if(!$this->_checkRequest($step4_result)) { return; }
					
			//step5 : supscribe to APP to receive webhook 
			$step5_result=$this->whatsapp->subscribeApp ($WABA_ID);
			if(!$this->_checkRequest($step5_result)) { return; }

			$sandbox_init_status ='EXPANDPAY_REVIEW'; 
			$data = [
				'whatsapp_business_id' 	  => $WABA_ID ,
				'whatsapp_sandbox_status' => $sandbox_init_status,
				'embedded_signup' 		  => 1
			];
			
			//step6 : update setting 
			$this->_signupDefaultSetting($data);
			
			//step7 : send ECtool Request
			$this->model_module_whatsapp->RequestIntegration($data);
				
			$result_json['success'] = '1';
		}
		
		$this->response->setOutput(json_encode($result_json));
		return;
	}
*/

	//--signup integration 
	private function _signup_integration ($token){
		
		
			$this->initializer([
				'module/whatsapp',
				'setting/setting'
			]);

			//step1 : fetch WABA ID From token
			$WABA_ID = $this->_fetchWABA($token);
						
			//validate that this WABA has phone numbers 
			$this->_validateWABA($WABA_ID);
			
			if(!empty($this->errors))
				return;
			
			//step2 : add Admin system user to WABA 
			$assign_admin_result = $this->whatsapp->assignUserToWABA ($WABA_ID,'admin',['MANAGE','DEVELOP']);
				
			//step3 : add Employee system user to WABA 
			$assign_employee_result = $this->whatsapp->assignUserToWABA ($WABA_ID,'employee',['MANAGE','DEVELOP']);
			
			//step4: share line of credit with client WABA_ID 
			$share_credit_result = $this->whatsapp->shareLineOfCredit ($WABA_ID);
			if(!$this->_checkRequest($share_credit_result)) { 
				return;
			}
					
			//step5 : supscribe to APP to receive webhook 
			$subscribe_result=$this->whatsapp->subscribeApp ($WABA_ID);
			if(!$this->_checkRequest($subscribe_result)) {
				return;
			}
			
			//------- prepare Im data before create Integration request 
			
			//get last integration request if exists to fill image data with it 
			//for easier re verify old uninstalled images 
			
			$business_info	  = $this->model_module_whatsapp->WABABusinessInfo($WABA_ID);
			
			$business_name = $business_id  = '';
			
			if($business_info->owner_business_info){
				$business_name 	  =  $business_info->owner_business_info->name;
				$business_id 	  =  $business_info->owner_business_info->id;
			}
					
			
			$last_request 	  = $this->model_module_whatsapp->lastIntegrationRequest();
			$client_url		  = isset($last_request['client_url'])?$last_request['client_url']:'';
			$client_username  = isset($last_request['client_username'])?$last_request['client_username']:'';
			$client_password  = isset($last_request['client_password'])?$last_request['client_password']:'';
			$fb_status 		  = isset($last_request['fb_status'])?$last_request['fb_status']:'PENDING_REVIEW';
			$whatsapp_sandbox_status    = 'EXPANDPAY_REVIEW'; 
			
			$data = [
					'business_name' 	  	  => $business_name,
					'business_id' 	  		  => $business_id ,
					'whatsapp_business_id' 	  => $WABA_ID ,
					'client_url' 	  		  => $client_url ,
					'client_username' 	  	  => $client_username ,
					'client_password' 	  	  => $client_password ,
					'whatsapp_sandbox_status' => $whatsapp_sandbox_status,
					'fb_status' 	  		  => $fb_status,
					'embedded_signup' 	 	  => 1
				];
				
			//step6 : update setting 
			$this->_signupDefaultSetting($data);
			
			//step7 : send ECtool Request
			$this->model_module_whatsapp->RequestIntegration($data);
	
		return;
	}

	private function _checkRequest($result){
		$isSuccess = !empty($result) && !property_exists($result,'error') ;
		if(!$isSuccess){
			$this->errors[] = $result->error;	
		}
		return $isSuccess;		
	}
	
	private function _fetchWABA($token){
		$result				= $this->whatsapp->debugToken ($token);		
		$shared_WABAs 		= isset($result['whatsapp_business_management'])? $result['whatsapp_business_management'] : [];
		$WABA_id 			= false;
		
		if(count($shared_WABAs) == 1){
			return end($shared_WABAs);
		}
		else if(count($shared_WABAs) > 1){
			$WABA_id = $this->_filterWABA($shared_WABAs);
		}
		
		return $WABA_id;
	}
	
	private function _filterWABA($shared_WABAs){
		$result	= $this->whatsapp->sharedWABAs();
		$shared_WABA_id = false;
		
		if(!empty($result) && property_exists($result,'error')) {
			$this->errors[] = $result->error;
		}
		if(property_exists($result,'data')) {
			
			foreach ($result->data as $row ){
				if(in_array($row->id ,$shared_WABAs)){
					$shared_WABA_id = $row->id;
					break;
				}
			}
		}
		
		return $shared_WABA_id;
	}
	
	private function _validateWABA($shared_WABA){
		
		$phone_numbers = $this->whatsapp->WABAPhoneNumbers($shared_WABA); 
		if(empty($phone_numbers)){
			
			$this->errors[]= (object) [
									'title' 			=> 'No phone attached',
									'message' 		    => 'there is no phone numbers attached to this WABA' ,
									'type'   			=> 'UnHandled scenario'
								];
		}
	}
	
	private function _signupDefaultSetting($data){
		$defaultSettings = array(
            "whatsapp_cfg_log" 				=> false,
            "whatsapp_phone_number" 		=> '',
            "whatsapp_business_account_id"  => $data['whatsapp_business_id'],
            "whatsapp_template_namespaces"  => '',
            "whatsapp_phone_certificate"  	=> '',
            "whatsapp_api_url"  			=> '',
            "whatsapp_api_username"  		=> 'admin',
            "whatsapp_api_password"  		=> '',
            "whatsapp_integration_step"  	=> 1,
			"whatsapp_sandbox_status"		=> $data['whatsapp_sandbox_status'],
			"whatsapp_embedded_signup"		=> 1
        );
        
		//default app logs setting for tracking 
		$defaultLogsSettings = array(
            "whatsapp_cfg_log_client" 			=> true,
            "whatsapp_client_log_filename" 		=> 'whats_client_logs',
            "whatsapp_cfg_log_developer" 		=> true,
            "whatsapp_developer_log_filename" 	=> 'whats_developer_logs'
        );
		
        $this->setting->editSetting('whatsapp_config', $defaultSettings);
        $this->setting->editSetting('whatsapp_logs', $defaultLogsSettings);
		
	}
	
	//built for embeded flow images that their phone numbers need verification 
	public function phoneVerification(){
		 if (!isset($this->request->get['target'])) {
            echo '{error: "Error"}';
            return;
        }
		$this->initializer([
			'module/whatsapp',
			'setting/setting'
		]);
		
        $target   = $this->request->get['target'];
        $postData = $this->request->post;
		$integration_step = $this->config->get('whatsapp_integration_step');
		
		$response= [];
		$response['hasErrors'] = false;
		$response['whatsErrors'] = [];
				
		if ($target == 'send_code') {
			$phone_cc 		= $this->config->get('whatsapp_phone_cc');
			$phone_number 	= $this->config->get('whatsapp_phone_number');
			$cert 		  	= $this->config->get('whatsapp_phone_certificate');
				
			//TO:DO we may regenerate certificate here to avoid ceritificate issues 
			$WhatAppCommons = new WhatAppCommons($this->config);
			$sendcode_response = $WhatAppCommons->account($phone_cc,$phone_number,$postData['whatsapp_methods'],$cert,'');
			$sendcode_response = json_decode($sendcode_response);
			
			if(isset($sendcode_response->errors)){
				$response['hasErrors'] = true;
				$response['whatsErrors'] = $sendcode_response->errors;
			}
        } else if ($target == 'verify_code') {
			
            $response = $this->whatsapp->validateVerifyActivation($postData);
			if(!$response['hasErrors']){
				
				$WhatAppCommons = new WhatAppCommons($this->config);
				$verify_response= $WhatAppCommons->accountVerify($postData['whatsapp_verification_code']);
				$verify_response = json_decode($verify_response);
				
				if(isset($verify_response->errors) || empty($verify_response)){
					$response['hasErrors'] = true;
					$response['whatsErrors'] = property_exists($verify_response,'errors')?$verify_response->errors : [];
				}else {
					$integration_step=6;
					$this->setting->editSettingValue('whatsapp_config','whatsapp_integration_step',$integration_step);	
				}
			}
        }
		
        $this->response->setOutput(json_encode($response));
        return;
		
	}
	
	private function _signupValidate(){	
		if(empty($this->request->get['token'])){
			$this->error['token'] = "token missing in the request";
		}	
		
		if (!$this->error) {
      		return true;
    	} else {
      		return false;
    	}
	}
	 
	public function sharedWABAs (){
		$result=false;
			if ( $this->request->server['REQUEST_METHOD'] == 'GET'  )
        {
			$this->load->model('module/whatsapp');
			
			if (isset($this->request->get['business_id']) && $this->request->get['business_id'] != '' ){
				$business_id = $this->request->get['business_id'];
				$this->load->model('module/whatsapp');
				$result	= $this->model_module_whatsapp->sharedWABAs ($business_id);
				
			}
			
		}
		$this->response->setOutput(json_encode($result));
         return;
	}
	
	/**
	 *  #  whatsapp chat application main function 
	 *
	 */
	public function chat() {

		$this->load->model('module/whatsapp');
		
		$current_chat = '';
		if(isset($this->request->get['id'])){
			$current_chat	= $this->request->get['id'];
		}
		
		$chat_with_arr = $this->model_module_whatsapp->get_contact($current_chat);
		$profile_name  = '';
		if(!empty($chat_with_arr)){
			if(isset($chat_with_arr['profile_name'])){
				$profile_name = $chat_with_arr['profile_name'];
			}
			 $profile_name = ($profile_name == '')?  '+'.str_replace("@s.whatsapp.net","",$chat_with_arr['jid']) : $profile_name ;
		}
		
		$this->data['profile_name'] = $profile_name;
		$this->data['current_chat'] = $current_chat;

		 //send template Message
		$this->load->model('marketplace/common');
	
  
        //Load the language file for this module
        $this->load->language('module/whatsapp');

        $this->document->setTitle("whatsApp Chat");
        
        $this->load->model('setting/setting');

        $this->data['breadcrumbs'] = array();

           $this->data['breadcrumbs'][] = array(
                   
               'href'      => $this->url->link('common/home', '', 'SSL'),
               'text'      => $this->language->get('text_home'),
               'separator' => FALSE
           );

           $this->data['breadcrumbs'][] = array(
               'href'      => $this->url->link('marketplace/home', '', 'SSL'),
               'text'      => $this->language->get('text_module'),
               'separator' => ' :: '
           );
        
           $this->data['breadcrumbs'][] = array(
               'href'      => $this->url->link('module/whatsapp', '', 'SSL'),
               'text'      => $this->language->get('text_whatsapp'),
               'separator' => ' :: '
           );
           $this->data['breadcrumbs'][] = array(
               'href'      => $this->url->link('module/whatsapp', '', 'SSL'),
               'text'      => $this->language->get('text_whatsapp_chat'),
               'separator' => ' :: '
           );
        	    
		
        $this->data['back']   = $this->url->link('module/whatsapp', '', 'SSL');
        $this->data['cancel'] = $this->url->link('module/whatsapp', '', 'SSL');

        
        //Choose which template file will be used to display this request, and send the output.
        $this->template = 'module/whatsapp/chat.expand';

        $this->children = array(
            'common/header',    
            'common/footer'    
        );
        
        $this->response->setOutput($this->render(TRUE));
	}
	
	/**
	 * Check if login and register by phone number is activated
	 */
	private function _stepsPage() {
		
		//enable after publishing of app 
        /*$market_appservice_status = $this->model_marketplace_common->hasApp('whatsapp');
        if (!$market_appservice_status['hasapp']) {
            $this->redirect($this->url->link('marketplace/app', 'id=' . $market_appservice_status['appserviceid'], 'SSL'));
            return;
        }*/
		//Load the language file for this module
        $this->load->language('module/whatsapp');
		$this->load->model('module/whatsapp');
		
		$this->document->setTitle($this->language->get('heading_steps_title'));
		$this->data['integration_step'] = $this->config->get('whatsapp_integration_step');
		$this->data['whatsapp_phone_number'] = $this->config->get('whatsapp_phone_number');
		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {
			/*
            if ( ! $this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));

                return;
            }*/
			
			//
			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

			$result_json['success'] = '1';
            $result_json['redirect'] = '1';
            $result_json['to'] = (string) $this->url->link('sale/order/info', 'order_id=' . $order_id, 'SSL');

            $this->response->setOutput(json_encode($result_json));

            return;
		}
   

        $this->data['breadcrumbs'] = array();

           $this->data['breadcrumbs'][] = array(
                   
               'href'      => $this->url->link('common/home', '', 'SSL'),
               'text'      => $this->language->get('text_home'),
               'separator' => FALSE
           );

           $this->data['breadcrumbs'][] = array(
               'href'      => $this->url->link('marketplace/home', '', 'SSL'),
               'text'      => $this->language->get('text_module'),
               'separator' => ' :: '
           );
			 $this->data['breadcrumbs'][] = array(
               'href'      => $this->url->link('module/whatsapp', '', 'SSL'),
               'text'      => $this->language->get('heading_title'),
               'separator' => ' :: '
           );
           $this->data['breadcrumbs'][] = array(
               'href'      => $this->url->link('module/whatsapp', '', 'SSL'),
               'text'      => $this->language->get('heading_steps_title'),
               'separator' => ' :: '
           );
        

		$this->template = 'module/whatsapp/steps.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render_ecwig());
  	}
  	
	public function steps_validate(){
        if (!isset($this->request->get['target'])) {
            echo '{error: "Error"}';
            return;
        }

        $target = $this->request->get['target'];
        $postData = $this->request->post;
		
        $this->initializer([
			'module/whatsapp',
			'setting/setting'
		]);
		$integration_step = $this->config->get('whatsapp_integration_step');
		
        if ($target == 'business') {
            $response = $this->whatsapp->validateBussinessData($postData);
			if(!$response['hasErrors']){
				//send ectool data request 
				$res= $this->whatsapp->RequestIntegration ($postData);
				$this->setting->editSettingValue('whatsapp_config','whatsapp_integration_step',$integration_step+1);
			}
        } else if ($target == 'request_activation') {
            $response = $this->whatsapp->validateRequestActivation($postData);
			if(!$response['hasErrors']){
				//here send curl for activation code request api  
				$phone_number 			= $this->config->get('whatsapp_phone_number');
				$cert 		  			= $this->config->get('whatsapp_phone_certificate');
				$WhatAppCommons 		= new WhatAppCommons($this->config);
				
			//	$res= $WhatAppCommons->account($whatsapp_country_code,$phone_number,$postData['whatsapp_methods'],$cert,'');
				$res= $WhatAppCommons->account($postData['whatsapp_country_code'],$phone_number,$postData['whatsapp_methods'],$cert,'');
				$res = json_decode($res);
				if(isset($res->errors)){
				$response['hasErrors'] = true;
				$response['whatsErrors'] = $res->errors;
				}else {
					$this->setting->editSettingValue('whatsapp_config','whatsapp_integration_step',$integration_step+1);	
				}
			}
        } else if ($target == 'verify_activation') {
			
            $response = $this->whatsapp->validateVerifyActivation($postData);
			if(!$response['hasErrors']){
				
			//for demo only [should be commented ]
			// if ($postData['whatsapp_verification_code'] == '5555'){
			// 	$this->setting->editSettingValue('whatsapp_config','whatsapp_integration_step',$integration_step+1);
			// $this->response->setOutput(json_encode([
			// 		'hasErrors' => false
			// 	]));
			// 	return;
			// }
			//#for demo only 
			
				//here send curl for activation code request api  
				$WhatAppCommons = new WhatAppCommons($this->config);
				$res= $WhatAppCommons->accountVerify($postData['whatsapp_verification_code']);
				$res = json_decode($res);
				if(isset($res->errors)){
				$response['hasErrors'] = true;
				$response['whatsErrors'] = $res->errors;
				}else {
					$this->setting->editSettingValue('whatsapp_config','whatsapp_integration_step',$integration_step+1);	
				}
			}
        } else if ($target == 'verified') {
            //$response = $this->order->validateProduct($postData);
        }
		
        $this->response->setOutput(json_encode($response));
        return;
    }

	public function finish_integration(){
		$this->initializer([
			'setting/setting'
		]);
		$integration_step = $this->config->get('whatsapp_integration_step');
        
		$WhatAppCommons = new WhatAppCommons($this->config);
		$health_status = $WhatAppCommons-> checkHealth ();
		//var_dump($health_status);die();
		$errors_alerts = [];
		$api_status = 'connection_failed';
		if($health_status){
			$health_status=json_decode($health_status);
			if(isset($health_status->health) && isset($health_status->health->gateway_status)){
				$api_status =$health_status->health->gateway_status;
			}else if (isset($health_status->errors)){
				$errors_alerts=array_merge($errors_alerts,$health_status->errors);
			}
		}
		
		$response['api_status'] = $api_status;
		if($api_status=='connected' ){
			$response['integration_status'] = true;
			$this->setting->editSettingValue('whatsapp_config','whatsapp_integration_step',$integration_step+1);	
			
			//enable auto garbage for DB healthy 
			$WhatAppCommons= new WhatAppCommons($this->config);
		    $WhatAppCommons->updateSetting(['db_garbagecollector_enable'=> true]);
			
		}else if ($api_status=='unregistered'){
			$response['integration_status'] = true;
			//return to activation step 
			$this->setting->editSettingValue('whatsapp_config','whatsapp_integration_step',2);
			
		}else{
			$response['integration_status'] = false;
			$response['whatsErrors'] = $errors_alerts;
		}
		
        $this->response->setOutput(json_encode($response));
        return;
    }

	//not used now 
	public function messages() {
		$this->load->model('module/whatsapp');
		$messages= [];
		$current_chat = '';
		
		if(isset($this->request->get['id'])){
			$current_chat= $this->request->get['id'];
		}else if (isset($_POST['id'])){
			$current_chat= $_POST['id'];
		}
		
		if($current_chat != ''){
			$messages= $this->model_module_whatsapp->getMessages($current_chat);
		}
		
		$chats = $this->model_module_whatsapp->getAllChats();
		$contacts = $this->model_module_whatsapp->getAllcontacts();
		//var_dump($allChats);die();
		//var_dump($allcontacts);die();
		
		//disable clearing of encoded template message at current time 
		/*
		$new_messages =[];
		
		foreach($messages as $message){
			if($message['hsm'] != null ){
			$message['hsm']  = explode('order_confirmed', $message['hsm'] );
			 $message['hsm']  = mb_convert_encoding($message['hsm'], 'UTF-8', 'UTF-8');
			$message['hsm']  = $message['hsm'][1];
			// $message['hsm']  = mb_convert_encoding($message['hsm'], 'UTF-8', 'UTF-8');
			 $message['hsm']  = $this->_cleanup_text($message['hsm']);
			$message['hsm'] = preg_replace('/[^\p{L}\p{N}\s]/u', '', $message['hsm']);


			//var_dump($message['hsm']);
			}
			$new_messages[]=$message;
		}
		
		$this->data['messages'] = $new_messages;*/
		$this->data['messages'] = $messages;
		$this->data['contacts'] = $contacts;
		$this->data['chats'] = $chats;
		$this->data['current_chat'] = $current_chat;

		 //send template Message
	
		$this->load->model('marketplace/common');
		
		//enable after publishing of app 
      /*  $market_appservice_status = $this->model_marketplace_common->hasApp('whatsapp');
        if (!$market_appservice_status['hasapp']) {
            $this->redirect($this->url->link('marketplace/app', 'id=' . $market_appservice_status['appserviceid'], 'SSL'));
            return;
        }*/

        //Seems to be available in a "default" language object. See controller/common/header.php @0ce36
        $this->data['direction'] = $this->language->get('direction');
        
        //Load the language file for this module
        $this->load->language('module/whatsapp');

        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('setting/setting');
        
       

        $this->data['breadcrumbs'] = array();

           $this->data['breadcrumbs'][] = array(
                   
               'href'      => $this->url->link('common/home', '', 'SSL'),
               'text'      => $this->language->get('text_home'),
               'separator' => FALSE
           );

           $this->data['breadcrumbs'][] = array(
               'href'      => $this->url->link('marketplace/home', '', 'SSL'),
               'text'      => $this->language->get('text_module'),
               'separator' => ' :: '
           );
        
           $this->data['breadcrumbs'][] = array(
               'href'      => $this->url->link('module/whatsapp', '', 'SSL'),
               'text'      => $this->language->get('heading_title'),
               'separator' => ' :: '
           );
        
		//get messages 
		
		//get contacts 
		
		
		//submit messages 
		
		


	    
		
	    $this->data['action'] = $this->url->link('module/whatsapp/messages?id='.$current_chat, '', 'SSL');
        $this->data['cancel'] = $this->url->link('marketplace/home', '', 'SSL');

        
        
        //Choose which template file will be used to display this request, and send the output.
        $this->template = 'module/whatsapp/messages.expand';

        $this->children = array(
            'common/header',    
            'common/footer'    
        );
        
        $this->response->setOutput($this->render(TRUE));
	}
	
	
	
	/**
	 *	for sending direct messages by ajax 
	 */
	public function send_message() {
		$result=false;
			if ( $this->request->server['REQUEST_METHOD'] == 'POST'  )
        {
			$this->load->model('module/whatsapp');
			
			if (isset($_POST['to']) && $_POST['to'] != '' ){
				$to = str_replace("@s.whatsapp.net","",$_POST['to']);
				$messageData["type"]='text';
				$messageData["recipient_type"]='individual';
				$messageData['text']       = [
									"body" => $_POST['message']
								];
								
				$WhatAppCommons     = new WhatAppCommons($this->config);
				$result= $WhatAppCommons->sendMessage($to,$messageData,true);

				$this->model_module_whatsapp->track_event('message', $to);
			}
			
		}
		$this->response->setOutput($result);
         return;
	}
	
	/**
	 *	for get messages using pagination by ajax 
	 *
	 */
	public function get_messages() {
		$result=false;
		if ( $this->request->server['REQUEST_METHOD'] == 'GET'  )
        {
			$limit=5;
			$current_chat = $this->request->get['to'];
			$page = $this->request->get['page'];
			$this->load->model('module/whatsapp');
			$messages= $this->model_module_whatsapp->getMessages($current_chat,$limit,$page);
			$result = json_encode($messages,true);	
			
		}
		$this->response->setOutput($result);

                return;
	}
	
	/**
	 * 	get specific message data by ajax 
	 *
	 */
	public function get_message() {
		$result=false;
		if ( $this->request->server['REQUEST_METHOD'] == 'GET'  )
        {
			//var_dump($this->request->get);die();
			$limit=5;
			$current_chat = $this->request->get['to'];
			$id = $this->request->get['id'];
			$this->load->model('module/whatsapp');
			$message= $this->model_module_whatsapp->getMessage($id);
			//$messages_groupped = $this->date_groupping($messages);
			//var_dump($messages_groupped);die();
			$result = json_encode($message,true);	
			
		}
		$this->response->setOutput($result);

                return;
	}
   
    /* 
	 * function for grouping data by date 
	 * currently not used
	 *
	 */ 
	private function date_groupping ($messages){
		$result=[];
		foreach($messages as $message) {

			$today_date =   strtotime(date("Y-m-d H:i:s"));
			$event_date =   (int)$message['timestamp']/1000;//message.timestamp
			$diff       =   floor(($today_date - $event_date)/(60*60*24));

			switch($diff) {
				case 0:
					$title             = "Today";
					$result[$title][]  = $message;
					break;
				case 1:
					$title             =  "Yesterday";
					$result[$title][]  =  $message;
					break;
				default:
					$title             = date("M jS", $event_date);
					$result[$title][]  = $message;
			}
		}
		return $result;
	}
	
	/**
	 *	for get messages using pagination 
	 */
	public function get_chats(){
		$result=false;
		if ( $this->request->server['REQUEST_METHOD'] == 'GET'  )
        {
			$this->load->model('module/whatsapp');
			$chats = $this->model_module_whatsapp->getAllChats();
			$result = json_encode($chats,true);	
		}
		$this->response->setOutput($result);

                return;
	}
	
	/**
	 *	get specific contact data 
	 */
	 public function get_contact(){
		$result=false;
		if ( $this->request->server['REQUEST_METHOD'] == 'GET'  )
        {
			$current_chat = $this->request->get['jid'];
			$this->load->model('module/whatsapp');
			$chats = $this->model_module_whatsapp->get_contact($id);
			$result = json_encode($chats,true);	
		}
		$this->response->setOutput($result);

        return;
	}
    
	/**
	 *	for getting new_messages
	 */
	 public function new_messages() {
		
		$result=false;
		if ( $this->request->server['REQUEST_METHOD'] == 'GET'  )
        {
			$chat = $this->request->get['to'];
			$id = $this->request->get['last_id'];
			$this->load->model('module/whatsapp');
			$messages= $this->model_module_whatsapp->getMessagesAfter($chat,$id);
			$result = json_encode($messages,true);	
		}
		$this->response->setOutput($result);

                return;
	}
	
	/**
	 *	for read chat messages
	 */
	public function read_messages(){
		$result=false;
		if ( $this->request->server['REQUEST_METHOD'] == 'GET'  && isset($this->request->get['to']))
        {
			$current_chat = $this->request->get['to'];
			$this->load->model('module/whatsapp');
			$result = $this->model_module_whatsapp->readMessages($current_chat);
		}
		$this->response->setOutput(json_encode($result));

                return;
		
	}
   
	
	
	//not used now 
	private function _cleanup_text($text) {

	  $text = strip_tags($text);
	  //$text = htmlspecialchars_decode($text);
	  //$text = preg_replace("/&#?[a-z0-9]{2,8};/i","",$text);

	  $RemoveChars[] = "([,.:;!?()#&%+*\"'])";
	  $ReplaceWith = " ";
	  $text  = preg_replace($RemoveChars, $ReplaceWith, $text);

	return $text; 
	}
  
    /**
     * This function is called to ensure that the settings chosen by the admin user are allowed/valid.
     * You can add checks in here of your own.
     */
    private function validate()
    {
        if ( ! $this->user->hasPermission('modify', 'module/whatsapp') )
        {
           $this->error['warning'] = $this->language->get('error_permission');
        }

        return $this->error ? false : true;
    }
	
	/**
	
	*/
    public function deleteField()
    {
        $this->load->model('setting/setting');
        $config = $this->config->get($this->request->post['targetField']);
        unset($config[$this->request->post['targetIndex']]);
        $this->model_setting_setting->insertUpdateSetting('whatsapp', [$this->request->post['targetField'] => $config]);
        return;
    }
	
	/**
		remove configuration data
	*/
	public function uninstall(){
		 $this->load->model('setting/setting');
		 $this->load->model('module/whatsapp');
		$this->model_setting_setting->deleteSetting('whatsapp_config');
		$this->model_setting_setting->deleteSetting('whatsapp_logs');
		$this->model_setting_setting->deleteSetting('whatsapp_chat');
		$this->model_module_whatsapp->integrationRequestUninstall();
	}
	
	/**
		remove configuration data
	*/
	public function chat_uninstall(){
		 $this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting('whatsapp_chat');
		$this->redirect($this->url->link('module/whatsapp', '', 'SSL'));
	}

	public function testing_step_changes(){
		$step=(int)$this->request->get['step'];
		$this->load->model('setting/setting');
		$this->model_setting_setting->editSetting('whatsapp_config',['whatsapp_integration_step'=>$step]);
		
	}
}
?>
