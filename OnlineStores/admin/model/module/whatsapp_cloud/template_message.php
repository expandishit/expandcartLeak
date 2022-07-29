<?php


class ModelModuleWhatsappCloudTemplateMessage extends Model {


	private static  $messagesTable 		    = DB_PREFIX . 'whatsapp_cloud_messages',
					$messageTemplatesTable  = DB_PREFIX . 'whatsapp_cloud_message_templates',	
					$saveSentTemplates 		= true,
					$configPrefix 			= 'whatsapp_cloud_',
					$adminToken 			= EC_WHATSAPP['ADMIN_TOKEN'];

	
	//========================== Graph API methods ================================//

	
	/**
	 * Send Template  message 
	 *
	 * @param String $to 
	 * @param String $template_name  
	 * @param language array 
	 * @param components array 
	 *
	 * @return array "array of objects"
	 */
	public function sendTemplateMessage(string $to,string $template_name,array $language,array $components):array{
		
		$path = ONLINE_STORES_PATH . 'OnlineStores/admin/';
						
		$this->load->model('module/whatsapp_cloud/message',false,$path);
		
		$message_data							= [];
		$message_data['type']					= "template";
		$message_data['template']['name']		= $template_name;
		$message_data['template']['language']	= $language;
		$message_data['template']['components']	= $components;
		
		//namespace depreciated at cloud API request 

		$responses = [];
		
		if(is_array($to)){
			
			$numbers = $to ; 
			
			foreach ($numbers as $number){
				$responses[] = $this->model_module_whatsapp_cloud_message->sendMessage($number,$message_data);
			}
			
		}else {
			$responses[] = $this->model_module_whatsapp_cloud_message->sendMessage($to,$message_data);
		}
		
		return $responses;
	}
	
	/*
	 * currently not used 
	 * getTemplateMessages 
	 *
	 * @return object $response_data | false
	 *
	 */
	public function getTemplateMessages():array {
		
		$_url 		 = '/'.$this->config->get('whatsapp_cloud_business_account_id') .'/message_templates';
		$_url 		.= '?limit=100&access_token='.self::$adminToken;
		$headers	 = [
						"Content-Type: application/json",
						];
						
		$response = json_decode(WhatappCloudHelper::sendGraphApiRequest($_url,'GET',$headers, []));
		
		
		if(isset($response->data))
			return $response->data;

		return [];
	}

	/*
	 * delete template message
	 *
	 * @parm string $template_name 
	 *
	 * @return object $response
	 *
	 */
	public function deleteTemplate(string $template_name){
		

		$_url = '/'.$this->config->get('whatsapp_cloud_business_account_id') .'/message_templates';
		$_url .= '?access_token='.self::$adminToken;
        
		$headers = [
					"Content-Type: application/json",
					];
		$data = [
				'name'=>$template_name
				];
				
		$response = json_decode(WhatappCloudHelper::sendGraphApiRequest($_url,'DELETE',$headers,$data));

		return $response;
	}

	/*
	 * delete observer template message
	 *
	 * @parm string $observer_config_name 
	 * @parm int 	$template_index 
	 *
	 * @return array $observer_templates
	 *
	 */
	public function deleteObserverTemplate(string $observer_config_name ,int $template_index = 0){
		
		$observer_templates  = $this->config->get($observer_config_name);
		$template 			 = $observer_templates[$template_index]??[];
		
		if(!empty($template) && isset($template["name"])){
			
			$this->deleteTemplate($template["name"]);
			
		}
		if(isset($observer_templates[$template_index])){
			
			unset($observer_templates[$template_index]);
			$observer_templates = array_values($observer_templates); // 'reindex' array
			
		}
		
		return $observer_templates;
	}

	/*
	 * @parm array $template 
	 *
	 * @return object $response
	 *
	 */
	public function requestTemplate(array $template){
		
		$_url  = '/'.$this->config->get('whatsapp_cloud_business_account_id') .'/message_templates';
		$_url .= '?access_token='.self::$adminToken;
		
        $headers = [
					"Content-Type: application/json",
					];
					
		$data = [
				'name'		 => $template["name"]		?? "",
				'language'	 => $template["language"]	?? "",
				'components' => $template["components"]	?? [],
				'category'	 => $template["category"]	?? "ALERT_UPDATE"
				];
					
		$response = json_decode(WhatappCloudHelper::sendGraphApiRequest($_url,'POST',$headers,$data));
		
		return $response;
	}

	/*
	 * @parm string $template_name 
	 *
	 * @return $response_data object | null
	 *
	 */
	public function getTemplate(string $template_name){
		
		$admin_token = self::$adminToken;

		$_url  = '/'.$this->config->get('whatsapp_cloud_business_account_id') .'/message_templates';
		$_url .= '?name=' . $template_name . '&access_token='.$admin_token;
        
		$headers = [
					"Content-Type: application/json",
					];
		
		$result		= WhatappCloudHelper::sendGraphApiRequest($_url,'GET',$headers,$data);
		$response 	= json_decode($result);
		
		return $response->data ?? null;
	}

	/*
	 * @parm  array $data 
	 *
	 * @return 	
	 *
	 */
	public function sendObserverTemplate(array $data){
		
		//WhatappCloudHelper::clientLog(' should sent template :' . $template_name . ' template_to ' . $template_to);

		$observer_template = $data['observer_template']	?? [];
		
		if(empty($data['observer_template'])){
			
			if(!empty($data['observer_key']) && !empty($data['observer_name'])){
				
				$observer_template  = $this->getTemplateFromObserver($data['observer_key'], self::$configPrefix. $data['observer_name']);	
			}
		}
		
		//if not template for this status at  do nothing 
				
		if(empty($observer_template)){
			WhatappCloudHelper::clientLog('[whatsapp] no template exists at observer with this status :'. $order_info['check_if_failed']);//for test 
			return;
		}
		
		$handled_data = [
						"observer_template"		=> $data['observer_template']		?? [],
						"template_to" 			=> $data['template_to']				?? "",
						"lang_code"   			=> $data["lang_code"]				?? "",
						"template_variables" 	=> $data["template_variables"]		?? "",
						"receiver_country_code"	=> $data['receiver_country_code']	?? "",
					];
				
		return  $this->sendTemplate('observer',$handled_data);
	}

	/*
	 * @parm  array $data 
	 *
	 * @return 	
	 *
	 */
	public function sendDefaultTemplate(array $data){

		$handled_data = [
						"template_key"   		=> $data["template_key"]			?? "",
						"template_to" 			=> $data['template_to']				?? "",
						"lang_code"   			=> $data["lang_code"]				?? "",
						"template_variables" 	=> $data["template_variables"]		?? "",
						"receiver_country_code"	=> $data['receiver_country_code']	?? "",
						];
						
		//WhatappCloudHelper::clientLog(' should sent template :' . json_encode($data));
			
		return  $this->sendTemplate('default',$handled_data);
		
	}
	
	/*
	 * @parm string $type  <default|observer>
	 * @parm array  $data 
	 *
	 * @return 	
	 *
	 */
	public function sendTemplate(string $type,array $data){
		
		//WhatappCloudHelper::clientLog(__function__ . ' type: ' . $type . ' data: ' . json_encode($data));
		$account_connected 		 = (int)$this->config->get('whatsapp_cloud_account_connected');
		
		if(!$account_connected)
			return false;
		
		$phonenumbers 			= $data['template_to']			 ?? "";
		$lang_code   			= $data["lang_code"]			 ?? "";
		$template_key   		= $data["template_key"]			 ?? "";
		$template_variables 	= $data["template_variables"]	 ?? "";
		$receiver_country_code	= $data['receiver_country_code'] ?? "";
		$observer_template		= $data['observer_template'] 	 ?? [];
		$template_language		= [
									"policy" => "deterministic",
									"code"	 => $lang_code
								];
								
		if($type == 'observer' && !empty($observer_template))
		{
			$template_name 	= $observer_template['name'];
			
			if(!$template_name){
				WhatappCloudHelper::developerLog('[whatsapp] this template not contain name index! template : '.json_encode($observer_template));
				return false;
			}
			$components 	= $this->observerTemplateComponents([
																'lang_code'			 => $lang_code,
																'observer_template'  => $observer_template,
																'template_variables' => $template_variables
																]);
																
			$sent_template	= $observer_template["data"][$lang_code] ?? [];
		} 
		else if ($type == 'default' && !empty($template_key))
		{
			$template_name 	= $this->config->get(self::$configPrefix.$template_key.'_name');
			
			if(!$template_name){
				WhatappCloudHelper::developerLog('[whatsapp] config ['.self::$configPrefix.$template_key.'_name] not exists or empty');//for debuging 
					return false;
			}
				
			$template 		= $this->config->get(self::$configPrefix.$template_key);
			$sent_template	= $template[$lang_code] ?? [];
			
			$components  	= $this->defaultTemplateComponents([
																'lang_code'			 => $lang_code,
																'template_key' 	     => $template_key,
																'template_variables' => $template_variables
																]);
			
		}else {
		
			return false;
		}
		
	
		$additional_info		= [
										'country_code' 		 => $receiver_country_code,
										'sent_template' 	 => $sent_template,
										'template_variables' => $template_variables
									];		
		
		/*
		 * the problem is that using national number not works 
		 * if te number not contains + we will generate a international number to it 
		 * using the shipping cpuntry code 
		 */
		 
		$phonenumbers = is_array($phonenumbers) ? $phonenumbers : [$phonenumbers];
		
		//WhatappCloudHelper::clientLog(' should sent template :' . $template_name . ' to ' . json_encode($phonenumbers) );
		
		foreach ($phonenumbers as $template_to){
			if((substr($template_to, 0, 1) !== '+') && (substr($template_to, 0, 2) !== '00') && !empty($additional_info["country_code"]??""))
				$template_to = WhatappCloudHelper::toInternationalPhoneNumber($template_to,$additional_info["country_code"]);
			
			$response  = $this->sendTemplateMessage($template_to,$template_name,$template_language,$components);
			$response  = $response[0] ?? [];
			
			//we will trigger later wethear we will save sent templates or not as its have some differences may lead to problems 
			
			if(property_exists($response,"contacts") && !empty($response->contacts)){
				
				$to_waid 			= $response->contacts[0]->wa_id;
				$fb_message_id 		= $response->messages[0]->id;
				$sent_template		= $additional_info["sent_template"] ?? [];
				$template_variables = $additional_info['template_variables'];
				$rendered_template  = $this->renderTemplateMessage($sent_template,$template_variables);
				
				$this->saveSentTemplate($to_waid,$rendered_template,$fb_message_id);//TO:DO | should un-comment this line 
			}
	
		}
		
		return $response ;
	}

	//========================== Helper methods ================================//

	/*
	 * variables names at template should be same of indexs name at values_array
	 *
	 * @parm 	string  $template_text 
	 * @parm 	array   $values_array 
	 *
	 * @return 	array $whatsapp_component_parameters
	 *
	 */
	public function getWhatsappVariables(string $template_text,array $values_array):array{
		
		preg_match_all('/{(\w+)}/', $template_text, $matches);
		$template_variables =$matches[1];
		
		$whatsapp_component_parameters = [];
		 foreach($template_variables as $template_variable ){
			 //return $values_array[$template_variable];
			 //whatsapp doesnt accept null value so if variable name not found will replaced with ' ... '
			 $whatsapp_component_parameters[] = [
								"type"=> "text",
								"text"=> $values_array[$template_variable] ?? "..."  
							];
		 }
 
        return $whatsapp_component_parameters;
	}

	/*
	 * @parm 	int  	$order_status_id 
	 * @parm 	string  $observer_type 
	 *
	 * @return 	array $template
	 *
	 */
	public function getTemplateFromObserver(int $order_status_id,string  $observer_type):array {
        $template = [];
        $observers = $this->config->get($observer_type);

        if($observers){
            foreach ($observers as $observer) {
                if($observer['key'] == $order_status_id){
                    $template = $observer;
                }
            }
        }
		
        return $template;
    }

	/*
	 * @parm 	array  $data 
	 *
	 * @return 	array $components
	 *
	 */
	public function defaultTemplateComponents(array $data) : array {
		
		if(empty($data['template_key']??"")){
			return [];
		}
		
		return $this->_templateComponents($data);
	}
	
	/*
	 * @parm 	array  $data 
	 *
	 * @return 	array $components
	 *
	 */
	public function observerTemplateComponents(array $data) : array {
		
		if(empty($data['observer_template']??[])) 
			return [];
		
		return $this->_templateComponents($data,'observer');
	}
	
	/*
	 * @parm 	array  $data 
	 * @parm 	string $type   <default|observer>
	 *
	 * @return 	array $components
	 *
	 */
	private function _templateComponents(array $data,string $type='default') : array {
		
		$template_key 		= $data['template_key'] 	  ?? "";
		$template_variables = $data['template_variables'] ?? [];
		$lang_code 			= $data['lang_code'] 		  ??  "";
		
		if(empty($lang_code)){
			return [];
		}
		if($type == 'default'){
			
			if(empty($template_key))
				return [];
			
			$template 		 	= $this->config->get('whatsapp_cloud_'.$template_key);
		}else {
			
			//observer
			$observer_template 	= $data['observer_template'] ?? [];
			$template		   	= $data['observer_template']["data"] ?? [];
			
		}
		
		$header_template		= $template[$lang_code]['HEADER']	?? "";
		$body_template			= $template[$lang_code]['BODY']		?? "";	
		$header_parameters 		= $this->getWhatsappVariables( $header_template,$template_variables);
		$body_parameters   		= $this->getWhatsappVariables( $body_template,$template_variables);
		
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
		
		return $components ;
	}
	
	/*
	 *
	 * @parm 	string $template_key 
	 * @parm 	string $lang_code 
	 *
	 * @return 	array $template
	 *
	 */
	public function defaultTemplate(string $template_key,string $lang_code) : array {
		$msg_prefix = $data['msg_prefix'] ?? "whatsapp_cloud_msg";
		
		$header 	= $this->config->get($msg_prefix.'_'.$template_key.'_'.$lang_code.'_HEADER');
		$body   	= $this->config->get($msg_prefix.'_'.$template_key.'_'.$lang_code.'_BODY');
		$footer   	= $this->config->get($msg_prefix.'_'.$template_key.'_'.$lang_code.'_FOOTER');
				
		$template =  [
						"header"	=> $header,
						"body" 		=> $body,
						"footer" 	=> $footer
					];
					
		return $template;			
	}
	
	/*
	 * Replace our variables with text with a render text -- order #{order_id} => order #20543
	 *
	 * @parm 	string $our_format 
	 * @parm 	array  $variables 
	 *
	 * @return 	array $rendered_text
	 *
	 */
	public function renderTemplateMessage(array $template,array $variables) : array {
		
		$header = $this->_renderMessageText($template['HEADER']??"",$variables);
		$body   = $this->_renderMessageText($template['BODY']??"",$variables);
		$footer	= $template['FOOTER']??""; //footer not supports variables at current time 
		
		$rendered_template =  [
								"HEADER"	=> $header,
								"BODY" 		=> $body,
								"FOOTER" 	=> $footer
								];
		
		return $rendered_template;
	}
	
	/*
	 * Replace our variables with text with a render text -- order #{order_id} => order #20543
	 *
	 * @parm 	string $our_format 
	 * @parm 	array  $variables 
	 *
	 * @return 	string $rendered_text
	 *
	 */
	public function _renderMessageText(string $our_format,array $variables) : string {

		preg_match_all('/{(\w+)}/', $our_format, $matches);

		$rendered_text = $our_format;
		
		foreach ($matches[0] as $index => $var_text) {
			
			$var_index 		= trim($var_text,"{}");			
			$rendered_text 	= str_replace($var_text, $variables[$var_index]??"" , $rendered_text);
		
		}

		return $rendered_text;
	}
	
	/*
	 * Replace our variables with facebook parameters identifier {{1}}, {{2}} .. 
	 *
	 * @parm 	string $our_format 
	 *
	 * @return 	string $facebook_format
	 *
	 */
	public function facebookTextFormat(string $our_format) : string {

		preg_match_all('/{(\w+)}/', $our_format, $matches);

		$facebook_format = $our_format;
		$counter = 1;
		foreach ($matches[0] as $index => $var_name) {
			$facebook_format = str_replace($var_name, "{{". $counter ."}}" , $facebook_format);
		  $counter++;
		}

		return $facebook_format;
	}
	
	/*
	 *
	 */
	public function initDefaultTemplates(): array {
		
		$data = [
				"customer_account_registration" => [
					"ar" => [
							 "HEADER"	=> "تم تسجيل الحساب بنجاح",
							 "BODY"		=> "مرحبا {firstname} {lastname} \n شكرا لتسجيلك معنا ."
							],
					"en" => [
							 "HEADER"	=> "account Register successfully",
							 "BODY"		=> "Hi {firstname} {lastname} , \n Thanks For Your Register."
							]
				],
				"customer_checkout" => [
					"ar" => [
							 "HEADER"	=> "تآكيد الطلب",
							 "BODY"		=> "اهلا بحضرتك \n تم تأكيد طلبك # {order_id}  \n تاريخ التسليم المتوقع : {order_date}" 
							],
					"en" => [
							"HEADER"	=> "Order Confirmation",
							"BODY"		=> "Hi Sir,\n Your Order# {order_id} is Confirmed , expected date : {order_date}" 
							]
					],
				"customer_phone_confirm" => [
					"ar" => [
							 "HEADER"	=> "تاكيد الموبايل",
							 "BODY"		=> "مرحبا {firstname} \n رمز تاكيد الموبايل الخاص بك   {confirm_code}" 
							],
					"en" => [
							 "HEADER"	=> "phone confirmation",
							 "BODY"		=> "Hi  {firstname} , \n your confirmation code is :  {confirm_code}" 
							]
					],
				"admin_account_registration" => [
					"ar" => [
							 "HEADER"	=> "تم تسجيل عميل جديد",
							 "BODY"		=> "مرحبا , \n تم تسجيل عميل جديد  :  {firstname} {lastname} \n رقم الموبايل  : {telephone}" 
							],
					"en" => [
							 "HEADER"	=> "New Customer",
							 "BODY"		=> "Hi Sir , \n New Customer Registered :  {firstname} {lastname} \n phone number   {telephone}"
							]
					],
				"admin_checkout" => [
					"ar" => [
							 "HEADER"	=> "طلب جديد",
							 "BODY"		=> "مرحبا, \n هناك طلب جديد # {order_id}" 
							],
					"en" => [
							 "HEADER" => "New Order Placed",
							 "BODY"   => "Hi Sir ,  \n New Order Placed  \n # {order_id}"
							 ]
					]
				];
		
		
		
		
		/* 
		 it seems wierd but no warry here about time complicity,the complicity of this is just O 
		 as the number of templates and its component is static 
		 */
		$data_formatted = [] ;
		foreach ($data as $template_key => $template ){
			
			$data_formatted[$template_key] = [];
			
			foreach ($template as $lang_code => $components ){
				
				foreach($components as $component => $message){
					$data_formatted[$template_key]['whatsapp_cloud_msg_'.$template_key.'_'. $lang_code . '_'.$component] = $message;
				}
			}
		}
		
		return $data_formatted; 
	}
	
	//========================== internal DB Methods ================================//

	/*
	 * @parm string $to 
	 * $parm array  $rendered_template
	 * $parm string $fb_message_id
	 *
	 * @return void 
	 */
	private function saveSentTemplate(string $to,array $rendered_template,string $fb_message_id="" ){
		
		$path = ONLINE_STORES_PATH . 'OnlineStores/admin/';
						
		$this->load->model('module/whatsapp_cloud/chat',false,$path);
		$chat_id  = $this->model_module_whatsapp_cloud_chat
						->insertUpdateChat(["phone_number" => $to ]);
		
		if(!$chat_id)
			return false ;
		
		$this->load->model('module/whatsapp_cloud/message',false,$path);
		$message_id = $this->model_module_whatsapp_cloud_message
						   ->insertMessage([
											"chat_id"		=> $chat_id ,
											"type"			=> "template",
											"fb_message_id"	=> $fb_message_id,
											"from_me"		=> 1
										  ]);
		if(!$message_id)
				return false;
					
		return  $this->insertTemplateMessage([
											"template"		=> $rendered_template ,
											"message_id"	=> $message_id,
											"fb_message_id"	=> $fb_message_id
										  ]);
		
	}

	/*
	 * @parm array $data 
	 *
	 * @return int 
	 * 
	 */
	public function insertTemplateMessage(array $data=[]){

		$template = isset($data['template']) ? json_encode($data['template']) : "";
		
		$query   = [];
        $query[] = 'INSERT INTO `' . self::$messageTemplatesTable . '` SET ';
		$query[] = ' `template`   	  = "' . $this->db->escape($template) 					. '",';
		$query[] = ' `message_id`  	  = "' . $this->db->escape($data['message_id']??'') . '",';	
		$query[] = ' `created_at`  	  = NOW()';
		
		$this->db->query(implode(' ', $query));
		
		return $this->db->getLastId();
	}
	
	/*
	 * @return void 
	 *
	 */
	public function createMessageTemplatesTable(){
		$sql = "CREATE TABLE IF NOT EXISTS ". self::$messageTemplatesTable . " (
					  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					  `template` json DEFAULT NULL,
					  `message_id` int(11) UNSIGNED NOT NULL,
					  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
					  `updated_at` timestamp NULL DEFAULT NULL,
					  PRIMARY KEY (`id`),
					  FOREIGN KEY (`message_id`) REFERENCES " .self::$messagesTable ."(`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
		 
		 $this->db->query($sql);
	}

	/*
	 *
	 * @return void 
	 *
	 */
	public function dropMessageTemplatesTable(){
		 $this->db->query("DROP TABLE  IF EXISTS ". self::$messageTemplatesTable);
	}
}
?>
