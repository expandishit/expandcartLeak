<?php


class ModelModuleWhatsappCloudChat extends Model {

	
	private static 	$chatsTable		  = DB_PREFIX . 'whatsapp_cloud_chats',
					$messagesTable	  = DB_PREFIX . 'whatsapp_cloud_messages',
					$dbCharsetUpdated = false;

	
	/*
	 *
	 * @return array  
	 * 
	 */
	public function getAllChats(){
		
		$this->DBCharsetUpdate();
		
		$sql	= "SELECT wm.text,wm.type,wm.fb_status,wm.fb_message_id,wc.*
					FROM `" . self::$messagesTable ."` wm 
					JOIN `". self::$chatsTable ."` wc 
					ON wc.id = wm.chat_id
					WHERE  wm.id IN (select max(id) from  `". self::$messagesTable ."`
					group by chat_id)
					ORDER BY wc.last_timestamp DESC ;";

		$result = $this->db->query($sql);

		return $result->num_rows > 0 ? $result->rows : [];
	}
	
	/*
	 * @parm int $id 
	 *
	 * @return array 
	 * 
	 */
	public function getChat(int $id = 0){
		
		$this->DBCharsetUpdate();
		
		$sql	= "SELECT * FROM `". self::$chatsTable ."` WHERE  `id` = '{$id}';";

		$result = $this->db->query($sql);

		return $result->num_rows > 0 ? $result->row : [];
	}
	
	/*
	 * @parm array $data 
	 * @parm int   $increase_unread 
	 *
	 * @return int  
	 * 
	 */
	public function insertUpdateChat(array $data = [],$increase_unread=false){
		
		$default_unread = $increase_unread ? 1 :  0;
	
		$unread_count = $data['unread_count']?? $default_unread;
		
		$query   = [];
        $query[] = 'INSERT INTO `' . self::$chatsTable . '` SET';
		$query[] = ' `profile_name` 	= "' . $this->db->escape($data['profile_name']??'') . '",';
		$query[] = ' `phone_number`  	= "' . $this->db->escape($data['phone_number']??'') .'",';
		$query[] = ' `last_timestamp` 	= NOW(),';
		$query[] = ' `unread_count` 	= "' . $this->db->escape($unread_count).'"';
		$query[] = '  ON DUPLICATE KEY UPDATE ';
		
		if($increase_unread){
			$query[] = ' `unread_count` 	= unread_count+1 ,';
		}
		
		$query[] = ' `last_timestamp` 	= NOW() ';
		 
		$this->db->query(implode(' ', $query));
		return $this->db->getLastId();
	}
	

	/*
	 * @parm int   $id 
	 * @parm array $data 
	 *
	 * @return int  
	 * 
	 */
	public function updateChat(int $id , array $data = []){
		
		if(empty($data))
			return false ;
		
		$columns  = [] ;
		foreach ($data as $key => $value ){
			$columns[] = "`${key}` = '".$this->db->escape($value) ."'";
		}
				
		$query = 'UPDATE `' . self::$chatsTable . '` SET '. implode(', ', $columns) . " WHERE id = '$id'";
		
		$this->db->query($query);
		
		return $this->db->getLastId();
	}
	
	/*
	 *
	 * @return void 
	 *
	 */
	public function createChatsTable(){
		
		$sql = "CREATE TABLE IF NOT EXISTS " .self::$chatsTable . " (
					  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					  `profile_name` varchar(100) DEFAULT NULL,
					  `phone_number` varchar(20)  NOT NULL,
					  `unread_count` int(11) NOT NULL DEFAULT 0,
					  `last_timestamp` timestamp NULL DEFAULT NULL,
					   PRIMARY KEY (`id`),
					   UNIQUE (`phone_number`)
					  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
		 
		 $this->db->query($sql);
	}
	
	/*
	 *
	 * @return void 
	 *
	 */
	public function dropChatsTable(){;
		
		$this->db->query("DROP TABLE  IF EXISTS ". self::$chatsTable);
	}	

	/*
	 *
	 * to update the DB connection to use utf8mb4 for emoji support 
	 * needed only at messages read/write
	 *
	 */
	public  function DBCharsetUpdate(){
		
		if(!self::$dbCharsetUpdated){
			$this->db->query("SET CHARACTER SET utf8mb4");
			self::$dbCharsetUpdated = true;
		}
	}
	
}
?>
