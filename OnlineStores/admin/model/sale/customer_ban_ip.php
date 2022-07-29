<?php
class ModelSaleCustomerBanIp extends Model {

    public function dtHandler($start=0, $length=10, $search = null, $orderColumn="name", $orderType="ASC")
    {
        $query = "SELECT * FROM " . DB_PREFIX . "customer_ban_ip";
        //$query = ;
        $total = $totalFiltered = $this->db->query($query)->num_rows;
        $where = "";
        if (!empty($search)) {
            $where .= "(customer_ban_ip.customer_ban_ip_id like '%" . $this->db->escape($search) . "%')";
            $query .= " WHERE " . $where;
            $totalFiltered = $this->db->query($query)->num_rows;
        }

        $query .= " ORDER by {$orderColumn} {$orderType}";

        if($length != -1) {
            $query .= " LIMIT " . $start . ", " . $length;
        }
        //$data = array_merge($this->db->query($query)->rows, array($totalFiltered));

        $results = $this->db->query($query)->rows;

        foreach ($results as $key => $result)
        {
        	// Grab the total customers for each ip.
        	$results[$key]['total_customers'] = $this->db->query("SELECT COUNT(DISTINCT customer_id) AS total FROM " . DB_PREFIX . "customer_ip WHERE customer_ip.ip = '{$result['ip']}'")->row['total'];
        }

        $data = array (
            'data' => $results,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        );
        //$data = $this->db->query($query)->rows;
        return $data;
    }

	public function addCustomerBanIp($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_ban_ip` SET `ip` = '" . $this->db->escape($data['ip']) . "'");
	}
	
	public function editCustomerBanIp($customer_ban_ip_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "customer_ban_ip` SET `ip` = '" . $this->db->escape($data['ip']) . "' WHERE customer_ban_ip_id = '" . (int)$customer_ban_ip_id . "'");
	}
	
	public function deleteCustomerBanIp($customer_ban_ip_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_ban_ip` WHERE customer_ban_ip_id = '" . (int)$customer_ban_ip_id . "'");
		return True;
	}
	
	public function getCustomerBanIp($customer_ban_ip_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_ban_ip` WHERE customer_ban_ip_id = '" . (int)$customer_ban_ip_id . "'");
	
		return $query->row;
	}
	
	public function getCustomerBanIps($data = array()) {
		$sql = "SELECT *, (SELECT COUNT(DISTINCT customer_id) FROM `" . DB_PREFIX . "customer_ip` ci WHERE ci.ip = cbi.ip) AS total FROM `" . DB_PREFIX . "customer_ban_ip` cbi";
				
		$sql .= " ORDER BY `ip`";	
			
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
	
	public function getTotalCustomerBanIps($data = array()) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "customer_ban_ip`");
				 
		return $query->row['total'];
	}
}
?>