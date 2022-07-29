<?php


class ModelModuleWhatsappCloudBusinessProfile extends Model {


	private static $adminToken = EC_WHATSAPP['ADMIN_TOKEN'];
	

	//========================== Graph API methods ================================//
	
	/*
	 * get Business Profile 
	 * 
	 * @return array
	 */
	public function getBusinessProfile(){
			
		$_url  = '/'.$this->config->get("whatsapp_cloud_phone_number_id") . '/whatsapp_business_profile';
		$_url .= '?fields=about,address,description,email,profile_picture_url,websites,vertical';
       
		$headers	= [];
		$headers[]	= "Authorization: Bearer ".self::$adminToken;
		$response 	= json_decode(WhatappCloudHelper::sendGraphApiRequest($_url,'GET',$headers,[]));
		
		return  $response->data[0] ?? [];
	}
	
	/*
	 * update Business Profile 
	 * 
	 * @parm array $data 
	 *
	 * @return object $response 
	 */
	public function updateBusinessProfile(array $data){
		
		$_url  = '/'.$this->config->get("whatsapp_cloud_phone_number_id") . '/whatsapp_business_profile';
       
	    $data ["messaging_product"]  = "whatsapp";
		
		$headers	= [];
		$headers[]	= "Authorization: Bearer ".self::$adminToken;
		$response 	= json_decode(WhatappCloudHelper::sendGraphApiRequest($_url,'POST',$headers,$data));
		
		return $response;
	}
	
	/*
	 * create upload session or init uploading 
	 * @parm array $data 
	 *
	 * @return object $response
	 * 
	 */
	public function createUploadSession(array $data){
				
		$_url  = "/app/uploads?access_token=" . self::$adminToken;
		
		if(isset($data["file_length"])){
			$_url  .= "&file_length={$data["file_length"]}";
		}
		
		if(isset($data["file_type"])){
			$_url  .= "&file_type={$data["file_type"]}";
		}
		
		if(isset($data["file_name"])){
			$_url  .= "&file_name={$data["file_name"]}";
		}
		
		
		$headers	= [];
		$headers[]	= "Authorization: Bearer ".self::$adminToken;
		$response 	= json_decode(WhatappCloudHelper::sendGraphApiRequest($_url,'POST',$headers,[]));
		return $response;
	}
	
	/*
	 * upload media method 
	 * @parm array $data 
	 *
	 * @return object $response
	 * 
	 */
	public function uploadFbMedia(array $data){
		
		$_url		 = 'https://graph.facebook.com/v13.0/'.$data["upload_session_id"];
		$file_offset = (int) ($data["file_offset"] ?? 0);

		if(isset($data["file_length"])){
			$_url  .= "&file_length={$data["file_length"]}";
		}
		
		if(isset($data["file_type"])){
			$_url  .= "&file_type={$data["file_type"]}";
		}
		
		if(isset($data["file_name"])){
			$_url  .= "&file_name={$data["file_name"]}";
		}
		

		$headers = [
					"Authorization: OAuth ".self::$adminToken,
					"cache-control: no-cache",
					"content-type: multipart/form-data",
					"file_offset: ".$file_offset
					];
					
		
		$file_local_full = $data['file_path'] ?? '';


		$filesize = filesize($file_local_full);
		$stream = fopen($file_local_full, 'r');

		$curl_opts = array(
			CURLOPT_URL 			=> $_url,
			CURLOPT_RETURNTRANSFER 	=> true,
			CURLOPT_PUT 			=> true,
			CURLOPT_CUSTOMREQUEST 	=> "POST",
			CURLOPT_HTTPHEADER 		=> $headers,
			CURLOPT_INFILE 			=> $stream,
			CURLOPT_INFILESIZE 		=> $filesize,
			CURLOPT_HTTP_VERSION 	=> CURL_HTTP_VERSION_1_1
		);

		$curl = curl_init();
		curl_setopt_array($curl, $curl_opts);
		$response = curl_exec($curl);
		fclose($stream);

		if (curl_errno($curl)) {
			$error_msg = curl_error($curl);
			throw new \Exception($error_msg);
		}
		curl_close($curl);
		
		WhatappCloudHelper::developerLog("Request : ".$_url."\n => Response : ".$response);
		return json_decode($response);
	}
	
	/*
	 *
	 * @parm string $upload_session_id 
	 *
	 * @return object response
	 * 
	 */
	public function uploadStatus(string $upload_session_id = null){
		
		$_url  = $upload_session_id;
		
		$headers	= [];
		
		//according to documentation this endpoint require OAuth  instead of bearer 
		$headers[]	= "Authorization: OAuth ".self::$adminToken;
		$response 	= json_decode(WhatappCloudHelper::sendGraphApiRequest($_url,'GET',$headers));
		return $response;
	}

	
	
}
?>
