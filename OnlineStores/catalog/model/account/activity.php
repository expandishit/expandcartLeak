<?php
class ModelAccountActivity extends Model {

  
  public function getauctionproduct() {
		
		
		$sql = "SELECT pb.*,(pd.name) AS pname,(p.image) AS image FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_bid 
		pb ON (p.product_id = pb.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";
		
		$sql .= " WHERE pb.auction_status = 1 AND pb.bid_close_status=0 
		AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		   
		$query = $this->db->query($sql);
	
		return $query->rows;
	}	
	

	
	public function getactivitys($data = array()) {
		
		
		$sql = "SELECT pb.*,(pd.name) AS pname,(p.image) AS image FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "customer_bids 
		pb ON (p.product_id = pb.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";
		
		$sql .= " WHERE pb.customer_id = '" . (int)$this->customer->getId() . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		   
		$sort_data = array(
			'pb.date_added',
			'pb.price_bid',
			'pb.name'
		);
	
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY pb.date_added";	
		}
			
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}
		
	$config_limit= $this->config->get('config_max_popular_auctions');
		
	  if($config_limit){
	  
	      $data['start'] = 0;
			
			$data['limit'] = $config_limit;
			
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			
		}else{
		
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}			

			
			
			
			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

           			
			
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		
		}
		
		

		$query = $this->db->query($sql);
	
		return $query->rows;
	}	
		
	public function getTotalactivitys() {
	
      	$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "customer_bids` pb WHERE pb.customer_id = '" . (int)$this->customer->getId() . "'";
		
		
		$sort_data = array(
			'pb.date_added',
			'pb.price_bid',
			'pb.name'
		);
	
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY pb.date_added";	
		}
			
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}
		
		$config_limit= $this->config->get('config_max_popular_auctions');
		
	  if($config_limit){
	  
	      $data['start'] = 0;
			
			$data['limit'] = $config_limit;
			
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			
		}else{
		
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}			

			
			
			
			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

           			
			
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		
		}

		$query = $this->db->query($sql);
			
		return $query->row['total'];
	}

public function getTotalactivityss() {
      	$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_bids` WHERE customer_id = '" . (int)$this->customer->getId() . "'");
			
		if ($query->num_rows) {
			return $query->num_rows;
		} else {
			return 0;	
		}
	}	
			
	public function getTotalbids($pid) {
	
		
		$query = $this->db->query("SELECT  b_id  FROM `" . DB_PREFIX . "bids_history` WHERE customer_id = '" . (int)$this->customer->getId() . "' AND product_id = '" . (int)$pid . "' GROUP BY b_id");
		
		if ($query->num_rows) {
			return $query->num_rows;
		} else {
			return 0;	
		}
	}
	
	public function getbids($pid) {
	
		
		$query = $this->db->query("SELECT  *  FROM `" . DB_PREFIX . "bids_history` WHERE customer_id = '" . (int)$this->customer->getId() . "' AND product_id = '" . (int)$pid . "' GROUP BY b_id");
		
		if ($query->num_rows) {
			return $query->rows;
		} else {
			return '';	
		}
	}
	
	
	public function getTotalbids1($pid) {
	
		
		$query = $this->db->query("SELECT  b_id  FROM `" . DB_PREFIX . "bids_history` WHERE product_id = '" . (int)$pid . "' GROUP BY b_id");
		
		if ($query->num_rows) {
			return $query->num_rows;
		} else {
			return 0;	
		}
	}
	
	public function getbids1($pid) {
	
		
		$query = $this->db->query("SELECT  *  FROM `" . DB_PREFIX . "bids_history` WHERE product_id = '" . (int)$pid . "' GROUP BY b_id");
		
		if ($query->num_rows) {
			return $query->rows;
		} else {
			return '';	
		}
	}
}
?>