<?php
/**
 * User: Anh TO
 * Date: 10/3/14
 * Time: 10:51 AM
 */

class ModelCatalogRule extends Model
{
	CONST FIXED_REWARD = 1;
	CONST SPENT_REWARD = 2;
	public function applyRule($conditions, $rule_id){
		$counters = array();
		$_conditions = array();
		$data_filter = array();
		foreach($conditions as $counter => $rule)
		{
			if((int)strpos($counter, "-") == 0 )
			{
				$counters[$counter] = $rule;
			}
			else
			{
				$sub_counter = explode("--", $counter);

				$data_option = $this->getDataOption($rule['type'], false, $rule['value']);
				$_conditions[$counter]['data'] = $data_option;
				$_conditions[$counter]['data']['filter']['operator'] = $rule['operator'];

				$data_filter[$sub_counter[0]][$sub_counter[1]] = $_conditions[$counter]['data']['filter'];
			}
		}

		$rule_data = $this->getRule($rule_id);

		$sql = "DELETE FROM ".DB_PREFIX."product_to_reward WHERE rule_id = $rule_id";
		$this->db->query($sql);

        $default_reward = 0;
        $this->load->model('catalog/product');

		foreach ($counters as $counter => $condition)
		{
			$conds = (isset($data_filter[$counter])) ? $data_filter[$counter] : array();

			$products = $this->conditionToSql($conds, $condition);

			foreach ($products as $product)
			{
		
		/**************************************************************************************/
		/*
		* This part is obsolete as the first special_prices do not always reflect the customer`s price
		* instead reward points are calculated on product price at runtime, as rules are already being loaded there to validate
		* customer_group
		* see model/rewardpoints/catalogrule.php getPoints() for more details
		*  
		* */
			
				$product_special_query = $this->db->query("SELECT price FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product['product_id'] . "' AND ((IFNULL(date_start, '0000-00-00') = '0000-00-00' OR date_start < NOW()) AND (IFNULL(date_end, '0000-00-00') = '0000-00-00' OR date_end > NOW())) ORDER BY priority ASC, price ASC LIMIT 1");
                $special = false;
                if(isset($product_special_query->row['price']) && $product_special_query->row['price'] > 0){
                    $special = $product_special_query->row['price'];
                }

                if($special != false){
                    $price = $special;
                }else{
                    $price = $product['price'];
                }

				if($rule_data['actions'] == self::FIXED_REWARD)
				{
					$reward_point = $rule_data['reward_point'];
				}

				else if($rule_data['actions'] == self::SPENT_REWARD)
				{
                    $reward_point = 0;
                    if($rule_data['reward_per_spent'] > 0){
			    $reward_point = round($price * ($rule_data['reward_point'] / $rule_data['reward_per_spent']));
			    

		    }
				}
		/******************************************************************************************/

                if($reward_point > 0 && !empty($product['product_id'])){
                    $sql = "INSERT INTO ".DB_PREFIX."product_to_reward VALUES(".$product['product_id'].", $rule_id, $reward_point);";
                    $this->db->query($sql);
                }

			}
		}
	}

	public function getRule($rule_id){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "catalog_rules WHERE rule_id = " . (int)$rule_id);

		return $query->row;
	}
	/**
	 * $aggregator = all -> AND, = any -> OR
	 * $value: true/false
	 */
    protected function conditionToSql($data, $condition)
    {
        $field_available = array();
        $combine_conditions = array();
        if(count($data) > 0)
            foreach ($data as $_condition)
            {
                foreach ($_condition as $key => $value)
                {
                    if($key != 'operator' && !in_array($key, $field_available))
                    {
                        $field_available[] = $key;
                    }
                }
            }

        $products = $this->getProducts($data, $field_available, $condition['aggregator']);

        return $products;
    }

	protected function getDataOption($model_value = "", $json_output = true, $value_selected = '')
	{
		$model_value = (empty($model_value)) ? $this->request->post['value'] : $model_value;
		if($model_value != '')
		{
			if(strpos($model_value, "|") > -1 && strpos($model_value, "-") > -1)
			{
				$data   = explode("|", $model_value);
				$model  = $data[0];
				$data   = explode("-", $data[1]);
				$field  = $data[0];
				$type   = $data[1];
				$method = isset($data[2]) ? $data[2] : null;
			}else{
				$data = explode("|", $model_value);
				$model = $data[0];
				$id = $data[1];
			}

			$this->load->model($model);
			$model_alias = str_replace("/", "_", $model);
			$selected = "";
			$selected_ID = "";
			switch($model_alias)
			{
				case 'catalog_option':
					$option  = $this->{"model_".$model_alias}->getOption($id);
					$_data = $this->{"model_".$model_alias}->getOptionValues($id);

					$option_values = array();

					foreach($_data as $_option)
					{
						if($_option['option_value_id'] == $value_selected)
						{
							$selected_ID = $value_selected;
							break;
						}
					}
					$data = array(
						'type'  =>  $option['type'],
						'selected_ID'   => $selected_ID,
					);

					break;
				case 'catalog_manufacturer':
					$manufacturers = $this->{"model_".$model_alias}->{$method}(array());

					$option_values = array();
					foreach($manufacturers as $manufacturer)
					{
						if($manufacturer['manufacturer_id'] == $value_selected)
						{
							$selected_ID = $value_selected;
							break;
						}
					}

					$data = array(
						'type'  =>  'select',
						'selected_ID'   => $selected_ID,
						'filter'    =>  array(
							'filter_manufacturer_id'    =>  $selected_ID,
						)
					);

					break;
				case 'catalog_attribute':
					$this->load->model('catalog/attribute_group');
					$data = array(
						'filter_attribute_group_id' =>  $id
					);
					$_attribute = $this->{"model_".$model_alias."_group"}->getAttributeGroupDescriptions($id);

					$_attributes = $this->{"model_".$model_alias}->getAttributesByAttributeGroupId($data);

					$option_values = array();
					foreach($_attributes as $attribute)
					{
						if($attribute['attribute_id'] == $value_selected)
						{
							$selected_ID = $value_selected;
						}
					}

					$data = array(
						'type'  =>  'select',
						'selected_ID'   => $selected_ID,
						'filter'    =>  array(
							'filter_attribute_id'    =>  $selected_ID,
						)
					);

					break;
				case 'catalog_category':
					$categories = $this->{"model_".$model_alias}->{$method}(array());

					$option_values = array();
					foreach($categories as $category)
					{
						if($category['category_id'] == $value_selected)
						{
							$selected_ID = $value_selected;
						}
					}

					$data = array(
						'type'  =>  'select',
						'selected_ID'   => $selected_ID,
						'filter'    =>  array(
							'filter_category_id'    =>  $selected_ID,
						)
					);

					break;
				case 'catalog_product':

					$data = array(
						'type'  =>  'text',
						'selected'  =>  $value_selected,
						'filter'    =>  array(
							'filter_'.$field    =>  $value_selected,
						)
					);

					break;
			}

			if($json_output)
			{
				echo json_encode($data);
			}
			else
			{
				return $data;
			}
		}
	}

    public function getProducts($data = array(), $field_available = array(), $aggregator)
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "product p
                LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";

        if (in_array('filter_category_id', $field_available))
        {
            $sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";
        }

        if (in_array('filter_attribute_id', $field_available))
        {
            $sql .= " LEFT JOIN " . DB_PREFIX . "product_attribute pat ON (p.product_id = pat.product_id)";
        }

        $sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
        if($aggregator == 'any'){
            $operator_sql = 'OR';
        }else{
            $operator_sql = 'AND';
        }
        $filter_model = $filter_price = $filter_sku = $filter_manufacturer_id = $filter_category_id = $filter_attribute_id = [];

		foreach($data as $condition)
        {
            if (isset($condition['filter_model']) && !empty($condition['filter_model']))
            {
                $filter_model[] = $this->operatorToMysql('p.`model`', $condition['operator'], $this->db->escape($condition['filter_model']));
            }

            if (isset($condition['filter_price']) && !empty($condition['filter_price']))
            {
                $filter_price[] = $this->operatorToMysql('p.`price`', $condition['operator'], $this->db->escape($condition['filter_price']));
			}

            if (isset($condition['filter_sku']) && !empty($condition['filter_sku']))
            {
                $filter_sku[] = $this->operatorToMysql('p.`sku`', $condition['operator'], $this->db->escape($condition['filter_sku']));
            }

            if (isset($condition['filter_manufacturer_id']) && !empty($condition['filter_manufacturer_id']))
            {
                $filter_manufacturer_id[] = $this->operatorToMysql('p.`manufacturer_id`', $condition['operator'], $this->db->escape($condition['filter_manufacturer_id']));
            }

            if (isset($condition['filter_category_id']) && !empty($condition['filter_category_id']))
            {
                $filter_category_id[] = $this->operatorToMysql('p2c.`category_id`', $condition['operator'], $this->db->escape($condition['filter_category_id']));
            }

            if (isset($condition['filter_attribute_id']) && !empty($condition['filter_attribute_id']))
            {
                $filter_attribute_id[] = $this->operatorToMysql('pat.`attribute_id`', $condition['operator'], $this->db->escape($condition['filter_attribute_id']));
            }
		}
        $sql_filter_model = $sql_filter_price = $sql_filter_sku = $sql_filter_manufacturer_id = $sql_filter_category_id = $sql_filter_attribute_id = "";
        if(is_array($filter_model) && !empty($filter_model))
            $sql_filter_model = " AND (".implode($operator_sql." ", $filter_model).") ";
        if(is_array($filter_price)  && !empty($filter_price) )
            $sql_filter_price = " AND (".implode($operator_sql." ", $filter_price).") ";
        if(is_array($filter_sku)  && !empty($filter_sku))
            $sql_filter_sku = " AND (".implode($operator_sql." ", $filter_sku).") ";
        if(is_array($filter_manufacturer_id)  && !empty($filter_manufacturer_id))
            $sql_filter_manufacturer_id = " AND (".implode($operator_sql." ", $filter_manufacturer_id).") ";
        if(is_array($filter_category_id) && !empty($filter_category_id))
            $sql_filter_category_id = " AND (".implode($operator_sql." ", $filter_category_id).") ";
        if(is_array($filter_attribute_id) && !empty($filter_attribute_id))
            $sql_filter_attribute_id = " AND (".implode($operator_sql." ", $filter_attribute_id).") ";


        $sql .= $sql_filter_model.$sql_filter_price.$sql_filter_sku.$sql_filter_manufacturer_id.$sql_filter_category_id.$sql_filter_attribute_id;

        $sql .= " GROUP BY p.product_id";
        $sql .= " ORDER BY p.product_id";
        $sql .= " DESC";

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

	protected function operatorToMysql($field, $op, $value)
	{
		$op = htmlspecialchars_decode($op);
		$text = '';
		switch($op)
		{
			case '>=':
				$text = $field ." $op '". $value."'";
				break;
			case '<=':
				$text = $field ." $op '". $value."'";
				break;
			case '>':
				$text = $field ." $op '". $value."'";
				break;
			case '<':
				$text = $field ." $op '". $value."'";
				break;
			case '{}':
				$text = $field ." LIKE '%". $value."%'";
				break;
			case '!{}':
				$text = $field ." NOT LIKE '%". $value."%'";
				break;
			case '()':
				$text = $field ." IN ('$value')";
				break;
			case '==':
				$text = $field ." = '$value'";
				break;
			case '!()':
				$text = $field ." NOT IN ('$value')";
				break;
			case '=!':
				$text = $field ." <> '$value'";
				break;
		}
		return $text." ";
	}
}
