<?php
class ModelModuleWhatsapp extends Model {

	//----------- part1 : whatsApp API client ----------/
	//this part implementation at whatsapp_helper

	//----------- part2 : Business Manager [GRAPH APIs] ----------/
	//graph APIS
	private $APIGraph_URL = 'https://graph.facebook.com';
	private $graphApi_version 	= 'v11.0';
	private $business_id 	  	= '697248897325946';
	private $credit_id 	  		= EC_WHATSAPP['CREDIT_ID'];
	private $admin_user_id 		= '104272995252685'; 	//WhatsApp notifications user Admin
	private $employee_user_id 	= '107358521147997'; 	//WhatsApp User Employee
	private $facebook_appToken 	= EC_WHATSAPP['ADMIN_TOKEN'];
	private $employee_appToken 	= EC_WHATSAPP['EMPLOYEE_TOKEN'];

	private $waba_currency 		= 'USD'; 	
	/**
     * the payment methods table name
     *
     * @var string
     */
    private $integrationRequestTable = DB_PREFIX . 'whatsapp_requests';
	//----------- part3 : client DBS --------------/
	//DB server
	private static $messagesDB = 'messageStore';
	private static $contactsDB = 'contactStore';
	private static $messConn = false;
	private static $CONTConn = false;



	//---------------------------------# graph API requests #------------------------//
	public function getTemplateMessage (){

		if(!$this->facebook_appToken){
			return false;
		}
		$facebook_appToken = $this->facebook_appToken;
		//the default lmitit = 25 template [25 x languages num ] [template not object as template has many objects [for languages] "
		$_url = '/message_templates?limit=100&access_token='.$facebook_appToken;
        $headers = [];
		$headers[]="Content-Type: application/json";
		$response = json_decode($this->sendGraphApiRequest($_url,'GET',$headers, []));
		//$response =
		//var_dump($response->error);die();
		if(isset($response->error)){
			return false;
		}
		//$template_messages = [];
		//$template_messages_names = [];
		//if(isset($response->data)){
		//	foreach($response->data as $template_message){
		//		$template_messages[$template_message->name][$template_message->language]=$template_message;
			//	$template_messages[$template_message->name]['category']=$template_message->category;

				/*if(!in_array($template_message->name,$template_messages_names)){
				//$template_messages_names[]=$template_message->name;
				}*/
		//	}
		//}
		//var_dump($template_messages_names);
		//var_dump($template_messages);
		//die();
		if(isset($response->data)){
			return $response->data;
		}

		return false;
	}

	//delete template message
	public function deleteTemplate($template_name){
		if(!$this->facebook_appToken){
			return false;
		}
		$facebook_appToken = $this->facebook_appToken;

		$_url = '/message_templates?access_token='.$facebook_appToken;
        $headers = [];
		$headers[]="Content-Type: application/json";
		$data = array('name'=>$template_name);
		$response = json_decode($this->sendGraphApiRequest($_url,'DELETE',$headers,$data));

		if(isset($response->error)){
			return false;
		}

		if(isset($response)){
			return $response;
		}

		return false;
	}

	//request template message
	public function requestTemplate($template_name,$lang,$components,$category='ALERT_UPDATE'){
		if(!$this->facebook_appToken){
			return false;
		}
		$facebook_appToken = $this->facebook_appToken;

		$_url = '/message_templates?access_token='.$facebook_appToken;
        $headers = [];
		$headers[]="Content-Type: application/json";
		$data = array(
					'name'=>$template_name,
					'language'=>$lang,
					'components'=>$components,
					'category'=>$category
					);
		$response = json_decode($this->sendGraphApiRequest($_url,'POST',$headers,$data));
return $response;
		if(isset($response->error)){
			return false;
		}

		if(isset($response)){
			return $response;
		}

		return false;
	}

	//------- Used in embedded SignUp -----------/

	//debug token 
	public function debugToken($token){
		if(!$this->facebook_appToken){
			return false;
		}
		$facebook_appToken 	= $this->facebook_appToken;
        $headers 		 	= [];
		$headers[]		 	= 'Content-Type: application/json';
		$_url 			 	= '/debug_token?input_token=' . $token . '&access_token='.$facebook_appToken;
		$response 		 	= json_decode($this->sendPureGraphApiRequest($_url,'GET',$headers,[]));
		$granular_scopes 	= $response->data->granular_scopes;
		$data 	  		 	= [];
		foreach($granular_scopes as $scope ){
			$data[$scope->scope]=$scope->target_ids;
		}
		
		return $data;
	}

	//shared WABAs 
	public function sharedWABAs(){
		if(!$this->facebook_appToken){
			return false;
		}
		$facebook_appToken = $this->facebook_appToken;
		$_url = '/' . $this->business_id . '/client_whatsapp_business_accounts?access_token='.$facebook_appToken.'&sort=creation_time_descending&limit=1000';
       
	    $headers = [];
		$headers[]="Content-Type: application/json";
		$headers[]="Authorization: Bearer ".$facebook_appToken;

		$response = json_decode($this->sendPureGraphApiRequest($_url,'GET',$headers,[]));
		return $response;
	}
	
	//owned WABAs 
	public function ownedWABAs(){
		if(!$this->facebook_appToken){
			return false;
		}
		$facebook_appToken = $this->facebook_appToken;
		$_url = '/' . $this->business_id . '/owned_whatsapp_business_accounts';
        $headers = [];
		$headers[]="Content-Type: application/json";
		$headers[]="Authorization: Bearer ".$facebook_appToken;

		$response = json_decode($this->sendPureGraphApiRequest($_url,'GET',$headers,[]));
		return $response;
	}
	
	//system users ids 
	public function systemUsers(){
		if(!$this->facebook_appToken){
			return false;
		}
		$facebook_appToken = $this->facebook_appToken;
		$_url = '/' . $this->business_id . '/system_users';
        $headers = [];
		$headers[]="Content-Type: application/json";
		$headers[]="Authorization: Bearer ".$facebook_appToken;

		$response = json_decode($this->sendPureGraphApiRequest($_url,'GET',$headers,[]));
		$data 	  = $response? $response->data : []; 
		return $data;
	}
	
	//assign system user to WABA_ID
	public function assignUserToWABA ($WABA_ID,$type,$tasks){
		if(!$this->facebook_appToken){
			return false;
		}
		
		$user_id = ($type == 'admin')? $this->admin_user_id : $this->employee_user_id;
		
		$facebook_appToken 		=  $this->facebook_appToken ;

		$_url 		= '/' . $WABA_ID . '/assigned_users?user='.$user_id;
		$_url 	   .= "&tasks=['" . implode ( "','", $tasks ) . "']";
		$_url 	   .= "&access_token=".$facebook_appToken;

		$headers	= [];
		$headers[]	= "Authorization: Bearer ".$facebook_appToken;
		$headers[]	= "Accept: */*";
		$response 	= json_decode($this->sendPureGraphApiRequest($_url,'POST',$headers,[]));
		
		return !empty($response->success) && $response->success;
	}
	
	//Fetch Assigned Users of WhatsApp Business Account
	public function fetchAssignWABA ($WABA_ID){
		if(!$this->facebook_appToken){
			return false;
		}
		
		$facebook_appToken 		= $this->facebook_appToken;
		$_url 		= '/' . $WABA_ID . '/assigned_users?business='.$this->business_id;
        $headers	= [];
		$headers[]	= "Authorization: Bearer ".$facebook_appToken;
		$headers[]	= "Accept: */*";
		$response 	= json_decode($this->sendPureGraphApiRequest($_url,'GET',$headers,[]));
		return $response;
	}
	
	//our business line of credit id 
	//
	public function lineOfCredit(){
		if(!$this->facebook_appToken){
			return false;
		}
		$facebook_appToken = $this->employee_appToken;
		$_url = '/' . $this->business_id . '/extendedcredits?fields=id,legal_entity_name';
        $headers = [];
		$headers[]="Content-Type: application/json";
		$headers[]="Authorization: Bearer ".$facebook_appToken;

		$response = json_decode($this->sendPureGraphApiRequest($_url,'GET',$headers,[]));
		$credit_id 	  = $response->data[0]->id ? $response->data[0]->id : false; 
		return $credit_id;
	}

	//share line of credit with client 
	public function shareLineOfCredit ($WABA_ID){
		if(!$this->employee_appToken){
			return false;
		}
		
		$employee_appToken 		= $this->employee_appToken;
		$_url 		= '/' . $this->credit_id . '/whatsapp_credit_sharing_and_attach?waba_id='.$WABA_ID.'&waba_currency='.$this->waba_currency;
        $headers	= [];
		$headers[]	= "Authorization: Bearer ".$employee_appToken;
		$headers[]	= "Accept: */*";
		$response 	= json_decode($this->sendPureGraphApiRequest($_url,'POST',$headers,[]));
		return $response;
	}
	
	// verify share line of credit with client 
	public function verifyShareOfCredit ($allocation_config_id){
		if(!$this->facebook_appToken){
			return false;
		}
		
		$facebook_appToken 		= $this->facebook_appToken;
		$_url 		= $allocation_config_id.'?fields=receiving_credential{id}';
        $headers	= [];
		$headers[]	= "Authorization: Bearer ".$facebook_appToken;
		$headers[]	= "Accept: */*";
		$response 	= json_decode($this->sendPureGraphApiRequest($_url,'GET',$headers,[]));
		return $response;
	}
	
	// Subscribe App to WhatsApp Business Account to receive webhook
	public function subscribeApp ($WABA_ID){
		if(!$this->facebook_appToken){
			return false;
		}
		
		$facebook_appToken 		= $this->facebook_appToken;
		$_url 		= '/' .$WABA_ID.'/subscribed_apps';
        $headers	= [];
		$headers[]	= "Authorization: Bearer ".$facebook_appToken;
		$headers[]	= "Accept: */*";
		$response 	= json_decode($this->sendPureGraphApiRequest($_url,'POST',$headers,[]));
		return $response;
	}
	
	//get WABA Phone numbers 
	public function WABAPhoneNumbers($WABA_ID){
		if(!$this->facebook_appToken){
			return false;
		}
		
		$facebook_appToken 		= $this->facebook_appToken;
		$_url 		= '/' .$WABA_ID.'/phone_numbers?fields=display_phone_number,certificate,name_status';
        $headers	= [];
		$headers[]	= "Authorization: Bearer ".$facebook_appToken;
		$headers[]	= "Accept: */*";
		$response 	= json_decode($this->sendPureGraphApiRequest($_url,'GET',$headers,[]));
		return ($response->data)? $response->data : [];
	}
	
	//get WABA Business info 
	public function WABABusinessInfo($WABA_ID){
		if(!$this->facebook_appToken){
			return false;
		}
		$facebook_appToken = $this->facebook_appToken;
		
		$_url 		= '/' .$WABA_ID.'?fields=owner_business_info,account_review_status&access_token='.$facebook_appToken;
        $headers	= [];
		$headers[]	= "Authorization: Bearer ".$facebook_appToken;
		$headers[]	= "Accept: */*";
		$response 	= json_decode($this->sendPureGraphApiRequest($_url,'GET',$headers,[]));
		return  $response;
	}
	
	
	//------- #Used in embedded SignUp -----------/
	
	
	public function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
	}

	/**
		Replace our variables with facebook parameters identifier {{1}}, {{2}} ..
	*/
	public function facebook_text_format ($our_format){

		preg_match_all('/{(\w+)}/', $our_format, $matches);
		//$parameters =$matches[1];
		$facebook_format = $our_format;
		$counter = 1;

		foreach ($matches[0] as $index => $var_name) {
		$facebook_format = str_replace($var_name, "{{". $counter ."}}" , $facebook_format);
		  $counter++;
		}

		return $facebook_format;
	}

	/**
	 Generating the complete URL Using API_client Host URL & API_version with the passed URL
	*/
	private function GraphApiUrl($path){
		return $this->APIGraph_URL."/".$this->graphApi_version."/" . $this->config->get('whatsapp_business_account_id') . $path;
	}


	//------------------------------# events #----------------------//

	  /**
     *
     */
	//[DONE]
    public function notify_or_not_on_order_status_update($order_info, $data){
        $this->notify_or_not_customer_on_order_update($order_info, $data);
        $this->notify_or_not_store_owner_on_order_update($order_info, $data);
    }

	//[DONE]
	//firing at system\library\mswhats.php
    public function notify_or_not_on_admin_change_seller_status($message)
    {
        if (!WhatsAppPhonenumberFilter::passFilters($message['data']['seller_mobile'], $this->config)){
            return false;
        }

        $msg_type = $message['type'];
       // return $msg_type;
        $whatsapp_template = $this->get_template_from_observer($msg_type, 'whatsapp_cfg_seller_observers');
        $template_to = WhatsAppPhonenumberFilter::rewritePhoneNumber($message['data']['seller_mobile'], $this->config);
        $msg_salt 		= $this->config->get('whats_cfg_msg_salt');
		$lang_code 		= $this->language->get('code');
		//return	 $whatsapp_template;
		//TO:DO need to check that order_status_id is passed at calling place
		$template_name = 'whatsapp_cfg_seller_observers_'.$msg_type.'_'.$msg_salt;
		$template_language	= [
								"policy"=>"deterministic",
								"code"=> $lang_code
								];

		$template_header = $whatsapp_template['header_'.$lang_code];
		$template_body   = $whatsapp_template['body_'.$lang_code];

        $values_array = $message['data'];
		//$find = array('{seller_email}' , '{seller_firstname}', '{seller_lastname}', '{seller_nickname}');
       // $smshare_sms_template = str_replace($find, $message['data'], $smshare_sms_template);
		$WhatAppCommons = new WhatAppCommons($this->config);
		$header_parameters = $WhatAppCommons->get_whatsapp_variables($template_header,$values_array);
		$body_parameters   = $WhatAppCommons->get_whatsapp_variables($template_body,$values_array);
		//return $body_parameters;
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
        return $res;
    }

    /**
     *
     */
	 //[DONE]
    public function notify_or_not_customer_on_order_update($order_info, $data){

        //Optin: Allow or not sms sending to customer
        //hook_d2341

        if(!isset($data['notify_by_whatsapp']) || !$data['notify_by_whatsapp']) {
            if (WhatAppCommons::log($this->config)) $this->log->write("[whatsapp] Do not notify customer on order update because notify is false. Not checked or not submitted. Aborting!");
            return;
        }

        if(!WhatsAppPhonenumberFilter::passFilters($order_info['telephone'], $this->config)){
            return;
        }

        /*
         * Get all observers
         */
        $whatsapp_template = $this->get_template_from_observer($data['order_status_id'], 'whatsapp_cfg_odr_observers');


		//prepare data
		//The order total
        $this->load->model('sale/order');
		$order_id = $order_info['order_id'];
		$WhatAppCommons = new WhatAppCommons($this->config);
        $order_total = $WhatAppCommons->doGetOrderTotal($this->model_sale_order->getOrderTotals($order_id),$this->config);

        if(!empty($order_info['order_status_id']))
        {
         $order_status_query = $this->db->query("SELECT name FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_info['order_status_id'] . "' AND language_id = '" . (int)$order_info['language_id'] . "'");
         $order_info['status_name'] = $order_status_query->row['name'];
        }
        //RETURN  $whatsapp_template;
        //$sms_body = $this->merge_template($whatsapp_template, $order_info, $data);
        $template_to   = WhatsAppPhonenumberFilter::rewritePhoneNumber($order_info['telephone'], $this->config);
        $msg_salt 		= $this->config->get('whats_cfg_msg_salt');
		$lang_code 		= $this->language->get('code');

		$template_name = 'whatsapp_cfg_odr_observers_'.$data['order_status_id'].'_'.$msg_salt;
		$template_language	= [
								"policy"=>"deterministic",
								"code"=> $lang_code
								];

		$template_header = $whatsapp_template['header_'.$lang_code];
		$template_body   = $whatsapp_template['body_'.$lang_code];

        $values_array = array(
			'comment' => strip_tags(html_entity_decode($data['comment'], ENT_QUOTES, 'UTF-8')),
			'firstname'          => $order_info['firstname'],
            'lastname'           => $order_info['lastname'],
            'order_id'           => isset($order_info['order_id']) ? $order_info['order_id'] : "" ,
            'phonenumber'        => $order_info['telephone'],
            'total'              => $order_total,
            'store_url'          => isset($order_info['store_url']) ? $order_info['store_url'] : "",
            'shipping_address_1' => isset($order_info['shipping_address_1']) ? $order_info['shipping_address_1'] : "",
            'shipping_address_2' => isset($order_info['shipping_address_2']) ? $order_info['shipping_address_2'] : "",
            'payment_address_1'  => isset($order_info['payment_address_1'])  ? $order_info['payment_address_1']  : "",
            'payment_address_2'  => isset($order_info['payment_address_2'])  ? $order_info['payment_address_2']  : "",
            'payment_method'     => isset($order_info['payment_method'])     ? $order_info['payment_method']     : "",
            'shipping_method'    => isset($order_info['shipping_method'])    ? $order_info['shipping_method']    : "",
            'order_date'          => $order_info['date_added'],
            'status_name'        => $order_info['status_name']
		);

		$header_parameters = $WhatAppCommons->get_whatsapp_variables($template_header,$values_array);
		$body_parameters   = $WhatAppCommons->get_whatsapp_variables($template_body,$values_array);
		//return $template_to;
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
        //return $res;
    }


    /**
     *
     */
	 //[DONE]
    public function notify_or_not_store_owner_on_order_update($order_info, $data){

        /*
         * Get all observers
         */
        $whatsapp_template = $this->get_template_from_observer($data['order_status_id'], 'whatsapp_cfg_odr_observers_for_admin');
        //return  $whatsapp_template;
		if(empty($whatsapp_template)){
            if (WhatAppCommons::log($this->config)) $this->log->write("[whatsapp] Do not notify store owner on order status update because no observer is found. Aborting!");
            return ;
        }

		//prepare data
		//The order total
        $this->load->model('sale/order');
		$order_id = $order_info['order_id'];
		$WhatAppCommons = new WhatAppCommons($this->config);
        $order_total = $WhatAppCommons->doGetOrderTotal($this->model_sale_order->getOrderTotals($order_id),$this->config);

        if(!empty($order_info['order_status_id']))
        {
         $order_status_query = $this->db->query("SELECT name FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_info['order_status_id'] . "' AND language_id = '" . (int)$order_info['language_id'] . "'");
         $order_info['status_name'] = $order_status_query->row['name'];
        }

        $template_to   = $this->config->get('config_telephone');
		$template_to 	= explode(",", str_replace(' ', '', $template_to));
		$msg_salt 		= $this->config->get('whats_cfg_msg_salt');
		$lang_code 		= $this->language->get('code');

		$template_name = 'whatsapp_cfg_odr_observers_for_admin_'.$data['order_status_id'].'_'.$msg_salt;
		$template_language	= [
								"policy"=>"deterministic",
								"code"=> $lang_code
								];

		$template_header = $whatsapp_template['header_'.$lang_code];
		$template_body   = $whatsapp_template['body_'.$lang_code];
		//return  $template_body;
        $values_array = array(
			'comment' => strip_tags(html_entity_decode($data['comment'], ENT_QUOTES, 'UTF-8')),
			'firstname'          => $order_info['firstname'],
            'lastname'           => $order_info['lastname'],
            'order_id'           => isset($order_info['order_id']) ? $order_info['order_id'] : "" ,
            'phonenumber'        => $order_info['telephone'],
            'total'              => $order_total,
            'store_url'          => isset($order_info['store_url']) ? $order_info['store_url'] : "",
            'shipping_address_1' => isset($order_info['shipping_address_1']) ? $order_info['shipping_address_1'] : "",
            'shipping_address_2' => isset($order_info['shipping_address_2']) ? $order_info['shipping_address_2'] : "",
            'payment_address_1'  => isset($order_info['payment_address_1'])  ? $order_info['payment_address_1']  : "",
            'payment_address_2'  => isset($order_info['payment_address_2'])  ? $order_info['payment_address_2']  : "",
            'payment_method'     => isset($order_info['payment_method'])     ? $order_info['payment_method']     : "",
            'shipping_method'    => isset($order_info['shipping_method'])    ? $order_info['shipping_method']    : "",
            'order_date'          => $order_info['date_added'],
            'status_name'        => $order_info['status_name']
		);

		$header_parameters = $WhatAppCommons->get_whatsapp_variables($template_header,$values_array);
		$body_parameters   = $WhatAppCommons->get_whatsapp_variables($template_body,$values_array);
		//return $template_to;
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

    /**
     * @param $observer_type 'whatsapp_cfg_odr_observers' or 'whatsapp_cfg_odr_observers_for_admin'
     */
	 //[DONE]
    private function get_template_from_observer($order_status_id, $observer_type){
        $template = "";

        $observers = $this->config->get($observer_type);
		//return $observers;
        if($observers){
            foreach ($observers as $observer) {
                if($observer['key'] == $order_status_id){
                    $template = $observer;
                    if (WhatAppCommons::log($this->config)) $this->log->write("[whatsapp] SMS template: " . $template);
                    break;
                }
            }
        }

        return $template;
    }



	   /**
     * Check if app installed
     *
     * @return boolean
     */
    public function isInstalled()
    {
        return \Extension::isInstalled('whatsapp');
    }


	/**
	Main Function for send all  FacebookManager [GRAPH API] Curl Requests
	*/

	private function sendGraphApiRequest($_url,$request_type='POST',$headers=[], $data=[]){
		$api_url = $this->GraphApiUrl($_url);
		$response = $this->sendCurlRequest($api_url,$request_type,$headers,$data,$this->config);
		return $response;
	}

	private function sendPureGraphApiRequest($_url,$request_type='POST',$headers=[], $data=[]){
		$api_url  = $this->APIGraph_URL."/".$this->graphApi_version . $_url;
		//var_dump($api_url);die();
		$response = $this->sendCurlRequest($api_url,$request_type,$headers,$data,$this->config);
		return $response;
	}

	/**
	Main Function for send all Curl Requests
	*/
	private function sendCurlRequest($api_url,$request_type='POST',$headers=[], $data=[],$config){

		$soap_do     = curl_init();
        curl_setopt($soap_do, CURLOPT_URL, $api_url);
        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($soap_do, CURLOPT_TIMEOUT, 10);
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($soap_do, CURLOPT_ENCODING, true);
        curl_setopt($soap_do, CURLOPT_CUSTOMREQUEST, $request_type);

		if ($request_type == 'POST' || $request_type == 'DELETE' ){
		$data = json_encode($data);
		$length = strlen($data);
		$headers[]="Content-Length: $length";
		curl_setopt($soap_do, CURLOPT_POSTFIELDS, $data);
		}

		curl_setopt($soap_do, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($soap_do);
		 WhatAppCommons::developerLog(" [Graph API] $api_url | $response ",$config);
		return $response;

	}

	//--------------// integration steps //------------------------//
	/**
     * Validate personal tab fields
	 *
	 * @param array $data
	 *
	 * @return array
	 */
    public function validateBussinessData($data)
	{
		$errors = [];
		if (mb_strlen($data['business_name']) < 1 || isset($data['business_name']) == false) {
			$errors['business_name'] = 'error in "business name" field';
		}

        if (
            strlen($data['whatsapp_business_id']) < 1 ||
            isset($data['whatsapp_business_id']) == false ||
            preg_match('#^[\+0-9]+[0-9]$#', $data['whatsapp_business_id']) == false
        ) {
            $errors['whatsapp_business_id'] = 'error in "whatsapp business id" field';
        }

		if (
            strlen($data['whatsapp_phone_number']) < 1 ||
            isset($data['whatsapp_phone_number']) == false ||
            preg_match('#^[\+0-9]+[0-9]$#', $data['whatsapp_phone_number']) == false
        ) {
            $errors['whatsapp_phone_number'] = 'error in "whatsapp phone number" field';
        }

        if (count($errors) > 0) {
			return [
				'hasErrors' => true,
				'errors' => $errors
			];
		}

		return [
			'hasErrors' => false
		];
	}

	public function validateRequestActivation($data)
	{
		$errors = [];
		if (mb_strlen($data['whatsapp_methods']) < 1 || isset($data['whatsapp_methods']) == false) {
			$errors['whatsapp_methods'] = 'error in "whatsapp methods " field';
		}
		if (mb_strlen($data['whatsapp_country_code']) < 1 || isset($data['whatsapp_country_code']) == false) {
			$errors['whatsapp_country_code'] = 'you should enter phone country code ex : 20 in egpyt case';
		}



        if (count($errors) > 0) {
			return [
				'hasErrors' => true,
				'errors' => $errors
			];
		}

		return [
			'hasErrors' => false
		];
	}

	public function validateVerifyActivation($data)
	{
		$errors = [];
		if (mb_strlen($data['whatsapp_verification_code']) < 1 || isset($data['whatsapp_verification_code']) == false) {
			$errors['whatsapp_verification_code'] = 'error in "whatsapp verification code " field';
		}

        if (count($errors) > 0) {
			return [
				'hasErrors' => true,
				'errors' => $errors
			];
		}

		return [
			'hasErrors' => false
		];
	}

	//ectools - Add Request
	public function RequestIntegration ($data){
		
		$business_name 		  	 = isset($data['business_name'])? $data['business_name']:'';
		$business_id 		  	 = isset($data['business_id'])? $data['business_id']: '';
		$whatsapp_business_id 	 = isset($data['whatsapp_business_id'])? $data['whatsapp_business_id']: '';
		$phone_number		  	 = isset($data['whatsapp_phone_number'])? $data['whatsapp_phone_number']: '';
		$client_url		 	  	 = isset($data['client_url'])? $data['client_url']:'';
		$client_username 	  	 = isset($data['client_username'])?$data['client_username']:'';
		$client_password 	  	 = isset($data['client_password'])?$data['client_password']:'';
		$embedded_signup 	  	 = isset($data['embedded_signup'])? $data['embedded_signup']:0;
		$whatsapp_sandbox_status = isset($data['whatsapp_sandbox_status'])? $data['whatsapp_sandbox_status'] :'';
		$fb_status   	 		 = isset($data['fb_status'])? $data['fb_status'] :'';
		$store_code 	 	  	 = defined('STORECODE')? STORECODE:'';

		$query = [];
        $query[] = 'INSERT INTO ' . $this->integrationRequestTable . ' SET';
        $query[] = ' business_name  = "' . $this->ecusersdb->escape($business_name) . '",';
        $query[] = ' business_id  = "' . $this->ecusersdb->escape($business_id) . '",';
        $query[] = ' whatsapp_business_id  = "' . $this->ecusersdb->escape($whatsapp_business_id) . '",';
        $query[] = ' phone_number  = "' . $this->ecusersdb->escape($phone_number) . '",';
        $query[] = ' store_code  = "' . $this->ecusersdb->escape($store_code) . '",';
        $query[] = ' client_url  = "' . $this->ecusersdb->escape($client_url) . '",';
        $query[] = ' client_username  = "' . $this->ecusersdb->escape($client_username) . '",';
        $query[] = ' client_password  = "' . $this->ecusersdb->escape($client_password) . '",';
        $query[] = ' embedded_signup  = "' . $this->ecusersdb->escape($embedded_signup) . '",';
        $query[] = ' status  = "' . $this->ecusersdb->escape($whatsapp_sandbox_status) . '",';
        $query[] = ' fb_status  = "' . $this->ecusersdb->escape($fb_status) . '",';
        $query[] = ' created_at  = NOW()';
        $this->ecusersdb->query(implode(' ', $query));
        $requestId = $this->ecusersdb->getLastId();
        return  $requestId;
	}
	
	//last integration request has image url [instance url]
	public function lastIntegrationRequest(){
		$sql = sprintf('Select * from '.$this->integrationRequestTable.
						' where store_code = "%s" and client_url <> ""
						order by id DESC LIMIT 1',strtoupper(STORECODE));
						
		$data = $this->ecusersdb->query($sql);
        return $data->row;
	}
	
	/*
	 * update the last WA Request Record at ectools 
	 * set app_status = 'uninstall'	
	 *
	 */
	public function integrationRequestUninstall (){
		//avoid any suitable exceptions from ectools DB
		try {
				$uninstall_status = 'uninstall';
				$sql = sprintf('UPDATE '.$this->integrationRequestTable.' set app_status = "%s" where store_code = "%s" order by id DESC LIMIT 1',$uninstall_status, strtoupper(STORECODE));
				$this->ecusersdb->query($sql);
			} catch (Exception $e) {
						//TO:DO Add Log here 
			}
					
	}

	//--------------#integration steps  #------------------------//

	//--------------------------------# connected docker DBS#----------------------------//

	//--------------#docker DB connection & functionality #------------------------//
	//##-- not used at current time ##---//
	static function getMessConn(){
		
		if(!self::$messConn) {
			//avoid any suitable exceptions from images DBs
			try {
				$DB = self::dbPrefix().self::$messagesDB;
				$conn = new mysqli(WHATSAPP_HOSTNAME,MEMBERS_USERNAME, MEMBERS_PASSWORD, $DB);

				if ($conn->connect_error) {
					
					//temp log file till make sure that all instance are now stable 
					$log = new Log('whatsapp_chat');
					$log->write(" [Whatsapp-chat]connection error : ".$conn->connect_error);
					
					return false;
				}

				$conn->query("SET NAMES 'utf8mb4'");
				$conn->query("SET CHARACTER SET utf8mb4");
				$conn->query("SET CHARACTER_SET_CONNECTION=utf8mb4");
				$conn->query("SET SET CHARACTER_SET_RESULTS=utf8mb4");
				$conn->query("SET SQL_MODE = ''");

				self::$messConn = $conn;
			} catch (Exception $e) {
				
				//temp log file till make sure that all instance are now stable 
				$log = new Log('whatsapp_chat');
                $log->write(" [Whatsapp-chat]connection exception : ".$e->getMessage());
			}
		}

		return self::$messConn;
	}

	static function getContactsConn(){
		if(!self::$CONTConn) {
			//avoid any suitable exceptions from images DBs
			try {
				$DB = self::dbPrefix().self::$contactsDB;
				$conn = new mysqli(WHATSAPP_HOSTNAME,MEMBERS_USERNAME, MEMBERS_PASSWORD,$DB );

				if ($conn->connect_error) {
				  //die("Connection failed: " . $conn->connect_error);
				  return false;
				}

				$conn->query("SET NAMES 'utf8mb4'");
				$conn->query("SET CHARACTER SET utf8mb4");
				$conn->query("SET CHARACTER_SET_CONNECTION=utf8mb4");
				$conn->query("SET SET CHARACTER_SET_RESULTS=utf8mb4");
				$conn->query("SET SQL_MODE = ''");
				self::$CONTConn = $conn;
			} catch (Exception $e) {
						//TO:DO Add Log here 
			}
		}

		return self::$CONTConn;
	}

	//db prefix dynamic So we can change it easily
	//at testing or at any other needed cases
	private static function dbPrefix (){
		
		//temp for demo - kuzlo DB 
		if(strtolower(STORECODE) == 'ngputa9337'){
			return strtoupper('fkuop697_');
		}
		
		if(in_array(STORECODE,CHECKOUT_TEST_STORES)){
		return "";
		}
		return  STORECODE.'_';
	}

	//DB1: messages DB
	public function getAllMessages (){
		$db_conn = self::getMessConn();
		$sql = "SELECT * FROM messages";
		$result = $db_conn->query($sql);
		$data = [];
		if ($result->num_rows > 0) {
		  // output data of each row
		  while($row = $result->fetch_assoc()) {
			$data[]=$row;
		  }
		}

		return $data;
	}

	//DB1: messages DB
	public function getMessages ($key_remote_jid,$limit=10,$page=1){
		$db_conn = self::getMessConn();
		$data = [];
		$offset = ($limit * $page ) - $limit ;

		$sql ="SELECT t.*
				FROM
				(
				SELECT messages.*,CONVERT(messages.hsm USING utf8) as hsm_converted,TO_BASE64(hsm) as hsm_64,
						media_items.plaintext_file_path, media_items.external_provider_handle,
						media_items.plaintext_file_hash, media_items.duration_seconds,
						media_items.mms_direct_path, media_items.mime_type, media_items.type,
						TO_BASE64(thumb_data) as thumb_data,
						media_items.size,media_items.external_provider_name, media_items.mms_hint_ip,
						media_items.processed_file_path, media_items.external_provider_config_id,
						media_items.mms_url, media_items.processed_file_hash

				   FROM messages
								LEFT JOIN media_items ON media_items.media_id = messages.media_id
								where messages.key_remote_jid = '$key_remote_jid' and (messages.data IS NOT NULL OR messages.media_id IS NOT NULL )
								ORDER BY messages._id desc
								limit $limit offset $offset
				) t
				ORDER BY t._id ASC";


		$result = $db_conn->query($sql);
		if ($result->num_rows > 0) {
		  // output data of each row
		  while($row = $result->fetch_assoc()) {
			$data[]=$row;
		  }
		}
		return $data;
	}

	//DB1: messages DB
	public function getMessage ($id){
		$db_conn = self::getMessConn();
		$data    = [];
		$sql     = "SELECT * FROM messages where _id = '$id'";
		$result = $db_conn->query($sql);

		if ($result->num_rows > 0) {
		  // output data of each row
		  while($row = $result->fetch_assoc()) {
			$data=$row;
		  }
		}
		return $data;
	}

	//DB1: messages DB
	public function getMessagesAfter ($key_remote_jid,$_id){
		$db_conn = self::getMessConn();
		
		$data = [];
		if($db_conn){
			$offset = ($limit * $page ) - $limit ;
			$sql = "
					SELECT
					messages.*,
							media_items.plaintext_file_path, media_items.external_provider_handle,
							media_items.plaintext_file_hash, media_items.duration_seconds,
							media_items.mms_direct_path, media_items.mime_type, media_items.type,
							TO_BASE64(thumb_data) as thumb_data,
							media_items.size,media_items.external_provider_name, media_items.mms_hint_ip,
							media_items.processed_file_path, media_items.external_provider_config_id,
							media_items.mms_url, media_items.processed_file_hash
					FROM messages
					LEFT JOIN media_items ON media_items.media_id = messages.media_id
					where messages.key_remote_jid = '$key_remote_jid' and messages._id > '$_id'
					ORDER BY messages._id desc
					";

			$result = $db_conn->query($sql);

			if ($result->num_rows > 0) {
			  // output data of each row
			  while($row = $result->fetch_assoc()) {
				$data[]=$row;
			  }
			}
		}
		return $data;
	}

	//DB1: messages DB
	public function readMessages ($key_remote_jid){
		$db_conn = self::getMessConn();
		$result = false;
		if($db_conn){
			$offset = ($limit * $page ) - $limit ;
			$sql = "UPDATE `chats` SET `unread_count` = '0'
					where chats.key_remote_jid = '$key_remote_jid'";
			$result = $db_conn->query($sql);
		}
		
		return $result;
	}

	public function getAllChats (){

		$db_conn = self::getMessConn();
		$data = [];
		
		if($db_conn){
			$sql = "
					SELECT mc.*,cc.profile_name,cc.last_sync_time
					FROM ".self::dbPrefix()."messageStore.chats mc
					LEFT JOIN ".self::dbPrefix()."contactStore.wa_contacts cc
					ON mc.key_remote_jid = cc.jid
					order by mc.last_timestamp DESC
					";
		
			$result = $db_conn->query($sql);

			if ($result->num_rows > 0) {
			  // output data of each row
			  while($row = $result->fetch_assoc()) {
				$data[]=$row;
			  }
			}
		}
		
		return $data;
	}

	public function unreaded_messages (){
		$db_conn = self::getMessConn();
		$unread_count = 0 ;
		if($db_conn){
			$sql = "SELECT SUM(unread_count) as unread_count FROM `chats`";
			$result = $db_conn->query($sql);
			$data 	= $result ->fetch_assoc();
			$unread_count = $data['unread_count'] ;
		}
		return $unread_count;
	}

	public function get_contact ($jid){
		$db_conn = self::getContactsConn();
		$data = [];
		if($db_conn){
			$sql = "SELECT * FROM wa_contacts where jid='$jid'";
			$result = $db_conn->query($sql);
			
			if ($result->num_rows > 0) {
			  // output data of each row
			  while($row = $result->fetch_assoc()) {
				$data=$row;
			  }
			}
		}
		return $data;
	}

	//contacts DB :
	public function getAllcontacts (){
		$db_conn = self::getContactsConn();
		$data = [];
		if($db_conn){
			$sql = "SELECT * FROM wa_contacts";
			$result = $db_conn->query($sql);

			if ($result->num_rows > 0) {
			  // output data of each row
			  while($row = $result->fetch_assoc()) {
				$data[]=$row;
			  }
			}
		}
		return $data;
	}


		/**
     *  validate webhook signature 
     * 
     * 
     */
	public function validateSignature($body, $header_signature = ''): bool
    {
       
		if(!defined('ECTOOLS_ENC_KEY')){
			define ('ECTOOLS_ENC_KEY', '8ah3ww72bk4b9agddm2art1gy5h75zhaz4im9gd3');
		}
		
		// Signature matching
		$expected_signature = hash_hmac('sha1', $body , ECTOOLS_ENC_KEY );

		$signature = '';
		if(
			strlen($header_signature) == 45 &&
			substr($header_signature, 0, 5) == 'sha1='
		  ) {
		  $signature = substr($header_signature, 5);
		}
		
		//validate 
		if (hash_equals($signature, $expected_signature)) {
		 return true;
		}

		return false;
    }

    public function track_event($type, $to){
    	$this->ecusersdb->query("INSERT INTO `" . DB_PREFIX . "whatsapp_messages_tracking` (`type`, `store_code`, `to`) VALUES ('$type', '" . STORECODE ."', '$to')");
    }

}
?>
