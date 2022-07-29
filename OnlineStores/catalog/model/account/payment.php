<?php
class ModelAccountPayment extends Model {	
	public function getpayments($data = array()) {
		$sql = "SELECT w.*,os.name AS status FROM " . DB_PREFIX . "winner w
        LEFT JOIN " . DB_PREFIX . "order_status os ON w.status = os.order_status_id 
		WHERE customer_id = '" . (int)$this->customer->getId() . "' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		   
		$sort_data = array(
			'w.price_bid',
			'w.name',
			'w.date_added'
		);
	
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY w.date_added";	
		}
			
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}
		
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}			

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
			
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);
	
		return $query->rows;
	}	
		
	public function getTotalpayments() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "winner` WHERE customer_id = '" . (int)$this->customer->getId() . "'");
			
		return $query->row['total'];
	}	
			
	public function getwinnerss() {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "winner` WHERE customer_id = '" . (int)$this->customer->getId() . "'");
		
		if ($query->num_rows) {
			return $query->num_rows;
		} else {
			return 0;	
		}
	}
}
?>