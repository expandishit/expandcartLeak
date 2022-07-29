<?php
class ModelCatalogOption extends Model {
	public function addOption($data) {

        if (\Extension::isInstalled('product_builder') && $this->config->get('product_builder')['status']) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "option` SET type = '" . $this->db->escape($data['type']) . "', sort_order = '" . (int)$data['sort_order'] . "', custom_option = '" . $data['custom_option'] . "'");
        }else{
            $this->db->query("INSERT INTO `" . DB_PREFIX . "option` SET type = '" . $this->db->escape($data['type']) . "', sort_order = '" . (int)$data['sort_order'] . "'");
        }
		$option_id = $this->db->getLastId();
		
		foreach ($data['option_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "option_description SET option_id = '" . (int)$option_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}

		if (isset($data['option_value'])) {
            // Product Option Image PRO module <<
            $this->load->model('module/product_option_image_pro');
            if (isset($data['poip_settings'])) {
                $this->model_module_product_option_image_pro->setRealOptionSettings($option_id, $data['poip_settings']);
            }
			// >> Product Option Image PRO module
			

			if ($data['type'] == 'product') {
				foreach ($data['option_value'] as $option_value) {
					$query = "INSERT INTO " . DB_PREFIX . "option_value SET option_id = '" . (int)$option_id . "', image = '" . $this->db->escape(html_entity_decode($option_value['image'], ENT_QUOTES, 'UTF-8')) . "', sort_order = '" . (int)$option_value['sort_order'] . "'";
					$query .=" ,valuable_type='product', valuable_id={$option_value['value']}";
					$this->db->query($query);
				}
			} else {
				foreach ($data['option_value'] as $option_value) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "option_value SET option_id = '" . (int)$option_id . "', image = '" . $this->db->escape(html_entity_decode($option_value['image'], ENT_QUOTES, 'UTF-8')) . "', sort_order = '" . (int)$option_value['sort_order'] . "'");
					
					$option_value_id = $this->db->getLastId();
					
					foreach ($option_value['option_value_description'] as $language_id => $option_value_description) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "option_value_description SET option_value_id = '" . (int)$option_value_id . "', language_id = '" . (int)$language_id . "', option_id = '" . (int)$option_id . "', name = '" . $this->db->escape($option_value_description['name']) . "'");
					}
				}
			}
		}

        return $option_id;
	}
	
	public function editOption($option_id, $data) {
        if (\Extension::isInstalled('product_builder') && $this->config->get('product_builder')['status']) {
            $this->db->query("UPDATE `" . DB_PREFIX . "option` SET type = '" . $this->db->escape($data['type']) . "', sort_order = '" . (int)$data['sort_order'] . "', custom_option = '" . $this->db->escape($data['custom_option']) . "' WHERE option_id = '" . (int)$option_id . "'");
        }else {
            $this->db->query("UPDATE `" . DB_PREFIX . "option` SET type = '" . $this->db->escape($data['type']) . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE option_id = '" . (int)$option_id . "'");
        }
		$this->db->query("DELETE FROM " . DB_PREFIX . "option_description WHERE option_id = '" . (int)$option_id . "'");

		foreach ($data['option_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "option_description SET option_id = '" . (int)$option_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}
				
		$this->db->query("DELETE FROM " . DB_PREFIX . "option_value WHERE option_id = '" . (int)$option_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "option_value_description WHERE option_id = '" . (int)$option_id . "'");
		
		if (isset($data['option_value'])) {
            // Product Option Image PRO module <<
            $this->load->model('module/product_option_image_pro');
            if (isset($data['poip_settings'])) {
                $this->model_module_product_option_image_pro->setRealOptionSettings($option_id, $data['poip_settings']);
            }
            // >> Product Option Image PRO module

			$i = 1;
			
			if ($data['type'] == 'product') {

				$option_value['sort_order'] = $i;
				foreach ($data['option_value'] as $option_value) {
					if ($option_value['option_value_id']) {
						$query = "INSERT INTO " . DB_PREFIX . "option_value SET option_value_id = '" . (int)$option_value['option_value_id'] . "', option_id = '" . (int)$option_id . "', image = '" . $this->db->escape(html_entity_decode($option_value['image'], ENT_QUOTES, 'UTF-8')) . "', sort_order = '" . (int)$option_value['sort_order'] . "'";
						$query .=" ,valuable_type='product', valuable_id={$option_value['value']}";
						$this->db->query($query);
					} else {
						$query = "INSERT INTO " . DB_PREFIX . "option_value SET option_id = '" . (int)$option_id . "', image = '" . $this->db->escape(html_entity_decode($option_value['image'], ENT_QUOTES, 'UTF-8')) . "', sort_order = '" . (int)$option_value['sort_order'] . "'";
						$query .=" ,valuable_type='product', valuable_id={$option_value['value']}";
						$this->db->query($query);	
					}
				}

			} else {

				foreach ($data['option_value'] as $option_value) {

					$option_value['sort_order'] = $i;

					if ($option_value['option_value_id']) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "option_value SET option_value_id = '" . (int)$option_value['option_value_id'] . "', option_id = '" . (int)$option_id . "', image = '" . $this->db->escape(html_entity_decode($option_value['image'], ENT_QUOTES, 'UTF-8')) . "', sort_order = '" . (int)$option_value['sort_order'] . "'");
					} else {
						$this->db->query("INSERT INTO " . DB_PREFIX . "option_value SET option_id = '" . (int)$option_id . "', image = '" . $this->db->escape(html_entity_decode($option_value['image'], ENT_QUOTES, 'UTF-8')) . "', sort_order = '" . (int)$option_value['sort_order'] . "'");
					}
					
					$option_value_id = $this->db->getLastId();
					
					foreach ($option_value['option_value_description'] as $language_id => $option_value_description) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "option_value_description SET option_value_id = '" . (int)$option_value_id . "', language_id = '" . (int)$language_id . "', option_id = '" . (int)$option_id . "', name = '" . $this->db->escape($option_value_description['name']) . "'");
					}
					$i++;
				}
			}
		}
	}
	
	public function deleteOption($option_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "option` WHERE option_id = '" . (int)$option_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "option_description WHERE option_id = '" . (int)$option_id . "'");

        // Product Option Image PRO module <<
        $this->load->model('module/product_option_image_pro');
        $this->model_module_product_option_image_pro->deleteRealOptionSettings($option_id);
        // >> Product Option Image PRO module

		$this->db->query("DELETE FROM " . DB_PREFIX . "option_value WHERE option_id = '" . (int)$option_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "option_value_description WHERE option_id = '" . (int)$option_id . "'");
	}
	
	public function getOption($option_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "option` o LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE o.option_id = '" . (int)$option_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "'");
		
		return $query->row;
	}
		
	public function getOptions($data = array()) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "option` o LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE od.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		
		if($this->config->get('wk_amazon_connector_status')){ 
			$sql .= " AND o.option_id NOT IN (SELECT variation_id FROM ".DB_PREFIX."amazon_variation_map) "; 
		}

		if (isset($data['filter_name']) && !is_null($data['filter_name'])) {
			$sql .= " AND od.`name` LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

        if (isset($data['option_type']) ) {
            $sql .= " AND o.`type` = '" . $this->db->escape($data['option_type']) . "'";
        }

		$sort_data = array(
			'od.`name`',
			'o.`type`',
			'o.sort_order'
		);	
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY od.`name`";
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
	
	public function getOptionDescriptions($option_id) {
		$option_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_description WHERE option_id = '" . (int)$option_id . "'");
				
		foreach ($query->rows as $result) {
			$option_data[$result['language_id']] = array('name' => $result['name']);
		}
		
		return $option_data;
	}

    /**
     * @param $option_value_id
     * @return array
     */
    public function getOptionValueDescription($option_value_id)
    {
        $option_value_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_value_description WHERE option_value_id = '" . (int)$option_value_id . "'");
        $option_value_description_data = array();
		foreach ($option_value_description_query->rows as $option_value_description) {
			$option_value_description_data[$option_value_description['language_id']] = array('name' => $option_value_description['name']);
		}
        return $option_value_description_data;
    }
	
	public function getOptionValue($option_value_id) {

		$lang_id = $this->config->get('config_language_id') ? (int)$this->config->get('config_language_id') : 1;

		$query = "SELECT * FROM " . DB_PREFIX . "option_value ov LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id AND ovd.language_id = '" . (int)$lang_id . "') WHERE ov.option_value_id = '" . (int)$option_value_id . "'";
		$opv_product = $this->db->query($query)->row;
		if (!$opv_product['name'] && \Extension::isInstalled('product_builder') && $this->config->get('product_builder')['status']) {
			$query = "SELECT p.product_id, pd.name FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd  ON (p.product_id = pd.product_id) WHERE p.product_id='{$opv_product['valuable_id']}'";
			$query .= " AND pd.language_id = '{$lang_id}'";
			$opv_product = $this->db->query($query)->row;
		}

		return $opv_product;
	}
	
	public function getOptionValues($option_id) {
		$option_value_data = array();

		$lang_id = $this->config->get('config_language_id') ? (int)$this->config->get('config_language_id') : 1;
		$option_value_query = $this->db->query("SELECT *, ov.option_value_id FROM " . DB_PREFIX . "option_value ov LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id AND ovd.language_id = '" . (int)$lang_id . "') WHERE ov.option_id = '" . (int)$option_id . "' ORDER BY ov.sort_order ASC");
				
		foreach ($option_value_query->rows as $option_value) {
			if (isset($option_value['valuable_type']) && $option_value['valuable_type'] == 'product' && \Extension::isInstalled('product_builder') && $this->config->get('product_builder')['status']) {
				$query = "SELECT p.product_id, pd.name FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd  ON (p.product_id = pd.product_id) WHERE p.product_id='{$option_value['valuable_id']}'";
				$query .= " AND pd.language_id = '{$lang_id}'";
				$opv_product = $this->db->query($query)->row;
			}
			$option_value_data[] = array(
				'option_value_id' => $option_value['option_value_id'],
				'name'            => isset($opv_product) && $opv_product ? $opv_product['name'] : $option_value['name'],
				'image'           => $option_value['image'],
				'sort_order'      => $option_value['sort_order']
			);
		}
		
		return $option_value_data;
	}
	
	public function getOptionValueDescriptions($option_id) {
		$option_value_data = array();
		
		$option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_value WHERE option_id = '" . (int)$option_id . "'");

		foreach ($option_value_query->rows as $option_value) {
			$option_value_description_data = array();
			
			
			$option_value_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_value_description WHERE option_value_id = '" . (int)$option_value['option_value_id'] . "'");			
			
			foreach ($option_value_description_query->rows as $option_value_description) {
				$option_value_description_data[$option_value_description['language_id']] = array('name' => $option_value_description['name']);
			}
			
			$option_value_data[] = array(
				'option_value_id'          => $option_value['option_value_id'],
				'option_value_description' => $option_value_description_data,
				'image'                    => $option_value['image'],
				'sort_order'               => $option_value['sort_order'],
				'valuable_type'            => $option_value['valuable_type'],
				'valuable_id'              => $option_value['valuable_id']
			);
		}

		usort($option_value_data, function($a, $b) {
			return $a['sort_order'] - $b['sort_order'];
		});

		return $option_value_data;
	}

	public function getTotalOptions() {
		if($this->config->get('wk_amazon_connector_status')){ 
			$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "option` WHERE option_id NOT IN (SELECT variation_id FROM `".DB_PREFIX."amazon_variation_map`)"); 
		}else{ 
			$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "option`"); 
		}
      	
		return $query->row['total'];
	}

	public function addOptionValues($data)
    {

        $valuesIds = [];

        foreach ($data['option_value'] as $option_value) {
        	
            $query = [];
            $query[] = "INSERT INTO " . DB_PREFIX . "option_value SET";
            $query[] = "option_id = '" . (int)$data['option_id'] . "',";
            if (isset($option_value['image'])) {
                $query[] = "image = '" . $this->db->escape(
                    html_entity_decode($option_value['image'], ENT_QUOTES, 'UTF-8')
                    ) . "',";
			}

			if ($data['option_type'] == 'product') {
				$query[] = "valuable_type = 'product',";
				$query[] = "valuable_id = '" . (int)$option_value['value'] . "',";

			}
            $query[] = "sort_order = '0'";

			$this->db->query(implode(' ', $query));

            $option_value_id = $this->db->getLastId();
			
			if ($data['option_type'] != 'product ') {
				foreach ($option_value['option_value_description'] as $language_id => $option_value_description) {

					$this->db->query("INSERT INTO " . DB_PREFIX . "option_value_description SET
						option_value_id = '" . (int)$option_value_id . "',
						language_id = '" . (int)$language_id . "',
						option_id = '" . (int)$data['option_id'] . "',
						name = '" . $this->db->escape($option_value_description['name']) . "'"
					);
				}
			}
            if($data['single_option_value']){
            	return $option_value_id;
            }
            
            $valuesIds[] = $option_value_id;
        }

        return $valuesIds;
    }

    /**
     * Updates the sort order.
     *
     * @param int $key
     * @param int $sort
     *
     * @return void
     */
    public function updateSortOrder($key, $sort)
    {
        $query = [];

        $query[] = 'UPDATE `%s` SET';
        $query[] = 'sort_order=%d';
        $query[] = 'WHERE option_id=%d';

        $this->db->query(vsprintf(implode(' ', $query), [
            'option',
            $sort,
            $key
        ]));
    }

    //Store Dropna Ids of product option values
    public function addDropnaPrOptVal($data){
        $this->db->query("INSERT INTO " . DB_PREFIX . "pr_op_val_to_dropna SET product_option_value_id = '" . (int)$data['product_option_value_id'] . "', dropna_pr_op_val_id = '" . (int)$data['dropna_pr_op_val_id'] . "'");
    }



    public function getOptionsIds(){
    	return array_column($this->db->query("SELECT option_id , `name`
    		FROM `" . DB_PREFIX . "option_description` 
    		WHERE language_id = 1;")->rows, 'option_id','name');
    }


    public function getOptionsValuesIds(){
    	return array_column($this->db->query("SELECT option_value_id, `name` 
    		FROM `" . DB_PREFIX . "option_value_description` 
    		WHERE language_id = 1;")->rows, 'option_value_id', 'name');
    }
}
?>
