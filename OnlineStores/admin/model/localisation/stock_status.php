<?php 
class ModelLocalisationStockStatus extends Model {
	public function addStockStatus($data) {
		foreach ($data['stock_status'] as $language_id => $value) {
			if (isset($stock_status_id)) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "stock_status SET stock_status_id = '" . (int)$stock_status_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
			} else {
				$this->db->query("INSERT INTO " . DB_PREFIX . "stock_status SET language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
				
				$stock_status_id = $this->db->getLastId();
			}
		}
		
		$this->cache->delete('stock_status');
	}

	public function editStockStatus($stock_status_id, $data) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "stock_status WHERE stock_status_id = '" . (int)$stock_status_id . "'");

		foreach ($data['stock_status'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "stock_status SET stock_status_id = '" . (int)$stock_status_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}
				
		$this->cache->delete('stock_status');
	}
	
	public function deleteStockStatus($stock_status_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "stock_status WHERE stock_status_id = '" . (int)$stock_status_id . "'");
	
		$this->cache->delete('stock_status');
	}
		
	public function getStockStatus($stock_status_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "stock_status WHERE stock_status_id = '" . (int)$stock_status_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
		
		return $query->row;
	}
	
	public function getStockStatuses($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "stock_status WHERE language_id = '" . (int)$this->config->get('config_language_id') . "'";
      		
			$sql .= " ORDER BY name";	
			
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
			$stock_status_data = $this->cache->get('stock_status.' . (int)$this->config->get('config_language_id'));
		
			if (!$stock_status_data) {
				$query = $this->db->query("SELECT stock_status_id, name, default_color, current_color FROM " . DB_PREFIX . "stock_status WHERE language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY name");
	
				$stock_status_data = $query->rows;
			
				$this->cache->set('stock_status.' . (int)$this->config->get('config_language_id'), $stock_status_data);
			}	
	
			return $stock_status_data;			
		}
	}
	
	public function getStockStatusDescriptions($stock_status_id) {
		$stock_status_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "stock_status WHERE stock_status_id = '" . (int)$stock_status_id . "'");
		
		foreach ($query->rows as $result) {
			$stock_status_data[$result['language_id']] = array('name' => $result['name']);
		}
		
		return $stock_status_data;
	}
	
	public function getTotalStockStatuses() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "stock_status WHERE language_id = '" . (int)$this->config->get('config_language_id') . "'");
		
		return $query->row['total'];
	}
        
         //ahmed
    public function handler($start, $length, $lang_id, $orderColumn, $orderType, $search = null) {
        $query = "select * from " . DB_PREFIX . "stock_status where language_id='" . $lang_id . "' ";
        $query .= "and 1=1 ";
        $total = $this->db->query($query)->num_rows;
        if (!empty($search)) {
            $query .= "and name like '%" . $this->db->escape($search) . "%' ";
        }
        $totalFiltered = $this->db->query($query)->num_rows;
        $query .= " ORDER by {$orderColumn} {$orderType} LIMIT " . $start . " ," . $length;
        $data = array('data' => $this->db->query($query)->rows, 'total' => $total, 'totalFiltered' => $totalFiltered);

        return $data;
    }

    public function getStockStatusLocales($stock_status) {
        $query = "SELECT * FROM " . DB_PREFIX . "stock_status WHERE stock_status_id = '" . (int) $stock_status . "'";
        return $this->db->query($query)->rows;
    }

    public function insertStockStatus($data) {
        foreach ($data as $nameWithLang) {
            if (isset($stock_status_id)) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "stock_status SET stock_status_id = '" . (int) $stock_status_id . "', language_id = '" . (int) $nameWithLang['language_id'] . "', name = '" . $this->db->escape($nameWithLang['name']) . "'");
            } else {
                $this->db->query("INSERT INTO " . DB_PREFIX . "stock_status SET language_id = '" . (int) $nameWithLang['language_id'] . "', name = '" . $this->db->escape($nameWithLang['name']) . "'");

                $stock_status_id = $this->db->getLastId();
            }
        }
    }
    
    public function updateStockStatus($data, $id) {
        $this->deleteStockStatus($id);
        $this->insertStockStatus($data);
        $newId = $this->db->getLastId();
        $this->db->query("update ".DB_PREFIX."stock_status set stock_status_id='".$id."' where stock_status_id='".$newId."'");
        
    }
	
	/**
	 * Get all the stock statuses by IDs
	 * @author MohamedHassanWD
	 * @param array $ids Array containing ids to get
	 * @return array Array of stock statuses from database
	 */
	public function getStockStatusesByIds($ids=[])
	{
		$query = "SELECT * FROM " . DB_PREFIX . "stock_status WHERE stock_status_id in (" . implode(',',$ids) . ")";
    return $this->db->query($query)->rows;
	}
}
?>