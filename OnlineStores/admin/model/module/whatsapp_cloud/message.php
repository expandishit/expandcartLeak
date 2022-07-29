<?php


class ModelModuleWhatsappCloudMessage extends Model {
	
	
	private static 	$chatsTable 			= DB_PREFIX . 'whatsapp_cloud_chats',
					$messagesTable 			= DB_PREFIX . 'whatsapp_cloud_messages',
					$mediasTable 			= DB_PREFIX . 'whatsapp_cloud_medias',
					$messageTemplatesTable 	= DB_PREFIX . 'whatsapp_cloud_message_templates',
					$messageTrackingTable 	= 'whatsapp_messages_tracking',
					$dbCharsetUpdated		= false,
					$adminToken 			= EC_WHATSAPP['ADMIN_TOKEN'];
	
	
	//========================== Graph API methods ================================//

	/**
	 * send Message 
	 *
	 * @param String $to 
	 * @param message_data array 
	 *
	 * @return sendGraphApiRequest response [json response]
	 *
	 */
	public function sendMessage($to,array $message_data){

		$phone_number_id 	= $this->config->get('whatsapp_cloud_phone_number_id');
		$_url = $phone_number_id. '/messages';
		
		$data = $message_data;
		
		$data['to'] = $to; 
		$data['messaging_product'] = 'whatsapp'; 
	 
		$headers = [
					"Authorization: Bearer ".self::$adminToken,
					"Content-Type: application/json;charset=utf-8"
				];
			
		$response	= json_decode(WhatappCloudHelper::sendGraphApiRequest($_url,'POST',$headers, $data));
		
		$track_type = $message_data['type'] == 'template' ? 'notification' : 'message' ;
		
		//
		$this->_trackEvent($track_type, $to);
		
		return $response;
	}
	
	//========================== internal DB Methods ================================//
	
	/*
	 * @parm int $chat_id 
	 * @parm int $limit 
	 * @parm int $page 
	 *
	 * @return array
	 * 
	 */
	public function getMessages(int $chat_id,int $limit=10,int $page=1){
		
		$this->DBCharsetUpdate();
		
		$offset = ($limit * $page ) - $limit ;	
		$sql	= "SELECT wm.*,wm2.media,wm2.url,wt.template FROM `". self::$messagesTable  ."` wm  
					LEFT JOIN `".self::$mediasTable ."` wm2 
							ON wm.media_id = wm2.id 
					LEFT JOIN `".self::$messageTemplatesTable ."` wt
							ON wt.message_id = wm.id
					WHERE wm.chat_id = '{$chat_id}' 
					ORDER BY wm.id desc
					limit {$limit} offset {$offset}";
		
		$result = $this->db->query($sql);
		
		return $result->num_rows > 0 ? $result->rows : [];
	}
	
	/*
	 * @parm int $id 
	 *
	 * @return array
	 * 
	 */
	public function getMessage(int $id = 0){
		
		return $this->getMessageFilter(["id"=>$id]);
	}
	
	/*
	 * @parm int $id 
	 *
	 * @return array
	 * 
	 */
	public function getMessageFilter ($filters = []){
		
		$this->DBCharsetUpdate();
		
		$filter_fields = ['id','fb_message_id'];
		
		
		$sql	= "SELECT wm.*,wm2.media,wm2.url FROM `". self::$messagesTable  ."` wm  
					LEFT JOIN `".self::$mediasTable ."` wm2 
					ON wm.media_id = wm2.id
					WHERE 1=1 ";
		
		foreach ($filters as $field => $value){
			if(in_array($field,$filter_fields))
				$sql .= "AND wm.${field} = '" . $this->db->escape($value) . "' " ;
		}
		
		$result = $this->db->query($sql);
		
		$data = [] ;
		
		if($result->num_rows > 1)
			$data  =  $result->rows; 
		
		else if($result->num_rows > 0)
			$data  =  $result->row; 
		
		return $data;
	}
	
	/*
	 * @parm int $chat_id 
	 * @parm int $id 
	 *
	 * @return array
	 * 
	 */
	public function getMessagesAfter(int $chat_id = 0,int $id = 0){
		
		$this->DBCharsetUpdate();
		
		$offset = ($limit * $page ) - $limit ;	
		$sql	= "SELECT wm.*,wm2.media,wm2.url FROM `". self::$messagesTable  ."` wm  
						LEFT JOIN `".self::$mediasTable ."` wm2 
							ON wm.media_id = wm2.id
							WHERE wm.chat_id = '$chat_id' 
							and wm.id > '$id'
							ORDER BY wm.id desc";
		
		$result = $this->db->query($sql);
		
		return $result->num_rows > 0 ? $result->rows : [];
	}

	/*
	 * @parm int $chat_id 
	 *
	 * @return 
	 * 
	 */
	public function readMessages (int $chat_id = 0){
		
		$sql	= "UPDATE `". self::$chatsTable ."` SET `unread_count` = '0' WHERE `id` = '{$chat_id}'";
					
		$result = $this->db->query($sql);
		
		return $result;
	}

	/*
	 * @parm array $data 
	 *
	 * @return int 
	 * 
	 */
	public function insertMessage(array $data=[]){
		
		$this->DBCharsetUpdate();
		
		$query   = [];
        $query[] = 'INSERT INTO `' . self::$messagesTable . '` SET ';
		$query[] = ' `text`  		  = "' . $this->db->escape($data['text']??'') 				. '",';
		
		if(isset($data['fb_timestamp']) && !empty($data['fb_timestamp']))
			$query[] = ' `fb_timestamp`   = "' . $this->db->escape($data['fb_timestamp']) 		. '",';
		else 
			$query[] = ' `fb_timestamp`   = NOW() ,';

		if(isset($data['media_id']) && !empty($data['media_id']))
			$query[] = ' `media_id`   = "' . $this->db->escape($data['media_id']) 		. '",';
		
		$query[] = ' `type`  		  = "' . $this->db->escape($data['type']??'text') 			. '",';
		$query[] = ' `fb_status`  	  = "' . $this->db->escape($data['fb_status']??'sent')  . '",';
		$query[] = ' `from_me`  	  = "' . $this->db->escape($data['from_me']??'0') 			. '",';
		
		if(!empty($data['fb_message_id']??'')){
			$query[] = ' `fb_message_id`  = "' . $this->db->escape($data['fb_message_id']??'') 		. '",';
		}
		$query[] = ' `chat_id`  	  = "' . $this->db->escape($data['chat_id']) 		. '",';
		$query[] = ' `created_at`  	  = NOW()';
		
		$query[] = '  ON DUPLICATE KEY UPDATE ';
		$query[] = ' `text`  		  = "' . $this->db->escape($data['text']??'') 				. '",';
		
		if(isset($data['media_id']) && !empty($data['media_id']))
			$query[] = ' `media_id`   = "' . $this->db->escape($data['media_id']) 		. '",';
		
		if(isset($data['fb_timestamp']) && !empty($data['fb_timestamp']))
			$query[] = ' `fb_timestamp`   = "' . $this->db->escape($data['fb_timestamp']) 		. '",';
		else 
			$query[] = ' `fb_timestamp`   = NOW() ,';
		
		$query[] = ' `type`  	  = "' . $this->db->escape($data['type']??'text') 			. '",';
		$query[] = ' `fb_status`  = "' . $this->db->escape($data['fb_status']??'sent')  . '",';
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
	public function updateMessage(int $id,array $data=[]){
		
		$this->DBCharsetUpdate();
		
		$query   = [];
        $query[] = 'UPDATE `' . self::$messagesTable . '` SET ';
		
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
	 * to update the DB connection to use utf8mb4 for emoji support 
	 * needed only at messages read/write
	 *
	 */
	public  function DBCharsetUpdate(){
		
		if(!self::$dbCharsetUpdated){
			$this->db->query("SET NAMES 'utf8mb4'");
			$this->db->query("SET CHARACTER_SET_CONNECTION=utf8mb4");
			self::$dbCharsetUpdated = true;
		}
	}
		
	/*
	 *
	 * @return void 
	 *
	 */
	public function createMessagesTable(){
		
		//read, delivered, sent, failed, deleted
		
		$sql  = "CREATE TABLE IF NOT EXISTS `" .self::$messagesTable . "` (
					  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					  `text` text DEFAULT NULL,
					  `fb_timestamp` timestamp NULL DEFAULT NULL,
					  `type` enum('text','image','audio','video','template','unknown','button','system') DEFAULT 'text',
					  `fb_status` enum('read','delivered', 'sent','failed','deleted') NULL DEFAULT NULL,
					  `from_me` tinyint(1) DEFAULT 0,
					  `fb_message_id` varchar(100) DEFAULT NULL,
					  `media_id` int(11) UNSIGNED  NULL ,
					  `chat_id` int(11) UNSIGNED NOT NULL ,
					  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
					  `updated_at` timestamp NULL DEFAULT NULL,
					   PRIMARY KEY (`id`),
					   FOREIGN KEY (`media_id`) REFERENCES ".self::$mediasTable ."(`id`),
					   FOREIGN KEY (`chat_id`) REFERENCES ".self::$chatsTable ."(`id`),
					   UNIQUE (`fb_message_id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
					
		$this->db->query($sql);
	}

	/*
	 *
	 * @return void 
	 *
	 */
	public function dropMessagesTable(){
		$this->db->query("DROP TABLE  IF EXISTS ". self::$messagesTable);
	}
	
	/**
	 * Method For tracking Store Usage | used at Ectools Reports 
	 *
	 * @param String $type  < notification | Message > 
	 * @param String $to
	 *
	 * @return Void
	 */
	private function _trackEvent(string $type,string  $to){
		$sql  = "INSERT INTO `" . self::$messageTrackingTable ;
		$sql .= "` (`type`, `store_code`, `to`) VALUES ('{$type}', '" . STORECODE ."', '{$to}')";
    	$this->ecusersdb->query($sql);
    }

}
?>
