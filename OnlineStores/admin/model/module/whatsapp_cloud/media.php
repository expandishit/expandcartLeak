<?php


class ModelModuleWhatsappCloudMedia extends Model {


	private static  $mediasTable  = DB_PREFIX . 'whatsapp_cloud_medias',
					$adminToken	  = EC_WHATSAPP['ADMIN_TOKEN'];

	

	//========================== Graph API methods ================================//
	
	
	/**
	 * send Media  message 
	 *
	 * @param String $to 
	 * @param String $Media_type  
	 * @param media_data array 
	 *  - id  [required when no link set ] 
	 *  - link [required when no id set ] 
	 *  - caption  optional[dont use with audio ] 
	 *  - filename optional[ Use only with document media]
	 *
	 * @return array "array of objects"
	 */
	public function sendMediaMessage($to,$media_type,$media_data){
		
		if(!in_array($media_type,['image','audio','document'])){
			return false;
		}
		
		$this->load->model('module/whatsapp_cloud/message');

		$message_data				= [];
		$message_data['type']		= $media_type;
		$message_data[$media_type]  = $media_data;

			
		$responses = [];
		
		if(is_array($to)){
			
			$numbers = $to ; 
			
			foreach ($numbers as $number){
				$responses[] = $this->model_module_whatsapp_cloud_message->sendMessage($number,$message_data);
			}
		//$this->developerLog("[fnc]" . __function__ . " | multiple-numbers | data " . json_encode($message_data). ' to=> ' . json_encode($to) . ' responses => ' . json_encode($responses));
		}else {
			$responses[] = $this->model_module_whatsapp_cloud_message->sendMessage($to,$message_data);
		//$this->developerLog("[fnc]" . __function__ . " | data " . json_encode($message_data). ' to=> ' . $to . ' response => ' . $response );
		}
		
		return $responses;
	}
	
	/**
	 * Upload Media  message 
	 *
	 * @param CurlFile object  $file 
	 *
	 * @return sendGraphApiRequest response
	 */
	public function uploadMedia($file){
		
		$phone_number_id 	= $this->config->get('whatsapp_cloud_phone_number_id');
		
		$_url 	= $phone_number_id. '/media';
		
		$data   = [
					'messaging_product' => 'whatsapp', 
					'file'				 => $file 
					];
        
		$headers = [
					"Authorization: Bearer " . self::$adminToken,
					"cache-control: no-cache",
					"content-type: multipart/form-data"
					];
					
		$response = json_decode(WhatappCloudHelper::sendGraphApiRequest($_url,'POST',$headers, $data,false));
		
		return $response;
	}
	
	/*
	 *
	 * @parm string $media_id 
	 *
	 * @return 
	 * 
	 */
	public function getMediaUrl(string $media_id ){
				
		$_url  		= "/" . $media_id;
		
		$headers	= [
						"Authorization: Bearer " . self::$adminToken
						];
		
		$response 	= json_decode(WhatappCloudHelper::sendGraphApiRequest($_url,'GET',$headers));
		
		return $response;
	}
	
	/*
	 *
	 * @parm string $media_id 
	 *
	 * @return 
	 * 
	 */
	public function downloadFbMedia(string $media_id){
		
		$media_data = $this->getMediaUrl($media_id);
		
		if(property_exists($media_data,"url")){
			$extension 	= trim(explode("/",explode(" ",$media_data->mime_type)[0])[1],';');
			$save_to 	= 'whatsapp/' . 'm_'.uniqid() . '.'.$extension;
			
			$downloaded = $this->grabImage($media_data->url,$save_to);
			
			if($downloaded)
				return $save_to;
		}
			
		return false ;
	}
	
	/*
	 *
	 * @parm string $url 
	 * @parm string $saveto 
	 *
	 * @return 
	 * 
	 */
	public function grabImage(string $url,string  $saveto) : bool {
		
		set_time_limit(0);
		$ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 400);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_CUSTOMREQUEST , "GET");
		curl_setopt($ch,CURLOPT_ENCODING , "");
		//curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);

		$headers	= [];
		$headers[]	= "Authorization: Bearer " . self::$adminToken;
		$headers[]	= "Accept-Language:en-US,en;q=0.5";
		$headers[]	= "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $raw = curl_exec($ch);
		
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if((int)$httpcode == 200){
			if (\Filesystem::isExists($saveto)) {
				\Filesystem::deleteFile($saveto);
			}
			
			\Filesystem::setPath($saveto)->put($raw);
			
			return true ;
		}
		
		return false;
	}

	//========================== internal DB Methods ================================//

	/*
	 * @parm int $media_id 
	 *
	 * @return array 
	 * 
	 */
	public function getMedia(int $id = 0){

		$sql	= "SELECT * FROM `". self::$mediasTable  ."`  WHERE id = '{$id}'";
		
		$result = $this->db->query($sql);
		
		return $result->num_rows > 0 ? $result->row : [];
	}
	
	/*
	 * @parm array $data 
	 *
	 * @return int 
	 * 
	 */
	public function insertMedia(array $data=[]){

		$query   = [];
        $query[] = 'INSERT INTO `' . self::$mediasTable . '` SET ';
		$query[] = ' `media`  		  	= "' . $this->db->escape($data['media']??'') 		. '",';
		$query[] = ' `url`   	  		= "' . $this->db->escape($data['url']?? "")		    . '",';
		$query[] = ' `fb_media_id`    = "' . $this->db->escape($data['fb_media_id']??"")	. '",';
		$query[] = ' `created_at`  	  = NOW()';
		$query[] = '  ON DUPLICATE KEY UPDATE ';
		$query[] = ' `media`  		  	= "' . $this->db->escape($data['media']??'') 		. '",';
		$query[] = ' `url`   	  		= "' . $this->db->escape($data['url']?? "")		    . '",';
		$query[] = ' `fb_media_id`      = "' . $this->db->escape($data['fb_media_id']??"")	. '",';	
		$query[] = ' `updated_at` = NOW()';	
		$this->db->query(implode(' ', $query));
		return $this->db->getLastId();
	}
	
	/*
	 * @parm int $id 
	 * @parm array $data 
	 *
	 * @return int 
	 * 
	 */
	public function updateMedia(int $id,array $data=[]){

		$query   = [];
        $query[] = 'UPDATE `' . self::$mediasTable . '` SET ';
		
		foreach ($data as $key => $value ){
			$query[] = " `${key}`  	= '" . $this->db->escape($value) . "',";
		}
		
		$query[] = " `updated_at`  	  = NOW()";
		$query[] = " WHERE id = '${id}' ";
		
		$this->db->query(implode(' ', $query));
		return $this->db->getLastId();
	}

	/*
	 *
	 * @return void 
	 *
	 */
	public function createMediasTable(){
		$sql = "CREATE TABLE IF NOT EXISTS ".self::$mediasTable . " (
					  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					  `media` JSON DEFAULT NULL,
					  `fb_media_id` bigint(20) NOT NULL,
					  `url` varchar(100) DEFAULT NULL,
					  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
					  `updated_at` timestamp NULL DEFAULT NULL,
					  PRIMARY KEY (`id`),
					   UNIQUE (`fb_media_id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
		 
		$this->db->query($sql);
	}
	
	/*
	 *
	 * @return void 
	 *
	 */
	public function dropMediasTable(){
		$this->db->query("DROP TABLE  IF EXISTS ". self::$mediasTable);
	}
	
}
?>
