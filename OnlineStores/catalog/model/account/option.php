<?php
class ModelAccountOption extends Model {
	public function addoption($data) {

	    if(!isset($data['email_outbid'])){
		$data['email_outbid'] =0;
		
		}
		
		if(!isset($data['email_bid'])){
		$data['email_bid'] =0;
		
		}
		
		
		if(!isset($data['email_finish'])){
		$data['email_finish'] =0;
		
		}
		
		if(!isset($data['email_sub'])){
		$data['email_sub'] =0;
		
		}
		
		
			
      	$this->db->query("INSERT INTO " . DB_PREFIX . "option_setting 
		SET nickname = '" . $this->db->escape($data['nickname']) . "', 
		customer_id = '" . (int)$this->customer->getId() . "',
		new_bid_email = '" . (int)$data['email_bid']. "',
		outbidded_email = '" . (int)$data['email_outbid']. "',
	    finished_email = '" . (int)$data['email_finish']. "',
		subscribed_email = '" . (int)$data['email_sub']. "'
		");   	
		

      	
	}
	
	
	public function updateoption($data) {
	
	
	

	    if(!isset($data['email_outbid'])){
		$data['email_outbid'] =0;
		
		}
		
		if(!isset($data['email_bid'])){
		$data['email_bid'] =0;
		
		}
		
		
		if(!isset($data['email_finish'])){
		$data['email_finish'] =0;
		
		}
		
		if(!isset($data['email_sub'])){
		$data['email_sub'] =0;
		
		}
		
		
			
      	$this->db->query("UPDATE  " . DB_PREFIX . "option_setting 
		SET nickname = '" . $this->db->escape($data['nickname']) . "', 
		customer_id = '" . (int)$this->customer->getId() . "',
		new_bid_email = '" . (int)$data['email_bid']. "',
		outbidded_email = '" . (int)$data['email_outbid']. "',
	    finished_email = '" . (int)$data['email_finish']. "',
		subscribed_email = '" . (int)$data['email_sub']. "'
		WHERE customer_id = '" . (int)$this->customer->getId() . "'");  	
		

      	
	}
	
	
	public function selectoption() {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "option_setting` WHERE customer_id = '" . (int)$this->customer->getId() . "'");  
		
		if($query->num_rows){
		return $query->num_rows;
		}else{
		return '';
		
		}
	}

   public function getoption() {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "option_setting` WHERE customer_id = '" . (int)$this->customer->getId() . "'");  
		
		if($query->row){
		return $query->row;
		}else{
		return '';
		
		}
	}	
}
?>