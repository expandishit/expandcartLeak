<?php

class SmshareNet {

    /**
     * @return response status.
     */
    static public function post_json($url, $postData) {

// 	    $log = new Log('error.txt');
// 	    $log->write(print_r($postData, true));
// 	    return ;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem");    //because some customers has a Curl wich comes with an outdated file to authenticate HTTPS certificates from. See http://stackoverflow.com/a/316732/353985

        //$smshare_headers = array("Accept" => "application/json", "Content-Type" => "application/json");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json')
        );//'Content-Length: ' . strlen($postData)

        $result = curl_exec($ch);

        if(!$result){
            $log = new Log('error.txt');
            $log->write('Curl error: ' . curl_error($ch));
        }

        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return $http_status;
    }
}

class SmshareCommons {
	
	//declare public field here
	//…
	
	public static $SMSHARE_HOST ;
	public static $REQUEST_PATH_SHARELINK;
	public static $REQUEST_PATH_SMS_BULK ;
	public static $ALERT_SMS_GATEWAY_URL;
	public static $ATOMPARK_SMS_GATEWAY_URL;

	/**
	 * Called at the end of file
	 */
	static function init(){
	    
	    //XXX
		self::$SMSHARE_HOST 		    = "https://smshare.fr";
// 		self::$SMSHARE_HOST 		    = "http://smshare.me";
		self::$REQUEST_PATH_SHARELINK   = self::$SMSHARE_HOST . "/service/sharelink";
		self::$REQUEST_PATH_SMS_BULK    = self::$SMSHARE_HOST . "/service/sms/bulk";		
		self::$ALERT_SMS_GATEWAY_URL    = "http://alertsms.ro/ws/server.php";
		self::$ATOMPARK_SMS_GATEWAY_URL = "http://atompark.com/api/sms/3.0/sendSMS";
	}


	static public function log($config){
	    return $config->get('config_error_log') && $config->get('smshare_cfg_log');
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

	
	
	static public function replace_first($haystack, $needle, $replace, $idx = 0) {
		$pos = strpos($haystack,$needle);
		if ($pos === 0) {
			$patternArr = explode(',', $replace);
		    $newstring = substr_replace($haystack, trim($patternArr[$idx]),$pos,strlen($needle));

			return $newstring;
		}
	    return $haystack;
	}

	

	/**
	 * Done for users with php < 5.3 that does not support anonymous functions.
	 * @param unknown $m
	 */
	private function utf8ToUnicodeCodePoints_replacement_callback($m){
		$ord = ord($m[0]);
		
		if ($ord <= 127) {
			$r = sprintf('\u%04x', $ord);
		} else {
			$r = trim(json_encode($m[0]), '"');
		}
		return str_replace("\u", "", $r);  //remove \u because mobily.ws need unicode without \u
	}
	
	/**
	 * http://stackoverflow.com/questions/10100617/how-to-convert-text-to-unicode-code-point-like-u0054-u0068-u0069-u0073-using-p
	 * required for mobily.ws (arabic) that needs msg to be in unicode. Eg. test → %5Cu0074%5Cu0065%5Cu0073%5Cu0074
	 */
	public function utf8ToUnicodeCodePoints($str) {
	    if (!mb_check_encoding($str, 'UTF-8')) {
	        trigger_error('$str is not encoded in UTF-8, I cannot work like this');
	        return false;
	    }
	    $str_unicode = preg_replace_callback('/./u', array($this, "utf8ToUnicodeCodePoints_replacement_callback")/*http://stackoverflow.com/a/5530057/353985*/, $str);

		return str_replace("\n", "000D", $str_unicode);    //\n was not handled before..
	}

	/**
	 * @deprecated
	 * @see send_sms
	 */
	public function sendSMS($sms_to, $sms_body, $config){
		$res = $this->send_sms(array(
			'to'     => $sms_to,
			'body'   => $sms_body,
			'config' => $config
		));

		try {
			
			(new Log('SMSHARE_ORDER_SUBMITTING_DEBUGGING.log'))->write('SMSHARE__DEBUGGING' . $res);
			
		} catch (\Exception $e) {
	
		}
		
		return $res;
	}
	
	/**
	 * To debug use the following command on the server: 
	 * tshark  -i eth0 
	 */
	public function send_sms($args){

	    $config = $args['config'];
		
		if (SmshareCommons::log($config)) $log = new Log($config->get('config_error_filename'));

		if (SmshareCommons::log($config)) $log->write('[smshare] Selecting a sending mode..');
		
		if('profile_api' === 'profile_android') {    //send using android
			
			$smshare_json = $this->build_smshare_json($args);
			
			$reply = SmshareNet::post_json(SmshareCommons::$REQUEST_PATH_SHARELINK, $smshare_json);
			
    		if (SmshareCommons::log($config)) $log->write('[smshare] Smshare server response: ' . print_r($reply, true));
			
			return $reply;
			
		}else{    //send using api
			//populate postData:

			// check if admin choose xml (vodafone)
			if($config->get('smshare_api_type') == 'xml'){
				return $this->sendSmsXmlVodafone($args);
			}
			
			//args['to']
			$data = array($config->get('smshare_api_dest_var') => $args['to']);
			
			$method = $config->get('smshare_api_http_method');
				
			//API URL:
			$url=$config->get('smshare_api_url');
			
			if($config->get('smshare_api_msg_to_unicode') === 'on' || $config->get('smshare_api_msg_to_unicode') == '1'){
				$args['body'] = $this->utf8ToUnicodeCodePoints($args['body']);
			}
			
			//args['body']
			if($method === 'post' &&						        //Do not encode for GET because http_build_query encodes also.
			   $url !== SmshareCommons::$ALERT_SMS_GATEWAY_URL  &&  //Do not encode for alertsms.ro
	           strpos($url, '/api/v3/texting') === false            //Do not encode for ProWebSms gateway.
	        ){    
				$args['body'] = urlencode($args['body']);
			}

			
			$sms_body = html_entity_decode($args['body'], ENT_QUOTES, 'UTF-8');
			$data[$config->get('smshare_api_msg_var')] = $sms_body;
			
			
			//Inject additionnal fields
			$api_kv_arr = $config->get('smshare_api_kv');
			
			if(strpos($method,'post') !== false){
				foreach ($api_kv_arr as $api_kv) {
					if(!is_null($api_kv['val']))
						$data[$api_kv['key']] = $api_kv['val'];
					else
						$data[$api_kv['key']] = "";
				}
			
			}else{    //GET
				$do_not_encode_kv = array();
				foreach ($api_kv_arr as $api_kv) {
				    
				    if($url === SmshareCommons::$ATOMPARK_SMS_GATEWAY_URL){
				        //remove private key, it should be used only for sum building.
				        if ('private_key' === $api_kv['key']){
				            $private_key    = $api_kv['val'];
				            continue;
				        }
				    }
				    
					if(!isset($api_kv['encode'])) {    //No Encode. Encode must be set explicitely by the user before we encode params in http_build_query
						array_push($do_not_encode_kv, $api_kv['key'] . '=' . $api_kv['val']);
					}else{
						if(!is_null($api_kv['val']))
							$data[$api_kv['key']] = $api_kv['val'];
						else
							$data[$api_kv['key']] = "";

					}
				}
				
				if($url === SmshareCommons::$ATOMPARK_SMS_GATEWAY_URL){
			        if (SmshareCommons::log($config)) $log->write('[smshare] ATOMPARK gateway selected.');
				    $data['sum'] = self::build_control_sum($data, $private_key); 
				}
				
			
				$request_params = http_build_query($data, '', '&');
				if(count($do_not_encode_kv) > 0){
					$request_params .= '&' . implode("&", $do_not_encode_kv);
				}
			
				if (strpos($url, '?') === false) {
					$connector = '?';
				}else{
					$connector = '&';
				}

				if($method === 'get'){
					$url = $url;
				}else{
					$url = $url . $connector . $request_params;
				}
				
				$url = str_replace("%2B", "+", $url);
				if (SmshareCommons::log($config)) $log->write('[smshare] Final Gateway URL: ' . $url);
			}
			
			//read parameters from Query Params in url so we should append them to url
			if (isset($data['mobile'])) {
				$data['mobile'] = str_replace("+", "", $data['mobile']);
			} else {
				unset($data['mobile']);
			}

			if (!strpos($url, "basic.unifonic.com")){
				$url =$url."?". http_build_query($data,'','&');
			}

			// curl options:
			$options = array(
				CURLOPT_URL 		      => $url,
				CURLOPT_RETURNTRANSFER    => true
			);
			
			if (strpos($url, '/api/v3/texting') !== false) {
			    //inject access token in header.
			    $options[CURLOPT_HTTPHEADER] = array('X-Access-Token: ' . $data['access_token']);
			    unset($data['access_token']);
			}
			
			
			/*
			 * Passing a URL-encoded string will result in application/x-www-form-urlencoded
			 * $options[CURLOPT_HTTPHEADER] = array('Content-Type: application/x-www-form-urlencoded');
			 *
			 * passing an array to CURLOPT_POSTFIELDS results in  multipart/form-data
			 * http://php.net/manual/en/function.curl-setopt.php#84916
			 */
			if($method === 'post (application/x-www-form-urlencoded)'){
			    $data = http_build_query($data, '', '&');
			}
			
			
			if(strncmp($method, "post", strlen("post")) === 0){  //startswith
			    $options[CURLOPT_POST]       = true;
			    $options[CURLOPT_POSTFIELDS] = $data;
			}

			//error_log(print_r($options, true));

			// init the resource: curl handle
			$ch = curl_init();
			curl_setopt_array($ch, $options);
		
			//get response
			$output = curl_exec($ch);

			if(!$output){
			    if (SmshareCommons::log($config)) $log->write('[smshare] Curl error: ' . curl_error($ch));
			}
			
			curl_close($ch);
			
			if(curl_error($ch)){
				$this->record_error_msg(curl_error($ch),$url);
			}
			if($output != 1 || $output != "1"){
				$this->record_error_msg($output,$url);
			}
			/*
			 * log response
			 */
			if (SmshareCommons::log($config)) $log->write('[smshare] data is: ' . print_r($data, true));
			if (SmshareCommons::log($config)) $log->write('[smshare] Gateway reply: ' . $output);
			
			return $output;
		}

	}

	/**
	 * Send SMS
	 * @param  array  $args
	 * @return string
	 */

	public function sendSmsXmlVodafone($args){

		$config = $args['config'];

		$data = [];

		//Inject additionnal fields
		$params = $config->get('smshare_api_kv');

		foreach ($params as $key=>$param){
			$data[$param['key']] = $param['val'];
		}


		//API URL:
		$url = $config->get('smshare_api_url');

		if($config->get('smshare_api_msg_to_unicode') === 'on' || $config->get('smshare_api_msg_to_unicode') == '1'){
			$args['body'] = $this->utf8ToUnicodeCodePoints($args['body']);
		}

		// add more keys and values to data array
		$data['to'] = $args['to'];
		$data['body'] = urldecode($args['body']);

		$formatedData = $this->formatData($data);
		$secureHash = $this->generateHMAC($formatedData,$data['SecretKey']);

		
		//setting the curl parameters.
		$headers = array(
			"Content-Type: application/xml",
			"Accept: application/xml",
		);

		$xmlRequest = '<?xml version="1.0" encoding="UTF-8"?>
		<SubmitSMSRequest xmlns:="http://www.edafa.com/web2sms/sms/model/" xmlns:xsi="http://www.w3.org/2001/XMLSchema - instance" xsi:schemaLocation="http://www.edafa.com/web2sms/sms/model/ SMSAPI.xsd " xsi:type="SubmitSMSRequest">
			<AccountId>'.$data['AccountId'].'</AccountId>
			<Password>'.$data['Password'].'</Password>
			<SecureHash>'.$secureHash.'</SecureHash>
			<SMSList>
				<SenderName>'.$data['SenderName'].'</SenderName>
				<ReceiverMSISDN>'.$args['to'].'</ReceiverMSISDN>
				<SMSText>'.$data['body'].'</SMSText>
			</SMSList>
		</SubmitSMSRequest>';

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt( $ch, CURLOPT_POSTFIELDS,$xmlRequest);
		$result = curl_exec($ch);
		curl_close($ch);

		if (SmshareCommons::log($config)) $log->write('[smshare] data is: ' . print_r($data, true));
		if (SmshareCommons::log($config)) $log->write('[smshare] Gateway reply: ' . $result);

		return $result;
	}

	/**
	 * Format Data to be hashed
	 * @param  array $data
	 * @return string
	 */
	protected function formatData($data) {
		return "AccountId={$data['AccountId']}&Password={$data['Password']}&SenderName={$data['SenderName']}&ReceiverMSISDN={$data['to']}&SMSText={$data['body']}";
	}

	/**
	 * Generate HMAC SHA-256 (SecureHash)
	 * @param  string $formatedData
	 * * @param  string $secretKey
	 * @return string
	 */
	protected function generateHMAC($formatedData,$secretKey)
	{
		$hash = hash_hmac('sha256',  $formatedData , $secretKey);
		$hash = strtoupper($hash);
		return $hash;
	}


	public function record_error_msg($error_code,$url){
		$errors=array(
			"StoreCode" => STORECODE,
			"DateTime" => date("Y-m-d h:i:s A"),
			"Api_url" => $url,
			"Error_msg" => $error_code,
		);
		$errors_in_json=json_encode($errors).", \r\n";
        if (file_exists(ONLINE_STORES_PATH."/OnlineStores/sms_errors.json")) {
    		file_put_contents(ONLINE_STORES_PATH."/OnlineStores/sms_errors.json",$errors_in_json,FILE_APPEND);
        }
	  
	}  
	
	/**
	 * http://www.atompark.com/bulk-sms-service/smsapiv3/
	 */
	static function build_control_sum($params, $privateKey){
	    $params ['version'] ="3.0";
	    $params ['action'] = "sendSMS";
	    ksort ($params);
	    $sum='';
	    foreach ($params as $k=>$v)
	        $sum.=$v;
	    $sum .= $privateKey; //your private key
	    $control_sum =  md5($sum);
	    return $control_sum;
	}

	/**
	 * 
	 */
	public function sendSmsList($smsList, $config){
		
        if ($config->get('config_error_log')) {
        	error_log('→ smshare_json for notify customer by sms is: ' . json_encode($smsList)); 
        } 

        $smshare_json = $this->build_sms_list_json(
        	$config->get('smshare_username'), 
            $config->get('smshare_passwd')  , 
            $smsList
        );

        SmshareNet::post_json(SmshareCommons::$REQUEST_PATH_SMS_BULK, $smshare_json);
	}

	/**
	 * 
	 */
	public function build_sms_list_json($username, $passwd, $smsList){
		//Create the smsList bean.
		$smshare_json = new stdClass();
		$smshare_json->login    = $username;
		$smshare_json->passwd   = $passwd;
		$smshare_json->smsBeans = $smsList; 

		$smshare_json = json_encode($smshare_json);
		return $smshare_json;
	}

	/**
	 * 
	 * @param unknown $args
	 * login
	 * passwd
	 * to
	 * body
	 * [scheduleTimestamp]
	 * @return string
	 */
	public function build_smshare_json($args) {
	
	    $config = $args['config'];
	    
	    //Create the user
	    $smshare_user = new stdClass();
	    $smshare_user->login  = $config->get('smshare_username');
	    $smshare_user->passwd = $config->get('smshare_passwd');
	
	    //Create the smsbean
	    $smshare_smsBean = new stdClass();
	    $smshare_smsBean->destination = $args['to'];
	    $smshare_smsBean->message     = html_entity_decode($args['body'], ENT_QUOTES, 'UTF-8');
	    if(isset($args['scheduleTimestamp'])){
	        $smshare_smsBean->scheduleTimestamp = $args['scheduleTimestamp'];
	    }
	
	    //Create the smshareBean
	    $smshare_json = new stdClass();
	    $smshare_json->user    = $smshare_user;
	    $smshare_json->smsBean = $smshare_smsBean;
	
	    //Stringify
	    $smshare_data = json_encode($smshare_json);
	
	    return $smshare_data;
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

		if ($config){
			$log = new Log($config->get('config_error_filename'));
			$log->write('[smshare] Total was not found as title in the order row. This means that order_total subtitution has failed.');
		}

        return 0;
	}

	public function replace_smshare_variables($template, $order_info, $smshare_order_total=''){

		$find = array(
	        '{firstname}'         , 
            '{lastname}'          , 
            '{order_id}'          , 
            '{phonenumber}'       ,
            '{total}'             , 
            '{store_url}'         ,
            '{shipping_address_1}', 
            '{shipping_address_2}', 
            '{payment_address_1}' , 
            '{payment_address_2}' , 
            '{payment_method}'    ,
            '{shipping_method}'   ,
            '{order_date}'   ,
            '{status_name}'   ,
        );

        $replace = array(
            'firstname'          => $order_info['firstname'],
            'lastname'           => $order_info['lastname'],
            'order_id'           => isset($order_info['order_id']) ? $order_info['order_id'] : "" ,
            'phonenumber'        => $order_info['telephone'],
            'total'              => $smshare_order_total,
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

        $message = str_replace($find, $replace, $template);
        return $message;
	}
	
}

class SmsharePhonenumberFilter {

    /**
     * Number filtering is applied to one number only which is the customer number.
     * TODO rename to passTheStartsWithFilter
     */
    static public function isNumberAuthorized($number, $patterns){

        //check that the number is not null and is not empty
        if(SmshareCommons::isNullOrEmptyString($number)){
            return false;
        }

        //if no patterns return true
        if(SmshareCommons::isNullOrEmptyString($patterns)){
            return true;
        }

        //split patterns by comma into an array
        $patternArr = explode(",", $patterns);

        foreach ($patternArr as $pattern) {
            if(SmshareCommons::startsWith($number, trim($pattern))){
                return true;
            }
        }

        return false;
    }

    static public function passTheNumberSizeFilter($number, $size){

        if(SmshareCommons::isNullOrEmptyString($number)){
            return false;
        }

        if(SmshareCommons::isNullOrEmptyString($size)){
            return true;
        }

        return strlen($number) == $size;
    }

    /**
     * @return boolean true if the number passes the filter, thus the sms should be sent. False otherwise.
     */
    static public function passFilters($number, $config){
        $smshare_patterns   = $config->get('smshare_config_number_filtering');
        $number_size_filter = $config->get('smshare_cfg_num_filt_by_size');

        $pass = self::isNumberAuthorized($number,      $smshare_patterns) &&
            self::passTheNumberSizeFilter($number, $number_size_filter);

        if($pass === false){
            if ($config->get('config_error_log')) {
                $log = new Log($config->get('config_error_filename'));
                $log->write("[smshare] Phone number: $number does not pass filters");
            }
        }

        return $pass;
    }

    static public function rewritePhoneNumber($number, $config){
        //if search is not empty.
        if(! SmshareCommons::isNullOrEmptyString($config->get('smshare_cfg_number_rewriting_search'))) {
	
			//split rewrite search number by comma into an array
			$patternArr = explode(",", $config->get('smshare_cfg_number_rewriting_search'));
			
			foreach ($patternArr as $index => $pattern) {
				$pattern = trim($pattern);
				if(SmshareCommons::startsWith($number, trim($pattern))){
					return SmshareCommons::replace_first($number, $pattern, $config->get('smshare_cfg_number_rewriting_replace'));

				}
			}

		}

        return $number;
    }

}

SmshareCommons::init();

?>
