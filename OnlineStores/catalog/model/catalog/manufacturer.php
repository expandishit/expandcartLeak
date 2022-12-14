<?php
class ModelCatalogManufacturer extends Model {
	public function getManufacturer($manufacturer_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "manufacturer m LEFT JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id) WHERE m.manufacturer_id = '" . (int)$manufacturer_id . "' AND m2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");
	
		return $query->row;	
	}
	
	public function getManufacturers($data = array()) {
		if ($data) {

			$columns = '*';
			if (isset($data['columns'])) {
				$columns = is_array($data['columns']) ? implode(',', $data['columns']) : $data['columns'];
			}

			$sql = "SELECT {$columns} FROM " . DB_PREFIX . "manufacturer m LEFT JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id) WHERE m2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND m.manufacturer_id IN ((select manufacturer_id from " . DB_PREFIX . "product WHERE status <> 0 group by manufacturer_id) )";
			
			$sort_data = array(
				'm.manufacturer_id',
				'm.name',
				'm.sort_order'
			);	
			
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];	
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
			$manufacturer_data = $this->cache->get('manufacturer.' . (int)$this->config->get('config_store_id'));
		
			if (!$manufacturer_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "manufacturer m LEFT JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id) WHERE m2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY name");
	
				$manufacturer_data = $query->rows;
			
				$this->cache->set('manufacturer.' . (int)$this->config->get('config_store_id'), $manufacturer_data);
			}
		 
			return $manufacturer_data;
		}
	}

    public function getCategoryByManufacturerId($manufacturerId) {
        $sql =   "SELECT DISTINCT cd.*,c.image
                    FROM product p 
                    JOIN product_to_category p2c ON (p.product_id = p2c.product_id) 
                    JOIN category c ON (c.category_id = p2c.category_id) 
                    JOIN category_description cd ON (cd.category_id = p2c.category_id) 
                    JOIN manufacturer m ON (p.manufacturer_id = m.manufacturer_id)
                    WHERE p.manufacturer_id = $manufacturerId ";

        $query = $this->db->query($sql);

        return $query->rows;
    }
        public function searchManufacturers($data = array())
    {
        $queryString = [];
        $queryString[] = "SELECT * FROM " . DB_PREFIX . "manufacturer";

        if (!empty($data['filter_name'])) {
            $queryString[] = " WHERE name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        $sort_data = array(
            'name',
            'sort_order'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $queryString[] = " ORDER BY " . $data['sort'];
        } else {
            $queryString[] = " ORDER BY name";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $queryString[] = " DESC";
        } else {
            $queryString[] = " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $queryString[] = " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query(implode(' ', $queryString));

        return $query->rows;
    }
    
}
?>