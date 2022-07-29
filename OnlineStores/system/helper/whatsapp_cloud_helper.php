<?php

class WhatappCloudHelper {
	
	private static	$apiVersion		 = "v13.0",
					$apiUrl			 = "https://graph.facebook.com",
					$instance		 = false,
					$clientLogger	 = false,
					$developerLogger = false;
	
	
	/**
	 * @param $rows
	 *
	 * @return int|string
	 */
	public static function getOrderTotal($order_totals){
		
		foreach ($order_totals as $row) {
            $_title = $row['code'];    //was 'title', but title was translated to Arabic, so it won't match on 'Total'

            if($_title === 'total'){
                return html_entity_decode($row['text'], ENT_NOQUOTES, 'UTF-8');
            }
        }
        return 0;
	}

	/**
	 * @param  string $number
	 * @param  string $patterns
	 *
	 * @return bool
	 */
	static public function isNumberAuthorized($number, $patterns){

        //check that the number is not null and is not empty
        if(!isset($number) || trim($number)===''){
            return false;
        }
		
        //if no patterns return true
        if(!isset($patterns) || trim($patterns)===''){
            return true;
        }

        //split patterns by comma into an array
        $patternArr = explode(",", $patterns);

        foreach ($patternArr as $pattern) {
            if(self::startsWith($number, trim($pattern))){
                return true;
            }
        }

        return false;
    }
	
	/**
	 * @param  string $haystack
	 * @param  string $needle
	 *
	 * @return bool
	 */
	static public function startsWith($haystack, $needle){
	    return !strncmp($haystack, $needle, strlen($needle));
	}
	
	/**
	 * @param  string $haystack
	 * @param  string $needle
	 * @param  int 	  $idx
	 *
	 * @return string
	 */
    static public function replaceFirst($haystack, $needle, $replace, $idx = 0){
		
		$pos = strpos($haystack, $needle);
		if ($pos === 0) {
			$patternArr = explode(',', $replace);
		    $stringN = substr_replace($haystack, trim($patternArr[$idx]),$pos,strlen($needle));

			return $stringN;
		}
	    return $haystack;
	}

	/**
	 * Method For Sending Facebook Graph API Request 
	 *
	 * @param String $_url   #API route 
	 * @param String $request_type   <POST | GET | PUT | DELETE >   
	 * @param Array $headers  
	 * @param Array $data  
	 * @param bool $json_encode  # for automattically convert request body to json 
	 * 
	 * @return Json Object
	 *
	 */
	static public function sendGraphApiRequest($_url,$request_type='POST',$headers=[], $data=[] , $json_encode= true){
		
		$api_url  = self::$apiUrl .'/' . self::$apiVersion .'/'. $_url;
		
		return self::sendRequest([
									'url' 		  => $api_url , 
									'type'		  => $request_type , 
									'headers'	  => $headers, 
									'data'		  => $data,
									'json_encode' => $json_encode
									]);
	}

	/**
	 * Method For Sending Curl Requests 
	 *
	 * @param array $request
	 * @return Json Object
	 *
	 */
	static public function sendRequest($request){
		
		$request_url     = $request['url']		  ?? "";
		$type 	 		 = $request['type']		  ?? "POST";
		$headers 		 = $request['headers']	  ?? [];
		$request_data 	 = $request['data']		  ?? [];
		$json_encode 	 = $request['json_encode']?? false;

		$soap_do     = curl_init();
        curl_setopt($soap_do, CURLOPT_URL, $request_url);
        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($soap_do, CURLOPT_TIMEOUT, 120);
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($soap_do, CURLOPT_ENCODING, true);
        curl_setopt($soap_do, CURLOPT_CUSTOMREQUEST, $type);

		
		
		if(
			in_array(strtoupper($type),['POST','PUT','PATCH','DELETE'])
			&& !empty($request_data)
		){
			if($json_encode){
				$request_data 	= json_encode($request_data);
				$length 	  	= strlen($request_data);
				$headers[]		= "Content-Type: application/json";
				$headers[]		= "Content-Length: $length";
			}
			
			curl_setopt($soap_do, CURLOPT_POSTFIELDS, $request_data);
		}

		curl_setopt($soap_do, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($soap_do);

		//log here controlled by config 
		self::developerLog("Request : ".json_encode($request)."\n => Response : ".$response);
		
		return $response;
	}

	/**
	 * Method to handle number & try to convert it to international number 
	 *
	 * @param String $phone_number
	 * @param String $country_iso_2
	 *
	 * @return String
	 */
	public static function toInternationalPhoneNumber(string $phone_number = null , string $country_iso_2 = null ){
               
		if (!$phone_number || !$country_iso_2) return $phone_number;

		$phone_util = \libphonenumber\PhoneNumberUtil::getInstance();
		
		try {
			
			$number_proto = $phone_util->parse($phone_number, strtoupper($country_iso_2));
			$phone_number = $phone_util->format($number_proto, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
		    $phone_number = str_replace(" ","",$phone_number);
		
		} catch (\libphonenumber\NumberParseException $e) {
			return $phone_number;
		}
				
        return $phone_number;
    }
	
	/**
	 * user freindly logs 
	 *
	 * @param String $text
	 * @return void
	 */
	public static function clientLog($text){
		
		if(!defined('WHATSAPP_CLOUD_CLIENT_LOG')){
			//at current time the default will be enabling it till the stability of the APP 
			define('WHATSAPP_CLOUD_CLIENT_LOG',True); 
		}
		
		if(!self::$clientLogger)
			self::$clientLogger =  new \Log('whatsapp_cloud_client_logs');

		if (WHATSAPP_CLOUD_CLIENT_LOG) {
                self::$clientLogger->write($text);
            }
	}
	
	/**
	 * developer logs for debuging prepose 
	 *
	 * @param String $text
	 * @return void
	 */
	public static function developerLog($text){
		
		if(!defined('WHATSAPP_CLOUD_DEVELOPER_LOG')){
			//at current time the default will be enabling it till the stability of the APP 
			define('WHATSAPP_CLOUD_DEVELOPER_LOG',True); 
		}
		
		if(!self::$developerLogger)
			self::$developerLogger =  new \Log('whatsapp_cloud_developer_logs');
		
		if (WHATSAPP_CLOUD_DEVELOPER_LOG) {
            self::$developerLogger->write($text);
        }
	}
	
}


?>
