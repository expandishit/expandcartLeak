<?php 
class ModelCatalogAttribute extends Model {
	public function addAttribute($data) {
		
		//if one of attribute names (languages) not exists, so we have to insert this attribute
		$attribute_exists = true;
		foreach ($data['attribute_description'] as $language_id => $value) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "attribute_description at LEFT JOIN " . DB_PREFIX . "attribute a ON (at.attribute_id = a.attribute_id) WHERE at.language_id = '" . (int)$language_id . "' AND at.name = '" . $this->db->escape($value['name']) . "' AND a.attribute_group_id = '" . (int)$data['attribute_group_id'] . "'");

			if(!$query->num_rows){
				$attribute_exists = false;
				break;
			}
		}
		if($attribute_exists)
			return false;
		////////////////////////////////////////////////////////////////////////////////////////

		$this->db->query("INSERT INTO " . DB_PREFIX . "attribute SET attribute_group_id = '" . (int)$data['attribute_group_id'] . "', sort_order = '" . (int)$data['sort_order'] . "'");
		
		$attribute_id = $this->db->getLastId();
		
		foreach ($data['attribute_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "attribute_description SET attribute_id = '" . (int)$attribute_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}

		return $attribute_id;
	}

	public function editAttribute($attribute_id, $data) {

		//if one of attribute names (languages) not exists, so we have to insert this attribute
		$attribute_exists = true;
		foreach ($data['attribute_description'] as $language_id => $value) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "attribute_description ad LEFT JOIN " . DB_PREFIX . "attribute a ON (ad.attribute_id = a.attribute_id) WHERE ad.language_id = '" . (int)$language_id . "' AND ad.name = '" . $this->db->escape($value['name']) . "' AND a.attribute_group_id = '" . (int)$data['attribute_group_id'] ."' AND ad.attribute_id != '" . (int)$attribute_id . "'");

			if(!$query->num_rows){
				$attribute_exists = false;
				break;
			}
		}
		if($attribute_exists)
			return false;
		////////////////////////////////////////////////////////////////////////////////////////

		$this->db->query("UPDATE " . DB_PREFIX . "attribute SET attribute_group_id = '" . (int)$data['attribute_group_id'] . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE attribute_id = '" . (int)$attribute_id . "'");
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "attribute_description WHERE attribute_id = '" . (int)$attribute_id . "'");

		foreach ($data['attribute_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "attribute_description SET attribute_id = '" . (int)$attribute_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}

		return true;
	}
	
	public function deleteAttribute($attribute_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "attribute WHERE attribute_id = '" . (int)$attribute_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "attribute_description WHERE attribute_id = '" . (int)$attribute_id . "'");
	}
		
	public function getAttribute($attribute_id) {
		$query = $this->db->query("SELECT  a.* ,ad.*, agd.name AS GroupName FROM " . DB_PREFIX . "attribute a LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) LEFT JOIN attribute_group_description agd ON(a.attribute_group_id = agd.attribute_group_id) WHERE a.attribute_id = '" . (int)$attribute_id . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') ."' AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
		
		return $query->row;
	}

    public function getAttributeByDescription($attribute_name,$lang) {
        $query = $this->db->query("SELECT * FROM ". DB_PREFIX . "attribute_description WHERE name = '" . $attribute_name . "' AND language_id = '" . $lang . "'");

        return $query->row;
    }
		
	public function getAttributes($data = array()) {
		$sql = "SELECT *, (SELECT agd.name FROM " . DB_PREFIX . "attribute_group_description agd WHERE agd.attribute_group_id = a.attribute_group_id AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS attribute_group FROM " . DB_PREFIX . "attribute a LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND ad.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if($this->config->get('wk_amazon_connector_status')){ 
			$sql .= " AND a.attribute_id NOT IN (SELECT oc_attribute_id FROM ".DB_PREFIX."amazon_attribute_map) "; 
		}
		
		if (!empty($data['filter_attribute_group_id'])) {
			$sql .= " AND a.attribute_group_id = '" . $this->db->escape($data['filter_attribute_group_id']) . "'";
		}
								
		$sort_data = array(
			'ad.name',
			'attribute_group',
			'a.sort_order'
		);	
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY attribute_group, ad.name";	
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

	public function getAdvancedAttributes($data = array()) {
		$sql = "SELECT *,a.advanced_attribute_id as attribute_id, (SELECT agd.name FROM " . DB_PREFIX . "attribute_group_description agd WHERE agd.attribute_group_id = a.attribute_group_id AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS attribute_group FROM " . DB_PREFIX . "advanced_attribute a LEFT JOIN " . DB_PREFIX . "advanced_attribute_description ad ON (a.advanced_attribute_id = ad.advanced_attribute_id) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "'";
        
		if (!empty($data['filter_name'])) {
			$sql .= " AND ad.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if($this->config->get('wk_amazon_connector_status')){ 
			$sql .= " AND a.advanced_attribute_id NOT IN (SELECT oc_attribute_id FROM ".DB_PREFIX."amazon_attribute_map) "; 
		}
		
		if (!empty($data['filter_attribute_group_id'])) {
			$sql .= " AND a.attribute_group_id = '" . $this->db->escape($data['filter_attribute_group_id']) . "'";
		}
								
		$sort_data = array(
			'ad.name',
			'attribute_group',
			'a.sort_order'
		);	
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY attribute_group, ad.name";	
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
		
	public function getAttributeDescriptions($attribute_id) {
		$attribute_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "attribute_description WHERE attribute_id = '" . (int)$attribute_id . "'");
		
		foreach ($query->rows as $result) {
			$attribute_data[$result['language_id']] = array('name' => $result['name']);
		}
		
		return $attribute_data;
	}
		
	public function getAttributesByAttributeGroupId($data = array()) {
		$sql = "SELECT *, (SELECT agd.name FROM " . DB_PREFIX . "attribute_group_description agd WHERE agd.attribute_group_id = a.attribute_group_id AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS attribute_group FROM " . DB_PREFIX . "attribute a LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND ad.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_attribute_group_id'])) {
			$sql .= " AND a.attribute_group_id = '" . $this->db->escape($data['filter_attribute_group_id']) . "'";
		}
								
		$sort_data = array(
			'ad.name',
			'attribute_group',
			'a.sort_order'
		);	
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY ad.name";	
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
	
	public function getTotalAttributes() {
		if($this->config->get('wk_amazon_connector_status')){ 
			$query = $this->db->query("SELECT COUNT(*) AS total FROM ".DB_PREFIX."attribute a LEFT JOIN ".DB_PREFIX."attribute_group ag ON(a.attribute_group_id = ag.attribute_group_id) WHERE a.attribute_id NOT IN (SELECT oc_attribute_id FROM ".DB_PREFIX."amazon_attribute_map) "); 
		}else{ 
			$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "attribute"); 
		}
		
		return $query->row['total'];
	}	
	public function getTotalAdvancedAttributes() {
		if($this->config->get('wk_amazon_connector_status')){ 
			$query = $this->db->query("SELECT COUNT(*) AS total FROM ".DB_PREFIX."advanced_attribute a LEFT JOIN ".DB_PREFIX."attribute_group ag ON(a.attribute_group_id = ag.attribute_group_id) WHERE a.advanced_attribute_id NOT IN (SELECT oc_attribute_id FROM ".DB_PREFIX."amazon_attribute_map) "); 
		}else{ 
			$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "advanced_attribute"); 
		}
		
		return $query->row['total'];
	}
	
	public function getTotalAttributesByAttributeGroupId($attribute_group_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "attribute WHERE attribute_group_id = '" . (int)$attribute_group_id . "'");
		
		return $query->row['total'];
	}

    public function getGroupedAttributes($filter = [])
    {
        $query = $fields = $data = [];

        $fields[] = 'at.attribute_id';
        $fields[] = 'atg.attribute_group_id';
        $fields[] = 'atd.name as attribute_name';
        $fields[] = 'atgd.name as group_name';
        $fields[] = 'at.sort_order as attribute_order';
        $fields[] = 'atg.sort_order as group_order';

        $query[] = 'SELECT';
        $query[] = implode(',', $fields);
        $query[] = 'FROM `attribute_group` as atg';
        $query[] = 'INNER JOIN `attribute_group_description` as atgd ON atg.attribute_group_id=atgd.attribute_group_id';
        $query[] = 'LEFT JOIN `attribute` as at ON at.attribute_group_id=atg.attribute_group_id';
        $query[] = 'LEFT JOIN attribute_description as atd ON at.attribute_id=atd.attribute_id';
        $query[] = 'AND atd.language_id=' . (int)$this->config->get('config_language_id');
        $query[] = 'WHERE atgd.language_id=' . (int)$this->config->get('config_language_id');

        if (isset($filter['attribute_name']) && $filter['attribute_name'] != '') {
            $query[] = 'AND atd.name LIKE "%' . $filter['attribute_name'] . '%"';
        }

        $results = $this->db->query(implode(' ', $query));

        foreach ($results->rows as $key => $row) {
            $data[$row['attribute_group_id']]['group_id'] = $row['attribute_group_id'];
            $data[$row['attribute_group_id']]['group_name'] = $row['group_name'];
            $data[$row['attribute_group_id']]['group_sort'] = $row['group_order'];
            $data[$row['attribute_group_id']]['options'][$row['attribute_id']] = [
                'attribute_id' => $row['attribute_id'],
                'attribute_name' => $row['attribute_name'],
                'attribute_sort' => $row['attribute_order'],
            ];
        }

//        echo '<pre>';print_r($data);exit;

        return $data;
    }
}
?>
