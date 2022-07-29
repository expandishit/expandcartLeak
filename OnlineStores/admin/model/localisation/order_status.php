<?php 
class ModelLocalisationOrderStatus extends Model {
	public function addOrderStatus($data) {
		foreach ($data['order_status'] as $language_id => $value) {
			if (isset($order_status_id)) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_status SET order_status_id = '" . (int)$order_status_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
			} else {
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_status SET language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
				
				$order_status_id = $this->db->getLastId();
			}
		}
		
		$this->cache->delete('order_status');
		return $order_status_id;
	}

	public function editOrderStatus($order_status_id, $data) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_status_id . "'");

		foreach ($data['order_status'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "order_status SET order_status_id = '" . (int)$order_status_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}
				
		$this->cache->delete('order_status');
	}
	
	public function deleteOrderStatus($order_status_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_status_id . "'");
	
		$this->cache->delete('order_status');
	}
		
	public function getOrderStatus($order_status_id,$lang_id=null) {
		
		if(!$lang_id){
			$lang_id = $this->config->get('config_language_id') ? (int) $this->config->get('config_language_id') : 1;
		}
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_status_id . "' AND language_id = '{$lang_id}'");
		
		return $query->row;
	}

	public function getOrderStatusByLang($order_status_id, $lang_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_status_id . "' AND language_id = '{$lang_id}'");
		return $query->row;
	}
		
	public function getOrderStatuses($data = array()) {
        $language_id = $this->config->get('config_language_id') ?: 1;
      	if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "order_status WHERE language_id = '" . $language_id . "'";

			if (isset($data['orderby']) && ($data['orderby'] == 'order_status_id')) {
				
				$sql .= " ORDER BY order_status_id";	
			} else {
				$sql .= " ORDER BY name";	
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
		} else {
			$order_status_data = $this->cache->get('order_status.' . $language_id);
		
			if (!$order_status_data) {
				$query = $this->db->query("SELECT order_status_id, name FROM " . DB_PREFIX . "order_status WHERE language_id = '" . $language_id . "' ORDER BY name");
	
				$order_status_data = $query->rows;
			
				$this->cache->set('order_status.' . $language_id, $order_status_data);
			}	
	
			return $order_status_data;
		}
	}
	
	public function getOrderStatusDescriptions($order_status_id) {
		$order_status_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_status_id . "'");
		
		foreach ($query->rows as $result) {
			$order_status_data[$result['language_id']] = array('name' => $result['name']);
		}
		
		return $order_status_data;
	}
	
	public function getTotalOrderStatuses() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order_status WHERE language_id = '" . (int)$this->config->get('config_language_id') . "'");
		
		return $query->row['total'];
	}
        
        //ahmed
        public function handler($start, $length, $lang_id, $orderColumn, $orderType, $search = null) {
        $query = "select * from " . DB_PREFIX . "order_status where language_id='" . $lang_id . "' ";
        $query .= "and 1=1 ";
        $total = $this->db->query($query)->num_rows;
        if (!empty($search)) {
            $query .= "and name like '%{$search}%' ";
        }
        $totalFiltered = $this->db->query($query)->num_rows;
        $query .= " ORDER by {$orderColumn} {$orderType} LIMIT " . $start . " ," . $length;
        $data = array('data' => $this->db->query($query)->rows, 'total' => $total, 'totalFiltered' => $totalFiltered);

        return $data;
    }
    
    public function getOrderStatusLocales($order_status) {
        $query = "SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int) $order_status . "'";
        return $this->db->query($query)->rows;
    }
    
    public function insertOrderStatus($data) {
        foreach ($data as $nameWithLang) {
            if (isset($order_status_id)) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "order_status SET order_status_id = '" . (int) $order_status_id . "', language_id = '" . (int) $nameWithLang['language_id'] . "', name = '" . $this->db->escape($nameWithLang['name']) . "'");
            } else {
                $this->db->query("INSERT INTO " . DB_PREFIX . "order_status SET language_id = '" . (int) $nameWithLang['language_id'] . "', name = '" . $this->db->escape($nameWithLang['name']) . "'");

                $order_status_id = $this->db->getLastId();
            }
        }
    }
    
    public function updateOrderStatus($data, $id) {
        $this->deleteOrderStatus($id);
        $this->insertOrderStatus($data);
        $newId = $this->db->getLastId();
        $this->db->query("update ".DB_PREFIX."order_status set order_status_id='".$id."' where order_status_id='".$newId."'");
    }

    public function getOrdersByOrderStatusId( $order_status_id )
    {
        # code...
    }

    public function findOrderStatusByName($name){
    	return $this->db->query("SELECT `order_status_id` FROM `" . DB_PREFIX . "order_status` WHERE `name` = '" . $this->db->escape($name) ."' LIMIT 1;")->row['order_status_id'] ?: -1;
    }
}
?>
