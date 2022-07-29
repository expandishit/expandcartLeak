<?php

class ModelAmazonPriceRuleAmazonPriceRule extends Model {
    private $sql_keys = array('price_from',
                              'price_to',
                              'price_value',
                              'price_type',
                              'price_opration',
                              'price_status',
                              'rule_type'
                            );

  public function addPriceRule($data = array()) {

      $this->db->query("INSERT INTO ".DB_PREFIX."amazon_price_rules SET price_from = '" . (int)$data['price_from'] . "', price_to = '" . (int)$data['price_to'] . "', price_value = '" . (int)$data['price_value'] . "',price_type = '" . (int)$data['price_type'] . "', price_opration = '" . $this->db->escape($data['price_opration']) . "', price_status = '" . (int)$data['price_status'] . "', rule_type = '".$this->db->escape($data['rule_type'])."'");
      
  }

  public function getTotalPriceRules($data = array()) {
    $basesql = '';

    $basesql = "SELECT COUNT(DISTINCT id) AS total FROM " . DB_PREFIX . "amazon_price_rules WHERE 1";

    $sql = $this->_buildQuery($data = array());

    $sql_query = $basesql .' ' .$sql;

    $query = $this->db->query($sql_query);

  	return $query->row['total'];
  }

  public function getPriceRules($data = array()) {
     $field = $data;

     $basesql = '';

     $basesql = "SELECT * FROM " . DB_PREFIX . "amazon_price_rules WHERE 1";

     $sql_bq = $this->_buildQuery($field);

     $sql = $this->_buildSortingQuery($field);

     $sql_query = $basesql .' '.$sql_bq. ' ' .$sql;

     $query = $this->db->query($sql_query);

  	 return $query->rows;
  }

   public function getAllPriceRules($start = 0, $length = 10, $filterData = null, $orderColumn = "id") {
     
      $sql_str = "SELECT * FROM " . DB_PREFIX . "amazon_price_rules WHERE 1";
 
		$total = $this->db->query($sql_str)->num_rows;
		$where = "";
		if (!empty($filterData['search'])) {
         $where .= "(price_from like '%" . $this->db->escape($filterData['search']) . "%'
                  OR price_to like '%".$this->db->escape($filterData['search'])."%'
                  OR price_value like '%".$this->db->escape($filterData['search'])."%'
                  OR price_type like '%".$this->db->escape($filterData['search'])."%'
                  OR price_opration like '%".$this->db->escape($filterData['search'])."%'
                  OR price_status like '%".$this->db->escape($filterData['search'])."%'
                  ) ";
         $sql_str .= " AND " . $where;
		}

		$totalFiltered = $this->db->query($sql_str)->num_rows;
		$sql_str .= " ORDER by {$orderColumn} DESC";

		if ($length != -1) {
		$sql_str .= " LIMIT " . $start . ", " . $length;
		}

		$results=$this->db->query($sql_str)->rows;

		$data = array(
			'data' => $results,
			'total' => $total,
			'totalFiltered' => $totalFiltered
		);

		return $data;

   }

  public function getPriceRule($rule_id) {

     $sql_query = '';

     $sql_query = "SELECT * FROM " . DB_PREFIX . "amazon_price_rules WHERE id = " .(int)$rule_id. "";

     $query = $this->db->query($sql_query);

     return $query->row;
  }

  public function getPriceRulesRanges($type) {

     $sql_query = '';

     $sql_query = "SELECT price_from as min , price_to as max FROM " . DB_PREFIX . "amazon_price_rules WHERE rule_type='".$type."'";

     $query = $this->db->query($sql_query);

     return $query->rows;
  }

  public function getExcludedPriceRulesRanges($id,$type) {

     $sql_query = '';

     $sql_query = "SELECT price_from as min , price_to as max FROM " . DB_PREFIX . "amazon_price_rules WHERE id !=".(int)$id." AND rule_type='".$type."'";

     $query = $this->db->query($sql_query);

     return $query->rows;
  }

  public function editPriceRule($data = array(),$rule_id) {

     $oldValue = $this->getPriceRule($rule_id);
     $oldValue = array_reverse($oldValue);
     array_pop($oldValue);
     $oldValue = array_reverse($oldValue);

     if($oldValue !== $data) {
        $this->updatePriceRuleMapping($rule_id ,$oldValue , $data);
     }

     $this->db->query("UPDATE " . DB_PREFIX . "amazon_price_rules SET price_from = '" . (int)$data['price_from'] . "', price_to = '" . (int)$data['price_to'] . "', price_value = '" . (int)$data['price_value'] . "',price_type = '" . (int)$data['price_type'] . "', price_opration = '" . $this->db->escape($data['price_opration']) . "', price_status = '" . (int)$data['price_status'] . "' WHERE id = '" .(int)$rule_id. "'");

  }

  public function updatePriceRuleMapping($rule_id, $old_value, $new_value) {
    $mapperItems =  $this->getMappedProducts($rule_id);
    if(!empty($mapperItems)){
        foreach ($mapperItems as $key => $value) {
        $price = $this->db->query("SELECT price FROM " . DB_PREFIX . "product WHERE product_id =".(int)$value['rule_product_id']."");

          if($value['source'] == 'amazon') {
               $param['price'] = $price->row['price'];
               $param['product_id'] = $value['rule_product_id'];
               $this->load->controller('price_rules_amazon/import_map/realtime_update',$param);
          }
       }
    }
  }

  public function deleteRule($rule_id) {
       $this->db->query("DELETE FROM " . DB_PREFIX . "amazon_price_rules WHERE id = " .(int)$rule_id. "");
  }

  public function getMappedProducts($rule_id) {
       $rule_mapped = $this->db->query("SELECT * FROM " . DB_PREFIX . "amazon_price_rules_map_product WHERE rule_id =".(int)$rule_id."");
       return $rule_mapped->rows;
  }

  public function getColumnNames() {
       $column_names = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'".DB_PREFIX."amazon_price_rules' AND COLUMN_NAME != N'id'");

       return $column_names->rows;
  }

  public function _buildQuery($data = array()) {

     $sql = '';

     foreach ($this->sql_keys as $key => $sql_key) {
       if (isset($data['filter_'.$sql_key]) && !is_null($data['filter_'.$sql_key])) {
         $sql .= " AND " .$sql_key. " = '" .$data['filter_'.$sql_key]. "'";
       }
     }

     return $sql;
  }

  public function _buildSortingQuery($data = array()) {
     $sql = '';

 		 $sort_data = array_merge($this->sql_keys, array('sort_order'));

   	 if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
   			$sql .= " ORDER BY " . $data['sort'];
     } else {
   			$sql .= " ORDER BY price_to";
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

     return $sql;
  }

}
