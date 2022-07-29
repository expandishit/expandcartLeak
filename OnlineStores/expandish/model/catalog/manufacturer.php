<?php
class ModelCatalogManufacturer extends Model {
	public function getManufacturer($manufacturer_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "manufacturer m LEFT JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id) WHERE m.manufacturer_id = '" . (int)$manufacturer_id . "' AND m2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");
	
		return $query->row;	
	}
	
	public function getManufacturers($data = array()) {
		if ($data) {
            $sql = "SELECT * FROM " . DB_PREFIX . "manufacturer m LEFT JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id) WHERE m2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
            
            if (!isset($data['without_check_product'])) {
                $sql.= " AND m.manufacturer_id IN ((SELECT DISTINCT manufacturer_id FROM " . DB_PREFIX . "product WHERE status <> 0) )";
            }

            $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

            if($queryMultiseller->num_rows) {
                if (!empty($data['filter_name'])) {
                    $sql .= " AND name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
                }
            }

			$sort_data = array(
				'name',
				'sort_order'
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
			
			$manufacturer_data = $query->rows;
		} else {
			$manufacturer_data = $this->cache->get('manufacturer.' . (int)$this->config->get('config_store_id'));
		
			if (!$manufacturer_data) {
                $sql = "SELECT * FROM " . DB_PREFIX . "manufacturer m LEFT JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id) WHERE m2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
                
                if (!isset($data['without_check_product'])) {
                    $sql.= " AND m.manufacturer_id IN ((SELECT DISTINCT manufacturer_id FROM " . DB_PREFIX . "product WHERE status <> 0) )";
                }
                
                $sql.= " ORDER BY name";
                
                $query = $this->db->query($sql);
                
				$manufacturer_data = $query->rows;
			
				$this->cache->set('manufacturer.' . (int)$this->config->get('config_store_id'), $manufacturer_data);
			}
        }	
        
        return $manufacturer_data;
	}
    public function getAllManufacturers()
    {
        $queryString = [];
        $queryString[] = "SELECT * FROM " . DB_PREFIX . "manufacturer";

        $query = $this->db->query(implode(' ', $queryString));

        return $query->rows;
    }

	public function getManufacturerByName($name)
    {
        $queryString = '
            SELECT * FROM ' . DB_PREFIX . 'manufacturer where slug="'.$this->db->escape($name).'"
        ';

        $data = $this->db->query($queryString);

        if ($data->num_rows > 0) {
            return $data;
        } else {
            return false;
        }
    }

    public function getManufacturerssByIds($Ids = array()) {
        if(count($Ids) == 0 || !is_array($Ids)) {
            return false;
        }

        $brand_ids = implode(",", $Ids);
        if(empty($brand_ids)) return false;

        $query = $this->db->query("SELECT * FROM manufacturer m WHERE m.manufacturer_id IN (" . $brand_ids . ")");


        if ($query->num_rows) {
            $brands = array();
            foreach ($query->rows as $brand) {
                $brands[$brand['manufacturer_id']] =  array(
                    'brand_id' => $brand['manufacturer_id'],
                    'name' => $brand['name'],
                    'image' => $brand['image'],
                    'slug' => $brand['slug']
                );
            }
            // author : Ahmed abdelfattah
            // Sort Array

            $brands = array_replace(array_flip($Ids), $brands);

            return $brands;
        } else {
            return false;
        }
    }
}
?>
