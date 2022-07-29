<?php

class ModelModuleAdvancedProductAttributes extends Model {

	public function isInstalled(){
        $isInstalled =  \Extension::isInstalled('advanced_product_attributes');
        
		if($isInstalled && $this->config->get('advanced_product_attribute_status')) return true;

        return false;
	}


	public function getAttributes($data = array(), $sort = array()) {
		$sql = "SELECT
					SQL_CALC_FOUND_ROWS
					*
				FROM `" . DB_PREFIX . "attribute_group` agd
				LEFT JOIN " . DB_PREFIX . "advanced_attribute a
					ON (agd.attribute_group_id = a.attribute_group_id)
				LEFT JOIN " . DB_PREFIX . "advanced_attribute_description ad ON (a.advanced_attribute_id = ad.advanced_attribute_id)
				WHERE 1 = 1 "
				. (isset($data['advanced_attribute_id']) ? " AND a.advanced_attribute_id =  " .  (int)$data['advanced_attribute_id'] : '')
				. (isset($data['language_id']) ? " AND ad.language_id =  " .  (int)$data['language_id'] : " AND ad.language_id =  " .  (int)$this->config->get('config_language_id'))
				. (isset($sort['order_by']) ? " ORDER BY {$sort['order_by']} {$sort['order_way']}" : '')
				. (isset($sort['limit']) ? " LIMIT ".(int)$sort['offset'].', '.(int)($sort['limit']) : '');

		$res = $this->db->query($sql);

		$total = $this->db->query("SELECT FOUND_ROWS() as total");
		
		if ($res->rows) $res->rows[0]['total_rows'] = $total->row['total'];

		return ($res->num_rows == 1 && isset($data['single']) ? $res->row : $res->rows);
	}

	public function getAttributeValuesCurrentLanguage($advanced_attribute_id) {
		$attribute_value_data = array();

		$sql = "
			SELECT av.advanced_attribute_value_id as id , avd.name
			FROM " . DB_PREFIX . "advanced_attribute_value av 
			LEFT JOIN " . DB_PREFIX . "advanced_attribute_value_description avd 
			ON (av.advanced_attribute_value_id = avd.advanced_attribute_value_id) 
			WHERE av.advanced_attribute_id = '" . (int)$advanced_attribute_id . 
			"' AND avd.language_id = " . (int)$this->config->get('config_language_id') ;

		$attribute_value_query = $this->db->query($sql);

		return $attribute_value_query->rows;
    }

    public function getProductAttributesCustom($product_id){
        $product_attribute_data = array();

        $product_advanced_attribute_ids = $this->db->query("SELECT 
        	advanced_attribute_id 
        	FROM " . DB_PREFIX . "product_attribute 
        	WHERE product_id = '" . (int)$product_id . "' 
        	AND advanced_attribute_id <> 0 AND language_id = 0 AND attribute_id = 0
        	GROUP BY advanced_attribute_id");

        foreach ($product_advanced_attribute_ids->rows as $product_attribute) {
            $type = $this->db->query("
							SELECT a.type 
							FROM advanced_attribute a
							WHERE a.advanced_attribute_id = " . $product_attribute['advanced_attribute_id'])
            		->row['type'];

            if($type == 'text'){

                $product_attribute_description_data = [];

                $product_attribute_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "advanced_product_attribute_text WHERE product_id = '" . (int)$product_id . "' AND advanced_attribute_id = '" . (int)$product_attribute['advanced_attribute_id'] . "'");

                foreach ($product_attribute_description_query->rows as $product_attribute_description) {
                    $product_attribute_description_data[$product_attribute_description['language_id']] = [ 'text' => $product_attribute_description['text'] ];
                }

                $product_attribute_data[] = array(
                    'attribute_id' => $product_attribute['advanced_attribute_id'],
                    'product_attribute_description' => $product_attribute_description_data
                );
            }
            elseif(in_array($type, ['single_select', 'multi_select'])){
                $values = [];
                $values = $this->db->query("SELECT advanced_attribute_value_id FROM " . DB_PREFIX . "advanced_product_attribute_choose WHERE product_id = '" . (int)$product_id . "' AND advanced_attribute_id = '" . (int)$product_attribute['advanced_attribute_id'] . "'");

                $product_attribute_data[] = array(
                    'attribute_id' => $product_attribute['advanced_attribute_id'],
                    'product_attribute_values' => array_column($values->rows, 'advanced_attribute_value_id')
                );
            }


        }
        return $product_attribute_data;
    }

    public function getAttribute($attribute_id) {
		$query = $this->db->query("
			SELECT *
			FROM " . DB_PREFIX . "advanced_attribute a 
			LEFT JOIN " . DB_PREFIX . "advanced_attribute_description ad 
				ON (a.advanced_attribute_id = ad.advanced_attribute_id) 

			WHERE a.advanced_attribute_id = " . (int)$attribute_id . " 
			AND ad.language_id = " . (int)$this->config->get('config_language_id') . " ");
		
		return $query->row;
	}

	public function addProductAttributes($product_id, $advanced_attributes){
		//Add
		foreach($advanced_attributes as $advanced_attribute){
			 // print_r($advanced_attribute);die('okk');
			$this->db->query("INSERT INTO `". DB_PREFIX ."product_attribute` 
				SET product_id =" . (int)$product_id . ",  
				attribute_id = 0, language_id = 0, text = '',
				advanced_attribute_id = ". $advanced_attribute['advanced_attribute_id']);
			
			if($advanced_attribute['representation_type'] == 'text'){
 				foreach ($advanced_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "advanced_product_attribute_text SET product_id = '" . (int)$product_id . "', advanced_attribute_id = '" . (int)$advanced_attribute['advanced_attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" . $this->db->escape($product_attribute_description['text']) . "'");
                }
			}
			elseif(in_array($advanced_attribute['representation_type'] , ['multi_select', 'single_select'] )){
				foreach ($advanced_attribute['values'] as $value_id) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "advanced_product_attribute_choose SET product_id = '" . (int)$product_id . "', advanced_attribute_id = '" . (int)$advanced_attribute['advanced_attribute_id'] . "', advanced_attribute_value_id = '" . (int)$value_id . "'");
                }
			}
		}
	}


	public function getProductAttributes($product_id) {
		$product_attribute_group_data = array();

		$product_attribute_group_query = $this->db->query("
            SELECT ag.attribute_group_id, agd.name 
            FROM " . DB_PREFIX . "product_attribute pa 
            LEFT JOIN " . DB_PREFIX . "advanced_attribute a 
                ON (pa.advanced_attribute_id = a.advanced_attribute_id) 
            LEFT JOIN " . DB_PREFIX . "attribute_group ag 
                ON (a.attribute_group_id = ag.attribute_group_id) 
            LEFT JOIN " . DB_PREFIX . "attribute_group_description agd 
                ON (ag.attribute_group_id = agd.attribute_group_id) 
            WHERE pa.product_id = '" . (int)$product_id . "' 
            AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "' 
            GROUP BY ag.attribute_group_id 
            ORDER BY ag.sort_order, agd.name");

		foreach ($product_attribute_group_query->rows as $product_attribute_group) {
			$product_attribute_data = array();

			$product_attribute_query = $this->db->query("
                SELECT a.*, ad.name
                FROM " . DB_PREFIX . "product_attribute pa 
                LEFT JOIN " . DB_PREFIX . "advanced_attribute a 
                ON (pa.advanced_attribute_id = a.advanced_attribute_id) 
                LEFT JOIN " . DB_PREFIX . "advanced_attribute_description ad 
                ON (a.advanced_attribute_id = ad.advanced_attribute_id) 
                WHERE pa.product_id = '" . (int)$product_id . "' 
                AND a.attribute_group_id = '" . (int)$product_attribute_group['attribute_group_id'] . "' 
                AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "' 
               
                ORDER BY a.sort_order, ad.name");

			foreach ($product_attribute_query->rows as $product_attribute) {
                if($product_attribute['type'] === 'text'){
                    $query_text = $this->db->query("
                        SELECT `text` FROM `advanced_product_attribute_text`
                        WHERE advanced_attribute_id = '" . (int) $product_attribute['advanced_attribute_id'] . "'
                        AND language_id = '" . (int)$this->config->get('config_language_id') ."' 
                        AND product_id  = '" . (int)$product_id . "'" );

                    $product_attribute_data[] = array(
                     'advanced_attribute_id' => $product_attribute['advanced_attribute_id'],
                     'name'         => $product_attribute['name'],
                     'text'         => $query_text->row['text'],
                     'glyphicon'    => $product_attribute['glyphicon'],
                     'type'         => $product_attribute['type']
                    );
                }
                elseif(in_array($product_attribute['type'], ['single_select', 'multi_select']) ){
                    $query_choose = $this->db->query("
                        SELECT GROUP_CONCAT(`name`) as `text` 
                        FROM `advanced_product_attribute_choose` pavc
                        LEFT JOIN `advanced_attribute_value_description` avd
                        ON avd.`advanced_attribute_value_id` = pavc.advanced_attribute_value_id
                        WHERE pavc.advanced_attribute_id = '" . (int) $product_attribute['advanced_attribute_id'] . "'
                        AND language_id = '" . (int)$this->config->get('config_language_id') ."' 
                        AND product_id  = '" . (int)$product_id . "'" );

                    $product_attribute_data[] = array(
                     'advanced_attribute_id' => $product_attribute['advanced_attribute_id'],
                     'name'         => $product_attribute['name'],
                     'text'         => $query_choose->row['text'],
                     'glyphicon'    => $product_attribute['glyphicon'],
                     'type'         => $product_attribute['type']
                    );
                }
			}

			$product_attribute_group_data[] = array(
				'attribute_group_id' => $product_attribute_group['attribute_group_id'],
				'name'               => $product_attribute_group['name'],
				'attribute'          => $product_attribute_data
			);
		}
		return $product_attribute_group_data;
	}

	public function getProductAttributes2($product_id) {

		$product_attributes = $this->db->query("
            SELECT a.advanced_attribute_id, aad.name, a.glyphicon, a.type 
            FROM " . DB_PREFIX . "product_attribute pa 
            LEFT JOIN " . DB_PREFIX . "advanced_attribute a 
                ON (pa.advanced_attribute_id = a.advanced_attribute_id) 
            LEFT JOIN " . DB_PREFIX . "advanced_attribute_description aad 
                ON (aad.advanced_attribute_id = a.advanced_attribute_id) 
            WHERE pa.product_id = " . (int)$product_id . "
            AND aad.language_id = " . (int)$this->config->get('config_language_id') )->rows;

		foreach ($product_attributes as $key=>$attribute) {

	        if($attribute['type'] === 'text'){
                $query_text = $this->db->query("
                    SELECT `text` FROM `advanced_product_attribute_text`
                    WHERE advanced_attribute_id = '" . (int) $attribute['advanced_attribute_id'] . "'
                    AND language_id = '" . (int)$this->config->get('config_language_id') ."' 
                    AND product_id  = '" . (int)$product_id . "'" );

                $product_attributes[$key]['text'] = $query_text->row['text'];

                // $product_attribute_data[] = array(
                //  'advanced_attribute_id' => $product_attribute['advanced_attribute_id'],
                //  'name'         => $product_attribute['name'],
                //  'text'         => $query_text->row['text'],
                //  'glyphicon'    => $product_attribute['glyphicon'],
                //  'type'         => $product_attribute['type']
                // );
            }
            elseif(in_array($attribute['type'], ['single_select', 'multi_select']) ){
                $query_choose = $this->db->query("
                    SELECT GROUP_CONCAT(`name`) as `text` 
                    FROM `advanced_product_attribute_choose` pavc
                    JOIN `advanced_attribute_value_description` avd
                    ON avd.`advanced_attribute_value_id` = pavc.advanced_attribute_value_id
                    WHERE pavc.advanced_attribute_id = '" . (int) $attribute['advanced_attribute_id'] . "'
                    AND language_id = '" . (int)$this->config->get('config_language_id') ."' 
                    AND product_id  = '" . (int)$product_id . "'" );
                $product_attributes[$key]['text'] = $query_choose->row['text'];
            }
		}
		return $product_attributes;
	}
}

