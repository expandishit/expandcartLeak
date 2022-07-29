<?php
use FacebookAds\Api;
use FacebookAds\Logger\CurlLogger;
use FacebookAds\Object\ServerSide\ActionSource;
use FacebookAds\Object\ServerSide\Event;
use FacebookAds\Object\ServerSide\EventRequest;
use FacebookAds\Object\ServerSide\UserData;

class ControllerModuleWhatsappCloud extends Controller {

	private static  $module_name  		= 'whatsapp_cloud';
	private static  $facebook_app_id 	= '329928231042768';
    private static  $redirect_page 	 	= 'https://auth.expandcart.com/whatsapp_cloud_signup.php';  //should un-comment this 
	
	//private static  $redirect_page 	= 'https://auth.facebook.me/whatsapp_cloud_signup.php'; //should comment this 
	private static  $default_template_names = [
												'customer_account_registration',
												'customer_checkout',
												'customer_phone_confirm',
												'admin_account_registration',
												'admin_checkout'
											];
											
	private static  $template_observers 	= [
												'customer_order_observers',
												'admin_order_observers',
												'seller_order_observers'
											];
											
	private static  $template_components 	= ['HEADER','BODY','FOOTER'];	
	private static  $templates_languages 	= [];	
	
	private $result_json 					= [ 'success' => 0, 'error' => '' , 'errors'  => [] ];
	private $validation_errors 				= [] ;

	public function __construct ($registry){
		
		parent::__construct($registry);
		
	
		$this->initializer([
							'setting/setting'
							]);

	}

	public function index(){
		
		$account_connected 		 = (int)$this->config->get('whatsapp_cloud_account_connected'); 
		$phone_selected			 = (int)$this->config->get('whatsapp_cloud_phone_selected');
		$phone_verified			 = (int)$this->config->get('whatsapp_cloud_phone_verified');
		$phone_registered		 = (int)$this->config->get('whatsapp_cloud_phone_registered');
		$congratulation_skipped  = (int)$this->config->get('whatsapp_cloud_congratulation_skipped'); 
		$account_status 		 = $this->config->get('whatsapp_cloud_sandbox_status'); 
		$phone_number_id 		 = $this->config->get('whatsapp_cloud_phone_number_id'); 
		
		$this->children = ['common/header','common/footer'];
		
		if(!$account_connected)
			return $this->_connectPage();
		
		if(!$phone_selected)
			return $this->_selectPhonePage();
		
		if(!$phone_verified)
			return $this->_verifyPhonePage();
		
		if(!$phone_registered)
		{
			$register_result = $this->_phoneRegister($phone_number_id);
			
			if(!$register_result['success']){
				return $this->_somethingWrongPage($register_result["error"]??"");
			}
		}
		
		if($account_status != 'VERIFIED_ACCOUNT')
			return $this->_sandboxPage();
		
		if(!$congratulation_skipped)
			return $this->_congratulationPage();

		$tab = $this->request->get['tab'] ?? "";
		
		//if there is a typo error at tab value correct the url 
		if(!in_array($tab,['notifications','chat','settings'])){
            $this->response->redirect($this->url->link('module/whatsapp_cloud?tab=notifications', '','SSL'));
		}
		
		return $this->_livePage($tab);
	}
	
	//======================== Live Account View ============================//
	
	private function _livePage($active_page='notifications'){
		
		$this->load->language('module/whatsapp_cloud');

		$this->data['active_page'] 		= $active_page;
		
		if($active_page == 'notifications')
			$this->_notificationsData();
		
		else if($active_page == 'chat')
			$this->_chatData();
		
		else 
			$this->_settingsData();
		
		$this->data['update_status_url']  = $this->url->link('module/whatsapp_cloud/updateStatusXHR', '', 'SSL');
		
        $this->template = 'module/whatsapp_cloud/index.expand';
        $this->response->setOutput($this->render());
	}
	
	private function _notificationsData(){
		
		$this->document->setTitle($this->language->get('title_whatsapp_bot'));
		$this->load->model('localisation/order_status');
		
		$this->data['order_statuses'] 	= $this->model_localisation_order_status->getOrderStatuses();
		$this->data['languages'] 		= $this->_getTemplatesLanguages();
		$this->data['add_template_url'] = $this->url->link('module/whatsapp_cloud/updateCustomTemplateXHR', '', 'SSL');;
		
		$this->data['customer_account_registration'] = $this->config->get('customer_account_registration');
		$this->data['update_custom_template_url']    = $this->url->link('module/whatsapp_cloud/updateCustomTemplateXHR', '', 'SSL');;
		$this->data['update_default_template_url']   = $this->url->link('module/whatsapp_cloud/updateDefaultTemplateXHR', '', 'SSL');;
		$this->data['delete_custom_template_url']    = $this->url->link('module/whatsapp_cloud/deleteCustomTemplateXHR', '', 'SSL');;
		$this->data['multiseller_installed']		 = \Extension::isInstalled('multiseller');
		//$this->data['multiseller_installed']		 = true; //should comment this 
		
		// init seller statuses list
        $msSeller = new ReflectionClass('MsSeller');
        $this->data['seller_statuses'] = [];
        foreach ($msSeller->getConstants() as $constant => $value) {
            if (strpos($constant, 'STATUS_') !== FALSE) {
                $this->data['seller_statuses'][] = $value;
            }
        }
		
		$this->_TemplateStatusGrouping();
	}
	
	private function _chatData(){
		$this->document->setTitle($this->language->get('title_whatsapp_chatting'));
		
		$this->load->model('module/whatsapp_cloud/waba');
		
		$phone_numbers = $this->model_module_whatsapp_cloud_waba
							  ->wabaPhoneNumbers($this->config->get('whatsapp_cloud_business_account_id')); 
		$phone_data = null;
		foreach ($phone_numbers as $phone_number){
			if($phone_number->id == $this->config->get('whatsapp_cloud_phone_number_id')){
				$phone_data = $phone_number;
				break;
			}
		}
		
		$this->data["profile_name"] 		= $phone_data->verified_name ?: $this->config->get("whatsapp_cloud_profile_name");
		$this->data["profile_description"]  = $this->config->get("whatsapp_cloud_profile_description");	
		$this->data["media_path"]  			= "../ecdata/stores/" . STORECODE;	
		$this->data["whatsapp_verticals"] 	= [
												["value" => "AUTO"			, "text" => "Vehicle service"],
												["value" => "BEAUTY"		, "text" => "Beauty, cosmetic & personal care"],
												["value" => "APPAREL"		, "text" => "Apparel & clothing"],
												["value" => "EDU"			, "text" => "Education"],
												["value" => "ENTERTAIN"		, "text" => "Arts & entertainment"],
												["value" => "EVENT_PLAN"	, "text" => "Event planner"],
												["value" => "FINANCE"		, "text" => "Finance"],
												["value" => "GROCERY"		, "text" => "Supermarket/Convenience store"],
												["value" => "GOVT"			, "text" => "Public & government service"],
												["value" => "HOTEL"			, "text" => "Hotel"],
												["value" => "HEALTH"		, "text" => "Medical & health"],
												["value" => "NONPROFIT"		, "text" => "Non-profit organisation"],
												["value" => "PROF_SERVICES"	, "text" => "Professional service"],
												["value" => "RETAIL"		, "text" => "Shopping & retail"],
												["value" => "TRAVEL"		, "text" => "Travel & transport"],
												["value" => "RESTAURANT"	, "text" => "restaurant"],
												["value" => "OTHER"			, "text" => "other"]
											];	
	}
	
	private function _settingsData(){
		
		$this->document->setTitle($this->language->get('title_whatsapp_settings'));
		
		$this->load->model('localisation/country');
		$this->load->model('sale/customer_group');
		
		$language_id = $this->config->get('config_language_id');
	    $this->data["countries"] = $this->model_localisation_country->getCountries($language_id);
	   
		$this->data["whatsapp_business_id"] 	= $this->config->get("whatsapp_cloud_business_account_id");
	    $this->data["phone_number"] 			= $this->config->get("whatsapp_cloud_phone_number");
		$this->data['whatsapp_cloud_chat_show']	= $this->config->get('whatsapp_cloud_chat_show');
	
		$this->data['customer_groups']						= $this->model_sale_customer_group->getCustomerGroups();
		$this->data['whatsapp_cloud_chat_selected_groups']	= $this->config->get('whatsapp_cloud_chat_selected_groups');
		$this->data['whatsapp_cloud_chat_applied_on']	  	= $this->config->get('whatsapp_cloud_chat_applied_on');
		
		$number_filtering = explode(",",$this->config->get("whatsapp_cloud_number_filtering"));

		$this->data["whatsapp_cloud_number_filtering"]  = $number_filtering;
		$this->data['whatsapp_cloud_config_confirmation_trials'] = $this->config->get('whatsapp_cloud_config_confirmation_trials');

		
		$this->data["update_account_settings_url"] 		= $this->url->link('module/whatsapp_cloud/updateAccountSettingXHR', '', 'SSL');;
	    $this->data["uninstall_url"] 			    	= $this->url->link('marketplace/home/uninstall?extension=whatsapp_cloud', '', 'SSL');;
	    $this->data["after_uninstall_redirect_url"] 	= $this->url->link('marketplace/home', '', 'SSL');
	}
	
	//======================== Onboarding Views ===============================//
	
	// WA Registration Step 
	private function _connectPage(){
		
		$this->load->language('module/whatsapp_cloud');
		
		$this->data['facebook_app_id'] 	= $this->facebook_app_id;
		$this->data['domain'] 			= DOMAINNAME;
        $this->data['store_code'] 		= STORECODE;
		$this->data['redirect_page'] 	= self::$redirect_page;
		$this->data['embedded_status'] 	= $this->config->get('whatsapp_cloud_sandbox_status');
		$this->data['has_errors']		= $this->request->get['has_errors'];
		
        $this->template = 'module/whatsapp_cloud/onboarding/connect.expand';
        $this->response->setOutput($this->render());
	}
	
	// Account Has multiple phone number - we need to select the required one 
	private function _selectPhonePage(){
		
		$this->load->language('module/whatsapp_cloud');
		$this->load->model('module/whatsapp_cloud/waba');
		
		$phone_numbers = $this->model_module_whatsapp_cloud_waba
								->wabaPhoneNumbers($this->config->get('whatsapp_cloud_business_account_id'));
								
		$this->data['phone_numbers'] = $phone_numbers;

        $this->template = 'module/whatsapp_cloud/onboarding/selectphone.expand';
        $this->response->setOutput($this->render());
	}
	
	//Phone Number need verification / not added through SDK 
	private function _verifyPhonePage(){
		
		$this->load->language('module/whatsapp_cloud');
		
		$phone_cc 	   = $this->config->get('whatsapp_cloud_phone_cc');
		$phone_number  = $this->config->get('whatsapp_cloud_phone_number');
		$phone_number_id  = $this->config->get("whatsapp_cloud_phone_number_id");

		$this->data['whatsapp_cloud_phone_number'] 	  = $phone_cc.$phone_number;
		$this->data['whatsapp_cloud_phone_number_id'] = $phone_number_id;

		/*
		 * this step added to re-correct any wrong happened at setup related to OTP Verification
		 * we double check &  make sure here that he really need verification 
		 * if it already verifed update the config for him & redirect him to app main page  
		 */
		 $this->load->model('module/whatsapp_cloud/waba');
		 
		 $number_data = $this->model_module_whatsapp_cloud_waba->phoneNumberData($phone_number_id);
		 
		 if($number_data->code_verification_status == "VERIFIED")
		 {
			$this->setting->editSettingValue(
												self::$module_name,
												'whatsapp_cloud_phone_verified',
												1
												);
			$this->redirect($this->url->link('module/whatsapp_cloud'));
		 }
		
		 
        $this->template = 'module/whatsapp_cloud/onboarding/verifyphone.expand';
        $this->response->setOutput($this->render());
	}
	
	// WAITING WA Verification Step | PENDING - BANNED 
	private function _sandboxPage(){
			
		$this->load->language('module/whatsapp_cloud');
		$this->load->model('module/whatsapp_cloud/waba');
		
		$waba_id		    = $this->config->get("whatsapp_cloud_business_account_id");
		$business_info	    = $this->model_module_whatsapp_cloud_waba->wabaData($waba_id);
		
		//account_status possible values : PENDING_REVIEW,VERIFIED_ACCOUNT,DISABLED_UPDATE
		$account_status 	= $this->config->get('whatsapp_cloud_sandbox_status') ; //should un-comment this line 
		$integration_step 	= (int)$this->config->get('whatsapp_cloud_integration_step');
		
		//$account_status 	= 'PENDING_REVIEW'; 	//for test  
		//$account_status 	= 'VERIFIED_ACCOUNT'; 	//for test  
		//$account_status 	= 'DISABLED_UPDATE'; 	//for test  
		
		$this->data['review_status'] 					= $business_info->account_review_status ?? '';
		$this->data['integration_step'] 				= $integration_step;
		$this->data['account_status'] 					= $account_status;
		//$this->data['whatsapp_cloud_congratulation_skipped'] = $this->config->get('$whatsapp_cloud_congratulation_skipped') ;
        
		$this->template = 'module/whatsapp_cloud/onboarding/sandbox.expand';				
        $this->response->setOutput($this->render());
	}
	
	// VERIFIED & Ready to GO LIVE 
	private function _congratulationPage(){
		$this->load->language('module/whatsapp_cloud');
		$this->load->model('module/whatsapp_cloud');       
		$this->template = 'module/whatsapp_cloud/onboarding/congratulation.expand';				
        $this->response->setOutput($this->render());
	}
	
	//blocker case | cant proceed till check & fix 
	private function _somethingWrongPage($error=null){
		$this->load->language('module/whatsapp_cloud');
		$this->load->model('module/whatsapp_cloud');
		$this->template = 'module/whatsapp_cloud/onboarding/somethingwrong.expand';			
		
		if(!empty($error)){
			$this->data['error'] = $error;
		}
		
        $this->response->setOutput($this->render());
	}
	
	//callback from embeded Flow 
	public function signupCallback(){
		 
        if ($this->request->server['REQUEST_METHOD'] == 'GET' && isset($this->request->get['accessToken'])) {
            $access_token = $this->request->get['accessToken'];
            
			//$user_id = $this->request->get['userID'];
			$this-> _signupIntegration($access_token);
			$has_errors = empty($this->errors)? 0 : 1;


			//FB pixel event tracking
			if(!$has_errors)
				$this->sendFbEventRequest();

			$this->redirect($this->url->link('module/whatsapp_cloud?has_errors='.$has_errors));
        }
    }
	
	//======================= XHR ===========================//
	//sandbox : sending trial message 
	public function sendTrialMessage(){
		
		$result_json = [];
		
		if ( $this->request->server['REQUEST_METHOD'] == 'POST'  ){
			
			
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
				$message_errors 		= [];
				
				$this->load->model('module/whatsapp_cloud/template_message');
				$result	= $this->model_module_whatsapp_cloud_template_message->sendTemplateMessage($to,$template_name,$language,$components);
				
				if(is_array($result) && $result[0] != false ){
					
					$result			= $result[0];
					$message_sent 	= $result->messages ?? false;
					$message_errors = $result->errors ?? [];
					
					if($message_sent){
						$result_json['success']	= '1';
					}
					
					$result_json['errors']	= $message_errors;
				}
			}
	
		}
		
		$this->response->setOutput(json_encode($result_json));
        return;
	}
	
	//sandbox : congratulation skip 
	public function congratulationSkip(){
		
		$result_json = [];
		$result_json['success']	= '0';
		$result_json['errors'] 	= [];
		
		if ($this->request->server['REQUEST_METHOD'] == 'POST'
			&& $this->config->get('whatsapp_cloud_sandbox_status') == 'VERIFIED_ACCOUNT' )
			{
			$this->setting->editSettingValue(self::$module_name,'whatsapp_cloud_congratulation_skipped',1);
			$result_json['success']	= '1';
			$this->_defaultTemplatesGenerate();
		}
		
		$this->response->setOutput(json_encode($result_json));
        return;
	}
	
	//phone selection 
	public function phoneSelection(){
		
		$response		 = ['hasErrors'=>false , 'whatsErrors' => [] ];
		$phone_number_id = $this->request->post['phone_number_id'] ?? "";
		
		if(empty($phone_number_id)){
			$response['hasErrors']   = true;
			$this->response->setOutput(json_encode($response));
			return;
		}
		
		$this->load->model('module/whatsapp_cloud/waba');
		
		$phone_numbers =$this->model_module_whatsapp_cloud_waba
							->wabaPhoneNumbers($this->config->get('whatsapp_cloud_business_account_id')); 
		
		if(isset($phone_numbers->errors) || empty($phone_numbers)){
			$response['hasErrors']	 = true;
			$response['whatsErrors'] = property_exists($phone_numbers,'errors')?$phone_numbers->errors : [];
			$this->response->setOutput(json_encode($response));
			return;
		}
		
		$selected_phone_number = false;
		foreach ($phone_numbers as $phone_number ) {
			if($phone_number->id == $phone_number_id ) {
				$selected_phone_number = $phone_number ;
			}
		}
		
		if(!$selected_phone_number){
			$response['hasErrors']   = true;
			$this->response->setOutput(json_encode($response));
			return;
		}
		
		$display_phonenumber	 		 = $selected_phone_number->display_phone_number?:'';
		$display_phonenumber_arr 		 = explode(' ',$display_phonenumber );
		$whatsapp_cloud_phone_cc		 = $display_phonenumber_arr[0]??"";
		$whatsapp_cloud_phone_number 	 = str_replace(' ','',substr($display_phonenumber, strlen($whatsapp_cloud_phone_cc)));
		$whatsapp_cloud_phone_number_id  = $selected_phone_number->id ;
		$whatsapp_cloud_phone_verified   = $selected_phone_number->code_verification_status == 'VERIFIED' ? 1 : 0 ;
		$whatsapp_cloud_profile_name     = $selected_phone_number->verified_name ;
		
		$data = [
            "whatsapp_cloud_phone_selected"  => 1, 
            "whatsapp_cloud_phone_number_id" => $whatsapp_cloud_phone_number_id, 
			"whatsapp_cloud_phone_verified"  => $whatsapp_cloud_phone_verified,
            "whatsapp_cloud_phone_cc" 		 => $whatsapp_cloud_phone_cc,
			"whatsapp_cloud_phone_number" 	 => $whatsapp_cloud_phone_number,
			"whatsapp_cloud_profile_name" 	 => $whatsapp_cloud_profile_name
        ];
		
		$register_result = $this->_phoneRegister($whatsapp_cloud_phone_number_id,false);
		
		if($register_result['success']){
			$data["whatsapp_cloud_phone_registered"] = 1; 
			$data["whatsapp_cloud_phone_bin"]		 = $register_result["bin"] ;
		}
		
		
        $this->setting->insertUpdateSetting('whatsapp_cloud', $data);		
		
        $this->response->setOutput(json_encode($response));
        return;
	}
	
	//
	public function phoneVerification(){
		
		if (!isset($this->request->get['target'])) {
			$this->response->setOutput(json_encode(["error" => "Error"]));
            return;
        }
		
		$this->load->model('module/whatsapp_cloud/waba');
		
        $target				= $this->request->get['target'];
        $post_data			= $this->request->post;
		$integration_step	= $this->config->get('whatsapp_integration_step');
		$phone_number_id 	= $this->config->get('whatsapp_cloud_phone_number_id');
		
		$response = [];
		$response['hasErrors']	 = false;
		$response['whatsErrors'] = [];
				
		if ($target == 'send_code') {
			
			$verification_method = $post_data['whatsapp_methods']??'sms';
			$language 			 = 'en_US';
			
			$sendcode_response= $this->model_module_whatsapp_cloud_waba
									 ->requestCode($phone_number_id,$verification_method,$language);
			
			if(isset($sendcode_response->errors)){
				$response['hasErrors']	 = true;
				$response['whatsErrors'] = $sendcode_response->errors;
			}
			
        } else if ($target == 'verify_code') {
		   
		   $whatsapp_verification_code  = $post_data['whatsapp_verification_code'];
		   $verify_response				= $this->model_module_whatsapp_cloud_waba
											   ->verifyCode($phone_number_id,$whatsapp_verification_code);
			
			
			if($verify_response->success){
				
				$this->setting->editSettingValue(
												self::$module_name,
												'whatsapp_cloud_phone_verified',
												1
												);
												
			} else if(isset($verify_response->errors))
			{
				$response['hasErrors']	 = true;
				$response['whatsErrors'] = $verify_response->errors;
			}
			
			
        } else {
			$response['hasErrors'] = true;
		}
		
        $this->response->setOutput(json_encode($response));
        return;
		
	}
	
	//
	public function updateDefaultTemplateXHR(){
		
		//TO:DO | validate default template update 
		$data = $this->request->post;
		
		$template_key = $data['template_key']??"";
	
		$result  = $this->_updateTemplate($data,$template_key);
		$configs = $result['configs'] ?? [] ;
		
		
		if(!empty($configs)){
			$this->setting->insertUpdateSetting(self::$module_name,$configs);
		}
		
		$this->result_json['success']= empty($this->result_json['errors'])? 1 : 0 ;
		
		$this->response->setOutput(json_encode($this->result_json));
        return;
	}

	// this method for add and update Custom templates 
	public function updateCustomTemplateXHR(){
		
		//TO:DO | validate validateUpdateTemplateXHR 
		
		$data  					= $this->request->post;
		$observer_name 			= $data["observer_name"] ?? "";
		$observer_key  			= $data["observer_key"]  ?? "";
		$observer_config_name 	= "whatsapp_cloud_".$observer_name;
		$observer_templates 	= $this->config->get($observer_config_name);

		$this->load->model('module/whatsapp_cloud/template_message');

		//for update case 
		if(isset($data["template_index"])) {
			
			$observer_templates = $this->model_module_whatsapp_cloud_template_message
											->deleteObserverTemplate($observer_config_name,$data["template_index"]);
		}else {
			
			//handle add case  with already exists status 
			foreach ($observer_templates as $index => $template ){
				if($template["key"] == $observer_key ){
					
					$observer_templates = $this->model_module_whatsapp_cloud_template_message
											->deleteObserverTemplate($observer_config_name,$index);
					break;
				}
			}
			
		}
		
		
		//TO:DO if there is old template with the same observer_key delete it 
		$languages 			= $this->_getTemplatesLanguages();
		$components 		= self::$template_components;
		$template_new_name 	= $observer_name.'_'.$observer_key.'_'.time();
		
		//saving of templates 
		
		$new_template 		  = [];
		$new_template["key"]  = $data["observer_key"] ?? "" ;
		$new_template["name"] = "";
		$new_template["data"] = [];
		
		foreach ($languages as $language){
			
			$new_template["data"][$language["code"]] = []; 
			$msg_components = []; 

			foreach($components as $component ){
				
				$submitted_text 		= $data["message_". $component][$language["code"]] ?? ""; 
				
				$facebook_text_format 	= $this->model_module_whatsapp_cloud_template_message->facebookTextFormat($submitted_text);
				
				if( $component != 'HEADER'){
					$msg_components[]	= ["type"=>$component,"text"=>$facebook_text_format];
				}else{
					$msg_components[]	= ["type"=> $component,"text"=>$facebook_text_format,"format"=>'TEXT'];	
				}
				
				$input_data  = $data["message_".$component][$language["code"]] ?? "";
				
				//$new_template[$component."_".$language["code"]] = $input_data;
				$new_template["data"][$language["code"]][$component] = $input_data;
			}
			
			//$new_template["STATUS_".$language["code"]]  = "";
			$new_template["data"][$language["code"]]["STATUS"] = "";
				
			$result = $this->model_module_whatsapp_cloud_template_message
							->requestTemplate([
											'name' 		 => $template_new_name, 
											'language'   => $language['code'], 
											'components' => $msg_components
											]);
														
			
			if(property_exists($result,'id')){
				$new_template["name"] = $template_new_name;
				$new_template["data"][$language["code"]]["STATUS"] = "PENDING";
				
			}else if(property_exists($result,'error')){
				$this->result_json['errors'][] = ['message'=>$result->error->message];
			}else{
				$this->result_json['errors'][] = ['message'=>'something went wrong'];
			}
			
		}
				
		$observer_templates[] = $new_template;
		
		$this->setting->insertUpdateSetting(self::$module_name,[$observer_config_name=>$observer_templates]);
			
		$this->result_json['success']= empty($this->result_json['errors'])? 1 : 0 ;
		
		$this->response->setOutput(json_encode($this->result_json));
        return;
	}
	
	//
	public function deleteCustomTemplateXHR(){
		
		//TO:DO | validate validateDeleteTemplateXHR 
		
		$data  					= $this->request->post;
		$observer_name 			= $data["observer_name"] ?? "";
		$observer_config_name 	= "whatsapp_cloud_".$observer_name;
		$observer_templates 	= $this->config->get($observer_config_name);
		
		if(isset($data["template_index"])){
			$this->load->model('module/whatsapp_cloud/template_message');

			$observer_templates= $this->model_module_whatsapp_cloud_template_message
										->deleteObserverTemplate($observer_config_name,$data["template_index"]);
										
			$this->setting->editSettingValue(self::$module_name,$observer_config_name,$observer_templates);
		}else{
			$this->result_json['errors'][] = ['message'=>'something went wrong'];
		}
		
		$this->result_json['success']= empty($this->result_json['errors'])? 1 : 0 ;
		
		$this->response->setOutput(json_encode($this->result_json));
        return;
	}

	// 
	public function updateStatusXHR(){
		
		$data  			= $this->request->post;
		$config_name	= $data["config_name"]  ?? "";
		$config_value	= $data["config_value"] ?? 0;
		
		$allowed_configs = [
			//customer 
			'whatsapp_cloud_config_notify_customer', 
			'whatsapp_cloud_config_notify_customer_on_registration',
			'whatsapp_cloud_config_notify_customer_on_checkout',
			'whatsapp_cloud_config_notify_customer_phone_confirm',
			'whatsapp_cloud_config_customer_phone_confirmation_one_only',
			//admin
			'whatsapp_cloud_config_notify_admin',
			'whatsapp_cloud_config_notify_admin_on_registration',
			'whatsapp_cloud_config_notify_admin_on_checkout',
			//seller
			'whatsapp_cloud_config_notify_seller'
		];
		
		if(empty($config_name) || !in_array($config_name,$allowed_configs)){
			$this->result_json['errors'][] = ['message'=>'something went wrong'];
		}else {
			$this->setting->insertUpdateSetting(self::$module_name,[$config_name=>$config_value]);
		}
		
		
		$this->result_json['success'] = empty($this->result_json['errors'])? 1 : 0 ;
		$this->response->setOutput(json_encode($this->result_json));
        return;
	}
	
	//
	public function getChatsXHR(){
		
		$result = false;
		
		if ( $this->request->server['REQUEST_METHOD'] == 'GET'  )
        {
			$this->load->model('module/whatsapp_cloud/chat');
			$chats  = $this->model_module_whatsapp_cloud_chat->getAllChats();
			$result = json_encode($chats,true);	
		}
		
		$this->response->setOutput($result);
		return;
		
	}
	
	/**
	 *	for read chat messages
	 */
	public function readMessagesXHR(){
		
		$result = false;
		
		if ( $this->request->server['REQUEST_METHOD'] == 'GET'  && isset($this->request->get['to']))
        {
			$current_chat	= $this->request->get['to'];
			
			$this->load->model('module/whatsapp_cloud/message');
			$result = $this->model_module_whatsapp_cloud_message->readMessages($current_chat);
		}
		
		$this->response->setOutput(json_encode($result));
        return;
	}
  
	/**
	 *	for get messages using pagination by ajax 
	 *
	 */
	public function getMessagesXHR(){
		
		$result = false;
		
		if ( $this->request->server['REQUEST_METHOD'] == 'GET'  )
        {
			$limit			= 10;
			$current_chat	= $this->request->get['to'];
			$page 			= $this->request->get['page'];
			
			$this->load->model('module/whatsapp_cloud/message');
			$messages 		= $this->model_module_whatsapp_cloud_message->getMessages($current_chat,$limit,$page);
			
			$result 		= json_encode($messages,true);	
		}
		
		$this->response->setOutput($result);
        return;
	}
	
	/**
	 *	for get messages using pagination by ajax 
	 *
	 */
	public function getMessageXHR(){
		
		$result = false;
		
		if ( $this->request->server['REQUEST_METHOD'] == 'GET'  )
        {
			$limit			= 10;
			$current_chat	= $this->request->get['to'];
			$id 			= $this->request->get['id'];
			
			$this->load->model('module/whatsapp_cloud/message');
			$message 		= $this->model_module_whatsapp_cloud_message->getMessage($id);
			
			$result 		= json_encode($message,true);	
		}
		
		$this->response->setOutput($result);
        return;
	}
	
	/**
	 *	for get messages using pagination by ajax 
	 *
	 */
	public function newMessagesXHR(){
		
		$result = false;
		
		if ( $this->request->server['REQUEST_METHOD'] == 'GET'  )
        {
			$chat_id	= $this->request->get['to'];
			$id 		= $this->request->get['last_id'];
			
			$this->load->model('module/whatsapp_cloud/message');
			$messages 	= $this->model_module_whatsapp_cloud_message->getMessagesAfter($chat_id,$id);
			
			$result 	= json_encode($messages,true);	
		}
		
		$this->response->setOutput($result);
        return;
	}
	
	/**
	 *	for sending direct messages by ajax 
	 */
	public function sendMessageXHR(){
		
		$result=[];
		if ( $this->request->server['REQUEST_METHOD'] == 'POST'  )
        {
			if (isset($this->request->post['chat_id']) && $this->request->post['chat_id'] != '' ){
				
				$chat_id 	= $this->request->post['chat_id'];
				
				$this->load->model('module/whatsapp_cloud/chat');
				
				$chat = $this->model_module_whatsapp_cloud_chat->getChat($chat_id);
				
				if(empty($chat))
					return ; //TO:DO | return error here 
				
				//handle 
				$file 	 = $this->request->files["file"] 	?? "";
				$message = $this->request->post['message'] 	?? "";
				$type 	 = "text";
				$media	 = $media_id = $message_text =  '';
				
				
				if(!empty($file)){
					$file_data = new CurlFile($file['tmp_name'], $file['type'], $file['name']);
					
					$this->load->model('module/whatsapp_cloud/media');
					$result = $this->model_module_whatsapp_cloud_media->uploadMedia($file_data);
					$fb_media_id = $result->id ??"" ;
					
					//TO:DO need enhancements | what if fail , can we use link directly 
					if(!empty($fb_media_id)){
						
						$file_data  = new CurlFile($file['tmp_name'], $file['type'], $file['name']);
						$type 		= explode("/",$file['type'])[0]??"";
						$file_name 	= "m_".time(). '.' .  substr(strrchr($file['name'], '.'), 1);
						$media_url  = $file_path	= "/whatsapp/" . $file_name ;
						$uploaded   = \Filesystem::setPath($file_path)->upload($file['tmp_name']);
						
						$caption	= (!empty($message) && $type != 'audio') ? $message : ""; 
						
						$media      = json_encode(["name" =>$file['name'],"type" => $type ,"caption"=>$caption]);
						
						$this->load->model('module/whatsapp_cloud/media');
						$media_id	=  $this->model_module_whatsapp_cloud_media->insertMedia([
																		'media'			=> $media,
																		'fb_media_id'	=> $fb_media_id,
																		"url" 			=> $media_url
																		]);
						
						$media_data_sending  = ['id' => $fb_media_id];
						
						if(!empty($caption))
							$media_data_sending['caption']  = $message;
						
							$sending_data = [
											'type'	=> $type , 
											$type   => $media_data_sending
											];
					}

					
				}else if(!empty($message)){
					$message_text    = $message;
					$sending_data 	 = [
										"type" => "text",
										"text" => ["body" => $message]
										];
				}else {
					var_dump("something went wrong");die();
					//TO:DO || return error here 
				}
				
				$this->load->model('module/whatsapp_cloud/message');
				$result		= $this->model_module_whatsapp_cloud_message->sendMessage($chat["phone_number"],$sending_data);
				$message 	= $result->messages[0] ?? [] ;
				$fb_message_id = $message->id ?? false; 
				
				
				if(!$fb_message_id)
					return ; //TO:DO | return error here 
						
		
				//TO:DO | if success save message
				$message_data 			  		= [];
				$message_data["text"]			= $message_text;
				$message_data["chat_id"]  		= $chat_id; 
				$message_data["fb_message_id"] 	= $fb_message_id;  // returrned fb_id
				$message_data["type"]	  		= $type;
				$message_data["media_id"] 		= $media_id;
				$message_data["media"]    		= $media;
				$message_data["from_me"]  		= 1; 
				$message_data["fb_timestamp"] 	= gmdate("Y-m-d H:i:s",time());
				
				//update chat last_timestamp 
				$this->model_module_whatsapp_cloud_chat->updateChat($chat_id,[
																				'last_timestamp'=>$message_data["fb_timestamp"]
																				]);
																				
				$message_id = $this->model_module_whatsapp_cloud_message->insertMessage($message_data);
				
				$message_data["id"]		  		= $message_id;
				$result 						= json_encode([$message_data],true);
			}
		}
		
		$this->response->setOutput($result);
        return;
	}
	
	// 
	public function updateAccountSettingXHR(){
		
		$data  			= $this->request->post;
		$config_name	= $data["config_name"]  ?? "";
		$config_value	= $data["config_value"] ?? 0;
		
		$allowed_configs = [
			
			'whatsapp_cloud_number_filtering',
			'whatsapp_cloud_config_confirmation_trials',
			
			//chat settings 
			'whatsapp_cloud_chat_show',
			'whatsapp_cloud_chat_applied_on',
			'whatsapp_cloud_chat_selected_groups',
			
			
		];
		
		if(empty($config_name) || !in_array($config_name,$allowed_configs)){
			$this->result_json['errors'][] = ['message'=>'something went wrong'];
		}
		else if( $config_name == 'whatsapp_cloud_chat_selected_groups' && (empty($config_value) || $config_value=='null')){
			$this->result_json['errors'][] = ['message'=>'you should select a customer group or choose all customer option'];
		}else {
			
			$settings_arr = [$config_name=>$config_value];
			
			
			if($config_name == 'whatsapp_cloud_chat_selected_groups')
				$settings_arr['whatsapp_cloud_chat_applied_on'] = 'specific';
			
			$this->setting->insertUpdateSetting(self::$module_name,$settings_arr);
		}
		
		
		$this->result_json['success']= empty($this->result_json['errors'])? 1 : 0 ;
		$this->response->setOutput(json_encode($this->result_json));
        return;
	}

	/**
	 *	for get business profile  by ajax 
	 */
	public function getProfileXHR(){
		
		$this->load->model('module/whatsapp_cloud/business_profile');
		$data = $this->model_module_whatsapp_cloud_business_profile->getBusinessProfile();
		
		//TO:DO | need to handle this checking if there is error returned from the endpoint 
		$this->result_json['success']= empty($this->result_json['errors'])? 1 : 0 ;
		$this->result_json['data']	 = $data ;
		
		$this->response->setOutput(json_encode($this->result_json));
        return;
	}
	
	/**
	 *	for get business profile  by ajax 
	 */
	public function downloadMedia(){
		
		if(empty($this->request->get["id"]??""))
			$this->redirect($this->url->link('module/whatsapp_cloud?tab=chat'));
		
		$id = $this->request->get["id"];;
		
		$this->load->model('module/whatsapp_cloud/media');
		$media =  $this->model_module_whatsapp_cloud_media->getMedia($id);
		
		if(empty($media)){
			$this->result_json['success'] = 0;
			$this->result_json['errors']  = ["file not exists"];
			$this->response->setOutput(json_encode($this->result_json));
			return;
		}
			
		$media_data = json_decode($media["media"],true);
		$media_type = $media_data["type"]??"";
		$src 		= $media["url"];
		
		\Filesystem::setPath($src);

		if (\Filesystem::isExists()) {

				header('Content-Type: application/octet-stream');
				header('Content-Description: File Transfer');
				header('Content-Disposition: attachment; filename="' . ( basename($src)) . '"');
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Content-Length: ' . \Filesystem::getSize());
				if (ob_get_level()) ob_end_clean();
				//readfile($download_path, 'rb');
                echo \Filesystem::read();
				exit;
			}	
	}
	
	/**
	 *	for get business profile  by ajax 
	 */
	public function uploadProfileXHR() {
	
		$this->result_json['success']= 0;

		$this->load->model('module/whatsapp_cloud/business_profile');

		if(!$this-> _validateUploadProfile()){
			$this->response->setOutput(json_encode($this->result_json));
			return;
		}
			
		$file 		= $this->request->files["file"]; 
		$file_type 	= $file['type'];
			
		$result		= $this->model_module_whatsapp_cloud_business_profile
							->createUploadSession([
													'file_length'	=> $file['size'], 
													'file_type'		=> $file_type
												]);
			 
		if(!empty($result->id??"")){
			$upload_session_id = $result->id;

			$file_path	= realpath($file["tmp_name"]);
					
			$result		= $this->model_module_whatsapp_cloud_business_profile
								->uploadFbMedia([
											'upload_session_id' => $upload_session_id,
											'file_path'			=> $file_path,
											'file_length'		=> $file['size'], 
											'file_type'			=> $file_type
											]);
				
			if($result->h){
				$picture_handler = $result->h;
				
				$this->load->model('module/whatsapp_cloud/business_profile');

			
				$result = $this->model_module_whatsapp_cloud_business_profile
								->updateBusinessProfile([
														'profile_picture_handle' => $picture_handler
													]);
						
				$this->result_json['success'] = $result->success ?? 0;
				$this->result_json['errors']  = $result->error->message ? [$result->error] : [];
				$this->result_json['response_debug']   = $result;//for test prepose 
				$this->response->setOutput(json_encode($this->result_json));
				return;
			}
		}
		
		
		$this->result_json['response_debug']   = $result;//for test prepose 
		$this->response->setOutput(json_encode($this->result_json));
        return;
	}
	
	/**
	 *	for sending direct messages by ajax 
	 */
	public function updateProfileXHR() {
		
		$this->result_json['success']= 0;
		
		if ( $this->request->server['REQUEST_METHOD'] == 'POST'  )
        {
			
			$this->load->language('module/whatsapp_cloud');
			
			if(!$this->_validateUpdateProfile()){
				$this->result_json['success'] =  0;
				$this->result_json['error']	  = 'VALIDATION_ERROR';
				$this->result_json['errors']  = $this->validation_errors;
				$this->response->setOutput(json_encode($this->result_json));
				return;
			}
			
			$data 	= $this->request->post;

			$this->load->model('module/whatsapp_cloud/business_profile');

			$result = $this->model_module_whatsapp_cloud_business_profile->updateBusinessProfile($data);
			
			if(property_exists($result,"success") && $result->success){
				  $this->setting->insertUpdateSetting('whatsapp_cloud',[
															'whatsapp_cloud_profile_description' => $data["description"] ?? ""
															]);	
			}else {
				$this->result_json['error']   = "API_ERROR";
				$this->result_json['errors']  = ["message"=>$result->error->message??""];
			}
			
			$this->result_json['success'] = $result->success ?? 0;
			
		}
		
		$this->response->setOutput(json_encode($this->result_json));
        return;
	}
	
	/**
	 *	for sending direct messages by ajax 
	 */
	public function downloadFbMediaXHR() {
		
		$this->result_json['success']= 0;
		
		if ( $this->request->server['REQUEST_METHOD'] == 'POST'  )
        {
			

			$data		= $this->request->post;
			$media_id	= $data["media_id"];
			
			$this->load-> model('module/whatsapp_cloud/media');

			$media		= $this->model_module_whatsapp_cloud_media->getMedia($media_id);
			$media_url 	= $this->model_module_whatsapp_cloud_media->downloadFbMedia($media["fb_media_id"]);
			
			if(!empty($media_url)){
				$this->model_module_whatsapp_cloud_media->updateMedia($media["id"],["url" => $media_url]);
				$media["url"] = $media_url;
			}
			
			$this->result_json['success'] 	= !empty($media_url);
			$this->result_json['data'] 		= $media;
			$this->result_json['error']   	= ["message"=>$result->error->message??""];
		}
		
		$this->response->setOutput(json_encode($this->result_json));
        return;
	}
	
	/**
	 *
	 */
	public function install(){
		$this->load->model('module/whatsapp_cloud');
		$this->model_module_whatsapp_cloud->install();
	}
	
	/**
	 *	
	 */
	public function uninstall(){
		$this->load->model('module/whatsapp_cloud');
		$this->model_module_whatsapp_cloud->uninstall();
	}
	
	//===================== Helpers ===========================//
	//--signup integration 
	private function _signupIntegration($token){
		
		
			$this->load->model('module/whatsapp_cloud/waba');
			
			//step1 : fetch WABA ID From token
			try{
				$waba_id 		= $this->model_module_whatsapp_cloud_waba->fetchWaba($token);
			
			}catch (\Exception $e){
				//echo $e->getMessage();
				
				$waba_id = NULL;
			}
			
			if(!$waba_id)
			{
				$this->errors[]= (object) [
											'title' 			=> 'Cant Fetch Whatsapp Business Account',
											'message' 		    => 'Cant Fetch Whatsapp Business Account' ,
											'type'   			=> 'UnHandled scenario'
										];
				return;
			}
				
			$phone_numbers = $this->model_module_whatsapp_cloud_waba->wabaPhoneNumbers((int)$waba_id); 
			
				
				
			if(empty($phone_numbers)){
				
				$this->errors[]= (object) [
										'title' 			=> 'No phone attached',
										'message' 		    => 'there is no phone numbers attached to this WABA' ,
										'type'   			=> 'UnHandled scenario'
									];
				return;
			}
			
			//handle multiple phone-number connected to The  WABA 
			$whatsapp_cloud_phone_selected 	= 0 ; 
			$whatsapp_cloud_phone_registered = 0 ; 
			$whatsapp_cloud_phone_verified   = 0 ; 
			$whatsapp_cloud_phone_cc = $whatsapp_cloud_phone_number ='';
			$whatsapp_cloud_phone_number_id  = $whatsapp_cloud_profile_name  = $whatsapp_cloud_phone_bin = '';
			
			//here auto selection if there is only one number attached to the WABA 
			if(count($phone_numbers)== 1){
				$whatsapp_cloud_phone_selected 	= 1 ; 
				$display_phonenumber	 		= $phone_numbers[0]->display_phone_number?:'';
				$display_phonenumber_arr 		= explode(' ',$display_phonenumber );
				$whatsapp_cloud_phone_cc		= $display_phonenumber_arr[0]??"";
				$whatsapp_cloud_phone_number 	= str_replace(' ','',substr($display_phonenumber, strlen($whatsapp_cloud_phone_cc)));
				$whatsapp_cloud_phone_number_id = $phone_numbers[0]->id ;
				$whatsapp_cloud_phone_verified  = $phone_numbers[0]->code_verification_status == 'VERIFIED' ? 1 : 0 ;
				$whatsapp_cloud_profile_name    = $phone_numbers[0]->verified_name ;
			}
			
			
		
			if($whatsapp_cloud_phone_selected){
				$register_result = $this->_phoneRegister($whatsapp_cloud_phone_number_id,false);
				
				if($register_result['success']){
					$whatsapp_cloud_phone_bin 		 = $register_result["bin"]; 
					$whatsapp_cloud_phone_registered = 1; 
				}
			}
				
		
			//step2 : add Admin system user to WABA 
			$assign_admin_result = $this->model_module_whatsapp_cloud_waba->assignUserToWaba ($waba_id,'admin',['MANAGE','DEVELOP']);
				
			
			//step3 : add Employee system user to WABA 
			$assign_employee_result = $this->model_module_whatsapp_cloud_waba->assignUserToWaba ($waba_id,'employee',['MANAGE','DEVELOP']);
			
			//step4: share line of credit with client WABA_ID 
			$share_credit_result = $this->model_module_whatsapp_cloud_waba->shareLineOfCredit ($waba_id);
			if(!$this->_checkRequest($share_credit_result)){
				return;
			}
					
			//step5 : supscribe to APP to receive webhook 
			$subscribe_result=$this->model_module_whatsapp_cloud_waba->subscribeApp($waba_id);
			if(!$this->_checkRequest($subscribe_result)) {
				return;
			}
			
			
			//------- prepare Im data before create Integration request 
			
			//get last integration request if exists to fill image data with it 
			//for easier re verify old uninstalled images 
			$waba_data	  = $this->model_module_whatsapp_cloud_waba->wabaData($waba_id);
			
			
			
			$whatsapp_cloud_business_name = $whatsapp_cloud_business_id  = '';
			
			if($waba_data->owner_business_info){
				$whatsapp_cloud_business_name 	=  $waba_data->owner_business_info->name;
				$whatsapp_cloud_business_id 	=  $waba_data->owner_business_info->id;
			}
			
			$account_review_status 				= $waba_data->account_review_status;
			$whatsapp_cloud_template_namespaces = $waba_data->message_template_namespace;

			//TO:DO | Need to review this point 
			$whatsapp_cloud_fb_status = $account_review_status == 'APPROVED' ? 'VERIFIED_ACCOUNT' : 'PENDING_REVIEW';
			
			
			$data = [
					'whatsapp_cloud_business_account_id'	=> $waba_id ,
					'whatsapp_cloud_template_namespaces'	=> $whatsapp_cloud_template_namespaces ,
					'whatsapp_cloud_sandbox_status'			=> $whatsapp_cloud_fb_status,
					'whatsapp_cloud_phone_selected' 		=> $whatsapp_cloud_phone_selected,
					'whatsapp_cloud_phone_number_id' 	 	=> $whatsapp_cloud_phone_number_id,
					'whatsapp_cloud_phone_verified' 	 	=> $whatsapp_cloud_phone_verified,
					'whatsapp_cloud_profile_name' 	 	 	=> $whatsapp_cloud_profile_name,
					'whatsapp_cloud_phone_registered' 	 	=> $whatsapp_cloud_phone_registered,
					'whatsapp_cloud_phone_cc' 	 	 	 	=> $whatsapp_cloud_phone_cc,
					'whatsapp_cloud_phone_number' 	 	 	=> $whatsapp_cloud_phone_number,	
					'whatsapp_cloud_phone_bin' 	 	 		=> $whatsapp_cloud_phone_bin,	
					
					//Additional For Ectools request 
					'whatsapp_cloud_business_name' 	  	 	=> $whatsapp_cloud_business_name, 
					'whatsapp_cloud_business_id' 	  	 	=> $whatsapp_cloud_business_id ,
					'whatsapp_cloud_fb_status' 	  		 	=> $whatsapp_cloud_fb_status, 
				];
	
			//add ectools Integration request 
			//step6 : send ECtool Request
			$data['whatsapp_cloud_ectools_request_id'] = $this->model_module_whatsapp_cloud_waba->requestIntegration($data);
			
			//step7 : update setting 
			$this->_signupDefaultSetting($data);
			
		return;
	}
	
	//
	private function _checkRequest($result){
		$isSuccess = !empty($result) && !property_exists($result,'error') ;
		if(!$isSuccess){
			$this->errors[] = $result->error;	
		}
		return $isSuccess;		
	}
	
	//
	private function _phoneRegister($phone_number_id,$update_config=true,$bin=false){
		
		//6 DIGIT TWO Factor authentication , we need to keep this in DB 
		if(!$bin){
			$bin = random_int(100000, 999999);
		}
		
		$this->load->model('module/whatsapp_cloud/waba');
		$registration_response = $this->model_module_whatsapp_cloud_waba->wabaRegister($phone_number_id,$bin);
		
		//if register success 
		$error = '';
		if(property_exists($registration_response,'success') && $registration_response->success ){ 
			//to avoid multiple DBs attempts at some cases already there is update setting request after doing this proccess 
			if($update_config){
				$this->setting->insertUpdateSetting('whatsapp_cloud',[
																 'whatsapp_cloud_bin' => $bin ,
																 'whatsapp_cloud_phone_registered' =>1 
																 ]);
			}
			
			return ['success'=> true , 'bin' => $bin];
		}else {
			
			$error = $registration_response->error ?? "";
		}
		
		return ['success'=> false , 'error' => $error];
	}
	
	//
	private function _signupDefaultSetting($data){
		
		$defaultSettings = array(
            "whatsapp_cloud_account_connected" 		 	=> 1, 
            "whatsapp_cloud_business_account_id"  	 	=> $data['whatsapp_cloud_business_account_id']		?? "",
            "whatsapp_cloud_template_namespaces" 	 	=> $data['whatsapp_cloud_template_namespaces']		?? "",
			"whatsapp_cloud_sandbox_status"			 	=> $data['whatsapp_cloud_sandbox_status']			?? "PENDING_REVIEW",
            "whatsapp_cloud_phone_selected" 		 	=> $data['whatsapp_cloud_phone_selected'] 			?? 0, 
            "whatsapp_cloud_phone_number_id" 		 	=> $data['whatsapp_cloud_phone_number_id'] 			?? "", 
			"whatsapp_cloud_phone_verified" 		 	=> $data['whatsapp_cloud_phone_verified']			?? 0,
			"whatsapp_cloud_profile_name" 			 	=> $data['whatsapp_cloud_profile_name']			?? 0,
			"whatsapp_cloud_phone_registered" 		 	=> $data['whatsapp_cloud_phone_registered']			?? 0,
            "whatsapp_cloud_phone_cc" 				 	=> $data['whatsapp_cloud_phone_cc']					?? "",
			"whatsapp_cloud_phone_number" 			 	=> $data['whatsapp_cloud_phone_number']				?? "",
			"whatsapp_cloud_bin" 			 		 	=> $data['whatsapp_cloud_bin']						?? "",
            "whatsapp_cloud_congratulation_skipped"   	=> 0,
            "whatsapp_cloud_congratulation_skipped"   	=> 0,
            "whatsapp_cloud_config_notify_customer"   	=> 0,
            "whatsapp_cloud_config_notify_admin"   	  	=> 0,
            "whatsapp_cloud_config_notify_seller"   	=> 0,
			"whatsapp_cloud_config_confirmation_trials"	=> 3, 
			"whatsapp_cloud_config_customer_phone_confirmation_one_only" => 1 
        );

        $this->setting->insertUpdateSetting('whatsapp_cloud', $defaultSettings);		
	}
	
	//
	private function _updateTemplate($data,$template_key){
		
		$languages 	  = $this->_getTemplatesLanguages();
	
		$this->load->model('module/whatsapp_cloud/template_message');
		
		//delete template if exists 		
		$template_name_index = 'whatsapp_cloud_'.$template_key.'_name';
		
		if($this->config->has($template_name_index)){
			$result = $this->model_module_whatsapp_cloud_template_message
							->deleteTemplate($this->config->get($template_name_index));
		}
		
		$template_new_name 	= $template_key.'_'.time();
		$components 		= self::$template_components;
		$configs 			= [];
		$statuses_indexs    = [] ;	
		$template_data      = [];
		
		foreach ($languages as $lang){
			
			$msg_components = []; 
			$input_name		= '';
			$template_data[$lang['code']]=[];
			foreach($components as $component){

				$input_name			  = 'whatsapp_cloud_msg_'.$template_key.'_'.$lang['code'] . '_' . $component;
				$input_data 		  = $data[$input_name]??"";
				
				$template_data[$lang['code']][$component] = $input_data;
				
				$facebook_text_format = $this->model_module_whatsapp_cloud_template_message->facebookTextFormat($input_data);
							
				if($component != 'HEADER'){
					$msg_components[]=["type"=>$component,"text"=>$facebook_text_format];
				}else{
					$msg_components[]=["type"=>$component,"text"=>$facebook_text_format,"format"=>'TEXT'];	
				}
			}
			
			$template_data[$lang['code']]['STATUS'] = '';
			

			$result	= $this->model_module_whatsapp_cloud_template_message
							->requestTemplate([
											'name' 		  => $template_new_name , 
											'language'    => $lang['code'] , 
											'components'  => $msg_components
											]);
														
			//if success save the new configs 
			if(property_exists($result,'id')){
				$template_data[$lang['code']]['STATUS'] 	= 'PENDING';
				$configs[$template_name_index]		 		= $template_new_name; 
				$configs['whatsapp_cloud_'.$template_key] 	= $template_data; 
				
			}else {
				if(property_exists($result,'error')){
					$this->result_json['errors'][] = ['message'=>$result->error->message];
				}else{
					$this->result_json['errors'][] = ['message'=>'something went wrong'];
				}
			}
		}
		

		$templates = $this->model_module_whatsapp_cloud_template_message->getTemplate($template_new_name);
		
		foreach($templates as $template ){
			$template_lang = $template->language;
			$template_data[$lang['code']]['STATUS'] = 'PENDING';
		}
		
		return ['configs'=>$configs];
	}
	
	//
	private function _defaultTemplatesGenerate(){
		
		$default_template_names = self::$default_template_names;
		$components				= self::$template_components;
		$store_names 			= $this->config->get("config_name");
		
		$this->load->model('module/whatsapp_cloud/template_message');
		
		$default_template_data  = $this->model_module_whatsapp_cloud_template_message->initDefaultTemplates();
		$default_data_languages = ['en','ar'];

		//adding store name at footer 
		$configs = [];
		foreach ($default_template_data as $template_key => $template ){
			
			foreach($default_data_languages as $lang_code ){
				$default_template_data[$template_key]['whatsapp_cloud_msg_'.$template_key.'_'. $lang_code . '_FOOTER'] =$store_names[$lang_code]??"";;
			}
			
			$result 	 = $this->_updateTemplate($default_template_data[$template_key],$template_key);
			$new_configs = $result['configs'] ?? [] ;
			$configs 	 = array_merge($configs, $new_configs);
			
		}
		
		if(!empty($configs)){
			$this->setting->insertUpdateSetting(self::$module_name,$configs);
		}
		
		
	}

	//
	private function _TemplateStatusGrouping(){

		foreach(self::$default_template_names as  $template_name){
			$this->_handleTemplateStatuses($template_name);
		}

		foreach(self::$template_observers as $observer_name){
			$observer_templates = $this->config->get('whatsapp_cloud_'.$observer_name);
			
			foreach($observer_templates as $index => $observer){
				$template_name 	= $observer_name .'_' .$index ?? "";
				$this->_handleTemplateStatuses($template_name,'observer',$observer);
			}
		}
	}
	
	//dynamically grouping of template language status and set them at data array 
	private function _handleTemplateStatuses($template_name,$template_type='default',$observer=[]){
		
		${$template_name.'_rejected'}  = [] ;
		${$template_name.'_pending'}   = [] ;
		${$template_name.'_approved'}  = [] ;
		${$template_name.'_undefined'} = [] ;
		
		$languages = $this->_getTemplatesLanguages();
		
		foreach ($languages as $lang){
			
			if($template_type == 'observer'){
				$template = $observer["data"];
			}else {
				$template = $this->config->get('whatsapp_cloud_'.$template_name);
			}
			
			$status = strtoupper($template[$lang['code']]['STATUS']);
				
			if ($status == 'APPROVED' ) {
				
				${$template_name.'_approved'}[]		= $lang; 
				
			} else if ($status == 'PENDING' ) {
				
				${$template_name.'_pending'}[]		= $lang; 
			
			} else if ($status == 'REJECTED' ) {
				
				${$template_name.'_rejected'}[] 	= $lang; 
			
			} else {

				${$template_name.'_undefined'}[]	= $lang; 
			}
		}
			
		if(!empty(${$template_name.'_rejected'})){
			$this->data['whatsapp_cloud_msg_'.$template_name.'_final'] 	= 'need_attention';
		}else if(!empty(${$template_name.'_pending'})){
			$this->data['whatsapp_cloud_msg_'.$template_name.'_final'] = 'pending';
		}else if(count(${$template_name.'_approved'}) == count($languages)){
			$this->data['whatsapp_cloud_msg_'.$template_name.'_final'] = 'approved';
		}else {
			$this->data['whatsapp_cloud_msg_'.$template_name.'_final'] = 'undefined';
		}
			
		$this->data['whatsapp_cloud_msg_'.$template_name.'_rejected'] 	= ${$template_name.'_rejected'};
		$this->data['whatsapp_cloud_msg_'.$template_name.'_pending'] 	= ${$template_name.'_pending'};
		$this->data['whatsapp_cloud_msg_'.$template_name.'_approved'] 	= ${$template_name.'_approved'};
		$this->data['whatsapp_cloud_msg_'.$template_name.'_undefined'] 	= ${$template_name.'_undefined'};
		
	}
	
	//
	private function _getTemplatesLanguages(){
		
		if(empty($this->templates_languages)){
			$this->load->model('localisation/language');
			$this->templates_languages = $this->model_localisation_language->getLanguages();
		}
		
		return $this->templates_languages;
	}
	
	//====================== validations ==============================//
	
	private function _validateUpdateProfile(){
		$data = $this->request->post;
		
		if(!empty($data['email'] ?? "") ){
			if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
			 $this->validation_errors['email'] =  $this->language->get('error_email_invalid');
			}
			
		}
		
		return empty($this->validation_errors);
	}
	
	private function _validateUploadProfile(){
		
		if ( $this->request->server['REQUEST_METHOD'] !== 'POST' ){
			$this->result_json['errors'] = ["invalid request type"];
		}
		
		if (!empty($this->request->files['file']['name'])) {
			
			$filename = html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8');

			if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 128)) {
				$this->result_json['errors'] = [$this->language->get('error_filename')];
			}

			$allowed = ['jpg','jpeg','png','gif','webp'];

			if (!in_array(utf8_substr(strrchr($filename, '.'), 1), $allowed)) {
				$this->result_json['errors'] = [$this->language->get('error_filetype')];
			}

			if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
				$this->result_json['errors'] = [$this->language->get('error_upload_' . $this->request->files['file']['error'])];
			}
		} else {
			$this->result_json['errors']  = [ $this->language->get('error_upload')];
		}
		
		return empty($this->result_json['errors']);
	}
	
	//========= for test ============ 
	/**
	 *
	 * this method to enable expandcart instance at specific store 
	 * note this not adding a record at ectools to avoid any changing of webhooks route 
	 * we will add ectool record in DB Manually 
	 * so you can only test forward message not receiving message till adding ectools record 
	 *
	 */
	public function expandCartInstance(){
		
		if(!defined("WHATSAPP_CLOUD_EXPAND_KEY")){
			define("WHATSAPP_CLOUD_EXPAND_KEY", "o3KW1hIzDo9qkv3LFvFD");
		}
		
		$key = $this->request->get["key"]??"";
		
		if($key !== WHATSAPP_CLOUD_EXPAND_KEY)
			$this->redirect($this->url->link('module/whatsapp_cloud'));
		
		
		$this->load->model('module/whatsapp_cloud/waba');
		
		$whatsapp_cloud_sandbox_status = $this->request->get["whatsapp_cloud_sandbox_status"] ?? "VERIFIED_ACCOUNT";
		
		$data = [
					'whatsapp_cloud_business_account_id' => '628449804419498' ,
					'whatsapp_cloud_template_namespaces' => '4a98f78d_b82c_4d60_9ad8_13920a3b2132',
					'whatsapp_cloud_sandbox_status'		 => $whatsapp_cloud_sandbox_status,
					'whatsapp_cloud_phone_selected' 	 => '1',
					'whatsapp_cloud_phone_number_id' 	 => '246268163480925',
					'whatsapp_cloud_phone_verified' 	 => '1',
					'whatsapp_cloud_profile_name' 	 	 => 'Omar Tammam',
					'whatsapp_cloud_phone_registered' 	 => '1',
					'whatsapp_cloud_phone_cc' 	 	 	 => '+20',
					'whatsapp_cloud_phone_number' 	 	 => '1202570360',	
					'whatsapp_cloud_phone_bin' 	 	 	 => '221133',	
					
					//Additional For Ectools request 
					'whatsapp_cloud_business_name' 	  	 => 'expand', 
					'whatsapp_cloud_business_id' 	  	 => '1218527625264654' ,
					'whatsapp_cloud_fb_status' 	  		 => 'VERIFIED_ACCOUNT' 
				];
	
			//add ectools Integration request 
			//step6 : send ECtool Request
		//	$data['whatsapp_cloud_ectools_request_id'] = $this->model_module_whatsapp_cloud_waba->RequestIntegration($data);
			$data['whatsapp_cloud_ectools_request_id'] = '1';
			
			//step7 : update setting 
			$this->_signupDefaultSetting($data);
		
	}
	// should be removed/commented before publishing 
	/*
	public function deleteAllTemplates(){
		$this->load->model('module/whatsapp_cloud/template_message');
		$this->model_module_whatsapp_cloud_template_message->deleteAllTemplates();
	}
	
	//test registering 
	
	public function testRegistering(){
		//$waba_id = $this->config->get("whatsapp_cloud_business_account_id");
		//var_dump($waba_id);die();
		
		$this->load->model('module/whatsapp_cloud/waba');
		
		$backup_result_json = '{"settings":{"data":"V0FCSVoCAAW+G\/pNxW\/8vzuteDxpVlOEg1kvn8lW\/ZWiZ5MxV3\/pOZFEHdjHAsBgMNkDfLrV0Iq5zrhlaPpRMM2WC4rqYQVZ7AGJU8m+nMRARto66P\/Oku4lvb3OTXxNvshdGz\/uiHq4ZUUlI3OG9IbxcUcgI4oC6wHuMD21bJuW4JUnfxGPDma6jXM+oP1zhIiloBb76tTTlkKxQRyXGDcjU4AY8Vw6IBfhOEURxruMzQSZYEPquwXLFDz4YHIRgkBdbPwwomg38QdR\/ybGMEOR2kX7jALFageyzB7y0lenWkWGnkhcGIJQJlLgmjhjpKasp3NNkPB\/V0SzhYtew0LmFtj42HVBT1OH1Eg2ZUO5WupSUPrM9mUOnp2ee6+UI7m509iMxeUvMLYUxxpM9tix0fERdJL8PT+uE\/GQfMUDWRoKaeVksUL4KUmqyxcWrZ4bkDlH8IIL9NsXrf0C52\/OLHCxOj+SoE5xvAagtBCRrP8gwXU8vMeRfKzTOFXnd7M7txgrE2ZrC8s\/epYzD6LIAR9EvwMnHBf70Nju8OiEUdQ77pIpDm5RB\/YOsapjxbVuBuTNnWEmVglmoA+Oh2J\/xJZWVOCArQUzyyKyANHOgHW3bfrHCBxhj58+l+mp\/r\/4a7ai\/qaTxWoR3YqCxrdUATiT0seaNGJpNRBVBTFh4OFu\/Dpk9WtnIMBsMi\/QTpvYEQYzzBhrmG2ia5aFO8tWZCkJQHfr9mgto1SyFJfu7ZAvqnBSnak8iuqMp6zAzBROPOiG45G0JacRHCH6\/v3xxLOSSZ7URQvHGLpbpOmgohu76FP\/PUNQeW5jKru\/+ee5P47zz5LYBjqx7erkEelLv\/Q\/zryHu3B2FsPXkDdlX6DPXLQ11bn+xqnwmRV+qdyEKnNUTytFAvxYoo+eREPJS32MlyQ8I\/ZzHUjawkwPkRoAyaiZ1lzzPLZM8Zd0DxJNP2Vyqn6wLFjb0esCyIZfWqIulRBwwXLtNEHggkNOo9BwKVb6z5\/UaqaoRFR9yJlhMbEo0eIH98HouHCALn5nAEL8S8eJn9YVg7h6H\/0CwCx\/jLsE9PoME1MOWVX+YAWT2udFkwbLPAACrToHrgOx642iH48qD99GfDL8yU0e3ziX+jtENJbn+WLmrVk5oX8cGwv+nNhuiWcaoJTKTy3itfV5F+9y3ipmhpIq9La4DEWP6TYt8X1H7bFSU9EmRIC8+k3FdN\/MG1oDRCqNDq4bqp48Rt6kwvVcczIW\/grboGfilMTiu6VRcHHbq4qsRyLsRNYUEGPnXz8Wj\/z0SG2zUUaEuJGTlJYPF+t0\/ARSwYGsIGx4q+F\/NE4jMlNmROwxTmF5io4IfofuYSjLLiB04NOSWpKE90cVbQpSWCJg2zgUgyHsMafedARVOrYWwuAIyIlQVgq3polwo24DfjecPt2Q6eFRQaXWRdZU0QYditXRBfonkgzoLthJ\/yKBaZBk\/OFf9fwzERbGAMVR0syJBfmWjuO\/yUUGBgo97LfbaSjpO7im0O0cmyxbTjAfYBFmpaG\/YD8c4itRiBOVCDk5V1bjR4Qt2cDCug9Dd4H2nz8rfKbIEHyjb7xVkrKKoSHhHE+4bgd3rqXLKVmtF52iYnnOV+ZRxqNw\/rsE6DDqkcEK2kwd65NlxEzAwHZJwovKVw3+dnhTPkzdEpyD00OqSPfp7086ykf5mjzBf\/gYqFNLvgciJXmtaQS46dhzeToYg9SEsEABEHsgqh08zXUkOXqdgF\/wYjSPw9ymvO4YafpasYx0Rkkg4iFl2InF2eVNISmXt53BiVrBt2jbmuneNIOSiGHNfDHZZ4UuNjTi4R5KcKV+Hizxnaqrwqqld2KBMDUT666sRB\/rWs6hoCsxjDm5QWO+RHoRcO9GoBXmzfojisntAUpsYeXOXpqYGAsP4YJ7ngrN7OWgIrBAquRj24cxS76aAlrwOqITTtKEvs3vyAds+JvMm0XmqFWgmSdM+OufZtPMp0F9\/AodCeYEiQ69bi7ZeVNfSXgWBK8p\/pHC2zG0AIIbnvnuElIYXYGxLZ56mSshnsMI4cCcf03mRkh8Y\/MMirey2MLqZg8KFK\/vFuufXMZWvOoOOZiZASUBISATbvrr+4sphotAVV7mSXG5PJMdWM3DHW5ifKCzQCK0MK10SmQv0ewZOJq6zMmPH7yZwD58HZDHpSobD4\/How=="},"meta":{"api_status":"stable","version":"2.37.1"}} ';
		
		$backup_data = json_decode($backup_result_json);
		$backup_data = $backup_data->settings->data;
		$waba_id = '628449804419498'; 
		$phone_number_id = '246268163480925';
		$bin= "221133";
		//$backup_data = "";
		$backup=["data"=>$backup_data, "password" => EC_WHATSAPP["BACKUP_PASSWORD"]];
		//$result = $this->model_module_whatsapp_cloud_waba->requestCode($phone_number_id);
		
		$result = $this->model_module_whatsapp_cloud_waba->wabaRegister($phone_number_id,$bin,$backup);
		var_dump($result);
	}*/


	/**
	 * sendFbEventRequest send event to FB API
	 */
	function sendFbEventRequest(){
		if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else{
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		$access_token = FB_PIXEL_TOKEN;
		$pixel_id = FB_PIXEL_ID;
		$api = Api::init(null, null, $access_token);
		$api->setLogger(new CurlLogger());

		$user_data = (new UserData())
			->setEmails($this->config->get('config_email'))
			->setClientIpAddress($ip)
			->setClientUserAgent($_SERVER['HTTP_USER_AGENT']);

		$event = (new Event())
			->setEventName('WhatsAppCloudInstalled')
			->setEventTime(time())
			->setEventSourceUrl('https://'.$_SERVER['HTTP_HOST'])
			->setUserData($user_data)
			//->setCustomData($data['custom_data'])
			//->setEventId($data['event_id'])
			->setActionSource(ActionSource::WEBSITE);

		$events = [$event];

		try{
			$request = (new EventRequest($pixel_id))
				//->setTestEventCode('TEST77763') // this is used for test events in https://business.facebook.com/events_manager2/list/pixel/251971669041254/test_events
				->setEvents($events);
			$response = $request->execute();
		} catch (\Exception $ex) {
			return false;
		}
	}

}

?>
