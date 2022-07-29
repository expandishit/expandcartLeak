<?php


class WhatAppCommons {
	
	//live server 
	private $AdminAuthToken =false;
	private $AuthToken = false;
	private $AdminUsername;
	private $AdminPassword;
	private $Username;
	private $Password;
	private $APIclient_URL;
	private $api_version = 'v1';
	private $templates_namespace;
	private $config = array();
	
	private $old_ip = '35.198.187.26';
	private $new_ip = '34.141.77.100';
	/**
	 * Called at the end of file
	 */
 	
	public function __construct($config){
		$this->config = $config;
		if (!empty($config)){
			$this->templates_namespace = $config->get('whatsapp_template_namespaces');
			$this->AdminUsername =$this->Username=$config->get('whatsapp_api_username');
			$this->AdminPassword =$this->Password=$config->get('whatsapp_api_password');
			$this->APIclient_URL =$config->get('whatsapp_api_url');
			
			//for automatically replace our old service ip with the new one
			if(!empty($this->APIclient_URL)){
				$this->APIclient_URL = str_replace($this->old_ip,$this->new_ip,$this->APIclient_URL);
			}
		}
	}
	
	static function init(){
	    
			
	}
	
	//from sms app logic 
	static public function log($config){
	    return  $config->get('whatsapp_cfg_log');
	}
	
	// Function for basic field validation (present and neither empty nor only white space
	static public function isNullOrEmptyString($question){
	    return (!isset($question) || trim($question)==='');
	}
	
	static public function startsWith($haystack, $needle)
	{
	    return !strncmp($haystack, $needle, strlen($needle));
	}
	
	function endsWith($haystack, $needle)
	{
	    $length = strlen($needle);
	    if ($length == 0) {
	        return true;
	    }
	
	    return (substr($haystack, -$length) === $needle);
    }
    
    static public function replace_first($haystack, $needle, $replace, $idx = 0)
    {
		$pos = strpos($haystack, $needle);
		if ($pos === 0) {
			$patternArr = explode(',', $replace);
		    $stringN = substr_replace($haystack, trim($patternArr[$idx]),$pos,strlen($needle));

			return $stringN;
		}
	    return $haystack;
	}
	
	//#from sms app logic 

	//-------------- (00)-USER -------------------------//
	
	/**
	login api 
	*/
	public function login($admin=false){
		$_url = 'users/login';
		
		// user or admin login 
		if($admin){
		$login_credentials = $this->AdminUsername . ":" . $this->AdminPassword ;
		$encoded_auth = base64_encode($login_credentials);
		$data = array("username" => $this->AdminUsername, "password" => $this->AdminPassword);	
		} else {
		$login_credentials = $this->Username . ":" . $this->Password ;
		$encoded_auth = base64_encode($login_credentials);
		$data = array("username" => $this->Username, "password" => $this->Password);
		}
		
		$headers = [];
		$headers[]="Content-Type: application/json;charset=utf-8";
		$headers[]="Authorization: Basic  $encoded_auth";
		$response = $this->sendclientApiRequest($_url,'POST',$headers, $data);
		$data = json_decode($response);
		
		if($admin){
			$this->AdminAuthToken = false;
			if(isset($data->users)){
				$this->AdminAuthToken = $data->users[0]->token;
			}
		}else{
			$this->AuthToken = false;
			if(isset($data->users)){
				$this->AuthToken = $data->users[0]->token;
			}
		}
		
		return $response;
	}
	
	//TO:DO first Admin Login 
	//-------------- (01)-Settings -------------------------//
	//update instance setting 
	public function updateSetting ($settings=[]){
		$allowed_settings = [		
						"callback_backoff_delay_ms",
						"max_callback_backoff_delay_ms",
						"callback_persist",
						"media",
						"webhooks",
						"pass_through",
						"sent_status",
						"db_garbagecollector_enable"
						];
						
		//prepare data 
		$postData = [];
		foreach ($settings as $key => $value ) {
			if(!in_array($key,$allowed_settings)){
				continue;
			}
			$postData[$key] = $value;
		}
		
		//request data 
        $headers = [];
		$_url = 'settings/application';
		if(!$this->AuthToken){
			$this->login();
		}
		$token = $this->AuthToken;
		$headers = [];
		$headers[]="Content-Type: application/json;charset=utf-8";
		$headers[]="Authorization: Bearer $token";
		$response = $this->sendclientApiRequest($_url,'PATCH',$headers,$postData);
		return $response;
						
	}
	
	//-------------- (02)-Rgistration -------------------------//
	/**
	Registration : Register Account
	*/
	/*
		method : <sms | voice >
	*/
	public function account($cc,$phone_number,$method,$cert,$pin=''){
		$_url = 'account';
		if(!$this->AdminAuthToken){
		$this->login(true); //true for admin login
		}
		$token = $this->AdminAuthToken;	
		
		if(!$token){
			return false;
		}
		$postData = [];
		$postData['cc'] = $cc;
		$postData['phone_number'] = $phone_number;
		$postData['method'] = $method;
		$postData['cert'] = $cert;
		$postData['pin'] = $pin;
		
        $headers = [];
		$headers[]="Authorization: Bearer $token";
		$headers[]="Content-Type: application/json;charset=utf-8";
		$response = $this->sendclientApiRequest($_url,'POST',$headers, $postData);
		return $response;
		
	}
	
	/**
	Registration : Account verify
	*/
	public function accountVerify($code){
		$_url = 'account/verify';
		if(!$this->AdminAuthToken){
		$this->login(true); //true for admin login
		}
		$token = $this->AdminAuthToken;	
		
		$postData = [];
		$postData['code'] = $code;
        $headers = [];
		$headers[]="Authorization: Bearer $token";
		$headers[]="Content-Type: application/json;charset=utf-8";
		$response = $this->sendclientApiRequest($_url,'POST',$headers, $postData);
		return $response;
		
	}
	
	//-------------- (03)-contacts -------------------------//
	/** 
	check contact availability 
	*/
	public function contacts($number,$blocking='wait',$foceCheck=true){
		
		$_url = 'contacts';
		if(!$this->AuthToken){
			$this->login();
		}
		$token = $this->AuthToken;
		$postData = array();
		$postData['blocking']       = $blocking;
		//if number contain + sign just add it 
		//if it doesent added it with & without + sign
		//we will get array of numbers "all of them represent the same one just pick up the valid wa_id one 

		 $contacts = [];
		 $contacts[] = $number;
		
		//if string "+" sign plus remove it add at number again 
		 if(substr( $number, 0, 1 ) === "+"){
			 $contacts[]  = substr($number, 1);
		 }else {
			 //if string does'nt contain "+" sign just add it 
			 $contacts[]  = "+".$number;
		 }
		 
		 //now we have array of number with two possibilities with & without "+" sign 
		 //one of them is valid & the other is not what we need only that we have a valid one of array 
		$postData['contacts'] 			= $contacts;
		$postData['force_check']        = $foceCheck;
		
		$headers = [];
		$headers[]="Authorization: Bearer $token";
		$headers[]="Content-Type: application/json;charset=utf-8";
		$response = $this->sendclientApiRequest($_url,'POST',$headers, $postData);
		return $response;
		
	}
	
	public function IScontactValid($number){
		$response=$this->contacts($number);
		
		if($response){
			
			$response = json_decode($response);
				$contacts = $response->contacts;
				
				//now we have a multiple form of numbers need to extract the valid one 
				foreach ($contacts as $contact ){
				$status =$contact->status;
				
				//return wa_id only when find a valid form of number 
				if($status == 'valid' ){
					$wa_id = $contact->wa_id;
					return $wa_id;
				}
				
				}			
			
		}

		return false;
	}
	
	//-------------- (04)-Message -----------------------//
	/**
	send direct message 
	*/
	public function sendMessage($to,$messageData,$w_id= false){
		$_url = 'messages';
		
		if($w_id){
		$w_id = $to ; 
		}else{
			//$w_id = '2'.$to;//need edit here call contacts API
			$w_id =$this->IScontactValid($to);
			
			if(!$w_id){
				return false;
			}
		}
		if(!$this->AuthToken){
			$this->login();
		}
		$token = $this->AuthToken;
		
		$postData = $messageData;
		$postData['to'] = $w_id;
        $headers = [];
		$headers[]="Authorization: Bearer $token";
		$headers[]="Content-Type: application/json;charset=utf-8";
		$response = $this->sendclientApiRequest($_url,'POST',$headers, $postData);
		return $response;
	}
	
	/**
	send template message 
	*/
	public function sendTemplateMessage($to,$template_name,$language,$components){
		$messageData = [];
		$messageData['type'] = 'template';
		$messageData['template']['namespace']=$this->templates_namespace;
		$messageData['template']['name']=$template_name;
		$messageData['template']['language']=$language;
		$messageData['template']['components']=$components;
		//for testing prepose 
		$responses = [];
		
		if(is_array($to)){
			$numbers = $to ; 
			foreach ($numbers as $number){
				$responses[] = $this->sendMessage($number,$messageData);
			}
		WhatAppCommons::developerLog("[fnc]" . __function__ . " | multiple-numbers | data " . json_encode($messageData). ' to=> ' . $to . ' response => ' . $response , $this->config);
		}else {
		$response= $this->sendMessage($to,$messageData);
		$responses[] =$response;
		WhatAppCommons::developerLog("[fnc]" . __function__ . " | data " . json_encode($messageData). ' to=> ' . $to . ' response => ' . $response , $this->config);
		}
		
		
		
	return $responses;
	}
	
	
	//---------- (05)-Groups [depreciated]---------------//
	
	//-------------- (06)-Media TO:DO -------------------------//
	
	//-------------- (07)-Stickers TO:DO----------------------//
	
	//--------------- (08)-Health ----------------------//
	/**
	check API Client health 
	*/
	public function checkHealth (){
		$_url = 'health';
		if(!$this->AuthToken){
			$this->login();
		}
		$token = $this->AuthToken;
		$headers = [];
		$headers[]="Content-Type: application/json;charset=utf-8";
		$headers[]="Authorization: Bearer $token";
		$response = $this->sendclientApiRequest($_url,'GET',$headers);
		return $response;
	}
		
	/**
	 backup data 
	*/
	public function backup($password=""){
		
		$_url = 'settings/backup';
		if(!$this->AuthToken){
			$this->login();
		}
		$token = $this->AuthToken;
		
		if(empty($password)){
			$password = EC_WHATSAPP["BACKUP_PASSWORD"]; 
		}
		
		$postData = ["password" => $password];
		
        $headers	= [];
		$headers[]	= "Authorization: Bearer $token";
		$headers[]	= "Content-Type: application/json;charset=utf-8";
		$response	= $this->sendclientApiRequest($_url,'POST',$headers, $postData);
		
		return $response;
	}
	
	/**
	 Generating the complete URL Using API_client Host URL & API_version with the passed URL 
	*/
	private function ClientApiUrl($path){
		return $this->APIclient_URL."/".$this->api_version."/".$path;
	}
	
	/**
	Main Function for send all WhatsApp Curl Requests 
	*/
	private function sendclientApiRequest($_url,$request_type='POST',$headers=[], $data=[]){
		$api_url = $this->ClientApiUrl($_url);
		$response = $this->sendCurlRequest($api_url,$request_type,$headers,$data,$this->config);
		return $response;
	}
	
	/**
	Main Function for send all WhatsApp Curl Requests 
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
		
		if ($request_type == 'POST' || $request_type == 'PATCH'){
		$data = json_encode($data);
		$length = strlen($data);
		$headers[]="Content-Length: $length";
		curl_setopt($soap_do, CURLOPT_POSTFIELDS, $data);
		}
		
		curl_setopt($soap_do, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($soap_do);
		
		//logger for development prepose 
		 WhatAppCommons::developerLog(" [API] $api_url | $response ",$config);
		
		return $response;

	}
	
	
	/**
	 * @param $rows
	 * @param null $config
	 * @return int|string
	 */
	public function doGetOrderTotal($rows,$config =null){
		foreach ($rows as $row) {
            $_title = $row['code'];    //was 'title', but title was translated to Arabic, so it won't match on 'Total'

            if($_title === 'total'){
                return html_entity_decode($row['text'], ENT_NOQUOTES, 'UTF-8');
            }
        }
		
		/*
		if ($config){
		if ($config->get('whatsapp_cfg_log_client')) {
                $log = new Log($config->get('whatsapp_client_log_filename'));
                $log->write("[whatsapp] Phone number: $number does not pass filters");
            }
		}*/
		 WhatAppCommons::clientLog("[whatsapp] Phone number: $number does not pass filters",$config);
		
        return 0;
	}
	
	
	public function get_whatsapp_order_variables( $template,$order_info,$whats_order_total=''){
		
		
        $whatsapp_values = array(
            'firstname'          => $order_info['firstname'],
            'lastname'           => $order_info['lastname'],
            'order_id'           => isset($order_info['order_id']) ? $order_info['order_id'] : "" ,
            'phonenumber'        => $order_info['telephone'],
            'total'              => $whats_order_total,
            'store_url'          => isset($order_info['store_url']) ? $order_info['store_url'] : "",
            'shipping_address_1' => isset($order_info['shipping_address_1']) ? $order_info['shipping_address_1'] : "",
            'shipping_address_2' => isset($order_info['shipping_address_2']) ? $order_info['shipping_address_2'] : "",
            'payment_address_1'  => isset($order_info['payment_address_1'])  ? $order_info['payment_address_1']  : "",
            'payment_address_2'  => isset($order_info['payment_address_2'])  ? $order_info['payment_address_2']  : "",
            'payment_method'     => isset($order_info['payment_method'])     ? $order_info['payment_method']     : "",
            'shipping_method'    => isset($order_info['shipping_method'])    ? $order_info['shipping_method']    : "",
            'order_date'          => $order_info['date_added'],
            'status_name'        => $order_info['status_name'],
        );

 
		$whatsapp_component_parameters = $this->get_whatsapp_variables( $template,$whatsapp_values);
        return $whatsapp_component_parameters;
	}

	//variables names at template should be same of indexs name at values_array
	public function get_whatsapp_variables( $template,$values_array){
		
		preg_match_all('/{(\w+)}/', $template, $matches);
		$template_variables =$matches[1];
		
		$whatsapp_component_parameters = [];
		 foreach($template_variables as $template_variable ){
			 //return $values_array[$template_variable];
			 //whatsapp doesnt accept null value so if variable name not found will replaced with ' ... '
			 $whatsapp_component_parameters[] = [
								"type"=> "text",
								"text"=>   isset( $values_array[$template_variable])   ? (string)$values_array[$template_variable] : "..."  
							];
		 }
 
        return $whatsapp_component_parameters;
	}

	//to get the template messages from observers helper 
	public function getObserverTemplate($order_status_id, $observer_type){
        $template = "";

        $observers = $this->config->get($observer_type);
        if($observers){
            foreach ($observers as $observer) {
                if($observer['key'] == $order_status_id){
                    $template = $observer;
                    break;
                }
            }
        }

        return $template;
    }
	
	//to handle failed orders check at payment methods has custom failed status 
	public function paymentFailed($payment_code,$order_status){
		
		if (empty($order_status)){
			return true;
		}

		$payments_custom_failed_status = [
				'urway'		=> 'payment_urway_failed_status_id',
				'expandpay'	=> 'expandpay_denied_order_status_id',
				'tap2' => 'tap2_denied_status_id'
				];
				
		if(isset($payments_custom_failed_status[$payment_code])){
			return $order_status == $this->config->get($payments_custom_failed_status[$payment_code]);
		}
		
		//default paymment methods failed status names 
		return $order_status == $this->config->get($payment_code.'_failed_order_status_id');
		
	}
	
	
		static public function clientLog($text,$config){
		if ($config->get('whatsapp_cfg_log_client')) {
                $log = new Log($config->get('whatsapp_client_log_filename'));
                $log->write($text);
            }
	}
	static public function developerLog($text,$config){
		if ($config->get('whatsapp_cfg_log_developer')) {
                $log = new Log($config->get('whatsapp_developer_log_filename'));
                $log->write($text);
            }
	}
	
	
}

class WhatsAppPhonenumberFilter {

    /**
     * Number filtering is applied to one number only which is the customer number.
     * TODO rename to passTheStartsWithFilter
     */
    static public function isNumberAuthorized($number, $patterns){

        //check that the number is not null and is not empty
        if(WhatAppCommons::isNullOrEmptyString($number)){
            return false;
        }

        //if no patterns return true
        if(WhatAppCommons::isNullOrEmptyString($patterns)){
            return true;
        }

        //split patterns by comma into an array
        $patternArr = explode(",", $patterns);

        foreach ($patternArr as $pattern) {
            if(WhatAppCommons::startsWith($number, trim($pattern))){
                return true;
            }
        }

        return false;
    }

    static public function passTheNumberSizeFilter($number, $size){

        if(WhatAppCommons::isNullOrEmptyString($number)){
            return false;
        }

        if(WhatAppCommons::isNullOrEmptyString($size)){
            return true;
        }

        return strlen($number) == $size;
    }

    /**
     * @return boolean true if the number passes the filter, thus the sms should be sent. False otherwise.
     */
    static public function passFilters($number, $config){
        $whatsapp_patterns   = $config->get('whatsapp_config_number_filtering');
        // $number_size_filter = $config->get('whatsapp_cfg_num_filt_by_size');

        $pass = self::isNumberAuthorized($number, $whatsapp_patterns);
            // && self::passTheNumberSizeFilter($number, $number_size_filter);

        if($pass === false){
            WhatAppCommons::clientLog('number not valid',$config);
        }

        return $pass;
    }
	

	
    static public function rewritePhoneNumber($number, $config){
        //if search is not empty.
        if(! WhatAppCommons::isNullOrEmptyString($config->get('whatsapp_cfg_number_rewriting_search'))) {
	
			//split rewrite search number by comma into an array
			$patternArr = explode(",", $config->get('whatsapp_cfg_number_rewriting_search'));
			
			foreach ($patternArr as $index => $pattern) {
				$pattern = trim($pattern);
				if(WhatAppCommons::startsWith($number, $pattern)){
					return WhatAppCommons::replace_first($number, $pattern, $config->get('whatsapp_cfg_number_rewriting_replace'), $index);
				}
			}

		}

        return $number;
    }

}

whatAppCommons::init();

?>
