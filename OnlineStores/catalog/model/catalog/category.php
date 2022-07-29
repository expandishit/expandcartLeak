<?php
class ModelCatalogCategory extends Model {
	public function getCategory($category_id, $language_id = null) {
		if( !$language_id ) $language_id = $this->config->get('config_language_id');

		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.category_id = '" . (int)$category_id . "' AND cd.language_id = '" . (int)$language_id . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1'");
		
		return $query->row;
	}
	
	public function getCategories($parent_id = 0,$data = array(), $language_id = null) {
		if( !$language_id ) $language_id = $this->config->get('config_language_id');

        $sql = "SELECT * FROM " . DB_PREFIX . "category c 
	            LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) 
	            LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) 
	            WHERE cd.language_id = '" . (int)$language_id . "'";
        
	    if (isset($data['filter_text'])) {
            $filter_name = $data['filter_text'];	    	
	    	$sql .= " AND cd.name  LIKE '%{$filter_name}%'";
	    }

        if(is_array($parent_id) && count($parent_id) > 0)
            $sql .= " ANd c.parent_id IN (".implode(',', $parent_id).")";
        else
            $sql .= " AND c.parent_id = '" . (int)$parent_id . "'";

	    $sql  .=" AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
	            AND c.status = '1' 
	            ORDER BY c.sort_order, LCASE(cd.name)";

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
	
	public function getCategoryFilters($category_id) {
		$implode = array();
		
		$query = $this->db->query("SELECT filter_id FROM " . DB_PREFIX . "category_filter WHERE category_id = '" . (int)$category_id . "'");
		
		foreach ($query->rows as $result) {
			$implode[] = (int)$result['filter_id'];
		}
		
		
		$filter_group_data = array();
		
		if ($implode) {
			$filter_group_query = $this->db->query("SELECT DISTINCT f.filter_group_id, fgd.name, fg.sort_order FROM " . DB_PREFIX . "filter f LEFT JOIN " . DB_PREFIX . "filter_group fg ON (f.filter_group_id = fg.filter_group_id) LEFT JOIN " . DB_PREFIX . "filter_group_description fgd ON (fg.filter_group_id = fgd.filter_group_id) WHERE f.filter_id IN (" . implode(',', $implode) . ") AND fgd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY f.filter_group_id ORDER BY fg.sort_order, LCASE(fgd.name)");
			
			foreach ($filter_group_query->rows as $filter_group) {
				$filter_data = array();
				
				$filter_query = $this->db->query("SELECT DISTINCT f.filter_id, fd.name FROM " . DB_PREFIX . "filter f LEFT JOIN " . DB_PREFIX . "filter_description fd ON (f.filter_id = fd.filter_id) WHERE f.filter_id IN (" . implode(',', $implode) . ") AND f.filter_group_id = '" . (int)$filter_group['filter_group_id'] . "' AND fd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY f.sort_order, LCASE(fd.name)");
				
				foreach ($filter_query->rows as $filter) {
					$filter_data[] = array(
						'filter_id' => $filter['filter_id'],
						'name'      => $filter['name']			
					);
				}
				
				if ($filter_data) {
					$filter_group_data[] = array(
						'filter_group_id' => $filter_group['filter_group_id'],
						'name'            => $filter_group['name'],
						'filter'          => $filter_data
					);	
				}
			}
		}
		
		return $filter_group_data;
	}
				
	public function getCategoryLayoutId($category_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_to_layout WHERE category_id = '" . (int)$category_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");
		
		if ($query->num_rows) {
			return $query->row['layout_id'];
		} else {
			return $this->config->get('config_layout_category');
		}
	}
					
	public function getTotalCategoriesByCategoryId($parent_id = 0) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1'");
		
		return $query->row['total'];
	}

	public function getCategoryManufacturerImages($category_id, $limit=10) {
	    $firstLvlCats = $this->getCategories($category_id);

        $allCatsIds = array();
        foreach ($firstLvlCats as $cat) {
            $allCatsIds[] = $cat['category_id'];

            $secondLvlCats = $this->getCategories($cat['category_id']);
            foreach ($secondLvlCats as $subcat) {
                $allCatsIds[] = $subcat['category_id'];
            }
        }

        $sql =   "SELECT DISTINCT m.*
                    FROM product p 
                    JOIN product_to_category p2c ON (p.product_id = p2c.product_id) 
                    JOIN manufacturer m ON (p.manufacturer_id = m.manufacturer_id)
                    WHERE m.image != ''
                    AND p2c.category_id IN (" . $category_id;

        if(count($allCatsIds) > 0) {
            $sql .= "," . implode(',', $allCatsIds);
        }

        $sql .= ") LIMIT " . $limit;

        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getManufacturerByCategoryId($category_id) {
        $sql =   "SELECT DISTINCT m.*
                    FROM product p 
                    JOIN product_to_category p2c ON (p.product_id = p2c.product_id) 
                    JOIN manufacturer m ON (p.manufacturer_id = m.manufacturer_id)
                    WHERE p2c.category_id = $category_id ";

        $query = $this->db->query($sql);

        return $query->rows;
    }
    
    	public function searchCategories($data=[]) {
                $filterName = $this->db->escape($data['filter_name']);
                
                $sql = "SELECT * FROM category_description WHERE language_id = ". (int)$this->config->get('config_language_id').
                        " AND name LIKE '%$filterName%' OR description LIKE '%$filterName%' OR meta_description LIKE '%$filterName%' OR meta_keyword LIKE '%$filterName%' OR slug LIKE '%$filterName%'";

		$sql .= " GROUP BY category_description.category_id ORDER BY name";
		
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
			    $data['start'] = 0;
			}

			if ($data['limit'] < 1) {
			    $data['limit'] = 20;
			}
		}
                
                $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		$query = $this->db->query($sql);
		
		return $query->rows;
	}
	public function getCustomCategoriesIDs($table) 
    {
		$query = $this->db->query("SELECT category_id FROM " . DB_PREFIX .$table);
        $categoriesIDs=array();
        foreach($query->rows as $cat_id){
            $categoriesIDs[]=$cat_id['category_id'];
        }
		return $categoriesIDs;
	}
	
    public function getCustomCategories($data,$language_id) {
       
		if( !$language_id ) $language_id = $this->config->get('config_language_id');

        $sql = "SELECT * FROM " . DB_PREFIX . "category c 
	            LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) 
	            LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) 
				WHERE c.category_id in (".implode(',',$data).") 
	            AND cd.language_id = '" . (int)$language_id . "'";
        if(is_array($parent_id) && count($parent_id) > 0)
            $sql .= " ANd c.parent_id IN (".implode(',', $parent_id).")";
        else
            $sql .= " AND c.parent_id = '" . (int)$parent_id . "'";

	    $sql  .=" AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
	            AND c.status = '1' 
	            ORDER BY c.sort_order, LCASE(cd.name)";

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
    

}
?>
