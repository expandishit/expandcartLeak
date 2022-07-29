<?php

class ModelAmazonPriceRuleAmazonExportMap extends Model {

    public function _applyPriceRule($params) {
      $price_change = 0 ;
      $newprice = $params['price'];
      $rule_ranges = $this->getPriceRules('price');
      foreach ($rule_ranges as $key => $rule_range) {
        if(!$this->getMapProduct($params['product_id'],'price')){
          if($this->_validateRuleRange($params['price'], $rule_range['price_from'], $rule_range['price_to'])){

           if($rule_range['price_opration']) { // take the precentage of the price of product
              $price_change += ($params['price'] * $rule_range['price_value']) / 100 ;
           } else{
             $price_change += $rule_range['price_value'];
           }

           if($rule_range['price_type']) { // take the precentage of the price of product
              $newprice = $params['price'] - $price_change ;
           } else{
             $newprice = $params['price'] + $price_change ;
           }

           $updateEntry = array(
             'product_id'     => $params['product_id'],
             'change_type'    => $rule_range['price_type'],
             'price_change'   => $price_change,
             'source'         => 'opencart',
             'rule_id'        => $rule_range['id'],
             'rule_type'      =>'price',
           );
          $this->addRuleMapProduct($updateEntry);
         }
       }
      }
      return $newprice;
    }
    public function _applyQuantityRule($params) {
    $price_change = 0 ;
    $newquantity = $params['quantity'];
    $rule_ranges = $this->getPriceRules('quantity');
    foreach ($rule_ranges as $key => $rule_range) {
      if(!$this->getMapProduct($params['product_id'],'quantity')){
        if($this->_validateRuleRange($params['quantity'], $rule_range['price_from'], $rule_range['price_to'])){



         if($rule_range['price_type']) { // take the precentage of the price of product
            $newquantity =abs($params['quantity'] - $rule_range['price_value']);
         } else{
           $newquantity = $params['quantity'] + $rule_range['price_value'] ;
         }

         $updateEntry = array(
           'product_id'     => $params['product_id'],
           'change_type'    => $rule_range['price_type'],
           'price_change'   => $price_change,
           'source'         => 'opencart',
           'rule_id'        => $rule_range['id'],
          'rule_type'      =>'quantity',
         );
        $this->addRuleMapProduct($updateEntry);
       }
     }
    }
    return $newquantity;
  }

    public function _realtimeUpdatePriceRule($params){

        $this->deleteEntry($params['product_id']);
        $this->_applyPriceRule($params);

    }

    public function _validateRuleRange($price, $min, $max){

      if($price >= $min && $price <= $max) {
        return 1;
      } else {
        return 0;
      }
  	 }

     public function getPriceRules($type) {

        $sql_query = '';

        $sql_query = "SELECT * FROM " . DB_PREFIX . "amazon_price_rules WHERE price_status = 1 AND rule_type='".$type."'";

        $query = $this->db->query($sql_query);

        return $query->rows;
     }

     public function addRuleMapProduct($data) {

        $this->db->query("INSERT INTO ".DB_PREFIX."amazon_price_rules_map_product SET rule_product_id = '" . (int)$data['product_id'] . "', change_price = '" . (float)$data['price_change'] . "',change_type = '" . (int)$data['change_type'] . "', source = '" . $this->db->escape($data['source']) . "',rule_id = '" . (int)$data['rule_id'] . "', rule_type= '".$data['rule_type']."'");

     }

     public function updateRuleMapProduct($data) {

        $this->db->query("UPDATE ".DB_PREFIX."amazon_price_rules_map_product SET rule_product_id = '" . (int)$data['product_id'] . "', change_price = '" . (float)$data['price_change'] . "',change_type = '" . (int)$data['change_type'] . "', source = '" . $this->db->escape($data['source']) . "' WHERE rule_id = '" . (int)$data['rule_id'] . "'");

     }

     public function getMapProduct($rule_product_id, $type) {
       $price_map_rules = $this->db->query("SELECT rule_product_id FROM " . DB_PREFIX . "amazon_price_rules_map_product WHERE rule_product_id =".(int)$rule_product_id." AND rule_type='".$type."'");

       return $price_map_rules->num_rows;
     }

     public function deleteEntry($product_id) {
       $status = $this->db->query("DELETE FROM " . DB_PREFIX . "amazon_price_rules_map_product WHERE rule_product_id =".(int)$product_id."");
       return $status;
     }

}
