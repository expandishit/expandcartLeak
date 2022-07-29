<?php

class ModelModuleAdvancedProductAttributesAttribute extends Model {



	public function add($attribute){
		//Check if the name of the attribute exists..
		if($this->_ifExist($attribute)) return false;

		//Insert new Attribute
		$this->db->query("INSERT INTO " . DB_PREFIX . "advanced_attribute SET attribute_group_id = '" . (int)$attribute['attribute_group_id'] . "', sort_order = '" . (int)$attribute['sort_order'] . "', type = '" . $this->db->escape($attribute['type']) ."' , glyphicon = '" . $this->db->escape($attribute['glyphicon']) . "'");

		$attribute_id = $this->db->getLastId();
		
		foreach ($attribute['attribute_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "advanced_attribute_description SET advanced_attribute_id = '" . (int)$attribute_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}

		//Add attribute values
		if (in_array($attribute['type'], ['single_select', 'multi_select']) 
			&& isset($attribute['attribute_values'])) {

			foreach ($attribute['attribute_values'] as $attribute_value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "advanced_attribute_value SET advanced_attribute_id = '" . (int)$attribute_id . "'");

				$attribute_value_id = $this->db->getLastId();
				
				foreach ($attribute_value as $language_id => $value_description) {
					$this->db->query("INSERT INTO " . DB_PREFIX .
                        "advanced_attribute_value_description SET advanced_attribute_value_id = '" .
                        (int)$attribute_value_id . "', language_id = '" . (int)$language_id . "', advanced_attribute_id = '" .
                        (int)$attribute_id . "', name = '" . $this->db->escape($value_description['value']) . "'");
				}
			}
		}


		return $attribute_id;
	}

	public function edit($attribute_id, $data) {

		//Check if the name of the attribute exists..
		// if($this->_ifExist($data)) return false;

		$this->db->query("UPDATE " . DB_PREFIX . "advanced_attribute SET attribute_group_id = '" . (int)$data['attribute_group_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', type = '" . $this->db->escape($data['type']) . "' , glyphicon = '" . $this->db->escape($data['glyphicon']) . "' WHERE advanced_attribute_id = '" . (int)$attribute_id . "'");
		
		foreach ($data['attribute_description'] as $language_id => $value) {
			$this->db->query("UPDATE " . DB_PREFIX . "advanced_attribute_description SET name = '" . $this->db->escape($value['name']) . "'" ." where advanced_attribute_id = '" . (int)$attribute_id . "' and language_id = '" . (int)$language_id . "'"
            );
		}

        //Add attribute values
        if (in_array($data['type'], ['single_select', 'multi_select'])
            && isset($data['attribute_values'])) {
			$edited = [];
            foreach ($data['attribute_values'] as $advanced_attribute_value_id => $item){
                $insert=0;
		
                foreach ($item  as $lang_id =>$value2) {

                    $c = $this->db->query("select count(*) as count from " . DB_PREFIX . "advanced_attribute_value_description" ." where advanced_attribute_value_id = '" . (int)$advanced_attribute_value_id . "' and language_id = '" . (int)$lang_id . "'". " and advanced_attribute_id = '"."$attribute_id"."'"
		    );
                    if ($c->row['count'] != 0 ) {
                        $this->db->query("UPDATE " . DB_PREFIX . "advanced_attribute_value_description SET name = '" . $this->db->escape($value2['value']) . "'" ." where advanced_attribute_value_id = '" . (int)$advanced_attribute_value_id . "' and language_id = '" . (int)$lang_id . "'". " and advanced_attribute_id = '"."$attribute_id"."'"
			);
                    }else{
                        $insert=1;
                    }
                }

                if ($insert == 1){

                    $this->db->query("INSERT INTO " . DB_PREFIX . "advanced_attribute_value SET advanced_attribute_id = '" . (int)$attribute_id . "'");
                    $advanced_attribute_value_id = $this->db->getLastId();
	
                    foreach ($item  as $lang_id =>$value2) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "advanced_attribute_value_description SET advanced_attribute_value_id = '" . (int)$advanced_attribute_value_id . "', language_id = '" . (int)$lang_id . "', advanced_attribute_id = '" . (int)$attribute_id . "', name = '" . $this->db->escape($value2['value']) . "'");
                    }
                }
				$edited[] = $advanced_attribute_value_id;
		}
		
		$edited = "('".implode("' , '", $edited)."')";
		$this->db->query("DELETE FROM " . DB_PREFIX . "advanced_attribute_value_description WHERE advanced_attribute_id = ".((int)$attribute_id). 
		    	" AND advanced_attribute_value_id NOT IN ".$edited);
		$this->db->query("DELETE FROM " . DB_PREFIX . "advanced_attribute_value WHERE advanced_attribute_id = ".((int)$attribute_id). 
			" AND  advanced_attribute_value_id NOT IN ".$edited);
			
	}

		return true;
	}

    public function editSimpleAttribute($attribute_id, $data) {

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
    public function getAttributes($settings) {
        $sql = "SELECT a.attribute_id as advanced_attribute_id,attribute_group_id,sort_order,type,glyphicon,name, (SELECT agd.name FROM " . DB_PREFIX .
            "attribute_group_description agd WHERE agd.attribute_group_id = a.attribute_group_id AND agd.language_id = '" .
            (int)$this->config->get('config_language_id') . "') AS attribute_group FROM " . DB_PREFIX . "attribute a LEFT JOIN " .
            DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE ad.language_id = '" .
            (int)$this->config->get('config_language_id') . "'";

        if($this->config->get('wk_amazon_connector_status')){
            $sql .= " AND a.attribute_id NOT IN (SELECT oc_attribute_id FROM ".DB_PREFIX."amazon_attribute_map) ";
        }

        $sql .= " ORDER BY " . $settings['order'];
        $sql .= " ".$settings['direction'];

        $query = $this->db->query($sql);

        return $query->rows;
	}

    /**
     * Get Advanced Attribute by Name
     * @param $names array
     * @return mixed
     */
    public function getAttributeByName(array $names){

        // prepare the values for the query
        $names = array_map(function($name){ return "'" . $this->db->escape($name) . "'"; }, $names);

        return $this->db->query("SELECT av.advanced_attribute_id, avd.name as attribute_name
            FROM ". DB_PREFIX ."advanced_attribute av
                     LEFT JOIN ". DB_PREFIX ."advanced_attribute_description avd
                               ON (av.advanced_attribute_id = avd.advanced_attribute_id)
                     LEFT JOIN language l
                               ON avd.language_id = l.language_id
            WHERE avd.name IN (". implode(",", $names) .")
              and av.attribute_group_id = 1
            group by av.advanced_attribute_id
            limit 1
        ");

    }

    /**
     * @param array|null $settings
     * @param int $advanced_attribute_id
     * @return array
     */
	public function get($settings = null , int $advanced_attribute_id = 0): array
    {
		if(!$settings) {
            $settings = ['order' => 'advanced_attribute_id', 'direction' => 'ASC'];
        }

		//if there is no id , then get all attributes...
		if($advanced_attribute_id <= 0){

            $attributes = $this->db->query("SELECT at.*, atd.name , agd.name as attribute_group
				FROM advanced_attribute  at
				JOIN advanced_attribute_description atd ON at.advanced_attribute_id = atd.advanced_attribute_id
				JOIN attribute_group_description agd ON at.attribute_group_id = agd.attribute_group_id
				WHERE atd.language_id = ". (int)$this->config->get('config_language_id') ."
				AND agd.language_id = ". (int)$this->config->get('config_language_id') ." 
				ORDER BY atd.". $settings['order']. " " . $settings['direction']);

            $simple_attributes = $this->getAttributes($settings);
            $i = count($simple_attributes);
            $max_id = (int) $this->db->query("select MAX(attribute_id) as max FROM attribute;")->row['max'];
            $min_advanced_id = (int)  $this->db->query("select min(advanced_attribute_id) as min FROM advanced_attribute;")->row['min'];
            $max_advanced_id = (int) $this->db->query("select max(advanced_attribute_id) as max FROM advanced_attribute;")->row['max'];

            if( $i < $max_advanced_id){
                $i = $max_advanced_id;
            }

            foreach ($attributes->rows as $key => $attribute){

                $i++;

                if( !($min_advanced_id > $max_id ) && count($attributes->rows) > 0){
                    $this->db->query("UPDATE " . DB_PREFIX . "advanced_attribute SET advanced_attribute_id = '" . $i .
                        "' WHERE advanced_attribute_id = '" . (int) $attribute['advanced_attribute_id']  . "'");

                    $this->db->query("UPDATE " . DB_PREFIX . "product_attribute SET advanced_attribute_id = '" . $i .
                        "' WHERE (advanced_attribute_id = '" . (int) $attribute['advanced_attribute_id']  . "') ");

                    $this->db->query("UPDATE " . DB_PREFIX . "advanced_product_attribute_text SET advanced_attribute_id = '" . $i .
                        "' WHERE (advanced_attribute_id = '" . (int) $attribute['advanced_attribute_id']  . "') ");

                    $this->db->query("UPDATE " . DB_PREFIX . "advanced_attribute_description SET advanced_attribute_id = '" . $i .
                        "' WHERE advanced_attribute_id = '" . (int) $attribute['advanced_attribute_id']  . "'");

                    $simple_attributes[$key]['advanced_attribute_id'] = $i;
                }
            }
            return array_merge($simple_attributes, $attributes->rows);


		}
		//if there is an id , then get this attribute data

        $attributes = $this->db->query("SELECT at.*, atd.name , agd.name as attribute_group
            FROM advanced_attribute  at
            JOIN advanced_attribute_description atd ON at.advanced_attribute_id = atd.advanced_attribute_id
            JOIN attribute_group_description agd ON at.attribute_group_id = agd.attribute_group_id
            WHERE atd.language_id = ". (int)$this->config->get('config_language_id') ."
            AND agd.language_id = ". (int)$this->config->get('config_language_id') ." 
            AND at.advanced_attribute_id = ". $advanced_attribute_id ."  
            ORDER BY atd.". $settings['order']. " " . $settings['direction']);

        return $attributes->row;
    }

    public function getSimpleAttributeDescriptions($attribute_id) {
        $attribute_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "attribute_description WHERE attribute_id = '" . (int)$attribute_id . "'");

        foreach ($query->rows as $result) {
            $attribute_data[$result['language_id']] = array('name' => $result['name']);
        }

        return $attribute_data;
    }
    public function getSimpleAttribute($attribute_id) {
        $query = $this->db->query("SELECT  a.attribute_id as advanced_attribute_id,a.* ,ad.*, agd.name AS GroupName FROM " . DB_PREFIX . "attribute a LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) LEFT JOIN attribute_group_description agd ON(a.attribute_group_id = agd.attribute_group_id) WHERE a.attribute_id = '" . (int)$attribute_id . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') ."' AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

        return $query->row;
    }

	public function getValues($advanced_attribute_id){
		$attribute_value_data = array();
		
		$attribute_value_query = $this->db->query("
			SELECT * 
			FROM " . DB_PREFIX . "advanced_attribute_value av 
			LEFT JOIN " . DB_PREFIX . "advanced_attribute_value_description avd 
			ON (av.advanced_attribute_value_id = avd.advanced_attribute_value_id) 

			WHERE av.advanced_attribute_id = '" . (int)$advanced_attribute_id . "'");
		
		foreach ($attribute_value_query->rows as $attribute_value) {
			$attribute_value_data[$attribute_value['advanced_attribute_value_id']][] = [
				'name'               => $attribute_value['name'],
				'language_id'        => $attribute_value['language_id']
			];
		}
		return $attribute_value_data;
	}

	public function getAttributeDescriptions($advanced_attribute_id) {
		$attribute_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "advanced_attribute_description WHERE advanced_attribute_id = '" . (int)$advanced_attribute_id . "'");
		
		foreach ($query->rows as $result) {
			$attribute_data[$result['language_id']] = array('name' => $result['name']);
		}
		
		return $attribute_data;
	}


	public function delete($id){
        $this->db->query("DELETE FROM " . DB_PREFIX . "attribute WHERE attribute_id = '" . (int)$id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "attribute_description WHERE attribute_id = '" . (int)$id . "'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "advanced_attribute WHERE advanced_attribute_id = '" . (int)$id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "advanced_attribute_description WHERE advanced_attribute_id = '" . (int)$id . "'");
	}


	private function _ifExist($attribute){

		$attribute_exists = true;
		
		foreach ($attribute['attribute_description'] as $language_id => $value) {
			$query = $this->db->query("
				SELECT * 
				FROM " . DB_PREFIX . "advanced_attribute_description at 
				LEFT JOIN " . DB_PREFIX . "advanced_attribute a ON (at.advanced_attribute_id = a.advanced_attribute_id) 
				WHERE at.language_id = " . (int)$language_id . " 
				AND at.name = '" . $this->db->escape($value['name']) . "' 
				AND a.attribute_group_id = " . (int)$attribute['attribute_group_id']);

			if(!$query->num_rows){
				$attribute_exists = false;
				break;
			}
		}
		return $attribute_exists;
	}

	public function getTotalProductsByAttributeId($id){
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_attribute WHERE advanced_attribute_id = '" . (int)$id . "'");

        return $query->row['total'];
    }

    public function getSimpleGroupedAttributes($filter = []){
        $query = $fields = $data = [];

        $fields[] = 'at.attribute_id';
        $fields[] = 'at.type as attribute_type';
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
        $query[] = 'LEFT JOIN `attribute_description` as atd ON at.attribute_id=atd.attribute_id';
        $query[] = 'AND atd.language_id=' .(int) $this->config->get('config_language_id');
        $query[] = 'WHERE atgd.language_id=' .(int) $this->config->get('config_language_id');

        if (isset($filter['attribute_name']) && $filter['attribute_name'] != '') {
            $query[] = 'AND atd.name LIKE "%' . $filter['attribute_name'] . '%"';
        }
        if(!empty($filter['limit'])){
            $query[] ='LIMIT '.$filter['limit'];
        }
        else{
            $query[] ='LIMIT 100';
        }

        $results = $this->db->query(implode(' ', $query));

        return $results->rows;
    }
    public function getGroupedAttributes($filter = []){
        $query = $fields = $data = [];

        $fields[] = 'at.advanced_attribute_id';
        $fields[] = 'at.type as attribute_type';
        $fields[] = 'atg.attribute_group_id';
        $fields[] = 'atd.name as attribute_name';
        $fields[] = 'atgd.name as group_name';
        $fields[] = 'at.sort_order as attribute_order';
        $fields[] = 'atg.sort_order as group_order';

        $query[] = 'SELECT';
        $query[] = implode(',', $fields);
        $query[] = 'FROM `attribute_group` as atg';
        $query[] = 'INNER JOIN `attribute_group_description` as atgd ON atg.attribute_group_id=atgd.attribute_group_id';
        $query[] = 'LEFT JOIN `advanced_attribute` as at ON at.attribute_group_id=atg.attribute_group_id';
        $query[] = 'LEFT JOIN `advanced_attribute_description` as atd ON at.advanced_attribute_id=atd.advanced_attribute_id';
        $query[] = 'AND atd.language_id=' . (int)$this->config->get('config_language_id');
        $query[] = 'WHERE atgd.language_id=' . (int)$this->config->get('config_language_id');

        if (isset($filter['attribute_name']) && $filter['attribute_name'] != '') {
            $query[] = 'AND atd.name LIKE "%' . $filter['attribute_name'] . '%"';
        }

        if(!empty($filter['limit'])){
            $query[] ='LIMIT '.$filter['limit'];
        }
        else{
            $query[] ='LIMIT 100';
        }

        $results = $this->db->query(implode(' ', $query));

        $ar= $this->getSimpleGroupedAttributes($filter);

        $results->rows = array_merge($ar,$results->rows);
        $sql = "select count(advanced_attribute_id) as attribute_count FROM advanced_attribute;";
        $advanced_attribute_count = (int) $this->db->query($sql)->row['attribute_count'];
        $max_id= (int) $this->db->query("select MAX(attribute_id) as max FROM attribute;")->row['max'];
        $min_advanced_id= (int) $this->db->query("select min(advanced_attribute_id) as min FROM advanced_attribute;")->row['min'];
        $max_advanced_id= (int) $this->db->query("select max(advanced_attribute_id) as max FROM advanced_attribute;")->row['max'];

        $i=$max_id;
        if( $i < $max_advanced_id){
            $i=$max_advanced_id;
        }

        $this->db->query("SET FOREIGN_KEY_CHECKS = 0");

        foreach ($results->rows as $key => $row) {
            $i++;
            $data[$row['attribute_group_id']]['group_id'] = $row['attribute_group_id'];
            $data[$row['attribute_group_id']]['group_name'] = $row['group_name'];
            $data[$row['attribute_group_id']]['group_sort'] = $row['group_order'];

            if(isset($row['advanced_attribute_id'] ) &&  $advanced_attribute_count != 0  && !($min_advanced_id > $max_id ) ){

                $this->db->query("UPDATE " . DB_PREFIX . "advanced_attribute SET advanced_attribute_id = '" . (int)$i .
                    "' WHERE advanced_attribute_id = '" . (int) $row['advanced_attribute_id']  . "'");

                $this->db->query("UPDATE " . DB_PREFIX . "product_attribute SET advanced_attribute_id = '" . (int)$i .
                    "' WHERE (advanced_attribute_id = '" . (int) $row['advanced_attribute_id']  . "') ");

                $this->db->query("UPDATE " . DB_PREFIX . "advanced_product_attribute_text SET advanced_attribute_id = '" . (int)$i .
                    "' WHERE (advanced_attribute_id = '" . (int) $row['advanced_attribute_id']  . "') ");

                $this->db->query("UPDATE " . DB_PREFIX . "advanced_attribute_description SET advanced_attribute_id = '" . (int)$i .
                    "' WHERE advanced_attribute_id = '" . (int) $row['advanced_attribute_id']  . "'");

                $row['advanced_attribute_id'] = $i;

            }else{
                if (! isset($row['advanced_attribute_id'] ) ){
                    $row['advanced_attribute_id'] = (int) $row['attribute_id'];
                }
            }

            $data[$row['attribute_group_id']]['advanced_attributes'][$row['advanced_attribute_id']] = [
                'advanced_attribute_id' => isset($row['advanced_attribute_id'])? $row['advanced_attribute_id'] :$i,
                'attribute_name' => $row['attribute_name'],
                'attribute_sort' => $row['attribute_order'],
                'attribute_type' => $row['attribute_type'],
            ];
        }

        $this->db->query("SET FOREIGN_KEY_CHECKS = 1");

        return $data;
    }


	public function getCurrentProductAdvancedAttributesIds($product_id){
        $query = $this->db->query('SELECT attribute_id as advanced_attribute_id
			FROM `' . DB_PREFIX . 'product_attribute` WHERE product_id='.(int)$product_id.'
			AND advanced_attribute_id = 0 group by attribute_id;
			');
        $simple_attribute_ids = array_column($query->rows, 'advanced_attribute_id');

		$query = $this->db->query('SELECT advanced_attribute_id 
			FROM `' . DB_PREFIX . 'product_attribute` WHERE product_id='.(int)$product_id.' 
			AND attribute_id=0 AND language_id=0 AND text="" AND advanced_attribute_id <> 0;
			');

		$advanced_attribute_ids = array_column($query->rows, 'advanced_attribute_id');

        return array_merge($simple_attribute_ids,$advanced_attribute_ids);
	}



	public function getProductAttributes($product_id) {
            if(!empty($product_id)) {

            $simple_attributes = $this->db->query("
			SELECT pa.attribute_id as advanced_attribute_id , aa.type, aa.glyphicon, aad.name, agd.`name` as GroupName
			from product_attribute pa join `attribute` aa on pa.attribute_id = aa.attribute_id
			join attribute_description aad on aad.`attribute_id` = aa.`attribute_id`
			join `attribute_group_description` agd on agd.`attribute_group_id` = aa.`attribute_group_id`
			where product_id=" . $product_id . "
			and pa.attribute_id <> 0 and advanced_attribute_id=0
			and aad.language_id=". (int)$this->config->get("config_language_id") ."
			and agd.`language_id`=" . (int) $this->config->get("config_language_id") ." GROUP BY pa.attribute_id ")->rows;

		$advanced_attributes = $this->db->query("
			SELECT pa.advanced_attribute_id, aa.type, aa.glyphicon, aad.name, agd.`name` as GroupName
			from product_attribute pa join advanced_attribute aa on pa.advanced_attribute_id = aa.advanced_attribute_id
			join advanced_attribute_description aad on aad.`advanced_attribute_id` = aa.`advanced_attribute_id`
			join `attribute_group_description` agd on agd.`attribute_group_id` = aa.`attribute_group_id`
			where product_id=" . $product_id . " 
			and pa.advanced_attribute_id <> 0 and attribute_id=0 
			and aad.language_id=". (int)$this->config->get("config_language_id") ." 
			and agd.`language_id`=" .  (int)$this->config->get("config_language_id"))->rows;

            $advanced_attributes = array_merge($simple_attributes,$advanced_attributes);

		foreach ($advanced_attributes as $key=>$advanced_attribute) {

			$product_attribute_description_data = []; 
			$advanced_attribute_selected_values = [];
			$advanced_attribute_all_values = [];

	        if($advanced_attribute['type'] === 'text'){
                $product_attribute_description_query_simple  = $this->db->query("
		    		SELECT * 
		    		FROM " . DB_PREFIX . "product_attribute 
		    		WHERE product_id = '" . (int)$product_id . "' AND advanced_attribute_id=0
		    		AND attribute_id = '" . (int)$advanced_attribute['advanced_attribute_id'] . "'"
                );
                foreach ($product_attribute_description_query_simple->rows as $product_attribute_description) {
                    $product_attribute_description_data[$product_attribute_description['language_id']] = ['text' => $product_attribute_description['text'] ];
                }

		    	$product_attribute_description_query = $this->db->query("
		    		SELECT * 
		    		FROM " . DB_PREFIX . "advanced_product_attribute_text 
		    		WHERE product_id = '" . (int)$product_id . "' 
		    		AND advanced_attribute_id = '" . (int)$advanced_attribute['advanced_attribute_id'] . "'");

	            foreach ($product_attribute_description_query->rows as $product_attribute_description) {
	                $product_attribute_description_data[$product_attribute_description['language_id']] = ['text' => $product_attribute_description['text'] ];
	            }
            }
            elseif(in_array($advanced_attribute['type'], ['single_select', 'multi_select']) ){
                $advanced_attribute_selected_values = array_column($this->db->query("
                    SELECT advanced_attribute_value_id
                    FROM `advanced_product_attribute_choose` pavc
                    WHERE pavc.advanced_attribute_id = ".(int)$advanced_attribute['advanced_attribute_id']."
                    AND pavc.product_id  = ".(int)$product_id)->rows
                , 'advanced_attribute_value_id');
				
				$advanced_attribute_all_values = $this->getAttributeValuesCurrentLanguage($advanced_attribute['advanced_attribute_id']);
                
            }
            $advanced_attributes[$key]['product_attribute_description'] = $product_attribute_description_data?:[];
            $advanced_attributes[$key]['values'] = $advanced_attribute_all_values?: [];
            $advanced_attributes[$key]['selected_values'] = $advanced_attribute_selected_values?:[];
		}
		return $advanced_attributes;
	}else{
            return [];
        }
    }


 	public function getAttributeValuesCurrentLanguage($advanced_attribute_id) {
 		$attribute_values = [];
		$attribute_values = $this->db->query("
			SELECT av.advanced_attribute_value_id as id , avd.name
			FROM " . DB_PREFIX . "advanced_attribute_value av 
			LEFT JOIN " . DB_PREFIX . "advanced_attribute_value_description avd 
			ON (av.advanced_attribute_value_id = avd.advanced_attribute_value_id) 
			WHERE av.advanced_attribute_id = '" . (int)$advanced_attribute_id . 
			"' AND avd.language_id = " . (int)$this->config->get('config_language_id') )->rows;

		return $attribute_values;
    }



    public function addProductAttributes($product_id, $advanced_attributes){
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");

		foreach($advanced_attributes as $advanced_attribute){

		    // migrate simple attributes data to advanced attributes

            $id = null;
            $sql = "select * from `". DB_PREFIX ."attribute` where attribute_id = ".(int) $advanced_attribute['advanced_attribute_id'] ;

            $attribute = $this->db->query($sql);
            $attribute = $attribute->row;
            if (count($attribute) > 0){
                $sql= "INSERT IGNORE INTO `". DB_PREFIX ."advanced_attribute` SET sort_order =" . (int)$attribute['sort_order'] . ", attribute_group_id = ".(int) $attribute['attribute_group_id'];
                $this->db->query($sql);
                $id=  $transaction_id = $this->db->getLastId();

                $sql = "delete from `". DB_PREFIX ."attribute` where attribute_id = ".(int) $advanced_attribute['advanced_attribute_id'] ;
                $this->db->query($sql);

                $sql = "select * from attribute_description where attribute_id = ".(int) $advanced_attribute['advanced_attribute_id'] ;
                $attribute_descriptions = $this->db->query($sql);

                $attribute_descriptions = $attribute_descriptions->rows;

                if($id){
                   foreach ($attribute_descriptions as $item){
                     $sql = "INSERT INTO `". DB_PREFIX ."advanced_attribute_description`SET advanced_attribute_id =". (int)$id .", name = '". $this->db->escape($item['name']) ."',language_id=". (int) $item['language_id'];
                     $this->db->query($sql);
                    }
                }

                $sql = "delete from attribute_description where attribute_id = ".(int) $advanced_attribute['advanced_attribute_id'] ;
                 $this->db->query($sql);
            }

            if ($id){
                $advanced_attribute_id = $id;
            }
            else{
                $advanced_attribute_id=$advanced_attribute['advanced_attribute_id'];
            }
            
                $this->db->query("INSERT IGNORE INTO `" . DB_PREFIX . "product_attribute` 
                SET product_id =" . (int)$product_id . ",  
				attribute_id = 0, language_id = 0, text = '',
				advanced_attribute_id = " . $advanced_attribute_id);

                if ($advanced_attribute['type'] == 'text') {
                    foreach ($advanced_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
                        $this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "advanced_product_attribute_text SET product_id = '" . (int)$product_id . "', advanced_attribute_id = '" . (int)$advanced_attribute_id . "', language_id = '" . (int)$language_id . "', text = '" . $this->db->escape($product_attribute_description['text']) . "'");
                    }
                } elseif (in_array($advanced_attribute['type'], ['multi_select', 'single_select'])) {
                    foreach ($advanced_attribute['values'] as $value_id) {
                        $this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "advanced_product_attribute_choose SET product_id = '" . (int)$product_id . "', advanced_attribute_id = '" . (int)$advanced_attribute_id . "', advanced_attribute_value_id = '" . (int)$value_id . "'");
                    }
                }

		}
    }

}
